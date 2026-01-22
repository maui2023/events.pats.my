@extends('layouts.app')

@section('content')
@php($freeTickets = $tickets->where('type', 'free'))
@php($paidTickets = $tickets->whereIn('type', ['paid','sponsor']))
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="md:col-span-2 border rounded-xl bg-white overflow-hidden">
      @php($img = $event->banner_path ? (\Illuminate\Support\Str::startsWith($event->banner_path, ['http://','https://']) ? $event->banner_path : asset($event->banner_path)) : null)
      @if(!empty($img))
        <img src="{{ $img }}" alt="{{ $event->title }}" class="w-full h-64 object-cover">
      @else
        <div class="w-full h-64 bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
      @endif
      <div class="p-6">
        <h1 class="text-2xl font-semibold mb-2">{{ $event->title }}</h1>
        <div class="text-slate-700 mb-4">{{ optional($event->start_at)->format('d M Y, h:i A') }}</div>
        <div class="prose prose-slate max-w-none">
          {!! $event->description !!}
        </div>
      </div>
    </div>
    <div class="space-y-4">
      @if(session('order_created'))
        <div class="border rounded-xl bg-green-50 text-green-700 p-4">Pesanan tiket dicipta.</div>
      @endif
      <div class="border rounded-xl bg-white p-4">
        <div class="font-medium mb-2">Butiran</div>
        <div class="text-sm text-slate-700">Lokasi: {{ $event->location ?? '—' }}</div>
        <div class="text-sm text-slate-700">Mula: {{ optional($event->start_at)->format('d M Y, h:i A') ?? '—' }}</div>
        <div class="text-sm text-slate-700">Tamat: {{ optional($event->end_at)->format('d M Y, h:i A') ?? '—' }}</div>
        @php($org = $event->organization)
        @if($org)
          <div class="text-sm text-slate-700">Organisasi: {{ $org->name }}</div>
        @endif
        @php($creator = $event->organizer)
        @php($nick = optional($creator?->profile)->nickname)
        @if($creator)
          <div class="text-sm text-slate-700">Pencipta: {{ $nick ?: $creator->name }}</div>
        @endif
      </div>
      <div class="border rounded-xl bg-white p-4">
        <div class="font-medium mb-2">Penyertaan</div>
        @php($ticketsCol = $tickets ?? collect())
        @php($hasFree = $ticketsCol->where('type','free')->isNotEmpty())
        @php($hasSponsor = $ticketsCol->where('type','sponsor')->isNotEmpty())
        @php($label = $hasFree ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar'))
        @php($labelClass = $label==='Berbayar' ? 'bg-amber-100 text-amber-800' : ($label==='Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'))
        @php($minPaid = $ticketsCol->where('type','paid')->pluck('price')->filter()->min())
        @php($minSponsorDue = $ticketsCol->where('type','sponsor')->pluck('price')->filter()->min())
        @php($zeroSponsor = isset($minSponsorDue) && ((float)$minSponsorDue) <= 0)
        @php($label = ($hasFree || $zeroSponsor) ? 'Percuma' : ($hasSponsor ? 'Sponsor' : 'Berbayar'))
        @php($labelClass = $label==='Berbayar' ? 'bg-amber-100 text-amber-800' : ($label==='Sponsor' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'))
        @php($minSponsorBase = $ticketsCol->where('type','sponsor')->pluck('base_price')->filter()->min())
        @php($totalQty = (int) $ticketsCol->sum('quantity'))
        @php($totalSold = (int) $ticketsCol->sum(function($t){ return (int)($t->sold ?? 0); }))
        @php($remain = max(0, $totalQty - $totalSold))
        <div class="space-y-1 text-sm text-slate-700 mb-3">
          <div>Jenis: <span class="inline-flex items-center px-2 py-0.5 rounded border {{ $labelClass }}">{{ $label }}</span></div>
          <div>Harga Tiket:
            @if($label==='Berbayar')
              <span class="font-medium">{{ isset($minPaid) ? ('RM '.number_format($minPaid,2)) : '—' }}</span>
            @elseif($label==='Sponsor')
              @if(isset($minSponsorBase))
                <span class="line-through font-bold text-red-600 mr-2">RM {{ number_format($minSponsorBase,2) }}</span>
              @endif
              <span class="font-medium">{{ isset($minSponsorDue) ? ('RM '.number_format($minSponsorDue,2)) : '—' }}</span>
            @else
              <span class="font-medium">Percuma</span>
            @endif
          </div>
          <div>Baki Tiket: <span class="font-medium">{{ $remain }}/{{ $totalQty }}</span></div>
        </div>
        <p class="text-sm text-slate-600 mb-3">1 pengguna = 1 tiket. Tekan butang di bawah untuk sertai. Jika belum log masuk, anda akan diarahkan ke halaman login terlebih dahulu.</p>
        @php($isSoldOut = ($remain <= 0))
        <div class="flex justify-end">
          @if($isSoldOut)
            <button class="px-4 py-2 rounded bg-slate-300 text-white cursor-not-allowed" disabled>Sold Out</button>
          @else
            <a href="{{ route('events.join', $event->slug) }}" class="px-4 py-2 rounded bg-blue-600 text-white">Sertai</a>
          @endif
        </div>
      </div>
    </div>
  </div>
  <div>
    <a href="{{ route('events.discover') }}" class="text-sm text-blue-600">← Kembali ke senarai acara</a>
  </div>
</div>
@endsection
