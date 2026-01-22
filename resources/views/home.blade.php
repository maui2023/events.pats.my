@extends('layouts.app')

@section('content')
    <section class="relative overflow-hidden home-hero">
        <style>
            .blob{position:absolute;border-radius:50%;filter:blur(40px);opacity:.35}
            .blob.b1{width:280px;height:280px;background:#b91c1c;top:-60px;left:-60px;animation:float1 8s ease-in-out infinite}
            .blob.b2{width:320px;height:320px;background:#4b5563;top:20%;right:-80px;animation:float2 9s ease-in-out infinite}
            .blob.b3{width:220px;height:220px;background:#111827;bottom:-60px;left:40%;animation:float3 10s ease-in-out infinite}
            @keyframes float1{0%{transform:translateY(0)}50%{transform:translateY(-10px)}100%{transform:translateY(0)}}
            @keyframes float2{0%{transform:translateX(0)}50%{transform:translateX(-12px)}100%{transform:translateX(0)}}
            @keyframes float3{0%{transform:translate(-8px,0)}50%{transform:translate(8px,-6px)}100%{transform:translate(-8px,0)}}
        </style>
        <span class="blob b1"></span>
        <span class="blob b2"></span>
        <span class="blob b3"></span>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 py-12 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-4xl font-semibold mb-3">{{ __('ui.hero.title') }}</h1>
                <p class="text-slate-600 mb-6">{{ __('ui.hero.subtitle') }}</p>
                <div class="flex gap-3">
                    <a href="/create" class="inline-block bg-blue-600 text-white px-5 py-2 rounded">{{ __('ui.cta.create') }}</a>
                    <a href="{{ route('events.discover') }}" class="inline-block bg-white border px-5 py-2 rounded">{{ __('ui.cta.browse') }}</a>
                </div>
            </div>
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-medium mb-4">{{ __('ui.features.title') }}</h3>
                <ul class="space-y-3 text-slate-700">
                    <li class="flex items-center gap-3"><span class="inline-flex size-8 rounded-lg bg-red-100 items-center justify-center">🎟️</span><span>{{ __('ui.feature.create') }}</span></li>
                    <li class="flex items-center gap-3"><span class="inline-flex size-8 rounded-lg bg-blue-100 items-center justify-center">💳</span><span>{{ __('ui.feature.ticket') }}</span></li>
                    <li class="flex items-center gap-3"><span class="inline-flex size-8 rounded-lg bg-green-100 items-center justify-center">📊</span><span>{{ __('ui.feature.analytics') }}</span></li>
                    <li class="flex items-center gap-3"><span class="inline-flex size-8 rounded-lg bg-slate-100 items-center justify-center">🧾</span><span>{{ __('ui.feature.qr') }}</span></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
        <h2 class="text-2xl font-semibold mb-6">{{ __('ui.section.featured') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($events as $event)
                @php
                    $img = \Illuminate\Support\Str::startsWith($event->banner_path, ['http://', 'https://']) ? $event->banner_path : asset($event->banner_path);
                    $tickets = $event->tickets ?? collect();
                    $hasFree = $tickets->where('type','free')->isNotEmpty();
                    $hasSponsor = $tickets->where('type','sponsor')->isNotEmpty();
                    $pricing = $hasFree ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar');
                    $pricingClass = $pricing==='Berbayar' ? 'bg-amber-100 text-amber-800' : ($pricing==='Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800');
                    $totalQty = (int) $tickets->sum('quantity');
                    $totalSold = (int) $tickets->sum(function($t){ return (int)($t->sold ?? 0); });
                    $remain = max(0, $totalQty - $totalSold);
                    $meta = $eventMeta[$event->slug] ?? ['pricing' => $pricing, 'category' => 'IT'];
                @endphp
                <a href="/events/{{ $event->slug }}" class="block rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition border bg-white">
                    <div class="relative">
                        @if ($event->banner_path)
                            <img src="{{ $img }}" alt="{{ $event->title }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
                        @endif
                        <span class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full border {{ $pricingClass }}">{{ $pricing }}</span>
                        <span class="absolute top-2 right-2 text-xs px-2 py-1 rounded-full bg-white/90 border text-slate-900">{{ optional($event->start_at)->format('d M Y') }}</span>
                        <span class="absolute bottom-2 right-2 text-xs px-2 py-1 rounded-full bg-white/90 border text-slate-900">Baki: {{ $remain }}</span>
                    </div>
                    <div class="p-4">
                        <div class="mb-2">
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-700">{{ $meta['category'] }}</span>
                        </div>
                        <h3 class="text-lg font-medium">{{ $event->title }}</h3>
                        <p class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 90) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
