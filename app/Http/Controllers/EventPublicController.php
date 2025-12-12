<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventPublicController extends Controller
{
    public function index(Request $request)
    {
        $threshold = \Illuminate\Support\Carbon::today();
        $events = Event::query()
            ->where('is_published', true)
            ->whereDate('start_at', '>=', $threshold)
            ->with('tickets')
            ->orderBy('start_at')
            ->paginate(12);

        return view('events.discover', compact('events'));
    }

    public function show(Request $request, string $slug)
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with(['organizer.profile', 'organization'])
            ->firstOrFail();
        $tickets = $event->tickets()->orderBy('price')->get();
        return view('events.show', compact('event', 'tickets'));
    }
}
