@extends('layouts.app')

@section('title', 'Dashboard Monitoring & Rekapitulasi Tahunan (Bendahara BUMDes)')

@section('nav-items')
    <a href="{{ route('bendahara.dashboard') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Dashboard Monitoring</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ selectedSeason: '{{ $currentYear }}' }">

    {{-- Banner Utama Bendahara --}}
    <div class="bg-gradient-to-r from-[#12345A] to-[#1976D2] rounded-2xl p-6 sm:p-8 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-medium text-blue-100 mb-2">
                <span>📊</span> Bendahara BUMDes Karya Mandiri
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Sistem Monitoring Terpadu SatuAir</h1>
            <p class="text-blue-100 text-sm sm:text-base mt-1">
                Laporan Keuangan & Kinerja Layanan Air Bersih Antar-Tahun (Model Klasemen Musim).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-[#12345A] hover:bg-blue-50 text-xs font-bold rounded-xl shadow-sm transition">
                <span>🖨️</span> Cetak / Simpan PDF Laporan
            </button>
        </div>
    </div>

    {{-- Pemilih Klasemen / Musim Tahun Anggaran (Ide Klasemen LaLiga) --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-[#12345A] flex items-center gap-2">
                <span>🏆</span> Pilihan Tahun Anggaran (Musim Pembukuan)
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Pendapatan tiap tahun dihitung mandiri (reset ke 0 setiap 1 Januari) tanpa tertumpuk tahun lalu.</p>
        </div>
        <div class="inline-flex p-1 bg-gray-100 rounded-xl">
            <button type="button" @click="selectedSeason = '{{ $currentYear }}'"
                :class="selectedSeason === '{{ $currentYear }}' ? 'bg-[#1976D2] text-white shadow' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-1.5 rounded-lg text-xs font-bold transition">
                Tahun {{ $currentYear }} (Aktif)
            </button>
            <button type="button" @click="selectedSeason = '{{ $previousYear }}'"
                :class="selectedSeason === '{{ $previousYear }}' ? 'bg-[#1976D2] text-white shadow' : 'text-gray-600 hover:text-gray-900'"
                class="px-4 py-1.5 rounded-lg text-xs font-bold transition">
                Tahun {{ $previousYear }} (Arsip)
            </button>
        </div>
    </div>

    {{-- Ringkasan Indikator Keuangan & Operasional --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Pendapatan Tahun Ini --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Pendapatan Air {{ $currentYear }}</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">💰</span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600 mt-2">Rp {{ number_format($revenueThisYear, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">
                @if($revenueChange >= 0)
                    <span class="text-emerald-600 font-bold">▲ {{ $revenueChange }}%</span> dibanding tahun {{ $previousYear }}
                @else
                    <span class="text-rose-600 font-bold">▼ {{ abs($revenueChange) }}%</span> dibanding tahun {{ $previousYear }}
                @endif
            </p>
        </div>

        {{-- Pendapatan Tahun Lalu (Tersimpan Rapi) --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Arsip Kas {{ $previousYear }}</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#1976D2] flex items-center justify-center text-sm font-bold">📁</span>
            </div>
            <p class="text-2xl font-extrabold text-[#12345A] mt-2">Rp {{ number_format($revenuePreviousYear, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Tercatat di pembukuan final</p>
        </div>

        {{-- Total Pelanggan Air Terdaftar --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-purple-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-700">Pelanggan Aktif</span>
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">👥</span>
            </div>
            <p class="text-2xl font-extrabold text-purple-700 mt-2">{{ $totalMasyarakat }} KK</p>
            <p class="text-xs text-gray-500 mt-1">+{{ $newUsersThisMonth }} warga baru bulan ini</p>
        </div>

        {{-- Tingkat Penyelesaian Masalah --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-indigo-100">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">Penyelesaian Gangguan</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">🛠️</span>
            </div>
            <p class="text-2xl font-extrabold text-indigo-700 mt-2">{{ $resolvedComplaints }} / {{ $totalComplaints }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $pendingComplaints }} dalam antrean</p>
        </div>
    </div>

    {{-- Tabel Klasemen Kinerja Bulanan (Mirip Tabel Klasemen Liga) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-[#12345A]">Tabel Rekapitulasi Kas Masuk Per Bulan</h2>
                <p class="text-xs text-gray-500">Mencatat pendapatan terverifikasi dari warga untuk audit BUMDes</p>
            </div>
            <span class="text-xs font-mono font-bold px-3 py-1 bg-[#EAF4FF] text-[#1976D2] rounded-full">
                Musim Anggaran: <span x-text="selectedSeason"></span>
            </span>
        </div>

        {{-- Tampilan Musim Berjalan (Tahun Ini) --}}
        <div x-show="selectedSeason === '{{ $currentYear }}'" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F5F7FA] text-gray-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Bulan</th>
                        <th class="py-3 px-4">Keterangan Periode</th>
                        <th class="py-3 px-4">Status Buku</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan Terverifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach($monthlyRevenue as $monthNum => $amount)
                        <tr class="hover:bg-slate-50 transition {{ $monthNum === $activeMonth ? 'bg-blue-50/40' : '' }}">
                            <td class="py-3.5 px-4 font-bold text-[#12345A] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-[10px] font-bold">
                                    {{ $monthNum }}
                                </span>
                                {{ $namaBulan[$monthNum] }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 font-mono">
                                {{ sprintf('%04d-%02d', $currentYear, $monthNum) }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($activeMonth !== null && $monthNum === $activeMonth)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 animate-pulse">Sedang Berjalan</span>
                                @elseif($monthNum <= ($activeMonth ?? $lastClosedMonth))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Buku Ditutup</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Mendatang</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-[#12345A]">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-[#F5F7FA] font-bold text-xs text-[#12345A]">
                    <tr>
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase">Total Akumulasi Kas {{ $currentYear }}:</td>
                        <td class="py-3.5 px-4 text-right text-emerald-700 text-sm font-extrabold">
                            Rp {{ number_format($revenueThisYear, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Tampilan Musim Lalu (Arsip) --}}
        <div x-show="selectedSeason === '{{ $previousYear }}'" x-cloak class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F5F7FA] text-gray-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Bulan</th>
                        <th class="py-3 px-4">Keterangan Periode</th>
                        <th class="py-3 px-4">Status Buku</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan Terverifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($monthlyRevenuePreviousYear as $monthNum => $amount)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-[#12345A] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-[10px] font-bold">
                                    {{ $monthNum }}
                                </span>
                                {{ $namaBulan[$monthNum] }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 font-mono">
                                {{ sprintf('%04d-%02d', $previousYear, $monthNum) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Tutup Buku Final</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-[#12345A]">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-[#F5F7FA] font-bold text-xs text-[#12345A]">
                    <tr>
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase">Total Buku Final {{ $previousYear }}:</td>
                        <td class="py-3.5 px-4 text-right text-emerald-700 text-sm font-extrabold">
                            Rp {{ number_format($revenuePreviousYear, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
