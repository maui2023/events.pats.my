@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
  <div class="border rounded-xl bg-white p-6">
    <h2 class="text-lg font-medium mb-3">Urus Organisasi</h2>
    @if(session('status'))
      <div class="mb-3 p-2 rounded bg-green-50 text-green-700 text-sm">{{ session('status') }}</div>
    @endif
    @error('organization')
      <div class="mb-3 p-2 rounded bg-red-50 text-red-700 text-sm">{{ $message }}</div>
    @enderror

    @forelse($organizations as $org)
      <div class="border rounded p-4 mb-4">
        <div class="flex items-center justify-between mb-2">
          <div class="font-medium">{{ $org->name }}</div>
          <span class="text-xs px-2 py-0.5 rounded {{ $org->status==='approved' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($org->status) }}</span>
        </div>
        <div class="text-sm mb-2">Permohonan keahlian menunggu kelulusan:</div>
        <table class="w-full text-sm border rounded">
          <thead>
            <tr class="text-left">
              <th class="py-2 px-3">Nama Pengguna</th>
              <th class="py-2 px-3">Email</th>
              <th class="py-2 px-3">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            @forelse(($pendingByOrg[$org->id] ?? collect()) as $member)
              <tr>
                <td class="py-2 px-3">{{ $member->name }}</td>
                <td class="py-2 px-3">{{ $member->email }}</td>
                <td class="py-2 px-3">
                  <form method="POST" action="{{ route('organizations.members.approve', [$org->id, $member->id]) }}" class="inline">
                    @csrf
                    <button class="px-2 py-1 rounded bg-green-600 text-white text-xs">Lulus</button>
                  </form>
                  <form method="POST" action="{{ route('organizations.members.reject', [$org->id, $member->id]) }}" class="inline ml-2">
                    @csrf
                    <button class="px-2 py-1 rounded bg-red-600 text-white text-xs">Tolak</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td class="py-2 px-3" colspan="3">Tiada permohonan baharu.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @empty
      <div class="text-sm">Anda belum mempunyai organisasi untuk diuruskan.</div>
    @endforelse

    <a href="{{ route('profile.show') }}" class="px-3 py-1.5 rounded border text-sm">Kembali ke Profil</a>
  </div>
</div>
@endsection

