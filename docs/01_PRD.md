# 01 — PRODUCT REQUIREMENTS DOCUMENT (PRD)

| Metadata | Nilai |
|---|---|
| Document ID | DOC-01 |
| Version | 1.3.0 |
| Status | Draft Baseline — Web Migration |
| Platform | Web Application |
| Product | Aplikasi Manajemen Tugas dan Belajar Mahasiswa |

## 1. Tujuan dokumen

PRD mendefinisikan masalah, tujuan, pengguna, ruang lingkup, kemampuan produk, aturan bisnis, dan batasan dari sudut pandang pengguna. PRD menjadi sumber utama kebutuhan produk untuk `02_SRS.md`.

Status asal kebutuhan:
- `SOURCE-DEFINED`: berasal dari tugas awal.
- `USER-DECIDED`: disetujui pemilik proyek.
- `PROPOSED`: rekomendasi yang belum final.
- `TBD`: belum diputuskan.

## 2. Deskripsi produk

Aplikasi web terpusat yang membantu mahasiswa mengelola tugas dan aktivitas belajar, Guru/Dosen mengelola tugas akademik dan memantau progres, serta Admin mengelola akun, enrollment, dan monitoring terbatas.

Aplikasi menggunakan Laravel Blade dan membutuhkan koneksi internet. Server menyimpan data terpusat di MySQL. Tidak ada local-first mode atau sinkronisasi offline pada baseline ini.

## 3. Tujuan dan keberhasilan

| ID | Goal | Indikator |
|---|---|---|
| PG-01 | Mengelola tugas personal | Mahasiswa dapat mengelola tugas miliknya sesuai aturan. |
| PG-02 | Menyampaikan tugas akademik | Semua mahasiswa eligible menerima satu progres per tugas. |
| PG-03 | Melacak progres | Mahasiswa memperbarui progres sendiri; dosen memantau. |
| PG-04 | Merencanakan belajar | Mahasiswa dapat mengelola aktivitas belajar pribadi. |
| PG-05 | Mengelola struktur akademik | Mata kuliah dan enrollment dikelola role berwenang. |
| PG-06 | Mengingatkan pengguna | Notifikasi web disimpan dan ditampilkan sesuai aturan. |
| PG-07 | Menjaga privasi | Data personal tidak terbuka ke role lain tanpa kewenangan. |
| PG-08 | Menyediakan akses web responsif | Fungsi inti dapat digunakan pada browser modern desktop/mobile. |

Target angka performa dan ketersediaan belum ditetapkan (`TBD`); jangan mencantumkan SLA numerik sebelum disetujui.

## 4. Persona dan kebutuhan pengguna

### 4.1 Mahasiswa
Mahasiswa membutuhkan satu tempat untuk:
- mencatat tugas personal;
- melihat tugas akademik dari mata kuliah yang diikuti;
- memperbarui progres akademik miliknya;
- mengelola jadwal, materi, durasi, dan status aktivitas belajar;
- melihat riwayat dan notifikasi.

### 4.2 Guru/Dosen
Guru/Dosen membutuhkan:
- daftar mata kuliah yang diampu;
- pembuatan tugas akademik;
- distribusi tugas ke seluruh mahasiswa eligible;
- penutupan tugas;
- monitoring progres per mahasiswa;
- koreksi progres pada ruang lingkup yang diampu, dengan pencatatan aktor perubahan.

### 4.3 Admin
Admin membutuhkan:
- pengelolaan akun Mahasiswa;
- pembuatan dan pengelolaan akun Guru/Dosen;
- pengelolaan enrollment;
- pengelolaan data mata kuliah sesuai kewenangan;
- monitoring akademik read-only;
- pemberian temporary password.

Admin bukan superuser untuk konten pribadi mahasiswa.

## 5. Matriks kewenangan produk

