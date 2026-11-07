@extends('layouts.app')

@section('title', 'Laporan Gangguan Air')

@section('nav-items')
    <a href="{{ route('masyarakat.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('masyarakat.bills.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Tagihan Air</a>
    <a href="{{ route('masyarakat.payments.history') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Riwayat Pembayaran</a>
    <a href="{{ route('masyarakat.complaints.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Laporan Gangguan</a>
    <a href="{{ route('masyarakat.profile.show') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Profil</a>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#12345A]">Laporan Gangguan Air Bersih</h1>
            <p class="text-sm text-gray-500">Sampaikan keluhan dan pantau tindak lanjut penanganan petugas BUMDes</p>
        </div>
        <div>
            <a href="{{ route('masyarakat.complaints.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1976D2] hover:bg-[#1565C0] text-white rounded-xl text-xs font-bold shadow-sm transition">
                <span>+</span> Buat Laporan Baru
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('masyarakat.complaints.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status Penanganan</label>
                <select name="status" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Ditangani</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Kategori Gangguan</label>
                <select name="category" class="w-full text-xs rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-[#1976D2] hover:bg-[#1565C0] text-white text-xs font-semibold py-2 px-4 rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('masyarakat.complaints.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Daftar Laporan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($complaints->isEmpty())
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">🛠️</div>
                <h3 class="font-bold text-[#12345A] text-base">Belum ada laporan pengaduan</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Jika air di rumah Anda mati, debit mengecil, atau terjadi kebocoran pipa, buat laporan agar segera ditinjau oleh petugas lapangan BUMDes.</p>
                <div class="mt-4">
                    <a href="{{ route('masyarakat.complaints.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1976D2] text-white text-xs font-bold rounded-xl shadow-sm">
                        + Buat Laporan Sekarang
                    </a>
                </div>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F5F7FA] text-gray-600 font-semibold uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3 px-4">No. Tiket</th>
                            <th class="py-3 px-4">Tanggal Masuk</th>
                            <th class="py-3 px-4">Kategori Masalah</th>
                            <th class="py-3 px-4">Lokasi Kejadian</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($complaints as $complaint)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#1976D2]">
                                    {{ $complaint->ticket_number }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600">
                                    {{ $complaint->created_at ? $complaint->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-[#12345A]">
                                    {{ $complaint->category->name ?? 'Gangguan Air' }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate">
                                    {{ $complaint->location }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClasses = match($complaint->status) {
                                            'pending' => 'bg-amber-100 text-amber-800',
                                            'verified' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-indigo-100 text-indigo-800',
                                            'resolved' => 'bg-emerald-100 text-emerald-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($complaint->status) {
                                            'pending' => 'Menunggu Verifikasi',
                                            'verified' => 'Terverifikasi',
                                            'in_progress' => 'Sedang Ditangani',
                                            'resolved' => 'Selesai',
                                            'rejected' => 'Ditolak',
                                            default => $complaint->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('masyarakat.complaints.show', $complaint->id) }}" class="inline-flex items-center px-3 py-1 bg-[#1976D2] hover:bg-[#1565C0] text-white font-semibold rounded-lg text-xs transition">
                                        Lacak Progres &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List --}}
            <div class="block md:hidden divide-y divide-gray-100">
                @foreach($complaints as $complaint)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-[#1976D2]">{{ $complaint->ticket_number }}</span>
                            @php
                                $badgeClasses = match($complaint->status) {
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'verified' => 'bg-blue-100 text-blue-800',
                                    'in_progress' => 'bg-indigo-100 text-indigo-800',
                                    'resolved' => 'bg-emerald-100 text-emerald-800',
                                    'rejected' => 'bg-rose-100 text-rose-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match($complaint->status) {
                                    'pending' => 'Menunggu Verifikasi',
                                    'verified' => 'Terverifikasi',
                                    'in_progress' => 'Sedang Ditangani',
                                    'resolved' => 'Selesai',
                                    'rejected' => 'Ditolak',
                                    default => $complaint->status,
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#12345A] text-sm">{{ $complaint->category->name ?? 'Gangguan Air' }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">📍 {{ $complaint->location }}</p>
                            <p class="text-xs text-gray-700 mt-2 line-clamp-2">{{ $complaint->description }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs text-gray-400">
                            <span>{{ $complaint->created_at ? $complaint->created_at->format('d M Y, H:i') : '-' }}</span>
                            <a href="{{ route('masyarakat.complaints.show', $complaint->id) }}" class="font-bold text-[#1976D2] hover:underline">
                                Lacak Progres &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 bg-[#F5F7FA]/50">
                {{ $complaints->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
