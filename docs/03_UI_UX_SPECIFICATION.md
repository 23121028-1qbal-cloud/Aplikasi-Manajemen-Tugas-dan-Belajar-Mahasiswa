# 03 — UI/UX SPECIFICATION

| Metadata | Value |
|---|---|
| Document ID | DOC-03 |
| Version | 1.1.0 |
| Status | Draft |
| Platform | Responsive Web |
| UI framework | Laravel Blade + Bootstrap |

## 1. Purpose

Dokumen ini menetapkan struktur navigasi, inventaris halaman, alur, dan perilaku antarmuka. Hak akses selalu mengikuti PRD/SRS; keberadaan halaman tidak otomatis memberi izin.

## 2. UX principles

1. Role-first navigation: menu menampilkan fungsi sesuai role.
2. Responsive by design: sidebar desktop berubah menjadi offcanvas/compact navigation pada viewport kecil.
3. Clear status: label teks selalu menyertai warna status.
4. Progressive disclosure: detail kompleks ditampilkan saat diperlukan.
5. Consistent feedback: loading, success, error, empty, disabled, dan confirmation states.
6. Privacy by default: data personal tidak muncul pada layar dosen/Admin.
7. Accessible forms: label, hint, validation, keyboard focus, dan error summary.

## 3. Information architecture

### 3.1 Visitor
- Login
- Registrasi Mahasiswa

### 3.2 Mahasiswa
- Dashboard
- Tugas
  - Semua tugas
  - Tugas personal
  - Tugas akademik
  - Riwayat
- Aktivitas Belajar
  - Daftar aktivitas
  - Riwayat
  - Kalender opsional
- Mata Kuliah
- Notifikasi
- Pengaturan akun dan tema

### 3.3 Guru/Dosen
- Dashboard
- Mata Kuliah Saya
- Tugas Akademik
  - Daftar
  - Buat tugas
  - Detail
  - Monitoring progres
- Notifikasi
- Pengaturan akun dan tema

### 3.4 Admin
- Dashboard
- Akun Mahasiswa
- Akun Guru/Dosen
- Mata Kuliah
- Enrollment
- Monitoring Akademik (read-only)
- Notifikasi milik Admin
- Pengaturan akun dan tema

## 4. Navigation behavior

### Desktop
- Sidebar tetap terlihat pada layout utama.
- Header memuat nama halaman, breadcrumb bila relevan, notifikasi, dan menu akun.
- Konten utama menggunakan container dengan lebar yang sesuai jenis halaman.
- Sidebar dapat collapse bila disetujui pada desain detail; tidak mengubah akses.

### Mobile
- Sidebar diganti tombol menu dan Bootstrap Offcanvas atau navigasi ringkas setara.
- Tautan navigasi memiliki label yang jelas.
- Konten menggunakan satu kolom untuk form dan detail.
- Tabel lebar berubah menjadi stacked list/card atau memiliki scroll horizontal yang terkendali.
- Aksi utama mudah dijangkau dan tidak tertutup viewport.

## 5. Page inventory

### 5.1 Shared / public
| ID | Page | Purpose |
|---|---|---|
| PUB-01 | Login | Username/password login |
| PUB-02 | Student Registration | Mahasiswa self-registration |
| SH-01 | Account Profile | Informasi akun sendiri |
| SH-02 | Change Password | Perubahan password |
| SH-03 | Notifications | Daftar notifikasi milik pengguna |
| SH-04 | Appearance Settings | Light/Dark |
| SH-05 | Access Denied / Not Found | Penanganan akses dan resource |

### 5.2 Mahasiswa
| ID | Page | Purpose |
|---|---|---|
| ST-01 | Dashboard | Ringkasan tugas, deadline, aktivitas belajar |
| ST-02 | Task List | Personal + academic task, dibedakan jelas |
| ST-03 | Personal Task Create/Edit | Form tugas personal |
| ST-04 | Personal Task Detail | Detail milik sendiri |
| ST-05 | Academic Task Detail | Definisi read-only + progress sendiri |
| ST-06 | Task History | Tugas selesai/arsip |
| ST-07 | Study Activity List | Daftar aktivitas belajar |
| ST-08 | Study Activity Create/Edit | Form aktivitas |
| ST-09 | Study Activity Detail | Detail aktivitas sendiri |
| ST-10 | Study History | Aktivitas selesai |
| ST-11 | Course List | Mata kuliah yang diikuti |
| ST-12 | Course Detail | Informasi mata kuliah dan tugas akademik terkait |

### 5.3 Guru/Dosen
| ID | Page | Purpose |
|---|---|---|
| GD-01 | Lecturer Dashboard | Ringkasan mata kuliah, tugas, progres |
| GD-02 | Assigned Course List | Mata kuliah yang diampu |
| GD-03 | Course Detail/Edit | Detail dan pengelolaan mata kuliah yang ditugaskan |
| GD-04 | Academic Task List | Daftar tugas akademik |
| GD-05 | Academic Task Create/Edit | Form definisi tugas |
| GD-06 | Academic Task Detail | Definisi, status tutup, ringkasan |
| GD-07 | Course Progress Overview | Monitoring progres per mata kuliah |
| GD-08 | Task Progress Detail | Progres per mahasiswa |
| GD-09 | Close Task Confirmation | Konfirmasi penutupan tugas |

