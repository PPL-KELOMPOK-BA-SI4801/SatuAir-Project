<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\WaterMeter;
use App\Services\BillingService;
use App\Services\HitungPemakaianMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

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
        if ($user->role !== 'petugas') {
            abort(403, 'Akses ditolak.');
        }

        $areas = Area::where('is_active', true)->orderBy('name')->get();

        $query = WaterMeter::with(['user', 'area', 'latestReading', 'latestRecordedReading'])
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
    public function create($waterMeterId, HitungPemakaianMeter $hitung)
    {
        $user = Auth::user();
        if ($user->role !== 'petugas') {
            abort(403, 'Akses ditolak.');
        }

        $meter = WaterMeter::with(['user', 'area', 'latestReading'])->findOrFail($waterMeterId);
        $period = BillingPeriod::query()->where('status', 'active')->first();
        $defaultReadingDate = $period
            ? Carbon::create((int) $period->year, (int) $period->month, 10)->min(now())->toDateString()
            : now()->toDateString();
        $readingPeriod = Carbon::parse($defaultReadingDate)->format('Y-m');
        $previousReading = $meter->readingBeforePeriod($readingPeriod);
        $meterAwal = $hitung->angkaKeSatuan($previousReading?->reading_value ?? '0');
        $tercatat = $meter->readings()->where('reading_date', '>=', $readingPeriod.'-01')
            ->where('reading_date', '<', Carbon::parse($defaultReadingDate)->startOfMonth()->addMonth()->toDateString())
            ->exists();
        $bolehMencatat = $period?->billing_period === $readingPeriod && $meter->status === 'active' && ! $tercatat;
        $currentReading = $meter->readings()->where('reading_date', '>=', $readingPeriod.'-01')
            ->where('reading_date', '<', Carbon::parse($defaultReadingDate)->startOfMonth()->addMonth()->toDateString())
            ->orderByDesc('reading_date')->first();

        return view('petugas.meters.create', compact(
            'meter', 'meterAwal', 'readingPeriod', 'defaultReadingDate', 'tercatat', 'bolehMencatat', 'currentReading',
        ));
    }

    /**
     * Simpan data pembacaan meter baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, HitungPemakaianMeter $hitung)
    {
        $user = Auth::user();
        if ($user->role !== 'petugas') {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'water_meter_id' => 'required|exists:water_meters,id',
            'reading_value'  => ['required'],
            'reading_date'   => 'required|date|before_or_equal:today',
            'photo'          => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'notes'          => 'nullable|string|max:500',
        ], [
            'water_meter_id.required' => 'Meter air wajib dipilih.',
            'water_meter_id.exists'   => 'Meter air tidak ditemukan.',
            'reading_value.required'  => 'Angka pembacaan wajib diisi.',
            'reading_date.required'   => 'Tanggal pembacaan wajib diisi.',
            'reading_date.date'       => 'Format tanggal tidak valid.',
            'reading_date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'photo.image'             => 'File harus berupa gambar.',
            'photo.max'               => 'Ukuran foto maksimal 5MB.',
            'notes.max'               => 'Catatan maksimal 500 karakter.',
        ]);

        $meter = WaterMeter::findOrFail($validated['water_meter_id']);
        $readingPeriod = Carbon::parse($validated['reading_date'])->format('Y-m');
        $akhir = $hitung->angkaKeSatuan($validated['reading_value'], 'reading_value');
        if ($akhir > 9999999999) {
            throw ValidationException::withMessages([
                'reading_value' => 'Meter akhir maksimal 8 digit dan 2 angka desimal.',
            ]);
        }

        $photoPath = null;
        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('meter-photos', 'public');
            }

            $bill = DB::transaction(function () use ($validated, $readingPeriod, $akhir, $hitung, $user, $photoPath) {
                $meter = WaterMeter::query()->lockForUpdate()->findOrFail($validated['water_meter_id']);
                if ($meter->status !== 'active') {
                    throw ValidationException::withMessages(['water_meter_id' => 'Meter air tidak aktif.']);
                }

                $period = BillingPeriod::query()->where('billing_period', $readingPeriod)
                    ->where('status', 'active')->lockForUpdate()->first();
                if ($period === null) {
                    throw ValidationException::withMessages(['reading_date' => 'Catatan meter harus untuk periode tagihan yang aktif.']);
                }

                $nextPeriod = Carbon::parse($validated['reading_date'])->startOfMonth()->addMonth()->toDateString();
                if ($meter->readings()->where('reading_date', '>=', $readingPeriod.'-01')
                    ->where('reading_date', '<', $nextPeriod)->exists()) {
                    throw ValidationException::withMessages(['reading_value' => 'Pelanggan ini sudah dicatat pada periode yang sama.']);
                }
                if ($meter->readings()->where('reading_date', '>=', $nextPeriod)->exists()
                    || \App\Models\WaterBill::query()->where('water_meter_id', $meter->id)->where('billing_period', '>', $readingPeriod)->exists()) {
                    throw ValidationException::withMessages(['reading_date' => 'Sudah ada pencatatan atau tagihan lanjutan. Pencatatan mundur tidak diizinkan.']);
                }

                $previousReading = $meter->readingBeforePeriod($readingPeriod);
                $awal = $hitung->angkaKeSatuan($previousReading?->reading_value ?? '0', 'reading_value');
                $hitung->hitung($awal, $akhir, 'reading_value');

                $reading = MeterReading::create([
                    'water_meter_id' => $meter->id,
                    'reading_value' => $hitung::satuanKeDesimal($akhir),
                    'previous_value' => $hitung::satuanKeDesimal($awal),
                    'reading_date' => $validated['reading_date'],
                    'photo' => $photoPath,
                    'recorded_by' => $user->id,
                    'notes' => $validated['notes'] ?? null,
                ]);

                return app(BillingService::class)->createBillFromReading($reading, $user->id);
            }, 3);
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }

        $message = 'Pembacaan meter ' . $meter->meter_number . ' berhasil disimpan.';
        if ($bill) {
            $message .= ' Tagihan periode ' . $bill->billing_period . ' sebesar Rp ' . number_format((float) $bill->total_amount, 0, ',', '.') . ' telah dibuat.';
        }

        return redirect()
            ->route('petugas.meters.index')
            ->with('success', $message);
    }
}
