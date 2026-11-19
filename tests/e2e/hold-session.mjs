import { chromium } from 'playwright';

const BASE = process.env.BASE_URL || 'https://stroller-dowry-expulsion.ngrok-free.dev';
const HOLD_MS = Number(process.env.HOLD_MS || 90000);

const devtoolsArgs = process.env.AUTO_DEVTOOLS ? ['--auto-open-devtools-for-tabs'] : [];

const browser = await chromium.launch({
  headless: false,
  args: [
    ...devtoolsArgs,
    '--no-sandbox',
    '--window-position=0,0',
    '--window-size=1440,900',
    '--no-first-run',
    '--disable-features=Translate,AcceptCHFrame',
  ],
});

const ctx = await browser.newContext({
  extraHTTPHeaders: { 'ngrok-skip-browser-warning': 'true' },
  viewport: null,
});
const page = await ctx.newPage();
await page.goto(`${BASE}/login`);
await page.fill('input[name="username"]', 'mhs1');
await page.fill('input[name="password"]', 'password');
await page.getByRole('button', { name: 'Masuk', exact: true }).click();
await page.waitForLoadState('networkidle');
await page.goto(`${BASE}/mahasiswa/dashboard`);
await page.waitForTimeout(1200);

console.log('READY', page.url());
await page.waitForTimeout(HOLD_MS);
await browser.close();
