# Integrasi PBI-08 dan PBI-09

## Status 18 November 2026

PBI-09 telah diverifikasi pada MySQL XAMPP melalui login, daftar/filter, perhitungan, validasi, simpan, rincian, koreksi, pembatalan, dan pencatatan ulang. Panduan koneksi dan pemeriksaan kolom database ada di [setup MySQL lokal](database-mysql-lokal.md). `.env` dan isi database lokal tidak disertakan dalam Git. Contoh SQLite terpisah di bawah tetap tersedia untuk pengujian alternatif.

`php vendor/bin/phpunit` lulus **92 tes dan 656 assertion** dengan database SQLite dalam memori. Pengujian browser MySQL menggunakan Playwright yang sudah tersedia; konkurensi transaksi MySQL belum diuji. Dependency Laravel Dusk sudah ditambahkan pada Composer sebagai persiapan, tetapi test suite Dusk belum dibuat/dijalankan dan PBI ini belum dinyatakan selesai untuk In Review.

Pembaruan 16 November 2026: rincian, koreksi meter akhir, dan pembatalan tersedia pada pencatatan yang sama. Lihat [aturan, migration, dan daftar file perubahan](PBI-09-koreksi-pembatalan.md). Fitur ini memerlukan migration tambahan sebelum aplikasi dijalankan pada database lama.

Alur yang dipakai adalah halaman bersama dari `main`: login Petugas → `/petugas/meters` → tombol **Catat** → input meter akhir → Total Pemakaian → simpan. Tidak ada menu perhitungan atau halaman preview baru.

## Pembagian tanggung jawab

- **PBI-08:** pelanggan/meter yang dipilih, tanggal, input `reading_value` (meter akhir), foto/catatan, dan penyimpanan pembacaan.
- **PBI-09:** mengambil meter awal dari pembacaan terakhir sebelum periode yang sedang dicatat, menghitung meter akhir dikurangi meter awal, menolak input tidak valid, dan menampilkan Total Pemakaian.
- **PBI-10:** pemeriksaan Bendahara belum ditambahkan dalam perubahan ini. Pembuatan tagihan otomatis yang sudah ada di `main` tetap berjalan. Alur persetujuan sebelum tagihan terbit perlu dibahas tersendiri.

PBI-08 tetap menyediakan input meter akhir. Input tersebut memicu tampilan PBI-09 pada halaman yang sama; petugas tidak perlu memasukkan meter akhir dua kali.

## Sambungan kode

`Petugas/MeterReadingController::create()` mengambil meter awal melalui `WaterMeter::readingBeforePeriod()`. `store()` mengambilnya lagi dari database, sehingga kiriman `previous_value`, `usage_m3`, atau `recorded_by` dari browser tidak dipakai.

Perhitungan server menggunakan satu service, `App\Services\HitungPemakaianMeter`:

```php
$awal = $hitung->angkaKeSatuan($previousReading?->reading_value ?? '0', 'reading_value');
$akhir = $hitung->angkaKeSatuan($request->input('reading_value'), 'reading_value');
$pemakaian = $hitung->hitung($awal, $akhir, 'reading_value');
```

Nilai internal berupa integer dalam satuan 0,01 m³: 317,00 → 31700. `satuanKeDesimal()` menghasilkan nilai desimal database, sedangkan `format()` menghasilkan teks Indonesia. Service yang sama dipakai `BillingService` untuk `water_bills.usage_m3`.

`meter_readings.previous_value` dan `reading_value` menyimpan angka meter, bukan hasil pengurangan. Tidak ditambahkan tabel pemakaian lain. Pelanggan baru tanpa pembacaan sebelumnya memakai meter awal nol.

`public/assets/meter.js` memperbarui `<x-total-pemakaian />` saat input berubah. Perhitungan JavaScript hanya untuk tampilan; server tetap menghitung ulang saat simpan. Input kosong/tidak valid menampilkan **— m³**. Input koma/titik diterima dengan maksimal dua angka desimal. Batas 8 digit bilangan bulat mengikuti kolom `DECIMAL(10,2)` milik tabel tim.

