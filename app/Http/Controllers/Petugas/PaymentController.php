<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($user->role !== 'petugas') {
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
        if ($user->role !== 'petugas') {
            abort(403, 'Akses ditolak.');
        }

        $payment = Payment::with(['user', 'waterBill.waterMeter.area', 'waterBill.user', 'verifier'])
            ->findOrFail($id);

        return view('petugas.payments.show', compact('payment'));
    }

    public function storeOffice(Request $request)
    {
        abort(403, 'Pembayaran hanya dapat dilakukan oleh warga melalui akun masing-masing.');
    }

}
