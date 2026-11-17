@extends('layouts.app')
@use('App\Services\HitungPemakaianMeter')

@section('title', 'Catat Pemakaian Air')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#12345A]">Catat Pemakaian Air</h1>
        <p class="mt-2 text-slate-500">Masukkan angka meter terbaru. Pemakaian dihitung dari selisih angka meter.</p>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700" role="alert">
            <strong>Pencatatan belum disimpan.</strong>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($tercatat)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800" role="status">
            Pelanggan ini sudah dicatat pada periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $readingPeriod)->locale('id')->translatedFormat('F Y') }}.
            @if ($currentReading)
                <a class="ml-1 underline font-semibold" href="{{ route('petugas.readings.show', $currentReading->id) }}">Lihat rincian pencatatan</a>
            @endif
        </div>
    @elseif (! $bolehMencatat)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800" role="status">
            Pencatatan belum tersedia untuk meter atau periode ini. Hubungi pengelola untuk memeriksa statusnya.
        </div>
    @endif

    <form id="meter-form" method="POST" action="{{ route('petugas.meters.store') }}" enctype="multipart/form-data"
        data-awal="{{ $meterAwal }}" data-max-satuan="9999999999" data-invalid="{{ $errors->any() ? '1' : '0' }}"
        class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-8 space-y-6">
        @csrf
        <input type="hidden" name="water_meter_id" value="{{ $meter->id }}">

        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#12345A] break-words">{{ $meter->user->name }} · {{ $meter->user->customer_id }}</h2>
            <p class="mt-3 text-slate-500">Nomor meter {{ $meter->meter_number }} • Periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $readingPeriod)->locale('id')->translatedFormat('F Y') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="meter-awal" class="text-sm font-semibold text-slate-500">METER AWAL (m³)</label>
                <input id="meter-awal" readonly value="{{ HitungPemakaianMeter::format($meterAwal) }}"
                    class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 p-4 text-lg text-[#12345A]">
            </div>
            <div>
                <label for="meter-akhir" class="text-sm font-semibold text-slate-500">METER AKHIR (m³)</label>
                <input id="meter-akhir" name="reading_value" type="text" inputmode="decimal" required maxlength="12"
                    placeholder="Masukkan meter akhir" value="{{ is_scalar(old('reading_value')) ? old('reading_value') : '' }}"
                    aria-describedby="meter-help meter-error" @if ($errors->has('reading_value')) aria-invalid="true" @endif
                    @disabled(! $bolehMencatat)
                    class="mt-3 block w-full rounded-xl border border-slate-200 bg-slate-50 p-4 text-lg text-[#12345A] disabled:cursor-not-allowed disabled:opacity-60">
                <p id="meter-help" class="mt-2 text-xs text-slate-500">Gunakan koma atau titik, maksimal 2 angka desimal.</p>
                <p id="meter-error" class="mt-2 text-sm text-red-700" role="status">{{ $errors->first('reading_value') }}</p>
            </div>
        </div>

        <x-total-pemakaian />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="reading-date" class="text-sm font-semibold text-slate-600">Tanggal pencatatan</label>
                <input id="reading-date" type="date" name="reading_date" required max="{{ now()->toDateString() }}"
                    value="{{ is_string(old('reading_date')) ? old('reading_date') : $defaultReadingDate }}"
                    @disabled(! $bolehMencatat) class="mt-2 block w-full rounded-xl border border-slate-200 p-3">
            </div>
            <div>
                <label for="meter-photo" class="text-sm font-semibold text-slate-600">Foto meter (opsional)</label>
                <input id="meter-photo" type="file" name="photo" accept="image/*" capture="environment"
                    @disabled(! $bolehMencatat) class="mt-2 block w-full text-sm">
            </div>
        </div>

        <div>
            <label for="meter-notes" class="text-sm font-semibold text-slate-600">Catatan (opsional)</label>
            <textarea id="meter-notes" name="notes" rows="3" maxlength="500" @disabled(! $bolehMencatat)
                class="mt-2 block w-full rounded-xl border border-slate-200 p-3">{{ is_string(old('notes')) ? old('notes') : '' }}</textarea>
        </div>

        <p class="text-sm text-slate-500">Angka meter akhir tidak boleh lebih kecil dari angka meter awal.</p>
        <div class="flex flex-wrap gap-4">
            <button type="submit" @disabled(! $bolehMencatat)
                class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Simpan &amp; Buat Tagihan</button>
            <a href="{{ route('petugas.meters.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 font-semibold text-[#1976D2]">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/meter.js') }}" defer></script>
@endpush
