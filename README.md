# Aplikasi Manajemen Tugas dan Belajar Mahasiswa

Dokumentasi baseline proyek **Web Application** berbasis Laravel Full-Stack.

## Status baseline

- Platform: Web Application saja.
- Frontend: Laravel Blade.
- Arsitektur: Laravel Full-Stack dalam satu aplikasi.
- Database: MySQL.
- CSS framework: Bootstrap.
- Authentication: Laravel Session Authentication.
- Akses: online-first; koneksi internet diperlukan.
- Notifikasi: notifikasi dalam aplikasi web dan Laravel Scheduler.
- Deployment target: satu VM Oracle Cloud Infrastructure (OCI) untuk Nginx, Laravel, dan MySQL.

Dokumen ini merupakan **Draft Baseline v1.10.0**. Keputusan migrasi platform dan seluruh rekonsiliasi REC-01 s.d. REC-15 telah disetujui pada 2026-11-18, termasuk REC-04, REC-11, dan `PRD-TBD-016`. Pada 2026-11-19 pemilik proyek menetapkan **Policy/Gate layer wajib** untuk otorisasi resource, alur **arsip + restore** akun, cakupan notifikasi MVP, dan kebijakan progres setelah tugas akademik ditutup. Beberapa rincian operasional tetap ditandai `TBD` atau `OPEN`. Status tersebut tidak boleh dianggap sebagai persetujuan otomatis.

## Struktur dokumentasi

```text
project/
├── README.md
└── docs/
    ├── 00_PROJECT_OVERVIEW.md
    ├── 01_PRD.md
    ├── 02_SRS.md
    ├── 03_UI_UX_SPECIFICATION.md
    ├── 04_DESIGN_SYSTEM.md
    ├── 05_COMPONENT_LIBRARY.md
    ├── 06_DATABASE_DESIGN.md
    ├── 07_SYSTEM_ARCHITECTURE.md
    ├── 08_API_SPECIFICATION.md
    ├── 09_TESTING_STRATEGY.md
    ├── 10_IMPLEMENTATION_PLAN.md
    ├── 11_DEMO_DEPLOYMENT.md
    ├── 12_AI_PROMPT_LOG.md
    ├── 13_TECHNICAL_REPORT.md
    └── 14_SCREENSHOT_CHECKLIST.md
```

## Urutan membaca

1. `00_PROJECT_OVERVIEW.md` — konteks, tujuan, ruang lingkup, dan keputusan.
2. `01_PRD.md` — kebutuhan produk dan aturan bisnis.
3. `02_SRS.md` — kebutuhan sistem yang dapat diuji.
4. `03_UI_UX_SPECIFICATION.md` — halaman, navigasi, alur, dan interaksi.
5. `04_DESIGN_SYSTEM.md` — token dan aturan visual.
6. `05_COMPONENT_LIBRARY.md` — komponen antarmuka.
7. `06_DATABASE_DESIGN.md` — entitas, relasi, constraint, dan indeks.
8. `07_SYSTEM_ARCHITECTURE.md` — rancangan teknis aplikasi dan deployment.
9. `08_API_SPECIFICATION.md` — kontrak Web Routes dan JSON endpoints terbatas.
10. `09_TESTING_STRATEGY.md` — strategi verifikasi.
11. `10_IMPLEMENTATION_PLAN.md` — urutan pekerjaan dan gerbang persetujuan.
12. `11_DEMO_DEPLOYMENT.md` — cara menjalankan aplikasi dan mengeksposnya lewat ngrok untuk demo.
13. `12_AI_PROMPT_LOG.md` — log prompt AI, hasil analisis, dan verifikasi mandiri.
14. `13_TECHNICAL_REPORT.md` — laporan teknis (siap konversi ke PDF).
15. `14_SCREENSHOT_CHECKLIST.md` — daftar screenshot bukti tugas.

