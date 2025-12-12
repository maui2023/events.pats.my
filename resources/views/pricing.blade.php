@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-semibold mb-2">Pelan Langganan</h1>
    <p class="text-slate-600 mb-6">Pembayaran melalui Toyibpay dan Bayarcash (Pro/Free: akaun sistem BeSpoke EMS; VIP: gateway sendiri). Garis masa pembayaran bergantung kepada tier.</p>

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
                <li>Gateway: Toyibpay/Bayarcash BeSpoke@Sistem</li>
                <li>Pembayaran: 5–10 hari bekerja selepas tamat</li>
                <li>Komisen tiket: 5% daripada harga tiket</li>
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
                <li>Gateway: Toyibpay/Bayarcash BeSpoke@Sistem</li>
                <li>Pembayaran: 3 hari bekerja selepas tamat</li>
                <li>Komisen tiket: 3% daripada harga tiket</li>
            </ul>
        </div>

        <div class="border rounded-xl p-6 bg-white ring-2 ring-amber-400">
            <h2 class="text-xl font-semibold mb-2">VIP (RM 250/setahun)</h2>
            <p class="text-slate-600 mb-4">Untuk agensi dan perusahaan.</p>
            <ul class="space-y-2 text-slate-700 list-disc ml-5">
                <li>Organisasi: tanpa had</li>
                <li>Acara: tanpa had</li>
                <li>Tempoh Acara: tanpa had</li>
                <li>Peserta: tanpa had</li>
                <li>Sijil kehadiran digital</li>
                <li>Sijil khas digital</li>
                <li>Gateway: Sendiri (Toyibpay, Bayarcash)</li>
                <li>Pembayaran: Payment Gateway anda sendiri</li>
                <li>Komisen tiket: 1% daripada harga tiket</li>
            </ul>
        </div>
    </div>
</div>
@endsection

