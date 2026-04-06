@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
            <p class="text-slate-600">Urus Peserta & Pesanan</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.show', $event->slug) }}" class="px-4 py-2 text-sm bg-slate-100 rounded hover:bg-slate-200">Lihat Acara</a>
            <a href="{{ route('events.scan', $event->slug) }}" class="px-4 py-2 text-sm bg-slate-100 rounded hover:bg-slate-200">Scan QR</a>
            <a href="{{ route('events.edit', $event->slug) }}" class="px-4 py-2 text-sm bg-slate-100 rounded hover:bg-slate-200">Edit</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-700 px-3 py-2 text-sm">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $eventTimezone = method_exists($event, 'timezoneName') ? $event->timezoneName() : config('app.timezone', 'UTC');
    @endphp

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau emel..." class="w-full border rounded px-3 py-2">
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b">
                    <tr>
                        <th class="px-4 py-3 whitespace-nowrap">Peserta / Pembeli</th>
                        <th class="px-4 py-3 whitespace-nowrap">Tiket</th>
                        <th class="px-4 py-3 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 whitespace-nowrap">Hadir</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($orders as $order)
                        @php
                            $hasAttendees = $order->attendees->isNotEmpty();
                        @endphp
                        
                        @if($hasAttendees)
                            @foreach ($order->attendees as $attendee)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $attendee->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $attendee->email }}</div>
                                    @if($order->buyer_email !== $attendee->email)
                                        <div class="text-xs text-slate-400 mt-1">Buyer: {{ $order->buyer_name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ optional($order->ticket)->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($order->status == 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Paid</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($attendee->checked_in_at)
                                        <span class="text-green-600 font-medium">✓ {{ $attendee->checked_in_at->copy()->timezone($eventTimezone)->format('H:i') }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2 items-center">
                                        @if($attendee->checked_in_at)
                                            @php
                                                $certUrl = route('certificates.show', $attendee->qr_code);
                                                $waText = "Tahniah! Berikut adalah sijil penyertaan anda untuk " . $event->title . ": " . $certUrl;
                                                $waLink = "https://wa.me/?text=" . urlencode($waText);
                                            @endphp
                                            <a href="{{ $certUrl }}" target="_blank" class="px-3 py-1 text-xs rounded border border-blue-200 text-blue-600 hover:bg-blue-50">Sijil</a>
                                            <a href="{{ $waLink }}" target="_blank" class="px-3 py-1 text-xs rounded border border-green-200 text-green-600 hover:bg-green-50 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                            </a>
                                        @endif
                                        <form action="{{ route('events.orders.destroy', ['slug' => $event->slug, 'order' => $order->id]) }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu memadam pesanan ini? Tiket akan dikembalikan ke dalam stok jualan.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 text-xs rounded border border-red-200 text-red-600 hover:bg-red-50">Padam</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            {{-- Fallback for orders without attendees --}}
                             <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $order->buyer_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $order->buyer_email }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ optional($order->ticket)->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                     @if($order->status == 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Paid</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">-</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <form action="{{ route('events.orders.destroy', ['slug' => $event->slug, 'order' => $order->id]) }}" method="POST" onsubmit="return confirm('Adakah anda pasti mahu memadam pesanan ini? Tiket akan dikembalikan ke dalam stok jualan.');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs rounded border border-red-200 text-red-600 hover:bg-red-50">Padam</button>
                                    </form>
                                </td>
                             </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tiada peserta dijumpai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
