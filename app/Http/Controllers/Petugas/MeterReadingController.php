<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\WaterMeter;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller untuk pencatatan meter air oleh petugas.
 *
 * Menampilkan daftar meter air, form input pembacaan baru,
 * dan menyimpan data pembacaan meter.
 */
class MeterReadingController extends Controller
{
    /**
     * Tampilkan daftar semua meter air beserta pembacaan terakhir.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $areas = Area::where('is_active', true)->orderBy('name')->get();

        $query = WaterMeter::with(['user', 'area', 'latestReading'])
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('meter_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        $meters = $query->orderBy('meter_number')->paginate(15)->withQueryString();

        return view('petugas.meters.index', compact('meters', 'areas'));
    }

    /**
     * Tampilkan form input pembacaan baru untuk meter tertentu.
     *
     * @param  int  $waterMeterId
     * @return \Illuminate\View\View
     */
    public function create($waterMeterId)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $meter = WaterMeter::with(['user', 'area', 'latestReading'])->findOrFail($waterMeterId);
        $previousReading = $meter->latestReading;
        $period = BillingPeriod::query()->where('status', 'active')->first();
        $defaultReadingDate = $period
            ? Carbon::create((int) $period->year, (int) $period->month, 10)->min(now())->toDateString()
            : now()->toDateString();

        return view('petugas.meters.create', compact('meter', 'previousReading', 'defaultReadingDate'));
    }

    /**
     * Simpan data pembacaan meter baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['petugas', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading_value'  => 'required|numeric|min:0',
            'reading_date'   => 'required|date|before_or_equal:today',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'notes'          => 'nullable|string|max:500',
        ], [
            'water_meter_id.required' => 'Meter air wajib dipilih.',
            'water_meter_id.exists'   => 'Meter air tidak ditemukan.',
            'reading_value.required'  => 'Angka pembacaan wajib diisi.',
            'reading_value.numeric'   => 'Angka pembacaan harus berupa angka.',
            'reading_value.min'       => 'Angka pembacaan tidak boleh negatif.',
            'reading_date.required'   => 'Tanggal pembacaan wajib diisi.',
            'reading_date.date'       => 'Format tanggal tidak valid.',
            'reading_date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'photo.image'             => 'File harus berupa gambar.',
            'photo.max'               => 'Ukuran foto maksimal 5MB.',
            'notes.max'               => 'Catatan maksimal 500 karakter.',
        ]);

        $meter = WaterMeter::findOrFail($validated['water_meter_id']);
        $readingPeriod = Carbon::parse($validated['reading_date'])->format('Y-m');
        abort_unless(BillingPeriod::query()->where('billing_period', $readingPeriod)->where('status', 'active')->exists(), 422, 'Catatan meter harus untuk periode tagihan yang aktif.');
        $latestReading = $meter->latestReading;
        $previousValue = $latestReading ? (float) $latestReading->reading_value : 0;

        if ((float) $validated['reading_value'] < $previousValue) {
            return back()
                ->withInput()
                ->withErrors(['reading_value' => 'Angka meter tidak boleh lebih kecil dari pencatatan sebelumnya (' . $previousValue . ').']);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('meter-photos', 'public');
        }

        $reading = MeterReading::create([
            'water_meter_id' => $validated['water_meter_id'],
            'reading_value'  => $validated['reading_value'],
            'previous_value' => $previousValue,
            'reading_date'   => $validated['reading_date'],
            'photo'          => $photoPath,
            'recorded_by'    => $user->id,
            'notes'          => $validated['notes'] ?? null,
        ]);

        $bill = app(BillingService::class)->createBillFromReading($reading, $user->id);

        $message = 'Pembacaan meter ' . $meter->meter_number . ' berhasil disimpan.';
        if ($bill) {
            $message .= ' Tagihan periode ' . $bill->billing_period . ' sebesar Rp ' . number_format((float) $bill->total_amount, 0, ',', '.') . ' telah dibuat.';
        }

        return redirect()
            ->route('petugas.meters.index')
            ->with('success', $message);
    }
}
