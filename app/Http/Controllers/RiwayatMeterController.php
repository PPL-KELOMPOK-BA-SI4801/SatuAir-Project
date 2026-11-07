<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class RiwayatMeterController extends Controller
{
    // Menampilkan riwayat input meter dan tagihan
    public function index(Request $request)
    {
        $riwayat = DB::table('pencatatan_meter')
            ->join(
                'pelanggan',
                'pencatatan_meter.pelanggan_id',
                '=',
                'pelanggan.id'
            )
            ->leftJoin(
                'tagihan',
                'pencatatan_meter.id',
                '=',
                'tagihan.pencatatan_meter_id'
            )
            ->select(
                'pencatatan_meter.id',
                'pelanggan.kode_pelanggan',
                'pelanggan.nama',
                'pencatatan_meter.periode',
                'pencatatan_meter.meter_awal',
                'pencatatan_meter.meter_akhir',
                'tagihan.nomor_tagihan',
                'tagihan.pemakaian_m3',
                'tagihan.total',
                'tagihan.jatuh_tempo'
            )
            ->when($request->pelanggan, function ($query, $pelanggan) {
                $query->where('pelanggan.id', $pelanggan);
            })
            ->when($request->periode, function ($query, $periode) {
                $query->where('pencatatan_meter.periode', $periode);
            })
            ->orderByDesc('pencatatan_meter.periode')
            ->paginate(10)
            ->withQueryString();

        $pelanggan = DB::table('pelanggan')
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();

        return view('riwayat-meter.index', compact(
            'riwayat',
            'pelanggan'
        ));
    }

    // Menampilkan detail riwayat
    public function show($id)
    {
        $detail = DB::table('pencatatan_meter')
            ->join(
                'pelanggan',
                'pencatatan_meter.pelanggan_id',
                '=',
                'pelanggan.id'
            )
            ->leftJoin(
                'tagihan',
                'pencatatan_meter.id',
                '=',
                'tagihan.pencatatan_meter_id'
            )
            ->select(
                'pencatatan_meter.*',
                'pelanggan.kode_pelanggan',
                'pelanggan.nama',
                'pelanggan.alamat',
                'tagihan.nomor_tagihan',
                'tagihan.pemakaian_m3',
                'tagihan.total',
                'tagihan.jatuh_tempo'
            )
            ->where('pencatatan_meter.id', $id)
            ->first();

        if (!$detail) {
            abort(404);
        }

        return view('riwayat-meter.show', compact('detail'));
    }
}
