<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Event;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventPublicController extends Controller
{
    public function index(Request $request)
    {
        $now = \Illuminate\Support\Carbon::now();
        $category = (string) $request->query('category', '');
        $allowedCategories = Event::categoryKeys();
        $currentCategory = in_array($category, $allowedCategories, true) ? $category : null;

        $baseQuery = Event::query()
            ->visibleTo(Auth::user())
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '>=', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '>=', $now);
                    });
            });

        if ($currentCategory) {
            if ($currentCategory === 'world') {
                $baseQuery->where(function ($q) use ($currentCategory) {
                    $q->whereJsonContains('category_keys', $currentCategory)
                        ->orWhereNull('category_keys');
                });
            } else {
                $baseQuery->whereJsonContains('category_keys', $currentCategory);
            }
        }

        $events = (clone $baseQuery)
            ->with('tickets')
            ->orderBy('start_at')
            ->paginate(12)
            ->withQueryString();

        $popularEvents = (clone $baseQuery)
            ->with('tickets')
            ->orderBy('start_at')
            ->limit(6)
            ->get();

        $stateCounts = (clone $baseQuery)
            ->whereNotNull('country')
            ->where('country', '<>', '')
            ->whereNotNull('state_id')
            ->select('country', 'state_id')
            ->selectRaw('count(*) as events_count')
            ->groupBy('country', 'state_id')
            ->orderBy('country')
            ->get();

        $countsByStateId = $stateCounts
            ->mapWithKeys(fn ($r) => [(int) $r->state_id => (int) $r->events_count])
            ->all();

        $statesAll = State::query()
            ->orderBy('name')
            ->get(['id', 'country_code', 'name']);

        $statesByCountry = $statesAll->groupBy('country_code');
        $countryCodes = $statesByCountry->keys()->values();

        $countries = Country::query()
            ->whereIn('code', $countryCodes)
            ->get()
            ->keyBy('code');

        $countryEmoji = function (string $code): string {
            $code = strtoupper($code);
            if (strlen($code) !== 2) {
                return '🌍';
            }

            return mb_convert_encoding(
                '&#'.(127397 + ord($code[0])).';'.'&#'.(127397 + ord($code[1])).';',
                'UTF-8',
                'HTML-ENTITIES'
            );
        };

        $abbr = function (string $name): string {
            $parts = preg_split('/\s+/', trim($name)) ?: [];
            $letters = '';
            foreach ($parts as $p) {
                if ($p === '') {
                    continue;
                }
                $letters .= mb_strtoupper(mb_substr($p, 0, 1));
                if (mb_strlen($letters) >= 3) {
                    break;
                }
            }
            if ($letters === '') {
                $letters = mb_strtoupper(mb_substr($name, 0, 2));
            }

            return $letters;
        };

        $localAreas = $countryCodes
            ->map(fn ($c) => strtoupper((string) $c))
            ->sortBy(fn ($c) => $c === 'MY' ? 0 : 1)
            ->values()
            ->map(function (string $code) use ($countries, $statesByCountry, $countsByStateId, $countryEmoji, $abbr) {
                $country = $countries->get($code);
                $states = $statesByCountry->get($code, collect());
                $stateItems = $states->map(function ($s) use ($countsByStateId, $abbr) {
                    $count = $countsByStateId[(int) $s->id] ?? 0;

                    return [
                        'id' => (int) $s->id,
                        'name' => (string) $s->name,
                        'abbr' => $abbr((string) $s->name),
                        'count' => (int) $count,
                    ];
                })->values();

                return [
                    'code' => $code,
                    'name' => (string) ($country?->name ?? $code),
                    'emoji' => $countryEmoji($code),
                    'states' => $stateItems,
                ];
            })
            ->values();

        $upcomingTotal = Event::query()
            ->visibleTo(Auth::user())
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '>=', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '>=', $now);
                    });
            })
            ->count();

        $categoryDefs = Event::categoryDefinitions();
        $categoryCounts = array_fill_keys(array_map(fn (array $d) => $d['key'], $categoryDefs), 0);
        $categoryScan = Event::query()
            ->visibleTo(Auth::user())
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '>=', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '>=', $now);
                    });
            })
            ->select('category_keys')
            ->orderBy('start_at')
            ->limit(1000)
            ->get();

        foreach ($categoryScan as $ev) {
            $keys = $ev->category_keys;
            if (! is_array($keys)) {
                $keys = [];
            }
            $keys = array_values(array_unique(array_filter($keys, fn ($v) => is_string($v) && $v !== '')));
            if (empty($keys)) {
                $categoryCounts['world'] = ($categoryCounts['world'] ?? 0) + 1;

                continue;
            }
            foreach ($keys as $k) {
                if (array_key_exists($k, $categoryCounts)) {
                    $categoryCounts[$k]++;
                }
            }
        }

        $categoryIconMap = collect($categoryDefs)->pluck('icon', 'key')->all();
        $categoryLabelMap = collect($categoryDefs)->pluck('label', 'key')->all();

        $categories = array_map(function (array $def) use ($categoryCounts, $currentCategory) {
            return [
                'key' => $def['key'],
                'label' => $def['label'],
                'icon' => $def['icon'],
                'count' => (int) ($categoryCounts[$def['key']] ?? 0),
                'active' => $currentCategory === $def['key'],
            ];
        }, $categoryDefs);

        return view('events.discover', compact('events', 'popularEvents', 'localAreas', 'categories', 'currentCategory', 'categoryIconMap', 'categoryLabelMap'));
    }

    public function show(Request $request, string $slug)
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['organizer.profile', 'organization', 'staffs.user.profile'])
            ->withCount('attendees')
            ->firstOrFail();

        $org = $event->organization;
        if ($org && ! ($org->is_public ?? false)) {
            if (! Auth::check()) {
                abort(404);
            }
            $user = Auth::user();
            $isMember = $org->users()
                ->wherePivot('status', 'approved')
                ->where('users.id', $user->id)
                ->exists();
            $isOrgCreator = (int) ($org->created_by ?? 0) === (int) $user->id;
            $isOrganizer = (int) ($event->organizer_id ?? 0) === (int) $user->id;
            if (! $isMember && ! $isOrgCreator && ! $isOrganizer) {
                abort(404);
            }
        }

        $tickets = $event->tickets()->orderBy('price')->get();

        return view('events.show', compact('event', 'tickets'));
    }
}
