@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-4 py-12">
    <div class="bg-white border rounded-xl p-6 shadow-sm">
        <h1 class="text-2xl font-semibold mb-4">Daftar Peserta Lain</h1>
        <p class="text-slate-600 mb-6">
            Anda telah mendaftar untuk acara <strong>{{ $event->title }}</strong>. 
            Gunakan borang ini untuk mendaftarkan rakan atau peserta lain.
        </p>

        <form action="{{ route('events.rsvp', $event->slug) }}" method="POST">
            @csrf
            
            @php($ticket = $event->tickets()->orderBy('price')->first())
            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
            <input type="hidden" name="quantity" value="1">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Peserta</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" placeholder="Nama Penuh" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Emel Peserta</label>
                    <input type="email" name="email" class="w-full border rounded px-3 py-2" placeholder="user@example.com" required>
                    <p class="text-xs text-slate-500 mt-1">Tiket akan dihantar ke emel ini.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded font-medium hover:bg-blue-700">
                        Daftar Peserta
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-4 text-center">
            <a href="{{ route('events.show', $event->slug) }}" class="text-sm text-slate-600 hover:underline">Kembali</a>
        </div>
    </div>
</div>
@endsection
