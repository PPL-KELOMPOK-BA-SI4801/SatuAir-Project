@extends('layouts.app')

@section('title', 'Jadwal Pencatatan Meter')

@section('content')
<div class="space-y-6">
    {{-- Header Title --}}
    <div>
        <h1 class="text-2xl font-bold text-[#12345A]">Jadwal Pencatatan Meter</h1>
        <p class="text-sm text-gray-500 mt-1">Lihat pelanggan yang harus dicatat meternya pada periode ini agar tidak ada yang terlewat.</p>
    </div>

    @php
        $totalMeter = $meters->total();
        $activeYear = $activePeriod->year ?? now()->year;
        $activeMonth = $activePeriod->month ?? now()->month;

        $sudahDicatatCount = $meters->filter(function($m) use ($activeYear, $activeMonth) {
            if (!$m->latestReading) return false;
            $readingDate = \Carbon\Carbon::parse($m->latestReading->reading_date);
            return $readingDate->year == $activeYear && $readingDate->month == $activeMonth;
        })->count();

        $belumDicatatCount = max(0, $totalMeter - $sudahDicatatCount);
        $percent = $totalMeter > 0 ? ($sudahDicatatCount / $totalMeter) * 100 : 0;
    @endphp

    {{-- Statistik Card & Progress Bar --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div class="text-sm font-medium text-gray-700">
                <span class="font-bold text-gray-900">{{ $sudahDicatatCount }}</span> dari <span class="font-bold text-gray-900">{{ $totalMeter }}</span> pelanggan sudah dicatat
            </div>
            
            <div class="flex items-center gap-6 text-sm divide-x divide-gray-200">
                <div class="pl-4">
                    <span class="text-lg font-bold text-amber-600">{{ $belumDicatatCount }}</span>
                    <span class="block text-xs text-gray-400">Belum dicatat</span>
                </div>
                <div class="pl-6">
                    <span class="text-lg font-bold text-rose-600">0</span>
                    <span class="block text-xs text-gray-400">Terlambat</span>
                </div>
                <div class="pl-6">
                    <span class="text-lg font-bold text-emerald-600">{{ $sudahDicatatCount }}</span>
                    <span class="block text-xs text-gray-400">Sudah dicatat</span>
                </div>
                <div class="pl-6">
                    <span class="text-lg font-bold text-gray-800">31 Okt</span>
                    <span class="block text-xs text-gray-400">Batas pencatatan</span>
                </div>
            </div>
        </div>
        {{-- Progress Bar Line --}}
        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
            <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%;"></div>
        </div>
    </div>

    {{-- Filter Section --}}
    <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
        <div>
            <select name="period" class="w-full rounded-xl border-gray-200 text-sm">
                <option value="">Oktober 2026 (terbuka)</option>
            </select>
        </div>
        <div>
            <select name="area_id" class="w-full rounded-xl border-gray-200 text-sm">
                <option value="">Semua wilayah</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau nomor meter"
                   class="w-full rounded-xl border-gray-200 text-sm">
        </div>
        <div>
            <button class="w-full px-4 py-2.5 bg-[#1976D2] text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition">Filter</button>
        </div>
    </form>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#F5F7FA] text-gray-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3">Pelanggan</th>
                        <th class="text-left px-4 py-3">No. Meter</th>
                        <th class="text-left px-4 py-3">Wilayah</th>
                        <th class="text-left px-4 py-3">Jadwal Catat</th>
                        <th class="text-left px-4 py-3">Meter Terakhir</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($meters as $meter)
                        @php
                            $isRecorded = false;
                            if ($meter->latestReading) {
                                $readingDate = \Carbon\Carbon::parse($meter->latestReading->reading_date);
                                $isRecorded = ($readingDate->year == $activeYear && $readingDate->month == $activeMonth);
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-[#12345A]">{{ $meter->user->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $meter->user->customer_id ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $meter->meter_number }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $meter->area->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="text-gray-800 font-medium text-xs">Sab, 31 Okt 2026</p>
                                <p class="text-[11px] text-amber-600">Besok</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($meter->latestReading)
                                    <p class="font-semibold text-gray-800">{{ $meter->latestReading->reading_value }} m³</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($meter->latestReading->reading_date)->format('d M Y') }}</p>
                                @else
                                    <span class="text-xs text-amber-600 font-medium">Belum ada pencatatan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($isRecorded)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        SUDAH DICATAT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        BELUM DICATAT
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($isRecorded)
                                    <span class="text-xs text-gray-400 font-medium">Selesai</span>
                                @else
                                    <a href="{{ route('petugas.meters.create', $meter->id) }}"
                                       class="inline-flex px-3 py-1.5 border border-[#1976D2] text-[#1976D2] hover:bg-[#1976D2] hover:text-white text-xs font-semibold rounded-lg transition">
                                        Catat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada meter aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $meters->links() }}</div>
    </div>
</div>
@endsection