# Pemulihan integrasi PBI-02 dan PBI-09

Pemeriksaan dan perbaikan 17 November 2026. Sumber pemulihan: main GitHub pada commit `4279285`.

## Masalah

Working tree PBI-09 memuat penggabungan main lama (`5669029`) yang belum dicommit. Bagian hak akses pada main terbaru belum tercakup: middleware, model, migration, seeder, pengelolaan hak akses, dan tesnya tidak tersedia. Jika seluruh snapshot lokal dijadikan hasil akhir tanpa peninjauan, modul PBI-02 dapat hilang walaupun Git dapat menggabungkan teks tanpa konflik.

## Perbaikan

File berikut dipulihkan persis dari main:

- `app/Http/Controllers/Bendahara/AccessControlController.php`
- `app/Http/Middleware/EnsureRole.php`
- `app/Http/Middleware/EnsurePermission.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
- `database/migrations/2026_11_07_000001_create_roles_and_permissions_tables.php`
- `database/seeders/AccessControlSeeder.php`
- `resources/views/bendahara/access/index.blade.php`
- `tests/Feature/AccessControlTest.php`
- `resources/views/components/nav-bendahara.blade.php`
- `resources/views/components/nav-bendahara-mobile.blade.php`

Bagian berikut digabung tanpa menghapus fitur PBI-09:

| File | Penyesuaian |
| --- | --- |
| `bootstrap/app.php` | Mengembalikan alias middleware role dan permission. |
| `app/Models/User.php` | Mengembalikan `roleModel()` dan `hasPermission()`. |
| `routes/web.php` | Mengembalikan perlindungan role/permission dan route pengaturan hak akses dari main. Route rincian/edit/pembatalan PBI-09 berada dalam grup Petugas dengan `field.manage`. Dua route verifikasi pembayaran Petugas yang sudah dihapus pada main dan tidak memiliki method controller tidak dibawa kembali. |
| `routes/meter.php` | Route kompatibilitas lama menggunakan role Petugas dan permission `field.manage`, sehingga tidak menjadi jalan pintas ketika hak dicabut. |
| `database/seeders/DatabaseSeeder.php` | Mengembalikan pemanggilan AccessControlSeeder seperti pada main. |
| `database/seeders/Pbi09DemoSeeder.php` | Mengisi role/permission ketika membuat database demo baru. |
| `tests/Feature/PencatatanMeterTest.php` | Menyiapkan hak akses default pada fixture pencatatan lama. |
| `tests/Feature/PemakaianMeterIntegrationTest.php` | Menyiapkan hak akses default serta menambah pengujian pencabutan permission dan pembatasan role. |
| `tests/Feature/MeterReadingCorrectionTest.php` | Menyiapkan hak akses default pada fixture koreksi/pembatalan. |

Tidak menghapus perhitungan, komponen Total Pemakaian, koreksi, pembatalan, audit, atau migration PBI-09. Source branch Yuka tidak di-merge. Bagian lain seperti README dan pencatatan legacy belum dirapikan untuk scope PR dalam pekerjaan ini.

## Pengujian yang dijalankan

```powershell
php vendor/bin/phpunit --do-not-cache-result
php -l routes/web.php
php -l routes/meter.php
git diff --check
```

Hasil akhir: **92 tes, 656 assertion, lulus**. Termasuk enam tes AccessControlTest asli dari main dan pengujian berikut:

- Akun Bendahara yang mempunyai permission dapat membuka pengaturan dan memperbarui hak akses.
- Role yang tidak sesuai ditolak, termasuk Petugas yang diberi permission `access.manage` secara sengaja pada fixture.
- Permission `field.manage` yang dicabut memblokir daftar, form, simpan, rincian, edit, pembatalan, serta daftar kompatibilitas lama.
- Operasi yang diblokir tidak menambah/mengubah pembacaan, tagihan, atau audit.
- Ketika permission diberikan kembali, form Petugas dapat dibuka lagi.
- Seluruh tes perhitungan, penyimpanan, validasi, koreksi, dan pembatalan yang tersedia tetap lulus.

Pengujian menggunakan SQLite dalam memori sesuai phpunit.xml. Tidak menjalankan migration/seeder pada database kerja atau database demo lama dalam tugas ini. Laravel Dusk sudah menjadi dependency dari pekerjaan sebelumnya, tetapi tes Dusk belum dibuat/dijalankan; hasil di atas adalah PHPUnit, bukan Dusk.

## Database dan Git

Untuk menjalankan aplikasi pada database yang sudah dikonfigurasi, tabel role/permission perlu dibentuk dan hak default diisi:

```powershell
php artisan migrate
php artisan db:seed --class=AccessControlSeeder
```

Perintah tersebut dicantumkan sebagai langkah setup, belum dijalankan pada database kerja. Gunakan cadangan dan database yang tepat; tidak memakai migrate:fresh pada data tim. Seeder hak akses menambahkan permission default, sehingga dipakai pada setup awal, bukan dijalankan ulang sembarang setelah admin sengaja mencabut hak.

File `.env`, database kerja, index Git, HEAD, dan MERGE_HEAD dipertahankan. Tidak melakukan stage, commit, push, checkout, atau menyelesaikan merge pending. Perubahan source masih perlu dipilih/dimasukkan ke commit saat tahap persiapan PR berikutnya.

Simulasi snapshot source setelah pemulihan terhadap main `4279285` menghasilkan **nol konflik teks**. Perbandingan juga memastikan tidak ada lagi file PBI-02 yang terhapus. Penghapusan README yang sudah ada sebelumnya tetap dipertahankan sebagai perubahan lokal yang perlu ditinjau pada tahap pemilihan scope PR. Simulasi bukan merge sesungguhnya dan bukan pengganti peninjauan PR.
