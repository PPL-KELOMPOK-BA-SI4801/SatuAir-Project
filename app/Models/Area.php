<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Relasi: Area memiliki banyak meter air.
     */
    public function waterMeters(): HasMany
    {
        return $this->hasMany(WaterMeter::class);
    }

    /**
     * Relasi: Area memiliki banyak pengaduan.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    // ==================== Scopes ====================

    /**
     * Scope: Filter area yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
