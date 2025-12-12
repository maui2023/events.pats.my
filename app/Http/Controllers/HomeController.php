<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $threshold = \Illuminate\Support\Carbon::today();
        $events = Event::query()
            ->where('is_published', true)
            ->whereDate('start_at', '>=', $threshold)
            ->with('tickets')
            ->orderBy('start_at')
            ->limit(3)
            ->get();

        $eventMeta = [];
        foreach ($events as $e) {
            $eventMeta[$e->slug] = match ($e->slug) {
                'bespoke-tech-meetup' => ['pricing' => 'Percuma', 'category' => 'Training'],
                'product-design-workshop' => ['pricing' => 'Penaja', 'category' => 'Community'],
                default => ['pricing' => 'Berbayar', 'category' => 'IT'],
            };
        }

        return view('home', compact('events', 'eventMeta'));
    }
}
