<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Complaint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ticket_number',
        'category_id',
        'area_id',
        'location',
        'description',
        'photo',
        'occurred_at',
        'status',
        'assigned_to',
        'rejection_reason',
        'resolved_at',
        'resolution_notes',
        'resolution_photo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    // ==================== Boot ====================

    /**
     * Boot model: Generate ticket_number otomatis saat membuat pengaduan baru.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Complaint $complaint) {
            if (empty($complaint->ticket_number)) {
                $complaint->ticket_number = 'SA-' . date('Y') . '-' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ==================== Relationships ====================

    /**
     * Relasi: Pengaduan dimiliki oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Pengaduan memiliki satu kategori.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    /**
     * Relasi: Pengaduan berada di satu area.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Relasi: Pengaduan ditugaskan kepada satu user (petugas).
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relasi: Pengaduan memiliki banyak update/perkembangan.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class);
    }
}
