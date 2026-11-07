<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\WaterBill;
use App\Models\BillingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $query = WaterBill::where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->with(['payment', 'waterMeter']);

        // Filter berdasarkan tahun
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter berdasarkan periode
        if ($request->filled('period')) {
            $query->where('billing_period', 'like', '%' . $request->period . '%');
        }

        $bills = $query->orderBy('billing_period', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // Daftar tahun untuk filter
        $years = WaterBill::where('user_id', $user->id)
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();

        return view('masyarakat.bills.index', compact('bills', 'years', 'activePeriod'));
    }

    public function show($id)
    {
        $user = Auth::user();
        abort_unless($user->role === 'masyarakat', 403);

        $bill = WaterBill::where('user_id', $user->id)
            ->with(['payment', 'waterMeter'])
            ->findOrFail($id);
        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();

        return view('masyarakat.bills.show', compact('bill', 'activePeriod'));
    }
}
