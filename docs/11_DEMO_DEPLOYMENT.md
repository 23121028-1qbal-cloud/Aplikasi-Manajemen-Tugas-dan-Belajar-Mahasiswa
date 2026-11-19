# 11 — PANDUAN DEPLOYMENT SIMULASI (LOKAL + NGROK)

| Metadata | Value |
|---|---|
| Document ID | DOC-11 |
| Status | Panduan operasional demo |
| Target | Laravel 12, PHP 8.4, MySQL 8.4, Bootstrap 5.3 |
| Sifat | **Simulasi lokal/staging**, bukan server produksi |

## 1. Tujuan dan batasan

Dokumen ini menjelaskan cara menjalankan aplikasi dan mengeksposnya ke internet
lewat tunnel ngrok, sesuai instruksi tugas: bila hosting belum tersedia, deployment
boleh disimulasikan pada lingkungan lokal atau staging.

Batasan yang harus disebutkan pada laporan:

- Aplikasi berjalan di komputer lokal; tunnel hanya meneruskan trafik.
- Tautan hanya aktif selama proses `php artisan serve` dan `ngrok` berjalan.
- Paket gratis ngrok menampilkan halaman peringatan sebelum masuk aplikasi dan
  memiliki kuota 1 GB transfer serta 20.000 request per bulan.
- Domain gratis berbentuk `<nama-acak>.ngrok-free.app`/`.ngrok-free.dev`, statis
  per akun, tanpa custom domain.
- Backup, monitoring, dan rollback pada dokumen ini masih bersifat prosedur lokal,
  belum praktik di server produksi.

## 2. Prasyarat

