<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class WaterBill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'water_meter_id',
        'billing_period',
        'previous_reading',
        'current_reading',
        'usage_m3',
        'rate_per_m3',
        'admin_fee',
        'total_amount',
        'due_date',
        'status',
        'year',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'previous_reading' => 'decimal:2',
            'current_reading' => 'decimal:2',
            'usage_m3' => 'decimal:2',
            'rate_per_m3' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Relasi: Tagihan dimiliki oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Tagihan terkait dengan satu meter air.
     */
    public function waterMeter(): BelongsTo
    {
        return $this->belongsTo(WaterMeter::class);
    }

    /**
     * Relasi: Tagihan memiliki satu pembayaran.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Relasi: Tagihan dibuat oleh satu user (admin/petugas).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    /**
     * Scope: Filter tagihan yang belum dibayar.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    /**
     * Scope: Filter tagihan yang sudah dibayar.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Filter tagihan yang sudah jatuh tempo.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'unpaid')
                     ->where('due_date', '<', now());
    }

    /**
     * Scope: Filter tagihan berdasarkan tahun.
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    /**
     * Scope: Filter tagihan berdasarkan periode.
     */
    public function scopeByPeriod(Builder $query, string $period): Builder
    {
        return $query->where('billing_period', $period);
    }

    // ==================== Accessors ====================

    /**
     * Accessor: Format total tagihan ke format Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->total_amount, 0, ',', '.');
    }

    /**
     * Accessor: Cek apakah tagihan sudah melewati jatuh tempo.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'unpaid' && $this->due_date && $this->due_date->isPast();
    }
}