### 5.4 Admin
| ID | Page | Purpose |
|---|---|---|
| AD-01 | Admin Dashboard | Ringkasan administrasi |
| AD-02 | Student Account List | Cari/lihat akun mahasiswa |
| AD-03 | Student Account Detail | Kelola atribut/status yang diizinkan |
| AD-04 | Lecturer Account List | Cari/lihat akun dosen |
| AD-05 | Create Lecturer Account | Buat akun Guru/Dosen |
| AD-06 | Lecturer Account Detail | Kelola atribut/status yang diizinkan |
| AD-07 | Course Administration | Kelola data mata kuliah |
| AD-08 | Enrollment List | Daftar enrollment |
| AD-09 | Create/Edit Enrollment | Kelola enrollment |
| AD-10 | Academic Monitoring | Monitoring read-only |

## 6. Core journeys

### 6.1 Registration
- Visitor membuka registrasi.
- Mengisi field yang diwajibkan.
- Kesalahan validasi ditampilkan di dekat field.
- Server membuat account/profile secara atomik.
- Role yang dibuat selalu Mahasiswa; role tidak dapat dipilih sendiri.

### 6.2 Login/logout
- Login memakai username dan password.
- Error kredensial tidak membocorkan apakah username terdaftar.
- Setelah login, redirect berdasarkan role.
- Logout mengakhiri session dan mengarahkan ke login.

### 6.3 Personal task
- Daftar tugas menampilkan badge Tugas Pribadi.
- Tambah/Edit hanya tersedia bagi Mahasiswa pemilik.
- Course optional.
- Status dan priority menggunakan pilihan yang ditentukan.
- Archive memerlukan konfirmasi dan tidak berarti hard delete.

### 6.4 Academic task
- Badge Tugas Akademik.
- Definisi tugas (judul, instruksi, mata kuliah, deadline) read-only bagi mahasiswa.
- Area progres terpisah dan hanya untuk mahasiswa yang sedang login.
- Dosen melihat monitoring berdasarkan mata kuliah yang diampu.
- Admin melihat tampilan monitoring read-only tanpa tombol mutasi.

### 6.5 Study activity
- List menjadi tampilan utama.
- Kalender merupakan tampilan tambahan opsional, bukan pengganti list.
- Form memiliki judul, materi, mata kuliah opsional, waktu mulai, durasi, status.
- Aktivitas selesai dapat dilihat di riwayat.

### 6.6 Enrollment and distribution
- Admin memilih mahasiswa, mata kuliah, tahun akademik, semester.
- UI menampilkan ringkasan periode dan status.
- Setelah enrollment `Terdaftar`, sistem memproses distribusi tugas yang masih eligible.
- UI tidak menjanjikan jumlah jika server belum mengonfirmasi hasil.

### 6.7 Notifications
- Bell/menu menampilkan unread count.
- Notification list hanya berisi notifikasi pengguna saat ini.
- Mark as read memperbarui state server.
- Notifikasi duplikat tidak ditampilkan sebagai event baru.

## 7. Forms and validation UX

- Gunakan label permanen, bukan placeholder sebagai satu-satunya label.
- Tandai field wajib.
- Tampilkan pesan error spesifik dan actionable.
- Pertahankan input saat validasi gagal.
- Untuk operasi arsip/tutup, gunakan confirmation dialog yang menjelaskan dampak.
- Jangan menampilkan field yang tidak berlaku untuk tipe tugas terpilih.
- Gunakan server validation sebagai sumber kebenaran.

## 8. Data presentation

- List penting mendukung pencarian/filter/pagination sesuai volume.
- Default sorting harus dijelaskan dan konsisten.
- Tabel monitoring menampilkan data minimal yang dibutuhkan.
- Informasi personal task dan study activity tidak boleh masuk ke Admin monitoring.
- Progress ditampilkan per status, bukan persentase buatan.
- Deadline tidak otomatis mengubah status atau menutup academic task.

## 9. Responsive breakpoints

Nilai breakpoint final mengikuti Bootstrap yang dipilih saat implementasi. Layout harus diuji setidaknya pada:
- small mobile sekitar 360–390 CSS px;
- large mobile sekitar 414–430 CSS px;
- tablet sekitar 768–1024 CSS px;
- desktop 1280 CSS px ke atas.

Angka di atas adalah test viewport representatif, bukan dukungan terhadap perangkat tertentu.

## 10. Accessibility and interaction states

- Semua kontrol dapat dioperasikan dengan keyboard.
- Focus state terlihat.
- Dialog mengelola fokus dan dapat ditutup dengan pola aksesibel.
- Form error dikaitkan ke input.
- Kontras warna diuji untuk Light dan Dark.
- Status memakai teks dan ikon/warna sebagai tambahan.
- Empty state tidak menyembunyikan kemungkinan tindakan yang diizinkan.
- Loading state tidak menyebabkan aksi ganda.
- Tombol submit dinonaktifkan sementara request berlangsung bila perlu.

## 11. Open UX decisions

- Final brand identity/logo.
- Exact typography and color tokens.
- Sidebar collapse behavior.
- Final dashboard metrics.
- Notification lead-time and preferences.
- Whether study calendar is included in MVP.
- Exact table pagination size and default sorting.
