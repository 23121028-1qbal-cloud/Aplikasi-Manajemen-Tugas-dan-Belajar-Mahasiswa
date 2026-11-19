import { chromium } from 'playwright';
const BASE = process.env.BASE_URL || 'https://stroller-dowry-expulsion.ngrok-free.dev';
const browser = await chromium.launch({ headless: false, args: ['--no-sandbox','--remote-debugging-port=9223','--window-size=1440,900'] });
const ctx = await browser.newContext({ extraHTTPHeaders: { 'ngrok-skip-browser-warning': 'true' }, viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();
await page.goto(`${BASE}/login`);
await page.fill('input[name="username"]', 'mhs1');
await page.fill('input[name="password"]', 'password');
await page.getByRole('button', { name: 'Masuk', exact: true }).click();
await page.waitForLoadState('networkidle');
await page.goto(`${BASE}/mahasiswa/dashboard`);
await page.waitForTimeout(2000);

const targets = await fetch('http://127.0.0.1:9223/json/list').then(r => r.json());
const pageTarget = targets.find(t => t.type === 'page' && /mahasiswa\/dashboard/.test(t.url));
const devtoolsUrl = pageTarget.devtoolsFrontendUrl.replace(/^devtools:\/\//, 'http://127.0.0.1:9223/');
const dt = await ctx.newPage();
await dt.goto(devtoolsUrl, { waitUntil: 'domcontentloaded' });
await dt.waitForTimeout(6000);

// Find elements whose text is exactly "Application"
const els = await dt.$$('text=Application');
console.log('Application elements count:', els.length);
for (let i = 0; i < els.length; i++) {
  const txt = await els[i].innerText().catch(()=>'');
  const box = await els[i].boundingBox();
  console.log(i, JSON.stringify(txt), box);
}
// Screenshot
await dt.screenshot({ path: '/tmp/dt-explore.png' });
await browser.close();
console.log('done');
