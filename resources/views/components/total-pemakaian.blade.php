@props(['awal' => null, 'akhir' => null])
@use('App\Services\HitungPemakaianMeter')
<div {{ $attributes->class(['calculation rounded-2xl bg-[#EBF3FE] p-6']) }} aria-live="polite" aria-atomic="true">
    <div class="flex flex-col items-start gap-6">
        <span class="text-sm font-semibold uppercase text-[#1976D2]">Total Pemakaian</span>
        <strong id="total-pemakaian" class="text-4xl font-bold text-[#1976D2]">{{ $awal !== null && $akhir !== null ? HitungPemakaianMeter::format(app(HitungPemakaianMeter::class)->hitung($awal, $akhir)) : '—' }} m³</strong>
    </div>
    <p id="rumus" class="mt-6 text-sm sm:text-base text-slate-500">@if ($awal !== null && $akhir !== null){{ HitungPemakaianMeter::format($akhir) }} − {{ HitungPemakaianMeter::format($awal) }} = {{ HitungPemakaianMeter::format(app(HitungPemakaianMeter::class)->hitung($awal, $akhir)) }} m³@else Isi meter akhir yang valid untuk melihat perhitungan.@endif</p>
</div>
