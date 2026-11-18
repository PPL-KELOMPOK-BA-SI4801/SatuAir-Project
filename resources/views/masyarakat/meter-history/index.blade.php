
@extends('layouts.app')

@section('title', 'Riwayat Meter')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-[#12345A]">Riwayat Meter</h1>
        <p class="text-sm text-gray-500 mt-1">
            Pantau riwayat penggunaan air berdasarkan pencatatan meter Anda.
        </p>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Pencatatan</p>
            <h2 dusk="history-count" class="text-2xl font-bold text-[#12345A] mt-2">
                {{ $totalRecords }}
            </h2>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <p class="text-sm text-gray-500">Total Penggunaan Air</p>
            <h2 dusk="history-usage" class="text-2xl font-bold text-blue-600 mt-2">
                {{ number_format($totalUsage, 2, ',', '.') }} m³
            </h2>
        </div>
    </div>

    {{-- Search dan Filter --}}
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('masyarakat.meter-history.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3">

            <input type="date" name="search"
                   value="{{ request('search') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">

            <select name="month"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Bulan</option>
                @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}"
                        @selected(request('month') == $month)>
                        {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <select name="year"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tahun</option>
                @foreach($years as $year)
                    <option value="{{ $year }}"
                        @selected(request('year') == $year)>
                        {{ $year }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-[#12345A] text-white rounded-lg px-4 py-2 text-sm">
                    Cari
                </button>

                <a href="{{ route('masyarakat.meter-history.index') }}"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-bold text-[#12345A]">Daftar Riwayat Pencatatan</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Nomor Meter</th>
                        <th class="px-5 py-3">Meter Sebelumnya</th>
                        <th class="px-5 py-3">Meter Saat Ini</th>
                        <th class="px-5 py-3">Penggunaan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($readings as $reading)
                        <tr>
                            <td class="px-5 py-4">
                                {{ $reading->reading_date->format('d M Y') }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $reading->waterMeter->meter_number ?? '-' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $reading->previous_value ?? '-' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $reading->reading_value }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-blue-600">
                                {{ $reading->previous_value !== null
                                    ? number_format($reading->reading_value - $reading->previous_value, 2, ',', '.') . ' m³'
                                    : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                Belum ada riwayat pencatatan meter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-gray-100">
            {{ $readings->links() }}
        </div>
    </div>
</div>
@endsection
