# PBI-10 — Memeriksa hasil pemakaian sebelum digunakan untuk tagihan

Key Jira: **PBI-2**. Branch: `PBI-10-memeriksa-hasil-pemakaian-sebelum-digunakan-untuk-tagihan-PBI-2`, dibuat dari PBI-09 `2f5d82e`.

## 1. Flow bisnis dan batas PBI

1. PBI-08 menerima input Petugas dan menyimpan `meter_readings`.
2. PBI-09 menghitung meter akhir dikurangi meter awal menggunakan `HitungPemakaianMeter`. Hasil pada pencatatan baru belum menjadi tagihan.
3. **PBI-10:** Bendahara membuka antrean, membaca pencatatan/foto/catatan, membuat draf pemeriksaan, dan mengisi checklist serta catatan.
4. Draf dapat diedit atau dibatalkan. Pembatalan draf tidak menghapus pencatatan Petugas.
5. **Setujui:** server memeriksa ulang data, checklist, revisi, periode dan status; mengisi `verified_at`, menyimpan keputusan, dan menerbitkan satu tagihan dalam transaksi yang sama.
6. **Kembalikan:** alasan wajib diisi; keputusan tersimpan, Petugas melakukan koreksi melalui PBI-09, lalu Bendahara membuat pemeriksaan berikutnya. Keputusan lama tidak ditimpa.
7. Keputusan final tidak dapat diedit/dihapus. Data yang sudah disetujui mengunci koreksi/pembatalan Petugas.

Data meter tetap diinput pada form Petugas yang sama. Bendahara tidak memasukkan ulang meter awal, meter akhir, atau hasil pengurangan. Pembuatan tagihan memakai BillingService yang sudah ada, dengan gerbang persetujuan PBI-10.

## 2. Halaman / rancangan tampilan

| Halaman | URL | Isi |
|---|---|---|
| Antrean | `/bendahara/pemeriksaan` | Pelanggan, meter, periode, meter awal/akhir, pemakaian, status; filter nama/meter, periode, status. |
| Rincian pencatatan | `/bendahara/pemeriksaan/pencatatan/{id}` | Angka dan total dari PBI-09, tanggal, Petugas, foto/catatan, status dan seluruh riwayat pemeriksaan termasuk draf yang dibatalkan. |
| Buat draf | `/bendahara/pemeriksaan/pencatatan/{id}/buat` | Dua checklist dan catatan; angka meter hanya dibaca. |
| Rincian pemeriksaan | `/bendahara/pemeriksaan/{id}` | Pemeriksa, checklist, catatan, snapshot angka, status/waktu keputusan; aksi edit, batalkan, setujui, kembalikan sesuai kondisi. |
| Edit draf | `/bendahara/pemeriksaan/{id}/edit` | Mengubah checklist/catatan, dan memperbarui snapshot setelah Petugas mengoreksi data. |

Navigasi desktop dan ponsel Bendahara menampilkan **Pemeriksaan** jika memiliki `finance.manage`. Pencatatan Petugas menampilkan status dan alasan pengembalian. Pada Pembayaran Warga, pencatatan yang belum disetujui ditandai menunggu pemeriksaan, tanpa nominal tagihan yang belum terbit.

## 3. Tabel dan field yang berkaitan

| Tabel / kolom | Fungsi |
|---|---|
| `users.id` | Referensi Petugas pencatat, pelanggan, serta Bendahara pemeriksa. |
| `water_meters.id`, `user_id` | Hubungan meter dengan pelanggan. |
| `meter_readings.id`, `previous_value`, `reading_value`, `reading_date`, `photo`, `notes`, `recorded_by` | Sumber pencatatan PBI-08/09; tidak dibuat tabel pencatatan kedua. |
| `meter_readings.requires_review` — baru | Pencatatan baru wajib pemeriksaan; data sebelum migration diberi false untuk mempertahankan alur/data historis. |
| `meter_readings.verified_at` — sudah ada | Waktu pencatatan disetujui; diisi server. |
| `meter_reading_changes` | Audit koreksi Petugas; ikut menentukan revisi yang diperiksa. |
| `water_bills.meter_reading_id` — baru | FK nullable dan unik ke pencatatan; tagihan lama tetap null, satu pencatatan baru hanya menghasilkan satu tagihan. |
| `water_bills.usage_m3` | Pemakaian yang disetujui sebagai dasar tagihan. |
| `billing_periods` | Periode yang boleh diperiksa dan ditagihkan. |

