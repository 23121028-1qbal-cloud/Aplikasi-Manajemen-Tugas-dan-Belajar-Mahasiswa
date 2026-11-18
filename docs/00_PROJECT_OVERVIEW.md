# 00 — PROJECT OVERVIEW

| Metadata | Nilai |
|---|---|
| Project | Aplikasi Manajemen Tugas dan Belajar Mahasiswa |
| Document ID | DOC-00 |
| Version | 1.1.0 |
| Status | Draft Baseline — Web Migration |
| Last Updated | 2026-11-17 |
| Platform | Web Application only |
| Application Style | Laravel Full-Stack |
| Frontend | Laravel Blade |
| Database | MySQL (`db_manajemen_akademik`) |

## 1. Identitas dan ringkasan

Aplikasi Manajemen Tugas dan Belajar Mahasiswa adalah aplikasi web untuk membantu mahasiswa mengelola tugas pribadi, menerima dan memperbarui progres tugas akademik, serta merencanakan aktivitas belajar. Guru/Dosen membuat dan mengelola tugas akademik untuk mata kuliah yang diampu serta memantau progres mahasiswa. Admin mengelola akun, pendaftaran mata kuliah, dan melakukan monitoring terbatas.

Aplikasi diakses melalui browser desktop, tablet, maupun mobile. Baseline tidak mencakup aplikasi Android terpisah.

## 2. Latar belakang dan masalah

Mahasiswa perlu mengelola tugas dari berbagai mata kuliah, deadline, prioritas, status penyelesaian, serta jadwal dan materi belajar. Tugas akademik yang dibuat dosen perlu didistribusikan kepada mahasiswa yang terdaftar, dengan progres yang tersimpan secara independen per mahasiswa.

Sistem menyediakan satu aplikasi terpusat dengan pembagian kewenangan Mahasiswa, Guru/Dosen, dan Admin.

## 3. Tujuan produk

| ID | Tujuan | Indikator hasil |
|---|---|---|
| PG-01 | Pengelolaan tugas | Mahasiswa dapat membuat, melihat, memperbarui, dan mengarsipkan tugas personal sesuai aturan. |
| PG-02 | Pengelolaan progres akademik | Progres tugas akademik tersimpan per mahasiswa dan dapat dipantau pihak berwenang. |
| PG-03 | Perencanaan belajar | Mahasiswa dapat mengelola aktivitas belajar dan riwayatnya. |
| PG-04 | Pengelolaan perkuliahan | Data mata kuliah dan enrollment tersedia sesuai kewenangan. |
| PG-05 | Administrasi akun | Admin dapat mengelola akun Mahasiswa dan membuat akun Guru/Dosen. |
| PG-06 | Pengingat | Pengguna menerima notifikasi web sesuai pemicu dan preferensi yang ditetapkan. |
| PG-07 | Keamanan dan privasi | Data dan operasi dibatasi berdasarkan role, ownership, dan relasi sumber daya. |
| PG-08 | Akses web responsif | Fungsi inti dapat digunakan pada browser modern desktop dan mobile. |

## 4. Stakeholder dan role

### 4.1 Mahasiswa
- Mendaftar akun sendiri.
- Login dengan username dan password.
- Mengelola tugas personal miliknya.
- Melihat tugas akademik yang didistribusikan kepadanya.
- Memperbarui progres tugas akademik miliknya sendiri.
- Melihat mata kuliah tempat ia terdaftar.
- Mengelola aktivitas belajar pribadi.
- Membaca notifikasi miliknya.

### 4.2 Guru/Dosen
- Akun dibuat oleh Admin.
- Mengelola mata kuliah yang ditugaskan kepadanya sesuai kebijakan akses.
- Membuat, memperbarui, dan menutup tugas akademik pada mata kuliah yang diampu.
- Tugas akademik diberikan kepada seluruh mahasiswa yang memenuhi syarat enrollment.
- Memantau progres mahasiswa pada mata kuliah yang diampu.
- Dapat melakukan koreksi status progres sesuai kewenangan yang disetujui; identitas pelaku perubahan dicatat.

### 4.3 Admin
- Akun Admin disiapkan saat deployment melalui proses operasional yang terlindungi.
- Mengelola akun mahasiswa.
- Membuat dan mengelola akun Guru/Dosen.
- Mengelola data mata kuliah dalam cakupan administrasi.
- Mengelola enrollment mahasiswa ke mata kuliah.
- Monitoring definisi tugas akademik dan progres mahasiswa secara read-only.
- Tidak memiliki akses bebas terhadap isi tugas personal atau aktivitas belajar pribadi.
- Tidak boleh mengubah progres akademik mahasiswa melalui fungsi monitoring.

## 5. Baseline platform dan teknologi

| Area | Keputusan |
|---|---|
| Platform | Web Application saja (`USER-DECIDED`) |
| Frontend | Laravel Blade (`USER-DECIDED`) |
| Application architecture | Laravel Full-Stack satu aplikasi (`USER-DECIDED`) |
| Authentication | Laravel Session Authentication (`USER-DECIDED`) |
| Login | Username + password (`USER-DECIDED`) |
| Password recovery | Admin memberikan temporary password (`USER-DECIDED`) |
| Connectivity | Online-first; koneksi diperlukan (`USER-DECIDED`) |
| Notification | In-app web notification + Laravel Scheduler (`USER-DECIDED`) |
| Navigation | Sidebar desktop + navigasi ringkas mobile (`USER-DECIDED`) |
| Theme | Light dan Dark (`USER-DECIDED`) |
| CSS | Bootstrap (`USER-DECIDED`) |
| API | Web Routes dahulu; JSON API bila kebutuhan spesifik muncul (`USER-DECIDED`) |
| Concurrency | Ditinjau berdasarkan jenis data (`USER-DECIDED`) |
| Browser | Chrome, Firefox, Safari modern (`USER-DECIDED`) |
| Deployment | Satu VM OCI untuk Nginx, Laravel, dan MySQL (`USER-DECIDED`) |
| Database | MySQL; nama database `db_manajemen_akademik` (baseline terdahulu) |
| Server language | PHP melalui Laravel |
| Runtime versions | `TBD` pada tahap implementasi |

