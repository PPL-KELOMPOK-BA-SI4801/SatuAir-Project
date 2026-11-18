<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\PencatatanMeter;
use App\Services\HitungPemakaianMeter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PencatatanMeterController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->validate(['q' => 'nullable|string|max:100', 'wilayah' => 'nullable|string|max:100']);
        $periode = now('Asia/Jakarta')->format('Y-m');
        $pelanggan = Pelanggan::query()
            ->when($filter['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query
                ->where('nama', 'like', '%'.$q.'%')->orWhere('nomor_meter', 'like', '%'.$q.'%')))
            ->when($filter['wilayah'] ?? null, fn ($query, $wilayah) => $query->where('wilayah', $wilayah))
            ->with(['pencatatan' => fn ($query) => $query->orderByDesc('periode')])
            ->orderBy('id')->get();

        return view('meter.index', [
            'pelanggan' => $pelanggan, 'periode' => $periode,
            'wilayah' => Pelanggan::query()->distinct()->orderBy('wilayah')->pluck('wilayah'),
        ]);
    }

    public function create(Pelanggan $pelanggan): View
    {
        $periode = now('Asia/Jakarta')->format('Y-m');

        return view('meter.create', [
            'pelanggan' => $pelanggan, 'periode' => $periode,
            'meterAwal' => $pelanggan->meterAwal($periode),
            'tercatat' => $pelanggan->pencatatan()->where('periode', $periode)->exists(),
        ]);
    }

    public function store(Request $request, Pelanggan $pelanggan, HitungPemakaianMeter $hitung): RedirectResponse
    {
        $input = $request->validate([
            'meter_akhir' => ['required'],
            'periode' => ['required', 'date_format:Y-m', 'in:'.now('Asia/Jakarta')->format('Y-m')],
        ], [
            'meter_akhir.required' => 'Meter akhir wajib diisi.',
            'periode.in' => 'Periode sudah berubah. Muat ulang formulir sebelum menyimpan.',
            'periode.*' => 'Periode pencatatan tidak valid. Muat ulang formulir.',
        ]);
        try {
            DB::transaction(function () use ($input, $pelanggan, $hitung) {
                $pelanggan = Pelanggan::query()->lockForUpdate()->findOrFail($pelanggan->id);
                if ($pelanggan->pencatatan()->where('periode', $input['periode'])->exists()) {
                    throw ValidationException::withMessages(['pencatatan' => 'Pelanggan ini sudah dicatat pada periode yang sama.']);
                }
                $awal = $pelanggan->meterAwal($input['periode']);
                $akhir = $hitung->angkaKeSatuan($input['meter_akhir']);
                $pemakaian = $hitung->hitung($awal, $akhir);
                PencatatanMeter::create([
                    'pelanggan_id' => $pelanggan->id, 'periode' => $input['periode'],
                    'meter_awal_satuan' => $awal, 'meter_akhir_satuan' => $akhir,
                    'pemakaian_satuan' => $pemakaian, 'status' => 'menunggu_pemeriksaan_bendahara',
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['pencatatan' => 'Pelanggan ini sudah dicatat pada periode yang sama.']);
        }

        return to_route('meter.index')->with('success', 'Pencatatan '.$pelanggan->nama.' tersimpan. Status: menunggu pemeriksaan Bendahara.');
    }
}
