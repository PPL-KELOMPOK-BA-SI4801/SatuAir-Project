# Rencana integrasi PBI-09 dengan PBI-08 Yuka

Pemeriksaan pada 16 November 2026 menggunakan source remote setelah `git fetch origin`. Tahap ini membaca source dan menyusun rencana; belum mengubah implementasi aplikasi atau menjalankan tes pada branch Yuka.

## Acuan source dan kondisi kerja

- Branch Yuka: `origin/PBI-07-Melihat-daftar-dan-jadwal-pelanggan`, commit `d1f5d40`.
- Fondasi tim terbaru: `origin/main`, commit `4279285`.
- Branch kerja: `PBI-09-menghitung-pemakaian-dari-selisih-angka-meter-PBI-1`, HEAD `af27b5f`.
- Working tree masih memuat integrasi lokal sebelumnya dan penggabungan main yang belum dicommit. Semua konflik sudah ditandai selesai, tetapi merge masih berlangsung. Penggabungan lain tidak boleh ditumpuk pada keadaan ini.
- `database/database.sqlite` sudah memiliki perubahan lokal sebelum pemeriksaan. Database kerja dipertahankan.

Source Yuka sekarang mencakup controller, model, Blade daftar/form, route, migration, dan service tagihan. Model `WaterMeter` dan `MeterReading` serta tabel `water_meters` dan `meter_readings` sama dengan fondasi main yang dipakai integrasi lokal. Tidak perlu membuat tabel pemakaian kedua untuk PBI-09.

## Yang sudah dikerjakan Yuka

1. Form pelanggan, meter awal dari riwayat, input meter akhir, foto/catatan, dan penyimpanan pembacaan.
2. JavaScript yang langsung menampilkan selisih ketika meter akhir berubah.
3. Validasi server terhadap meter akhir yang lebih kecil daripada pembacaan sebelumnya.
4. Pemanggilan `BillingService` untuk membuat atau memperbarui tagihan.

Dengan demikian, sebagian perilaku PBI-09 sudah tercakup dalam source Yuka. Kontribusi implementasi awal tersebut perlu dipertahankan dalam review dan riwayat Git. Service PBI-09 yang sudah tersedia akan dipakai untuk menyatukan perhitungan dan memperbaiki celah yang ditemukan, bukan dianggap fitur baru hanya karena nama filenya berbeda.

## Temuan dari pembacaan kode

| Bagian | Temuan | Dampak atau rencana penanganan |
| --- | --- | --- |
| Hasil langsung di browser | `Math.max(0, val - meterAwal)` mengubah selisih negatif menjadi nol. Input kosong juga diperlakukan sebagai nol. | Tampilkan tanda `—` dan pesan kesalahan untuk input tidak valid. Nilai nol hanya untuk input valid dengan meter akhir sama dengan meter awal. |
| Meter awal | `create()` dan `store()` mengambil `latestReading` tanpa membatasi periode. | Gunakan pembacaan terakhir sebelum awal periode yang dicatat. Pembacaan bulan berjalan tidak boleh mengubah dasar perhitungan bulan tersebut. |
| Ketelitian input | Server menerima `numeric|min:0` tanpa batas dua desimal, sementara input HTML memakai `step=0.01`. | Validasi dua desimal dan batas kolom `DECIMAL(10,2)` di server. Terima format koma/titik melalui normalisasi yang sama. |
| Periode aktif | Pemeriksaan hanya berjalan di dalam `if ($activeBilling)`, sehingga periode yang tidak aktif justru melewati pemeriksaan. | Tolak permintaan jika periode aktif yang cocok tidak ditemukan. Ini merupakan penyesuaian alur pencatatan bersama PBI-08. |
| Pencatatan ganda | Teks form menyebut satu pencatatan per periode, tetapi `store()` belum memeriksa pembacaan pada periode yang sama. Migration juga tidak memberikan batas unik per bulan. | Tambahkan pemeriksaan server dan nonaktifkan form untuk periode yang sudah tercatat. Lindungi proses simpan dengan transaksi dan penguncian baris sesuai dukungan database. Jangan mengklaim konkurensi MySQL sudah teruji sebelum pengujiannya dijalankan. |
| Rumus tagihan | `BillingService` memiliki pengurangan sendiri dan penggunaan `max(0, ...)`. | Gunakan service perhitungan yang sama agar hasil yang ditampilkan dan hasil yang dipakai server konsisten. |
| Informasi form | Periode Oktober 2026, jadwal 31 Oktober, dan status BELUM DICATAT ditulis tetap. | Sesuaikan informasi tersebut dengan data aktual ketika mengintegrasikan form; ini termasuk perbaikan bagian pencatatan PBI-08. |
| Foto | HTML mewajibkan foto, tetapi validasi server menerima `nullable`. | Ikuti aturan foto yang disepakati untuk PBI-08/PBI-19. Jangan otomatis mengubahnya sebagai bagian perhitungan. |
| Fondasi akses | Branch Yuka memakai fondasi lama yang belum memuat middleware role/permission terbaru pada main. | Pertahankan login, role, dan permission terbaru saat mengambil perubahan Yuka. Jangan mengganti seluruh route atau model User dengan versi branch lama. |
| Pengujian | Suite pada branch Yuka belum memuat tes khusus alur meter/perhitungan. Laravel Dusk belum tercantum sebagai dependency. | Gunakan dan sesuaikan tes PBI-09 yang sudah ada, kemudian susun pengujian Dusk untuk form bersama. |

Temuan validasi browser dan server dibedakan: source server Yuka sudah menolak meter akhir yang lebih kecil. Masalah pada browser adalah tampilan nol yang menyesatkan sebelum permintaan tersebut ditolak server.

## Urutan pengembangan

