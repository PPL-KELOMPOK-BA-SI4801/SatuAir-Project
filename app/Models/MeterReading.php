<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use HasFactory;

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
}
