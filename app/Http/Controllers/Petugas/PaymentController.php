<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WaterBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Controller baca-saja untuk melihat pembayaran warga. */
class PaymentController extends Controller
{
    /**
     * Tampilkan daftar semua pembayaran dengan filter.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $query = Payment::with(['user', 'waterBill.waterMeter', 'verifier'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        // Counts for filter badges
        $pendingCount = Payment::where('status', 'pending')->count();
        $verifiedCount = Payment::where('status', 'verified')->count();
        $rejectedCount = Payment::where('status', 'rejected')->count();

        return view('petugas.payments.index', compact(
            'payments', 'pendingCount', 'verifiedCount', 'rejectedCount'
        ));
    }

    /**
     * Tampilkan detail pembayaran.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $payment = Payment::with(['user', 'waterBill.waterMeter.area', 'waterBill.user', 'verifier'])
            ->findOrFail($id);

        return view('petugas.payments.show', compact('payment'));
    }

    public function verify($id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403, 'Konfirmasi pembayaran hanya dapat dilakukan admin.');
        DB::transaction(function () use ($id, $user) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($id);
            abort_unless($payment->status === 'pending' && $payment->paid_at, 422, 'Pembayaran belum dikirim warga.');
            $payment->update(['status' => 'verified', 'verified_by' => $user->id, 'verified_at' => now()]);
            WaterBill::query()->whereKey($payment->water_bill_id)->update(['status' => 'paid']);
        });
        return back()->with('success', 'Pembayaran berhasil diverifikasi dan tagihan ditandai lunas.');
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403, 'Konfirmasi pembayaran hanya dapat dilakukan admin.');
        $validated = $request->validate(['notes' => ['nullable', 'string', 'max:500']]);
        $payment = Payment::query()->findOrFail($id);
        abort_unless($payment->status === 'pending' && $payment->paid_at, 422, 'Pembayaran belum dikirim warga.');
        $payment->update(['status' => 'rejected', 'verified_by' => $user->id, 'verified_at' => now(), 'notes' => $validated['notes'] ?? null]);
        return back()->with('success', 'Pembayaran ditolak. Warga dapat mengajukan pembayaran ulang.');
    }

    public function storeOffice(Request $request)
    {
        abort(403, 'Pembayaran hanya dapat dilakukan oleh warga melalui akun masing-masing.');
    }

}
