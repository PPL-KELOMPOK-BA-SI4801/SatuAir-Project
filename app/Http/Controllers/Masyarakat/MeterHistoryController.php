<?php

namespace App\Http\Controllers\Masyarakat;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeterHistoryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|digits:4',
        ]);

        $user = auth()->user();

        $query = MeterReading::with('waterMeter')
            ->whereHas('waterMeter', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($request->filled('search')) {
            $query->whereDate('reading_date', $request->search);
        }

        if ($request->filled('month')) {
            $query->whereMonth('reading_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('reading_date', $request->year);
        }

        $totalRecords = (clone $query)->count();

        $totalUsage = (clone $query)
            ->sum(DB::raw('reading_value - COALESCE(previous_value, 0)'));

        $readings = $query
            ->orderByDesc('reading_date')
            ->paginate(10)
            ->withQueryString();

        $years = MeterReading::whereHas('waterMeter', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->selectRaw("strftime('%Y', reading_date) as year")
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('masyarakat.meter-history.index', compact(
            'readings',
            'totalRecords',
            'totalUsage',
            'years'
        ));
    }
}