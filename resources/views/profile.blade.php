@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border rounded-xl bg-white p-6">
            <h2 class="text-lg font-medium mb-3">Profil</h2>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="flex items-center gap-3 mb-3">
                    @if(!empty($profile->avatar))
                        <img src="{{ asset($profile->avatar) }}" class="w-16 h-16 rounded-full border object-cover" />
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=96" class="w-16 h-16 rounded-full border" />
                    @endif
                    <div>
                        <div class="font-medium">{{ $user->name }}</div>
                        <div class="text-slate-600 text-sm">{{ $profile->position ?? '—' }}</div>
                    </div>
                </div>
                <div>
                    <label class="text-sm block mb-1">Nama Penuh</label>
                    <input type="text" name="name" class="w-full border rounded px-3 py-2" value="{{ $user->name }}" />
                </div>
                <div>
                    <label class="text-sm block mb-1">Nickname</label>
                    <input type="text" name="nickname" class="w-full border rounded px-3 py-2" value="{{ $profile->nickname }}" />
                </div>
                <div>
                    <label class="text-sm block mb-1">Email</label>
                    <input type="email" class="w-full border rounded px-3 py-2" value="{{ $user->email }}" readonly />
                </div>
                <div>
                    <label class="text-sm block mb-1">Telefon</label>
                    <input type="text" name="phone" class="w-full border rounded px-3 py-2" value="{{ $profile->phone }}" />
                </div>
                <div>
                    <label class="text-sm block mb-1">Syarikat</label>
                    <input type="text" name="company" class="w-full border rounded px-3 py-2" value="{{ $profile->company }}" />
                </div>
                <div>
                    <label class="text-sm block mb-1">Jawatan</label>
                    <input type="text" name="position" class="w-full border rounded px-3 py-2" value="{{ $profile->position }}" />
                </div>
                <div>
                    <label class="text-sm block mb-1">Muat naik avatar</label>
                    <div class="flex items-center border rounded overflow-hidden">
                        <label for="avatarInput" class="px-3 py-2 bg-slate-100 text-slate-700 cursor-pointer">Choose File</label>
                        <span id="avatarFileName" class="px-3 py-2 text-slate-500 flex-1">No file chosen</span>
                    </div>
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden" />
                </div>
                <div class="flex gap-2 mt-4">
                    <button class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm">Simpan</button>
                    <a href="/dashboard" class="px-3 py-1.5 rounded border text-sm">Kembali</a>
                </div>
            </form>

            <div class="mt-2">
                <label class="text-sm block mb-1">Wallet Label</label>
                @if($wallet && $wallet->wallet_id)
                    <div class="flex items-center border rounded overflow-hidden">
                        <span class="px-3 py-2 bg-slate-100 text-slate-700 flex-1">{{ $wallet->wallet_id }}</span>
                        <button type="button" class="px-3 py-2 text-slate-400 bg-slate-100 border-l" disabled>Set</button>
                    </div>
                @else
                    <form method="POST" action="{{ route('profile.wallet.set') }}" class="flex items-center border rounded overflow-hidden">
                        @csrf
                        <input type="text" name="wallet_id" value="" class="flex-1 px-3 py-2" placeholder="Contoh: Semasa_id_4" />
                        <button type="submit" class="px-3 py-2 bg-slate-100 border-l">Set</button>
                    </form>
                    @error('wallet_id')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                @endif
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            <div class="border rounded-xl bg-white p-6">
                <h2 class="text-lg font-medium mb-3">Organisasi</h2>
                <div class="mb-3 flex items-center justify-between">
                    <div class="text-sm">Tier: <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100">{{ $profile->tier ?? 'FREE' }}</span></div>
                    @if(in_array($profile->tier, ['PRO','VIP']))
                        <a href="{{ route('organizations.manage') }}" class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Urus Permohonan</a>
                    @endif
                </div>
                <table class="w-full text-sm border rounded">
                    <thead>
                        <tr class="text-left">
                            <th class="py-2 px-3">Nama</th>
                        <th class="py-2 px-3">Status Keahlian</th>
                            <th class="py-2 px-3">Dicipta Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myOrganizations as $org)
                            <tr>
                                <td class="py-2 px-3">{{ $org->name }}</td>
                            <td class="py-2 px-3">{{ ucfirst($org->pivot->status ?? 'member') }}</td>
                                <td class="py-2 px-3">{{ optional($org->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-2 px-3" colspan="3">Tiada organisasi dicipta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if(!in_array($profile->tier, ['PRO','VIP']))
                    <div class="mt-3 p-3 rounded bg-blue-50 text-blue-700 text-sm flex items-center justify-between">
                        <span>Hanya pengguna Pro & VIP boleh cipta organisasi.</span>
                        <a href="{{ route('pricing') }}" class="px-3 py-1.5 rounded border text-sm">Langgan</a>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <form method="POST" action="{{ route('profile.organization.create') }}" class="border rounded p-4">
                            @csrf
                            <div class="font-medium mb-2">Cipta Organisasi</div>
                            <input type="text" name="name" class="w-full border rounded px-3 py-2 mb-2" placeholder="Nama organisasi" />
                            <button class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm">Cipta</button>
                        </form>

                    <form method="POST" action="{{ route('profile.organization.join') }}" class="border rounded p-4">
                        @csrf
                        <div class="font-medium mb-2">Sertai Organisasi</div>
                        <select name="organization_id" class="w-full border rounded px-3 py-2 mb-2" required>
                            <option value="" disabled selected>Pilih organisasi</option>
                            @forelse($allOrganizations as $ao)
                                <option value="{{ $ao->id }}" @if($myOrganizations->contains('id', $ao->id)) disabled @endif>
                                    {{ $ao->name }}@if($myOrganizations->contains('id', $ao->id)) — sudah dipohon/ahli @endif
                                </option>
                            @empty
                                <option disabled>Tiada organisasi tersedia</option>
                            @endforelse
                        </select>
                        <button class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm">Sertai</button>
                        @error('organization')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            @endif
        </div>

            <div class="border rounded-xl bg-white p-6">
                <h2 class="text-lg font-medium mb-3">Tukar Kata Laluan</h2>
                <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm block mb-1">Kata Laluan Semasa</label>
                        <input type="password" name="current_password" class="w-full border rounded px-3 py-2" />
                        @error('current_password')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm block mb-1">Kata Laluan Baharu</label>
                        <input type="password" name="new_password" class="w-full border rounded px-3 py-2" />
                        @error('new_password')
                            <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm block mb-1">Sahkan Kata Laluan Baharu</label>
                        <input type="password" name="new_password_confirmation" class="w-full border rounded px-3 py-2" />
                    </div>
                    <button type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm">Kemaskini Kata Laluan</button>
                </form>
            </div>

            <div class="border rounded-xl bg-white p-6">
                <h2 class="text-lg font-medium mb-3">Kredit Kripto</h2>
                <div class="space-y-2 text-sm">
                    <div>Wallet Address: <span class="font-mono break-all">{{ $wallet->wallet_address ?? '—' }}</span></div>
                    <p class="text-xs text-slate-500">Alamat baharu akan dilumpuhkan jika akaun telah dijana. Ia hanya berfungsi sekali sahaja.</p>
                    <div>Baki Semasa: <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700"> {{ number_format(($wallet->credit_balance ?? 0), 4) }} SEN</span></div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="button" class="px-3 py-1.5 rounded bg-green-600 text-white text-sm">Terima</button>
                    <button type="button" class="px-3 py-1.5 rounded bg-red-600 text-white text-sm">Hantar</button>
                    <button type="button" class="px-3 py-1.5 rounded border text-sm">Transaksi</button>
                </div>
            </div>
        </div>
</div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
  var input = document.getElementById('avatarInput');
  var out = document.getElementById('avatarFileName');
  if (input && out) {
    input.addEventListener('change', function(e) {
      var name = (e.target.files && e.target.files[0]) ? e.target.files[0].name : 'No file chosen';
      out.textContent = name;
    });
  }
});
</script>
