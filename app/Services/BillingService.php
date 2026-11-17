<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\BillingPeriod;
use App\Models\WaterBill;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public const DEFAULT_RATE_PER_M3 = 5000;
    public const DEFAULT_ADMIN_FEE = 2500;

    /**
     * Buat tagihan dari pembacaan meter (selisih × tarif + admin).
     * Tidak menimpa tagihan periode yang sudah ada.
     */
    public function createBillFromReading(MeterReading $reading, int $createdBy): ?WaterBill
    {
        if ($reading->trashed()) return null;
        if ($reading->requires_review && (! $reading->verified_at || ! $reading->reviews()
            ->where('status', 'approved')->where('reading_revision', $reading->reviewRevision())->exists())) {
            return null;
        }
        $meter = $reading->waterMeter;
        if (!$meter || !$meter->user_id) {
            return null;
        }

        $readingDate = $reading->reading_date instanceof Carbon
            ? $reading->reading_date
            : Carbon::parse($reading->reading_date);

        $period = $readingDate->format('Y-m');

        $activePeriod = BillingPeriod::query()->where('status', 'active')->where('billing_period', $period)->first();
        if (!$activePeriod) {
            return null;
        }

        $hitung = app(HitungPemakaianMeter::class);
        $awal = $hitung->angkaKeSatuan($reading->previous_value ?? '0', 'reading_value');
        $akhir = $hitung->angkaKeSatuan($reading->reading_value, 'reading_value');
        $usage = $hitung::satuanKeDesimal($hitung->hitung($awal, $akhir, 'reading_value'));

        $existing = WaterBill::query()
            ->where('water_meter_id', $meter->id)
            ->where('billing_period', $period)
            ->lockForUpdate()->first();

        if ($existing) {
            if ($reading->requires_review) {
                if ($existing->meter_reading_id !== $reading->id) {
                    throw ValidationException::withMessages(['review' => 'Tagihan periode ini berasal dari pencatatan lain.']);
                }
                return $existing;
            }
            if ($existing->status !== 'unpaid' || $existing->payment()->exists()) {
                throw ValidationException::withMessages(['reading_value' => 'Tagihan sudah diproses atau memiliki pembayaran. Pencatatan tidak dapat diubah.']);
            }
            if ($existing->status === 'unpaid') {
                $existing->update([
                    'previous_reading' => $reading->previous_value,
                    'current_reading' => $reading->reading_value,
                    'usage_m3' => $usage,
                    'total_amount' => $this->totalAmount($usage, $existing->rate_per_m3, $existing->admin_fee),
                ]);
            }
            return $existing;
        }

        $rate = self::DEFAULT_RATE_PER_M3;
        $adminFee = self::DEFAULT_ADMIN_FEE;

        return WaterBill::create([
            'meter_reading_id' => $reading->id,
            'user_id' => $meter->user_id,
            'water_meter_id' => $meter->id,
            'billing_period' => $period,
            'previous_reading' => $reading->previous_value,
            'current_reading' => $reading->reading_value,
            'usage_m3' => $usage,
            'rate_per_m3' => $rate,
            'admin_fee' => $adminFee,
            'total_amount' => $this->totalAmount($usage, (string) $rate, (string) $adminFee),
            'due_date' => $readingDate->copy()->endOfMonth()->toDateString(),
            'status' => 'unpaid',
            'year' => (int) $readingDate->format('Y'),
            'created_by' => $createdBy,
        ]);
    }

    /** Hitung uang dalam satuan sen dan bulatkan sekali ke dua desimal. */
    private function totalAmount(string $usage, string $rate, string $fee): string
    {
        $hitung = app(HitungPemakaianMeter::class);
        $units = $hitung->angkaKeSatuan($usage, 'reading_value');
        $rateUnits = $hitung->angkaKeSatuan($rate, 'reading_value');
        $feeUnits = $hitung->angkaKeSatuan($fee, 'reading_value');
        if ($units > intdiv(PHP_INT_MAX - 50, max(1, $rateUnits))) {
            throw ValidationException::withMessages(['reading_value' => 'Jumlah tagihan melebihi batas penyimpanan.']);
        }
        $total = intdiv($units * $rateUnits + 50, 100) + $feeUnits;
        if ($total > 999999999999) {
            throw ValidationException::withMessages(['reading_value' => 'Jumlah tagihan melebihi batas penyimpanan.']);
        }

        return $hitung::satuanKeDesimal($total);
    }
}
