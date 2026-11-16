@extends('layouts.app')

@section('title', 'Dashboard Masyarakat')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan #{{ $user->customer_id ?? '-' }}</p>
            <h1 class="text-2xl font-bold text-[#12345A] mt-1">Halo, {{ $user->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Pantau tagihan, pembayaran, dan gangguan layanan air Anda.</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tagihan</p>
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-[#1976D2] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-[#12345A] mt-2">{{ $totalBills }}</p>
            <p class="text-xs text-gray-400 mt-1">Periode tercatat</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-amber-700 uppercase tracking-wider">Belum Lunas</p>
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-amber-600 mt-2">{{ $unpaidBills }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $unpaidBills > 0 ? 'Perlu diselesaikan' : 'Semua lunas' }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-blue-700 uppercase tracking-wider">Laporan Diproses</p>
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-[#1976D2] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-[#1976D2] mt-2">{{ $pendingComplaints }}</p>
            <p class="text-xs text-gray-400 mt-1">Menunggu penanganan</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Unpaid Bills --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between p-6 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-[#12345A]">Tagihan Menunggu Pembayaran</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Selesaikan tagihan tepat waktu untuk kelancaran aliran air</p>
                    </div>
                    <a href="{{ route('masyarakat.bills.index') }}" class="text-xs font-semibold text-[#1976D2] hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if($recentUnpaidBills->isEmpty())
                    <div class="text-center px-6 pb-6 pt-2">
                        <div class="py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <svg class="w-10 h-10 text-emerald-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="font-semibold text-gray-800 text-sm">Tidak ada tagihan tertunggak</h3>
                            <p class="text-xs text-gray-500 mt-1">Semua kewajiban tagihan air Anda telah terlunasi.</p>
                        </div>
                    </div>
                @else
                    <div class="px-6 pb-6 space-y-3">
                        @foreach($recentUnpaidBills as $bill)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white hover:border-gray-300 transition gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ substr($bill->billing_period, 5, 2) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-[#12345A] text-sm">Periode {{ $bill->billing_period }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $bill->payment?->paid_at ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">{{ $bill->payment?->paid_at ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran' }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Pemakaian: <strong class="text-gray-700">{{ $bill->usage_m3 }} m³</strong> &middot; Jatuh tempo: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between sm:justify-end gap-3">
                                    <div class="text-left sm:text-right">
                                        <p class="text-xs text-gray-400">Total</p>
                                        <p class="text-sm font-bold text-[#12345A]">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</p>
                                    </div>
                                    @if($bill->payment?->paid_at)
                                        <span class="text-xs font-semibold text-blue-700">Menunggu Konfirmasi Admin</span>
                                    @elseif($bill->payment?->status === 'pending')
                                        <a href="{{ route('masyarakat.payments.instruction', $bill->payment->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg">Lanjutkan Pembayaran</a>
                                    @else
                                        <a href="{{ route('masyarakat.payments.create', $bill->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1976D2] hover:bg-[#1565C0] text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            Bayar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info Card --}}
            <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#1976D2] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-[#12345A] text-sm">Pembayaran Digital</h3>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                            Bayar tagihan air menggunakan <strong>QRIS, Transfer Bank, GoPay, OVO, atau Dana</strong>. Bukti transaksi tersimpan otomatis dan bisa diakses kapan saja.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-4">
            {{-- Recent Complaints --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between p-5 pb-3">
                    <h2 class="text-sm font-bold text-[#12345A]">Laporan Saya</h2>
                    <a href="{{ route('masyarakat.complaints.index') }}" class="text-xs font-semibold text-[#1976D2] hover:underline">Semua &rarr;</a>
                </div>

                @if($recentComplaints->isEmpty())
                    <div class="text-center py-8 px-5 text-gray-400 text-xs">
                        Belum ada laporan gangguan.
                    </div>
                @else
                    <div class="divide-y divide-gray-100 px-5">
                        @foreach($recentComplaints as $complaint)
                            <div class="py-3 first:pt-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-mono font-semibold text-[#1976D2]">{{ $complaint->ticket_number }}</span>
                                    @php
                                        $badgeClasses = match($complaint->status) {
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'resolved' => 'bg-emerald-100 text-emerald-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($complaint->status) {
                                            'pending' => 'Menunggu',
                                            'in_progress' => 'Ditangani',
                                            'resolved' => 'Selesai',
                                            'rejected' => 'Ditolak',
                                            default => $complaint->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $badgeClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ $complaint->category->name ?? 'Gangguan Umum' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $complaint->location }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-[10px] text-gray-400">{{ $complaint->created_at?->format('d M Y, H:i') ?? '-' }}</span>
                                    <a href="{{ route('masyarakat.complaints.show', $complaint->id) }}" class="text-xs text-[#1976D2] hover:underline font-medium">Lacak &rarr;</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="p-5 pt-3 border-t border-gray-100">
                    <a href="{{ route('masyarakat.complaints.create') }}" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-dashed border-gray-300 text-gray-600 hover:text-[#1976D2] hover:border-[#1976D2] hover:bg-blue-50/50 text-xs font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Laporan Baru
                    </a>
                </div>
            </div>

            {{-- Contact --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="font-semibold text-[#12345A] text-sm">Kontak Layanan</h3>
                <p class="text-xs text-gray-500 mt-1">Hubungi jika ada darurat air bersih:</p>
                <div class="mt-3 space-y-2.5 text-xs">
                    <div class="flex items-center gap-2.5 text-gray-700">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Kantor BUMDes Karya Mandiri</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-gray-700">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>WhatsApp: 0812-3456-7890</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
