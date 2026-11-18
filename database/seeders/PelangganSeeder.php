<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['kode' => 'CUST-00001', 'nama' => 'Rina Marlina', 'nomor_meter' => 'MTR-CBD-001', 'wilayah' => 'Dusun I - Cibodas Krajan', 'meter_awal_satuan' => 53000],
            ['kode' => 'CUST-00002', 'nama' => 'Budi Santoso', 'nomor_meter' => 'MTR-CBD-002', 'wilayah' => 'Dusun III - Sukamaju', 'meter_awal_satuan' => 31700],
        ] as $data) {
            Pelanggan::firstOrCreate(['kode' => $data['kode']], $data + ['tanggal_meter_awal' => '2026-09-10']);
        }
    }
}
