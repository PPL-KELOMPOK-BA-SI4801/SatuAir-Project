# Laravel Dusk SatuAir - pengujian sesuai Jira

Paket ini mengikuti tujuh deskripsi Jira yang diberikan pada 18 November 2026.
Nomor **key Jira** berbeda dari nomor fitur. Nama grup selalu memakai key Jira.
Setiap metode `test_tcXX_...` mengikuti TC-XX pada deskripsi terkait. Skenario
normal, exception, input/output dan daftar AT yang berulang bukan tes tambahan.

| Pemilik | Key Jira | Fitur | File di `tests/Browser/` | TC |
|---|---|---|---|---|
| Saptya | PBI-7 | PBI-01 Registrasi, login, logout Masyarakat | RegistrasiLoginTest.php | 01-07 |
| Saptya | PBI-8 | PBI-02 Pengaturan hak akses | AccessControlTest.php | 01-08 |
| Niswah | PBI-3 | PBI-11 Riwayat input meter | RiwayatMeterTest.php | 01-02 |
| Zahwah | PBI-9 | PBI-05 Filter pengguna berdasarkan peran | FilterPenggunaTest.php | 01-08 |
| Zahwah | PBI-10 | PBI-06 Lihat dan ubah detail pelanggan | DetailPelangganTest.php | 01-08 |
| Fadhil | PBI-1 | PBI-09 Perhitungan selisih meter | PemakaianMeterTest.php | 01-09 |
| Fadhil | PBI-2 | PBI-10 Pemeriksaan sebelum tagihan | PemeriksaanPemakaianTest.php | 01-09 |

Total: **51 test case**. Variasi koma/titik serta dua input tidak valid PBI-09
dijalankan dalam TC yang sama sesuai Jira. Validasi kosong dan format email
melibatkan validasi HTML browser; penolakan server juga diperiksa pada kasus
angka meter, password lemah, permission tidak valid, dan keputusan pemeriksaan.

## Menjalankan browser terlihat dan lambat

Prasyarat: Composer dependencies sudah terpasang (`composer install`), PHP dengan
PDO MySQL, Google Chrome, dan MySQL XAMPP aktif pada `127.0.0.1:3306`.
ChromeDriver dapat disesuaikan saat Chrome diperbarui:

```powershell
php artisan dusk:chrome-driver --detect
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1
```

Perintah kedua memulai server Dusk sendiri, menyiapkan database uji, menjalankan
Laravel Dusk dengan Chrome terlihat, lalu menghentikan server uji miliknya.
Jeda bawaan **650 ms per aksi**. Input diisi utuh lalu dibaca ulang untuk memastikan
tidak ada karakter yang hilang. Pengetikan per karakter dapat dipilih lewat
`-TypeDelay`. Helper mengisi kontrol HTML di browser melalui WebDriver JavaScript
dan memicu event `input`/`change` agar Alpine serta perhitungan halaman tetap
berjalan, tanpa bergantung pada fokus keyboard Windows. Klik memakai kontrol
halaman asli. Helper tidak mengisi hasil perhitungan, menyimpan lewat API lain,
atau melewati validasi form; submit tetap menggunakan form, sesi, dan CSRF asli.
Memuat halaman
dan menunggu kondisi selesai tetap memakai wait Dusk; jeda bukan pengganti wait.
Untuk demo lebih lambat:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Delay 1000 -TypeDelay 60
```

Pilih key Jira atau pemilik:

```powershell
# Fadhil
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group "PBI-1,PBI-2"
# Saptya
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group "PBI-7,PBI-8"
# Niswah
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group PBI-3
# Zahwah
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Group "PBI-9,PBI-10"
# Satu kasus, contoh persetujuan pemeriksaan
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/dusk.ps1 -Filter test_tc05_setujui
```

Opsi `-Headless` tersedia untuk pengulangan tanpa tampilan browser. Demonstrasi
memakai mode bawaan tanpa opsi tersebut. Runner menjalankan `php artisan dusk`
dengan `phpunit.dusk.xml`, terpisah dari suite Unit/Feature pada `phpunit.xml`.

## Database, data contoh, dan pembatasan

- Database khusus tes: **satuair_dusk_jira**. Isinya direset setiap test case.
- Database kerja **satuair** dan file `.env` tidak diubah. SQLite kerja tidak dipakai.
- Server browser: `http://127.0.0.1:8002`; aplikasi kerja pada port 8000 tetap terpisah.
- Guard memeriksa environment `dusk`, penanda runner, MySQL lokal, nama database
  persis, serta header dari server Dusk sebelum melakukan reset.
- Runner memakai environment proses dan tidak menukar `.env`. Tidak memakai
  `migrate:fresh` pada database kerja. Jangan memakai database tes untuk data penting.
