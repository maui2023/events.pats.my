@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <div class="flex items-start justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">{{ __('ui.discover.title') }}</h1>
                <p class="mt-2 text-sm text-slate-600">Explore popular events near you, browse by category, or discover local communities.</p>
            </div>
            <div class="hidden sm:flex gap-2">
                <a href="/" class="btn-surface px-4 py-2 rounded text-sm">Home</a>
                @if(!empty($currentCategory))
                    <a href="{{ route('events.discover') }}#all-events" class="btn-surface px-4 py-2 rounded text-sm">Clear</a>
                @endif
                <a href="{{ route('events.discover') }}#all-events" class="btn-accent px-4 py-2 rounded text-sm">View all</a>
            </div>
        </div>

        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold">Popular Events</h2>
                    <a href="#all-events" class="text-sm app-accent-text">View All</a>
                </div>
                <div class="app-card rounded-xl overflow-hidden">
                    <div class="divide-y">
                        @forelse ($popularEvents as $event)
                            @php
                                $img = \Illuminate\Support\Str::startsWith($event->banner_path, ['http://', 'https://']) ? $event->banner_path : asset($event->banner_path);
                                $tickets = $event->tickets ?? collect();
                                $hasFree = $tickets->where('type', 'free')->isNotEmpty();
                                $hasSponsor = $tickets->where('type', 'sponsor')->isNotEmpty();
                                $label = $hasFree ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar');
                                $labelClass = $label === 'Berbayar' ? 'bg-amber-100 text-amber-800' : ($label === 'Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800');
                                $keys = is_array($event->category_keys ?? null) ? ($event->category_keys ?? []) : [];
                                $firstKey = (string) ($keys[0] ?? '');
                                $eventIcon = $event->icon ?: ($categoryIconMap[$firstKey] ?? '🌍');
                            @endphp
                            <a href="/events/{{ $event->slug }}" class="block px-4 py-4 hover:bg-black/5">
                                <div class="flex items-center gap-4">
                                    <div class="shrink-0">
                                        @if ($event->banner_path)
                                            <img src="{{ $img }}" alt="{{ $event->title }}" class="w-14 h-14 rounded-lg object-cover border">
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-slate-100 border flex items-center justify-center text-slate-500 text-xs">No image</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-sm font-medium truncate"><span class="mr-2">{{ $eventIcon }}</span>{{ $event->title }}</p>
                                            <span class="text-xs px-2 py-1 rounded-full border {{ $labelClass }}">{{ $label }}</span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                                            <span>{{ optional($event->start_at)->format('D, d M') }}</span>
                                            <span class="opacity-60">•</span>
                                            <span class="truncate">{{ $event->location ?: 'Online / TBA' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-10 text-center text-slate-600">No events yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <h2 class="text-lg font-semibold mb-3">Browse by Category</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($categories as $c)
                        <a href="{{ route('events.discover', ['category' => $c['key']]) }}#all-events" class="app-card rounded-xl p-4 hover:shadow-sm transition {{ !empty($c['active']) ? 'ring-2 ring-[color:var(--accent)]' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border flex items-center justify-center text-base">{{ $c['icon'] }}</div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium truncate">{{ $c['label'] }}</div>
                                    <div class="text-xs text-slate-600">{{ number_format($c['count']) }} events</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-10">
            <h2 class="text-lg font-semibold mb-3">Explore Local Events</h2>
            <div class="app-card rounded-xl p-4">
                <div class="space-y-6">
                    @forelse ($localAreas as $area)
                        <div>
                            <div class="text-sm font-semibold mb-3">{{ $area['emoji'] }} {{ $area['name'] }}</div>
                            <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-8 gap-3">
                                @foreach (($area['states'] ?? []) as $s)
                                    <div class="text-center">
                                        <div class="app-card rounded-xl p-3 flex flex-col items-center justify-center">
                                            <div class="text-xs font-semibold">{{ $s['abbr'] }}</div>
                                            <div class="text-lg font-semibold mt-1">{{ $s['count'] }}</div>
                                        </div>
                                        <div class="mt-1 text-[11px] text-slate-600 truncate">{{ $s['name'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-600">Tiada negeri untuk dipaparkan.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="mt-10" id="all-events">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">
                    All Upcoming Events
                    @if(!empty($currentCategory) && !empty($categoryLabelMap[$currentCategory]))
                        <span class="text-sm font-normal text-slate-600 ml-2">({{ $categoryLabelMap[$currentCategory] }})</span>
                    @endif
                </h2>
                <div class="text-sm text-slate-600">{{ $events->total() }} events</div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($events as $event)
                    @php
                        $img = \Illuminate\Support\Str::startsWith($event->banner_path, ['http://', 'https://']) ? $event->banner_path : asset($event->banner_path);
                        $tickets = $event->tickets ?? collect();
                        $hasFree = $tickets->where('type','free')->isNotEmpty();
                        $hasSponsor = $tickets->where('type','sponsor')->isNotEmpty();
                        $label = $hasFree ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar');
                        $labelClass = $label==='Berbayar' ? 'bg-amber-100 text-amber-800' : ($label==='Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800');
                        $totalQty = (int) $tickets->sum('quantity');
                        $totalSold = (int) $tickets->sum(function($t){ return (int)($t->sold ?? 0); });
                        $remain = max(0, $totalQty - $totalSold);
                        $keys = is_array($event->category_keys ?? null) ? ($event->category_keys ?? []) : [];
                        $keys = array_values(array_unique(array_filter($keys, fn($v) => is_string($v) && $v !== '')));
                        $firstKey = (string) ($keys[0] ?? '');
                        $eventIcon = $event->icon ?: ($categoryIconMap[$firstKey] ?? '🌍');
                        $badgeKeys = array_slice($keys, 0, 2);
                    @endphp
                    <a href="/events/{{ $event->slug }}" class="block rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition border bg-white">
                        <div class="relative">
                            @if ($event->banner_path)
                                <img src="{{ $img }}" alt="{{ $event->title }}" class="w-full h-40 object-cover">
                            @else
                                <div class="w-full h-40 bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
                            @endif
                            <span class="absolute top-2 right-2 text-xs px-2 py-1 rounded-full badge-pill">
                                {{ optional($event->start_at)->format('d M Y') }}
                            </span>
                            <span class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full border {{ $labelClass }}">{{ $label }}</span>
                            <span class="absolute bottom-2 right-2 text-xs px-2 py-1 rounded-full badge-pill">
                                Baki tiket: {{ $remain }}
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="text-lg font-medium truncate"><span class="mr-2">{{ $eventIcon }}</span>{{ $event->title }}</div>
                            </div>
                            @if(!empty($badgeKeys))
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($badgeKeys as $k)
                                        @if(!empty($categoryLabelMap[$k]))
                                            <span class="text-xs px-2 py-0.5 rounded border">{{ $categoryLabelMap[$k] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            <p class="text-sm text-slate-600">{{ $event->location }}</p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-center text-slate-600">No events yet.</div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $events->links() }}
            </div>
        </section>
    </div>
@endsection
