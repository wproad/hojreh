import { chromium } from 'playwright';
import fs from 'node:fs';
const base='http://localhost:8080';
const out='screenshots';
fs.mkdirSync(out,{recursive:true});
const browser=await chromium.launch({channel:'chrome',headless:true});
const errors=[];
function watch(p,l){p.on('console',m=>{if(m.type()==='error')errors.push(`[${l}] ${m.text()}`)});p.on('pageerror',e=>errors.push(`[${l}] ${e.message}`));}
async function settle(p){await p.waitForLoadState('networkidle');await p.evaluate(()=>document.fonts.ready);}
async function scrollAll(p){await p.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=500){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,35));}window.scrollTo(0,0);});await p.waitForTimeout(350);}
async function sectionShot(p,name,selector){await scrollAll(p);const el=p.locator(selector).first();await el.scrollIntoViewIfNeeded();await p.waitForTimeout(250);await p.screenshot({path:`${out}/${name}.png`});}

const m=await browser.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true});
const p=await m.newPage(); watch(p,'mobile');
await p.goto(base+'/',{waitUntil:'domcontentloaded'}); await settle(p);
await sectionShot(p,'m-hero','.hojreh-hero');
await sectionShot(p,'m-values','.hojreh-values');
await sectionShot(p,'m-categories','.hojreh-categories');
await p.locator('.hojreh-section').nth(1).scrollIntoViewIfNeeded(); await p.waitForTimeout(600);
await p.screenshot({path:`${out}/m-featured.png`});
await sectionShot(p,'m-footer','.hojreh-footer');
// mobile menu
await p.evaluate(()=>window.scrollTo(0,0)); await p.waitForTimeout(300);
await p.click('[data-menu-toggle]'); await p.waitForTimeout(300);
await p.screenshot({path:`${out}/m-menu.png`});
await p.click('[data-menu-toggle]'); await p.waitForTimeout(200);
// search
await p.click('[data-search-toggle]'); await p.waitForTimeout(300);
await p.screenshot({path:`${out}/m-search-open.png`});
await p.fill('#hojreh-search-input','ماگ');
await p.locator('#hojreh-search button[type=submit]').click();
await p.waitForLoadState('networkidle'); await p.waitForTimeout(500);
console.log('search url', p.url(), 'title', await p.title());
await p.screenshot({path:`${out}/m-search-results.png`});
// add and drawer
await p.goto(base+'/shop/',{waitUntil:'domcontentloaded'}); await settle(p);
await p.locator('[data-hojreh-atc]').first().click();
await p.waitForSelector('.hojreh-drawer.is-open'); await p.waitForTimeout(500);
await p.screenshot({path:`${out}/m-drawer.png`});
await p.locator('[data-drawer-close]').click(); await p.waitForTimeout(300);
await p.goto(base+'/cart/',{waitUntil:'domcontentloaded'}); await settle(p);
await p.screenshot({path:`${out}/m-cart.png`});
// checkout light
await p.goto(base+'/checkout/',{waitUntil:'domcontentloaded'}); await settle(p);
await p.screenshot({path:`${out}/m-checkout-top.png`});
await scrollAll(p);
await p.screenshot({path:`${out}/m-checkout.png`,fullPage:true});
// product page
await p.goto(base+'/product/headphones-quiet/',{waitUntil:'domcontentloaded'}); await settle(p);
await p.screenshot({path:`${out}/m-product-top.png`});
await scrollAll(p);
await p.screenshot({path:`${out}/m-product-full.png`,fullPage:true});
// footer and menu shots done

const d=await browser.newContext({viewport:{width:1440,height:1000}});
const dp=await d.newPage(); watch(dp,'desktop');
await dp.goto(base+'/journal/',{waitUntil:'domcontentloaded'}); await settle(dp);
await scrollAll(dp); await dp.screenshot({path:`${out}/d-journal.png`,fullPage:true});
await dp.goto(base+'/my-account/',{waitUntil:'domcontentloaded'}); await settle(dp);
await scrollAll(dp); await dp.screenshot({path:`${out}/d-account.png`,fullPage:true});
// add item and checkout desktop
await dp.goto(base+'/shop/',{waitUntil:'domcontentloaded'}); await settle(dp);
await dp.locator('[data-hojreh-atc]').first().click();
await dp.waitForSelector('.hojreh-drawer.is-open'); await dp.waitForTimeout(400);
await dp.screenshot({path:`${out}/d-drawer.png`});
await dp.goto(base+'/checkout/',{waitUntil:'domcontentloaded'}); await settle(dp);
await scrollAll(dp);
await dp.screenshot({path:`${out}/d-checkout.png`,fullPage:true});
// product desktop
await dp.goto(base+'/product/mug-sunrise/',{waitUntil:'domcontentloaded'}); await settle(dp);
await scrollAll(dp);
await dp.screenshot({path:`${out}/d-product.png`,fullPage:true});
// dark home mobile
const dp2=await browser.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true});
const p2=await dp2.newPage(); watch(p2,'dark-mobile');
await p2.goto(base+'/',{waitUntil:'domcontentloaded'}); await settle(p2);
await p2.click('[data-theme-toggle]'); await p2.waitForTimeout(400);
await p2.screenshot({path:`${out}/m-home-dark.png`});
await p2.locator('.hojreh-section').nth(1).scrollIntoViewIfNeeded(); await p2.waitForTimeout(500);
await p2.screenshot({path:`${out}/m-cards-dark.png`});
console.log('ERRORS', errors.length?JSON.stringify(errors,null,2):'none');
await browser.close();
