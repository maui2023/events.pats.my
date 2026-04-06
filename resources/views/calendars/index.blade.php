@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-start justify-between gap-4 mb-6">
        <div class="min-w-0">
            <div class="text-sm text-slate-600">Calendars</div>
            <h1 class="text-2xl sm:text-3xl font-semibold">Open Calendars</h1>
            <div class="text-sm text-slate-600 mt-1">Hanya organisasi terbuka dipaparkan.</div>
        </div>
    </div>

    <form method="GET" action="{{ route('calendars.index') }}" class="mb-6 flex items-center gap-2">
        <input type="text" name="q" value="{{ $search }}" class="flex-1 border rounded px-3 h-10 text-sm" placeholder="Cari organisasi..." />
        <button class="px-3 h-10 rounded btn-accent text-sm">Search</button>
    </form>

    @if($organizations->isEmpty())
        <div class="app-card rounded-xl p-6 text-sm text-slate-600">Tiada organisasi terbuka.</div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($organizations as $org)
                <div class="app-card rounded-xl p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold truncate">{{ $org->name }}</div>
                        </div>
                        <a href="{{ route('calendars.show', ['organization' => $org->id]) }}" class="btn-accent px-3 py-1.5 rounded text-xs shrink-0">Open Calendar</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $organizations->links() }}
        </div>
    @endif
</div>
@endsection

