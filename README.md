# SatuAir

Aplikasi layanan air BUMDes berbasis Laravel. README ini menjadi panduan anggota
kelompok untuk menyiapkan dan menjalankan automatic testing Laravel Dusk pada Windows.

Paket pengujian berisi **51 test case untuk 7 PBI**, mengikuti deskripsi Jira tim.
Nomor fitur PBI dan key Jira berbeda; opsi `-Group` selalu menggunakan **key Jira**.

## 1. Prasyarat

- Windows dengan PowerShell, Git, dan Composer.
- PHP CLI **8.2 atau lebih baru** dengan ekstensi yang dibutuhkan Composer, termasuk
  `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `zip`, `dom`, dan `xml`.
- Google Chrome terpasang.
- MySQL pada XAMPP aktif di `127.0.0.1:3306`. Apache tidak diperlukan oleh runner Dusk.
- Akses internet untuk instalasi dependency/ChromeDriver serta aset Tailwind,
  Alpine.js, dan font yang masih menggunakan CDN pada aplikasi.

Periksa terminal yang dipakai:

```powershell
php -v
composer --version
git --version
where.exe php
```

Pengujian pada laporan telah dijalankan dengan PHP 8.4.27, Laravel 12, Dusk 8.8,
dan MariaDB XAMPP 10.4.32. Hasil pada laptop masing-masing tetap perlu diverifikasi.

## 2. Ambil kode dan pasang dependency

Jika belum memiliki repository:

```powershell
git clone https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project.git
cd SatuAir-Project
```

Jika sudah memiliki repository, simpan perubahan lokal terlebih dahulu sebelum
berpindah branch. Dari folder repository, ambil versi `main` terbaru:

```powershell
git fetch origin
git switch main
git pull --ff-only origin main
```

Lalu jalankan dari **root repository**, yaitu folder yang berisi file `artisan`:

```powershell
composer install

# Hanya membuat konfigurasi bila .env belum tersedia.
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}

php artisan dusk:chrome-driver --detect
```

Gunakan `composer install` beserta dependency development; Dusk berada pada
`require-dev`. Tidak perlu menyalin `vendor`, `node_modules`, `.env`, atau file
database dari laptop anggota lain. Halaman yang diuji saat ini memakai aset CDN,
sehingga setup Dusk ini tidak membutuhkan `npm run dev`.

## 3. Database percobaan

Nyalakan **MySQL** di XAMPP Control Panel. Runner memakai konfigurasi berikut:

| Pengaturan | Nilai bawaan |
|---|---|
| Koneksi | MySQL / MariaDB |
| Host | `127.0.0.1` |
| Port MySQL | `3306` |
| Database pengujian | **`satuair_dusk_jira`** |
| Username | `root` |
| Password | Kosong |
| Server aplikasi pengujian | `http://127.0.0.1:8002` |

Runner akan **membuat database uji bila belum ada**, menjalankan migration di
database tersebut, dan menyiapkan data contoh untuk setiap test case. Tidak perlu
mengimpor SQL atau menjalankan seeder secara manual untuk Dusk.

**Isi `satuair_dusk_jira` direset sebelum setiap test case. Jangan menyimpan data
kerja di database ini.** Database aplikasi biasa, misalnya `satuair`, tetap terpisah.
Runner tidak mengganti file `.env` dan tidak menjalankan `migrate:fresh` pada
database kerja. Pengaturan koneksi tes berlaku hanya pada proses pengujian.

Jika akun MySQL lokal memakai username/password berbeda, atur pada terminal yang
sama sebelum menjalankan tes:

```powershell
$env:DUSK_DB_USERNAME = "username_mysql_anda"
$env:DUSK_DB_PASSWORD = "password_mysql_anda"
```

Akun tersebut harus memiliki izin membuat database uji dan menjalankan migration
serta reset tabel di dalamnya. Host dan port mengikuti tabel di atas.

Data contoh otomatis meliputi Bendahara, Petugas, dua pelanggan, permission,
periode aktif bulan berjalan, dan pencatatan sesuai kebutuhan kasus. Data ini
dibuat di database uji; akun pada database kerja tidak digunakan untuk login tes.

## 4. Menjalankan seluruh automatic testing

Jalankan satu runner pada satu waktu, dari root repository:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1
```

Chrome akan terbuka dan digerakkan otomatis dengan jeda **650 ms antar aksi**.
Runner menyalakan server uji pada port 8002 dan menutupnya ketika selesai.
Tidak perlu menjalankan `php artisan serve` untuk tes ini. Aplikasi kerja pada
port 8000 boleh tetap berjalan.

Untuk demo yang lebih lambat, termasuk pengisian per karakter:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Delay 1000 -TypeDelay 60
```

