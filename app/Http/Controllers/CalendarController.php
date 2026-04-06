<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $organizations = Organization::query()
            ->where('status', 'approved')
            ->where('is_public', true)
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('calendars.index', compact('organizations', 'search'));
    }

    public function show(Request $request, Organization $organization)
    {
        if (!($organization->is_public ?? false)) {
            if (!Auth::check()) {
                abort(404);
            }
            $user = Auth::user();
            $isMember = $organization->users()
                ->wherePivot('status', 'approved')
                ->where('users.id', $user->id)
                ->exists();
            if (!$isMember && (int) ($organization->created_by ?? 0) !== (int) $user->id) {
                abort(404);
            }
        }

        if (!Auth::check()) {
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->guest('/login');
        }

        $year = (int) $request->query('year', (int) Carbon::now()->year);
        $month = (int) $request->query('month', (int) Carbon::now()->month);
        if ($year < 2000 || $year > 2100) {
            $year = (int) Carbon::now()->year;
        }
        if ($month < 1 || $month > 12) {
            $month = (int) Carbon::now()->month;
        }

        $monthStart = Carbon::create($year, $month, 1, 0, 0, 0);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $events = Event::query()
            ->where('organization_id', $organization->id)
            ->where('is_published', true)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($qq) use ($monthStart, $monthEnd) {
                        $qq->whereNotNull('end_at')
                            ->where('start_at', '<=', $monthEnd)
                            ->where('end_at', '>=', $monthStart);
                    });
            })
            ->orderBy('start_at')
            ->get(['id', 'slug', 'title', 'start_at', 'end_at']);

        $eventsByDate = [];
        foreach ($events as $e) {
            $k = optional($e->start_at)->toDateString();
            if (!$k) {
                continue;
            }
            $eventsByDate[$k] = $eventsByDate[$k] ?? [];
            $eventsByDate[$k][] = $e;
        }

        $days = [];
        $cursor = $gridStart->copy();
        while ($cursor->lte($gridEnd)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        $prev = $monthStart->copy()->subMonth();
        $next = $monthStart->copy()->addMonth();

        return view('calendars.show', [
            'organization' => $organization,
            'days' => $days,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'eventsByDate' => $eventsByDate,
            'prev' => $prev,
            'next' => $next,
        ]);
    }
}
