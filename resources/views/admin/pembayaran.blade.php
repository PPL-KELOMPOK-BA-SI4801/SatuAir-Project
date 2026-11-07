@extends('layouts.app')

@section('title', 'Pembayaran Warga — SatuAir')

@section('content')
@php
    $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $belumLunas = $warga->filter(fn ($row) => $row->bill && $row->bill->status === 'unpaid')->count();
    $canConfirm = $selectedPayment && $selectedPayment->status === 'pending' && $selectedPayment->paid_at && $activePeriod?->billing_period === $periode;
    $canClose = $activePeriod?->billing_period === $periode && $allBillsPaid;
    $action = $canConfirm ? 'confirm' : ($activePeriod ? 'close' : 'activate');
    $actionEnabled = $canConfirm || $canClose || $canActivate;
    $actionLabel = $canConfirm ? 'Konfirmasi Pembayaran' : ($canClose ? 'Tutup Periode ' . $namaBulan[$bulan] : ($canActivate ? 'Aktifkan Periode ' . $namaBulan[$bulan] : ($activePeriod?->billing_period === $periode ? 'Menunggu Semua Tagihan Lunas' : 'Tutup Periode Aktif Terlebih Dahulu')));
@endphp
<div class="space-y-6">
    <a href="{{ route('admin.rekap.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-[#1976D2]">← Kembali ke Rekapitulasi</a>

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h1 class="text-xl font-bold text-[#12345A]">Pembayaran Warga</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih periode untuk melihat catatan meter dan tagihan seluruh warga.</p>
        <form method="GET" action="{{ route('admin.pembayaran') }}" class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-4 items-end mt-5">
            <label class="block text-sm font-medium text-gray-700">Tahun
                <select name="tahun" class="mt-2 w-full rounded-xl border-gray-200 py-3 px-4">
                    @foreach($years as $year)<option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>@endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-gray-700">Bulan
                <select name="bulan" class="mt-2 w-full rounded-xl border-gray-200 py-3 px-4">
                    @foreach($namaBulan as $number => $name)<option value="{{ $number }}" @selected($bulan === $number)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <button class="px-6 py-3 bg-[#1976D2] hover:bg-blue-700 text-white font-semibold rounded-xl">Tampilkan</button>
        </form>
    </section>

    @if(session('success'))<div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">{{ session('warning') }}</div>@endif

    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-[#12345A]">Rincian tagihan {{ $namaBulan[$bulan] }} {{ $tahun }}</h2>
                <p class="text-sm text-gray-500 mt-1">Tarif Rp {{ number_format(\App\Services\BillingService::DEFAULT_RATE_PER_M3, 0, ',', '.') }} per m³ + biaya admin Rp {{ number_format(\App\Services\BillingService::DEFAULT_ADMIN_FEE, 0, ',', '.') }}.</p>
            </div>
            <span class="text-sm text-gray-500">{{ $belumLunas }} tagihan belum lunas</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#F5F7FA] text-gray-600 text-xs uppercase"><tr>
                    <th class="px-4 py-3">Pelanggan</th><th class="px-4 py-3">Meter</th><th class="px-4 py-3">Pembacaan Meter</th><th class="px-4 py-3">Pemakaian</th><th class="px-4 py-3">Perhitungan</th><th class="px-4 py-3">Tagihan</th><th class="px-4 py-3">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($warga as $row)
                        <tr>
                            <td class="px-4 py-4"><p class="font-semibold text-[#12345A]">{{ $row->meter->user->name ?? 'Warga' }}</p><p class="text-xs text-gray-500">{{ $row->meter->user->customer_id ?? '-' }}</p></td>
                            <td class="px-4 py-4 font-mono text-xs">{{ $row->meter->meter_number }}</td>
                            <td class="px-4 py-4 font-mono text-xs">@if(isset($row->previous, $row->current)){{ number_format((float)$row->previous, 2, ',', '.') }} → {{ number_format((float)$row->current, 2, ',', '.') }} m³ @else — @endif</td>
                            <td class="px-4 py-4 font-semibold">{{ $row->usage !== null ? number_format($row->usage, 2, ',', '.') . ' m³' : '—' }}</td>
                            <td class="px-4 py-4 text-xs">@if($row->usage !== null){{ number_format($row->usage, 2, ',', '.') }} × Rp {{ number_format($row->rate, 0, ',', '.') }}<br><span class="text-gray-400">+ admin Rp {{ number_format($row->adminFee, 0, ',', '.') }}</span>@if($row->bill?->payment)<br><span class="text-gray-500">{{ strtoupper(str_replace('_', ' ', $row->bill->payment->payment_method)) }} · {{ $row->bill->payment->paid_at?->format('d M Y H:i') ?? 'belum dibayar' }}@if($row->bill->payment->paid_at)<br>Nominal diterima Rp {{ number_format((float)$row->bill->payment->amount, 0, ',', '.') }}@endif</span>@endif @else — @endif</td>
                            <td class="px-4 py-4 font-bold text-[#12345A]">{{ $row->amount !== null && $row->amount > 0 ? 'Rp ' . number_format($row->amount, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="px-4 py-4">@if($row->bill?->payment?->status === 'verified')<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Lunas</span>@elseif($row->bill?->payment?->status === 'pending' && $row->bill?->payment?->paid_at)<a href="{{ route('admin.pembayaran', ['tahun' => $tahun, 'bulan' => $bulan, 'payment_id' => $row->bill->payment->id]) }}" class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu Konfirmasi</a>@elseif($row->bill)<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu Pembayaran</span>@else<span class="text-gray-400">Belum ada tagihan</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Belum ada warga dengan meter aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5 border-t border-gray-100">
            <form method="POST" action="{{ route('admin.pembayaran.store') }}" onsubmit="return {{ $action === 'close' ? 'confirm(\'Tutup periode ini setelah semua tagihan lunas?\')' : 'true' }}">
                @csrf<input type="hidden" name="tahun" value="{{ $tahun }}"><input type="hidden" name="bulan" value="{{ $bulan }}"><input type="hidden" name="action" value="{{ $action }}"><input type="hidden" name="payment_id" value="{{ $paymentId }}">
                <button type="submit" @disabled(!$actionEnabled) class="px-6 py-4 bg-[#12345A] hover:bg-[#0f1e34] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-xl">{{ $actionLabel }}</button>
            </form>
        </div>
    </section>
</div>
@endsection
