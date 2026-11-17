<?php

namespace App\Services;

use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Mengatur koreksi/pembatalan; semua rumus meter tetap di HitungPemakaianMeter. */
class KoreksiPencatatanMeter
{
    public function __construct(private HitungPemakaianMeter $hitung, private BillingService $billing) {}

    public function alasanTerkunci(MeterReading $reading): ?string
    {
        if ($reading->trashed()) {
            return 'Pencatatan ini sudah dibatalkan. Riwayatnya tetap tersimpan.';
        }
        if ($reading->verified_at !== null) {
            return 'Pencatatan sudah diverifikasi. Edit dan pembatalan dikunci.';
        }
        if ($reading->waterMeter->status !== 'active') {
            return 'Meter air tidak aktif.';
        }

        $period = $reading->reading_date->format('Y-m');
        if (! BillingPeriod::query()->where('billing_period', $period)->where('status', 'active')->exists()) {
            return 'Periode pencatatan sudah ditutup atau tidak aktif.';
        }
        if ($reading->waterMeter->readings()->where('id', '!=', $reading->id)
            ->where('reading_date', '>=', $reading->reading_date->copy()->startOfMonth()->toDateString())->exists()
            || WaterBill::query()->where('water_meter_id', $reading->water_meter_id)->where('billing_period', '>', $period)->exists()) {
            return 'Sudah ada pencatatan atau tagihan lanjutan. Data ini tidak dapat diubah.';
        }

        $bills = $this->bills($reading)->get();
        if ($bills->count() > 1) {
            return 'Terdapat lebih dari satu tagihan pada periode ini. Hubungi Bendahara untuk memeriksa data.';
        }
        foreach ($bills as $bill) {
            if ($bill->status !== 'unpaid' || $bill->payment()->exists()) {
                return 'Tagihan sudah diproses atau memiliki pembayaran. Edit dan pembatalan dikunci.';
            }
        }

        return null;
    }

    /** Deteksi halaman lama, termasuk dua perubahan yang terjadi dalam detik yang sama. */
    public function revision(MeterReading $reading): string
    {
        return hash('sha256', json_encode([
            $reading->getAttributes(), $this->bills($reading)->get()->map->getAttributes()->all(),
            $reading->changes()->max('id'),
        ], JSON_THROW_ON_ERROR));
    }

    public function ubah(int $id, string $revision, mixed $value, string $reason, int $actor): MeterReading
    {
        return $this->mutate($id, $revision, function (MeterReading $reading) use ($value, $reason, $actor) {
            $awal = $this->hitung->angkaKeSatuan($reading->previous_value ?? '0', 'reading_value');
            $akhir = $this->hitung->angkaKeSatuan($value, 'reading_value');
            if ($akhir > 9999999999) {
                throw ValidationException::withMessages(['reading_value' => 'Meter akhir maksimal 8 digit dan 2 angka desimal.']);
            }
            $this->hitung->hitung($awal, $akhir, 'reading_value');
            if ($akhir === $this->hitung->angkaKeSatuan($reading->reading_value)) {
                throw ValidationException::withMessages(['reading_value' => 'Angka meter akhir belum berubah.']);
            }

            $before = $this->snapshot($reading);
            $reading->update(['reading_value' => $this->hitung::satuanKeDesimal($akhir)]);
            $this->billing->createBillFromReading($reading, $actor);
            $this->record($reading, 'corrected', $reason, $actor, $before);

            return $reading;
        });
    }

    public function batalkan(int $id, string $revision, string $reason, int $actor): MeterReading
    {
        return $this->mutate($id, $revision, function (MeterReading $reading) use ($reason, $actor) {
            $before = $this->snapshot($reading);
            foreach ($this->bills($reading)->get() as $bill) {
                $bill->delete();
            }
            $reading->delete();
            $this->record($reading, 'cancelled', $reason, $actor, $before);

            return $reading;
        });
    }

    private function mutate(int $id, string $revision, callable $operation): MeterReading
    {
        return DB::transaction(function () use ($id, $revision, $operation) {
            $meterId = MeterReading::withTrashed()->findOrFail($id)->water_meter_id;
            WaterMeter::query()->lockForUpdate()->findOrFail($meterId);
            $reading = MeterReading::withTrashed()->lockForUpdate()->findOrFail($id);
            BillingPeriod::query()->where('billing_period', $reading->reading_date->format('Y-m'))->lockForUpdate()->first();
            $this->bills($reading)->lockForUpdate()->get();

            if ($reason = $this->alasanTerkunci($reading)) {
                throw ValidationException::withMessages(['reading' => $reason]);
            }
            if (! hash_equals($this->revision($reading), $revision)) {
                throw ValidationException::withMessages(['revision' => 'Data telah berubah sejak halaman dibuka. Muat ulang halaman sebelum mencoba kembali.']);
            }

            return $operation($reading);
        }, 3);
    }

    private function bills(MeterReading $reading)
    {
        return WaterBill::query()->where('water_meter_id', $reading->water_meter_id)
            ->where('billing_period', $reading->reading_date->format('Y-m'))->orderBy('id');
    }

    private function snapshot(MeterReading $reading): array
    {
        return [
            'previous_value' => $reading->previous_value, 'reading_value' => $reading->reading_value,
            'reading_date' => $reading->reading_date->toDateString(), 'cancelled' => $reading->trashed(),
            'bills' => $this->bills($reading)->get(['id', 'usage_m3', 'rate_per_m3', 'admin_fee', 'total_amount'])->toArray(),
        ];
    }

    private function record(MeterReading $reading, string $action, string $reason, int $actor, array $before): void
    {
        $reading->changes()->create([
            'action' => $action, 'reason' => $reason, 'changed_by' => $actor,
            'before' => $before, 'after' => $this->snapshot($reading),
        ]);
    }
}
