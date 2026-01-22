@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10">
  <div class="border rounded-xl bg-white p-6">
    <h1 class="text-2xl font-semibold mb-3">Checkout</h1>
    <div class="text-slate-700 mb-4">{{ $event->title }}</div>
    <div class="space-y-2 text-sm text-slate-700">
      <div><span class="text-slate-500">Tiket:</span> {{ $ticket->name }}</div>
      <div><span class="text-slate-500">Harga:</span> RM {{ number_format((float)$ticket->price, 2) }}</div>
      <div><span class="text-slate-500">Kuantiti:</span> {{ $order->quantity }}</div>
      <div><span class="text-slate-500">Caj perkhidmatan SecurePay:</span> RM 2.00</div>
      <div><span class="text-slate-500">Jumlah:</span> RM {{ number_format((float)$order->total_amount, 2) }}</div>
      <div><span class="text-slate-500">Status:</span> {{ strtoupper($order->status) }}</div>
    </div>
    @if(strtoupper($order->status) === 'PAID' && !empty($attendee))
      @php($url = route('checkin.show', $attendee->qr_code))
      @php($png = base64_encode(QrCode::format('png')->size(240)->margin(1)->generate($url)))
      <div class="mt-6 border rounded-xl bg-white p-6">
        <h2 class="text-lg font-medium mb-2">Pass Kehadiran</h2>
        <div class="flex items-center gap-6">
          <img src="data:image/png;base64,{{ $png }}" alt="QR" class="w-40 h-40 border rounded" />
          <div class="text-sm text-slate-700">
            <div><span class="text-slate-500">Nama:</span> {{ $order->buyer_name }}</div>
            <div><span class="text-slate-500">Email:</span> {{ $order->buyer_email }}</div>
            <div><span class="text-slate-500">Kod:</span> {{ $attendee->qr_code }}</div>
          </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-3">
          <a href="{{ route('events.qr', [$event->slug, $attendee->qr_code]) }}" class="px-4 py-2 rounded border">Paparkan QR Penuh</a>
          <a href="{{ route('orders.qr.download', $order->id) }}" class="px-4 py-2 rounded border">Muat Turun QR</a>
          @if(!empty($calendarUrl))
            <a href="{{ $calendarUrl }}" target="_blank" class="px-4 py-2 rounded border">Masukkan ke Calendar</a>
          @endif
          <button onclick="window.print()" class="px-4 py-2 rounded bg-blue-600 text-white">Cetak</button>
        </div>
      </div>
    @else
      <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('events.show', $event->slug) }}" class="px-4 py-2 rounded border">Kembali</a>
        <form method="POST" action="{{ route('orders.cancel', $order->id) }}">
          @csrf
          <button class="px-4 py-2 rounded border">Batal & Buang</button>
        </form>
        <form method="POST" action="{{ route('orders.pay', $order->id) }}">
          @csrf
          <button class="px-4 py-2 rounded bg-emerald-600 text-white">Teruskan Pembayaran (SecurePay)</button>
        </form>
      </div>
    @endif
  </div>
</div>
@endsection
