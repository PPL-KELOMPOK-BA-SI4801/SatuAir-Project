<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReadingReview extends Model
{
    use SoftDeletes;

    protected $fillable = ['meter_reading_id', 'reviewer_id', 'status', 'checks', 'notes', 'snapshot', 'reading_revision', 'version', 'reviewed_at'];

    protected function casts(): array
    {
        return ['checks' => 'array', 'snapshot' => 'array', 'reviewed_at' => 'datetime', 'version' => 'integer'];
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class, 'meter_reading_id')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function revision(): string
    {
        return hash('sha256', json_encode([$this->getAttributes(), $this->reading->reviewRevision()], JSON_THROW_ON_ERROR));
    }

    public function label(): string
    {
        return $this->trashed() ? 'Draf dibatalkan' : match ($this->status) {
            'approved' => 'Disetujui', 'returned' => 'Dikembalikan untuk koreksi', default => 'Draf pemeriksaan',
        };
    }
}
