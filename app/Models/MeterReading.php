<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $attributes = ['requires_review' => true];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'water_meter_id',
        'reading_value',
        'previous_value',
        'reading_date',
        'photo',
        'recorded_by',
        'notes',
        'requires_review',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reading_date' => 'date',
            'reading_value' => 'decimal:2',
            'previous_value' => 'decimal:2',
            'verified_at' => 'datetime',
            'requires_review' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Relasi: Pembacaan meter dimiliki oleh satu meter air.
     */
    public function waterMeter(): BelongsTo
    {
        return $this->belongsTo(WaterMeter::class);
    }

    /**
     * Relasi: Pembacaan meter dicatat oleh satu user (petugas).
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(MeterReadingChange::class)->orderByDesc('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(MeterReadingReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(MeterReadingReview::class)->latestOfMany();
    }

    public function bill(): HasOne
    {
        return $this->hasOne(WaterBill::class);
    }

    /** Tidak memasukkan verified_at agar persetujuan tetap cocok dengan data yang diperiksa. */
    public function reviewRevision(): string
    {
        return hash('sha256', json_encode([
            $this->id, $this->water_meter_id, $this->previous_value, $this->reading_value,
            $this->reading_date->toDateString(), $this->recorded_by, $this->notes, $this->photo,
            $this->trashed(), $this->changes()->max('id'),
        ], JSON_THROW_ON_ERROR));
    }

    public function reviewLabel(): string
    {
        if ($this->trashed()) return 'Dibatalkan';
        if (! $this->requires_review) return 'Catatan lama';
        if ($this->verified_at) return 'Disetujui';
        $review = $this->latestReview;
        if ($review && $review->reading_revision !== $this->reviewRevision()) return 'Menunggu pemeriksaan ulang';
        return $review?->label() ?? 'Menunggu pemeriksaan';
    }
}