| Kemampuan | Mahasiswa | Guru/Dosen | Admin |
|---|---|---|---|
| Registrasi Mahasiswa | Ya, untuk diri sendiri | Tidak | Tidak |
| Login | Ya | Ya | Ya |
| Kelola akun Mahasiswa | Tidak | Tidak | Ya |
| Buat akun Guru/Dosen | Tidak | Tidak | Ya |
| Kelola tugas personal | Milik sendiri | Tidak | Tidak |
| Lihat isi tugas personal | Milik sendiri | Tidak | Tidak |
| Kelola aktivitas belajar | Milik sendiri | Tidak | Tidak |
| Lihat isi aktivitas belajar | Milik sendiri | Tidak | Tidak |
| Lihat mata kuliah | Enrollment sendiri | Mata kuliah yang diampu | Semua sesuai fungsi administrasi |
| Kelola mata kuliah | Tidak | Yang ditugaskan | Semua |
| Kelola enrollment | Tidak | Tidak, kecuali keputusan terpisah | Ya |
| Buat/edit/tutup tugas akademik | Tidak | Mata kuliah yang diampu | Tidak |
| Update progres akademik | Progres sendiri | Koreksi pada mata kuliah yang diampu | Tidak |
| Monitoring progres akademik | Progres sendiri | Mata kuliah yang diampu | Read-only sesuai cakupan |
| Baca notifikasi sendiri | Ya | Ya | Ya |
| Baca notifikasi pengguna lain | Tidak | Tidak | Tidak |

Catatan: matriks ini mengikuti keputusan bisnis yang sudah disetujui. Jika hak dosen untuk mengubah assignment dosen pengampu perlu dipisahkan dari pengelolaan detail mata kuliah, detail tersebut harus ditinjau sebagai keputusan tersendiri; jangan memberi akses lintas mata kuliah.

## 6. Modul dan kebutuhan produk

### 6.1 Authentication dan account
- Mahasiswa mendaftar sendiri.
- Guru/Dosen dibuat oleh Admin.
- Login menggunakan username + password.
- Autentikasi menggunakan Laravel session.
- Password disimpan dalam bentuk hash menggunakan fasilitas hashing Laravel.
- Pemulihan dilakukan dengan temporary password dari Admin.
- Penggunaan aplikasi membutuhkan koneksi internet.
- Akun nonaktif tidak boleh memperoleh sesi akses baru.
- Logout mengakhiri sesi pada server.
- Akun Admin awal dibuat melalui proses deployment/Artisan/Seeder yang terlindungi; tidak ada public Admin registration.

Keputusan 2026-11-18 (`USER-DECIDED`, `PRD-TBD-016`): temporary password berlaku **72 jam**, wajib diganti pada login pertama, dan seluruh route selain ubah password/logout diblokir sampai password diganti. Admin yang menerbitkan ulang temporary password mereset expiry dan flag wajib ganti. Session lifetime tetap `TBD`.

### 6.2 Tugas personal
- Mahasiswa dapat membuat, melihat, mengubah, dan mengarsipkan tugas personal miliknya.
- Mata kuliah opsional.
- Data mencakup judul, deskripsi, deadline, prioritas, status, dan timestamp.
- Prioritas: Rendah, Sedang, Tinggi.
- Status: Belum Dikerjakan, Sedang Dikerjakan, Selesai.
- Tugas Selesai masuk riwayat dan tidak otomatis dihapus.
- Akses ke data dibatasi berdasarkan pemilik.
- Kebijakan transfer ownership: `TBD`; jangan menyediakan fitur transfer sebelum diputuskan.

### 6.3 Tugas akademik
- Hanya Guru/Dosen dapat membuat definisi tugas akademik.
- Tugas harus terhubung ke mata kuliah yang dikelola pembuat.
- Satu definisi tugas disimpan satu kali.
- Semua mahasiswa dengan enrollment aktif/eligible menerima satu baris progres.
- Progres setiap mahasiswa independen.
- Status awal `Belum Dikerjakan`.
- Mahasiswa hanya mengubah progres miliknya sendiri.
- Guru/Dosen dapat memantau progres dan melakukan koreksi dalam mata kuliah yang diampu sesuai aturan.
- Admin hanya monitoring read-only.
- Deadline bersifat informasi, bukan syarat distribusi.
- Tugas dianggap eligible untuk distribusi susulan jika belum ditutup (`is_closed = false`) dan belum diarsipkan.
- Menutup tugas menghentikan distribusi progres baru; perilaku perubahan progres setelah penutupan harus mengikuti keputusan final yang belum ditetapkan jika ada kebutuhan khusus.
- Distribusi harus idempotent dan tidak boleh membuat ulang atau mereset progress.

