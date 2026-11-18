@extends('layouts.petugas')
@section('title', 'Pencatatan Water Meter')
@section('content')
<h1>Pencatatan Water Meter</h1>
<p class="subtitle">Masukkan angka meter untuk menghitung pemakaian air periode berjalan.</p>
@if(session('success'))<div class="notice success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
<form class="filter card" action="{{ route('meter.index') }}" method="get">
    <label class="search"><span class="sr-only">Cari nama atau nomor meter</span><input name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor meter" maxlength="100"></label>
    <label><span class="sr-only">Wilayah</span><select name="wilayah"><option value="">Semua wilayah</option>@foreach($wilayah as $item)<option value="{{ $item }}" @selected(request('wilayah') === $item)>{{ $item }}</option>@endforeach</select></label>
    <button class="button" type="submit">Filter</button>
</form>
<div class="card table-wrap">
<table>
    <thead><tr><th>PELANGGAN</th><th>METER</th><th>WILAYAH</th><th>METER TERAKHIR</th><th class="action">AKSI</th></tr></thead>
    <tbody>
    @forelse($pelanggan as $orang)
        @php
            $terakhir = $orang->pencatatan->first();
            $sudah = $orang->pencatatan->contains('periode', $periode);
        @endphp
        <tr>
            <td><strong class="customer">{{ $orang->nama }}</strong><small>{{ $orang->kode }}</small></td>
            <td>{{ $orang->nomor_meter }}</td><td>{{ $orang->wilayah }}</td>
            <td><strong>{{ \App\Services\HitungPemakaianMeter::format($terakhir?->meter_akhir_satuan ?? $orang->meter_awal_satuan) }} m³</strong><small>{{ ($terakhir?->created_at?->timezone('Asia/Jakarta') ?? $orang->tanggal_meter_awal)->locale('id')->translatedFormat('d M Y') }}</small>@if($sudah)<span class="status">Menunggu pemeriksaan Bendahara</span>@endif</td>
            <td class="action"><a class="button compact" href="{{ route('meter.create', $orang) }}" aria-label="Catat {{ $orang->nama }}">Catat</a></td>
        </tr>
    @empty
        <tr><td colspan="5" class="empty">Pelanggan tidak ditemukan. Coba nama, nomor meter, atau wilayah lain.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
