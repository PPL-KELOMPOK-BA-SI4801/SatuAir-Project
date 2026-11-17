@extends('layouts.app')
@section('title', 'Rincian Pemeriksaan Pemakaian')
@section('content')
<div class="space-y-6">
    <a href="{{ route('bendahara.reviews.index') }}" class="text-sm text-[#1976D2]">← Daftar pemeriksaan</a>
    <h1 class="text-2xl sm:text-3xl font-bold text-[#12345A]">Rincian Pemeriksaan Pemakaian</h1>
    @include('bendahara.reviews.errors')
    @include('bendahara.reviews.summary')
    @if ($lockedReason)<p class="rounded-xl bg-slate-100 p-4 text-slate-600">{{ $lockedReason }}</p>@endif
    @if (! $lockedReason)
        @if ($reading->latestReview?->status === 'draft')
            <a href="{{ route('bendahara.reviews.show', $reading->latestReview) }}" class="inline-block rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">Buka Draf Pemeriksaan</a>
        @elseif ($reading->latestReview?->status === 'returned' && $reading->latestReview->reading_revision === $reading->reviewRevision())
            <p class="rounded-xl bg-amber-50 p-4 text-amber-900">Menunggu koreksi Petugas. Alasan: {{ $reading->latestReview->notes }}</p>
        @else<a href="{{ route('bendahara.reviews.create', $reading) }}" class="inline-block rounded-xl bg-[#1976D2] px-5 py-3 font-semibold text-white">Buat Pemeriksaan</a>@endif
    @endif
    <section class="space-y-3" aria-label="Riwayat pemeriksaan"><h2 class="text-lg font-bold text-[#12345A]">Riwayat Pemeriksaan</h2>
        @forelse ($reading->reviews as $item)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">
                <div><p class="font-semibold">#{{ $item->id }} · {{ $item->label() }}</p><p class="mt-1 text-slate-500">{{ $item->reviewer->name ?? 'Pengguna tidak tersedia' }} · {{ $item->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p></div>
                <a href="{{ route('bendahara.reviews.show', $item->id) }}" class="text-[#1976D2]">Lihat pemeriksaan #{{ $item->id }}</a>
            </div>
        @empty<p class="text-sm text-slate-500">Belum ada pemeriksaan.</p>@endforelse
    </section>
</div>
@endsection
