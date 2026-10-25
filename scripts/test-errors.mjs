import { chromium } from 'playwright';
const base='http://localhost:8080';
const b=await chromium.launch({channel:'chrome',headless:true});
const ctx=await b.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true});
const p=await ctx.newPage();
const errors=[];p.on('pageerror',e=>errors.push(e.message));p.on('console',m=>{if(m.type()==='error')errors.push(m.text())});
// out-of-stock quantity error
await p.goto(base+'/shop/',{waitUntil:'networkidle'});
const btn=p.locator('[data-hojreh-atc]').first();
await btn.evaluate(el=>el.dataset.quantity='999');
await btn.click();
await p.waitForTimeout(1500);
console.log('toast', await p.evaluate(()=>{const t=document.querySelector('#hojreh-toast');return {hidden:t.hidden,text:t.textContent,type:t.dataset.type,opacity:getComputedStyle(t).opacity}}));
console.log('drawer open', await p.locator('.hojreh-drawer.is-open').count());
await p.screenshot({path:'screenshots/m-toast-error.png'});
// add one valid item and go checkout, submit empty
await p.reload({waitUntil:'networkidle'});
await p.locator('[data-hojreh-atc]').first().click();
await p.waitForSelector('.hojreh-drawer.is-open'); await p.waitForTimeout(300);
await p.locator('[data-drawer-close]').click(); await p.waitForTimeout(300);
await p.goto(base+'/checkout/',{waitUntil:'networkidle'}); await p.waitForTimeout(800);
await p.locator('#place_order').click(); await p.waitForTimeout(2000);
await p.screenshot({path:'screenshots/m-checkout-validation-v2.png'});
// coupon invalid
await p.goto(base+'/cart/',{waitUntil:'networkidle'}); await p.waitForTimeout(500);
const coupon=p.locator('#coupon_code');
if(await coupon.count()){
 await coupon.fill('nope-123');
 await p.locator('button[name="apply_coupon"], input[name="apply_coupon"]').first().click();
 await p.waitForLoadState('networkidle'); await p.waitForTimeout(900);
 console.log('error notices after coupon', await p.locator('.woocommerce-error').count());
 await p.screenshot({path:'screenshots/m-cart-coupon-error.png'});
}
console.log('errors', errors);
await b.close();
