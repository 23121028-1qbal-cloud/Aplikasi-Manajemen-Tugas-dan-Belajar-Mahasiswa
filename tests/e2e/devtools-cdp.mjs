import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL || 'https://stroller-dowry-expulsion.ngrok-free.dev';
const OUT = process.env.OUT_DIR || '/tmp/devtools-cdp';
fs.mkdirSync(OUT, { recursive: true });

const sleep = (ms) => new Promise(r => setTimeout(r, ms));

const browser = await chromium.launch({
  headless: false,
  args: ['--no-sandbox', '--remote-debugging-port=9222', '--window-size=1440,900'],
});

const ctx = await browser.newContext({
  extraHTTPHeaders: { 'ngrok-skip-browser-warning': 'true' },
  viewport: { width: 1440, height: 900 },
});
const page = await ctx.newPage();
await page.goto(`${BASE}/login`);
await page.fill('input[name="username"]', 'mhs1');
await page.fill('input[name="password"]', 'password');
await page.getByRole('button', { name: 'Masuk', exact: true }).click();
await page.waitForLoadState('networkidle');
await page.goto(`${BASE}/mahasiswa/dashboard`);
await page.waitForTimeout(2000);

// Find the DevTools frontend URL for the page target.
const targets = await fetch('http://127.0.0.1:9222/json/list').then(r => r.json());
console.log('targets:', targets.map(t => ({ id: t.id, type: t.type, url: t.url })));
const pageTarget = targets.find(t => t.type === 'page' && /mahasiswa\/dashboard/.test(t.url));
const devtoolsUrl = pageTarget.devtoolsFrontendUrl.replace(/^devtools:\/\//, 'http://127.0.0.1:9222/');
console.log('devtoolsUrl:', devtoolsUrl);

const dt = await ctx.newPage();
await dt.goto(devtoolsUrl, { waitUntil: 'domcontentloaded' });
await sleep(5000);
console.log('devtools title:', await dt.title());

// Try to click Application tab
const tabs = await dt.$$eval('div[role="tab"], [aria-label*="Application"]', els => els.map(e => ({text: e.textContent?.trim(), aria: e.getAttribute('aria-label')})));
console.log('tabs found:', JSON.stringify(tabs.slice(0, 20)));

await dt.screenshot({ path: path.join(OUT, 'dt-01.png') });
await sleep(2000);
await browser.close();
console.log('done');
