@extends('layouts.app')

@section('title', 'Detail Pengaduan — SatuAir')

@section('nav-items')
    <a href="{{ route('petugas.dashboard') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Dashboard</a>
    <a href="{{ route('petugas.payments.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Pembayaran</a>
    <a href="{{ route('petugas.meters.index') }}" class="px-3 py-2 text-sm font-medium text-blue-100 hover:text-white transition">Catat Meter</a>
    <a href="{{ route('petugas.complaints.index') }}" class="px-3 py-2 text-sm font-semibold text-white border-b-2 border-white">Pengaduan</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Kembali --}}
    <div>
        <a href="{{ route('petugas.complaints.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#1976D2] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pengaduan
        </a>
    </div>

    {{-- Header Detail --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-[#12345A] to-[#1976D2] text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-blue-200 mb-1">Detail Pengaduan</p>
                    <h1 class="text-xl font-bold">Tiket: {{ $complaint->ticket_number }}</h1>
                    <p class="text-xs text-blue-100 mt-1">
                        {{ $complaint->user->name ?? '-' }} &bull; {{ $complaint->area->name ?? '-' }}
                    </p>
                </div>
                <div class="text-right">
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badgeClasses }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            {{-- Info Pengaduan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Pelapor</p>
                    <p class="text-sm font-bold text-[#12345A]">{{ $complaint->user->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">ID: {{ $complaint->user->customer_id ?? '-' }}</p>
                    <p class="text-xs text-gray-500 mt-1">Wilayah: {{ $complaint->area->name ?? '-' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Kategori &amp; Tanggal</p>
                    <p class="text-sm font-bold text-[#12345A]">{{ ucfirst(str_replace('_', ' ', $complaint->category->name ?? 'Umum')) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Dibuat: {{ $complaint->created_at->format('d M Y, H:i') }}</p>
                    @if($complaint->assigned_to)
                        <p class="text-xs text-gray-500 mt-1">Ditugaskan ke: {{ optional($complaint->assignee)->name ?? 'Petugas' }}</p>
                    @else
                        <p class="text-xs text-amber-600 mt-1">Belum ditugaskan</p>
                    @endif
                </div>
            </div>

            {{-- Deskripsi --}}
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Deskripsi Masalah</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $complaint->description }}</p>
            </div>

            {{-- Foto Laporan (jika ada) --}}
            @if($complaint->photo)
                <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-3">Foto Laporan</p>
                    <a href="{{ asset('storage/' . $complaint->photo) }}" target="_blank" class="inline-flex items-center gap-2 text-[#1976D2] hover:text-[#0c4a6e] font-semibold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Lihat Foto
                    </a>
                </div>
            @endif

            {{-- Timeline Update --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Timeline Penanganan</h3>
                <div class="space-y-4">
                    @forelse($complaint->updates as $update)
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-2 h-2 rounded-full
                                @if($update->status === 'resolved') bg-emerald-500
                                @elseif($update->status === 'in_progress') bg-blue-500
                                @elseif($update->status === 'rejected') bg-rose-500
                                @else bg-amber-500 @endif"></div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-[#12345A]">{{ optional($update->user)->name ?? 'Sistem' }}</p>
                                    <p class="text-xs text-gray-400">{{ $update->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <p class="text-sm text-gray-700">{{ $update->notes }}</p>
                                @if($update->photo)
                                    <a href="{{ asset('storage/' . $update->photo) }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs text-[#1976D2] hover:text-[#0c4a6e]">
                                        <span>📎</span> Lihat Foto Update
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-sm text-gray-500">
                            Belum ada update penanganan.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Form Update Status --}}
            <div class="p-4 rounded-xl bg-[#F5F7FA] border border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Update Status Pengaduan</h3>
                <form method="POST" action="{{ route('petugas.complaints.update', $complaint->id) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status Baru</label>
                        <select name="status" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm">
                            <option value="in_progress">Diproses</option>
                            <option value="resolved">Selesai</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Catatan Petugas</label>
                        <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm" placeholder="Contoh: Teknisi sedang menuju lokasi..." required></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Foto Update (opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full rounded-xl border-gray-200 focus:border-[#1976D2] focus:ring-1 focus:ring-[#1976D2] py-2 px-3 text-sm">
                    </div>
                    <button type="submit" class="w-full py-3 px-6 bg-[#12345A] hover:bg-[#0f1e34] text-white text-sm font-bold rounded-xl shadow-sm transition active:scale-[0.99]">
                        Simpan Update
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