## 6. Aturan bisnis yang dipertahankan

1. Terdapat tiga role: Mahasiswa, Guru/Dosen, Admin.
2. Mahasiswa mendaftar sendiri; Admin membuat akun Guru/Dosen.
3. Satu mata kuliah memiliki tepat satu Guru/Dosen pengampu; satu Guru/Dosen dapat mengampu beberapa mata kuliah.
4. Enrollment Mahasiswa–Mata Kuliah adalah many-to-many.
5. Tugas personal dapat dikaitkan dengan mata kuliah secara opsional.
6. Tugas akademik wajib terkait mata kuliah yang dikelola Guru/Dosen pembuatnya.
7. Aktivitas belajar dapat dikaitkan dengan mata kuliah secara opsional.
8. Tugas akademik didistribusikan kepada seluruh mahasiswa dengan enrollment `Terdaftar` pada saat pembuatan.
9. Mahasiswa yang mendaftar kemudian menerima progres untuk tugas yang masih terbuka dan belum diarsipkan.
10. Distribusi idempotent: tidak membuat duplikasi dan tidak mereset progres yang telah ada.
11. Progres awal adalah `Belum Dikerjakan`.
12. Deadline tugas akademik bersifat informasi; kelayakan distribusi ditentukan oleh `is_closed`, bukan semata deadline.
13. Keluar dari mata kuliah tidak menghapus riwayat progres.
14. Tugas yang selesai tetap menjadi riwayat, bukan dihapus otomatis.
15. Tugas akademik memiliki definisi terpusat dan tabel progres individual.
16. Status tugas hanya `Belum Dikerjakan`, `Sedang Dikerjakan`, `Selesai`.
17. Status aktivitas belajar hanya `Direncanakan`, `Sedang Berlangsung`, `Selesai`.
18. Prioritas hanya `Rendah`, `Sedang`, `Tinggi`.
19. Arsip dilakukan secara soft archive; data historis dan relasi penting dipertahankan.
20. Admin tidak memperoleh akses ke isi data personal hanya karena memiliki role Admin.

## 7. Ruang lingkup MVP

### In scope
- Registrasi Mahasiswa.
- Login/logout dan session management.
- Manajemen akun oleh Admin.
- Manajemen mata kuliah dan enrollment sesuai kewenangan.
- Tugas personal.
- Tugas akademik dan distribusi progres.
- Monitoring progres.
- Aktivitas belajar personal.
- Riwayat dan arsip.
- Notifikasi dalam aplikasi web.
- Preferensi Light/Dark.
- Responsive UI.

### Out of scope
- Android native app, Kotlin, Jetpack Compose.
- Room, WorkManager, offline-first, background device sync.
- Sanctum personal access token untuk client mobile.
- FCM, browser push, dan email notification pada MVP kecuali disetujui kemudian.
- Integrasi LMS/SIAKAD eksternal.
- Penilaian, pengumpulan berkas tugas, rubrik nilai, persentase progres, checklist subtask, dan gamifikasi.
- AI features.
- Multi-tenant SaaS.
- Microservices, Kubernetes, dan pemisahan frontend/backend.

## 8. Deployment overview

Target adalah satu VM OCI. Rekomendasi awal sebelumnya adalah VM ARM Ampere A1 dengan 2 OCPU dan 12 GB RAM serta Ubuntu Server ARM64, tetapi ketersediaan kapasitas, kuota, dan parameter final harus diverifikasi saat provisioning. Nginx, PHP-FPM/Laravel, scheduler/queue worker bila digunakan, dan MySQL berjalan pada VM yang sama. HTTPS wajib untuk deployment publik.

Backup database harus disimpan di luar VM. MySQL tidak boleh dibuka langsung ke internet. Detail backup retention, RPO/RTO, firewall, dan monitoring masih `TBD`.

## 9. Dokumen turunan

Lihat `README.md` untuk daftar dan urutan dokumen. PRD menjadi sumber kebutuhan produk; SRS menurunkan kebutuhan yang dapat diuji; DB dan arsitektur menjabarkan implementasi; Testing Strategy mengikat persyaratan ke test case.

## 10. Keputusan terbuka

| ID | Topik | Status |
|---|---|---|
| TBD-PO-01 | Kebijakan transfer ownership tugas personal | OPEN |
| TBD-PO-02 | Waktu pengingat dan preferensi notifikasi | OPEN |
| TBD-PO-03 | Retensi notifikasi dan audit log | OPEN |
| TBD-PO-04 | Target performa terukur dan kapasitas | OPEN |
| TBD-PO-05 | Versi runtime/framework dan browser support floor | OPEN |
| TBD-PO-06 | Parameter final VM OCI, backup, RPO/RTO | OPEN |
| TBD-PO-07 | Aturan optimistic locking per entitas | OPEN |
| TBD-PO-08 | Finalisasi skema akun, profile lifecycle, dan DDL | OPEN |

## 11. Change control

Perubahan pada keputusan `USER-DECIDED` memerlukan persetujuan pemilik proyek. Dokumen berstatus Draft sampai seluruh keputusan yang memblokir implementasi ditutup. Implementasi tidak boleh menganggap rekomendasi atau TBD sebagai keputusan final.
