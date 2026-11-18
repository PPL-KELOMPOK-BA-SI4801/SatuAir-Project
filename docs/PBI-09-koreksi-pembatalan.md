# PBI-09: koreksi dan pembatalan pencatatan

Pembaruan 16 November 2026 pada branch `PBI-09-menghitung-pemakaian-dari-selisih-angka-meter-PBI-1`.

## Sambungan dengan PBI-08 Yuka

- Daftar `/petugas/meters`, filter, tombol Catat, form `/petugas/meters/{id}/catat`, upload foto/catatan, dan endpoint `POST /petugas/meters` tetap digunakan. Tidak ada input meter akhir kedua atau menu baru.
- Tombol **Rincian** pada daftar membuka catatan yang sudah disimpan. Peringatan pencatatan ganda juga menautkan rincian tersebut.
- `MeterReadingCorrectionController` menangani rincian, edit, dan pembatalan secara terpisah agar controller tambah PBI-08 hanya menerima perubahan kecil.
- `KoreksiPencatatanMeter` mengatur transaksi dan aturan koreksi, bukan service rumus kedua. Rumus tetap menggunakan `HitungPemakaianMeter`, baik untuk koreksi maupun tagihan.
- Source Yuka yang menjadi acuan adalah remote `origin/PBI-07-Melihat-daftar-dan-jadwal-pelanggan` commit `d1f5d40`. Branch tersebut tidak di-merge ulang karena working tree masih mempunyai merge main yang belum diselesaikan. Perubahan statistik/jadwal dari branch Yuka tetap perlu diselaraskan saat review integrasi; file ini tidak mengklaim semua perubahan remote Yuka sudah digabungkan.

## Perilaku

1. **Edit:** ubah meter akhir, tampilkan total secara langsung, isi alasan maksimal 300 karakter, lalu simpan. Server menghitung ulang dari meter awal tersimpan; tanggal, foto, catatan, pelanggan, dan petugas pencatat asli dipertahankan. Identitas pengoreksi dicatat terpisah.
2. **Sinkronisasi tagihan:** tagihan periode yang sama diperbarui dalam transaksi yang sama. Tarif dan biaya admin yang sudah diatur dipertahankan. Perhitungan uang memakai bilangan bulat sen, dibulatkan sekali ke dua desimal dan dibatasi kapasitas kolom.
3. **Pembatalan:** wajib konfirmasi dan alasan. Pembacaan serta tagihan di-soft-delete, bukan dihapus permanen. Foto tetap tersedia. Pembacaan terakhir/meter awal kembali ke catatan sebelumnya. Pencatatan ulang periode tersebut diperbolehkan tanpa mengaktifkan kembali catatan lama.
4. **Riwayat:** alasan, pelaku, waktu, dan snapshot sebelum/sesudah disimpan di `meter_reading_changes`. Catatan lama yang dibatalkan tetap dapat dibuka dari bagian Pencatatan lain setelah pencatatan ulang.
5. **Dua tab:** token revisi memeriksa nilai pembacaan, tagihan, dan ID perubahan terakhir. Permintaan dari halaman lama ditolak dan meminta pengguna memuat ulang; nilai terbaru tidak ditimpa.

Edit dan pembatalan ditolak oleh server ketika catatan sudah dibatalkan, `verified_at` sudah terisi, periode tidak aktif, meter nonaktif, ada catatan/tagihan lanjutan atau catatan lain pada bulan yang sama, tagihan ganda, status tagihan bukan `unpaid`, atau sudah ada pembayaran dengan status apa pun. Pembayaran yang pernah ditolak/dibatalkan tetap mengunci agar jejak transaksi keuangan tidak diubah diam-diam.

`verified_at` hanya merupakan penanda untuk integrasi PBI-10 kelak. Pembaruan ini **belum membuat alur pemeriksaan Bendahara**. Perilaku membuat tagihan langsung setelah pencatatan dari fondasi tim masih berlaku.

Pembuatan pembayaran masyarakat diberi transaksi dan penguncian baris tagihan yang sama agar tidak berjalan bersamaan dengan koreksi/pembatalan. Pengujian memakai SQLite; konkurensi pada MySQL belum diuji. Tidak mengubah `.env` maupun pilihan database tim.

## Migration

Migration baru `2026_11_16_000001_add_meter_reading_corrections.php` menambahkan:

- `meter_readings.deleted_at` dan `verified_at` yang nullable;
- `water_bills.deleted_at` yang nullable;
- tabel audit `meter_reading_changes`.

Tidak mengubah migration lama, tidak menduplikasi tabel pelanggan/pencatatan, dan tidak mengubah enum status tagihan. Migration menggunakan Schema Builder sehingga dapat diterapkan pada SQLite maupun MySQL; eksekusi yang diverifikasi saat ini hanya SQLite.

Untuk database kerja yang sudah berisi data, buat cadangan terlebih dahulu lalu jalankan `php artisan migrate`. Jangan menjalankan `migrate:fresh`. Pada pengerjaan ini database kerja `database/database.sqlite` tidak diubah; migration dijalankan pada SQLite demo terpisah dan database tes dalam memori.

