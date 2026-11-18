# Penyelesaian konflik PBI-09 dengan main

Pemeriksaan dan integrasi pada 18 November 2026.

- Branch kerja: `PBI-09-menghitung-pemakaian-dari-selisih-angka-meter-PBI-1`.
- Commit PBI-09 sebelum integrasi: `506a661`.
- Main yang diselaraskan: `4279285`.
- Arah integrasi: **main ke branch PBI-09**, bukan PBI-09 ke main. Tidak membuat pull request dalam pengerjaan ini.

## Penyebab dan keputusan

Kode fondasi tim sebelumnya masuk sebagai penambahan file pada commit PBI-09. File yang sama juga telah ditambahkan dalam riwayat main, sehingga Git melaporkan sembilan konflik add/add dan satu konflik isi route. Kedua versi dibandingkan sebelum konflik diselesaikan.

| File | Hasil penyelesaian |
|---|---|
| `app/Http/Controllers/Petugas/MeterReadingController.php` | Pertahankan form/pencatatan bersama, service perhitungan, validasi periode/duplikat, dan transaksi PBI-09. |
| `app/Http/Controllers/Masyarakat/PaymentController.php` | Pertahankan alur pembayaran main beserta transaksi dan penguncian tagihan untuk mencegah perubahan nominal saat koreksi. |
| `app/Models/MeterReading.php` | Pertahankan relasi dasar, soft delete, penanda verifikasi, dan relasi audit. |
| `app/Models/WaterBill.php` | Pertahankan model tim dengan soft delete yang dipakai pembatalan. |
| `app/Models/WaterMeter.php` | Pertahankan relasi tim, pembacaan sebelum periode, dan akses ke riwayat yang dibatalkan. |
| `app/Services/BillingService.php` | Pertahankan alur tagihan tim dan gunakan service perhitungan yang sama, termasuk hitung ulang dan perlindungan tagihan yang sudah diproses. |
| `resources/views/petugas/meters/create.blade.php` | Pertahankan input pencatatan bersama dan kartu Total Pemakaian, validasi, serta pembatasan pencatatan ganda. |
| `resources/views/petugas/meters/index.blade.php` | Pertahankan daftar/filter tim beserta tautan Rincian dan penanda pembatalan. |
| `routes/web.php` | Pertahankan seluruh grup role/permission main, serta route rincian/edit/pembatalan PBI-09 di grup Petugas. |
| `tests/Feature/SatuAirFunctionalTest.php` | Gunakan versi main karena perbedaan hanya urutan import dan baris kosong. |

Hasil penggabungan otomatis pada `composer.json` mempertahankan langkah setup `AccessControlSeeder` dari main dan dependency Dusk dari branch PBI-09. README dari main disertakan dalam hasil integrasi; kondisi lokal README yang sudah dihapus sebelumnya dipertahankan sebagai perubahan lokal di luar commit.

Kode login, middleware role/permission, model User/Role/Permission, migration dan seeder hak akses, halaman pengaturan hak akses, dan pengujian hak akses diperiksa cocok dengan main. Tidak menambahkan skema database baru untuk penyelesaian konflik ini.

## Verifikasi

- `php vendor/bin/phpunit --do-not-cache-result`: **92 tes, 656 assertion, lulus**, memakai SQLite dalam memori.
- `composer validate --no-check-publish --no-check-all`: valid.
- Migration dan DatabaseSeeder pada MySQL uji baru `satuair_pbi09_merge_test_20261118_0117`: seluruh **21 migration berhasil**.
- `node tests/browser.cjs` pada server sementara port 8002: login, daftar/filter, hitung 25/0/25,25, validasi, simpan/duplikat, rincian, edit, riwayat, konflik dua tab, pembatalan, dan pencatatan ulang.
- Data kerja pada database `satuair`, `.env`, dan file SQLite lama tidak diubah oleh integrasi/pengujian ini. Tes browser memakai database uji tersendiri.

Tes browser menggunakan Playwright. Test suite Laravel Dusk dan pengerjaan PBI-10 tetap belum diselesaikan oleh perubahan ini.
