@extends('layouts.app')

@section('title', 'Pencatatan Meter')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[#12345A]">Pencatatan Water Meter</h1>
            <p class="text-sm text-gray-500 mt-1">Masukkan angka meter dan foto. Tagihan periode berjalan dibuat otomatis.</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau nomor meter"
               class="rounded-xl border-gray-200 text-sm">
        <select name="area_id" class="rounded-xl border-gray-200 text-sm">
            <option value="">Semua wilayah</option>
            @foreach($areas as $area)
                <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2.5 bg-[#1976D2] text-white text-sm font-semibold rounded-xl">Filter</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#F5F7FA] text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Pelanggan</th>
                        <th class="text-left px-4 py-3">Meter</th>
                        <th class="text-left px-4 py-3">Wilayah</th>
                        <th class="text-left px-4 py-3">Meter terakhir</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($meters as $meter)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-[#12345A]">{{ $meter->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $meter->user->customer_id ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $meter->meter_number }}</td>
                            <td class="px-4 py-3 text-xs">{{ $meter->area->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($meter->latestReading)
                                    <p class="font-semibold">{{ $meter->latestReading->reading_value }} m³</p>
                                    <p class="text-xs text-gray-500">{{ $meter->latestReading->reading_date?->format('d M Y') }}</p>
                                @else
                                    <span class="text-xs text-amber-600">Belum ada pencatatan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('petugas.meters.create', $meter->id) }}"
                                   class="inline-flex px-3 py-1.5 bg-[#1976D2] text-white text-xs font-semibold rounded-lg">
                                    Catat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada meter aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $meters->links() }}</div>
    </div>
</div>
@endsection