- Default akses database: root, password kosong. Jika berbeda, set environment
  `DUSK_DB_USERNAME` dan `DUSK_DB_PASSWORD` di terminal sebelum menjalankan runner.
- Fixture direset tiap TC: Pak Hendra (`admin@gmail.com`), Ahmad Fauzi
  (`petugas@gmail.com`), Rina (`rina@gmail.com`), Budi (`budi@gmail.com`).
  Password fixture: `password123`. Akun ini dibuat hanya dalam database Dusk.
- Periode memakai bulan berjalan agar tes tidak bergantung pada November 2026.
  Meter lama 317.00 dibuat pada akhir bulan sebelumnya; input utama 342.00
  menghasilkan 25.00 m3. Tarif bawaan 5000 + admin 2500 menghasilkan 127500.
- Data masuk melalui browser untuk operasi yang diuji. Pembuatan kondisi awal,
  pemeriksaan DB, pencabutan permission awal, dan kondisi tanpa pengguna dilakukan
  langsung pada database uji, bukan melalui aplikasi kerja.

## Cakupan inti per PBI

- PBI-7: registrasi valid, email duplikat, password lemah, login valid, password
  salah, akun tidak aktif, logout dan akses halaman terlindungi setelah logout.
- PBI-8: akses berizin, 403 tanpa izin, simpan permission dan efek akses, ID
  permission palsu ditolak, Masyarakat/Petugas tanpa izin, URL langsung,
  perlindungan `access.manage` Bendahara. Tes Saptya yang sudah ada dilengkapi,
  bukan dijalankan ganda dengan fixture yang tidak terisolasi.
- PBI-3: ringkasan/jumlah/pemakaian, baris milik akun login dan tidak bocor ke
  pelanggan lain; kondisi tanpa riwayat. Sesuai dua TC inti Jira; filter riwayat
  tidak ditambahkan sebagai TC Dusk baru.
- PBI-9: daftar semua, tiga filter peran, kembali ke semua peran, hasil kosong,
  kegagalan query filter, dan pengguna tanpa akses. Admin di Jira mengikuti nilai
  role aplikasi `bendahara`.
- PBI-10 (Zahwah): detail, perubahan valid, kolom wajib kosong, email tidak valid,
  404 pelanggan tidak ada, kegagalan simpan, akses non-Bendahara, buka ulang
  detail setelah simpan. Profil pelanggan berada di `users`, sesuai kode main.
- PBI-1: meter awal readonly, 25 m3, nol, negatif ditolak, kosong, huruf,
  koma/titik, ketelitian dan batas nilai, pelanggan baru. Simpan dan rincian
  diverifikasi terhadap `meter_readings`.
- PBI-2: daftar/rincian, create/read/update/delete draft, persetujuan/tagihan
  dan penguncian Petugas, pengembalian/koreksi/draft baru, checklist/alasan
  kurang, data berubah setelah draft, dan dua tab agar tagihan tidak ganda.

## Simulasi kegagalan database dan perbaikan kecil

TC-07 filter dan TC-06 detail pelanggan membutuhkan kegagalan query. Router
`tests/Browser/Support/server.php` menyuntikkan satu `QueryException` pada query
yang ditargetkan, hanya setelah guard database Dusk lolos. Marker langsung
dihapus ketika dipakai dan dibersihkan pada tearDown. Tidak ada route fault
publik, server MySQL tidak dimatikan, dan autentikasi tetap berjalan asli.
Ini simulasi kegagalan query, bukan pengujian outage infrastruktur MySQL penuh.

Controller pengguna sebelumnya belum menangani dua kegagalan tersebut dengan
pesan yang dijanjikan Jira. Penanganan ditambah: filter gagal memberi respons
503 dan pesan coba lagi; simpan profil gagal memberi pesan validasi, mempertahankan
input, dan tidak menyatakan sukses. Detail teknis tetap di log, bukan ditampilkan.

## Bukti hasil

- Screenshot per TC: `tests/Browser/screenshots/PBI-<key>-TC<nomor>.png`.
- Screenshot kegagalan: `failure-*.png`; source dan console dicatat oleh Dusk.
- Laporan JUnit dan terminal: `tests/Browser/artifacts/junit-<waktu>.xml` dan
  `run-<waktu>.txt`. Log server juga di folder artifacts.
- Screenshot/log/laporan runtime diabaikan Git. Placeholder `.gitignore` dan
  kode tes tetap disertakan; pilih screenshot hasil run final untuk bukti Jira.
- Dusk menguji UI dan database; 111 tes Unit/Feature terdahulu adalah pengujian
  server yang berbeda. Jangan menyebut hasil PHPUnit server sebagai hasil Dusk.
- Tidak memindahkan issue Jira atau membuat commit resolve secara otomatis.
  Bukti dan perubahan perlu ditinjau sebelum dimasukkan ke Jira/commit.
