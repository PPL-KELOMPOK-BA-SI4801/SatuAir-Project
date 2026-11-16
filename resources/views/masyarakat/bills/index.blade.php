@extends('layouts.app')

@section('title', 'Tagihan Air Saya')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#12345A]">Tagihan Air Saya</h1>
            <p class="text-sm text-gray-500">Tagihan air yang belum dibayar. Riwayat pembayaran lengkap tersedia di menu Riwayat Pembayaran.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('masyarakat.payments.history') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-semibold shadow-sm transition">
                <span>📑</span> Riwayat Pembayaran
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('masyarakat.bills.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Tagihan</label>
                <select name="year" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Periode (Bulan)</label>
                <input type="text" name="period" value="{{ request('period') }}" placeholder="Contoh: 01 atau 2026-01" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#1976D2] hover:bg-[#1565C0] text-white text-xs font-semibold py-2 px-4 rounded-xl transition">
                    Cari
                </button>
                <a href="{{ route('masyarakat.bills.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel / Daftar Tagihan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($bills->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">💧</div>
                <h3 class="font-bold text-[#12345A] text-base">Tidak ada data tagihan</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Tagihan air Anda akan muncul di sini setelah pencatatan meteran dilakukan oleh petugas BUMDes.</p>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">Periode</th>
                            <th class="py-3 px-4">Meter Lalu</th>
                            <th class="py-3 px-4">Meter Kini</th>
                            <th class="py-3 px-4">Pemakaian</th>
                            <th class="py-3 px-4">Total Biaya</th>
                            <th class="py-3 px-4">Jatuh Tempo</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($bills as $bill)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-bold text-[#12345A]">
                                    {{ $bill->billing_period }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">{{ $bill->previous_reading }} m³</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">{{ $bill->current_reading }} m³</td>
                                <td class="py-3.5 px-4 font-semibold text-[#1976D2]">{{ $bill->usage_m3 }} m³</td>
                                <td class="py-3.5 px-4 font-extrabold text-[#12345A]">
                                    Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-500">
                                    {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $billMonth = (int) substr((string) $bill->billing_period, -2);
                                        $billYear = (int) substr((string) $bill->billing_period, 0, 4);
                                        $isCurrentPeriod = $activePeriod?->billing_period === $bill->billing_period;
                                        $isBeforeActivePeriod = $activePeriod && $bill->billing_period < $activePeriod->billing_period;
                                        $isLate = in_array($bill->status, ['unpaid', 'overdue'], true) && $bill->due_date && $bill->due_date->isPast()
                                            && ($activePeriod ? $isBeforeActivePeriod : true);
                                        $badgeClasses = match(true) {
                                            $bill->status === 'paid' => 'bg-emerald-100 text-emerald-800',
                                            $isLate => 'bg-rose-100 text-rose-800',
                                            in_array($bill->status, ['unpaid', 'overdue'], true) => 'bg-amber-100 text-amber-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match(true) {
                                            $bill->status === 'paid' => 'Lunas',
                                            $bill->payment?->status === 'pending' && $bill->payment?->paid_at => 'Menunggu Konfirmasi',
                                            $bill->payment?->status === 'pending' => 'Menunggu Pembayaran',
                                            $isLate => 'Telat',
                                            in_array($bill->status, ['unpaid', 'overdue'], true) => 'Menunggu Pembayaran',
                                            default => $bill->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <a href="{{ route('masyarakat.bills.show', $bill->id) }}" class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-xs transition">
                                        Rincian
                                    </a>
                                    @if($bill->status === 'unpaid' && $bill->payment?->paid_at)
                                        <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 font-semibold rounded-lg text-xs">Menunggu Konfirmasi</span>
                                    @elseif($bill->status === 'unpaid' && $bill->payment?->status === 'pending')
                                        <a href="{{ route('masyarakat.payments.instruction', $bill->payment->id) }}" class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs">Lanjutkan</a>
                                    @elseif($bill->status === 'unpaid' && $isCurrentPeriod)
                                        <a href="{{ route('masyarakat.payments.create', $bill->id) }}" class="inline-flex items-center px-3 py-1 bg-[#1976D2] hover:bg-[#1565C0] text-white font-bold rounded-lg text-xs transition">
                                            Bayar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List --}}
            <div class="block md:hidden divide-y divide-gray-100">
                @foreach($bills as $bill)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#12345A] text-sm">Periode {{ $bill->billing_period }}</span>
                            @php
                                $isCurrentPeriod = $activePeriod?->billing_period === $bill->billing_period;
                                $isBeforeActivePeriod = $activePeriod && $bill->billing_period < $activePeriod->billing_period;
                                $isLate = in_array($bill->status, ['unpaid', 'overdue'], true) && $bill->due_date && $bill->due_date->isPast()
                                    && ($activePeriod ? $isBeforeActivePeriod : true);
                                $badgeClasses = match(true) {
                                    $bill->status === 'paid' => 'bg-emerald-100 text-emerald-800',
                                    $isLate => 'bg-rose-100 text-rose-800',
                                    in_array($bill->status, ['unpaid', 'overdue'], true) => 'bg-amber-100 text-amber-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match(true) {
                                    $bill->payment?->status === 'pending' && $bill->payment?->paid_at => 'Menunggu Konfirmasi',
                                    $bill->payment?->status === 'pending' => 'Menunggu Pembayaran',
                                    $bill->status === 'paid' => 'Lunas',
                                    $isLate => 'Telat',
                                    in_array($bill->status, ['unpaid', 'overdue'], true) => 'Menunggu Pembayaran',
                                    default => $bill->status,
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-[#F5F7FA] p-3 rounded-xl">
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase">Pemakaian</span>
                                <span class="font-semibold text-gray-800">{{ $bill->usage_m3 }} m³</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase">Jatuh Tempo</span>
                                <span>{{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}</span>
                            </div>
                            <div class="col-span-2 pt-1 border-t border-gray-200">
                                <span class="text-gray-400 block text-[10px] uppercase">Total Tagihan</span>
                                <span class="text-base font-extrabold text-[#12345A]">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <a href="{{ route('masyarakat.bills.show', $bill->id) }}" class="flex-1 text-center py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-xs transition">
                                Rincian
                            </a>
                            @if($bill->status === 'unpaid' && $bill->payment?->paid_at)
                                <span class="flex-1 text-center py-2 bg-blue-50 text-blue-700 font-semibold rounded-xl text-xs">Menunggu Konfirmasi</span>
                            @elseif($bill->status === 'unpaid' && $bill->payment?->status === 'pending')
                                <a href="{{ route('masyarakat.payments.instruction', $bill->payment->id) }}" class="flex-1 text-center py-2 bg-amber-100 text-amber-800 font-bold rounded-xl text-xs">Lanjutkan Pembayaran</a>
                            @elseif($bill->status === 'unpaid' && $isCurrentPeriod)
                                <a href="{{ route('masyarakat.payments.create', $bill->id) }}" class="flex-1 text-center py-2 bg-[#1976D2] hover:bg-[#1565C0] text-white font-bold rounded-xl text-xs transition">
                                    Bayar Sekarang
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 bg-[#F5F7FA]/50">
                {{ $bills->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
