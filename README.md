# SatuAir — Sistem Manajemen Air Bersih Digital

**SatuAir** adalah platform digital terpadu untuk pengelolaan air bersih Desa Cibodas, dikembangkan oleh BUMDes Karya Mandiri. Sistem ini mencakup pencatatan meter, pembayaran tagihan digital (QRIS, GoPay, Dana, OVO, transfer bank), dan penanganan pengaduan.

---

## 🔑 Akun Demo

Semua akun demo menggunakan password: `password123`

Login hanya membutuhkan **Email** dan **Password** (pilih peran akun di tab login).  
Nomor HP sudah terdaftar di database — hanya dibutuhkan saat registrasi akun baru.

| Peran | Email | Password | No. HP |
|---|---|---|---|
| Bendahara (Pengurus BUMDes) | `bendahara@gmail.com` | `password123` | 081234567890 |
| Petugas PAB | `petugas@gmail.com` | `password123` | 081234567891 |
| Warga (Rina Marlina) | `rina@gmail.com` | `password123` | 081234567892 |
| Warga (Budi Santoso) | `budi@gmail.com` | `password123` | 081234567893 |

> **Catatan:** Akun demo tidak ditampilkan di UI. Gunakan tabel di atas untuk login.

---

## ⚙️ Instalasi & Setup

```bash
# 1. Clone & masuk ke direktori
cd satuair

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env
php artisan key:generate

# Jika memakai SQLite dari .env.example, pastikan file database tersedia
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"

# 4. Konfigurasi database di .env, lalu jalankan migrasi & seeder
php artisan migrate:fresh --seed

# 5. Jalankan server
php artisan serve
```

---

## 🧪 Menjalankan Test

```bash
php artisan test
# Expected: 15 tests, 59 assertions — semua passed
```

---

## 🏗️ Struktur Peran

| Peran | Akses |
|---|---|
| **Bendahara** | Dashboard bendahara BUMDes, rekapitulasi keuangan & kas masuk, tutup buku tahunan, konfirmasi pembayaran, monitoring pengaduan |
| **Petugas PAB** | Catat bacaan meter fisik, pencatatan pembayaran tunai di posko/kantor, tindak lanjut pengaduan teknis warga |
| **Masyarakat/Warga** | Lihat & bayar tagihan air (QRIS, VA Bank BRI/BCA/Mandiri/BNI/BSI/BJB, E-Wallet GoPay/DANA/OVO/ShopeePay/LinkAja), buat pengaduan gangguan |

---



### 1. Bendahara menyiapkan periode dan tagihan

Bendahara masuk menggunakan akun Bendahara (`bendahara@gmail.com`), lalu:

1. Membuka **Rekap** untuk memantau pemasukan dan status buku per bulan.
2. Membuka **Pembayaran** untuk memilih tahun dan bulan serta melihat catatan meter, pemakaian, nominal tagihan, dan status setiap warga.
3. Memperbarui meter atau tarif warga melalui menu **Pengguna** bila diperlukan.

Perhitungan tagihan menggunakan rumus:

**Pemakaian meter × tarif per m³ + biaya administrasi**

Pada simulasi awal, periode **September 2026** aktif. Tarif Rp0 berarti biaya pemakaian menjadi nol; tagihan hanya menyisakan biaya administrasi. Pastikan tarif benar sebelum menyimpan perubahan.

### 2. Petugas mencatat meter

Petugas masuk menggunakan akun Petugas, lalu membuka **Catat Meter**. Petugas memilih meter warga dan mencatat angka meter terbaru. Sistem menghitung pemakaian dari selisih angka meter dan membuat tagihan untuk periode yang sedang aktif. Tagihan itu kemudian muncul di akun warga.

Bendahara juga dapat memperbarui angka meter dan tarif melalui **Pengguna**. Untuk tagihan baru, periode harus aktif terlebih dahulu.

### 3. Warga melihat dan membayar tagihan

Warga masuk menggunakan akunnya, lalu membuka **Tagihan Air**. Tagihan pada periode aktif tampil dengan status **Menunggu Pembayaran**. Warga memilih **Bayar**, memilih metode pembayaran (QRIS, VA Bank, E-Wallet), lalu menyelesaikan proses pembayaran.

Pembayaran pada aplikasi ini masih berupa **simulasi**, belum terhubung ke transaksi bank atau penyedia pembayaran sungguhan. Setelah Warga menekan simulasi berhasil, pembayaran tersimpan dengan status **Menunggu Konfirmasi** dan notifikasi dibuat untuk Bendahara.

### 4. Bendahara mengonfirmasi pembayaran

Bendahara membuka notifikasi pembayaran atau halaman **Pembayaran**, meninjau data warga dan nominal yang dikirim, lalu menekan **Konfirmasi Pembayaran**. Setelah dikonfirmasi:

- Status pembayaran menjadi **Lunas / Dikonfirmasi**.
- Tagihan warga ditandai lunas.
- Pendapatan terverifikasi masuk ke Rekap.

Pembayaran dilakukan oleh Warga. Bendahara bertugas memantau dan mengonfirmasi pembayaran.

### 5. Warga mengirim pengaduan, Petugas menindaklanjuti

Jika mengalami gangguan air, Warga membuka **Pengaduan**, mengisi kategori, lokasi, dan keterangan, lalu mengirim laporan. Sistem membuat nomor tiket dan memberi status awal **Menunggu**.

Petugas membuka menu **Pengaduan**, melihat laporan yang ditugaskan kepadanya atau yang belum ditugaskan, lalu memperbarui status—misalnya **Diverifikasi**, **Diproses**, atau **Selesai**—beserta catatan dan foto bila diperlukan. Warga dapat melihat perkembangan laporan dan riwayat tindak lanjut. Bendahara dapat memantau daftar pengaduan.

### 6. Menutup September dan membuka Oktober

Bendahara hanya dapat menutup periode setelah seluruh meter warga memiliki tagihan dan semua tagihan periode tersebut lunas. Setelah September ditutup:

1. Status September berubah menjadi **Ditutup** dan tidak lagi menjadi periode pembayaran aktif.
2. Bendahara membuka periode berikutnya, yaitu **Oktober**.
3. Bendahara atau Petugas memperbarui catatan meter warga untuk Oktober.
4. Sistem membuat tagihan Oktober dan menampilkannya kepada Warga dengan status awal **Menunggu Pembayaran**.

**Ringkasnya:** Bendahara mengaktifkan periode → Petugas/Bendahara mencatat meter → Warga melihat dan membayar tagihan → Bendahara menerima notifikasi dan mengonfirmasi → Bendahara menutup periode setelah lunas → periode berikutnya dibuka → proses berulang.

---



Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).