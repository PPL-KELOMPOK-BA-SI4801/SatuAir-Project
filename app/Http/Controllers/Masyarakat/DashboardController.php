<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\WaterBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk dashboard masyarakat.
 *
 * Menampilkan ringkasan informasi tagihan, pembayaran,
 * dan pengaduan milik pengguna yang sedang login.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard masyarakat.
     *
     * Menyajikan data ringkasan berupa:
     * - Total tagihan
     * - Tagihan belum dibayar
     * - Total pembayaran tahun ini
     * - Pengaduan pending
     * - 5 tagihan belum dibayar terbaru
     * - 3 pengaduan terbaru
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Ringkasan tagihan
        $totalBills = WaterBill::where('user_id', $user->id)->count();

        $unpaidBills = WaterBill::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->count();

        // Total pembayaran yang diverifikasi tahun ini
        $totalPaidThisYear = Payment::whereHas('waterBill', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'verified')
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Pengaduan yang masih pending
        $pendingComplaints = Complaint::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // 5 tagihan belum dibayar terbaru
        $recentUnpaidBills = WaterBill::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->with('payment')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 3 pengaduan terbaru
        $recentComplaints = Complaint::where('user_id', $user->id)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('masyarakat.dashboard', compact(
            'user',
            'totalBills',
            'unpaidBills',
            'totalPaidThisYear',
            'pendingComplaints',
            'recentUnpaidBills',
            'recentComplaints'
        ));
    }
}
