import { chromium } from 'playwright';
import fs from 'node:fs';

const base = 'http://localhost:8080';
const out = 'screenshots';
fs.mkdirSync(out, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome', headless: true });
const errors = [];

function watch(page, label) {
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push(`[console:${label}] ${msg.text()}`);
  });
  page.on('pageerror', (err) => errors.push(`[pageerror:${label}] ${err.message}`));
  page.on('requestfailed', (req) => {
    if (!req.url().includes('favicon')) errors.push(`[requestfailed:${label}] ${req.url()} ${req.failure()?.errorText}`);
  });
}

async function settle(page) {
  await page.waitForLoadState('networkidle');
  await page.evaluate(() => document.fonts.ready);
}

async function scrollAll(page) {
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 500) {
      window.scrollTo(0, y);
      await new Promise((r) => setTimeout(r, 45));
    }
    window.scrollTo(0, 0);
  });
  await page.waitForTimeout(500);
}

async function fullShot(page, name) {
  await scrollAll(page);
  await page.addStyleTag({
    content: '.hojreh-bottom-nav,.hojreh-sticky-atc,.hojreh-toast,.hojreh-drawer,.hojreh-backdrop{display:none!important}',
  });
  await page.screenshot({ path: `${out}/${name}.png`, fullPage: true });
}

const desktop = await browser.newContext({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
const page = await desktop.newPage();
watch(page, 'desktop');

for (const [name, path] of [
  ['home-desktop', '/'],
  ['shop-desktop', '/shop/'],
  ['product-desktop', '/product/mug-sunrise/'],
  ['journal-desktop', '/journal/'],
  ['account-desktop', '/my-account/'],
]) {
  await page.goto(base + path, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await fullShot(page, name);
  await page.screenshot({ path: `${out}/${name}-viewport.png` });
}

// Dark mode
await page.goto(base + '/', { waitUntil: 'domcontentloaded' });
await settle(page);
await page.click('[data-theme-toggle]');
await page.waitForTimeout(350);
await page.screenshot({ path: `${out}/home-desktop-dark-viewport.png` });
await fullShot(page, 'home-desktop-dark');

// Desktop add to cart -> drawer
await page.goto(base + '/shop/', { waitUntil: 'domcontentloaded' });
await settle(page);
await page.locator('[data-hojreh-atc]').first().click();
await page.waitForSelector('.hojreh-drawer.is-open', { timeout: 10000 });
await page.waitForTimeout(500);
await page.screenshot({ path: `${out}/drawer-desktop.png` });
console.log('desktop count after add:', await page.locator('.hojreh-cart-button [data-cart-count]').textContent());

const mobile = await browser.newContext({
  viewport: { width: 390, height: 844 },
  deviceScaleFactor: 2,
  isMobile: true,
  hasTouch: true,
  userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
});
const mpage = await mobile.newPage();
watch(mpage, 'mobile');

for (const [name, path] of [
  ['home-mobile', '/'],
  ['shop-mobile', '/shop/'],
  ['product-mobile', '/product/mug-sunrise/'],
  ['journal-mobile', '/journal/'],
  ['account-mobile', '/my-account/'],
]) {
  await mpage.goto(base + path, { waitUntil: 'domcontentloaded' });
  await settle(mpage);
  await fullShot(mpage, name);
  await mpage.screenshot({ path: `${out}/${name}-viewport.png` });
}

// Mobile add to cart + drawer
await mpage.goto(base + '/shop/', { waitUntil: 'domcontentloaded' });
await settle(mpage);
await mpage.locator('[data-hojreh-atc]').nth(1).click();
await mpage.waitForSelector('.hojreh-drawer.is-open', { timeout: 10000 });
await mpage.waitForTimeout(500);
await mpage.screenshot({ path: `${out}/drawer-mobile.png` });
console.log('mobile drawer count:', await mpage.locator('.hojreh-bottom-nav [data-cart-count]').textContent());

const qtyBefore = await mpage.locator('.hojreh-drawer .hojreh-qty input').first().inputValue();
await mpage.locator('.hojreh-drawer .hojreh-qty [data-qty="inc"]').first().click();
await mpage.waitForTimeout(1400);
console.log('drawer qty', qtyBefore, '->', await mpage.locator('.hojreh-drawer .hojreh-qty input').first().inputValue());

await mpage.locator('[data-drawer-close]').click();
await mpage.waitForTimeout(400);

// Cart
await mpage.goto(base + '/cart/', { waitUntil: 'domcontentloaded' });
await settle(mpage);
await mpage.screenshot({ path: `${out}/cart-mobile-viewport.png` });
await fullShot(mpage, 'cart-mobile');

// Single product add + sticky
await mpage.goto(base + '/product/headphones-quiet/', { waitUntil: 'domcontentloaded' });
await settle(mpage);
await mpage.screenshot({ path: `${out}/product-mobile-viewport.png` });
await mpage.locator('.single_add_to_cart_button').scrollIntoViewIfNeeded();
await mpage.locator('.single_add_to_cart_button').click();
await mpage.waitForSelector('.hojreh-drawer.is-open', { timeout: 10000 });
await mpage.waitForTimeout(400);
await mpage.screenshot({ path: `${out}/single-add-drawer-mobile.png` });
await mpage.locator('[data-drawer-close]').click();
await mpage.waitForTimeout(350);
await mpage.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
await mpage.waitForTimeout(250);
await mpage.evaluate(() => window.scrollTo({ top: 1200, behavior: 'instant' }));
await mpage.waitForTimeout(500);
const stickyVisible = await mpage.locator('[data-sticky-atc]').evaluate((el) => el.classList.contains('is-visible'));
console.log('sticky atc visible after scrolling past form:', stickyVisible);
await mpage.screenshot({ path: `${out}/product-mobile-sticky.png` });
await fullShot(mpage, 'product-mobile');

// Mobile dark mode
await mpage.goto(base + '/', { waitUntil: 'domcontentloaded' });
await settle(mpage);
await mpage.click('[data-theme-toggle]');
await mpage.waitForTimeout(350);
await mpage.screenshot({ path: `${out}/home-mobile-dark-viewport.png` });
await fullShot(mpage, 'home-mobile-dark');

// Checkout flow
await mpage.goto(base + '/checkout/', { waitUntil: 'domcontentloaded' });
await settle(mpage);
await mpage.screenshot({ path: `${out}/checkout-mobile.png`, fullPage: true });

const checkoutVisible = await mpage.locator('#billing_first_name').count();
console.log('checkout form fields:', checkoutVisible);

if (checkoutVisible) {
  await mpage.fill('#billing_first_name', 'حامد');
  await mpage.fill('#billing_last_name', 'محمدی');
  await mpage.fill('#billing_address_1', 'خیابان ولیعصر، پلاک ۱۲');
  await mpage.fill('#billing_city', 'تهران');
  await mpage.fill('#billing_postcode', '1234567890');
  await mpage.fill('#billing_phone', '09121234567');
  await mpage.fill('#billing_email', 'hamed@example.com');
  await mpage.selectOption('#billing_state', { index: 1 }).catch(() => {});
  await mpage.waitForTimeout(1500);
  await mpage.screenshot({ path: `${out}/checkout-mobile-filled.png`, fullPage: true });
  await mpage.locator('#place_order').click();
  await mpage.waitForTimeout(3500);
  console.log('after order URL:', mpage.url());
  await mpage.screenshot({ path: `${out}/order-received-mobile.png`, fullPage: true });
}

console.log('ERRORS:', errors.length ? JSON.stringify(errors, null, 2) : 'none');
await browser.close();
