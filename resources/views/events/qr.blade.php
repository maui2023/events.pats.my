@extends('layouts.app')

@section('content')
@php($url = route('checkin.show', $attendee->qr_code))
<div class="max-w-md mx-auto px-4 sm:px-6 py-10">
  <div class="border rounded-xl bg-white p-6 text-center">
    <h2 class="text-xl font-semibold mb-2">QR Kehadiran</h2>
    <div class="text-slate-700 mb-4">{{ $attendee->name }} ({{ $attendee->email }})</div>
    <div class="flex items-center justify-center mb-4">
      {!! QrCode::size(220)->generate($url) !!}
    </div>
    <div class="text-sm text-slate-600">Kod: {{ $attendee->qr_code }}</div>
  </div>
  <div class="mt-4 text-center">
    <a href="/dashboard" class="text-sm text-blue-600">← Kembali ke Dashboard</a>
  </div>
  </div>
@endsection
