@extends('layouts.app')

@section('content')
@php($titleText = $organization->name.' — Calendar')
@php($monthLabel = $monthStart->format('F Y'))
@php($inMonth = fn($d) => $d->format('Y-m') === $monthStart->format('Y-m'))
@php($weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'])

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="min-w-0">
            <div class="text-sm text-slate-600">Calendar</div>
            <h1 class="text-2xl sm:text-3xl font-semibold truncate">{{ $organization->name }}</h1>
            <div class="text-sm text-slate-600 mt-1">{{ $monthLabel }}</div>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('calendars.show', ['organization' => $organization->id, 'year' => $prev->year, 'month' => $prev->month]) }}" class="btn-surface px-3 py-2 rounded text-sm">← Prev</a>
            <a href="{{ route('calendars.show', ['organization' => $organization->id, 'year' => now()->year, 'month' => now()->month]) }}" class="btn-surface px-3 py-2 rounded text-sm">Today</a>
            <a href="{{ route('calendars.show', ['organization' => $organization->id, 'year' => $next->year, 'month' => $next->month]) }}" class="btn-surface px-3 py-2 rounded text-sm">Next →</a>
        </div>
    </div>

    <div class="app-card rounded-2xl overflow-hidden">
        <div class="grid grid-cols-7 bg-slate-50">
            @foreach($weekdays as $w)
                <div class="px-3 py-2 text-xs font-semibold text-slate-600 border-b">{{ $w }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @foreach($days as $d)
                @php($key = $d->toDateString())
                @php($list = $eventsByDate[$key] ?? [])
                <div class="min-h-[110px] p-2 border-t border-l {{ $loop->iteration % 7 === 1 ? 'border-l-0' : '' }} {{ $inMonth($d) ? '' : 'bg-slate-50/60' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold {{ $inMonth($d) ? '' : 'text-slate-500' }}">{{ $d->format('j') }}</div>
                        @if($d->isSameDay(now()))
                            <div class="text-[10px] px-2 py-0.5 rounded-full border badge-pill">Today</div>
                        @endif
                    </div>
                    <div class="space-y-1">
                        @foreach($list as $ev)
                            <a href="{{ route('events.show', $ev->slug) }}" class="block text-[11px] px-2 py-1 rounded-lg border hover:shadow-sm transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate font-medium">{{ $ev->title }}</span>
                                    <span class="shrink-0 text-slate-600">{{ optional($ev->start_at)->format('H:i') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('events.discover') }}" class="text-sm app-accent-text">← Back to Discover</a>
    </div>
</div>
@endsection

