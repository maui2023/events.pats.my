<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Country;
use App\Models\Ticket;
use App\Models\Profile;
use Carbon\Carbon;

class CreateEventController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $tier = $profile->tier ?? 'FREE';
        $limits = [
            'FREE' => ['monthly_events' => 1, 'max_days' => 3, 'max_participants' => 20],
            'PRO' => ['monthly_events' => 15, 'max_days' => 31, 'max_participants' => 200],
            'VIP' => ['monthly_events' => null, 'max_days' => null, 'max_participants' => null],
        ];
        $tierLimits = $limits[$tier] ?? $limits['FREE'];

        $countries = Country::query()
            ->orderBy('name')
            ->get()
            ->map(function (Country $c) {
                $code = strtoupper($c->code);
                $emoji = mb_convert_encoding('&#' . (127397 + ord($code[0])) . ';' . '&#' . (127397 + ord($code[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
                return [
                    'code' => $c->code,
                    'name' => $c->name,
                    'emoji' => $emoji,
                ];
            });

        $organizations = collect();
        if (in_array($tier, ['PRO','VIP'])) {
            $organizations = $user->organizations()->wherePivot('status','approved')->orderBy('name')->get();
        }
        return view('events.create', ['countries' => $countries, 'tier' => $tier, 'tierLimits' => $tierLimits, 'organizations' => $organizations]);
    }

    public function edit(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $event = Event::where('slug', $slug)->firstOrFail();
        if ($event->organizer_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }
        $user = Auth::user();
        $profile = \App\Models\Profile::firstOrCreate(['user_id' => $user->id]);
        $tier = $profile->tier ?? 'FREE';
        $limits = [
            'FREE' => ['monthly_events' => 1, 'max_days' => 3, 'max_participants' => 20],
            'PRO' => ['monthly_events' => 15, 'max_days' => 31, 'max_participants' => 200],
            'VIP' => ['monthly_events' => null, 'max_days' => null, 'max_participants' => null],
        ];
        $tierLimits = $limits[$tier] ?? $limits['FREE'];

        $countries = Country::query()
            ->orderBy('name')
            ->get()
            ->map(function (Country $c) {
                $code = strtoupper($c->code);
                $emoji = mb_convert_encoding('&#' . (127397 + ord($code[0])) . ';' . '&#' . (127397 + ord($code[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
                return [
                    'code' => $c->code,
                    'name' => $c->name,
                    'emoji' => $emoji,
                ];
            });
        $ticket = $event->tickets()->orderBy('id')->first();
        $organizations = collect();
        if (in_array($tier, ['PRO','VIP'])) {
            $organizations = $user->organizations()->wherePivot('status','approved')->orderBy('name')->get();
        }
        
        $staffs = \App\Models\EventStaff::with('user')->where('event_id', $event->id)->get();
        
        return view('events.edit', compact('event', 'countries', 'ticket', 'tier', 'tierLimits', 'organizations', 'staffs'));
    }

    public function addStaff(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        if ($event->organizer_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user->id === $event->organizer_id) {
            return back()->withErrors(['email' => 'Penganjur sudah mempunyai akses.']);
        }

        try {
            \App\Models\EventStaff::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'role' => 'scanner'
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Pengguna ini sudah ditambah sebagai petugas.']);
        }

        return back()->with('success', 'Petugas berjaya ditambah.');
    }

    public function removeStaff(Request $request, string $slug, int $staffId)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        if ($event->organizer_id !== Auth::id()) {
            abort(403);
        }

        $staff = \App\Models\EventStaff::where('id', $staffId)
                    ->where('event_id', $event->id)
                    ->firstOrFail();
        
        $staff->delete();

        return back()->with('success', 'Petugas berjaya dipadam.');
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:35'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date', 'after_or_equal:tomorrow'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'banner_path' => ['nullable', 'string', 'max:500'],
            'banner_file' => ['nullable', 'image', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
            'pricing_type' => ['nullable', 'in:free,paid,sponsor'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
            'ticket_base_price' => ['nullable', 'numeric', 'min:0'],
            'ticket_sponsor_amount' => ['nullable', 'numeric', 'min:0'],
            'ticket_quantity' => ['nullable', 'integer', 'min:0'],
            'organization_id' => ['nullable','integer','exists:organizations,id'],
        ]);

        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $tier = $profile->tier ?? 'FREE';
        $limits = [
            'FREE' => ['monthly_events' => 1, 'max_days' => 3, 'max_participants' => 20],
            'PRO' => ['monthly_events' => 15, 'max_days' => 31, 'max_participants' => 200],
            'VIP' => ['monthly_events' => null, 'max_days' => null, 'max_participants' => null],
        ];
        $tierLimits = $limits[$tier] ?? $limits['FREE'];

        $start = Carbon::parse($data['start_at']);
        $minStart = Carbon::tomorrow();
        if ($start->lt($minStart)) {
            return back()->withErrors(['start_at' => 'Tarikh mula mestilah sekurang-kurangnya esok.'])->withInput();
        }
        $end = isset($data['end_at']) ? Carbon::parse($data['end_at']) : null;

        $monthEventsCount = Event::where('organizer_id', $user->id)
            ->whereYear('start_at', $start->year)
            ->whereMonth('start_at', $start->month)
            ->count();
        if (isset($tierLimits['monthly_events']) && $tierLimits['monthly_events'] > 0 && $monthEventsCount >= $tierLimits['monthly_events']) {
            return back()->withErrors(['title' => 'Had acara bulanan dicapai untuk tier anda.'])->withInput();
        }
        if ($end && isset($tierLimits['max_days']) && $tierLimits['max_days'] > 0) {
            $hours = $start->diffInHours($end);
            $maxHours = ($tierLimits['max_days'] ?? 0) * 24;
            if ($hours > $maxHours) {
                return back()->withErrors(['end_at' => 'Tempoh acara melebihi ' . $maxHours . ' jam untuk tier anda.'])->withInput();
            }
        }
        $qty = (int)($data['ticket_quantity'] ?? 0);
        if (isset($tierLimits['max_participants']) && $tierLimits['max_participants'] > 0 && $qty > $tierLimits['max_participants']) {
            return back()->withErrors(['ticket_quantity' => 'Jumlah tiket melebihi had peserta tier anda.'])->withInput();
        }

        $slug = Str::slug($data['title']);
        $base = $slug;
        $i = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $bannerPath = $data['banner_path'] ?? null;
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $filename = 'banner_'.$slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $dir = public_path('uploads/banners');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $bannerPath = 'uploads/banners/'.$filename;
        }

        $organizationId = null;
        if (!empty($data['organization_id'])) {
            if (!in_array($tier, ['PRO','VIP'])) {
                return back()->withErrors(['organization_id' => 'Hanya PRO/VIP boleh menetapkan organisasi untuk acara.'])->withInput();
            }
            $belongs = $user->organizations()->wherePivot('status','approved')->where('organizations.id', $data['organization_id'])->exists();
            if (!$belongs) {
                return back()->withErrors(['organization_id' => 'Anda tidak mempunyai akses ke organisasi tersebut atau belum diluluskan.'])->withInput();
            }
            $organizationId = (int)$data['organization_id'];
        }

        $event = Event::create([
            'organizer_id' => Auth::id(),
            'organization_id' => $organizationId,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'] ?? null,
            'location' => $data['location'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'country' => $data['country'] ?? null,
            'banner_path' => $bannerPath,
            'is_published' => (bool)($data['is_published'] ?? false),
        ]);

        $ptype = $data['pricing_type'] ?? null;
        if ($ptype === 'free') {
            Ticket::create([
                'event_id' => $event->id,
                'name' => 'Umum',
                'type' => 'free',
                'price' => null,
                'base_price' => null,
                'sponsor_amount' => null,
                'quantity' => (int)($data['ticket_quantity'] ?? 0),
            ]);
        } elseif ($ptype === 'paid') {
            Ticket::create([
                'event_id' => $event->id,
                'name' => 'Umum',
                'type' => 'paid',
                'price' => (float)($data['ticket_price'] ?? 0),
                'base_price' => null,
                'sponsor_amount' => null,
                'quantity' => (int)($data['ticket_quantity'] ?? 0),
            ]);
        } elseif ($ptype === 'sponsor') {
            $base = (float)($data['ticket_base_price'] ?? 0);
            $sponsor = (float)($data['ticket_sponsor_amount'] ?? 0);
            $due = max(0, $base - $sponsor);
            Ticket::create([
                'event_id' => $event->id,
                'name' => 'Umum (Sponsor)',
                'type' => 'sponsor',
                'price' => $due,
                'base_price' => $base,
                'sponsor_amount' => $sponsor,
                'quantity' => (int)($data['ticket_quantity'] ?? 0),
            ]);
        }

        return redirect()->to('/events/'.$event->slug);
    }

    public function update(Request $request, string $slug)
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        $event = Event::where('slug', $slug)->firstOrFail();
        if ($event->organizer_id !== Auth::id()) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:35'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'banner_path' => ['nullable', 'string', 'max:500'],
            'banner_file' => ['nullable', 'image', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
            'pricing_type' => ['nullable', 'in:free,paid,sponsor'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
            'ticket_base_price' => ['nullable', 'numeric', 'min:0'],
            'ticket_sponsor_amount' => ['nullable', 'numeric', 'min:0'],
            'ticket_quantity' => ['nullable', 'integer', 'min:0'],
            'organization_id' => ['nullable','integer','exists:organizations,id'],
        ]);

        $user = Auth::user();
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        $tier = $profile->tier ?? 'FREE';
        $limits = [
            'FREE' => ['monthly_events' => 1, 'max_days' => 3, 'max_participants' => 20],
            'PRO' => ['monthly_events' => 15, 'max_days' => 31, 'max_participants' => 200],
            'VIP' => ['monthly_events' => null, 'max_days' => null, 'max_participants' => null],
        ];
        $tierLimits = $limits[$tier] ?? $limits['FREE'];
        $start = Carbon::parse($data['start_at']);
        $end = isset($data['end_at']) ? Carbon::parse($data['end_at']) : null;
        if ($end && isset($tierLimits['max_days']) && $tierLimits['max_days'] > 0) {
            $hours = $start->diffInHours($end);
            $maxHours = ($tierLimits['max_days'] ?? 0) * 24;
            if ($hours > $maxHours) {
                return back()->withErrors(['end_at' => 'Tempoh acara melebihi ' . $maxHours . ' jam untuk tier anda.'])->withInput();
            }
        }

        $bannerPathInput = trim((string)($data['banner_path'] ?? ''));
        $bannerPath = $event->banner_path;
        if ($bannerPathInput !== '') {
            $bannerPath = $bannerPathInput;
        }
        if ($request->hasFile('banner_file')) {
            $file = $request->file('banner_file');
            $filename = 'banner_'.$event->slug.'_'.time().'.'.$file->getClientOriginalExtension();
            $dir = public_path('uploads/banners');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $file->move($dir, $filename);
            $bannerPath = 'uploads/banners/'.$filename;
        }

        $organizationId = null;
        if (!empty($data['organization_id'])) {
            if (!in_array($tier, ['PRO','VIP'])) {
                return back()->withErrors(['organization_id' => 'Hanya PRO/VIP boleh menetapkan organisasi untuk acara.'])->withInput();
            }
            $belongs = $user->organizations()->wherePivot('status','approved')->where('organizations.id', $data['organization_id'])->exists();
            if (!$belongs) {
                return back()->withErrors(['organization_id' => 'Anda tidak mempunyai akses ke organisasi tersebut atau belum diluluskan.'])->withInput();
            }
            $organizationId = (int)$data['organization_id'];
        }
        $event->title = $data['title'];
        $event->description = $data['description'] ?? null;
        $event->start_at = $data['start_at'];
        $event->end_at = $data['end_at'] ?? null;
        $event->location = $data['location'] ?? null;
        $event->postcode = $data['postcode'] ?? null;
        $event->country = $data['country'] ?? null;
        $event->banner_path = $bannerPath;
        $event->is_published = (bool)($data['is_published'] ?? false);
        $event->organization_id = $organizationId;
        $event->save();

        $ptype = $data['pricing_type'] ?? null;
        if ($ptype) {
            $ticket = $event->tickets()->orderBy('id')->first();
            if (!$ticket) {
                $ticket = new Ticket(['event_id' => $event->id]);
            }
            if ($ptype === 'free') {
                $ticket->type = 'free';
                $ticket->price = null;
                $ticket->base_price = null;
                $ticket->sponsor_amount = null;
                $ticket->name = $ticket->name ?: 'Umum';
                $ticket->quantity = (int)($data['ticket_quantity'] ?? 0);
            } elseif ($ptype === 'paid') {
                $ticket->type = 'paid';
                $ticket->price = (float)($data['ticket_price'] ?? 0);
                $ticket->base_price = null;
                $ticket->sponsor_amount = null;
                $ticket->name = $ticket->name ?: 'Umum';
                $ticket->quantity = (int)($data['ticket_quantity'] ?? 0);
            } elseif ($ptype === 'sponsor') {
                $base = (float)($data['ticket_base_price'] ?? 0);
                $sponsor = (float)($data['ticket_sponsor_amount'] ?? 0);
                $due = max(0, $base - $sponsor);
                $ticket->type = 'sponsor';
                $ticket->price = $due;
                $ticket->base_price = $base;
                $ticket->sponsor_amount = $sponsor;
                $ticket->name = $ticket->name ?: 'Umum (Sponsor)';
                $ticket->quantity = (int)($data['ticket_quantity'] ?? 0);
            }
            $ticket->event_id = $event->id;
            $ticket->save();
        }

        return redirect()->to('/events/'.$event->slug);
    }
}
