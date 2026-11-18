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

Dokumen ini merupakan **Draft Baseline v1.3.0**. Keputusan migrasi platform dan seluruh rekonsiliasi REC-01 s.d. REC-15 telah disetujui pada 2026-11-18, termasuk REC-04, REC-11, dan `PRD-TBD-016`. Beberapa rincian produk, database, keamanan, dan operasional tetap ditandai `TBD` atau `OPEN`. Status tersebut tidak boleh dianggap sebagai persetujuan otomatis.

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

- Detail kebijakan pemilik/pengalihan tugas personal.
- Detail waktu dan preferensi pengingat notifikasi.
- Nilai target performa dan kapasitas.
- Versi Laravel, PHP, MySQL, Bootstrap, dan Nginx yang akan dipakai saat implementasi.
- Parameter final VM OCI dan ketersediaan kuota di region.
- Detail concurrency per entitas, terutama operasi yang berpotensi saling menimpa.
- Retensi notifikasi dan audit log.
- Finalisasi ERD/DDL setelah seluruh TBD database ditutup.
- Transisi status enrollment (`Ditolak`/`Selesai`) dan field akun yang boleh diubah Admin (`PRD-TBD-022`, `PRD-TBD-023`).
- Nullability `deadline` tugas personal (`PRD-TBD-024`).

## Status rekonsiliasi (2026-11-18)

Audit repository menemukan konflik dan celah dokumentasi. Rekonsiliasi `REC-01` s.d. `REC-15` dicatat di `docs/06_DATABASE_DESIGN.md` §12 (database), `docs/08_API_SPECIFICATION.md` §10 (route), dan `docs/09_TESTING_STRATEGY.md` §13 (testing).

**Disetujui 2026-11-18 (`USER-DECIDED`):** seluruh REC-01 s.d. REC-15, `PRD-TBD-016`, `PRD-TBD-022`, `PRD-TBD-023`, dan `PRD-TBD-024`.

**Blocker Phase 4:** tidak ada lagi. Yang masih terbuka adalah keputusan non-blocker seperti waktu notifikasi, session lifetime, target performa, dan parameter deployment.

## Riwayat versi dokumentasi

| Versi | Tanggal | Status | Ringkasan |
|---|---|---|---|
| 1.0.0 | 2026-11-14 s.d. 2026-11-16 | Draft historis | Baseline dokumen berorientasi Android + Laravel API. |
| 1.1.0 | 2026-11-17 | Draft migrasi web | Mengganti target menjadi Web Application, Laravel Blade, session auth, online-first, Bootstrap, Web Routes, dan deployment satu VM OCI. |
| 1.1.1 | 2026-11-18 | Draft + rekonsiliasi | Menambahkan catatan rekonsiliasi `PROPOSED` (REC-01 s.d. REC-15), open decision baru (PRD-TBD-022 s.d. 024), dan status pelaksanaan Phase 0/2. Tidak mengubah keputusan baseline. |
| 1.2.0 | 2026-11-18 | Draft + keputusan | Menyetujui REC-01/02/03/05/06/07/08/09/10/12/13/14/15 dan PRD-TBD-024. REC-04, REC-11, dan PRD-TBD-016 tetap OPEN. |
| 1.3.0 | 2026-11-18 | Draft + keputusan | Menyetujui REC-04 (transisi enrollment), REC-11 (`nama_lengkap` saja), dan PRD-TBD-016 (temporary password 72 jam + wajib ganti). |

Dokumen-dokumen ini belum merupakan bukti bahwa aplikasi telah diimplementasikan atau dideploy.
