@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-2xl font-semibold mb-1">Edit Acara</h1>
    <p class="text-slate-600 mb-6">Kemas kini maklumat acara anda.</p>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 text-red-700 px-3 py-2 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('events.update', $event->slug) }}" enctype="multipart/form-data" class="border rounded-xl p-6 bg-white space-y-5 shadow-sm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm mb-1">Tajuk Acara</label>
                <input type="text" name="title" value="{{ old('title', $event->title) }}" required maxlength="35" class="w-full border rounded px-3 h-12 text-lg" />
            </div>
        </div>
        <div class="border rounded p-4">
            <div class="font-medium mb-2">Jenis Harga</div>
            @php($pricingType = old('pricing_type', optional($ticket)->type))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <label class="flex items-center gap-2 text-sm"><input type="radio" name="pricing_type" value="free" {{ $pricingType==='free' ? 'checked' : '' }} /> Percuma</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" name="pricing_type" value="paid" {{ $pricingType==='paid' ? 'checked' : '' }} /> Berbayar</label>
                <label class="flex items-center gap-2 text-sm"><input type="radio" name="pricing_type" value="sponsor" {{ $pricingType==='sponsor' ? 'checked' : '' }} /> Sponsor</label>
            </div>
            <div id="pricingFree" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2" style="display:none">
                <div>
                    <label class="block text-sm mb-1">Jumlah Tiket</label>
                    <input type="number" min="0" id="ticketQuantityFree" name="ticket_quantity" value="{{ old('ticket_quantity', optional($ticket)->quantity) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div class="md:col-span-2 text-xs text-slate-500 flex items-end">{{ ($tierLimits['max_participants'] ?? 0) > 0 ? 'Had mengikut tier: maks ' . ($tierLimits['max_participants'] ?? 0) . ' peserta.' : 'VIP: tanpa had peserta.' }}</div>
            </div>
            <div id="pricingPaid" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2" style="display:none">
                <div>
                    <label class="block text-sm mb-1">Harga Tiket (RM)</label>
                    <input type="number" step="0.01" min="0" name="ticket_price" value="{{ old('ticket_price', optional($ticket)->type==='paid' ? optional($ticket)->price : null) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Jumlah Tiket</label>
                    <input type="number" min="0" id="ticketQuantity" name="ticket_quantity" value="{{ old('ticket_quantity', optional($ticket)->quantity) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Lebihan Bayaran (RM1/peserta)</label>
                    <input type="text" id="tierExcessFeePaid" class="w-full border rounded px-3 h-10 bg-slate-50" readonly />
                </div>
            </div>
            <div id="pricingSponsor" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-2" style="display:none">
                <div>
                    <label class="block text-sm mb-1">Harga Sebenar (RM)</label>
                    <input type="number" step="0.01" min="0" id="ticketBasePrice" name="ticket_base_price" value="{{ old('ticket_base_price', optional($ticket)->base_price) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Ditaja (RM)</label>
                    <input type="number" step="0.01" min="0" id="ticketSponsorAmount" name="ticket_sponsor_amount" value="{{ old('ticket_sponsor_amount', optional($ticket)->sponsor_amount) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div>
                    <label class="block text-sm mb-1">Perlu Dibayar (RM)</label>
                <input type="text" id="ticketDueAmount" value="{{ old('ticket_due_amount', optional($ticket)->type==='sponsor' ? optional($ticket)->price : null) }}" data-prefilled="{{ optional($ticket)->type==='sponsor' ? '1' : '0' }}" class="w-full border rounded px-3 h-10 bg-slate-50" readonly />
                </div>
                <div>
                    <label class="block text-sm mb-1">Jumlah Tiket</label>
                    <input type="number" min="0" id="ticketQuantitySponsor" name="ticket_quantity" value="{{ old('ticket_quantity', optional($ticket)->quantity) }}" class="w-full border rounded px-3 h-10" />
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm mb-1">Lebihan Bayaran (RM1/peserta)</label>
                    <input type="text" id="tierExcessFeeSponsor" class="w-full border rounded px-3 h-10 bg-slate-50" readonly />
                </div>
            </div>
            <div class="text-xs text-slate-500">Pilih satu. Untuk Sponsor, sistem memaparkan baki bayar dan lebihan bayaran jika melebihi had peserta tier.</div>
        </div>
        <div class="border rounded p-4">
            <div class="font-medium mb-2">Organisasi</div>
            @php($canOrg = in_array(($tier ?? 'FREE'), ['PRO','VIP']))
            <select name="organization_id" class="w-full border rounded px-3 h-10" {{ $canOrg ? '' : 'disabled' }}>
                <option value="">— Tiada —</option>
                @foreach(($organizations ?? []) as $org)
                    <option value="{{ $org->id }}" {{ old('organization_id', $event->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                @endforeach
            </select>
            <div class="text-xs text-slate-500 mt-1">{{ $canOrg ? 'Pilih organisasi diluluskan anda (opsyenal).' : 'Organisasi tersedia untuk PRO/VIP sahaja.' }}</div>
        </div>

        <div class="border rounded p-4">
            <div class="font-medium mb-2">Petugas Imbasan (Scanners)</div>
            <div class="text-sm text-slate-600 mb-4">
                Lantik pengguna lain untuk mengimbas tiket QR acara ini. Mereka perlu mempunyai akaun berdaftar.
            </div>

            <div class="space-y-3 mb-6">
                @forelse ($staffs as $staff)
                    <div class="flex items-center justify-between bg-slate-50 p-3 rounded">
                        <div>
                            <div class="font-medium">{{ $staff->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $staff->user->email }}</div>
                        </div>
                        <button type="button" onclick="document.getElementById('remove-staff-{{ $staff->id }}').submit()" class="text-red-600 hover:text-red-800 text-sm">
                            Buang
                        </button>
                    </div>
                @empty
                    <div class="text-sm text-slate-500 italic">Tiada petugas dilantik.</div>
                @endforelse
            </div>
            
            <button type="button" onclick="document.getElementById('add-staff-modal').classList.remove('hidden')" class="text-sm text-blue-600 hover:underline">
                + Tambah Petugas
            </button>
        </div>

        <div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Banner URL</label>
                    <input type="url" name="banner_path" value="{{ old('banner_path') }}" class="w-full border rounded px-3 h-10" placeholder="https://..." />
                    <div class="text-xs text-slate-500 mt-1">Masukkan URL baharu jika ingin menukar banner. Jika tidak, biarkan kosong.</div>
                </div>
                <div>
                    <label class="block text-sm mb-1">Atau Muat Naik Banner</label>
                    <div class="flex items-center border rounded overflow-hidden">
                        <label for="bannerFileInput" class="px-3 py-2 bg-slate-100 text-slate-700 cursor-pointer">Choose File</label>
                        <span id="bannerFileName" class="px-3 py-2 text-slate-500 flex-1">No file chosen</span>
                    </div>
                    <input type="file" name="banner_file" id="bannerFileInput" accept="image/*" class="hidden" />
                    @error('banner_file')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="border rounded-xl bg-white overflow-hidden">
            <div class="p-4 font-medium">Pratonton Banner</div>
            @php($img = $event->banner_path ? (\Illuminate\Support\Str::startsWith($event->banner_path, ['http://','https://']) ? $event->banner_path : asset($event->banner_path)) : null)
            <div class="px-4 pb-4">
                @if(!empty($img))
                    <img id="bannerPreviewImg" src="{{ $img }}" alt="Banner Preview" class="w-full h-64 object-cover">
                @else
                    <div id="bannerPreviewEmpty" class="w-full h-64 bg-slate-100 flex items-center justify-center text-slate-500">No image</div>
                    <img id="bannerPreviewImg" src="" alt="Banner Preview" class="w-full h-64 object-cover hidden">
                @endif
            </div>
        </div>
        <div>
            <label class="block text-sm mb-1">Deskripsi (HTML Editor)</label>
            <input id="eventDescriptionInput" type="hidden" name="description" value="{{ old('description', $event->description) }}">
            <trix-editor input="eventDescriptionInput" class="w-full border rounded px-3 py-2" style="min-height:50vh"></trix-editor>
            <div class="text-xs text-slate-500 mt-1">Gunakan pemformatan untuk jadikan deskripsi lebih cantik.</div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Mula</label>
                <input type="datetime-local" name="start_at" value="{{ old('start_at', optional($event->start_at)->format('Y-m-d\TH:i')) }}" required class="w-full border rounded px-3 h-10" />
            </div>
            <div>
                <label class="block text-sm mb-1">Tamat</label>
                <input type="datetime-local" name="end_at" value="{{ old('end_at', optional($event->end_at)->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 h-10" />
            </div>
        </div>
        <div id="durationTip" class="text-xs text-slate-500"></div>
        <div>
            <label class="block text-sm mb-1">Lokasi</label>
            <input type="text" name="location" id="locationInput" value="{{ old('location', $event->location) }}" class="w-full border rounded px-3 h-10 mb-2" placeholder="Contoh: Menara KL, Kuala Lumpur" />
            <div class="flex items-center gap-2 mb-2">
                <input type="text" id="mapSearch" class="flex-1 border rounded px-3 h-10" placeholder="Cari lokasi (cth: Bukit Bintang)" />
                <button type="button" id="mapSearchBtn" class="px-3 h-10 rounded bg-blue-600 text-white text-sm">Cari</button>
            </div>
            <div id="mapStatus" class="text-xs text-slate-500 mb-2"></div>
            <div id="eventMap" class="w-full border rounded h-64"></div>
            <input type="hidden" name="location_lat" id="locationLat" value="{{ old('location_lat') }}" />
            <input type="hidden" name="location_lng" id="locationLng" value="{{ old('location_lng') }}" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Poskod</label>
                <input type="text" name="postcode" value="{{ old('postcode', $event->postcode) }}" class="w-full border rounded px-3 h-10" />
            </div>
            <div>
                <label class="block text-sm mb-1">Negara</label>
                <select name="country" class="w-full border rounded px-3 h-10">
                    <option value="">Pilih Negara</option>
                    @foreach(($countries ?? []) as $c)
                        <option value="{{ $c['code'] }}" {{ old('country', $event->country) === $c['code'] ? 'selected' : '' }}>
                            {{ $c['name'] }} {{ $c['emoji'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" class="border" {{ old('is_published', $event->is_published) ? 'checked' : '' }} />
            <span>Terbitkan selepas kemaskini</span>
        </label>
        <div class="flex items-center justify-end gap-2">
            <a href="/dashboard" class="px-3 py-2 rounded border text-sm">Kembali</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Simpan</button>
        </div>
    </form>
</div>

<!-- Modal Tambah Petugas -->
<div id="add-staff-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('add-staff-modal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('events.staff.add', $event->slug) }}" class="p-6">
                @csrf
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Petugas</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">
                        Masukkan emel pengguna yang anda ingin lantik sebagai petugas imbasan.
                    </p>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Emel Pengguna</label>
                        <input type="email" name="email" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Tambah
                    </button>
                    <button type="button" onclick="document.getElementById('add-staff-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Forms for Deleting Staff -->
@foreach ($staffs as $staff)
    <form id="remove-staff-{{ $staff->id }}" action="{{ route('events.staff.remove', ['slug' => $event->slug, 'staffId' => $staff->id]) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection

@push('head')
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  (function(){
    var mapEl = document.getElementById('eventMap');
    if (!mapEl) return;
    var lat = parseFloat(document.getElementById('locationLat').value || '3.1390');
    var lng = parseFloat(document.getElementById('locationLng').value || '101.6869');
    var map = L.map('eventMap').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    var marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    function setLocation(lat, lng, displayName, addr){
      document.getElementById('locationLat').value = lat;
      document.getElementById('locationLng').value = lng;
      if (displayName) document.getElementById('locationInput').value = displayName;
      var postcodeEl = document.querySelector('input[name="postcode"]');
      var countryEl = document.querySelector('select[name="country"]');
      if (addr) {
        if (postcodeEl && addr.postcode) postcodeEl.value = addr.postcode;
        if (countryEl) {
          var code = (addr.country_code || '').toLowerCase();
          var name = (addr.country || '').toLowerCase();
          var matched = false;
          if (code) {
            for (var i=0;i<countryEl.options.length;i++) {
              var opt = countryEl.options[i];
              if (opt.value && opt.value.toLowerCase() === code) { countryEl.value = opt.value; matched = true; break; }
            }
          }
          if (!matched && name) {
            for (var j=0;j<countryEl.options.length;j++) {
              var opt2 = countryEl.options[j];
              var text = (opt2.textContent || '').toLowerCase();
              if (text.includes(name)) { countryEl.value = opt2.value; matched = true; break; }
            }
          }
        }
      }
    }

    function reverseGeocode(lat, lng){
      fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+lat+'&lon='+lng)
        .then(function(r){ return r.json(); })
        .then(function(json){ setLocation(lat, lng, json.display_name, json.address || {}); });
    }

    map.on('click', function(e){
      marker.setLatLng(e.latlng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });
    marker.on('dragend', function(e){
      var ll = marker.getLatLng();
      reverseGeocode(ll.lat, ll.lng);
    });

    function search(){
      var q = document.getElementById('mapSearch').value.trim();
      if (!q) return;
      document.getElementById('mapStatus').textContent = 'Mencari lokasi…';
      fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q='+encodeURIComponent(q))
        .then(function(r){ return r.json(); })
        .then(function(list){
          if (!list || !list.length) { document.getElementById('mapStatus').textContent = 'Tiada keputusan ditemui.'; return; }
          var item = list[0];
          var lat = parseFloat(item.lat), lng = parseFloat(item.lon);
          map.setView([lat, lng], 15);
          marker.setLatLng([lat, lng]);
          reverseGeocode(lat, lng);
          document.getElementById('mapStatus').textContent = 'Lokasi ditemui.';
        })
        .catch(function(){ document.getElementById('mapStatus').textContent = 'Ralat carian. Cuba lagi.'; });
    }
    document.getElementById('mapSearchBtn').addEventListener('click', search);
    document.getElementById('mapSearch').addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); search(); } });

    // Pricing toggles & indicators
    var tier = '{{ $tier ?? 'FREE' }}';
    var maxParticipants = {{ ($tierLimits['max_participants'] ?? 0) > 0 ? ($tierLimits['max_participants']) : 'null' }};
    var maxDays = {{ ($tierLimits['max_days'] ?? 0) > 0 ? ($tierLimits['max_days']) : 'null' }};
    function setDisabled(containerId, disabled){
      var box = document.getElementById(containerId);
      if (!box) return;
      box.querySelectorAll('input, select, textarea, button').forEach(function(el){ el.disabled = !!disabled; });
    }
    function togglePricing(){
      var type = (document.querySelector('input[name="pricing_type"]:checked')||{}).value;
      var freeBox = document.getElementById('pricingFree');
      var paidBox = document.getElementById('pricingPaid');
      var sponsorBox = document.getElementById('pricingSponsor');
      if (freeBox) freeBox.style.display = (type==='free') ? '' : 'none';
      if (paidBox) paidBox.style.display = (type==='paid') ? '' : 'none';
      if (sponsorBox) sponsorBox.style.display = (type==='sponsor') ? '' : 'none';
      setDisabled('pricingFree', type!=='free');
      setDisabled('pricingPaid', type!=='paid');
      setDisabled('pricingSponsor', type!=='sponsor');
      computeDue(); computeExcess(); enforceMaxQty();
    }
    function computeDue(){
      var baseEl = document.getElementById('ticketBasePrice');
      var sponsorEl = document.getElementById('ticketSponsorAmount');
      var dueEl = document.getElementById('ticketDueAmount');
      var baseStr = baseEl ? baseEl.value : '';
      var sponsorStr = sponsorEl ? sponsorEl.value : '';
      var isBlank = (!baseStr || baseStr.trim()==='') && (!sponsorStr || sponsorStr.trim()==='');
      if (isBlank && dueEl && dueEl.dataset.prefilled === '1') { return; }
      var base = parseFloat(baseStr || '0');
      var sponsor = parseFloat(sponsorStr || '0');
      var due = Math.max(0, base - sponsor);
      if (dueEl) dueEl.value = due.toFixed(2);
    }
    function computeExcess(){
      var paidEl = document.getElementById('tierExcessFeePaid');
      var sponsorEl = document.getElementById('tierExcessFeeSponsor');
      if (maxParticipants == null) {
        if (paidEl) { paidEl.value = ''; paidEl.closest('div')?.style && (paidEl.closest('div').style.display = 'none'); }
        if (sponsorEl) { sponsorEl.value = ''; sponsorEl.closest('div')?.style && (sponsorEl.closest('div').style.display = 'none'); }
        return;
      }
      var qPaid = parseInt(document.getElementById('ticketQuantity')?.value || '0');
      var qSponsor = parseInt(document.getElementById('ticketQuantitySponsor')?.value || '0');
      var type = (document.querySelector('input[name="pricing_type"]:checked')||{}).value;
      var q = type==='sponsor' ? qSponsor : qPaid;
      var excess = Math.max(0, q - maxParticipants);
      var fee = (excess * 1.0).toFixed(2);
      if (paidEl) paidEl.value = fee;
      if (sponsorEl) sponsorEl.value = fee;
      if (paidEl) paidEl.closest('div')?.style && (paidEl.closest('div').style.display = '');
      if (sponsorEl) sponsorEl.closest('div')?.style && (sponsorEl.closest('div').style.display = '');
    }
    function enforceMaxQty(){
      if (maxParticipants == null) return;
      var elFree = document.getElementById('ticketQuantityFree');
      if (elFree) { elFree.max = String(maxParticipants); var v0 = parseInt(elFree.value||'0'); if (v0 > maxParticipants) elFree.value = String(maxParticipants); }
      var elPaid = document.getElementById('ticketQuantity');
      if (elPaid) { elPaid.max = String(maxParticipants); var v1 = parseInt(elPaid.value||'0'); if (v1 > maxParticipants) elPaid.value = String(maxParticipants); }
      var elSponsor = document.getElementById('ticketQuantitySponsor');
      if (elSponsor) { elSponsor.max = String(maxParticipants); var v2 = parseInt(elSponsor.value||'0'); if (v2 > maxParticipants) elSponsor.value = String(maxParticipants); }
    }
    document.querySelectorAll('input[name="pricing_type"]').forEach(function(r){ r.addEventListener('change', togglePricing); });
    ['ticketBasePrice','ticketSponsorAmount','ticketQuantity','ticketQuantitySponsor','ticketQuantityFree'].forEach(function(id){
      var el = document.getElementById(id); if (el) el.addEventListener('input', function(){ computeDue(); computeExcess(); });
    });
    var fq = document.getElementById('ticketQuantityFree'); if (fq) fq.addEventListener('input', enforceMaxQty);
    enforceMaxQty();
    togglePricing();

    // Enforce max duration by tier
    function fmtLocal(dt){
      function pad(n){ return (n<10?'0':'')+n; }
      return dt.getFullYear()+"-"+pad(dt.getMonth()+1)+"-"+pad(dt.getDate())+"T"+pad(dt.getHours())+":"+pad(dt.getMinutes());
    }
    function enforceMaxEnd(){
      var sEl = document.querySelector('input[name="start_at"]');
      var eEl = document.querySelector('input[name="end_at"]');
      var tip = document.getElementById('durationTip');
      if (!sEl || !eEl) return;
      var s = sEl.value;
      if (!s || maxDays == null) { if (tip) tip.textContent = ''; return; }
      var start = new Date(s);
      var maxEnd = new Date(start.getTime() + (maxDays*24*60*60*1000));
      eEl.max = fmtLocal(maxEnd);
      if (eEl.value) {
        var end = new Date(eEl.value);
        if (end.getTime() > maxEnd.getTime()) {
          eEl.value = fmtLocal(maxEnd);
        }
      }
      if (tip) tip.textContent = 'Tempoh maks mengikut tier: ' + maxDays + ' hari.';
    }
    var sEl = document.querySelector('input[name="start_at"]');
    var eEl = document.querySelector('input[name="end_at"]');
    if (sEl) sEl.addEventListener('change', enforceMaxEnd);
    if (eEl) eEl.addEventListener('change', enforceMaxEnd);
    enforceMaxEnd();
  })();
</script>
<script>
  (function(){
    var bannerUrlInput = document.querySelector('input[name="banner_path"]');
    var bannerFileInput = document.getElementById('bannerFileInput');
    var previewImg = document.getElementById('bannerPreviewImg');
    var previewEmpty = document.getElementById('bannerPreviewEmpty');
    function showImg(src){
      if (!previewImg) return;
      if (src && src.trim() !== '') {
        previewImg.src = src;
        previewImg.classList.remove('hidden');
        if (previewEmpty) previewEmpty.style.display = 'none';
      } else {
        previewImg.src = '';
        previewImg.classList.add('hidden');
        if (previewEmpty) previewEmpty.style.display = '';
      }
    }
    if (bannerUrlInput) {
      bannerUrlInput.addEventListener('input', function(e){
        var v = e.target.value || '';
        showImg(v);
      });
    }
    if (bannerFileInput) {
      bannerFileInput.addEventListener('change', function(e){
        var f = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;
        if (!f) { showImg(''); return; }
        var r = new FileReader();
        r.onload = function(evt){ showImg(evt.target.result || ''); };
        r.readAsDataURL(f);
      });
    }
  })();
</script>
@endpush