1. **Amankan kondisi kerja.** Simpan cadangan source, perubahan index/working tree, dan database sebelum merapikan penggabungan main yang masih berlangsung. Pertahankan perubahan lokal pengguna. Tentukan penyelesaian merge lama sebelum melakukan merge lain; jangan melakukan reset atau abort tanpa terlebih dahulu mengamankan seluruh perubahan.
2. **Selaraskan fondasi main.** Gunakan autentikasi dan permission dari `4279285`. Ambil perubahan PBI-07/08 secara terarah agar controller daftar, statistik, dan form Yuka tetap tersedia tanpa menurunkan fondasi akses main.
3. **Pakai satu form pencatatan.** Pertahankan form Yuka di `/petugas/meters/{id}/catat`, nama request `reading_value`, upload foto, catatan, dan endpoint simpan yang sama. Tidak membuat form perhitungan, menu, atau route preview baru.
4. **Sambungkan service yang sudah ada.** Pakai `HitungPemakaianMeter` pada `create()`/`store()` untuk normalisasi, pemilihan meter awal, dan validasi hasil. Gunakan helper `WaterMeter::readingBeforePeriod()` yang sudah dibuat. Hasil dari browser tidak menjadi sumber kebenaran saat penyimpanan.
5. **Sambungkan panel pemakaian.** Sesuaikan selector pada `public/assets/meter.js` dengan elemen form Yuka (`reading_value`, panel hasil, dan pesan kesalahan). Gunakan komponen Total Pemakaian yang sudah ada bila sesuai layout. Pertahankan JavaScript pemilihan/preview foto saat memisahkan skrip perhitungan.
6. **Konsistenkan jalur simpan.** Gunakan satu hasil perhitungan server untuk pembacaan dan pemakaian pada alur yang sudah ada. Gabungkan validasi periode dan duplikat bersama PBI-08. Pertahankan database kerja; tidak menyalin `.env` atau database milik Yuka dan tidak menjalankan `migrate:fresh` pada database tim.
7. **Uji integrasi dan Dusk.** Jalankan tes service/server pada database uji terpisah, lalu tes browser pada form Yuka yang sudah terhubung. Verifikasi juga login dan permission terbaru. Pengujian MySQL dilakukan pada database uji terpisah mengikuti pilihan database tim.

## Kriteria penerimaan integrasi

| Skenario | Hasil yang diharapkan |
| --- | --- |
| Meter awal 317,00 dan akhir 342,00 | Total 25,00 m³, formula sesuai, hasil server sama. |
| Meter awal 317,00 dan akhir 317,00 | Total 0,00 m³ dan input valid. |
| Meter akhir 300,00 dengan awal 317,00 | Pesan kesalahan dan penyimpanan ditolak. Tampilan tidak mengubah hasil menjadi 0,00. |
| Input kosong | Tanda `—` dan penyimpanan ditolak. |
| Input bukan angka atau lebih dari dua desimal | Ditolak server, termasuk request langsung yang melewati HTML/JavaScript. |
| Angka desimal koma/titik | Hasil perhitungan dan format dua desimal konsisten. |
| Pelanggan tanpa riwayat | Mengikuti aturan meter awal nol yang digunakan implementasi saat ini. |
| Riwayat dari periode berbeda | Meter awal berasal dari pembacaan sebelum periode pencatatan. |
| Pencatatan kedua pada bulan yang sama | Ditolak, termasuk pencatatan pada hari terakhir bulan. |
| Periode tutup atau meter nonaktif | Penyimpanan ditolak. |
| Meter awal/total/petugas palsu dikirim dari browser | Server memakai nilai dari database dan akun terautentikasi. |
| Pengguna tanpa role/permission yang sesuai | Tidak dapat mengakses atau menyimpan pencatatan. |
| Gagal saat penyimpanan lanjutan | Tidak meninggalkan pembacaan atau foto baru yang tidak konsisten. |

Pengujian Dusk berfokus pada login Petugas, pemilihan pelanggan, perubahan total secara langsung, tiga kasus utama di atas, dan hasil setelah simpan. Pemasangan Dusk serta hasil eksekusinya dilaporkan terpisah; belum dikerjakan pada tahap pemeriksaan ini.

## Batas pekerjaan dan kontribusi

- Form, daftar/jadwal, foto/catatan, dan penyimpanan awal tetap menjadi kontribusi PBI-07/08 Yuka.
- PBI-09 melanjutkan penyatuan perhitungan, perbaikan perilaku yang ditemukan, ketelitian desimal, konsistensi meter awal, tampilan hasil, dan pengujian. Integrasi dapat menyentuh file bersama; seluruh controller/form tidak diklaim sebagai pekerjaan PBI-09.
- Tidak menambahkan fitur edit/pembatalan pencatatan dalam tahap ini.
- Source saat ini membuat tagihan langsung setelah simpan. Perubahan menjadi alur pemeriksaan Bendahara sebelum tagihan adalah keputusan PBI-10 dan perlu ditangani sebagai pekerjaan tersendiri.
- Pemeriksaan ini tidak melakukan merge baru, stage, commit, atau push. Hanya dokumen rencana ini yang ditambahkan.

Source utama yang diperiksa:

- [Controller Yuka](https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project/blob/d1f5d40/app/Http/Controllers/Petugas/MeterReadingController.php)
- [Form Yuka](https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project/blob/d1f5d40/resources/views/petugas/meters/create.blade.php)
- [Service tagihan Yuka](https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project/blob/d1f5d40/app/Services/BillingService.php)
- [Migration pembacaan meter](https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project/blob/d1f5d40/database/migrations/2026_01_01_000005_create_meter_readings_table.php)
- [Route main terbaru](https://github.com/PPL-KELOMPOK-BA-SI4801/SatuAir-Project/blob/4279285/routes/web.php)
