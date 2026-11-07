@extends('layouts.app')

@section('title', 'Detail Pembayaran — SatuAir')

@section('nav-items')
    <a href="{{ route('petugas.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('petugas.payments.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Pembayaran</a>
    <a href="{{ route('petugas.meters.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Catat Meter</a>
    <a href="{{ route('petugas.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pengaduan</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Kembali --}}
    <div>
        <a href="{{ route('petugas.payments.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pembayaran
        </a>
    </div>

    {{-- Header Detail --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-[#12345A] to-[#1976D2] text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-blue-200 mb-1">Detail Transaksi Pembayaran</p>
                    <h1 class="text-xl font-bold">No. Referensi: {{ $payment->reference_number }}</h1>
                    <p class="text-xs text-blue-100 mt-1">
                        {{ $payment->user->name ?? '-' }} &bull; {{ $payment->waterBill->billing_period ?? '-' }}
                    </p>
                </div>
                <div class="text-right">
                    @php
                        $badgeClasses = match($payment->status) {
                            'verified' => 'bg-emerald-100 text-emerald-800',
                            'pending' => 'bg-amber-100 text-amber-800',
                            'rejected' => 'bg-rose-100 text-rose-800',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        $statusLabel = match(true) {
                            $payment->status === 'verified' => 'Lunas / Dikonfirmasi',
                            $payment->status === 'pending' && $payment->paid_at => 'Menunggu Verifikasi',
                            $payment->status === 'pending' => 'Menunggu Pembayaran',
                            $payment->status === 'rejected' => 'Ditolak',
                            default => $payment->status,
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badgeClasses }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Info Pembayaran --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Pelanggan</p>
                    <p class="text-sm font-bold text-[#12345A]">{{ $payment->user->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">ID: {{ $payment->user->customer_id ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Meter: {{ $payment->waterBill->waterMeter->meter_number ?? '-' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Tagihan</p>
                    <p class="text-sm font-bold text-[#12345A]">Periode {{ $payment->waterBill->billing_period }}</p>
                    <p class="text-xs text-gray-500 mt-1">Pemakaian: {{ $payment->waterBill->usage_m3 }} m³</p>
                    <p class="text-xs text-gray-500 mt-1">Jatuh Tempo: {{ \Carbon\Carbon::parse($payment->waterBill->due_date)->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Ringkasan Biaya --}}
            <div class="border-t border-b border-gray-100 py-4 space-y-2 text-xs">
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Pemakaian Air</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($payment->waterBill->usage_m3 * $payment->waterBill->rate_per_m3, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Biaya Administrasi</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($payment->waterBill->admin_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t border-dashed border-gray-200 pt-3 text-sm font-bold text-[#12345A]">
                    <span>Total Pembayaran</span>
                    <span class="text-[#1976D2]">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Metode Pembayaran</span>
                    <span class="font-semibold text-gray-800 uppercase">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Tanggal Dibuat</span>
                    <span class="font-semibold text-gray-800">{{ $payment->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if($payment->paid_at)
                    <div class="flex justify-between text-gray-600">
                        <span>Tanggal Dibayar</span>
                        <span class="font-semibold text-gray-800">{{ $payment->paid_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
                @if($payment->verified_by && $payment->status === 'verified')
                    <div class="flex justify-between text-gray-600">
                        <span>Diverifikasi Oleh</span>
                        <span class="font-semibold text-gray-800">{{ optional($payment->verifier)->name ?? 'Petugas' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Waktu Verifikasi</span>
                        <span class="font-semibold text-gray-800">{{ $payment->verified_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
                @if($payment->notes)
                    <div class="flex justify-between text-gray-600">
                        <span>Catatan</span>
                        <span class="font-semibold text-gray-800">{{ $payment->notes }}</span>
                    </div>
                @endif
            </div>

            @if($payment->status === 'pending' && $payment->paid_at)
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('petugas.payments.verify', $payment->id) }}">@csrf<button class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl">Verifikasi Pembayaran</button></form>
                    <form method="POST" action="{{ route('petugas.payments.reject', $payment->id) }}" class="flex gap-2">@csrf<input name="notes" class="rounded-xl border-gray-200 text-sm" placeholder="Alasan penolakan"><button class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl">Tolak</button></form>
                </div>
            @elseif($payment->status === 'pending')
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm font-semibold text-amber-800">Warga belum menyelesaikan pembayaran.</div>
            @elseif($payment->status === 'verified')
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="font-semibold text-emerald-800">Pembayaran telah diverifikasi dan tagihan ditandai LUNAS.</span>
                    </div>
                </div>
            @elseif($payment->status === 'rejected')
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <span class="font-semibold text-rose-800">Pembayaran ditolak. Warga dapat mengajukan pembayaran ulang.</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
