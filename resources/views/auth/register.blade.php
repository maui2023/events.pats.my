@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="flex items-center justify-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-green-700 font-bold">RG</div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">Nama / Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Emel / Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Kata Laluan / Password</label>
                    <input type="password" name="password" required class="w-full border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Sahkan Kata Laluan / Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2" />
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">Sudah ada akaun? <a href="{{ route('login') }}" class="text-blue-600">Log Masuk / Login</a></div>
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Daftar / Register</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
