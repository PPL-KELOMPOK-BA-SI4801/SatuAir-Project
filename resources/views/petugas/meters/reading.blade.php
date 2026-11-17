@extends('layouts.app')
@use('App\Services\HitungPemakaianMeter')

@section('title', $editing ? 'Edit Pencatatan Meter' : 'Rincian Pemakaian Air')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('petugas.meters.index') }}" class="text-sm text-[#1976D2]">← Daftar pencatatan</a>
        <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-[#12345A]">{{ $editing ? 'Edit Pencatatan Meter' : 'Rincian Pemakaian Air' }}</h1>
        <p class="mt-2 text-slate-500">Periode {{ $reading->reading_date->locale('id')->translatedFormat('F Y') }}</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            <strong>Perubahan belum disimpan.</strong>
            <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            @if ($errors->has('revision'))
                <a class="mt-2 inline-block underline" href="{{ route($editing ? 'petugas.readings.edit' : 'petugas.readings.show', $reading->id) }}">Muat ulang data terbaru</a>
            @endif
        </div>
    @endif

    <form @if ($editing) id="meter-form" method="POST" action="{{ route('petugas.readings.update', $reading->id) }}" @endif
        data-awal="{{ $meterAwal }}" data-original="{{ $meterAkhir }}" data-correction="{{ $editing ? '1' : '0' }}"
        data-max-satuan="9999999999" data-invalid="{{ $errors->any() ? '1' : '0' }}"
        class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-8 space-y-6">
        @if ($editing)
            @csrf
            @method('PATCH')
            <input type="hidden" name="revision" value="{{ old('revision', $revision) }}">
            <input type="hidden" name="operation" value="edit">
        @endif

        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#12345A] break-words">{{ $reading->waterMeter->user->name }} · {{ $reading->waterMeter->user->customer_id }}</h2>
                <p class="mt-3 text-slate-500">Nomor meter {{ $reading->waterMeter->meter_number }}</p>
            </div>
            <span class="self-start rounded-lg px-3 py-1 text-xs font-semibold {{ $reading->trashed() ? 'bg-red-50 text-red-700' : ($lockedReason ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-800') }}">
                {{ $reading->trashed() ? 'Dibatalkan' : ($reading->verified_at ? 'Sudah diverifikasi' : ($lockedReason ? 'Terkunci' : 'Tercatat')) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="meter-awal" class="text-sm font-semibold text-slate-500">METER AWAL (m³)</label>
                <input id="meter-awal" readonly value="{{ HitungPemakaianMeter::format($meterAwal) }}"
                    class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 p-4 text-lg text-[#12345A]">
            </div>
            <div>
                <label for="meter-akhir" class="text-sm font-semibold text-slate-500">METER AKHIR (m³)</label>
                <input id="meter-akhir" name="reading_value" inputmode="decimal" maxlength="11"
                    value="{{ $editing && is_scalar(old('reading_value')) ? old('reading_value') : str_replace('.', ',', $reading->reading_value) }}"
                    @readonly(! $editing) @required($editing) aria-describedby="meter-help meter-error"
                    class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 p-4 text-lg text-[#12345A]">
                @if ($editing)<p id="meter-help" class="mt-2 text-xs text-slate-500">Gunakan koma atau titik, maksimal 2 angka desimal.</p>@endif
                <p id="meter-error" class="mt-2 text-sm text-red-700" role="status">{{ $errors->first('reading_value') }}</p>
            </div>
        </div>

        <x-total-pemakaian :awal="$editing ? null : $meterAwal" :akhir="$editing ? null : $meterAkhir" />
        @if ($reading->trashed())<p class="text-sm text-slate-500">Pemakaian di atas merupakan isi catatan yang dibatalkan dan tidak dipakai untuk tagihan aktif.</p>@endif

        @if ($editing)
            <div>
                <label for="correction-reason" class="text-sm font-semibold text-slate-600">Alasan perubahan <span aria-hidden="true">*</span></label>
                <textarea id="correction-reason" name="reason" rows="3" required maxlength="300" placeholder="Contoh: koreksi angka sesuai foto meter."
                    class="mt-2 block w-full rounded-xl border border-slate-200 p-3">{{ is_string(old('reason')) ? old('reason') : '' }}</textarea>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" data-correction-save class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Simpan Perubahan</button>
                <a href="{{ route('petugas.readings.show', $reading->id) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-[#1976D2]">Batalkan Perubahan</a>
            </div>
        @else
            <div class="text-sm text-slate-500 space-y-2">
                <p>Tanggal pencatatan: {{ $reading->reading_date->format('d/m/Y') }} · Dicatat oleh {{ $reading->recorder->name ?? 'Pengguna tidak tersedia' }}</p>
                @if ($reading->notes)<p>Catatan: {{ $reading->notes }}</p>@endif
                @if ($reading->photo)<a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($reading->photo) }}" class="inline-block underline text-[#1976D2]">Lihat foto meter</a>@endif
            </div>
            @if ($lockedReason)<p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">{{ $lockedReason }}</p>@endif
            <div class="flex flex-wrap gap-3">
                @if ($lockedReason)
                    <button type="button" disabled class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white opacity-50 cursor-not-allowed">Edit Pencatatan</button>
                @else
                    <a href="{{ route('petugas.readings.edit', $reading->id) }}" class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">Edit Pencatatan</a>
                @endif
                <button type="button" data-open-cancel @disabled($lockedReason !== null)
                    class="rounded-xl border border-red-200 px-5 py-3 font-semibold text-red-700 disabled:opacity-50 disabled:cursor-not-allowed">Batalkan Pencatatan</button>
            </div>
        @endif
    </form>

    <section aria-label="Riwayat pencatatan" class="space-y-3">
        <h2 class="text-lg font-semibold text-[#12345A]">Riwayat pencatatan</h2>
        <ul class="divide-y divide-slate-200">
            @foreach ($reading->changes as $change)
                <li class="py-4 space-y-1 text-sm break-words">
                    <p class="font-semibold">{{ $change->action === 'cancelled' ? 'Pencatatan dibatalkan' : 'Meter akhir dikoreksi' }}</p>
                    @if ($change->action === 'corrected')
                        <p>{{ HitungPemakaianMeter::format(app(HitungPemakaianMeter::class)->angkaKeSatuan($change->before['reading_value'])) }} menjadi {{ HitungPemakaianMeter::format(app(HitungPemakaianMeter::class)->angkaKeSatuan($change->after['reading_value'])) }} m³</p>
                    @endif
                    <p>Alasan: {{ $change->reason }}</p>
                    <p class="text-slate-500">{{ $change->actor->name ?? 'Pengguna tidak tersedia' }} · {{ $change->created_at->format('d/m/Y H:i') }}</p>
                </li>
            @endforeach
            <li class="py-4 text-sm text-slate-500">Pencatatan dibuat · {{ $reading->recorder->name ?? 'Pengguna tidak tersedia' }} · {{ $reading->created_at->format('d/m/Y H:i') }}</li>
        </ul>
    </section>

    @if ($otherReadings->isNotEmpty())
        <section aria-label="Pencatatan lain" class="space-y-3">
            <h2 class="text-lg font-semibold text-[#12345A]">Pencatatan lain untuk meter ini</h2>
            <ul class="divide-y divide-slate-200">
                @foreach ($otherReadings as $other)
                    <li class="py-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                        <span>{{ $other->reading_date->format('d/m/Y') }} · {{ HitungPemakaianMeter::format(app(HitungPemakaianMeter::class)->angkaKeSatuan($other->reading_value)) }} m³ · {{ $other->trashed() ? 'Dibatalkan' : 'Tercatat' }}</span>
                        <a class="text-[#1976D2] underline" href="{{ route('petugas.readings.show', $other->id) }}">Lihat catatan #{{ $other->id }}</a>
                    </li>
                @endforeach
            </ul>
            {{ $otherReadings->links() }}
        </section>
    @endif

    @if (! $editing && $lockedReason === null)
        <dialog id="cancel-reading-dialog" aria-labelledby="cancel-reading-title"
            data-reopen="{{ $errors->any() && old('operation') === 'cancel' ? '1' : '0' }}"
            class="w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 p-5 sm:p-7 text-[#263445]">
            <h2 id="cancel-reading-title" class="text-xl font-bold text-[#12345A]">Batalkan pencatatan?</h2>
            <p class="mt-3 text-sm text-slate-500">Catatan dan tagihannya dinonaktifkan. Data serta alasan pembatalan tetap tersimpan pada riwayat.</p>
            <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm">{{ $reading->waterMeter->user->name }} · {{ HitungPemakaianMeter::format($pemakaian) }} m³</p>
            <form method="POST" action="{{ route('petugas.readings.cancel', $reading->id) }}" class="mt-5 space-y-5">
                @csrf
                <input type="hidden" name="revision" value="{{ old('operation') === 'cancel' ? old('revision', $revision) : $revision }}">
                <input type="hidden" name="operation" value="cancel">
                <div>
                    <label for="cancel-reason" class="text-sm font-semibold">Alasan pembatalan <span aria-hidden="true">*</span></label>
                    <textarea id="cancel-reason" name="reason" rows="3" required maxlength="300" placeholder="Tuliskan alasan pembatalan pencatatan."
                        class="mt-2 block w-full rounded-xl border border-slate-200 p-3">{{ old('operation') === 'cancel' && is_string(old('reason')) ? old('reason') : '' }}</textarea>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" data-close-cancel class="rounded-xl border border-slate-200 px-4 py-3 text-[#1976D2]">Kembali</button>
                    <button type="submit" data-confirm-cancel class="rounded-xl bg-red-700 px-4 py-3 font-semibold text-white disabled:opacity-50 disabled:cursor-not-allowed">Ya, Batalkan Pencatatan</button>
                </div>
            </form>
        </dialog>
    @endif
</div>
@endsection

@push('styles')
<style>#cancel-reading-dialog::backdrop { background: rgb(15 39 68 / 45%); }</style>
@endpush
@push('scripts')
@if ($editing)<script src="{{ asset('assets/meter.js') }}" defer></script>@endif
<script src="{{ asset('assets/meter-corrections.js') }}" defer></script>
@endpush
