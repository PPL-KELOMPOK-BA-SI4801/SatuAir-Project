<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WaterBill;
use App\Models\BillingPeriod;
use App\Models\User;
use App\Notifications\ResidentPaymentSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected const PAYMENT_METHODS = [
        'qris'          => 'QRIS',
        'va_bri'        => 'Virtual Account BRI',
        'va_bca'        => 'Virtual Account BCA',
        'va_mandiri'    => 'Virtual Account Mandiri',
        'va_bni'        => 'Virtual Account BNI',
        'va_bjb'        => 'Virtual Account BJB',
        'va_bsi'        => 'Virtual Account BSI',
        'bank_transfer' => 'Transfer Bank BUMDes',
        'gopay'         => 'GoPay',
        'dana'          => 'DANA',
        'ovo'           => 'OVO',
        'shopeepay'     => 'ShopeePay',
        'linkaja'       => 'LinkAja',
    ];

    /**
     * Tampilkan halaman pilih metode pembayaran ala MyTelkomsel.
     */
    public function create($billId)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $bill = WaterBill::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->findOrFail($billId);
        abort_unless(BillingPeriod::query()->where('billing_period', $bill->billing_period)->where('status', 'active')->exists(), 403, 'Pembayaran hanya tersedia untuk periode aktif.');

        $existingPayment = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('waterBill', fn ($query) => $query->whereKey($billId))
            ->latest()
            ->first();
        if ($existingPayment && $existingPayment->paid_at) {
            return redirect()->route('masyarakat.bills.show', $billId)
                ->with('success', 'Pembayaran Anda sedang menunggu konfirmasi admin.');
        }
        if ($existingPayment) {
            return redirect()->route('masyarakat.payments.instruction', $existingPayment->id);
        }

        $paymentMethods = self::PAYMENT_METHODS;

        return view('masyarakat.payments.create', compact('bill', 'user', 'paymentMethods'));
    }

    /**
     * Proses pembuatan pembayaran.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $request->validate([
            'water_bill_id'  => ['required', 'exists:water_bills,id'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', array_keys(self::PAYMENT_METHODS))],
        ], [
            'water_bill_id.required'  => 'Tagihan wajib dipilih.',
            'water_bill_id.exists'    => 'Tagihan tidak ditemukan.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $bill = WaterBill::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->findOrFail($request->water_bill_id);
        abort_unless(BillingPeriod::query()->where('billing_period', $bill->billing_period)->where('status', 'active')->exists(), 403, 'Pembayaran hanya tersedia untuk periode aktif.');

        $existingPayment = Payment::where('water_bill_id', $bill->id)
            ->where('status', 'pending')->latest()->first();
        if ($existingPayment) {
            return $existingPayment->paid_at
                ? redirect()->route('masyarakat.bills.show', $bill->id)->with('success', 'Pembayaran Anda sedang menunggu konfirmasi admin.')
                : redirect()->route('masyarakat.payments.instruction', $existingPayment->id);
        }

        $method = $request->payment_method;
        $adminFee = str_starts_with($method, 'va_') ? 1000 : 0;
        $totalPay = $bill->total_amount + $adminFee;
        $refNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        // Generate nomor VA dinamis
        $vaNumber = null;
        if (str_starts_with($method, 'va_')) {
            $bankPrefix = match($method) {
                'va_bri'     => '1019 2000',
                'va_bca'     => '3901 0081',
                'va_mandiri' => '8890 8021',
                'va_bni'     => '8277 0019',
                'va_bjb'     => '0011 0023',
                'va_bsi'     => '9002 0014',
                default      => '9988 0001',
            };
            $vaNumber = $bankPrefix . ' ' . str_pad($user->id, 4, '0', STR_PAD_LEFT) . ' ' . str_pad($bill->id, 4, '0', STR_PAD_LEFT);
        }

        $payment = Payment::create([
            'water_bill_id'    => $bill->id,
            'user_id'          => $user->id,
            'amount'           => $totalPay,
            'payment_method'   => $method,
            'payment_proof'    => null,
            'reference_number' => $refNumber,
            'transaction_id'   => 'TRX-' . strtoupper(Str::random(8)),
            'status'           => 'pending',
            'paid_at'          => null,
            'notes'            => $vaNumber ? json_encode(['va_number' => $vaNumber]) : null,
        ]);

        return redirect()->route('masyarakat.payments.instruction', $payment->id);
    }

    /**
     * Tampilan Menunggu Pembayaran & Panduan Pembayaran (ala MyTelkomsel Gambar 1).
     */
    public function instruction($id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $payment = Payment::where('user_id', $user->id)
            ->with('waterBill')
            ->findOrFail($id);

        $vaNumber = null;
        if ($payment->notes && str_contains($payment->notes, 'va_number')) {
            $decoded = json_decode($payment->notes, true);
            $vaNumber = $decoded['va_number'] ?? null;
        }

        if (!$vaNumber && str_starts_with($payment->payment_method, 'va_')) {
            $vaNumber = '1019 2000 ' . str_pad($payment->user_id, 4, '0', STR_PAD_LEFT) . ' ' . str_pad($payment->water_bill_id, 4, '0', STR_PAD_LEFT);
        }

        return view('masyarakat.payments.instruction', compact('payment', 'vaNumber'));
    }

    /**
     * Tombol Simulasi Pembayaran Berhasil untuk pengujian / demo seketika.
     */
    public function simulateSuccess($id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $payment = Payment::with('waterBill')->where('user_id', $user->id)->findOrFail($id);
        abort_unless(BillingPeriod::query()->where('billing_period', $payment->waterBill?->billing_period)->where('status', 'active')->exists(), 403, 'Pembayaran hanya tersedia untuk periode aktif.');
        if ($payment->status !== 'pending') {
            return redirect()->route('masyarakat.bills.show', $payment->water_bill_id)
                ->with('error', 'Pembayaran ini sudah diproses.');
        }

        DB::transaction(function () use ($id, $user) {
            $lockedPayment = Payment::query()->where('user_id', $user->id)->lockForUpdate()->findOrFail($id);
            if ($lockedPayment->status !== 'pending' || $lockedPayment->paid_at) {
                return;
            }
            $lockedPayment->update(['paid_at' => now()]);
            User::query()->where('role', 'admin')->get()->each(function (User $admin) use ($lockedPayment) {
                $admin->notify(new ResidentPaymentSubmitted($lockedPayment));
            });
        });

        return redirect()
            ->route('masyarakat.bills.show', $payment->water_bill_id)
            ->with('success', 'Pembayaran berhasil dikirim dan sedang menunggu konfirmasi admin.');
    }

    /**
     * Batalkan pembayaran yang masih pending (belum dibayar)
     * agar tidak muncul di riwayat dan warga bisa pilih metode lain.
     */
    public function cancel($id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $payment = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->whereNull('paid_at')
            ->findOrFail($id);

        $billId = $payment->water_bill_id;

        $payment->delete();

        return redirect()
            ->route('masyarakat.bills.show', $billId)
            ->with('success', 'Pembayaran dibatalkan. Anda dapat memilih metode pembayaran lain.');
    }

    /**
     * Halaman Riwayat Transaksi Pembayaran Warga.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $query = Payment::where('user_id', $user->id)
            ->with('waterBill');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        $paymentMethods = self::PAYMENT_METHODS;

        $years = Payment::where('user_id', $user->id)
            ->get()
            ->map(function ($p) {
                return $p->created_at ? $p->created_at->format('Y') : date('Y');
            })
            ->unique()
            ->values();

        return view('masyarakat.payments.history', compact('payments', 'paymentMethods', 'years'));
    }
}
