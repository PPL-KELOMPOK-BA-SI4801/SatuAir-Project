<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintUpdate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'complaint_id',
        'user_id',
        'status',
        'notes',
        'photo',
    ];

    // ==================== Relationships ====================

    /**
     * Relasi: Update pengaduan terkait dengan satu pengaduan.
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Relasi: Update pengaduan dibuat oleh satu user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
