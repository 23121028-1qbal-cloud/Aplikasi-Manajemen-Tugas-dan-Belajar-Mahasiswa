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
    └── 10_IMPLEMENTATION_PLAN.md
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

## Keputusan terbuka penting

- Detail kebijakan pemilik/pengalihan tugas personal (`PRD-TBD-017`).
- Kebijakan transfer ownership tugas personal (`PRD-TBD-017`).
- Nilai target performa dan kapasitas (`PRD-TBD-020`).
- Session lifetime dan kebijakan concurrent session (`PRD-TBD-019`).
- Versi Nginx dan Ubuntu Server; versi aplikasi sudah ditetapkan pada REC-15.
- Parameter final VM OCI dan ketersediaan kuota di region.
- Finalisasi matriks concurrency per entitas/operasi.
- Retensi audit log dan finalisasi ERD/DDL pada CP2.

## Status rekonsiliasi (2026-11-18)

Audit repository menemukan konflik dan celah dokumentasi. Rekonsiliasi `REC-01` s.d. `REC-15` dicatat di `docs/06_DATABASE_DESIGN.md` §12 (database), `docs/08_API_SPECIFICATION.md` §10 (route), dan `docs/09_TESTING_STRATEGY.md` §13 (testing).

**Disetujui 2026-11-18 (`USER-DECIDED`):** seluruh REC-01 s.d. REC-15, `PRD-TBD-016`, `PRD-TBD-022`, `PRD-TBD-023`, dan `PRD-TBD-024`.

**Blocker Phase 4 dan Phase 7:** tidak ada lagi setelah keputusan 2026-11-19. Yang masih terbuka adalah keputusan non-blocker seperti session lifetime, target performa, dan parameter deployment. Implementasi tetap menunggu approval gate.

## Rekonsiliasi dan keputusan 2026-11-19

Rekonsiliasi ini menyelaraskan status yang sudah diputuskan dan menutup keputusan yang sebelumnya terbuka.

- Status REC-15 dan seluruh REC-01 s.d. REC-15 diseragamkan menjadi `USER-DECIDED`.
- Sisa referensi `aktivitas_belajar.id_user` pada desain diselaraskan ke `id_mahasiswa` sesuai REC-02.
- Temporary password 72 jam dan wajib ganti tidak lagi dicantumkan sebagai keputusan terbuka.
- **Policy/Gate layer wajib** untuk otorisasi resource (`ARCH-011`, `SRS-AUTH-015`). Policy untuk seluruh resource utama sudah diimplementasikan dan di-wire ke controller.
- **Arsip + restore akun** (`PRD-TBD-025`): arsip menonaktifkan akun; dosen hanya dapat diarsipkan tanpa mata kuliah non-arsip; restore tidak membackfill progres.
- **Cakupan notifikasi MVP** (`PRD-TBD-018`): distribusi tugas akademik, deadline akademik H-1, dan aktivitas belajar 60 menit sebelum mulai; retensi 90 hari; tanpa preferensi; scheduler 15 menit.
- **Progres pasca-close** (`PRD-TBD-021`): mahasiswa terkunci; dosen pengampu boleh koreksi dengan audit; tugas berarsip terkunci untuk semua.
- Gap closure 2026-11-19 menyelesaikan Policy/Gate, arsip/restore akun, post-close lock, halaman appearance, rekap progres mata kuliah (`GD-07`), dashboard per role, default prioritas `Sedang`, dan reaktivasi `Ditolak`.
- Phase 7 sudah diimplementasikan: notifikasi in-app, dedup per penerima, scheduler 15 menit, cleanup retensi 90 hari, inbox, dan badge belum dibaca.
- Pengujian Phase 7 mencakup SQLite, MySQL 8.4.13, Playwright browser/E2E, dan axe WCAG A/AA.
- Phase 8 (UI polish and compatibility) sudah diimplementasikan: tema Light/Dark, sidebar offcanvas responsif, dan perbaikan kontras pada seluruh halaman peran.
- Audit axe WCAG A/AA per halaman (Light/Dark, desktop 1280px dan mobile 390px) tidak menemukan violation; smoke test kompatibilitas lulus di Chromium dan Firefox, sedangkan WebKit belum dapat dijalankan karena library host belum tersedia.
- Optimasi query: dashboard mahasiswa kini eager-load `mataKuliah` (14 menjadi 13 query pada data audit); 134 test lulus pada SQLite dan MySQL 8.4.13.

