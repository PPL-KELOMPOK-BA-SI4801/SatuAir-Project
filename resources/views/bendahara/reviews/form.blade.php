@extends('layouts.app')
@section('title', $review ? 'Edit Draf Pemeriksaan' : 'Buat Pemeriksaan')
@section('content')
<div class="space-y-6">
    <a href="{{ route('bendahara.reviews.reading', $reading) }}" class="text-sm text-[#1976D2]">← Rincian pencatatan</a>
    <h1 class="text-2xl sm:text-3xl font-bold text-[#12345A]">{{ $review ? 'Edit Draf Pemeriksaan' : 'Buat Pemeriksaan' }}</h1>
    @include('bendahara.reviews.errors')
    @include('bendahara.reviews.summary')
    @if ($stale)<p role="status" class="rounded-xl bg-amber-50 p-4 text-amber-900">Pencatatan telah dikoreksi. Periksa ulang data terbaru dan checklist sebelum menyimpan draf.</p>@endif
    <form method="POST" action="{{ $review ? route('bendahara.reviews.update', $review) : route('bendahara.reviews.store', $reading) }}" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 sm:p-8">
        @csrf @if ($review) @method('PATCH') @endif
        <input type="hidden" name="revision" value="{{ old('revision', $review ? $review->revision() : $reading->reviewRevision()) }}">
        <fieldset class="space-y-4"><legend class="mb-4 font-bold text-[#12345A]">Checklist Pemeriksaan</legend>
            <label class="flex items-start gap-3"><input class="mt-1 rounded" type="checkbox" name="checks[identity_period]" value="1" @checked(old('checks.identity_period', session()->hasOldInput() ? false : (! $stale && ($review?->checks['identity_period'] ?? false))))><span>Identitas pelanggan dan periode sudah sesuai</span></label>
            <label class="flex items-start gap-3"><input class="mt-1 rounded" type="checkbox" name="checks[meter_usage]" value="1" @checked(old('checks.meter_usage', session()->hasOldInput() ? false : (! $stale && ($review?->checks['meter_usage'] ?? false))))><span>Angka meter, bukti yang tersedia, dan hasil pemakaian sudah diperiksa</span></label>
        </fieldset>
        <div><label for="review-notes" class="font-semibold text-slate-600">Catatan Pemeriksaan</label>
            <textarea id="review-notes" name="notes" rows="4" maxlength="1000" placeholder="Contoh: angka sesuai foto meter. Jika dikembalikan, tuliskan alasan koreksi."
                class="mt-2 block w-full rounded-xl border border-slate-200 p-3">{{ old('notes', $review?->notes) }}</textarea>
            <p class="mt-2 text-sm text-slate-500">Alasan wajib diisi jika pencatatan akan dikembalikan. Menyimpan draf belum menerbitkan tagihan.</p>
        </div>
        <div class="flex flex-wrap gap-3"><button class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">{{ $review ? 'Simpan Perubahan Draf' : 'Simpan Draf' }}</button>
            <a href="{{ route('bendahara.reviews.reading', $reading) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-[#1976D2]">Kembali</a></div>
    </form>
</div>
@endsection