Tabel baru **`meter_reading_reviews`**:

| Kolom | Tipe / sumber | Keterangan |
|---|---|---|
| `id` | ID otomatis | Identitas pemeriksaan. |
| `meter_reading_id` | FK → `meter_readings.id` | Pencatatan yang diperiksa. |
| `reviewer_id` | FK → `users.id` | Bendahara login, diisi server. |
| `status` | string | `draft`, `approved`, `returned`; ditentukan server sesuai aksi. |
| `checks` | JSON | `identity_period` dan `meter_usage` berupa boolean; diisi Bendahara. |
| `notes` | text nullable, maks. 1.000 karakter | Catatan pemeriksaan; wajib berisi alasan untuk pengembalian. |
| `snapshot` | JSON dari server | `previous_value`, `reading_value`, `usage_m3`, `reading_date`, `photo`, `notes`, `recorded_by` saat pemeriksaan. |
| `reading_revision` | string SHA-256 | Identitas data pencatatan/audit yang diperiksa; tidak dipercaya dari input angka browser. |
| `version` | integer otomatis | Menolak perubahan draf dari tab lama, termasuk perubahan dalam detik yang sama. |
| `reviewed_at` | timestamp nullable | Waktu keputusan final dari server. |
| `deleted_at` | timestamp nullable | Pembatalan draf (soft delete); riwayat dan catatan Petugas tetap ada. |
| `created_at`, `updated_at` | timestamp otomatis | Waktu pembuatan/perubahan; tampilan pemeriksaan menggunakan waktu Jakarta. |

FK menggunakan tabel tim yang sudah ada. Migration tambahan: `2026_11_18_000001_add_meter_reading_reviews.php`. Tidak memakai migrate:fresh dan tidak menimpa tagihan lama.

## 4. Use case dan CRUD

**Aktor:** Bendahara aktif dengan permission `finance.manage`. Hanya pembuat draf boleh mengedit, membatalkan, atau memfinalisasi draf tersebut; Bendahara lain dapat membaca rincian.

**Prakondisi:** login, pencatatan baru tersedia, meter/periode aktif, pencatatan belum dibatalkan/disetujui, belum ada tagihan periode yang sama, dan dasar meter awal masih sesuai riwayat. Pencatatan periode sebelumnya yang juga wajib pemeriksaan harus disetujui dahulu.

| Operasi | Implementasi |
|---|---|
| Create | Membuat draf pemeriksaan untuk satu pencatatan; satu draf aktif per pencatatan. |
| Read | Antrean, rincian pencatatan, draf/keputusan, snapshot dan riwayat. |
| Update | Mengubah checklist/catatan selama draf; menyegarkan snapshot setelah memeriksa ulang koreksi Petugas. |
| Delete | Tombol Batalkan Draf mengisi `deleted_at`; tidak menghapus pencatatan/tagihan atau keputusan final. |

**Alur utama:** antrean → rincian → buat draf → checklist/catatan → simpan → setujui → tagihan terbit dan pencatatan terkunci.

**Alur alternatif:** simpan draf tanpa checklist lengkap diperbolehkan tetapi tidak bisa disetujui; kembalikan dengan alasan → koreksi Petugas → pemeriksaan baru; batalkan draf → dapat membuat draf pengganti.

Data yang berubah setelah halaman dibuka ditolak. Jika Petugas mengoreksi setelah draf dibuat, muat ulang lalu edit/simpan draf dengan checklist yang diperiksa kembali. Draf tidak otomatis menyetujui angka terbaru tanpa pemeriksaan ulang.

