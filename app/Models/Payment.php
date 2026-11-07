<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'water_bill_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_proof',
        'transaction_id',
        'reference_number',
        'status',
        'verified_by',
        'verified_at',
        'notes',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    // ==================== Boot ====================

    /**
     * Boot model: Generate reference_number otomatis saat membuat pembayaran baru.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            if (empty($payment->reference_number)) {
                $payment->reference_number = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    // ==================== Relationships ====================

    /**
     * Relasi: Pembayaran terkait dengan satu tagihan air.
     */
    public function waterBill(): BelongsTo
    {
        return $this->belongsTo(WaterBill::class);
    }

    /**
     * Relasi: Pembayaran dilakukan oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Pembayaran diverifikasi oleh satu user (admin/petugas).
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ==================== Scopes ====================

    /**
     * Scope: Filter pembayaran yang menunggu verifikasi.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Filter pembayaran yang sudah diverifikasi.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }
}
