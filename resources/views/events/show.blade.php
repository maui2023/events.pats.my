@extends('layouts.app')

@section('content')
@php($img = $event->banner_path ? (\Illuminate\Support\Str::startsWith($event->banner_path, ['http://','https://']) ? $event->banner_path : asset($event->banner_path)) : null)
@php($org = $event->organization)
@php($creator = $event->organizer)
@php($nick = optional($creator?->profile)->nickname)
@php($hosts = collect([$creator])->filter()->merge(($event->staffs ?? collect())->map(fn($s) => $s->user)->filter())->unique('id')->values())
@php($ticketsCol = $tickets ?? collect())
@php($defaultTicketId = (int) ($ticketsCol->first()?->id ?? 0))
@php($selectedTicketId = (int) request()->query('ticket_id', $defaultTicketId))
@php($selectedTicket = $ticketsCol->firstWhere('id', $selectedTicketId) ?: $ticketsCol->first())
@php($mapQuery = $event->location ? urlencode($event->location) : null)
@php($catDefs = \App\Models\Event::categoryDefinitions())
@php($catIconMap = collect($catDefs)->pluck('icon', 'key')->all())
@php($catLabelMap = collect($catDefs)->pluck('label', 'key')->all())
@php($catKeys = is_array($event->category_keys ?? null) ? ($event->category_keys ?? []) : [])
@php($catKeys = array_values(array_unique(array_filter($catKeys, fn($v) => is_string($v) && $v !== ''))))
@php($firstCatKey = (string) ($catKeys[0] ?? ''))
@php($eventIcon = $event->icon ?: ($catIconMap[$firstCatKey] ?? '🌍'))
@php($badgeKeys = array_slice($catKeys, 0, 3))

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
  <div class="mb-6">
    <a href="{{ route('events.discover') }}" class="text-sm app-accent-text">← Kembali ke senarai acara</a>
  </div>

  @if($errors->any())
    <div class="app-card rounded-xl p-4 mb-6 text-sm">
      <div class="font-medium mb-2">Ada masalah</div>
      <ul class="list-disc pl-5 text-slate-600 space-y-1">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('order_created'))
    <div class="app-card rounded-xl p-4 mb-6 text-sm text-green-700">Pesanan tiket dicipta.</div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <aside class="lg:col-span-4 space-y-4">
      <div class="app-card rounded-2xl overflow-hidden">
        @if(!empty($img))
          <img src="{{ $img }}" alt="{{ $event->title }}" class="w-full aspect-[4/3] object-cover">
        @else
          <div class="w-full aspect-[4/3] bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
        @endif
        <div class="p-4">
          @if($org)
            <div class="text-xs text-slate-600 mb-2">Featured in <span class="font-medium">{{ $org->name }}</span></div>
          @endif
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-medium">Hosted By</div>
            <div class="text-xs text-slate-600">{{ (int)($event->attendees_count ?? 0) }} going</div>
          </div>
          <div class="mt-3 space-y-2">
            @forelse($hosts as $h)
              @php($hp = $h?->profile)
              @php($hname = $hp?->nickname ?: ($h?->name ?: '—'))
              <div class="flex items-center gap-3">
                @if(!empty($hp?->avatar))
                  <img src="{{ asset($hp->avatar) }}" class="w-9 h-9 rounded-full border object-cover" />
                @else
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($hname) }}&background=0D8ABC&color=fff&size=96" class="w-9 h-9 rounded-full border" />
                @endif
                <div class="min-w-0">
                  <div class="text-sm font-medium truncate">{{ $hname }}</div>
                  <div class="text-xs text-slate-600 truncate">{{ $h?->email }}</div>
                </div>
              </div>
            @empty
              <div class="text-sm text-slate-600">—</div>
            @endforelse
          </div>
        </div>
      </div>

      @if(Auth::check() && ($event->organizer_id === Auth::id() || ($event->staffs ?? collect())->where('user_id', Auth::id())->isNotEmpty()))
        <div class="app-card rounded-2xl p-4">
          <div class="font-medium mb-3">Menu Penganjur</div>
          <div class="flex flex-col gap-2">
            <a href="{{ route('events.manage', $event->slug) }}" class="block text-center px-4 py-2 rounded btn-accent text-sm font-medium">Urus Peserta & Pesanan</a>
            <div class="grid grid-cols-2 gap-2">
              <a href="{{ route('events.scan', $event->slug) }}" class="block text-center px-4 py-2 rounded btn-surface text-sm">Scan QR</a>
              <a href="{{ route('events.edit', $event->slug) }}" class="block text-center px-4 py-2 rounded btn-surface text-sm">Edit Acara</a>
            </div>
          </div>
        </div>
      @endif
    </aside>

    <div class="lg:col-span-8 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div>
          <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight"><span class="mr-2">{{ $eventIcon }}</span>{{ $event->title }}</h1>
          @if(!empty($badgeKeys))
            <div class="mt-3 flex flex-wrap gap-2">
              @foreach($badgeKeys as $k)
                @if(!empty($catLabelMap[$k]))
                  <span class="text-xs px-2 py-0.5 rounded-full border">{{ $catLabelMap[$k] }}</span>
                @endif
              @endforeach
            </div>
          @endif
          <div class="mt-4 space-y-2 text-sm text-slate-700">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-slate-100 border flex items-center justify-center">🗓️</div>
              <div class="min-w-0">
                <div class="font-medium">{{ optional($event->start_at)->format('l, d M Y') ?: '—' }}</div>
                <div class="text-slate-600">
                  {{ optional($event->start_at)->format('H:i') ?: '—' }}
                  @if($event->end_at)
                    – {{ optional($event->end_at)->format('H:i') }}
                  @endif
                </div>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl bg-slate-100 border flex items-center justify-center">📍</div>
              <div class="min-w-0">
                <div class="font-medium">{{ $event->location ?: 'Online / TBA' }}</div>
                @if($org)
                  <div class="text-slate-600 truncate">{{ $org->name }}</div>
                @elseif($creator)
                  <div class="text-slate-600 truncate">{{ $nick ?: $creator->name }}</div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <div class="md:sticky md:top-20">
          <div class="app-card rounded-2xl p-4">
            <div class="text-xs text-slate-600 mb-2">Registration</div>
            <div class="font-medium mb-3">Pilih tiket anda</div>

            @if($ticketsCol->isEmpty())
              <div class="text-sm text-slate-600">Tiada tiket tersedia.</div>
            @else
              <form method="GET" action="{{ route('events.join', $event->slug) }}" class="space-y-2">
                @foreach($ticketsCol as $t)
                  @php($type = $t->type)
                  @php($qty = (int) $t->quantity)
                  @php($sold = (int) ($t->sold ?? 0))
                  @php($remain = max(0, $qty - $sold))
                  @php($isSoldOut = $remain <= 0)
                  @php($price = (float) ($t->price ?? 0))
                  @php($isFree = $type === 'free' || $price <= 0)
                  <label class="block">
                    <input
                      type="radio"
                      name="ticket_id"
                      value="{{ $t->id }}"
                      class="sr-only peer"
                      @checked((int)$t->id === (int)$selectedTicketId)
                      @disabled($isSoldOut)
                    />
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5 peer-checked:border-[color:var(--accent)] peer-checked:bg-black/5 {{ $isSoldOut ? 'opacity-50' : 'hover:bg-black/5' }}">
                      <div class="min-w-0">
                        <div class="text-sm font-medium truncate">{{ $t->name }}</div>
                        <div class="text-xs text-slate-600">
                          @if($isSoldOut)
                            Sold out
                          @else
                            Baki: {{ $remain }}
                          @endif
                        </div>
                      </div>
                      <div class="shrink-0 text-sm font-semibold">
                        @if($isFree)
                          Free
                        @else
                          RM {{ number_format($price, 2) }}
                        @endif
                      </div>
                    </div>
                  </label>
                @endforeach

                <div class="pt-3">
                  @php($canJoin = $selectedTicket && max(0, ((int)$selectedTicket->quantity) - ((int)($selectedTicket->sold ?? 0))) > 0)
                  <button type="submit" class="w-full px-4 py-2.5 rounded btn-accent text-sm font-medium" @disabled(!$canJoin)>
                    {{ Auth::check() ? 'Sertai Acara' : 'Log Masuk untuk Sertai' }}
                  </button>
                  <div class="text-xs text-slate-600 mt-2">1 pengguna = 1 tiket.</div>
                </div>
              </form>
            @endif
          </div>
        </div>
      </div>

      <div class="app-card rounded-2xl p-6">
        <div class="font-semibold mb-3">About Event</div>
        <div class="prose prose-slate max-w-none">
          {!! $event->description !!}
        </div>
      </div>

      <div class="app-card rounded-2xl p-6">
        <div class="font-semibold mb-3">Location</div>
        <div class="text-sm text-slate-700 mb-4">{{ $event->location ?: 'Online / TBA' }}</div>
        @if($mapQuery)
          <div class="overflow-hidden rounded-2xl border">
            <iframe
              class="w-full h-64 sm:h-80"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q={{ $mapQuery }}&output=embed"
            ></iframe>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