## 5. Test case

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| TC-10-01 | CRUD draf | Field masuk ke `meter_reading_reviews`; edit tersimpan; pembatalan mengisi deleted_at dan riwayat dapat dibaca. |
| TC-10-02 | Setujui data valid | Status approved, verified_at terisi, satu tagihan terkait pencatatan; angka tidak bisa dikoreksi lagi oleh Petugas. |
| TC-10-03 | Kembalikan dengan alasan dan koreksi | Status returned tanpa tagihan; Petugas melihat alasan, mengoreksi, kemudian pemeriksaan baru memakai hasil terbaru. |
| TC-10-04 | Checklist/alasan tidak lengkap | Persetujuan tanpa dua checklist atau pengembalian tanpa alasan ditolak. |
| TC-10-05 | Draf ganda / klik finalisasi berulang | Tidak menggandakan draf aktif, keputusan atau tagihan. |
| TC-10-06 | Tab lama / pencatatan dikoreksi | Permintaan lama ditolak, snapshot lama tetap terbaca; wajib memeriksa ulang data terbaru. |
| TC-10-07 | Role, permission, pemilik draf salah | Ditolak server; status dan database tidak berubah. |
| TC-10-08 | Tagihan gagal diterbitkan | Seluruh transaksi rollback: status tetap draft dan verified_at tetap null. |
| TC-10-09 | Catatan lama / dibatalkan / meter nonaktif / periode tutup / dasar meter berubah | Tidak dapat disetujui atau mengganti tagihan historis. |
| TC-10-10 | Pemakaian nol dan desimal | Hasil tetap akurat dari service PBI-09; tagihan baru hanya setelah persetujuan. |

## 6. Skenario normal dan exception

**Normal:** Petugas mengisi 342,00 dari awal 317,00 → total 25,00, belum ada tagihan → Bendahara membuat draf, mencentang kedua pemeriksaan → setujui → tagihan 25,00 m³ terbit.

**Exception:** draf disimpan ketika meter akhir 342,00, lalu Petugas mengoreksi menjadi 347,00 → draf lama tidak boleh disetujui → Bendahara memeriksa ulang dan menyimpan snapshot 30,00 m³ → baru dapat menyetujui.

**Pengembalian:** catatan “Angka foto berbeda, mohon koreksi” → status returned → tidak ada tagihan → Petugas memperbaiki angka → pemeriksaan baru, sementara keputusan returned sebelumnya tetap tersimpan.

## 7. Input dan output pengujian

| ID | Input / aksi | Output UI dan database yang diharapkan |
|---|---|---|
| TS-10-01 | Simpan draf, catatan “Angka sesuai bukti meter.” | status draft, notes dan checks tersimpan, reviewer_id otomatis, bill belum ada. |
| TS-10-02 | Edit catatan draf | notes berubah, version bertambah, angka pencatatan tidak berubah. |
| TS-10-03 | Batalkan draf | deleted_at terisi; `meter_readings` tetap aktif; dapat membuat draf baru. |
| TS-10-04 | Awal 317,00; akhir 342,00; dua checklist; Setujui | approved; verified_at terisi; `water_bills.usage_m3 = 25.00`, total default Rp127.500, FK meter_reading_id terisi. |
| TS-10-05 | Awal 317,00; akhir 317,00; Setujui | usage_m3 0.00; biaya admin default Rp2.500. |
| TS-10-06 | Awal 317,00; akhir 342,25; Setujui | usage_m3 25.25; total default Rp128.750. |
| TS-10-07 | Kembalikan dengan notes kosong | Validasi alasan; keputusan dan tagihan tidak dibuat. |
| TS-10-08 | Kembalikan dengan alasan, koreksi akhir 347,00, periksa ulang | Riwayat returned tetap 25.00; pemeriksaan approved baru dan bill memakai 30.00. |
| TS-10-09 | Angka diubah setelah draf disimpan / tab lama | Pesan data berubah; tidak menerbitkan tagihan hingga draf diperbarui. |
| TS-10-10 | Kirim status approved/reviewer palsu melalui create | Server tetap membuat draft dengan pemeriksa login dan snapshot server. |
| TS-10-11 | Ulangi persetujuan / coba edit atau hapus keputusan final | Ditolak; tetap satu tagihan dan keputusan final tidak berubah. |

