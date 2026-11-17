<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\BillingPeriod;
use App\Models\MeterReading;
use App\Models\User;
use App\Models\WaterMeter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Data untuk mencoba integrasi PBI-08 dan PBI-09 pada SQLite demo yang terpisah. */
class Pbi09DemoSeeder extends Seeder
{
    public function run(): void
    {
        $database = config('database.connections.sqlite.database');
        if (! app()->environment(['local', 'testing']) || config('database.default') !== 'sqlite'
            || realpath($database) === realpath(database_path('database.sqlite'))) {
            throw new RuntimeException('Gunakan database SQLite demo terpisah di environment local/testing.');
        }
        if (User::query()->exists() || WaterMeter::query()->exists()) {
            throw new RuntimeException('Seeder demo hanya boleh dijalankan pada database baru yang belum berisi pengguna/meter.');
        }

        DB::transaction(function () {
            $this->call(AccessControlSeeder::class);
            $password = Hash::make('password123');
            $petugas = User::create([
                'name' => 'Petugas Demo PBI-09', 'email' => 'petugas@gmail.com',
                'password' => $password, 'role' => 'petugas', 'status' => 'active',
            ]);
            $month = now()->startOfMonth();
            BillingPeriod::query()->update(['status' => 'closed']);
            BillingPeriod::updateOrCreate(['billing_period' => $month->format('Y-m')], [
                'year' => $month->year, 'month' => $month->month, 'status' => 'active',
                'opened_by' => $petugas->id, 'opened_at' => now(),
            ]);

            foreach ([
                ['Rina Marlina', 'CUST-00001', 'MTR-CBD-001', 'Dusun I - Cibodas Krajan', '530.00'],
                ['Budi Santoso', 'CUST-00002', 'MTR-CBD-002', 'Dusun III - Sukamaju', '317.00'],
            ] as [$name, $customerId, $meterNumber, $areaName, $reading]) {
                $user = User::create([
                    'name' => $name, 'email' => strtolower(strtok($name, ' ')).'@example.test',
                    'customer_id' => $customerId, 'password' => $password,
                    'role' => 'masyarakat', 'status' => 'active',
                ]);
                $area = Area::create(['name' => $areaName, 'is_active' => true]);
                $meter = WaterMeter::create([
                    'user_id' => $user->id, 'area_id' => $area->id,
                    'meter_number' => $meterNumber, 'status' => 'active',
                ]);
                MeterReading::create([
                    'requires_review' => false,
                    'water_meter_id' => $meter->id, 'previous_value' => '0.00',
                    'reading_value' => $reading, 'reading_date' => $month->copy()->subMonth()->toDateString(),
                    'recorded_by' => $petugas->id,
                ]);
            }
        });
    }
}
