# Database MySQL lokal SatuAir

Setup pada 18 November 2026 menggunakan layanan MySQL XAMPP (MariaDB 10.4.32), dengan koneksi Laravel `mysql`. Database lokal bernama `satuair` dibuat baru; database SQLite sebelumnya tidak dihapus atau diimpor.

## Menjalankan aplikasi setiap hari

1. Nyalakan **MySQL** pada XAMPP Control Panel.
2. Buka terminal pada root repository SatuAir-Project.
3. Jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

4. Buka <http://127.0.0.1:8000/login>, pilih tab **Petugas**, dan masuk menggunakan `petugas@gmail.com` / `password123`.
5. Buka <http://127.0.0.1:8000/petugas/meters>. Data demo Budi memiliki meter awal **317,00** dan belum dicatat pada periode November 2026 di database utama saat setup selesai.

Apache tidak diperlukan oleh `artisan serve`. Jika ingin memakai phpMyAdmin dari XAMPP, nyalakan Apache juga, lalu buka <http://localhost/phpmyadmin> dan pilih database `satuair`.

## Konfigurasi lokal

File `.env` lokal telah disesuaikan menjadi:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=satuair
DB_USERNAME=root
DB_PASSWORD=
```

`.env` tetap diabaikan Git. Salinan konfigurasi sebelum perubahan ada di `storage/app/private/mysql-setup-20261118/.env.before-mysql`, yang juga diabaikan Git. Cookie sesi lokal diganti menjadi `satuair_mysql_session` agar login tidak memakai sesi demo SQLite lama.

## Setup yang sudah dijalankan

Database `satuair` dipastikan belum ada sebelum dibuat. Pembuatan database dilakukan dengan klien MySQL XAMPP:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' --host=127.0.0.1 --port=3306 --user=root -e 'CREATE DATABASE satuair CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
```

Kemudian migration dan seeder yang sudah ada dijalankan pada database baru tersebut:

```powershell
php artisan migrate --seed --force
php artisan config:clear
```

Hasil: **21 migration berhasil**, **27 tabel**, dan **4 akun demo aktif**. DatabaseSeeder juga menghasilkan contoh tagihan, pembayaran, serta pengaduan. Data tersebut merupakan data simulasi, bukan transaksi pengguna. Jangan mengulangi seeder utama pada database yang sudah berisi data karena seeder tersebut membuat baris baru, bukan memperbarui seluruh data secara idempoten.

Periode September 2026 bawaan migration ditutup dan periode November 2026 diaktifkan khusus pada database demo baru. Pengaturan periode tersedia melalui alur Bendahara di aplikasi; saat berganti bulan, sesuaikan periode aktif sebelum pencatatan baru.

## Tabel yang dipakai PBI-09

| Tabel | Kolom / kegunaan |
|---|---|
| `users` | Akun pelanggan dan Petugas, termasuk `role` dan `status`. |
| `water_meters` | `user_id` menghubungkan meter dengan pelanggan. |
| `meter_readings` | `water_meter_id`, `previous_value`, `reading_value`, `reading_date`, `recorded_by`, `photo`, `notes`, `deleted_at`. |
| `water_bills` | `previous_reading`, `current_reading`, `usage_m3`, `billing_period`, dan nilai tagihan. |
| `meter_reading_changes` | Riwayat edit atau pembatalan, alasan, dan data sebelum/sesudah. |
| `billing_periods` | Menentukan periode yang boleh dicatat. |
| `roles`, `permissions`, `permission_role` | Hak akses dari PBI-02. |

Total pemakaian saat ini tersimpan di `water_bills.usage_m3`, bukan kolom bernama `jumlah_pemakaian`. Input meter akhir tersimpan di `meter_readings.reading_value`; meter awal yang digunakan disimpan di `previous_value`. Pembatalan menggunakan soft delete: `deleted_at` terisi dan data tidak lagi dihitung sebagai pencatatan aktif, tetapi riwayat tetap ada.

Untuk memeriksa hasil input melalui phpMyAdmin, pilih database `satuair`, lalu jalankan query ini pada tab SQL:

```sql
SELECT id, water_meter_id, previous_value, reading_value,
       reading_date, recorded_by, deleted_at
FROM meter_readings
ORDER BY id DESC;

SELECT id, water_meter_id, previous_reading, current_reading,
       usage_m3, billing_period, deleted_at
FROM water_bills
ORDER BY id DESC;
```

## Verifikasi setup

Pengujian simpan dilakukan pada database baru terpisah `satuair_pbi09_test_20261118`, menggunakan migration dan DatabaseSeeder yang sama. Server sementara memakai port 8002 dan dihentikan setelah pengujian. Database utama `satuair` tidak dipakai untuk transaksi uji.

Perintah browser yang dijalankan dari root repository:

```powershell
$env:PLAYWRIGHT_MODULE='C:/Users/Fadhil/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'
$env:METER_TEST_BASE_URL='http://127.0.0.1:8002'
node tests/browser.cjs
```

Hasil lulus: login, daftar/filter, hitung 25/0/25,25 m³, validasi, simpan dan penolakan pencatatan ganda, edit dan hitung ulang, riwayat, penolakan perubahan dari tab lama, pembatalan, serta pencatatan ulang. Query langsung pada MySQL menunjukkan hasil akhir meter awal 317,00, meter akhir 342,00, dan pemakaian 25,00 pada pencatatan aktif. Catatan uji yang dibatalkan tetap memiliki `deleted_at`.

Tes browser ini menggunakan **Playwright**, belum Laravel Dusk. Setup database ini tidak menyelesaikan tugas pengujian Dusk maupun implementasi PBI-10.
