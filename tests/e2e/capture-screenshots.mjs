import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL || 'https://stroller-dowry-expulsion.ngrok-free.dev';
const OUT = process.env.OUT_DIR || path.join(process.cwd(), 'bukti', 'screenshot');
fs.mkdirSync(OUT, { recursive: true });

// Header ini melewati halaman peringatan ngrok (free tier) agar screenshot bersih.
const CTX_OPTS = {
  extraHTTPHeaders: { 'ngrok-skip-browser-warning': 'true' },
  viewport: { width: 1280, height: 900 },
};

const shot = async (page, name, opts = {}) => {
  await page.waitForTimeout(350);
  await page.screenshot({ path: path.join(OUT, name), ...opts });
  console.log('saved', name);
};

const login = async (page, username, password = 'password') => {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.getByRole('button', { name: 'Masuk', exact: true }).click();
  await page.waitForLoadState('networkidle');
};

const run = async () => {
  const browser = await chromium.launch({ args: ['--no-sandbox'] });

  // ---------- 01 halaman login ----------
  {
    const ctx = await browser.newContext(CTX_OPTS);
    const page = await ctx.newPage();
    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
    await shot(page, '01-login.png');
    await ctx.close();
  }

  // ---------- Mahasiswa ----------
  const mhsCtx = await browser.newContext(CTX_OPTS);
  const mhs = await mhsCtx.newPage();
  await login(mhs, 'mhs1');
  await shot(mhs, '07-login-mahasiswa.png');
  await shot(mhs, '04-dashboard-mahasiswa.png', { fullPage: true });

  await mhs.goto(`${BASE}/admin/dashboard`, { waitUntil: 'networkidle' });
  await shot(mhs, '08-akses-ditolak.png');

  await mhs.goto(`${BASE}/mahasiswa/tasks/personal/create`, { waitUntil: 'networkidle' });
  await mhs.fill('#judul_tugas', 'Tugas Personal Screenshot Demo');
  await mhs.fill('#deskripsi', 'Dibuat otomatis untuk bukti pengujian fitur tugas personal.');
  await mhs.fill('#deadline', '2026-12-05T10:00');
  await shot(mhs, '11-form-tugas-personal.png');
  await mhs.getByRole('button', { name: 'Simpan Tugas', exact: true }).click();
  await mhs.waitForLoadState('networkidle');
  console.log('after create tugas:', mhs.url());

  await mhs.goto(`${BASE}/mahasiswa/tasks`, { waitUntil: 'networkidle' });
  await shot(mhs, '12-daftar-tugas-aktif.png', { fullPage: true });

  const detailHref = await mhs.evaluate(() => {
    const a = [...document.querySelectorAll('a')].find((el) => /Tugas Personal Screenshot Demo/.test(el.textContent));
    return a ? a.getAttribute('href') : null;
  });
  if (detailHref) {
    await mhs.goto(new URL(detailHref, BASE).toString(), { waitUntil: 'networkidle' });
    const trigger = mhs.locator('[data-bs-toggle="modal"]').first();
    if (await trigger.count()) {
      await trigger.click();
      await mhs.waitForSelector('.modal.show', { timeout: 5000 });
      await mhs.waitForTimeout(600);
      await shot(mhs, '13-modal-arsip.png');
      await mhs.locator('.modal.show form button[type="submit"]').first().click();
      await mhs.waitForLoadState('networkidle');
      console.log('archived via modal');
    }
  } else {
    console.log('WARN: detail tugas tidak ditemukan');
  }

  await mhs.goto(`${BASE}/mahasiswa/task-history`, { waitUntil: 'networkidle' });
  await shot(mhs, '14-riwayat-tugas.png', { fullPage: true });

  await mhs.goto(`${BASE}/notifications`, { waitUntil: 'networkidle' });
  await shot(mhs, '19-inbox-notifikasi.png', { fullPage: true });

  await mhs.goto(`${BASE}/mahasiswa/dashboard`, { waitUntil: 'networkidle' });
  await shot(mhs, '20-badge-unread.png', { clip: { x: 0, y: 0, width: 1280, height: 140 } });

  await mhs.goto(`${BASE}/mahasiswa/academic-tasks/1`, { waitUntil: 'networkidle' });
  await mhs.selectOption('#id_status', { label: 'Sedang Dikerjakan' }).catch(() => {});
  await shot(mhs, '16-progres-mahasiswa.png', { fullPage: true });

  // id 2 = "Tugas Mingguan 1" (is_closed = true)
  await mhs.goto(`${BASE}/mahasiswa/academic-tasks/2`, { waitUntil: 'networkidle' });
  await shot(mhs, '18-tugas-ditutup.png', { fullPage: true });

  await mhs.goto(`${BASE}/mahasiswa/dashboard`, { waitUntil: 'networkidle' });
  await mhs.click('[data-theme-toggle]');
  await mhs.waitForTimeout(400);
  await shot(mhs, '21-tema-gelap.png', { fullPage: true });
  await mhs.click('[data-theme-toggle]');
  await mhs.waitForTimeout(200);

  await mhsCtx.close();

  // ---------- Dosen ----------
  const dosenCtx = await browser.newContext(CTX_OPTS);
  const dosen = await dosenCtx.newPage();
  await login(dosen, 'dosen1');
  await shot(dosen, '09-login-dosen.png');
  await shot(dosen, '05-dashboard-dosen.png', { fullPage: true });

  await dosen.goto(`${BASE}/guru-dosen/academic-tasks/create`, { waitUntil: 'networkidle' });
  await dosen.fill('#judul_tugas', 'Kuis Pemrograman Web');
  await dosen.fill('#deskripsi', 'Kuis singkat materi Blade dan routing.');
  await dosen.fill('#deadline', '2026-12-10T23:59');
  await dosen.selectOption('#id_mata_kuliah', { index: 1 }).catch(() => {});
  await shot(dosen, '15-form-tugas-akademik.png', { fullPage: true });

  await dosen.goto(`${BASE}/guru-dosen/academic-tasks/1/progress`, { waitUntil: 'networkidle' });
  await shot(dosen, '17-progres-dosen.png', { fullPage: true });

  await dosen.setViewportSize({ width: 390, height: 844 });
  await dosen.goto(`${BASE}/guru-dosen/courses/1/progress`, { waitUntil: 'networkidle' });
  await shot(dosen, '23-tabel-responsive.png', { fullPage: true });

  await dosen.goto(`${BASE}/guru-dosen/dashboard`, { waitUntil: 'networkidle' });
  const toggler = dosen.locator('[data-bs-toggle="offcanvas"], .navbar-toggler').first();
  if (await toggler.count()) {
    await toggler.click();
    await dosen.waitForTimeout(700);
    await shot(dosen, '22-sidebar-mobile.png');
  }
  await dosenCtx.close();

  // ---------- Admin ----------
  const adminCtx = await browser.newContext(CTX_OPTS);
  const admin = await adminCtx.newPage();
  await login(admin, 'admin', 'Admin123!');
  await shot(admin, '10-login-admin.png');
  await shot(admin, '06-dashboard-admin.png', { fullPage: true });
  await adminCtx.close();

  await browser.close();
};

run().catch((err) => {
  console.error('FAILED', err);
  process.exit(1);
});
