<section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8" aria-label="Data pencatatan">
    <div><h2 class="text-xl sm:text-2xl font-bold text-[#12345A]">{{ $reading->waterMeter->user->name }} · {{ $reading->waterMeter->user->customer_id }}</h2>
        <p class="mt-2 text-slate-500">Nomor meter {{ $reading->waterMeter->meter_number }} · Periode {{ $reading->reading_date->locale('id')->translatedFormat('F Y') }}</p></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[#12345A]">
        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">METER AWAL</p><p class="mt-2 text-xl">{{ str_replace('.', ',', $snapshot['previous_value']) }} m³</p></div>
        <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">METER AKHIR</p><p class="mt-2 text-xl">{{ str_replace('.', ',', $snapshot['reading_value']) }} m³</p></div>
    </div>
    <x-total-pemakaian :awal="$awal" :akhir="$akhir" />
    <p class="text-sm text-slate-500">Tanggal: {{ $reading->reading_date->format('d/m/Y') }} · Dicatat oleh {{ $reading->recorder->name ?? 'Pengguna tidak tersedia' }}</p>
    @if ($reading->notes)<p class="text-sm text-slate-600">Catatan Petugas: {{ $reading->notes }}</p>@endif
    @if ($reading->photo)
        <a href="{{ Storage::disk('public')->url($reading->photo) }}" target="_blank" rel="noopener" class="inline-block font-semibold text-[#1976D2]">Lihat foto meter</a>
    @else<p class="text-sm text-slate-500">Tidak ada foto meter pada pencatatan ini.</p>@endif
    <p class="text-sm font-semibold text-[#1976D2]">{{ $reading->reviewLabel() }}</p>
</section>
