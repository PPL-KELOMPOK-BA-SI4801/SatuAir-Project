@extends('layouts.app')

@section('title', 'Pengaduan Masyarakat — SatuAir')

@section('nav-items')
    <a href="{{ route('petugas.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('petugas.payments.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pembayaran</a>
    <a href="{{ route('petugas.meters.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Catat Meter</a>
    <a href="{{ route('petugas.complaints.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Pengaduan</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-[#12345A]">Pengaduan Masyarakat</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola laporan gangguan layanan air dari warga dan pantau status penanganan.</p>
        </div>
        <a href="{{ route('petugas.complaints.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-semibold shadow-sm transition">
            <span>🔄</span> Segarkan
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Filter</label>
                <select name="filter" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm">
                    <option value="">Saya + Belum Ditugaskan</option>
                    <option value="mine" {{ request('filter') === 'mine' ? 'selected' : '' }}>Saya Ditugaskan</option>
                    <option value="unassigned" {{ request('filter') === 'unassigned' ? 'selected' : '' }}>Belum Ditugaskan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Diproses</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-[#1976D2] hover:bg-[#1565C0] text-white text-sm font-semibold rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('petugas.complaints.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Stats Badges --}}
    <div class="flex gap-2 mb-2">
        <a href="{{ route('petugas.complaints.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold
            {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} transition">
            Menunggu: {{ $pendingCount }}
        </a>
        <a href="{{ route('petugas.complaints.index', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold
            {{ request('status') === 'in_progress' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} transition">
            Diproses: {{ $inProgressCount }}
        </a>
        <a href="{{ route('petugas.complaints.index', ['status' => 'resolved']) }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold
            {{ request('status') === 'resolved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }} transition">
            Selesai: {{ $resolvedCount }}
        </a>
    </div>

    {{-- Tabel Pengaduan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($complaints->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">📋</div>
                <h3 class="font-bold text-[#12345A] text-base">Tidak ada pengaduan</h3>
                <p class="text-xs text-gray-500 mt-1">Belum ada laporan gangguan yang masuk.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">No. Tiket</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Wilayah</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($complaints as $complaint)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $complaint->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-[#12345A]">
                                    {{ $complaint->ticket_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-[#12345A]">{{ $complaint->user->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $complaint->user->customer_id ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-600">{{ $complaint->area->name ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded uppercase font-semibold text-[10px] bg-slate-100 text-slate-800">
                                        {{ ucfirst(str_replace('_', ' ', $complaint->category->name ?? 'Umum')) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeClasses = match($complaint->status) {
                                            'resolved' => 'bg-emerald-100 text-emerald-800',
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($complaint->status) {
                                            'resolved' => 'Selesai',
                                            'pending' => 'Menunggu',
                                            'in_progress' => 'Diproses',
                                            'rejected' => 'Ditolak',
                                            default => $complaint->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('petugas.complaints.show', $complaint->id) }}" class="inline-flex items-center px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-xs transition">
                                        Lihat &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-[#F5F7FA]/50">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
