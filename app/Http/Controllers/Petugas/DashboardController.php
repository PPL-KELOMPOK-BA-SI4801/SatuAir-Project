<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\WaterBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk dashboard petugas.
 *
 * Menampilkan ringkasan statistik terkait tugas petugas
 * seperti pembacaan meter, tagihan, dan pengaduan.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard petugas.
     *
     * Menyajikan data ringkasan berupa:
     * - Total pembacaan meter bulan ini
     * - Tagihan belum dibayar
     * - Pembayaran menunggu verifikasi
     * - Pengaduan yang perlu ditangani
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Total pembacaan meter bulan ini
        $meterReadingsThisMonth = MeterReading::whereMonth('reading_date', now()->month)
            ->whereYear('reading_date', now()->year)
            ->count();

        // Total tagihan belum dibayar
        $unpaidBills = WaterBill::where('status', 'unpaid')->count();

        // Pembayaran menunggu verifikasi
        $pendingPayments = Payment::where('status', 'pending')->count();

        // Pengaduan yang sedang diproses
        $activeComplaints = Complaint::whereIn('status', ['pending', 'in_progress'])
            ->count();

        // Pengaduan baru hari ini
        $newComplaintsToday = Complaint::whereDate('created_at', today())->count();

        return view('petugas.dashboard', compact(
            'user',
            'meterReadingsThisMonth',
            'unpaidBills',
            'pendingPayments',
            'activeComplaints',
            'newComplaintsToday'
        ));
    }
}
