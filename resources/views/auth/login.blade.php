@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="flex items-center justify-center mb-6">
            <div class="text-2xl font-semibold leading-none">
                <span class="text-[#e50914]">Be</span><span class="logo-spoke">Spoke</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Kata Laluan</label>
                    <input type="password" name="password" required class="w-full border rounded px-3 py-2" />
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remember" class="border" />
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600">Lupa kata laluan?</a>
                </div>
                <div class="flex items-center justify-between">
                    <a href="{{ route('register') }}" class="px-4 py-2 border rounded text-sm">Daftar / Register</a>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded">LOG MASUK</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
