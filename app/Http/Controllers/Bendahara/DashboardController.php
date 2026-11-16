<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\User;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use App\Models\YearlySnapshot;
use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['bendahara', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $currentYear = (int) now()->year;
        $previousYear = $currentYear - 1;

        // Statistik pengguna
        $totalUsers = User::count();
        $totalMasyarakat = User::where('role', 'masyarakat')->count();
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalBendahara = User::whereIn('role', ['bendahara', 'admin'])->count();
        $totalAdmin = $totalBendahara;

        // Statistik meter air
        $totalWaterMeters = WaterMeter::count();
        $activeWaterMeters = WaterMeter::where('status', 'active')->count();

        // Statistik tagihan
        $totalBills = WaterBill::count();
        $unpaidBills = WaterBill::where('status', 'unpaid')->count();
        $paidBills = WaterBill::where('status', 'paid')->count();

        // Statistik pembayaran
        $totalPayments = Payment::count();
        $pendingPayments = Payment::where('status', 'pending')->whereNotNull('paid_at')->count();
        $verifiedPayments = Payment::where('status', 'verified')->count();

        // Pendapatan mengikuti tanggal bayar. Tahun yang dikunci memakai snapshot.
        $revenueThisYear = YearlySnapshot::revenueForYear($currentYear);
        $revenuePreviousYear = YearlySnapshot::revenueForYear($previousYear);

        $revenueChange = $revenuePreviousYear > 0
            ? round((($revenueThisYear - $revenuePreviousYear) / $revenuePreviousYear) * 100, 1)
            : 0;

        // Statistik pengaduan
        $totalComplaints = Complaint::count();
        $pendingComplaints = Complaint::where('status', 'pending')->count();
        $inProgressComplaints = Complaint::where('status', 'in_progress')->count();
        $resolvedComplaints = Complaint::where('status', 'resolved')->count();

        $monthlyRevenue = YearlySnapshot::monthlyRevenueForYear($currentYear);
        $monthlyRevenuePreviousYear = YearlySnapshot::monthlyRevenueForYear($previousYear);

        $arrears = WaterBill::query()
            ->with('user')
            ->where('status', 'unpaid')
            ->orderBy('year')
            ->orderBy('billing_period')
            ->get();

        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();
        $activeMonth = $activePeriod && (int) $activePeriod->year === $currentYear ? (int) $activePeriod->month : null;
        $lastClosedMonth = (int) (BillingPeriod::query()->where('year', $currentYear)->where('status', 'closed')->max('month') ?? 0);

        $newUsersThisMonth = User::where('role', 'masyarakat')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', $currentYear)
            ->count();

        return view('bendahara.dashboard', compact(
            'user',
            'currentYear',
            'previousYear',
            'totalUsers',
            'totalMasyarakat',
            'totalPetugas',
            'totalAdmin',
            'totalBendahara',
            'totalWaterMeters',
            'activeWaterMeters',
            'totalBills',
            'unpaidBills',
            'paidBills',
            'totalPayments',
            'pendingPayments',
            'verifiedPayments',
            'revenueThisYear',
            'revenuePreviousYear',
            'revenueChange',
            'totalComplaints',
            'pendingComplaints',
            'inProgressComplaints',
            'resolvedComplaints',
            'monthlyRevenue',
            'monthlyRevenuePreviousYear',
            'newUsersThisMonth',
            'arrears',
            'activeMonth',
            'lastClosedMonth'
        ));
    }
}
