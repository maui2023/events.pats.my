<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $now = \Illuminate\Support\Carbon::now();
        $events = Event::query()
            ->visibleTo(Auth::user())
            ->where('is_published', true)
            ->where(function ($q) use ($now) {
                $q->whereNotNull('end_at')->where('end_at', '>=', $now)
                    ->orWhere(function ($qq) use ($now) {
                        $qq->whereNull('end_at')->where('start_at', '>=', $now);
                    });
            })
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