## Menjalankan aplikasi (demo)

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install && npm run build
php artisan migrate && php artisan db:seed
php artisan app:create-admin admin
php artisan serve --host=127.0.0.1 --port=8000
```

Untuk akses publik dengan HTTPS, jalankan `ngrok http 8000`, lalu samakan `APP_URL`
dengan domain ngrok dan isi `TRUSTED_PROXIES`. Langkah lengkap, verifikasi HTTPS,
serta tiga skenario pengujian ada di `docs/11_DEMO_DEPLOYMENT.md`.

## Konvensi status

| Status | Makna |
|---|---|
| `USER-DECIDED` | Dipilih atau disetujui secara eksplisit oleh pemilik proyek. |
| `SOURCE-DEFINED` | Berasal dari dokumen tugas awal. |
| `PROPOSED` | Rekomendasi rancangan; belum menjadi keputusan final kecuali disetujui. |
| `TBD` | Masih membutuhkan keputusan atau nilai yang belum tersedia. |
| `DEPENDENT` | Mengikuti keputusan pada dokumen lain. |
| `OUT-OF-SCOPE` | Tidak termasuk baseline MVP. |

## Prinsip tata kelola

- Aturan bisnis yang telah disetujui dipertahankan selama migrasi platform.
- Teknologi Android, Kotlin, Jetpack Compose, Room, WorkManager, Android notification manager, token Sanctum, dan sinkronisasi offline bukan bagian dari baseline web.
- Jangan membuat fitur, endpoint, kolom, status, atau izin baru tanpa kebutuhan dan persetujuan.
- Validasi dan otorisasi wajib dilakukan di server; menyembunyikan tombol bukan kontrol keamanan.
- Perubahan harus ditelusuri dari PRD → SRS → UI/UX/DB/Architecture → Test.
- Dokumen tidak menyatakan implementasi telah tersedia. Dokumen menjelaskan target rancangan.

## Gambaran proyek

Aplikasi Manajemen Tugas dan Belajar Mahasiswa adalah aplikasi web berbasis Laravel Full-Stack untuk membantu mahasiswa, dosen/guru, dan administrator mengelola tugas akademik, mata kuliah, progres belajar, notifikasi, serta aktivitas pembelajaran dalam satu platform.

## Peran pengguna

- **Mahasiswa** — melihat tugas dan mata kuliah, memperbarui progres, mencatat aktivitas belajar, serta menerima notifikasi.
- **Dosen/Guru** — mengelola mata kuliah, membuat dan mendistribusikan tugas, memantau progres, serta melakukan koreksi pasca-tutup sesuai aturan.
- **Administrator** — mengelola akun dan enrollment, memantau data secara read-only, serta melakukan arsip dan restore akun.

## Fitur utama

- Autentikasi berbasis session dengan peran Mahasiswa, Dosen/Guru, dan Admin.
- Manajemen mata kuliah, enrollment, tugas personal, dan tugas akademik.
- Distribusi tugas akademik serta pembaruan progres mahasiswa yang idempotent.
- Monitoring progres dosen dan rekap progres mata kuliah.
- Aktivitas belajar mahasiswa beserta pengingat jadwal.
- Notifikasi in-app untuk distribusi tugas, deadline H-1, dan aktivitas belajar.
- Scheduler 15 menit, dedup notifikasi, cleanup retensi 90 hari, inbox, dan badge belum dibaca.
- Arsip dan restore akun dengan guard aturan bisnis.
- Optimistic locking pada resource yang berisiko konflik pembaruan.
- Tema Light/Dark dan tampilan responsif untuk desktop serta mobile.

## Teknologi

- **Backend:** PHP 8.2+ dan Laravel 12
- **Frontend:** Laravel Blade, Bootstrap 5, Bootstrap Icons, dan Vite
- **Database:** MySQL untuk production; SQLite didukung untuk pengujian
- **Pengujian:** PHPUnit, Playwright, dan pemeriksaan aksesibilitas axe WCAG A/AA
- **Deployment target:** Nginx dan MySQL pada satu VM Oracle Cloud Infrastructure

## Status implementasi

Baseline dokumentasi saat ini berada pada **versi 1.10.0**. Fase implementasi sampai Phase 8 telah dicatat dalam dokumentasi, termasuk Policy/Gate, arsip/restore akun, post-close lock, notifikasi in-app, appearance, dan dashboard per peran.

> Status implementasi pada dokumentasi tidak menggantikan verifikasi langsung terhadap source code dan proses deployment.

## Pengujian

Berdasarkan audit proyek:

- **134 pengujian** lulus pada SQLite dan MySQL 8.4.13.
- Pengujian browser/E2E mencakup Playwright.
- Pemeriksaan axe WCAG A/AA dilaporkan tanpa violation pada halaman yang diaudit.
- Smoke test lulus pada Chromium dan Firefox.

Jalankan pengujian dengan:

```bash
php artisan test
npm run build
```


