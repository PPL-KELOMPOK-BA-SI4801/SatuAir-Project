@extends('layouts.petugas')
@section('title', 'Catat Pemakaian Air')
@section('content')
<a class="back" href="{{ route('meter.index') }}">← Kembali ke pencatatan meter</a>
<h1>Catat Pemakaian Air</h1>
<p class="subtitle">Masukkan meter akhir untuk menghitung total pemakaian air pelanggan.</p>
@if($errors->any())
    <div class="notice error" role="alert"><strong>Pencatatan belum disimpan.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if($tercatat)<div class="notice warning" role="status">Pelanggan ini sudah dicatat pada periode {{ \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y') }}. Status: menunggu pemeriksaan Bendahara.</div>@endif
<form class="card record-form" action="{{ route('meter.store', $pelanggan) }}" method="post" id="meter-form" data-awal="{{ $meterAwal }}" data-invalid="{{ $errors->any() ? '1' : '0' }}">
    @csrf
    <input type="hidden" name="periode" value="{{ $periode }}">
    <h2>Informasi Pelanggan</h2>
    <div class="field-grid">
        <label>Pelanggan<input readonly value="{{ $pelanggan->nama }}" aria-readonly="true"><small>{{ $pelanggan->kode }}</small></label>
        <label>Nomor Meter<input readonly value="{{ $pelanggan->nomor_meter }}" aria-readonly="true"></label>
        <label>Wilayah<input readonly value="{{ $pelanggan->wilayah }}" aria-readonly="true"></label>
        <label>Periode<input readonly value="{{ \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y') }}" aria-readonly="true"></label>
    </div>
    <div class="separator"></div>
    <h2>Pencatatan Meter</h2>
    <div class="field-grid">
        <label>Meter Awal (m³)<input id="meter-awal" readonly value="{{ \App\Services\HitungPemakaianMeter::format($meterAwal) }}" aria-readonly="true"><small>Angka meter terakhir dari database.</small></label>
        <label for="meter-akhir">Meter Akhir (m³)<input id="meter-akhir" name="meter_akhir" type="text" inputmode="decimal" placeholder="Masukkan meter akhir" value="{{ is_scalar(old('meter_akhir')) ? old('meter_akhir') : '' }}" required maxlength="12" aria-describedby="meter-help meter-error" @if($errors->has('meter_akhir')) aria-invalid="true" @endif @disabled($tercatat)><small id="meter-help">Gunakan koma atau titik, maksimal 2 angka desimal.</small><span id="meter-error" class="field-error" role="status">{{ $errors->first('meter_akhir') }}</span></label>
    </div>
    <div class="calculation" aria-live="polite" aria-atomic="true">
        <div><span>Total Pemakaian</span><strong id="total-pemakaian">— m³</strong></div>
        <p id="rumus">Isi meter akhir yang valid untuk melihat perhitungan.</p>
    </div>
    <p class="review-note">Hasil pencatatan akan menunggu pemeriksaan Bendahara.</p>
    <div class="form-actions"><a class="button secondary" href="{{ route('meter.index') }}">Batal</a><button class="button" type="submit" @disabled($tercatat)>Simpan Pencatatan</button></div>
</form>
@endsection
