<?php

namespace App\Services;

use App\Models\MeterReading;
use App\Models\BillingPeriod;
use App\Models\WaterBill;
use Carbon\Carbon;

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
        $meter = $reading->waterMeter;
        if (!$meter || !$meter->user_id) {
            return null;
        }

        $readingDate = $reading->reading_date instanceof Carbon
            ? $reading->reading_date
            : Carbon::parse($reading->reading_date);

        $period = $readingDate->format('Y-m');

        $activePeriod = BillingPeriod::query()->where('status', 'active')->first();
        if (!$activePeriod || $activePeriod->billing_period !== $period) {
            return null;
        }

        $existing = WaterBill::query()
            ->where('water_meter_id', $meter->id)
            ->where('billing_period', $period)
            ->first();

        if ($existing) {
            if ($existing->status === 'unpaid' && !$existing->payment && (float) $reading->reading_value >= (float) $reading->previous_value) {
                $usage = (float) $reading->reading_value - (float) $reading->previous_value;
                $existing->update([
                    'previous_reading' => $reading->previous_value,
                    'current_reading' => $reading->reading_value,
                    'usage_m3' => $usage,
                    'rate_per_m3' => self::DEFAULT_RATE_PER_M3,
                    'admin_fee' => self::DEFAULT_ADMIN_FEE,
                    'total_amount' => ($usage * self::DEFAULT_RATE_PER_M3) + self::DEFAULT_ADMIN_FEE,
                ]);
            }
            return $existing;
        }

        $usage = max(0, (float) $reading->reading_value - (float) $reading->previous_value);
        $rate = self::DEFAULT_RATE_PER_M3;
        $adminFee = self::DEFAULT_ADMIN_FEE;

        return WaterBill::create([
            'user_id' => $meter->user_id,
            'water_meter_id' => $meter->id,
            'billing_period' => $period,
            'previous_reading' => $reading->previous_value,
            'current_reading' => $reading->reading_value,
            'usage_m3' => $usage,
            'rate_per_m3' => $rate,
            'admin_fee' => $adminFee,
            'total_amount' => ($usage * $rate) + $adminFee,
            'due_date' => $readingDate->copy()->endOfMonth()->toDateString(),
            'status' => 'unpaid',
            'year' => (int) $readingDate->format('Y'),
            'created_by' => $createdBy,
        ]);
    }
}
