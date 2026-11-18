<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\WaterBill;
use App\Models\BillingPeriod;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()?->hasPermission('users.manage')) {
            abort(403, 'Akses ditolak.');
        }

        $query = User::query()->orderBy('role')->orderBy('name');

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('bendahara.users.index', compact('users'));
    }

    public function showCustomer(User $user)
    {
        $this->authorizeCustomerProfile($user);

        return view('lihat-edit-pelanggan', [
            'customer' => $user,
            'editable' => false,
            'ownProfile' => false,
        ]);
    }

    public function editCustomerProfile(User $user)
    {
        $this->authorizeCustomerProfile($user);

        return view('lihat-edit-pelanggan', [
            'customer' => $user,
            'editable' => true,
            'ownProfile' => false,
        ]);
    }

    public function updateCustomerProfile(Request $request, User $user)
    {
        $this->authorizeCustomerProfile($user);
        $user->update($this->validatedProfile($request, $user));

        return redirect()->route('bendahara.pengguna.show', $user)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function editOwnProfile()
    {
        abort_unless(Auth::user()?->role === 'bendahara', 403, 'Akses ditolak.');

        return view('lihat-edit-pelanggan', [
            'customer' => Auth::user(),
            'editable' => true,
            'ownProfile' => true,
        ]);
    }

    public function updateOwnProfile(Request $request)
    {
        $user = Auth::user();
        abort_unless($user?->role === 'bendahara', 403, 'Akses ditolak.');
        $user->update($this->validatedProfile($request, $user));

        return redirect()->route('bendahara.profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    private function authorizeCustomerProfile(User $user): void
    {
        abort_unless(Auth::user()?->hasPermission('users.manage'), 403, 'Akses ditolak.');
        abort_unless($user->role === 'masyarakat', 404);
    }

    private function validatedProfile(Request $request, User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * Tampilkan form edit untuk warga (meter & tarif) atau petugas (profil).
     */
    public function edit(User $user)
    {
        if (! Auth::user()?->hasPermission('users.manage')) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($user->role, ['masyarakat', 'petugas'])) {
            abort(403, 'Hanya warga dan petugas yang dapat diedit.');
        }

        $meter = $user->waterMeters()->first();
        $latestReading = $meter ? $meter->latestReading : null;
        $latestBill = $user->waterBills()->latest('billing_period')->first();

        return view('bendahara.users.edit', compact('user', 'meter', 'latestReading', 'latestBill'));
    }

    /**
     * Update user (meter & tarif untuk warga, profil untuk petugas).
     */
    public function update(Request $request, User $user)
    {
        if (! Auth::user()?->hasPermission('users.manage')) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($user->role, ['masyarakat', 'petugas'])) {
            abort(403, 'Hanya warga dan petugas yang dapat diedit.');
        }

        if ($user->role === 'masyarakat') {
            $request->validate([
                'current_reading' => ['required', 'numeric', 'min:0'],
                'tarif_per_m3'    => ['required', 'numeric', 'min:0'],
            ]);

            $bendahara = Auth::user();

            // Ambil atau buat water meter untuk user
            $meter = $user->waterMeters()->first();
            if (!$meter) {
                $meter = WaterMeter::create([
                    'user_id'    => $user->id,
                    'meter_number' => 'MTR-' . strtoupper(Str::random(6)),
                    'area_id'    => null,
                    'location'   => null,
                    'status'     => 'active',
                ]);
            }

            // Simpan pembacaan meter baru
            $currentReading = (float) $request->current_reading;
            $previousValue = (float) ($meter->latestReading?->reading_value ?? 0);
            if ($currentReading < $previousValue) {
                return back()->withInput()->withErrors(['current_reading' => 'Angka meter tidak boleh lebih kecil dari pencatatan sebelumnya (' . $previousValue . ').']);
            }
            $activePeriod = BillingPeriod::query()->where('status', 'active')->first();

            if ($activePeriod) {
                $readingDate = Carbon::create((int) $activePeriod->year, (int) $activePeriod->month, 10);
                if ($readingDate->isFuture()) {
                    $readingDate = now();
                }
                $reading = MeterReading::query()->where('water_meter_id', $meter->id)
                    ->whereYear('reading_date', $activePeriod->year)->whereMonth('reading_date', $activePeriod->month)
                    ->latest('reading_date')->first();
                if ($reading) {
                    if (($reading->requires_review || $reading->verified_at) && (float) $reading->reading_value !== $currentReading) {
                        return back()->withInput()->withErrors(['current_reading' => 'Angka pencatatan yang memerlukan pemeriksaan tidak dapat diubah melalui Manajemen Pengguna. Gunakan alur koreksi Petugas.']);
                    }
                    if (! $reading->requires_review && ! $reading->verified_at) {
                        $reading->update(['reading_value' => $currentReading, 'recorded_by' => $bendahara->id, 'notes' => 'Diupdate oleh bendahara via Manajemen Pengguna']);
                    }
                } else {
                    $reading = MeterReading::create([
                        'water_meter_id' => $meter->id,
                        'reading_value'  => $currentReading,
                        'previous_value' => $previousValue,
                        'reading_date'   => $readingDate->toDateString(),
                        'recorded_by'    => $bendahara->id,
                        'notes'          => 'Diupdate oleh bendahara via Manajemen Pengguna',
                    ]);
                }
                app(BillingService::class)->createBillFromReading($reading, $bendahara->id);
            }

            $user->waterBills()->where('status', 'unpaid')->when($activePeriod, fn ($query) => $query->where('billing_period', $activePeriod->billing_period))->get()->each(function (WaterBill $bill) use ($request) {
                $rate = (float) $request->tarif_per_m3;
                $bill->update(['rate_per_m3' => $rate, 'total_amount' => ((float) $bill->usage_m3 * $rate) + (float) $bill->admin_fee]);
            });

            return back()->with('success', "Meter & tarif untuk {$user->name} berhasil diupdate. Pembacaan baru: {$currentReading} m³, Tarif: Rp " . number_format($request->tarif_per_m3, 0, ',', '.') . " / m³.");
        }

        // Petugas: update profil
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', "Data petugas {$user->name} berhasil diupdate.");
    }
}
