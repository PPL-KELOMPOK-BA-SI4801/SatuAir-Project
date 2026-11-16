@extends('layouts.app')

@section('title', 'Catat Meter')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <a href="{{ route('petugas.meters.index') }}" class="text-xs font-semibold text-gray-600 hover:text-[#1976D2]">&larr; Kembali ke daftar meter</a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div>
            <h1 class="text-xl font-bold text-[#12345A]">Catat Water Meter</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $meter->user->name ?? '-' }} &middot; {{ $meter->meter_number }}</p>
            <p class="text-xs text-gray-400">{{ $meter->location }}</p>
        </div>

        <div class="rounded-xl bg-slate-50 p-4 text-sm">
            <p class="text-xs uppercase text-gray-500 font-semibold">Angka sebelumnya</p>
            <p class="text-lg font-bold text-[#12345A]">{{ $previousReading->reading_value ?? 0 }} m³</p>
        </div>

        <form method="POST" action="{{ route('petugas.meters.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="water_meter_id" value="{{ $meter->id }}">

            <div>
                <label class="text-xs font-semibold text-gray-600">Angka meter saat ini</label>
                <input type="number" step="0.01" min="0" name="reading_value" value="{{ old('reading_value') }}" required
                       class="mt-1 w-full rounded-xl border-gray-200 text-sm">
                @error('reading_value')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Tanggal pencatatan</label>
                <input type="date" name="reading_date" value="{{ old('reading_date', $defaultReadingDate) }}" required
                       class="mt-1 w-full rounded-xl border-gray-200 text-sm">
                @error('reading_date')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Foto meter (opsional)</label>
                <input type="file" name="photo" accept="image/*" capture="environment"
                       class="mt-1 w-full text-sm">
                @error('photo')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-gray-600">Catatan</label>
                <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-gray-200 text-sm">{{ old('notes') }}</textarea>
            </div>

            <button class="w-full py-3 bg-[#1976D2] text-white font-semibold rounded-xl">Simpan & Buat Tagihan</button>
        </form>
    </div>
</div>
@endsection
