# 14 — CHECKLIST SCREENSHOT BUKTI TUGAS

Pengguna memilih **screenshot saja** (tanpa video). Setiap screenshot disimpan
dengan nama berurutan agar mudah dirujuk di laporan.

## A. Bukti aplikasi berjalan (5 poin)

| # | Nama file | Isi yang harus terlihat |
|---|---|---|
| 01 | `01-login.png` | Halaman login di URL ngrok (`https://...ngrok-free.app/login`) |
| 02 | `02-address-bar-https.png` | Address bar menampilkan HTTPS dan domain ngrok |
| 03 | `03-devtools-cookie-secure.png` | DevTools → Cookies → `laravel_session` bertanda Secure |
| 04 | `04-dashboard-mahasiswa.png` | Dashboard mahasiswa (summary + tugas terdekat) |
| 05 | `05-dashboard-dosen.png` | Dashboard dosen (mata kuliah, tugas terbuka) |
| 06 | `06-dashboard-admin.png` | Dashboard admin (mahasiswa, dosen, mata kuliah, enrollment) |

## B. Pengujian minimal tiga fitur utama (10 poin)

### Fitur 1 — Autentikasi & pembatasan role

| # | Nama file | Isi |
|---|---|---|
| 07 | `07-login-mahasiswa.png` | Login berhasil sebagai mahasiswa |
| 08 | `08-akses-ditolak.png` | Mahasiswa membuka `/admin/dashboard` → ditolak |
| 09 | `09-login-dosen.png` | Login sebagai dosen |
| 10 | `10-login-admin.png` | Login sebagai admin |

### Fitur 2 — Tugas personal (buat, tampilkan, arsip, riwayat)

| # | Nama file | Isi |
|---|---|---|
| 11 | `11-form-tugas-personal.png` | Form buat tugas personal (prioritas default Sedang) |
| 12 | `12-daftar-tugas-aktif.png` | Tugas baru tampil di `/mahasiswa/tasks` |
| 13 | `13-modal-arsip.png` | Modal konfirmasi arsip |
| 14 | `14-riwayat-tugas.png` | Tugas arsip tampil di `/mahasiswa/task-history` |

### Fitur 3 — Tugas akademik (distribusi, progres, koreksi, notifikasi)

| # | Nama file | Isi |
|---|---|---|
| 15 | `15-form-tugas-akademik.png` | Dosen membuat tugas akademik |
| 16 | `16-progres-mahasiswa.png` | Mahasiswa mengubah status progres |
| 17 | `17-progres-dosen.png` | Dosen melihat/mengoreksi progres |
| 18 | `18-tugas-ditutup.png` | Tugas ditutup: mahasiswa terkunci, dosen masih bisa koreksi |
| 19 | `19-inbox-notifikasi.png` | Inbox notifikasi mahasiswa |
| 20 | `20-badge-unread.png` | Badge unread sebelum/after ditandai dibaca |

## C. Bukti tema dan responsif (pendukung)

| # | Nama file | Isi |
|---|---|---|
| 21 | `21-tema-gelap.png` | Halaman dengan tema gelap |
| 22 | `22-sidebar-mobile.png` | Sidebar offcanvas di viewport 390px |
| 23 | `23-tabel-responsive.png` | Tabel progres yang dapat di-scroll horizontal di dalam kartu |

## D. Catatan pengambilan

- Gunakan URL ngrok (HTTPS) untuk screenshot A dan B agar membuktikan domain & HTTPS.
- Simpan screenshot di folder `bukti/screenshot/` (jangan commit ke repo publik bila berisi data pribadi; unggah ke Drive).
- Cantumkan tanggal dan URL pada nama file bila perlu, mis. `01-login-2026-11-19.png`.
- Pastikan tidak ada data pribadi nyata pada screenshot (gunakan akun demo).
