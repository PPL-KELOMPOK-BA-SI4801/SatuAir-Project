<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'address',
        'customer_id',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    // ==================== Relationships ====================

    /**
     * Relasi: User memiliki banyak meter air.
     */
    public function waterMeters(): HasMany
    {
        return $this->hasMany(WaterMeter::class);
    }

    /**
     * Relasi: User memiliki banyak tagihan air.
     */
    public function waterBills(): HasMany
    {
        return $this->hasMany(WaterBill::class);
    }

    /**
     * Relasi: User memiliki banyak pembayaran.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relasi: User memiliki banyak pengaduan.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    // ==================== Helper Methods ====================

    /**
     * Cek apakah user adalah Bendahara.
     */
    public function isBendahara(): bool
    {
        return $this->role === 'bendahara';
    }

    /**
     * Cek apakah user adalah Admin / Bendahara (kompatibel).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['bendahara', 'admin']);
    }

    /**
     * Cek apakah user adalah Petugas.
     */
    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    /**
     * Cek apakah user adalah Masyarakat.
     */
    public function isMasyarakat(): bool
    {
        return $this->role === 'masyarakat';
    }

    // ==================== Scopes ====================

    /**
     * Scope: Filter user berdasarkan role.
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}
