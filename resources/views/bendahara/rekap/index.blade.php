@extends('layouts.app')

@section('title', 'Rekapitulasi Keuangan — SatuAir')

@section('nav-items')
    <a href="{{ route('bendahara.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('bendahara.rekap.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Rekapitulasi</a>
    <a href="{{ route('bendahara.pengguna.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pengguna</a>
    <a href="{{ route('bendahara.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pengaduan</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Banner --}}
    <div class="bg-gradient-to-r from-[#12345A] to-[#1976D2] rounded-2xl p-6 sm:p-8 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-blue-200 mb-1">Rekapitulasi Keuangan BUMDes</p>
            <h1 class="text-2xl font-bold">Laporan Pendapatan Air Bersih</h1>
            <p class="text-blue-100 text-sm mt-1">Mencatat pendapatan terverifikasi dari warga untuk audit BUMDes</p>
        </div>
        <a href="{{ route('bendahara.pembayaran') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-[#12345A] hover:bg-blue-50 text-sm font-semibold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Pembayaran
        </a>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-emerald-100">
            <span class="text-xs font-bold uppercase text-emerald-700 block mb-2">Pendapatan Tahun {{ $currentYear }}</span>
            <p class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($revenueThisYear, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Tahun berjalan</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100">
            <span class="text-xs font-bold uppercase text-blue-700 block mb-2">Arsip Kas {{ $previousYear }}</span>
            <p class="text-2xl font-extrabold text-[#12345A]">Rp {{ number_format($revenuePreviousYear, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-1">Tercatat di pembukuan final</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100">
            <span class="text-xs font-bold uppercase text-amber-700 block mb-2">Tagihan Belum Lunas</span>
            <p class="text-2xl font-extrabold text-amber-600">{{ $arrears->count() }} tagihan</p>
            <p class="text-xs text-gray-500 mt-1">Warga belum bayar</p>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <span class="text-xs font-bold uppercase text-slate-700 block mb-2">Status Buku</span>
            @if($currentLocked)
                <p class="text-2xl font-extrabold text-emerald-600">Terkunci</p>
                <p class="text-xs text-gray-500 mt-1">Pembukuan tahun {{ $currentYear }} sudah ditutup</p>
            @else
                <p class="text-2xl font-extrabold text-blue-600">Belum Tertutup</p>
                <p class="text-xs text-gray-500 mt-1">Dapat diedit</p>
            @endif
        </div>
    </div>

    {{-- Tabel Per Bulan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-[#12345A]">Tabel Rekapitulasi Kas Masuk Per Bulan</h2>
                <p class="text-xs text-gray-500">Tahun {{ $currentYear }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F5F7FA] text-gray-600 uppercase font-semibold border-b border-gray-100">
                    <tr>
                        <th class="py-3 px-4">Bulan</th>
                        <th class="py-3 px-4">Keterangan Periode</th>
                        <th class="py-3 px-4">Status Buku</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan Terverifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @php
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    @foreach($monthlyThisYear as $monthNum => $amount)
                        <tr class="hover:bg-slate-50 transition">
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
    </div>

    {{-- Tombol Kunci Buku (Bendahara BUMDes Only) --}}
    @if(!$currentLocked)
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-amber-100">
            <h3 class="text-xs font-bold uppercase text-amber-700 mb-3">Kunci Pembukuan Tahun {{ $currentYear }}</h3>
            <p class="text-xs text-gray-600 mb-4">Setelah dikunci, angka pendapatan tahun ini tidak dapat diubah lagi dan akan disimpan sebagai arsip final.</p>
            <form method="POST" action="{{ route('bendahara.rekap.lock') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $currentYear }}">
                <button type="submit" class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm shadow-amber-600/20 transition active:scale-[0.99]">
                    Kunci Buku Sekarang
                </button>
            </form>
        </div>
    @else
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-800">Pembukuan Tahun {{ $currentYear }} Sudah Dikunci</p>
                    <p class="text-xs text-emerald-600 mt-1">Angka pendapatan telah disimpan sebagai arsip final.</p>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
