@extends('layouts.petugas')
@section('title', 'Catat Pemakaian Air')
@section('content')
<section class="meter-entry">
<h1>Catat Pemakaian Air</h1>
<p class="subtitle">Masukkan angka meter terbaru. Pemakaian dihitung dari selisih angka meter.</p>
@if($errors->any())
    <div class="notice error" role="alert"><strong>Pencatatan belum disimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if($tercatat)<div class="notice warning" role="status">Pelanggan ini sudah dicatat pada periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y') }}. Status: menunggu pemeriksaan Bendahara.</div>@endif
<form class="card record-form" action="{{ route('meter.store', $pelanggan) }}" method="post" id="meter-form" data-awal="{{ $meterAwal }}" data-invalid="{{ $errors->any() ? '1' : '0' }}">
    @csrf
    <input type="hidden" name="periode" value="{{ $periode }}">
    <h2>{{ $pelanggan->nama }} · {{ $pelanggan->kode }}</h2>
    <p class="meter-details">Nomor meter {{ $pelanggan->nomor_meter }} • Periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y') }}</p>
    <div class="field-grid">
        <label for="meter-awal">METER AWAL (m³)<input id="meter-awal" readonly value="{{ \App\Services\HitungPemakaianMeter::format($meterAwal) }}" aria-readonly="true"></label>
        <label for="meter-akhir">METER AKHIR (m³)<input id="meter-akhir" name="meter_akhir" type="text" inputmode="decimal" placeholder="Masukkan meter akhir" value="{{ is_scalar(old('meter_akhir')) ? old('meter_akhir') : '' }}" required maxlength="12" aria-describedby="meter-help meter-error" @if($errors->has('meter_akhir')) aria-invalid="true" @endif @disabled($tercatat)><small id="meter-help" class="sr-only">Gunakan koma atau titik, maksimal 2 angka desimal.</small><span id="meter-error" class="field-error" role="status">{{ $errors->first('meter_akhir') }}</span></label>
    </div>
    <div class="calculation" aria-live="polite" aria-atomic="true">
        <div><span>Total Pemakaian</span><strong id="total-pemakaian">— m³</strong></div>
        <p id="rumus">Isi meter akhir yang valid untuk melihat perhitungan.</p>
    </div>
    <p class="meter-guidance">Angka meter akhir tidak boleh lebih kecil dari angka meter awal.</p>
    <div class="form-actions"><button class="button" type="submit" @disabled($tercatat)>Simpan Pencatatan</button><a class="button secondary" href="{{ route('meter.index') }}">Batal</a></div>
</form>
<p class="review-note">Pencatatan yang disimpan akan menunggu pemeriksaan Bendahara.</p>
</section>
@endsection
