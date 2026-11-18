@extends('layouts.app')
@section('title', 'Rincian Pemeriksaan')
@section('content')
<div class="space-y-6">
    <a href="{{ route('bendahara.reviews.reading', $reading) }}" class="text-sm text-[#1976D2]">← Rincian pencatatan dan riwayat</a>
    <div><h1 class="text-2xl sm:text-3xl font-bold text-[#12345A]">Pemeriksaan #{{ $review->id }}</h1><p class="mt-2 font-semibold text-[#1976D2]">{{ $review->label() }}</p></div>
    @include('bendahara.reviews.errors')
    @include('bendahara.reviews.summary')
    <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8" aria-label="Hasil pemeriksaan">
        <p class="font-semibold">Pemeriksa: {{ $review->reviewer->name ?? 'Pengguna tidak tersedia' }}</p>
        <p class="text-sm text-slate-500">Dibuat {{ $review->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}@if ($review->reviewed_at) · Diputuskan {{ $review->reviewed_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}@endif</p>
        <ul class="space-y-2 text-sm"><li>Identitas dan periode: {{ ($review->checks['identity_period'] ?? false) ? 'Sudah diperiksa' : 'Belum diperiksa' }}</li><li>Angka meter dan pemakaian: {{ ($review->checks['meter_usage'] ?? false) ? 'Sudah diperiksa' : 'Belum diperiksa' }}</li></ul>
        <p class="whitespace-pre-wrap text-slate-600">Catatan: {{ $review->notes ?: 'Belum ada catatan.' }}</p>
        <div role="region" class="rounded-xl bg-slate-50 p-4 text-sm" aria-label="Snapshot pemeriksaan"><p class="font-semibold">Data saat draf disimpan / keputusan dibuat</p><p class="mt-2">{{ str_replace('.', ',', $review->snapshot['reading_value']) }} − {{ str_replace('.', ',', $review->snapshot['previous_value']) }} = {{ str_replace('.', ',', $review->snapshot['usage_m3']) }} m³</p></div>
        @if ($stale)<p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Data pencatatan berubah sejak pemeriksaan ini disimpan. Draf harus diperbarui sebelum diputuskan; snapshot lama tetap terlihat di atas.</p>@endif
        @if ($lockedReason)<p class="text-sm text-slate-500">{{ $lockedReason }}</p>@endif
        @if ($review->status === 'approved' && $reading->bill)<p class="font-semibold text-green-700">Tagihan #{{ $reading->bill->id }} telah diterbitkan untuk pemakaian {{ str_replace('.', ',', $reading->bill->usage_m3) }} m³.</p>@endif
        @if (! $review->trashed() && $review->status === 'draft' && $review->reviewer_id === auth()->id())
            <div class="flex flex-wrap gap-3">
                @if (! $lockedReason)<a href="{{ route('bendahara.reviews.edit', $review) }}" class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">Edit Draf</a>@endif
                <form method="POST" action="{{ route('bendahara.reviews.destroy', $review) }}" onsubmit="return confirm('Batalkan draf pemeriksaan? Pencatatan Petugas tetap disimpan.');">@csrf @method('DELETE')
                    <input type="hidden" name="revision" value="{{ $review->revision() }}"><button class="rounded-xl border border-red-200 px-5 py-3 font-semibold text-red-700">Batalkan Draf</button></form>
            </div>
            @if (! $lockedReason)
                <form method="POST" action="{{ route('bendahara.reviews.finalize', $review) }}" class="flex flex-wrap gap-3 border-t border-slate-100 pt-5">@csrf
                    <input type="hidden" name="revision" value="{{ $review->revision() }}">
                    <button name="decision" value="approved" @disabled($stale) class="rounded-xl bg-green-700 px-5 py-3 font-semibold text-white disabled:opacity-50">Setujui &amp; Terbitkan Tagihan</button>
                    <button name="decision" value="returned" @disabled($stale) class="rounded-xl border border-amber-300 px-5 py-3 font-semibold text-amber-900 disabled:opacity-50">Kembalikan untuk Koreksi</button>
                </form>
            @endif
        @endif
    </section>
</div>
@endsection
