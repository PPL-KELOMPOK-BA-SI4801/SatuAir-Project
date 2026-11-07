<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Area;
use App\Models\ComplaintCategory;
use App\Models\WaterMeter;
use App\Models\MeterReading;
use App\Models\WaterBill;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\ComplaintUpdate;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Wilayah / Dusun Desa Cibodas
        $areas = [
            ['name' => 'Dusun I - Cibodas Krajan', 'description' => 'Wilayah pusat desa dan balai desa', 'is_active' => true],
            ['name' => 'Dusun II - Pasir Muncang', 'description' => 'Wilayah elevasi atas / dekat mata air', 'is_active' => true],
            ['name' => 'Dusun III - Sukamaju', 'description' => 'Wilayah jalur pipa sekunder barat', 'is_active' => true],
            ['name' => 'Dusun IV - Mekarsari', 'description' => 'Wilayah ujung distribusi selatan', 'is_active' => true],
        ];
        $createdAreas = [];
        foreach ($areas as $a) {
            $createdAreas[] = Area::create($a);
        }

        // 2. Buat Kategori Pengaduan
        $categories = [
            ['name' => 'Air Tidak Mengalir', 'description' => 'Aliran air mati total ke sambungan rumah', 'is_active' => true],
            ['name' => 'Debit Air Menurun', 'description' => 'Aliran air mengecil drastis dari biasanya', 'is_active' => true],
            ['name' => 'Kebocoran Pipa', 'description' => 'Pipa distribusi atau tersier pecah/rembes', 'is_active' => true],
            ['name' => 'Kualitas Air Bermasalah', 'description' => 'Air keruh, berbau, atau berlumpur', 'is_active' => true],
            ['name' => 'Kerusakan Fasilitas / Meteran', 'description' => 'Kran induk, valve, atau meteran air rusak', 'is_active' => true],
            ['name' => 'Lainnya', 'description' => 'Permasalahan operasional air lainnya', 'is_active' => true],
        ];
        $createdCategories = [];
        foreach ($categories as $c) {
            $createdCategories[] = ComplaintCategory::create($c);
        }

        // 3. Buat Akun Pengguna (Bendahara, Petugas, Masyarakat)
        $bendahara = User::create([
            'name' => 'Pak Hendra (Bendahara BUMDes)',
            'email' => 'bendahara@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'bendahara',
            'phone' => '081234567890',
            'address' => 'Kantor BUMDes Karya Mandiri, Desa Cibodas',
            'status' => 'active',
            'customer_id' => 'BND-001',
        ]);

        $petugas = User::create([
            'name' => 'Ahmad Fauzi (Petugas PAB)',
            'email' => 'petugas@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'petugas',
            'phone' => '081234567891',
            'address' => 'Posko Operasional Air, Dusun II Cibodas',
            'status' => 'active',
            'customer_id' => 'PTG-001',
        ]);

        $masyarakat = User::create([
            'name' => 'Rina Marlina',
            'email' => 'rina@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'masyarakat',
            'phone' => '081234567892',
            'address' => 'Dusun I Cibodas Krajan RT 02 RW 01 No. 12',
            'status' => 'active',
            'customer_id' => 'CUST-00001',
        ]);

        $wargaDua = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'masyarakat',
            'phone' => '081234567893',
            'address' => 'Dusun III Sukamaju RT 04 RW 02 No. 05',
            'status' => 'active',
            'customer_id' => 'CUST-00002',
        ]);

        // 4. Buat Meteran Air Pelanggan
        $meterRina = WaterMeter::create([
            'user_id' => $masyarakat->id,
            'meter_number' => 'MTR-CBD-001',
            'area_id' => $createdAreas[0]->id,
            'location' => 'Depan Pagar Rumah Rina, RT 02 RW 01',
            'installed_at' => '2024-05-10',
            'status' => 'active',
        ]);

        $meterBudi = WaterMeter::create([
            'user_id' => $wargaDua->id,
            'meter_number' => 'MTR-CBD-002',
            'area_id' => $createdAreas[2]->id,
            'location' => 'Samping Teras Rumah Budi, RT 04 RW 02',
            'installed_at' => '2024-06-15',
            'status' => 'active',
        ]);

        // 5. Buat Pembacaan Meteran & Tagihan Tahun 2025 (Arsip Historis)
        // Simulasi tagihan 2025 yang sudah lunas semua
        for ($m = 1; $m <= 12; $m++) {
            $period = sprintf('2025-%02d', $m);
            $usage = 18 + ($m % 5);
            $prev = 100 + (($m - 1) * 20);
            $curr = $prev + $usage;
            $rate = 5000;
            $adminFee = 2500;
            $total = ($usage * $rate) + $adminFee;

            $bill = WaterBill::create([
                'user_id' => $masyarakat->id,
                'water_meter_id' => $meterRina->id,
                'billing_period' => $period,
                'previous_reading' => $prev,
                'current_reading' => $curr,
                'usage_m3' => $usage,
                'rate_per_m3' => $rate,
                'admin_fee' => $adminFee,
                'total_amount' => $total,
                'due_date' => sprintf('2025-%02d-20', $m),
                'status' => 'paid',
                'year' => 2025,
                'created_by' => $petugas->id,
            ]);

            // Pembayaran lunas untuk 2025
            Payment::create([
                'water_bill_id' => $bill->id,
                'user_id' => $masyarakat->id,
                'amount' => $total,
                'payment_method' => ($m % 2 === 0) ? 'qris' : 'bank_transfer',
                'reference_number' => 'PAY-2025' . sprintf('%02d', $m) . '-00' . $m,
                'status' => 'verified',
                'verified_by' => $bendahara->id,
                'verified_at' => sprintf('2025-%02d-15 10:00:00', $m),
                'paid_at' => sprintf('2025-%02d-15 09:30:00', $m),
                'created_at' => sprintf('2025-%02d-15 09:30:00', $m),
            ]);
        }

        // 6. Buat Tagihan Tahun 2026 (Tahun Berjalan) untuk Rina dan Budi
        // Bulan 1 s.d. 8 (Januari s.d. Agustus 2026): Lunas & Tercatat di Riwayat Pembayaran
        // Bulan 9 (September 2026): Menunggu Pembayaran (Unpaid)
        $wargaList = [
            [
                'user' => $masyarakat,
                'meter' => $meterRina,
                'start_reading' => 340,
                'monthly_usages' => [22, 19, 21, 20, 22, 19, 23, 20, 24], // 1 s.d 9
            ],
            [
                'user' => $wargaDua,
                'meter' => $meterBudi,
                'start_reading' => 120,
                'monthly_usages' => [20, 22, 19, 21, 24, 23, 22, 21, 25], // 1 s.d 9
            ],
        ];

        $paymentMethods = ['qris', 'bank_transfer', 'qris', 'dana', 'gopay', 'bank_transfer', 'qris', 'dana'];

        foreach ($wargaList as $wargaData) {
            $userObj = $wargaData['user'];
            $meterObj = $wargaData['meter'];
            $currentVal = $wargaData['start_reading'];

            for ($m = 1; $m <= 9; $m++) {
                $period = sprintf('2026-%02d', $m);
                $usage = $wargaData['monthly_usages'][$m - 1];
                $prev = $currentVal;
                $currentVal = $prev + $usage;
                $rate = 5000;
                $adminFee = 2500;
                $total = ($usage * $rate) + $adminFee;
                $isPaid = ($m <= 8);

                $bill = WaterBill::create([
                    'user_id' => $userObj->id,
                    'water_meter_id' => $meterObj->id,
                    'billing_period' => $period,
                    'previous_reading' => $prev,
                    'current_reading' => $currentVal,
                    'usage_m3' => $usage,
                    'rate_per_m3' => $rate,
                    'admin_fee' => $adminFee,
                    'total_amount' => $total,
                    'due_date' => sprintf('2026-%02d-20', $m),
                    'status' => $isPaid ? 'paid' : 'unpaid',
                    'year' => 2026,
                    'created_by' => $petugas->id,
                ]);

                // Buat meter reading record agar tercatat pembacaan meteran fisik
                MeterReading::create([
                    'water_meter_id' => $meterObj->id,
                    'reading_value' => $currentVal,
                    'previous_value' => $prev,
                    'reading_date' => sprintf('2026-%02d-10', $m),
                    'recorded_by' => $petugas->id,
                    'notes' => 'Pencatatan meter rutin ' . $period,
                ]);

                // Jika bulan 1 s.d 8, buat record pembayaran lunas yang terverifikasi
                if ($isPaid) {
                    $method = $paymentMethods[$m - 1] ?? 'qris';
                    $payDay = sprintf('2026-%02d-14 10:30:00', $m);

                    Payment::create([
                        'water_bill_id' => $bill->id,
                        'user_id' => $userObj->id,
                        'amount' => $total,
                        'payment_method' => $method,
                        'reference_number' => 'PAY-2026' . sprintf('%02d', $m) . '-' . strtoupper(substr($userObj->customer_id, -3)) . $m . 'X',
                        'status' => 'verified',
                        'verified_by' => $bendahara->id,
                        'verified_at' => sprintf('2026-%02d-15 09:00:00', $m),
                        'paid_at' => $payDay,
                        'created_at' => $payDay,
                    ]);
                }
            }
        }

        // 7. Buat Pengaduan Gangguan Simulasi
        $complaint1 = Complaint::create([
            'user_id' => $masyarakat->id,
            'category_id' => $createdCategories[1]->id, // Debit Air Menurun
            'area_id' => $createdAreas[0]->id,
            'ticket_number' => 'SA-2026-001',
            'location' => 'Jalur Pipa Depan RT 02 Dusun I Krajan',
            'description' => 'Sejak dua hari lalu aliran air ke bak mandi sangat kecil saat jam 06.00-09.00 pagi. Mohon diperiksa apakah ada endapan pada pipa pembagi.',
            'status' => 'in_progress',
            'assigned_to' => $petugas->id,
            'occurred_at' => now()->subDays(2),
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint1->id,
            'user_id' => $petugas->id,
            'status' => 'in_progress',
            'notes' => 'Petugas telah memeriksa bak penampung Dusun I. Ditemukan penurunan tekanan akibat sumbatan filter pasir. Pembersihan sedang dilakukan hari ini.',
        ]);

        $complaint2 = Complaint::create([
            'user_id' => $masyarakat->id,
            'category_id' => $createdCategories[2]->id, // Kebocoran Pipa
            'area_id' => $createdAreas[0]->id,
            'ticket_number' => 'SA-2026-002',
            'location' => 'Sambungan pipa dekat gapura masuk RT 02',
            'description' => 'Ada rembesan air cukup deras di pinggir selokan dekat gapura.',
            'status' => 'resolved',
            'assigned_to' => $petugas->id,
            'resolved_at' => now()->subDay(),
            'resolution_notes' => 'Pipa sambungan PVC 1/2 inch yang pecah sudah diganti dengan fitting baru. Aliran kembali normal.',
            'occurred_at' => now()->subDays(4),
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint2->id,
            'user_id' => $petugas->id,
            'status' => 'resolved',
            'notes' => 'Perbaikan tuntas dilaksanakan pukul 14.30 WIB oleh Tim Teknis PAB.',
        ]);
    }
}