## Menjalankan demo

Dari root repository di PowerShell, gunakan blok database demo pada `docs/PBI-09.md`:

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
php artisan serve --host=127.0.0.1 --port=8001 --no-reload --tries=1
```

Buka `http://127.0.0.1:8001/petugas/meters`. Pilih role **Petugas**, akun demo `petugas@gmail.com` / `password123`. Simpan Budi 342,00 dari awal 317,00, buka Rincian, edit misalnya 347,00 dan isi alasan, kemudian coba pembatalan. Keduanya mengubah data demo, bukan database kerja. Gunakan database baru untuk menjalankan ulang tes browser otomatis.

Route tambahan: `GET /petugas/readings/{id}`, `GET /petugas/readings/{id}/edit`, `PATCH /petugas/readings/{id}`, dan `POST /petugas/readings/{id}/cancel`. Semuanya memerlukan login dan role Petugas. Tidak ada route preview.

## File yang ditambah/diubah dalam pembaruan ini

| Kelompok | File |
| --- | --- |
| Controller baru | `app/Http/Controllers/Petugas/MeterReadingCorrectionController.php` |
| Aturan koreksi | `app/Services/KoreksiPencatatanMeter.php` |
| Audit dan pembatalan | `app/Models/MeterReadingChange.php`, `app/Models/MeterReading.php`, `app/Models/WaterBill.php`, `app/Models/WaterMeter.php` |
| Migration tambahan | `database/migrations/2026_11_16_000001_add_meter_reading_corrections.php` |
| Tampilan rincian/edit/dialog | `resources/views/petugas/meters/reading.blade.php`, `public/assets/meter-corrections.js` |
| Penghubung PBI-08 | `app/Http/Controllers/Petugas/MeterReadingController.php`, `resources/views/petugas/meters/index.blade.php`, `resources/views/petugas/meters/create.blade.php` |
| Komponen bersama | `resources/views/components/total-pemakaian.blade.php` |
| Sinkronisasi tagihan/pembayaran | `app/Services/BillingService.php`, `app/Http/Controllers/Masyarakat/PaymentController.php` |
| Route | `routes/web.php` |
| Pengujian | `tests/Feature/MeterReadingCorrectionTest.php`, `tests/browser.cjs` |
| Dokumentasi | `docs/PBI-09.md`, `docs/PBI-09-koreksi-pembatalan.md` |

Perubahan merge main dan file lokal yang sudah ada sebelum pembaruan ini tidak dianggap sebagai file baru hasil pengerjaan koreksi.

## Verifikasi

```powershell
php vendor/bin/phpunit --do-not-cache-result
$env:PLAYWRIGHT_MODULE = '<lokasi modul playwright yang tersedia>'
$env:METER_TEST_BASE_URL = 'http://127.0.0.1:8001'
node tests/browser.cjs
git diff --check
```

Tes server mencakup 25/0/desimal, input tidak valid, alasan, manipulasi request, audit, tarif khusus, revisi halaman lama, pembatalan/pencatatan ulang, pembacaan sebelumnya, akses role, seluruh kondisi terkunci, serta rollback saat tagihan/audit gagal. Tes browser menguji alur tambah PBI-08 sampai edit, dua tab, dialog pembatalan, dan pencatatan ulang pada desktop/ponsel. Hasil eksekusi terakhir dilaporkan pada respons pengerjaan. Laravel Dusk tetap tugas pengujian terpisah; tes browser ini memakai Playwright yang sudah tersedia, tanpa dependency baru.

Tidak melakukan stage, commit, push, merge baru, atau perubahan database kerja dalam tahap ini.

## Hasil verifikasi 16 November 2026

- `php vendor/bin/phpunit --do-not-cache-result`: **84 tes, 627 assertion, lulus**.
- `php artisan migrate --seed --seeder=Pbi09DemoSeeder --force` pada SQLite demo baru: seluruh migration termasuk migration koreksi berhasil.
- `php artisan route:list --path=petugas`: empat route koreksi terdaftar bersama route PBI-08.
- `node tests/browser.cjs` menggunakan Playwright yang sudah tersedia: lulus alur login, filter, input/perhitungan, simpan/duplikat, edit, dua tab, pembatalan, dan pencatatan ulang. Tidak ada error JavaScript.
- Pembacaan langsung SQLite demo setelah tes: catatan lama 317 → 347 dan tagihan 30 m³/Rp152.500 mempunyai `deleted_at`; dua audit `corrected` dan `cancelled` tersimpan. Catatan ulang aktif 317 → 342 mempunyai tagihan 25 m³/Rp127.500.
- `git diff --check`: bersih.
- SHA-256 database kerja sebelum dan setelah pengujian tetap `D1DFF14B2AC9F93A5EC5A07DBE3C4104B708C8A49BDCECE5DF59782379B4E4C6`.
- Merge main yang sudah berlangsung sebelum tugas tetap belum diselesaikan. Isi index tidak diubah oleh tugas ini.