| Komponen | Versi yang diuji |
|---|---|
| PHP | 8.4.24 (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`) |
| Composer | 2.10.x |
| Node.js | 20.x |
| MySQL | 8.4.13 |
| ngrok | agent versi terbaru + akun (authtoken) |

## 3. Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
```

`npm run build` wajib dijalankan. Jangan memakai `npm run dev` saat diakses lewat
tunnel karena koneksi HMR Vite tidak melewati ngrok.

## 4. Konfigurasi environment

Isi `.env` minimal seperti berikut (ganti nilai bertanda kurung):

```dotenv
APP_NAME="Manajemen Tugas Mahasiswa"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://<nama-acak>.ngrok-free.app

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_manajemen_akademik
DB_USERNAME=<user-db>
DB_PASSWORD=<password-db>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null

# Trust semua proxy HANYA untuk demo lokal lewat ngrok.
TRUSTED_PROXIES=*

CACHE_STORE=database
QUEUE_CONNECTION=database
```

| Variabel | Alasan |
|---|---|
| `APP_URL` | Harus sama dengan URL ngrok agar redirect, link aset, dan CSRF konsisten. Salah nilai → 419 atau redirect loop. |
| `TRUSTED_PROXIES` | ngrok mengirim `X-Forwarded-Proto: https`. Tanpa ini Laravel menganggap request HTTP sehingga cookie `Secure` tidak terkirim dan URL yang dihasilkan memakai `http://`. |
| `SESSION_SECURE_COOKIE=true` | Cookie sesi hanya dikirim lewat HTTPS. Set `false` bila menguji langsung di `http://127.0.0.1`. |

`TRUSTED_PROXIES=*` hanya untuk demo. Pada server nyata, isi dengan IP Nginx/proxy
yang spesifik.

## 5. Database dan akun awal

```bash
php artisan migrate
php artisan db:seed          # prioritas, status tugas, status belajar
php artisan app:create-admin admin
```

Lalu siapkan data demo. Urutan yang disarankan:

1. Admin membuat akun dosen (`/admin/lecturers`) — password sementara ditampilkan sekali.
2. Admin membuat mata kuliah dan menetapkan dosen (`/admin/courses`).
3. Mahasiswa mendaftar sendiri lewat `/register`.
4. Admin mendaftarkan mahasiswa ke mata kuliah (`/admin/enrollments`).

## 6. Menjalankan aplikasi

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Buka `http://127.0.0.1:8000` untuk memastikan aplikasi berjalan sebelum tunnel.

## 7. Mengekspos lewat ngrok

```bash
ngrok config add-authtoken <token>
ngrok http 8000
```

Salin domain yang tampil (mis. `https://abc123.ngrok-free.app`), lalu:

1. Set `APP_URL` di `.env` ke URL tersebut.
2. Jalankan `php artisan config:clear` (dan `php artisan cache:clear` bila perlu).
3. Restart `php artisan serve` bila sebelumnya sudah berjalan.
4. Buka URL ngrok di browser dan lewati halaman peringatan ngrok.

## 8. Verifikasi HTTPS dan proxy

Lakukan pemeriksaan berikut sebelum merekam video demo:

| Pemeriksaan | Cara | Hasil yang diharapkan |
|---|---|---|
| Skema URL | Lihat address bar dan link di halaman | Semua `https://`, tidak ada mixed content |
| Cookie sesi | DevTools → Application → Cookies | `laravel_session` bertanda `Secure` |
| Redirect | Akses halaman terproteksi tanpa login | Dialihkan ke `https://.../login` |
| Aset | DevTools → Network | CSS/JS termuat 200, tanpa error 404 |
| CSRF | Login dan submit form | Tidak muncul 419 Page Expired |

Jika muncul 419 atau redirect loop, periksa `APP_URL`, `TRUSTED_PROXIES`, dan
`SESSION_SECURE_COOKIE` — ketiganya harus konsisten.

## 9. Scheduler untuk demo notifikasi

Notifikasi deadline dan pengingat aktivitas dibuat oleh scheduler. Untuk demo:

```bash
php artisan schedule:work
```

Atau jalankan trigger secara manual di terminal lain:

```bash
php artisan notifications:dispatch
php artisan notifications:cleanup
```

## 10. Tiga skenario pengujian utama

Rekam ketiganya (satu video atau tiga klip) dan catat hasil aktual.

### Skenario 1 — Autentikasi dan pembatasan role

| Langkah | Hasil yang diharapkan |
|---|---|
| Login sebagai mahasiswa | Masuk ke `/mahasiswa/dashboard` |
| Buka `/admin/dashboard` di sesi mahasiswa | Ditolak (403/404), bukan menampilkan data admin |
| Logout lalu login sebagai dosen | Masuk ke `/guru-dosen/dashboard` |
| Login sebagai admin | Masuk ke `/admin/dashboard` |

### Skenario 2 — Tugas personal: buat, tampilkan, arsip, riwayat

| Langkah | Hasil yang diharapkan |
|---|---|
| Mahasiswa membuka `/mahasiswa/tasks/personal/create` | Form tampil dengan prioritas default `Sedang` |
| Simpan tugas dengan judul dan deadline | Tugas muncul di `/mahasiswa/tasks` dan dashboard |
| Klik arsip dan konfirmasi | Tugas hilang dari daftar aktif |
| Buka `/mahasiswa/task-history` | Tugas arsip tetap terlihat |

### Skenario 3 — Tugas akademik: distribusi, progres, koreksi, notifikasi

| Langkah | Hasil yang diharapkan |
|---|---|
| Dosen membuat tugas di mata kuliah yang diampu | Progres awal terdistribusi ke mahasiswa terdaftar |
| Mahasiswa membuka tugas dan mengubah status | Status tersimpan, tanggal ubah dan aktor tercatat |
| Dosen membuka `/guru-dosen/academic-tasks/{id}/progress` | Status mahasiswa terlihat; koreksi berhasil |
| Mahasiswa membuka `/notifications` | Notifikasi distribusi tugas tampil, badge berkurang setelah ditandai dibaca |
| Dosen menutup tugas | Mahasiswa tidak dapat mengubah progres; dosen masih dapat koreksi |

## 11. Checklist bukti untuk tugas

- [ ] Repository GitHub berisi source code dan riwayat commit.
- [ ] `.env.example` dan panduan ini disertakan (tanpa kredensial asli).
- [ ] Screenshot/video aplikasi berjalan melalui URL ngrok.
- [ ] Screenshot tiga skenario di atas beserta hasilnya.
- [ ] Laporan PDF: arsitektur, alur deployment, keamanan, backup, monitoring, rollback.
- [ ] Dokumen prompt AI, hasil analisis, dan verifikasi mandiri.

## 12. Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| 419 Page Expired | `APP_URL` tidak sama dengan domain ngrok | Samakan `APP_URL`, lalu `php artisan config:clear` |
| Redirect loop | `SESSION_SECURE_COOKIE=true` tapi diakses via HTTP | Pakai URL https ngrok atau set `false` untuk lokal |
| CSS/JS tidak termuat | Aset belum di-build atau `APP_URL` salah | `npm run build`, cek `public/build` |
| Login berhasil lalu logout sendiri | Cookie `Secure` tidak terkirim karena proxy tidak dipercaya | Isi `TRUSTED_PROXIES`, restart server |
| Link mati saat dinilai | Proses ngrok/server berhenti | Sertakan video sebagai bukti utama; nyalakan tunnel saat demo |

## 13. Backup, monitoring, dan rollback (lokal)

| Aspek | Prosedur demo |
|---|---|
| Backup | `mysqldump -u<user> -p db_manajemen_akademik > backup-$(date +%F).sql` |
| Restore | `mysql -u<user> -p db_manajemen_akademik < backup-YYYY-MM-DD.sql` |
| Monitoring | `tail -f storage/logs/laravel.log`, `php artisan about`, cek `/up` |
| Rollback kode | `git log --oneline` lalu `git checkout <commit>` (tanpa menghapus database) |
| Rollback migrasi | `php artisan migrate:rollback --step=1` setelah backup |

Untuk produksi, prosedur ini harus diganti dengan backup terjadwal, monitoring
eksternal, dan rollback rilis di VM (lihat `docs/10_IMPLEMENTATION_PLAN.md` Phase 9).
