import { chromium } from 'playwright';
const base='http://localhost:8080';
const b=await chromium.launch({channel:'chrome',headless:true});
const errors=[];
const m=await b.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true});
const p=await m.newPage(); p.on('pageerror',e=>errors.push(e.message)); p.on('console',x=>{if(x.type()==='error')errors.push(x.text())});
const settle=async()=>{await p.waitForLoadState('networkidle');await p.evaluate(()=>document.fonts.ready);await p.waitForTimeout(300);};

// Add and remove from drawer -> empty state
await p.goto(base+'/shop/',{waitUntil:'domcontentloaded'}); await settle();
await p.locator('[data-hojreh-atc]').first().click();
await p.waitForSelector('.hojreh-drawer.is-open'); await p.waitForTimeout(500);
await p.locator('.hojreh-drawer [data-hojreh-remove]').first().click();
await p.waitForTimeout(1400);
console.log('empty drawer?', await p.locator('.hojreh-drawer-empty').count(), 'count', await p.locator('.hojreh-bottom-nav [data-cart-count]').isHidden().catch(()=>null));
await p.screenshot({path:'screenshots/m-drawer-empty.png'});
await p.locator('[data-drawer-close]').click(); await p.waitForTimeout(300);
await p.goto(base+'/cart/',{waitUntil:'domcontentloaded'}); await settle();
await p.screenshot({path:'screenshots/m-cart-empty.png'});

// Invalid coupon notice
await p.goto(base+'/shop/',{waitUntil:'domcontentloaded'}); await settle();
await p.locator('[data-hojreh-atc]').nth(2).click();
await p.waitForSelector('.hojreh-drawer.is-open'); await p.waitForTimeout(500);
await p.locator('[data-drawer-close]').click(); await p.waitForTimeout(300);
await p.goto(base+'/cart/',{waitUntil:'domcontentloaded'}); await settle();
const coupon = p.locator('#coupon_code');
if (await coupon.count()) {
  await coupon.fill('INVALID-CODE');
  await p.locator('button[name="apply_coupon"]').click();
  await p.waitForLoadState('networkidle'); await p.waitForTimeout(700);
  console.log('notice count', await p.locator('.woocommerce-error, .woocommerce-message, .woocommerce-info').count());
  await p.screenshot({path:'screenshots/m-cart-notice.png'});
}

// Search with multiple results
await p.goto(base+'/',{waitUntil:'domcontentloaded'}); await settle();
await p.click('[data-search-toggle]'); await p.waitForTimeout(200);
await p.fill('#hojreh-search-input','دفتر');
await p.locator('#hojreh-search button[type=submit]').click();
await p.waitForLoadState('networkidle'); await p.waitForTimeout(500);
console.log('search url',p.url(),'cards',await p.locator('.hojreh-card').count());
await p.screenshot({path:'screenshots/m-search-results.png',fullPage:true});

// 404
await p.goto(base+'/this-page-does-not-exist/',{waitUntil:'domcontentloaded'}); await settle();
await p.screenshot({path:'screenshots/m-404.png'});

// Card demo
await p.goto(base+'/card-demo/',{waitUntil:'domcontentloaded'}); await settle();
await p.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,30));}window.scrollTo(0,0)});
await p.screenshot({path:'screenshots/m-card-demo.png',fullPage:true});
const d=await b.newContext({viewport:{width:1440,height:1000}});
const dp=await d.newPage(); dp.on('pageerror',e=>errors.push(e.message));
await dp.goto(base+'/card-demo/',{waitUntil:'domcontentloaded'}); await settle();
await dp.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,30));}window.scrollTo(0,0)});
await dp.screenshot({path:'screenshots/d-card-demo.png',fullPage:true});
await dp.goto(base+'/journal/',{waitUntil:'domcontentloaded'}); await settle();
await dp.screenshot({path:'screenshots/d-journal-v2.png',fullPage:true});
console.log('ERRORS', errors.length?JSON.stringify(errors,null,2):'none');
await b.close();
