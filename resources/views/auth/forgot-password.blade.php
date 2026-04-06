@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="flex items-center justify-center mb-6">
            <div class="text-2xl font-semibold leading-none">
                <span class="text-[#e50914]">Be</span><span class="logo-spoke">Spoke</span>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 text-green-700 px-3 py-2 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="text-sm text-slate-600 mb-4">
                Masukkan email anda. Kami akan hantar pautan untuk reset kata laluan.
            </div>
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div class="flex items-center justify-between">
                    <a href="{{ route('login') }}" class="px-4 py-2 border rounded text-sm">Kembali</a>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded">Hantar Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

