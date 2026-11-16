@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#12345A]">Riwayat Transaksi Pembayaran</h1>
            <p class="text-sm text-gray-500">Histori bukti pembayaran online air bersih Anda</p>
        </div>
        <div>
            <a href="{{ route('masyarakat.bills.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#1976D2] hover:bg-[#1565C0] text-white rounded-xl text-xs font-semibold shadow-sm transition">
                <span>💳</span> Bayar Tagihan Baru
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('masyarakat.payments.history') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status Verifikasi</label>
                <select name="status" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Pembayaran / Konfirmasi</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Lunas / Dikonfirmasi</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Metode Pembayaran</label>
                <select name="method" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Metode</option>
                    @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" {{ request('method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Transaksi</label>
                <select name="year" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#1976D2] hover:bg-[#1565C0] text-white text-xs font-semibold py-2 px-4 rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('masyarakat.payments.history') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Daftar Pembayaran --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($payments->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">📑</div>
                <h3 class="font-bold text-[#12345A] text-base">Belum ada riwayat pembayaran</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Setiap pembayaran tagihan air yang Anda lakukan secara online akan dicatat di sini beserta status konfirmasinya.</p>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">Tanggal Bayar</th>
                            <th class="py-3 px-4">No. Referensi</th>
                            <th class="py-3 px-4">Tagihan</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4">Nominal</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Rincian Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-medium text-gray-800">
                                    {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y, H:i') : $payment->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-[#12345A]">
                                    {{ $payment->reference_number ?? 'PAY-' . $payment->id }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-[#1976D2]">{{ $payment->waterBill->billing_period ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded uppercase font-semibold text-[10px] bg-slate-100 text-slate-800">
                                        {{ $payment->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-extrabold text-[#12345A]">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClasses = match($payment->status) {
                                            'verified' => 'bg-emerald-100 text-emerald-800',
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match(true) {
                                            $payment->status === 'verified' => 'Lunas / Dikonfirmasi',
                                            $payment->status === 'pending' && $payment->paid_at => 'Menunggu Konfirmasi',
                                            $payment->status === 'pending' => 'Menunggu Pembayaran',
                                            $payment->status === 'rejected' => 'Ditolak',
                                            default => $payment->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($payment->status === 'pending' && !$payment->paid_at)
                                            <form method="POST" action="{{ route('masyarakat.payments.cancel', $payment->id) }}" onsubmit="return confirm('Batalkan pembayaran ini? Riwayat pembayaran ini akan hilang dan tagihan akan kembali ke belum lunas.')" style="display:inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[10px] border border-rose-200 rounded transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('masyarakat.bills.show', $payment->water_bill_id) }}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-[10px] rounded-lg transition">
                                            Lihat &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile View --}}
            <div class="block md:hidden divide-y divide-gray-100">
                @foreach($payments as $payment)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#12345A]">{{ $payment->reference_number ?? 'PAY-' . $payment->id }}</span>
                            @php
                                $badgeClasses = match($payment->status) {
                                    'verified' => 'bg-emerald-100 text-emerald-800',
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'rejected' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match(true) {
                                    $payment->status === 'verified' => 'Lunas / Dikonfirmasi',
                                    $payment->status === 'pending' && $payment->paid_at => 'Menunggu Konfirmasi',
                                    $payment->status === 'pending' => 'Menunggu Pembayaran',
                                    $payment->status === 'rejected' => 'Ditolak',
                                    default => $payment->status,
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 bg-[#F5F7FA] p-3 rounded-xl">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Periode Tagihan</span>
                                    <span class="font-bold text-[#1976D2]">{{ $payment->waterBill->billing_period ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Metode</span>
                                    <span class="font-semibold uppercase">{{ $payment->payment_method }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Tanggal Bayar</span>
                                    <span>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Nominal</span>
                                    <span class="font-bold text-[#12345A]">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <a href="{{ route('masyarakat.bills.show', $payment->water_bill_id) }}" class="text-xs font-semibold text-[#1976D2] hover:underline">
                                    Rincian Tagihan &rarr;
                                </a>
                                @if($payment->status === 'pending' && !$payment->paid_at)
                                    <form method="POST" action="{{ route('masyarakat.payments.cancel', $payment->id) }}" onsubmit="return confirm('Batalkan pembayaran ini? Riwayat pembayaran ini akan hilang dan tagihan akan kembali ke belum lunas.')" style="display:inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700 mt-1">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 bg-[#F5F7FA]/50">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
