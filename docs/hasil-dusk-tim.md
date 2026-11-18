# Hasil Laravel Dusk tim SatuAir

Tanggal pelaksanaan: 18 November 2026. Acuan: tujuh deskripsi Jira yang diberikan pengguna.
Branch pengujian: `dusk-jira-tim`. Basis main saat diuji: `7dc48910f427bec567481fc31df586b84259a452`.

**Hasil akhir: 51 test case lulus, 392 assertion, 0 gagal/error, 0 dilewati.**

| Pemilik | Key Jira | Fitur | Tes lulus | Assertion |
|---|---|---|---:|---:|
| Fadhil | PBI-1 | PBI-09 Perhitungan meter | 9 | 86 |
| Fadhil | PBI-2 | PBI-10 Pemeriksaan pemakaian | 9 | 112 |
| Niswah | PBI-3 | PBI-11 Riwayat meter | 2 | 16 |
| Saptya | PBI-7 | PBI-01 Registrasi dan login | 7 | 40 |
| Saptya | PBI-8 | PBI-02 Hak akses | 8 | 36 |
| Zahwah | PBI-9 | PBI-05 Filter pengguna | 8 | 43 |
| Zahwah | PBI-10 | PBI-06 Detail pelanggan | 8 | 59 |

## Perintah yang benar-benar dijalankan

Validasi lengkap terakhir (headless, jeda 0 untuk pengulangan cepat):

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Headless -Delay 0 -TypeDelay 0
```

Hasil: `OK (51 tests, 392 assertions)`, durasi PHPUnit `13:30.972`.

- Laporan JUnit: `tests/Browser/artifacts/junit-20261118-162141.xml`.
- Log terminal: `tests/Browser/artifacts/run-20261118-162141.txt`.

Verifikasi browser terlihat, jeda 650 ms, setelah perbaikan helper:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Filter 'test_tc08_data_dikoreksi|test_tc02_meter_342' -Delay 650
```

Hasil: `OK (2 tests, 27 assertions)`. Mencakup input 342 - 317, penyimpanan/rincian, serta dua akun untuk koreksi setelah draft.
Bukti: `tests/Browser/artifacts/junit-20261118-161937.xml`.

Pengujian server yang berbeda dari Dusk juga dijalankan:

```powershell
php vendor/bin/phpunit --do-not-cache-result
```

Hasil: `OK (111 tests, 861 assertions)`. Suite ini memakai SQLite memory, bukan MySQL kerja.
Pemeriksaan sintaks seluruh file PHP browser/support dan `git diff --check` juga lulus.

## Menjalankan demo yang terlihat

Nyalakan MySQL XAMPP, buka terminal pada root repository, lalu:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1
```

- Chrome terlihat dengan jeda 650 ms setiap aksi. Opsi `-TypeDelay 60` menampilkan pengisian per karakter.
- Server Dusk otomatis di port 8002, database `satuair_dusk_jira`; server aplikasi kerja port 8000 tetap terpisah.
- Pilih `-Group "PBI-1,PBI-2"` untuk Fadhil, `-Group "PBI-7,PBI-8"` untuk Saptya, `-Group PBI-3` untuk Niswah, atau `-Group "PBI-9,PBI-10"` untuk Zahwah.
- Untuk memeriksa data sesudah satu kasus saja, gunakan `-Filter test_tc02_meter_342`. Data fixture direset sebelum setiap kasus.

## Bukti dan penyesuaian kode

- Screenshot per kasus tersedia di `tests/Browser/screenshots/`, dengan nama berdasarkan key Jira dan TC.
- Matriks file, cakupan, prasyarat, dan cara menjalankan: `docs/laravel-dusk-tim.md`.
- Dua skenario kegagalan query membutuhkan penanganan pesan di controller pengguna: filter gagal memberi 503; simpan profil gagal mempertahankan input dan memberi pesan gagal.
- Simulasi QueryException hanya dipasang pada router server Dusk. Ini bukan mematikan XAMPP atau simulasi outage infrastruktur penuh.
- Helper WebDriver mengisi kontrol HTML dan memicu input/change di browser, lalu membaca ulang nilainya. Perhitungan tetap oleh JavaScript aplikasi dan service server; penyimpanan tetap melalui form, sesi, dan CSRF asli.
- Hak akses Saptya yang sudah ada dilengkapi mengikuti TC Jira. Dua atribut dusk ditambahkan pada ringkasan riwayat untuk selector yang stabil.
- Tidak menambah dependency atau mengubah migration/database aplikasi kerja.

## Pemeriksaan data kerja

- Guard terbukti menolak environment kerja sebelum reset database.
- Hash `.env` dan `database/database.sqlite` sama sebelum dan setelah pengujian.
- Database Dusk pada akhir run berisi 4 akun fixture dan 0 pencatatan, sesuai TC terakhir (riwayat kosong). Database kerja tetap terpisah dengan 5 akun.
- Jumlah pencatatan pada database kerja berubah 20 menjadi 15 selama pekerjaan berlangsung. Pengguna mengonfirmasi ada perubahan di database kerja pada saat yang sama. Perubahan tersebut dipertahankan; tidak dilakukan pemulihan atau penimpaan.
- Checksum users dan meter_reading_reviews kerja tetap sama; checksum meter_readings dan water_bills berubah. Karena ada aktivitas bersamaan, laporan ini tidak mengklaim seluruh isi database kerja identik.

Laporan ini mencatat hasil pengujian sebelum publikasi. Pada tahap publikasi,
README disusun untuk setup anggota dan perintah tes setiap PBI. Status commit,
PR, dan merge dapat dilihat pada riwayat GitHub; status issue Jira tidak
dipindahkan secara manual oleh runner.
