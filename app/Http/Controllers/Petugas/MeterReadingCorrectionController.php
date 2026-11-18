<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Services\HitungPemakaianMeter;
use App\Services\KoreksiPencatatanMeter;
use Illuminate\Http\Request;

class MeterReadingCorrectionController extends Controller
{
    public function show(Request $request, int $reading, KoreksiPencatatanMeter $correction)
    {
        return $this->page($request, $reading, $correction, false);
    }

    public function edit(Request $request, int $reading, KoreksiPencatatanMeter $correction)
    {
        return $this->page($request, $reading, $correction, true);
    }

    public function update(Request $request, int $reading, KoreksiPencatatanMeter $correction)
    {
        $this->authorizePetugas($request);
        $input = $this->validateChange($request, true);
        $correction->ubah($reading, $input['revision'], $input['reading_value'], $input['reason'], $request->user()->id);

        return redirect()->route('petugas.readings.show', $reading)
            ->with('success', 'Perubahan tersimpan. Pemakaian dihitung ulang; pencatatan baru memerlukan pemeriksaan Bendahara sebelum tagihan dibuat.');
    }

    public function cancel(Request $request, int $reading, KoreksiPencatatanMeter $correction)
    {
        $this->authorizePetugas($request);
        $input = $this->validateChange($request, false);
        $correction->batalkan($reading, $input['revision'], $input['reason'], $request->user()->id);

        return redirect()->route('petugas.readings.show', $reading)
            ->with('success', 'Pencatatan dan tagihan dibatalkan. Alasan dan riwayat tetap tersimpan.');
    }

    private function page(Request $request, int $id, KoreksiPencatatanMeter $correction, bool $editing)
    {
        $this->authorizePetugas($request);
        $reading = MeterReading::withTrashed()->with(['waterMeter.user', 'recorder', 'changes.actor'])->findOrFail($id);
        $lockedReason = $correction->alasanTerkunci($reading);
        $revision = $correction->revision($reading);
        $hitung = app(HitungPemakaianMeter::class);
        $meterAwal = $hitung->angkaKeSatuan($reading->previous_value ?? '0');
        $meterAkhir = $hitung->angkaKeSatuan($reading->reading_value);
        $pemakaian = $hitung->hitung($meterAwal, $meterAkhir);
        $editing = $editing && $lockedReason === null;
        $otherReadings = MeterReading::withTrashed()->where('water_meter_id', $reading->water_meter_id)
            ->where('id', '!=', $reading->id)->orderByDesc('reading_date')->orderByDesc('id')
            ->paginate(5, ['*'], 'history_page');

        return view('petugas.meters.reading', compact(
            'reading', 'lockedReason', 'revision', 'meterAwal', 'meterAkhir', 'pemakaian', 'editing', 'otherReadings',
        ));
    }

    private function authorizePetugas(Request $request): void
    {
        abort_unless($request->user()?->role === 'petugas', 403, 'Akses ditolak.');
    }

    private function validateChange(Request $request, bool $editing): array
    {
        return $request->validate([
            'revision' => ['required', 'string', 'size:64'],
            'reason' => ['required', 'string', 'max:300'],
            ...($editing ? ['reading_value' => ['required']] : []),
        ], [
            'reason.required' => 'Alasan perubahan atau pembatalan wajib diisi.',
            'reason.max' => 'Alasan maksimal 300 karakter.',
            'revision.required' => 'Muat ulang halaman sebelum mengubah pencatatan.',
            'reading_value.required' => 'Meter akhir wajib diisi.',
        ]);
    }
}