## Menjalankan dan mencoba

Database lokal `satuair` sudah dicadangkan sebelum migration tambahan. Migration dijalankan pada 18 November 2026 dan data meter/tagihan lama dibandingkan sebelum/sesudah: nilai tetap sama. Konfigurasi `.env` MySQL dan file SQLite lama tidak diubah. Tidak menjalankan ulang DatabaseSeeder pada database kerja.

```powershell
php artisan migrate
php artisan serve --port=8000
```

Nyalakan MySQL XAMPP. Login Petugas `petugas@gmail.com` / `password123`, lalu catat pelanggan yang belum dicatat pada periode aktif. Saat setup selesai, Rina memiliki meter awal 530,00 dan belum dicatat pada November: coba akhir 555,00 → total 25,00. Budi yang sebelumnya sudah disimpan adalah catatan lama dan tidak dimasukkan ulang ke antrean PBI-10.

Login Bendahara `bendahara@gmail.com` / `password123` → **Pemeriksaan** atau <http://127.0.0.1:8000/bendahara/pemeriksaan>. Buat draf, isi checklist/catatan, coba edit atau batalkan. Untuk persetujuan, simpan draf dengan kedua checklist lalu tekan **Setujui & Terbitkan Tagihan**. Gunakan sesi browser terpisah jika ingin login Petugas dan Bendahara bersamaan.

Query pembuktian di phpMyAdmin, database `satuair`:

```sql
SELECT id, meter_reading_id, reviewer_id, status, checks, notes,
       snapshot, version, reviewed_at, deleted_at
FROM meter_reading_reviews ORDER BY id DESC;

SELECT id, previous_value, reading_value, requires_review, verified_at
FROM meter_readings ORDER BY id DESC;

SELECT id, meter_reading_id, usage_m3, total_amount, status
FROM water_bills ORDER BY id DESC;
```

## Bukti verifikasi dan pekerjaan lanjutan

- `php vendor/bin/phpunit --do-not-cache-result`: **111 tes, 861 assertion lulus**; 19 tes PBI-10 termasuk variasi data, dan tes regresi yang sudah ada.
- `php artisan route:list --path=bendahara/pemeriksaan`: sembilan route terdaftar.
- `composer validate --no-check-publish --no-check-all`: valid; tidak menambah dependency.
- `node tests/browser-pbi10.cjs`: lulus alur Petugas → CRUD draf Bendahara → pengembalian/koreksi → penolakan draf lama → persetujuan → tagihan dan penguncian, termasuk desktop/ponsel.
- Database MySQL uji `satuair_pbi10_test_20261118_0145b`: draf #1 soft deleted; #2 returned mempertahankan snapshot 25.00; #3 approved dengan snapshot 35.00; satu tagihan baru terhubung ke pencatatan, usage_m3 35.00 dan total Rp177.500.

Tes PHPUnit memakai SQLite dalam memori. Tes browser memakai Playwright yang tersedia di lingkungan pengembangan, bukan Laravel Dusk. Dusk tetap pekerjaan lanjutan sesuai urutan yang disepakati. Lengkapi hasil Dusk, prompt/bukti AI, serta lampiran tampilan pada Jira sebelum menandai selesai atau In Review. Teks file ini menjadi acuan pembaruan Jira; issue Jira belum diedit otomatis.

PBI-09 telah digabungkan ke main melalui PR #13 pada commit `802f4df`. Progres PBI-10 disimpan pada branch tersendiri dengan key Jira `PBI-2`; pengujian Laravel Dusk masih merupakan pekerjaan lanjutan sebelum dinyatakan selesai atau In Review.
