<?php

namespace App\Services;

use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\MeterReadingReview;
use App\Models\User;
use App\Models\WaterBill;
use App\Models\WaterMeter;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PemeriksaanPemakaian
{
    public function __construct(private HitungPemakaianMeter $hitung, private BillingService $billing) {}

    public function snapshot(MeterReading $reading): array
    {
        $awal = $this->hitung->angkaKeSatuan($reading->previous_value ?? '0');
        $akhir = $this->hitung->angkaKeSatuan($reading->reading_value);
        return [
            'previous_value' => $this->hitung::satuanKeDesimal($awal),
            'reading_value' => $this->hitung::satuanKeDesimal($akhir),
            'usage_m3' => $this->hitung::satuanKeDesimal($this->hitung->hitung($awal, $akhir)),
            'reading_date' => $reading->reading_date->toDateString(),
            'photo' => $reading->photo, 'notes' => $reading->notes, 'recorded_by' => $reading->recorded_by,
        ];
    }

    public function lockedReason(MeterReading $reading): ?string
    {
        if ($reading->trashed()) return 'Pencatatan telah dibatalkan.';
        if (! $reading->requires_review) return 'Catatan lama dipertahankan; tidak diproses ulang melalui PBI-10.';
        if ($reading->verified_at) return 'Pencatatan sudah disetujui. Keputusan final tidak dapat diubah.';
        if ($reading->waterMeter->status !== 'active') return 'Meter air tidak aktif.';
        $period = $reading->reading_date->format('Y-m');
        if (! BillingPeriod::where('billing_period', $period)->where('status', 'active')->exists()) return 'Periode pencatatan tidak aktif.';
        if (WaterBill::where('water_meter_id', $reading->water_meter_id)->where('billing_period', $period)->exists()) return 'Tagihan periode ini sudah tersedia; pemeriksaan baru tidak boleh menggandakannya.';
        if ($reading->waterMeter->readings()->where('id', '!=', $reading->id)
            ->whereBetween('reading_date', [$period.'-01', $reading->reading_date->copy()->endOfMonth()->toDateString()])->exists()) return 'Ada pencatatan lain pada periode yang sama.';
        if ($reading->waterMeter->readings()->where('reading_date', '<', $period.'-01')
            ->where('requires_review', true)->whereNull('verified_at')->exists()) return 'Pencatatan periode sebelumnya belum disetujui.';
        $previous = $reading->waterMeter->readingBeforePeriod($period);
        if ($this->hitung->angkaKeSatuan($previous?->reading_value ?? '0') !== $this->hitung->angkaKeSatuan($reading->previous_value ?? '0')) return 'Meter awal berubah. Petugas perlu membatalkan dan mencatat ulang dengan dasar yang benar.';
        return null;
    }

    public function create(int $readingId, string $revision, array $input, User $actor): MeterReadingReview
    {
        $this->authorize($actor);
        return $this->locked($readingId, function (MeterReading $reading) use ($revision, $input, $actor) {
            $this->assertAvailable($reading);
            if (! hash_equals($reading->reviewRevision(), $revision)) $this->fail('revision', 'Pencatatan berubah. Muat ulang sebelum membuat pemeriksaan.');
            if ($reading->reviews()->where('status', 'draft')->exists()) $this->fail('review', 'Draf pemeriksaan sudah tersedia. Buka draf yang ada.');
            $latest = $reading->reviews()->latest('id')->first();
            if ($latest?->status === 'returned' && $latest->reading_revision === $reading->reviewRevision()) $this->fail('review', 'Tunggu koreksi Petugas sebelum memeriksa ulang.');
            return $reading->reviews()->create([
                'reviewer_id' => $actor->id, 'status' => 'draft', 'checks' => $input['checks'],
                'notes' => $input['notes'] ?? null, 'snapshot' => $this->snapshot($reading),
                'reading_revision' => $reading->reviewRevision(),
            ]);
        });
    }

    public function update(int $id, string $revision, array $input, User $actor): MeterReadingReview
    {
        return $this->mutate($id, $revision, $actor, function ($review, $reading) use ($input) {
            $review->update([
                'checks' => $input['checks'], 'notes' => $input['notes'] ?? null,
                'snapshot' => $this->snapshot($reading), 'reading_revision' => $reading->reviewRevision(),
                'version' => $review->version + 1,
            ]);
            return $review;
        });
    }

    public function cancel(int $id, string $revision, User $actor): MeterReadingReview
    {
        return $this->mutate($id, $revision, $actor, function ($review) {
            $review->increment('version');
            $review->delete();
            return $review;
        }, false);
    }

    public function finalize(int $id, string $revision, string $decision, User $actor): MeterReadingReview
    {
        return $this->mutate($id, $revision, $actor, function ($review, $reading) use ($decision, $actor) {
            if (! in_array($decision, ['approved', 'returned'], true)) $this->fail('decision', 'Keputusan pemeriksaan tidak valid.');
            if ($review->reading_revision !== $reading->reviewRevision()) $this->fail('revision', 'Angka atau data pencatatan telah berubah. Edit dan simpan draf dengan data terbaru sebelum memutuskan.');
            if ($decision === 'approved' && (! ($review->checks['identity_period'] ?? false) || ! ($review->checks['meter_usage'] ?? false))) $this->fail('checks', 'Kedua checklist wajib diperiksa sebelum persetujuan.');
            if ($decision === 'returned' && trim($review->notes ?? '') === '') $this->fail('notes', 'Alasan pengembalian wajib diisi melalui Edit Draf.');
            // Hitung ulang di server; angka dan usage kiriman browser tidak digunakan.
            $snapshot = $this->snapshot($reading);
            $review->update(['status' => $decision, 'snapshot' => $snapshot, 'reviewed_at' => now(), 'version' => $review->version + 1]);
            if ($decision === 'approved') {
                $reading->forceFill(['verified_at' => now()])->save();
                if (! $this->billing->createBillFromReading($reading, $actor->id)) $this->fail('review', 'Tagihan belum dapat dibuat. Persetujuan tidak disimpan.');
            }
            return $review;
        });
    }

    private function mutate(int $id, string $revision, User $actor, callable $operation, bool $requireAvailable = true): MeterReadingReview
    {
        $this->authorize($actor);
        $readingId = MeterReadingReview::withTrashed()->findOrFail($id)->meter_reading_id;
        return $this->locked($readingId, function (MeterReading $reading) use ($id, $revision, $actor, $operation, $requireAvailable) {
            $review = MeterReadingReview::withTrashed()->lockForUpdate()->findOrFail($id);
            abort_unless($review->reviewer_id === $actor->id, 403, 'Hanya pembuat draf yang dapat mengubah atau memutuskan pemeriksaan ini.');
            if ($review->trashed() || $review->status !== 'draft') $this->fail('review', 'Pemeriksaan sudah final atau draf telah dibatalkan.');
            $review->setRelation('reading', $reading);
            if (! hash_equals($review->revision(), $revision)) $this->fail('revision', 'Data berubah sejak halaman dibuka. Muat ulang data terbaru.');
            if ($requireAvailable) $this->assertAvailable($reading);
            return $operation($review, $reading);
        });
    }

    private function locked(int $id, callable $operation): mixed
    {
        return DB::transaction(function () use ($id, $operation) {
            $meterId = MeterReading::withTrashed()->findOrFail($id)->water_meter_id;
            WaterMeter::lockForUpdate()->findOrFail($meterId);
            $reading = MeterReading::withTrashed()->lockForUpdate()->findOrFail($id);
            BillingPeriod::where('billing_period', $reading->reading_date->format('Y-m'))->lockForUpdate()->first();
            return $operation($reading);
        }, 3);
    }

    private function assertAvailable(MeterReading $reading): void
    {
        if ($reason = $this->lockedReason($reading)) $this->fail('review', $reason);
    }

    private function authorize(User $actor): void
    {
        abort_unless($actor->role === 'bendahara' && $actor->status === 'active' && $actor->hasPermission('finance.manage'), 403);
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
