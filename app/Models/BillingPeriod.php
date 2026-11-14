<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPeriod extends Model
{
    protected $fillable = [
        'year', 'month', 'billing_period', 'status', 'opened_by', 'closed_by', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
    }
}
