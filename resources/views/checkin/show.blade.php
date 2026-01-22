@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-10 px-4">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Imbasan Tiket</h1>
            <p class="text-slate-500">{{ $attendee->event->title ?? 'Acara' }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border">
            <!-- Status Header -->
            <div class="p-6 text-center {{ $status === 'valid' ? 'bg-emerald-50' : ($status === 'used' ? 'bg-amber-50' : 'bg-red-50') }}">
                @if($status === 'valid')
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h2 class="text-xl font-bold text-emerald-700">TIKET SAH</h2>
                    <p class="text-emerald-600 text-sm">Boleh masuk</p>
                @elseif($status === 'used')
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h2 class="text-xl font-bold text-amber-700">SUDAH DIGUNAKAN</h2>
                    <p class="text-amber-600 text-sm">Tiket ini telah diimbas pada {{ optional($attendee->checked_in_at)->format('h:i A, d M') }}</p>
                @else
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </div>
                    <h2 class="text-xl font-bold text-red-700">TIDAK SAH</h2>
                    <p class="text-red-600 text-sm">Tiket tidak ditemui atau tidak sah.</p>
                @endif
            </div>

            <!-- Attendee Details -->
            <div class="p-6 border-t border-slate-100 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 uppercase">Nama Peserta</label>
                    <div class="text-lg font-medium text-slate-900">{{ $attendee->name }}</div>
                    <div class="text-sm text-slate-500">{{ $attendee->email }}</div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase">Jenis Tiket</label>
                        <div class="font-medium">{{ $attendee->ticket->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase">ID Order</label>
                        <div class="font-medium">#{{ $attendee->order_id }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 bg-slate-50 border-t border-slate-100">
                @if($status === 'valid')
                    <form method="post" action="{{ route('checkin.scan', ['code' => $attendee->qr_code]) }}">
                        @csrf
                        @if(request('return_to'))
                            <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                        @endif
                        <button type="submit" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold shadow-sm text-lg mb-3">
                            SAHKAN KEHADIRAN
                        </button>
                    </form>
                @endif

                @if(request('return_to'))
                    <a href="{{ request('return_to') }}" class="block w-full py-3 px-4 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold text-center hover:bg-slate-50">
                        {{ $status === 'valid' ? 'Batal / Scan Lain' : 'Imbas Seterusnya' }}
                    </a>
                @else
                    <a href="/dashboard" class="block w-full py-3 px-4 bg-white border border-slate-300 text-slate-700 rounded-lg font-semibold text-center hover:bg-slate-50">
                        Kembali ke Dashboard
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
