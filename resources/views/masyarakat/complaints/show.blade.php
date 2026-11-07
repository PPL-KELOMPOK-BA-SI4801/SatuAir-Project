@extends('layouts.app')

@section('title', 'Lacak Laporan ' . $complaint->ticket_number)

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Tombol Kembali --}}
    <div>
        <a href="{{ route('masyarakat.complaints.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Laporan
        </a>
    </div>

    {{-- Kartu Status & Info Tiket --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="p-6 bg-[#12345A] text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-blue-200">Nomor Tiket Pengaduan</p>
                <h1 class="text-2xl font-bold font-mono mt-1">{{ $complaint->ticket_number }}</h1>
                <p class="text-xs text-blue-100 mt-1">
                    Diajukan pada {{ $complaint->created_at ? $complaint->created_at->format('d F Y, H:i') : '-' }} WIB
                </p>
            </div>
            <div>
                @php
                    $badgeBg = match($complaint->status) {
                        'pending' => 'bg-amber-400 text-slate-900',
                        'verified' => 'bg-blue-400 text-slate-900',
                        'in_progress' => 'bg-indigo-400 text-white',
                        'resolved' => 'bg-emerald-500 text-white',
                        'rejected' => 'bg-rose-500 text-white',
                        default => 'bg-gray-400 text-white',
                    };
                    $statusLabel = match($complaint->status) {
                        'pending' => 'MENUNGGU VERIFIKASI',
                        'verified' => 'TERVERIFIKASI',
                        'in_progress' => 'SEDANG DITANGANI',
                        'resolved' => 'SELESAI DITANGANI',
                        'rejected' => 'LAPORAN DITOLAK',
                        default => strtoupper($complaint->status),
                    };
                @endphp
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black tracking-wider {{ $badgeBg }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- Progress Bar / Timeline 4 Langkah --}}
        <div class="p-6 bg-slate-50 border-b border-gray-100">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Tahapan Penanganan</h3>
            @php
                $steps = [
                    'pending' => 1,
                    'verified' => 2,
                    'in_progress' => 3,
                    'resolved' => 4,
                ];
                $currentStep = $steps[$complaint->status] ?? ($complaint->status === 'rejected' ? 0 : 1);
            @endphp

            @if($complaint->status === 'rejected')
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <p class="font-bold">❌ Laporan Ditolak Petugas</p>
                    <p class="mt-1">Alasan: {{ $complaint->rejection_reason ?? 'Informasi laporan tidak sesuai atau di luar kewenangan operasional PAB.' }}</p>
                </div>
            @else
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <div class="space-y-1">
                        <div class="h-2 rounded-full {{ $currentStep >= 1 ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                        <p class="font-semibold {{ $currentStep >= 1 ? 'text-emerald-700' : 'text-gray-400' }}">1. Masuk</p>
                    </div>
                    <div class="space-y-1">
                        <div class="h-2 rounded-full {{ $currentStep >= 2 ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                        <p class="font-semibold {{ $currentStep >= 2 ? 'text-emerald-700' : 'text-gray-400' }}">2. Verifikasi</p>
                    </div>
                    <div class="space-y-1">
                        <div class="h-2 rounded-full {{ $currentStep >= 3 ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                        <p class="font-semibold {{ $currentStep >= 3 ? 'text-emerald-700' : 'text-gray-400' }}">3. Ditangani</p>
                    </div>
                    <div class="space-y-1">
                        <div class="h-2 rounded-full {{ $currentStep >= 4 ? 'bg-emerald-500' : 'bg-gray-200' }}"></div>
                        <p class="font-semibold {{ $currentStep >= 4 ? 'text-emerald-700' : 'text-gray-400' }}">4. Selesai</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Detail Informasi --}}
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Jenis Masalah</span>
                    <span class="font-bold text-[#12345A] text-sm">{{ $complaint->category->name ?? 'Gangguan Umum' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Wilayah</span>
                    <span class="font-bold text-[#12345A] text-sm">{{ $complaint->area->name ?? 'Wilayah layanan' }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Lokasi Kejadian</span>
                    <span class="font-medium text-gray-800">{{ $complaint->location }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Deskripsi Kendala</span>
                    <div class="p-4 rounded-xl bg-[#F5F7FA] text-gray-800 text-xs mt-1 leading-relaxed border border-gray-100">
                        {{ $complaint->description }}
                    </div>
                </div>
                @if($complaint->photo)
                    <div class="sm:col-span-2">
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold mb-2">Foto Kondisi dari Pelapor</span>
                        <img src="{{ asset('storage/' . $complaint->photo) }}" class="rounded-xl max-h-64 object-cover border border-gray-200">
                    </div>
                @endif
            </div>

            {{-- Riwayat Perkembangan / Log Pembaruan dari Petugas --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Catatan Perkembangan Petugas Lapangan</h3>
                @if($complaint->updates->isEmpty())
                    <p class="text-xs text-gray-400 italic">Belum ada catatan tindak lanjut dari petugas lapangan.</p>
                @else
                    <div class="space-y-4">
                        @foreach($complaint->updates as $update)
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-gray-100">
                                <div class="w-2 h-2 rounded-full bg-[#1976D2] mt-1.5 shrink-0"></div>
                                <div class="space-y-1 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-[#12345A]">{{ $update->user->name ?? 'Petugas PAB' }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $update->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">{{ $update->notes }}</p>
                                    @if($update->photo)
                                        <img src="{{ asset('storage/' . $update->photo) }}" class="rounded-lg max-h-36 mt-2 object-cover border border-gray-200">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