Penyimpanan menolak periode tertutup, meter nonaktif, dan pencatatan kedua untuk meter/periode yang sama. Pembacaan dan tagihan berada dalam satu transaksi. Jika penyimpanan gagal, foto yang baru diunggah dibersihkan. Penguncian baris meter dipakai ketika database mendukungnya; pengujian yang dijalankan memakai SQLite, belum menguji konkurensi MySQL.

Route lama `/catat-meter` dipertahankan untuk kompatibilitas data lama, kini memerlukan login Petugas. Gunakan `/petugas/meters` untuk integrasi bersama. Data tabel lama tidak otomatis dipindahkan ke tabel tim.

## Mencoba dengan database demo terpisah

Jalankan PowerShell dari root repository. Perintah ini membuat database baru di folder sementara; tidak mengganti `.env` atau `database/database.sqlite`. Jangan memakai `migrate:fresh` pada database tim.

```powershell
$demoFolder = Join-Path $env:TEMP ('satuair-pbi09-demo-' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path "$demoFolder/views" | Out-Null
New-Item -ItemType File -Path "$demoFolder/database.sqlite" | Out-Null
$env:APP_ENV = 'local'
$env:APP_CONFIG_CACHE = "$demoFolder/config.php"
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = "$demoFolder/database.sqlite"
$env:DB_URL = ''
$env:CACHE_STORE = 'array'
$env:SESSION_DRIVER = 'database'
$env:QUEUE_CONNECTION = 'sync'
$env:VIEW_COMPILED_PATH = "$demoFolder/views"
php artisan migrate --seed --seeder=Pbi09DemoSeeder
php artisan serve --host=127.0.0.1 --port=8001 --no-reload
```

Buka **http://127.0.0.1:8001/petugas/meters**. Pilih peran **Petugas** pada login, lalu gunakan `petugas@gmail.com` / `password123` (akun demo lokal).

Seeder demo membuat periode bulan berjalan dan dua pelanggan. Pilih Budi Santoso: meter awal **317,00**. Coba **342,00** → **25,00 m³**, **317,00** → **0,00 m³**, dan **316,00** → ditolak. Setelah disimpan, pelanggan tersebut tidak dapat dicatat lagi pada periode yang sama. Untuk mengulang seluruh percobaan, gunakan database demo baru dengan perintah di atas. Seeder menolak database utama maupun database yang sudah berisi pengguna/meter.

Environment di atas berlaku untuk terminal tersebut. Menutup terminal mengakhiri override; terminal lain tetap memakai konfigurasi `.env` proyek.

## Pengujian

```powershell
php vendor/bin/phpunit --do-not-cache-result
```

`phpunit.xml` memaksa SQLite `:memory:` agar tidak memakai database kerja. Tes integrasi memeriksa hasil 25/0/desimal, pelanggan baru, input tidak valid, manipulasi request, duplikat termasuk akhir bulan, periode tertutup, akses role, dan rollback ketika proses tagihan gagal.

Untuk pengujian browser tambahan, jalankan server demo baru, lalu jalankan `node tests/browser.cjs` di terminal kedua yang memiliki modul Playwright dan Microsoft Edge. `PLAYWRIGHT_MODULE` dapat diisi lokasi modul Playwright yang sudah tersedia, dan `METER_TEST_BASE_URL` mengatur URL server (default port 8001). Skrip menyimpan pembacaan Budi, mengoreksi, membatalkan, dan mencatat ulang, jadi gunakan database demo baru setiap kali mengulangnya. Skrip Playwright menggunakan modul yang tersedia di lingkungan pengujian; instalasi Laravel Dusk dikelola terpisah melalui Composer.

Tes browser ini memakai Playwright untuk verifikasi implementasi. Tugas pengujian **Laravel Dusk** dari praktikum masih perlu dikerjakan terpisah.