Untuk pengulangan cepat tanpa jendela browser:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Headless -Delay 0 -TypeDelay 0
```

Gunakan runner ini, bukan langsung `php artisan dusk`, agar server, koneksi,
dan pelindung reset database disiapkan bersama. Perintah `artisan dusk` tetap
dijalankan oleh runner menggunakan konfigurasi `phpunit.dusk.xml`.

## 5. Command automatic testing setiap PBI

| Anggota | Nomor fitur | Key Jira / grup Dusk | Jumlah tes |
|---|---|---|---:|
| Saptya | PBI-01 Registrasi, login, logout Masyarakat | `PBI-7` | 7 |
| Saptya | PBI-02 Pengaturan hak akses | `PBI-8` | 8 |
| Niswah | PBI-11 Riwayat input meter | `PBI-3` | 2 |
| Zahwah | PBI-05 Filter pengguna berdasarkan peran | `PBI-9` | 8 |
| Zahwah | PBI-06 Melihat dan mengubah detail pelanggan | `PBI-10` | 8 |
| Fadhil | PBI-09 Menghitung selisih angka meter | `PBI-1` | 9 |
| Fadhil | PBI-10 Pemeriksaan pemakaian sebelum tagihan | `PBI-2` | 9 |
| **Total** | | | **51** |

Pilih dan jalankan command sesuai PBI yang ingin diuji:

```powershell
# Saptya: PBI-01, key Jira PBI-7
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-7

# Saptya: PBI-02, key Jira PBI-8
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-8

# Niswah: PBI-11, key Jira PBI-3
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-3

# Zahwah: PBI-05, key Jira PBI-9
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-9

# Zahwah: PBI-06, key Jira PBI-10
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-10

# Fadhil: PBI-09, key Jira PBI-1
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-1

# Fadhil: PBI-10, key Jira PBI-2
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-2
```

Beberapa PBI dapat digabung dalam satu run. Contoh seluruh tes Fadhil:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group "PBI-1,PBI-2"
```

Untuk menjalankan satu kasus dan melihat data akhirnya di phpMyAdmin:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-1 -Filter test_tc02_meter_342
```

Sesudah kasus ini selesai, pilih database `satuair_dusk_jira` pada phpMyAdmin.
Tabel `meter_readings` memuat meter awal `317.00` dan meter akhir `342.00` untuk
pencatatan baru. Hasil pemakaian adalah `25.00`. Run berikutnya akan mereset fixture.

## 6. Membaca hasil dan bukti Jira

Tes berhasil jika terminal menampilkan semua kasus lulus, tanpa failed/error/skipped,
dan ringkasan seperti `OK (51 tests, 392 assertions)` untuk paket lengkap yang
tercatat pada laporan. Jumlah assertion dapat berubah jika tes diperbarui.
Pada kasus negatif, penolakan input/akses yang benar berarti **tes lulus**.

| Bukti | Lokasi |
|---|---|
| Kode tes per PBI | `tests/Browser/*Test.php` |
| Screenshot per kasus | `tests/Browser/screenshots/PBI-<key>-TC<nomor>.png` |
| Screenshot ketika gagal | `tests/Browser/screenshots/failure-*.png` |
| Log terminal | `tests/Browser/artifacts/run-<waktu>.txt` |
| Laporan JUnit | `tests/Browser/artifacts/junit-<waktu>.xml` |
| Log server uji | `tests/Browser/artifacts/server-<waktu>.*.log` |

Screenshot dan log dibuat pada laptop yang menjalankan tes dan diabaikan Git.
Untuk bukti Jira, unggah hasil run yang lulus sesuai key dan TC masing-masing.
Folder cache, session, compiled views, database lokal, dan dependency tidak ikut
dibagikan melalui Git. Placeholder `.gitignore` tetap menjaga struktur folder.

Tes server Unit/Feature adalah suite terpisah dari browser Dusk:

```powershell
php vendor/bin/phpunit --do-not-cache-result
```

## 7. Kendala umum

| Kendala | Penanganan |
|---|---|
| `php` / Composer tidak ditemukan atau versi PHP tidak cocok | Periksa `where.exe php`, PATH, versi PHP, dan ekstensi pada PHP CLI yang aktif. |
| `could not find driver` | Aktifkan `pdo_mysql` pada PHP CLI yang dipakai terminal. |
| MySQL menolak koneksi | Nyalakan MySQL XAMPP di port 3306; periksa kredensial `DUSK_DB_USERNAME` / `DUSK_DB_PASSWORD`. |
| ChromeDriver tidak cocok dengan Chrome | Jalankan kembali `php artisan dusk:chrome-driver --detect` setelah Chrome diperbarui. |
| Port 8002 sudah dipakai | Tunggu runner lain selesai, atau gunakan `-Port 8003`. Jangan menyalakan dua runner bersamaan. |
| File `run.lock` sedang dipakai | Ada run Dusk yang belum selesai. Tutup melalui terminal run tersebut sebelum memulai run baru. |
| Login/tab role atau styling tidak dimuat | Periksa internet dan akses CDN Alpine.js/Tailwind. |
| Ada `.env.dusk` / `.env.dusk.dusk` | Runner ini memakai konfigurasi proses. Cadangkan dan pindahkan file tersebut dari root sebelum memakai runner, agar `.env` kerja tidak ditukar. |
| Pesan guard database | Jalankan melalui `scripts/dusk.ps1`; nama database tes harus tepat `satuair_dusk_jira`. |
| Tes gagal | Baca log terminal, screenshot `failure-*`, dan log server. Jangan mengganti hasil yang diharapkan hanya agar lulus. |

## Dokumentasi lanjutan

- [Rincian tes dan pemetaan Jira](docs/laravel-dusk-tim.md).
- [Hasil pengujian yang sudah dijalankan](docs/hasil-dusk-tim.md).
- [Setup MySQL aplikasi kerja](docs/database-mysql-lokal.md), terpisah dari database Dusk.

Laravel adalah framework open source berlisensi [MIT](https://opensource.org/licenses/MIT).
