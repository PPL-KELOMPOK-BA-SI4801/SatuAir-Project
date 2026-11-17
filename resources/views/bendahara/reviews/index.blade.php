@extends('layouts.app')
@section('title', 'Pemeriksaan Pemakaian')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl sm:text-3xl font-bold text-[#12345A]">Pemeriksaan Pemakaian</h1>
        <p class="mt-2 text-slate-500">Periksa pencatatan Petugas sebelum pemakaian digunakan untuk tagihan.</p></div>
    <p class="inline-block rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">Belum disetujui: <strong>{{ $pending }}</strong> pencatatan</p>
    @include('bendahara.reviews.errors')
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 rounded-2xl border border-slate-200 bg-white p-5">
        <label class="text-sm text-slate-600">Pelanggan atau meter<input name="search" value="{{ request('search') }}" placeholder="Cari pelanggan atau meter" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <label class="text-sm text-slate-600">Periode<input type="month" name="period" value="{{ request('period') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <label class="text-sm text-slate-600">Status<select name="status" class="mt-2 w-full rounded-xl border border-slate-200 p-3">
            <option value="">Semua status</option>
            @foreach (['pending'=>'Belum disetujui','draft'=>'Draf','returned'=>'Dikembalikan','approved'=>'Disetujui'] as $value=>$label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
        </select></label>
        <div class="flex items-end gap-3"><button class="rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">Filter</button><a href="{{ route('bendahara.reviews.index') }}" class="py-3 text-[#1976D2]">Reset</a></div>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr>
            <th class="p-4">Pelanggan / Meter</th><th class="p-4">Periode</th><th class="p-4">Meter awal → akhir</th><th class="p-4">Pemakaian</th><th class="p-4">Status</th><th class="p-4">Aksi</th>
        </tr></thead><tbody class="divide-y divide-slate-100">
        @forelse ($readings as $reading)<tr>
            <td class="p-4"><p class="font-semibold text-[#12345A]">{{ $reading->waterMeter->user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $reading->waterMeter->meter_number }}</p></td>
            <td class="p-4">{{ $reading->reading_date->locale('id')->translatedFormat('F Y') }}</td>
            <td class="p-4">{{ str_replace('.', ',', $reading->previous_value ?? '0.00') }} → {{ str_replace('.', ',', $reading->reading_value) }} m³</td>
            <td class="p-4 font-semibold text-[#12345A]">{{ str_replace('.', ',', $usages[$reading->id]) }} m³</td>
            <td class="p-4 text-[#1976D2]">{{ $reading->reviewLabel() }}</td>
            <td class="p-4"><a href="{{ route('bendahara.reviews.reading', $reading) }}" class="font-semibold text-[#1976D2]" aria-label="Periksa {{ $reading->waterMeter->user->name }}">Rincian</a></td>
        </tr>@empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada pencatatan untuk pemeriksaan. Catatan lama tetap berada pada riwayat dan tagihan sebelumnya.</td></tr>@endforelse
        </tbody></table><div class="p-4">{{ $readings->links() }}</div>
    </div>
</div>
@endsection
