@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ __('ui.discover.title') }}</h1>
            <a href="/" class="text-sm text-blue-600">Home</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($events as $event)
                @php $img = \Illuminate\Support\Str::startsWith($event->banner_path, ['http://', 'https://']) ? $event->banner_path : asset($event->banner_path); @endphp
                <a href="/events/{{ $event->slug }}" class="block rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition border bg-white">
                    <div class="relative">
                        @if ($event->banner_path)
                            <img src="{{ $img }}" alt="{{ $event->title }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
                        @endif
                        @php($tickets = $event->tickets ?? collect())
                        @php($hasFree = $tickets->where('type','free')->isNotEmpty())
                        @php($hasSponsor = $tickets->where('type','sponsor')->isNotEmpty())
                        @php($label = $hasFree ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar'))
                        @php($labelClass = $label==='Berbayar' ? 'bg-amber-100 text-amber-800' : ($label==='Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'))
                        @php($totalQty = (int) $tickets->sum('quantity'))
                        @php($totalSold = (int) $tickets->sum(function($t){ return (int)($t->sold ?? 0); }))
                        @php($remain = max(0, $totalQty - $totalSold))
                        <span class="absolute top-2 right-2 text-xs px-2 py-1 rounded-full bg-slate-900/90 text-white font-semibold border border-white/40 shadow">
                            {{ optional($event->start_at)->format('d M Y') }}
                        </span>
                        <span class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full border {{ $labelClass }}">{{ $label }}</span>
                        <span class="absolute bottom-2 right-2 text-xs px-2 py-1 rounded-full bg-slate-900/90 text-white border border-white/40 shadow">
                            Baki tiket: {{ $remain }}
                        </span>
                    </div>
                    <div class="p-4">
                        <h2 class="text-lg font-medium">{{ $event->title }}</h2>
                        <p class="text-sm text-slate-600">{{ $event->location }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center text-slate-500">No events yet.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
@endsection
