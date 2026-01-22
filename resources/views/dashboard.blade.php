@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <a href="/create" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm">{{ __('ui.cta.create') }}</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border rounded-xl bg-white p-6 md:order-last">
            <h2 class="text-lg font-medium mb-3">Profil</h2>
            @php($handle = \Illuminate\Support\Str::before($user->email, '@'))
            <div class="flex items-center gap-3 mb-4">
                @if(!empty($profile?->avatar))
                    <img src="{{ asset($profile->avatar) }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full border object-cover" />
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=96" alt="{{ $user->name }}" class="w-16 h-16 rounded-full border" />
                @endif
                <div>
                    <div class="font-medium">{{ $user->name }}</div>
                    <div class="text-slate-600 text-sm">{{ $handle }}</div>
                </div>
            </div>
            <div class="space-y-2 mb-4 text-sm">
                <div>Wallet Label: <span class="font-medium">{{ $wallet->wallet_id ?? '—' }}</span></div>
                <div>Wallet Address: <span class="font-mono break-all">{{ $wallet->wallet_address ?? '—' }}</span></div>
                <div>Crypto Credit: <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700"> {{ number_format($wallet->credit_balance ?? 0, 4) }} SEN</span></div>
            </div>
            <div class="mb-3">
                <div class="text-sm text-slate-700 font-medium mb-1">QR Name Card</div>
                @php(
                    $vlines = [
                        'BEGIN:VCARD',
                        'VERSION:3.0',
                        'FN:'.$user->name,
                        'EMAIL:'.$user->email,
                    ]
                )
                @php(
                    (!empty($profile) && !empty($profile->phone)) ? $vlines[] = 'TEL;TYPE=CELL:'.$profile->phone : null
                )
                @php(
                    (!empty($profile) && !empty($profile->company)) ? $vlines[] = 'ORG:'.$profile->company : null
                )
                @php(
                    (!empty($profile) && !empty($profile->position)) ? $vlines[] = 'TITLE:'.$profile->position : null
                )
                @php(
                    ($wallet && $wallet->wallet_address) ? $vlines[] = 'NOTE:'.($wallet->wallet_address) : null
                )
                @php($vlines[] = 'END:VCARD')
                @php($vcard = implode("\n", $vlines))
                {!! QrCode::size(220)->generate($vcard) !!}
            </div>
            <div>
                <a href="/profile" class="px-3 py-1.5 rounded border text-sm">Edit Profil</a>
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            <div class="border rounded-xl bg-white p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-medium">Acara Saya</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-sm">{{ $events->count() }}</span>
                </div>
                @if ($events->isEmpty())
                    <p class="text-slate-600">Belum ada acara. Mulakan dengan butang "{{ __('ui.cta.create') }}".</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($events as $event)
                            <li class="flex items-center justify-between">
                                <div>
                                    <a href="/events/{{ $event->slug }}" class="font-medium">{{ $event->title }}</a>
                                    <div class="text-sm text-slate-600">{{ optional($event->start_at)->format('d M Y, h:i A') }}</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('events.scan', $event->slug) }}" class="text-slate-600 hover:text-slate-900 text-sm flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-qr-code"><path d="M17 12v4a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-4"/><path d="M17 12a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"/><path d="M5 7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7Z"/><line x1="12" x2="12" y1="7" y2="17"/></svg>
                                        Scan
                                    </a>
                                    <a href="{{ route('events.edit', $event->slug) }}" class="text-blue-600 text-sm">Edit</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border rounded-xl bg-white p-6">
                <h2 class="text-lg font-medium mb-3">Tiket Saya (RSVP)</h2>
                @if($attendees->isEmpty())
                    <p class="text-slate-600">Belum ada RSVP.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($attendees as $att)
                            <li class="flex items-center justify-between border-b pb-2 last:border-0 last:pb-0">
                                <div>
                                    <a href="/events/{{ $att->event->slug }}" class="font-medium hover:text-blue-600">{{ $att->event->title }}</a>
                                    <div class="text-sm text-slate-600">{{ optional($att->event->start_at)->format('d M Y, h:i A') }}</div>
                                    <div class="text-xs text-slate-500">Attendee: {{ $att->name }}</div>
                                </div>
                                <div>
                                    <a href="{{ route('orders.qr.download', $att->order_id) }}" class="px-3 py-1.5 rounded bg-slate-100 text-slate-700 text-sm hover:bg-slate-200">
                                        Tiket QR
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border rounded-xl bg-white p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-medium">Purchased Tickets</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-sm">{{ $orders->count() }}</span>
                </div>
                @if ($orders->isEmpty())
                    <p class="text-slate-600">Tiada tiket setakat ini.</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($orders as $o)
                            @php($ev = \App\Models\Event::find($o->event_id))
                            <li class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('orders.checkout', $o->id) }}" class="font-medium">{{ $ev?->title ?? 'Acara' }}</a>
                                    <div class="text-sm text-slate-600">Status: {{ strtoupper($o->status) }}, Jumlah: RM {{ number_format((float)$o->total_amount, 2) }}</div>
                                </div>
                                @if(strtoupper($o->status) === 'PAID')
                                    <a href="{{ route('orders.qr.download', $o->id) }}" class="text-blue-600 text-sm">Muat Turun QR</a>
                                @else
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('orders.checkout', $o->id) }}" class="text-emerald-600 text-sm">Teruskan Pembayaran</a>
                                        <form method="POST" action="{{ route('orders.cancel', $o->id) }}">
                                            @csrf
                                            <button class="text-slate-600 text-sm">Buang</button>
                                        </form>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border rounded-xl bg-white p-6">
                <h2 class="text-lg font-medium mb-3">Past Events Attended</h2>
                <p class="text-slate-600">Belum menghadiri sebarang acara.</p>
            </div>
        </div>
    </div>
</div>
@endsection
