# 12 — LOG PROMPT AI DAN VERIFIKASI MANDIRI

## 1. Tujuan dokumen

Dokumen ini mencatat prompt yang diberikan ke asisten AI, hasil analisis yang
dihasilkan, serta verifikasi mandiri yang dilakukan, sesuai rubrik "Dokumentasi
prompt AI dan verifikasi hasilnya" (10 poin).

## 2. Ringkasan percakapan

| # | Prompt pengguna (ringkas) | Hasil/keputusan | Verifikasi |
|---|---|---|---|
| 1 | "lanjut ke phase 8" | Audit UI menyeluruh: tema Light/Dark, sidebar/offcanvas, aksesibilitas, responsif, performa. | Playwright + axe-core dijalankan pada seluruh halaman tiga peran. |
| 2 | "untuk phase 9 memakai ngrok bisa?" | ngrok cocok sebagai simulasi lokal/staging, bukan pengganti VM OCI. | Dokumen batasan dibuat; `TRUSTED_PROXIES` ditambahkan. |
| 3 | "soalnya ini digunakan untuk tugas ini: ..." (instruksi + rubrik) | Rekomendasi ngrok disetujui; commit lokal disiapkan. | Commit 3 dibuat; panduan demo ditulis. |
| 4 | "rekam atau screenshot saya memilih screenshot saja dan siapkan document untuk pdf nya ... dan saya setuju untuk menyusun file log nya" | Disusun laporan MD, log prompt, dan panduan screenshot. | Dokumen ini dibuat. |

## 3. Prompt kunci dan hasil analisis

### 3.1 Prompt: "lanjut ke phase 8"

**Analisis AI:**
- Memeriksa definisi Phase 8 pada `docs/10_IMPLEMENTATION_PLAN.md`: tema Light/Dark, sidebar/offcanvas, kompatibilitas browser, aksesibilitas, state form/empty/loading/error, pengukuran performa.
- Menyiapkan data audit sementara (`Phase8AuditSeeder`) dengan ID: `student=1`, `lecturer=1`, `course=1`, `open_task=1`, `closed_task=2`, `personal=3`, `activity=1`.
- Menjalankan audit axe-core pada seluruh halaman tiga peran, Light/Dark, desktop 1280px dan mobile 390px.

**Hasil:**
- Ditemukan bug: `orWhereKey()` tidak ada pada Eloquent Builder di `Admin\CourseController::edit`; diganti `orWhere('id_guru_dosen', ...)`.
- Ditemukan metric overflow yang salah: `documentElement.scrollWidth` ikut menghitung tabel di dalam `.table-responsive`.
- Perbaikan CSS kontras: `.table`, tombol outline, link, active nav dark, `overflow-x: clip` pada `html`/`body`.
- Label input disabled diberi `for`/`id`; filter mobile pada study index diperbaiki.

**Verifikasi mandiri:**
- `php artisan test --compact` → 132 lulus (555 assertion) sebelum perubahan; 134 lulus (561 assertion) setelahnya.
- Audit ulang → 0 violation axe-core.
- MySQL 8.4.13 diuji dengan container `db-akademik-mysql` (port 3308) → 134 lulus.

### 3.2 Prompt: "untuk phase 9 memakai ngrok bisa?"

**Analisis AI:**
- Membandingkan kebutuhan exit gate Phase 9 (VM OCI, Nginx, PHP-FPM, MySQL, HTTPS, backup, monitoring, rollback) dengan kapabilitas ngrok.
- Mencari data terkini batas paket gratis ngrok (web search): 1 GB transfer/bulan, 20.000 request/bulan, 1 dev domain statis, halaman peringatan browser, 3 endpoint.

**Hasil:**
- ngrok cocok sebagai simulasi lokal/staging, bukan pengganti deployment produksi.
- Ditambahkan dukungan proxy HTTPS di `bootstrap/app.php` (`TRUSTED_PROXIES`) dan `SESSION_SECURE_COOKIE` pada `.env.example`.
- Ditulis `docs/11_DEMO_DEPLOYMENT.md` dengan langkah, verifikasi HTTPS, tiga skenario uji, dan troubleshooting.

**Verifikasi mandiri:**
- `php artisan test --compact` tetap 134 lulus setelah perubahan config.
- `npm run build` sukses (119 modul).

### 3.3 Prompt: "soalnya ini digunakan untuk tugas ini ..."

**Analisis AI:**
- Membaca instruksi tugas: deploy/simulasi lokal, kumpulkan link Drive/GitHub (source code + bukti commit/push, laporan PDF, screenshot/video, pengujian 3 fitur, prompt AI + verifikasi).
- Memeriksa status git: 6 commit, 70 file belum di-commit, belum ada remote.

**Hasil:**
- Commit lokal disiapkan dalam 3 commit tematik (gap closure+Phase 7, Phase 8, demo ngrok).
- `docs/11_DEMO_DEPLOYMENT.md` dan README diperbarui.

**Verifikasi mandiri:**
- `git status --short` bersih setelah commit.
- `git log --oneline` menunjukkan 9 commit.

### 3.4 Prompt: "screenshot saja ... siapkan document untuk pdf ... dan menyusun file log"

**Analisis AI:**
- Menyusun laporan teknis dalam Markdown (siap konversi ke PDF), log prompt AI, dan panduan screenshot.
- Memastikan bukti yang diperlukan (screenshot) terdokumentasi sebagai checklist.

**Hasil:**
- `docs/12_AI_PROMPT_LOG.md` (dokumen ini).
- `docs/13_TECHNICAL_REPORT.md` (laporan teknis).
- `docs/14_SCREENSHOT_CHECKLIST.md` (daftar screenshot yang harus diambil).

**Verifikasi mandiri:**
- Semua dokumen menggunakan data aktual dari repositori (versi, jumlah test, migrasi).

## 4. Pola verifikasi yang digunakan

1. **Test otomatis**: `php artisan test --compact` dijalankan setelah setiap perubahan kode yang menyentuh logika.
2. **Test lintas database**: SQLite in-memory (default) dan MySQL 8.4.13 (container port 3308).
3. **Audit browser**: Playwright + axe-core pada seluruh halaman tiga peran, Light/Dark, desktop dan mobile.
4. **Build aset**: `npm run build` untuk memastikan Vite tidak error.
5. **Inspeksi manual**: membaca diff, mengecek status git, memastikan tidak ada artefak sementara yang tertinggal.

## 5. Catatan kejujuran (keterbatasan)

- Browser WebKit/Safari tidak dapat diuji karena library host (`libicu74`, `libjpeg-turbo8`, `libjxl0.8`) tidak tersedia di mesin pengembang.
- Deployment yang dilakukan adalah simulasi lokal + tunnel ngrok, bukan server produksi. Tautan hanya aktif selama demo.
- Tidak ada klaim lulus yang tidak didukung oleh hasil test yang benar-benar dijalankan.
