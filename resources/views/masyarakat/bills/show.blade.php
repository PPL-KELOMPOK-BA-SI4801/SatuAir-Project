@extends('layouts.app')

@section('title', 'Detail Tagihan ' . $bill->billing_period)

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Tombol Kembali --}}
    <div>
        <a href="{{ route('masyarakat.bills.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Tagihan
        </a>
    </div>

    {{-- Kartu Utama Detail Tagihan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Header Status --}}
        <div class="p-6 bg-gradient-to-r from-[#12345A] to-[#1976D2] text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-blue-200">Surat Tagihan Pemakaian Air Bersih</p>
                <h1 class="text-2xl font-bold mt-1">Periode {{ $bill->billing_period }}</h1>
                <p class="text-xs text-blue-100 mt-1">BUMDes Karya Mandiri • Unit Pengelola Air Bersih (PAB)</p>
            </div>
            <div>
                @php
                    $paymentAwaitingConfirmation = $bill->payment?->status === 'pending' && $bill->payment?->paid_at;
                    $paymentAwaitingSubmission = $bill->payment?->status === 'pending' && !$bill->payment?->paid_at;
                    $badgeBg = $paymentAwaitingConfirmation ? 'bg-blue-500 text-white' : match($bill->status) {
                        'paid' => 'bg-emerald-500 text-white',
                        'unpaid' => 'bg-amber-400 text-slate-900',
                        'overdue' => 'bg-rose-500 text-white',
                        default => 'bg-white/20 text-white',
                    };
                    $statusLabel = $paymentAwaitingConfirmation ? 'MENUNGGU KONFIRMASI' : ($paymentAwaitingSubmission ? 'MENUNGGU PEMBAYARAN' : match($bill->status) {
                        'paid' => 'LUNAS',
                        'unpaid' => 'MENUNGGU PEMBAYARAN',
                        'overdue' => 'JATUH TEMPO',
                        default => strtoupper($bill->status),
                    });
                @endphp
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black tracking-wider {{ $badgeBg }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- Isi Rincian --}}
        <div class="p-6 space-y-6">
            {{-- Info Pelanggan & Meteran --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-[#F5F7FA] text-xs">
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Nama Pelanggan</span>
                    <span class="font-bold text-[#12345A] text-sm">{{ $bill->user->name ?? '-' }}</span>
                    <span class="text-gray-500 block mt-0.5">ID: {{ $bill->user->customer_id ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px] uppercase font-semibold">Nomor Meteran Air</span>
                    <span class="font-bold text-[#12345A] text-sm font-mono">{{ $bill->waterMeter->meter_number ?? '-' }}</span>
                    <span class="text-gray-500 block mt-0.5">Lokasi: {{ $bill->waterMeter->location ?? $bill->user->address ?? '-' }}</span>
                </div>
            </div>

            {{-- Kalkulasi Pemakaian Meteran --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Rincian Angka Meteran</h3>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl border border-gray-200">
                        <span class="text-[11px] text-gray-500 block">Meter Lalu</span>
                        <span class="text-base sm:text-lg font-bold font-mono text-gray-700">{{ $bill->previous_reading }} m³</span>
                    </div>
                    <div class="p-3 rounded-xl border border-gray-200">
                        <span class="text-[11px] text-gray-500 block">Meter Kini</span>
                        <span class="text-base sm:text-lg font-bold font-mono text-gray-700">{{ $bill->current_reading }} m³</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#EAF4FF] border border-blue-200">
                        <span class="text-[11px] text-[#1976D2] font-semibold block">Total Pemakaian</span>
                        <span class="text-base sm:text-lg font-extrabold font-mono text-[#1976D2]">{{ $bill->usage_m3 }} m³</span>
                    </div>
                </div>
            </div>

            {{-- Breakdown Biaya --}}
            <div class="border-t border-b border-gray-100 py-4 space-y-2 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Pemakaian Air ({{ $bill->usage_m3 }} m³ × Rp {{ number_format($bill->rate_per_m3, 0, ',', '.') }})</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($bill->usage_m3 * $bill->rate_per_m3, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Administrasi & Pemeliharaan Jaringan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($bill->admin_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Batas Waktu Pembayaran (Jatuh Tempo)</span>
                    <span class="font-semibold text-amber-700">{{ \Carbon\Carbon::parse($bill->due_date)->format('d F Y') }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-dashed border-gray-200 text-sm">
                    <span class="font-bold text-[#12345A]">Total Kewajiban Pembayaran</span>
                    <span class="text-xl font-extrabold text-[#1976D2]">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Status Pembayaran / Bukti Pembayaran --}}
            @if($bill->payment)
                <div class="p-4 rounded-xl bg-slate-50 border border-gray-200 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#12345A]">Informasi Pembayaran Terakhir</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                            {{ $bill->payment->status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $bill->payment->status === 'verified' ? 'Pembayaran Dikonfirmasi' : ($bill->payment->paid_at ? 'Menunggu Konfirmasi Admin' : 'Menunggu Pembayaran') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-gray-600">
                        <div>
                            <span class="text-gray-400 block text-[10px]">Metode</span>
                            <span class="font-semibold text-gray-800 uppercase">{{ $bill->payment->payment_method }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">No. Referensi</span>
                            <span class="font-mono text-gray-800">{{ $bill->payment->reference_number ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Tanggal Bayar</span>
                            <span>{{ $bill->payment->paid_at ? \Carbon\Carbon::parse($bill->payment->paid_at)->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px]">Jumlah Dibayar</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($bill->payment->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Aksi --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                @if($bill->status === 'unpaid' && $activePeriod?->billing_period === $bill->billing_period && !$paymentAwaitingConfirmation && !$paymentAwaitingSubmission)
                    <a href="{{ route('masyarakat.payments.create', $bill->id) }}" class="w-full sm:flex-1 py-3 px-4 bg-[#1976D2] hover:bg-[#1565C0] text-white text-center font-bold text-sm rounded-xl shadow-sm transition">
                        💳 Bayar Sekarang Secara Online
                    </a>
                @elseif($paymentAwaitingConfirmation)
                    <span class="w-full sm:flex-1 py-3 px-4 bg-blue-50 text-blue-800 text-center font-bold text-sm rounded-xl">Menunggu Konfirmasi Admin</span>
                @elseif($paymentAwaitingSubmission)
                    <a href="{{ route('masyarakat.payments.instruction', $bill->payment->id) }}" class="w-full sm:flex-1 py-3 px-4 bg-amber-50 text-amber-800 text-center font-bold text-sm rounded-xl">Lanjutkan Pembayaran</a>
                @endif
                <button onclick="window.print()" class="w-full sm:w-auto py-3 px-5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center font-semibold text-xs rounded-xl transition">
                    🖨️ Cetak Bukti Tagihan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
