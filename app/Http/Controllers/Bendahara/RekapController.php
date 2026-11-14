<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\BillingPeriod;
use App\Models\Payment;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use App\Models\YearlySnapshot;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function index()
    {
        $this->authorizeBendahara();

        $currentYear = (int) now()->year;
        $previousYear = $currentYear - 1;
        $currentLocked = YearlySnapshot::query()->where('year', $currentYear)->where('is_locked', true)->first();
        $previousLocked = YearlySnapshot::query()->where('year', $previousYear)->where('is_locked', true)->first();
        $revenueThisYear = YearlySnapshot::revenueForYear($currentYear);
        $revenuePreviousYear = YearlySnapshot::revenueForYear($previousYear);
        $monthlyThisYear = YearlySnapshot::monthlyRevenueForYear($currentYear);
        $monthlyPreviousYear = YearlySnapshot::monthlyRevenueForYear($previousYear);
        $arrears = WaterBill::query()->with('user')->where('status', 'unpaid')
            ->orderBy('year')->orderBy('billing_period')->get();
        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();
        $activeMonth = $activePeriod && (int) $activePeriod->year === $currentYear ? (int) $activePeriod->month : null;
        $lastClosedMonth = (int) (BillingPeriod::query()->where('year', $currentYear)->where('status', 'closed')->max('month') ?? 0);
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('bendahara.rekap.index', compact(
            'currentYear', 'previousYear', 'currentLocked', 'previousLocked',
            'revenueThisYear', 'revenuePreviousYear', 'monthlyThisYear', 'monthlyPreviousYear',
            'arrears', 'activeMonth', 'lastClosedMonth', 'namaBulan'
        ));
    }

    public function lock(Request $request)
    {
        $this->authorizeBendahara();
        $validated = $request->validate(['year' => 'required|integer|min:2000|max:2100']);
        $year = (int) $validated['year'];
        $existing = YearlySnapshot::query()->where('year', $year)->where('is_locked', true)->first();
        if ($existing) {
            return back()->with('warning', "Tahun {$year} sudah dikunci. Angka tidak diubah.");
        }
        YearlySnapshot::capture($year, Auth::user(), true);
        return back()->with('success', "Pembukuan tahun {$year} dikunci. Pembayaran di tahun berikutnya tidak mengubah angka ini.");
    }

    public function pembayaran(Request $request)
    {
        $this->authorizeBendahara();
        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();
        $latestUnpaidPeriod = WaterBill::query()->where('status', 'unpaid')->max('billing_period');
        $defaultDate = $activePeriod
            ? Carbon::createFromFormat('Y-m', $activePeriod->billing_period)
            : ($latestUnpaidPeriod ? Carbon::createFromFormat('Y-m', $latestUnpaidPeriod) : now());
        $tahun = (int) $request->input('tahun', $defaultDate->year);
        $bulan = (int) $request->input('bulan', $defaultDate->month);
        abort_if($tahun < 2020 || $tahun > 2100 || $bulan < 1 || $bulan > 12, 422);
        $periode = sprintf('%04d-%02d', $tahun, $bulan);
        $billingPeriod = BillingPeriod::query()->where('billing_period', $periode)->first();
        $paymentId = $request->integer('payment_id') ?: null;
        $selectedPayment = $paymentId
            ? Payment::query()->whereKey($paymentId)->whereHas('waterBill', fn ($query) => $query->where('billing_period', $periode))->first()
            : null;
        $periodBillCount = WaterBill::query()->where('billing_period', $periode)->count();
        $activeMeterCount = WaterMeter::query()->where('status', 'active')->whereHas('user', fn ($query) => $query->where('role', 'masyarakat'))->count();
        $allBillsPaid = $activeMeterCount > 0 && $periodBillCount >= $activeMeterCount
            && !WaterBill::query()->where('billing_period', $periode)->where('status', '!=', 'paid')->exists();
        $lastPeriod = BillingPeriod::query()->orderByDesc('year')->orderByDesc('month')->first();
        $expectedPeriod = $lastPeriod
            ? Carbon::create((int) $lastPeriod->year, (int) $lastPeriod->month, 1)->addMonth()->format('Y-m')
            : '2026-09';
        $canActivate = !$activePeriod && $expectedPeriod === $periode;

        $years = WaterBill::query()->select('year')->distinct()->pluck('year')->map(fn ($year) => (int) $year);
        $readingYears = MeterReading::query()->pluck('reading_date')->map(fn ($date) => (int) Carbon::parse($date)->year)->unique();
        $years = $years->merge($readingYears)->push($tahun)->unique()->sortDesc()->values();
        $meters = WaterMeter::query()->with(['user', 'latestReading'])->where('status', 'active')
            ->whereHas('user', fn ($query) => $query->where('role', 'masyarakat'))
            ->orderBy('meter_number')->get();

        $warga = $meters->map(function (WaterMeter $meter) use ($periode, $tahun, $bulan) {
            $bill = WaterBill::query()->with('payment')->where('water_meter_id', $meter->id)
                ->where('billing_period', $periode)->first();
            $reading = MeterReading::query()->where('water_meter_id', $meter->id)
                ->whereYear('reading_date', $tahun)->whereMonth('reading_date', $bulan)
                ->where(fn ($query) => $query->whereNull('notes')->orWhere('notes', 'not like', 'Diupdate oleh admin via Manajemen Pengguna%'))
                ->latest('reading_date')->first();

            if (!$bill && !$reading) {
                return (object) [
                    'meter' => $meter, 'bill' => null, 'reading' => null,
                    'latestReading' => $meter->latestReading, 'usage' => null, 'amount' => null,
                    'rate' => BillingService::DEFAULT_RATE_PER_M3, 'adminFee' => BillingService::DEFAULT_ADMIN_FEE,
                ];
            }

            $usage = (float) ($bill?->usage_m3 ?? max(0, (float) $reading->reading_value - (float) $reading->previous_value));
            $rate = (float) ($bill?->rate_per_m3 ?? BillingService::DEFAULT_RATE_PER_M3);
            $adminFee = (float) ($bill?->admin_fee ?? BillingService::DEFAULT_ADMIN_FEE);
            return (object) [
                'meter' => $meter, 'bill' => $bill, 'reading' => $reading,
                'latestReading' => $meter->latestReading,
                'previous' => $bill?->previous_reading ?? $reading?->previous_value,
                'current' => $bill?->current_reading ?? $reading?->reading_value,
                'usage' => $usage, 'rate' => $rate, 'adminFee' => $adminFee,
                'amount' => $bill ? (float) $bill->total_amount : ($usage > 0 ? ($usage * $rate) + $adminFee : 0),
            ];
        });

        return view('bendahara.pembayaran', compact('warga', 'tahun', 'bulan', 'periode', 'years', 'activePeriod', 'billingPeriod', 'paymentId', 'selectedPayment', 'periodBillCount', 'allBillsPaid', 'canActivate', 'activeMeterCount'));
    }

    public function storePembayaran(Request $request)
    {
        $this->authorizeBendahara();
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'action' => ['required', 'in:activate,close,confirm'],
            'payment_id' => ['nullable', 'integer'],
        ]);
        $bendahara = Auth::user();
        $tahun = (int) $validated['tahun'];
        $bulan = (int) $validated['bulan'];
        $periode = sprintf('%04d-%02d', $tahun, $bulan);
        $action = $validated['action'];

        if ($action === 'confirm') {
            $payment = Payment::query()->with('waterBill')->findOrFail($validated['payment_id'] ?? 0);
            abort_unless($payment->waterBill?->billing_period === $periode, 422, 'Pembayaran tidak sesuai periode yang dipilih.');
            abort_unless(BillingPeriod::query()->where('billing_period', $periode)->where('status', 'active')->exists(), 422, 'Periode pembayaran ini tidak aktif.');
            abort_unless($payment->status === 'pending' && $payment->paid_at, 422, 'Pembayaran belum menunggu konfirmasi.');
            DB::transaction(function () use ($payment, $bendahara) {
                $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
                abort_unless($lockedPayment->status === 'pending' && $lockedPayment->paid_at, 422, 'Pembayaran sudah diproses.');
                $lockedPayment->update(['status' => 'verified', 'verified_by' => $bendahara->id, 'verified_at' => now()]);
                $lockedPayment->waterBill()->update(['status' => 'paid']);
                $bendahara->unreadNotifications()->where('data->payment_id', $lockedPayment->id)->update(['read_at' => now()]);
            });
            return redirect()->route('bendahara.pembayaran', ['tahun' => $tahun, 'bulan' => $bulan])
                ->with('success', 'Pembayaran warga berhasil dikonfirmasi.');
        }

        if ($action === 'close') {
            $period = BillingPeriod::query()->where('billing_period', $periode)->where('status', 'active')->firstOrFail();
            $expectedBills = WaterMeter::query()->where('status', 'active')->whereHas('user', fn ($query) => $query->where('role', 'masyarakat'))->count();
            $periodBills = WaterBill::query()->where('billing_period', $periode);
            $unpaidBills = $periodBills->where('status', '!=', 'paid')->exists();
            $hasAllBills = WaterBill::query()->where('billing_period', $periode)->count() >= $expectedBills && $expectedBills > 0;
            $pendingPayments = Payment::query()->whereHas('waterBill', fn ($query) => $query->where('billing_period', $periode))
                ->where('status', 'pending')->whereNotNull('paid_at')->exists();
            if (!$hasAllBills || $unpaidBills || $pendingPayments) {
                return back()->with('warning', 'Periode belum dapat ditutup karena masih ada tagihan atau pembayaran yang belum lunas.');
            }
            $period->update(['status' => 'closed', 'closed_by' => $bendahara->id, 'closed_at' => now()]);
            $nextPeriod = Carbon::create($tahun, $bulan, 1)->addMonth();
            return redirect()->route('bendahara.pembayaran', ['tahun' => $nextPeriod->year, 'bulan' => $nextPeriod->month])
                ->with('success', "Periode {$periode} telah ditutup. Periode berikutnya siap diaktifkan.");
        }

        if (BillingPeriod::query()->where('status', 'active')->exists()) {
            return back()->with('warning', 'Tutup periode aktif terlebih dahulu sebelum membuka periode baru.');
        }
        $lastPeriod = BillingPeriod::query()->orderByDesc('year')->orderByDesc('month')->first();
        if ($lastPeriod) {
            abort_unless($lastPeriod->status === 'closed', 422, 'Tutup periode sebelumnya terlebih dahulu.');
            $next = Carbon::create((int) $lastPeriod->year, (int) $lastPeriod->month, 1)->addMonth();
            abort_unless($next->format('Y-m') === $periode, 422, 'Periode yang dibuka harus bulan setelah periode terakhir.');
            $previousUnpaid = WaterBill::query()->where('billing_period', $lastPeriod->billing_period)->where('status', '!=', 'paid')->exists();
            if ($previousUnpaid) {
                return back()->with('warning', 'Selesaikan semua tagihan periode sebelumnya sebelum membuka periode baru.');
            }
        } elseif ($periode !== '2026-09') {
            abort(422, 'Periode awal simulasi adalah September 2026.');
        }
        $period = BillingPeriod::query()->firstOrNew(['billing_period' => $periode]);
        $period->fill(['year' => $tahun, 'month' => $bulan, 'status' => 'active', 'opened_by' => $bendahara->id, 'opened_at' => now(), 'closed_by' => null, 'closed_at' => null])->save();

        return redirect()->route('bendahara.pembayaran', ['tahun' => $tahun, 'bulan' => $bulan])
            ->with('success', "Periode {$periode} berhasil diaktifkan. Catatan meter dapat diperbarui melalui menu Pengguna.");
    }

    public function notifications()
    {
        $this->authorizeBendahara();
        $notification = Auth::user()->unreadNotifications()->latest()->first();
        if (!$notification) {
            return redirect()->route('bendahara.pembayaran');
        }
        $data = $notification->data;
        $notification->markAsRead();
        $payment = Payment::query()->with('waterBill')->find($data['payment_id'] ?? 0);
        if (!$payment) {
            return redirect()->route('bendahara.pembayaran');
        }
        $period = $payment->waterBill?->billing_period ?? now()->format('Y-m');
        return redirect()->route('bendahara.pembayaran', [
            'tahun' => (int) substr($period, 0, 4), 'bulan' => (int) substr($period, -2), 'payment_id' => $payment->id,
        ])->with('success', $data['message'] ?? 'Pembayaran warga menunggu konfirmasi.');
    }

    private function authorizeBendahara(): void
    {
        abort_unless(in_array(Auth::user()?->role, ['bendahara', 'admin']), 403, 'Akses ditolak.');
    }
}