## Riwayat versi dokumentasi

| Versi | Tanggal | Status | Ringkasan |
|---|---|---|---|
| 1.0.0 | 2026-11-14 s.d. 2026-11-16 | Draft historis | Baseline dokumen berorientasi Android + Laravel API. |
| 1.1.0 | 2026-11-17 | Draft migrasi web | Mengganti target menjadi Web Application, Laravel Blade, session auth, online-first, Bootstrap, Web Routes, dan deployment satu VM OCI. |
| 1.1.1 | 2026-11-18 | Draft + rekonsiliasi | Menambahkan catatan rekonsiliasi `PROPOSED` (REC-01 s.d. REC-15), open decision baru (PRD-TBD-022 s.d. 024), dan status pelaksanaan Phase 0/2. Tidak mengubah keputusan baseline. |
| 1.2.0 | 2026-11-18 | Draft + keputusan | Menyetujui REC-01/02/03/05/06/07/08/09/10/12/13/14/15 dan PRD-TBD-024. REC-04, REC-11, dan PRD-TBD-016 tetap OPEN. |
| 1.3.0 | 2026-11-18 | Draft + keputusan | Menyetujui REC-04 (transisi enrollment), REC-11 (`nama_lengkap` saja), dan PRD-TBD-016 (temporary password 72 jam + wajib ganti). |
| 1.4.0 | 2026-11-18 | Draft + keputusan | Menambahkan optimistic locking `row_version` pada tugas, aktivitas belajar, dan mata kuliah, serta kewenangan dosen mengubah nama mata kuliah yang diampu. |
| 1.5.0 | 2026-11-19 | Implementasi Phase 6 | Menambahkan tugas akademik, distribusi progres idempotent, pembaruan progres mahasiswa, koreksi dosen, monitoring Admin read-only, dan `row_version` pada progres akademik. |
| 1.6.0 | 2026-11-19 | Rekonsiliasi dokumen | Menyelaraskan status keputusan yang sudah disetujui, mencatat kewajiban Policy/Gate layer, dan menandai gap implementasi Phase 7. |
| 1.7.0 | 2026-11-19 | Penutupan keputusan | Menyetujui arsip/restore, cakupan notifikasi MVP, progres pasca-close, dan kewajiban Policy/Gate layer. |
| 1.8.0 | 2026-11-19 | Gap closure | Mengimplementasikan Policy/Gate, arsip/restore, post-close lock, appearance, GD-07, dashboard per role, default prioritas, dan guard reaktivasi `Ditolak`; 122 test lulus. |
| 1.9.0 | 2026-11-19 | Phase 7 | Menambahkan notifikasi in-app, trigger distribusi/deadline/aktivitas belajar, dedup `(id_user, dedup_key)`, scheduler 15 menit, cleanup 90 hari, inbox, dan badge; 132 test lulus pada SQLite dan MySQL 8.4.13. |
| 1.10.0 | 2026-11-19 | Phase 8 | Poles UI: kontras Light/Dark, tabel dan tombol outline, label input disabled, filter mobile, perbaikan overflow halaman progres, bug `orWhereKey` pada form edit mata kuliah, serta eager-load `mataKuliah` di dashboard. Audit axe per halaman 0 violation; smoke test Chromium dan Firefox lulus; 134 test lulus. |

Dokumen-dokumen ini belum merupakan bukti bahwa aplikasi telah diimplementasikan atau dideploy.