### 6.4 Aktivitas belajar
- Mahasiswa mengelola aktivitas belajar pribadi.
- Mata kuliah opsional.
- Data: judul, materi, waktu mulai, durasi menit, status, tanggal dibuat.
- Durasi harus lebih besar dari nol.
- Status: Direncanakan, Sedang Berlangsung, Selesai.
- Aktivitas selesai tetap dapat dilihat di riwayat.
- Guru/Dosen dan Admin tidak memiliki akses isi aktivitas pribadi.

### 6.5 Mata kuliah dan enrollment
- Satu mata kuliah memiliki tepat satu Guru/Dosen pengampu.
- Satu Guru/Dosen dapat mengampu banyak mata kuliah.
- Relasi mahasiswa–mata kuliah many-to-many.
- Admin mengelola enrollment.
- Data enrollment menyimpan mahasiswa, mata kuliah, tahun akademik, semester, tanggal daftar, dan status.
- Unique key periode: mahasiswa + mata kuliah + tahun akademik + semester.
- Status enrollment: Terdaftar, Ditolak, Dibatalkan, Selesai.
- Hanya status `Terdaftar` yang eligible menerima distribusi tugas baru.
- Enrollment yang dibatalkan/selesai tidak menghapus progress historis.
- Re-enrollment pada periode yang sama mengaktifkan kembali baris yang sama; periode akademik berbeda membuat baris baru.
- Arsip mata kuliah tidak menghapus tugas/progress.

Keputusan 2026-11-18:
- Enrollment yang dibuat Admin selalu berstatus `Terdaftar`. Admin dapat mengubah `Terdaftar` → `Dibatalkan` atau `Terdaftar` → `Selesai`. `Selesai`/`Dibatalkan` dapat diaktifkan kembali menjadi `Terdaftar` melalui aksi enrollment periode yang sama. `Ditolak` hanya berlaku untuk alur pengajuan mahasiswa yang tidak termasuk MVP sehingga tidak ditawarkan di UI. Lihat REC-04/`PRD-TBD-022`.
- `tanggal_daftar` tidak ditimpa saat reaktivasi; waktu reaktivasi tercatat pada `updated_at`. Lihat REC-05.
- Field akun yang boleh diubah Admin: `nama_lengkap` saja. `username`, `npm`, dan `nip_guru` tidak dapat diubah pada MVP karena menjadi identifier login dan riwayat. Status akun diatur lewat aksi terpisah. Lihat REC-11/`PRD-TBD-023`.

### 6.6 Notifications
- Notifikasi disimpan di MySQL.
- Notifikasi ditampilkan pada web application.
- Laravel Scheduler menjalankan pekerjaan terjadwal.
- Pemicu minimal yang telah disetujui: pengingat deadline tugas dan jadwal aktivitas belajar; penugasan tugas akademik dapat menghasilkan notifikasi penerima.
- Deduplication wajib mencegah notifikasi ganda untuk satu event/penerima.
- Penerima hanya dapat membaca dan menandai notifikasi miliknya.
- Admin tidak dapat membaca isi notifikasi pengguna lain.
- Waktu pengingat, timezone policy, retensi, dan detail preferensi: `TBD`.
- Email dan browser push bukan bagian MVP.

## 7. Product journeys

### 7.1 Student registration and login
1. Pengunjung membuka halaman login.
2. Pengunjung memilih pendaftaran Mahasiswa.
3. Mengisi data registrasi yang diwajibkan.
4. Server memvalidasi data dan membuat akun/profile.
5. Pengguna login dengan username dan password.
6. Laravel membuat session dan mengarahkan ke dashboard Mahasiswa.

Field registrasi yang telah muncul dalam keputusan sebelumnya: NPM, nama lengkap, username, password. Konfirmasi ulang field wajib selain ini dan kebijakan NPM uniqueness tetap harus diverifikasi terhadap versi PRD final/DB.

### 7.2 Create personal task
1. Mahasiswa membuka Tugas.
2. Memilih Tambah Tugas.
3. Mengisi detail tugas.
4. Server memvalidasi ownership dan data.
5. Tugas tersimpan di MySQL.
6. Daftar diperbarui.

