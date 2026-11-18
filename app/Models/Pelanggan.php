<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal_meter_awal' => 'date', 'meter_awal_satuan' => 'integer'];
    }

    public function pencatatan(): HasMany
    {
        return $this->hasMany(PencatatanMeter::class);
    }

    public function meterAwal(string $periode): int
    {
        return (int) ($this->pencatatan()->where('periode', '<', $periode)
            ->orderByDesc('periode')->value('meter_akhir_satuan') ?? $this->meter_awal_satuan);
    }
}
