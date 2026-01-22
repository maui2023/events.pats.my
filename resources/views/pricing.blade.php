@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-semibold mb-2">Pelan Langganan</h1>
    <p class="text-slate-600 mb-4">Pembayaran langganan menggunakan SecurePay. Pro/Free menggunakan akaun sistem BeSpoke EMS; VIP menggunakan gateway sendiri (akan datang).</p>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border rounded-xl p-6 bg-white">
            <h2 class="text-xl font-semibold mb-2">Free</h2>
            <p class="text-slate-600 mb-4">Pelan percuma dengan ciri asas.</p>
            <ul class="space-y-2 text-slate-700 list-disc ml-5">
                <li>Tiada organisasi</li>
                <li>1 acara sebulan</li>
                <li>Tempoh Acara: 3 hari</li>
                <li>Had 20 peserta (ikut kuota tiket)</li>
                <li>Tambahan RM 1/peserta</li>
                <li>Tiada sijil kehadiran (opsyen RM 10)</li>
                <li>Gateway: SecurePay (akaun sistem BeSpoke EMS)</li>
                <li>Pembayaran: 5–10 hari bekerja selepas tamat</li>
                <li>Komisen tiket: 10% daripada harga tiket</li>
            </ul>
        </div>

        <div class="border rounded-xl p-6 bg-white ring-2 ring-blue-400">
            <h2 class="text-xl font-semibold mb-2">Pro (RM 30/sebulan)</h2>
            <p class="text-slate-600 mb-4">Untuk penganjur yang serius.</p>
            <ul class="space-y-2 text-slate-700 list-disc ml-5">
                <li>Organisasi: 1</li>
                <li>15 acara sebulan</li>
                <li>Tempoh Acara: 1 bulan</li>
                <li>Had 200 peserta</li>
                <li>Tambahan RM 1/peserta</li>
                <li>Sijil kehadiran digital</li>
                <li>Gateway: SecurePay (akaun sistem BeSpoke EMS)</li>
                <li>Pembayaran: 3 hari bekerja selepas tamat</li>
                <li>Komisen tiket: 5% daripada harga tiket</li>
            </ul>
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-slate-600 mb-2">Jumlah bayaran: <span class="font-semibold">RM 32.00</span> (RM 30 + RM 2 caj perkhidmatan SecurePay).</p>
                <form method="POST" action="{{ route('pricing.pro.pay') }}">
                    @csrf
                    <button class="w-full inline-flex items-center justify-center px-4 py-2 rounded bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        Naik Taraf ke Pro dengan SecurePay
                    </button>
                </form>
            </div>
        </div>

        <div class="border rounded-xl p-6 bg-white ring-2 ring-amber-400">
            <h2 class="text-xl font-semibold mb-2">VIP (Akan Datang)</h2>
            <p class="text-slate-600 mb-4">Untuk agensi dan perusahaan.</p>
            <ul class="space-y-2 text-slate-700 list-disc ml-5">
                <li>Organisasi: ?</li>
                <li>Acara: ?</li>
                <li>Tempoh Acara: ?</li>
                <li>Peserta: tanpa had</li>
                <li>Sijil kehadiran digital</li>
                <li>Sijil khas digital</li>
                <li>Gateway: Sendiri (Toyibpay, Bayarcash)</li>
                <li>Pembayaran: Payment Gateway anda sendiri</li>
                <li>Komisen tiket: 3% daripada harga tiket</li>
            </ul>
        </div>
    </div>
</div>
@endsection