### 7.3 Academic task distribution
1. Guru/Dosen memilih mata kuliah yang diampu.
2. Membuat tugas akademik dengan deadline dan prioritas.
3. Server memverifikasi kepemilikan mata kuliah.
4. Dalam transaksi, sistem menyimpan definisi tugas.
5. Sistem mengambil enrollment `Terdaftar` pada saat pembuatan.
6. Sistem membuat progress `Belum Dikerjakan` secara idempotent.
7. Sistem membuat notifikasi penerima tanpa duplikasi.
8. Dosen melihat ringkasan distribusi.

### 7.4 Late enrollment
1. Admin mendaftarkan mahasiswa ke mata kuliah.
2. Sistem mencari tugas akademik yang belum ditutup dan belum diarsipkan.
3. Sistem membuat progress yang belum ada.
4. Progress yang sudah ada tidak di-reset.
5. Notifikasi baru dibuat hanya jika event tersebut memang memenuhi deduplication policy.

### 7.5 Progress update
1. Mahasiswa membuka detail tugas akademik.
2. Sistem menampilkan definisi tugas read-only.
3. Mahasiswa memilih salah satu status yang tersedia.
4. Server memastikan progress adalah milik mahasiswa yang login.
5. Server memperbarui status dan aktor perubahan dalam transaksi.
6. Dosen dapat melihat perubahan pada monitoring.

### 7.6 Admin monitoring
1. Admin membuka modul monitoring.
2. Sistem hanya mengembalikan data tugas akademik dan progres yang diizinkan.
3. Tampilan read-only.
4. Tidak ada akses ke deskripsi tugas personal atau materi aktivitas belajar.

## 8. Non-functional product requirements

- Security: session-based authentication, CSRF protection, password hashing, role and resource authorization.
- Privacy: personal task and study activity private to owner.
- Availability: requires network connection.
- Usability: responsive UI for desktop, tablet, mobile.
- Accessibility: keyboard navigation, visible focus, semantic labels, contrast.
- Compatibility: modern Chrome, Firefox, Safari.
- Maintainability: one Laravel application, separated concerns, migrations and tests.
- Integrity: DB constraints plus application validation and transactions.
- Observability: application errors, scheduled task failures, and backup outcomes must be diagnosable.

Numeric thresholds remain `TBD`.

## 9. Product constraints and exclusions

- No native Android application in current scope.
- No offline data entry or background synchronization.
- No public REST API requirement for every screen.
- No gradebook, assignment submission, file attachment, scoring, checklist, percentage progress, or AI module unless separately approved.
- No unrestricted Admin access to personal content.
- No microservices or Kubernetes in MVP.

## 10. Open product decisions

| ID | Decision | Status |
|---|---|---|
| PRD-TBD-016 | Temporary password expiry and forced change | USER-DECIDED (2026-11-18): 72 jam + wajib ganti pada login pertama |
| PRD-TBD-017 | Personal task ownership transfer | OPEN |
| PRD-TBD-018 | Notification schedule, lead times, timezone and preferences | OPEN |
| PRD-TBD-019 | Browser session lifetime and concurrent session policy | OPEN |
| PRD-TBD-020 | Numeric performance and availability targets | OPEN |
| PRD-TBD-021 | Behavior of progress updates after academic task closure | OPEN |
| PRD-TBD-022 | Enrollment status transition (`Ditolak`/`Dibatalkan`/`Selesai`) workflow | USER-DECIDED (2026-11-18): lihat §6.5 |
| PRD-TBD-023 | Admin-editable account fields for Mahasiswa and Guru/Dosen | USER-DECIDED (2026-11-18): `nama_lengkap` saja |
| PRD-TBD-024 | Deadline nullability for personal tasks | USER-DECIDED (2026-11-18): wajib untuk tugas personal dan akademik |

## 11. Acceptance principles

A product capability is accepted only when:
- its user role and ownership rules are explicit;
- server-side validation and authorization exist;
- data changes are persisted correctly;
- relevant edge cases are covered by tests;
- UI communicates success, failure, empty, loading, and permission states;
- traceability to SRS and test cases is recorded.
