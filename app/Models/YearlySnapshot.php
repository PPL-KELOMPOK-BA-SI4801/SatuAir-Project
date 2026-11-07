<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YearlySnapshot extends Model
{
    protected $fillable = [
        'year',
        'total_revenue',
        'total_bills_generated',
        'total_customers',
        'total_bills_count',
        'paid_bills_count',
        'unpaid_bills_count',
        'total_arrears',
        'monthly_breakdown',
        'is_locked',
        'locked_at',
        'locked_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_revenue' => 'decimal:2',
            'total_bills_generated' => 'decimal:2',
            'total_arrears' => 'decimal:2',
            'monthly_breakdown' => 'array',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Pendapatan tahun berjalan dihitung dari tanggal bayar.
     * Jika tahun sudah dikunci, angka snapshot tidak ikut berubah.
     */
    public static function revenueForYear(int $year): float
    {
        $snapshot = static::query()->where('year', $year)->where('is_locked', true)->first();
        if ($snapshot) {
            return (float) $snapshot->total_revenue;
        }

        return static::liveRevenue($year);
    }

    public static function monthlyRevenueForYear(int $year): array
    {
        $snapshot = static::query()->where('year', $year)->where('is_locked', true)->first();
        if ($snapshot && is_array($snapshot->monthly_breakdown)) {
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = (float) ($snapshot->monthly_breakdown[$m] ?? $snapshot->monthly_breakdown[(string) $m] ?? 0);
            }
            return $months;
        }

        return static::liveMonthlyRevenue($year);
    }

    public static function liveRevenue(int $year): float
    {
        return (float) Payment::query()
            ->where('status', 'verified')
            ->where(function ($query) use ($year) {
                $query->whereYear('paid_at', $year)
                    ->orWhere(function ($fallback) use ($year) {
                        $fallback->whereNull('paid_at')->whereYear('verified_at', $year);
                    });
            })
            ->sum('amount');
    }

    public static function liveMonthlyRevenue(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = (float) Payment::query()
                ->where('status', 'verified')
                ->where(function ($query) use ($year, $m) {
                    $query->where(function ($paid) use ($year, $m) {
                        $paid->whereYear('paid_at', $year)->whereMonth('paid_at', $m);
                    })->orWhere(function ($fallback) use ($year, $m) {
                        $fallback->whereNull('paid_at')
                            ->whereYear('verified_at', $year)
                            ->whereMonth('verified_at', $m);
                    });
                })
                ->sum('amount');
        }

        return $months;
    }

    public static function capture(int $year, ?User $user = null, bool $lock = false): self
    {
        $monthly = static::liveMonthlyRevenue($year);
        $bills = WaterBill::query()->where('year', $year);
        $unpaid = (clone $bills)->where('status', 'unpaid');

        $payload = [
            'total_revenue' => static::liveRevenue($year),
            'total_bills_generated' => (float) (clone $bills)->sum('total_amount'),
            'total_customers' => User::query()->where('role', 'masyarakat')->count(),
            'total_bills_count' => (clone $bills)->count(),
            'paid_bills_count' => (clone $bills)->where('status', 'paid')->count(),
            'unpaid_bills_count' => $unpaid->count(),
            'total_arrears' => (float) $unpaid->sum('total_amount'),
            'monthly_breakdown' => $monthly,
        ];

        if ($lock) {
            $payload['is_locked'] = true;
            $payload['locked_at'] = now();
            $payload['locked_by'] = $user?->id;
            $payload['notes'] = 'Tutup buku tanggal ' . now()->format('d-m-Y') . '. Pembayaran tahun berikutnya tidak mengubah angka ini.';
        }

        return static::query()->updateOrCreate(['year' => $year], $payload);
    }
}
