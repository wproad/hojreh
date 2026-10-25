import { chromium } from 'playwright';
import fs from 'node:fs';
const base='http://localhost:8080';
const b=await chromium.launch({channel:'chrome',headless:true});
const ctx=await b.newContext({viewport:{width:1440,height:1000}});
const p=await ctx.newPage();
const errors=[]; p.on('pageerror',e=>errors.push(e.message)); p.on('console',m=>{if(m.type()==='error')errors.push(m.text())});
await p.goto(base+'/wp-login.php',{waitUntil:'networkidle'});
await p.fill('#user_login','admin');
await p.fill('#user_pass','admin');
await p.click('#wp-submit');
await p.waitForLoadState('networkidle');
console.log('login url', p.url());
await p.goto(base+'/wp-admin/admin.php?page=hojreh-settings',{waitUntil:'networkidle'});
await p.waitForTimeout(1200);
console.log('settings title', await p.title());
await p.screenshot({path:'screenshots/admin-settings.png',fullPage:true});
console.log('fields', await p.evaluate(()=>({
  accent: document.querySelector('input[name="hojreh_settings[accent]"]')?.value,
  card: document.querySelector('select[name="hojreh_settings[default_card]"]')?.value,
  cols: document.querySelector('select[name="hojreh_settings[shop_columns]"]')?.value,
  hero: document.querySelector('#hojreh_hero_image')?.value,
  colorPicker: !!document.querySelector('.wp-picker-container'),
  mediaButtons: document.querySelectorAll('[data-media-select]').length,
  notices: document.querySelectorAll('.notice').length,
})));
// Save altered values
await p.evaluate(()=>{
  const a=document.querySelector('input[name="hojreh_settings[accent]"]'); a.value='#9a3412';
  const card=document.querySelector('select[name="hojreh_settings[default_card]"]'); card.value='bordered';
  const cols=document.querySelector('select[name="hojreh_settings[shop_columns]"]'); cols.value='3';
});
await p.click('#submit');
await p.waitForLoadState('networkidle');
await p.waitForTimeout(800);
console.log('after save', await p.evaluate(()=>({
  accent: document.querySelector('input[name="hojreh_settings[accent]"]')?.value,
  card: document.querySelector('select[name="hojreh_settings[default_card]"]')?.value,
  cols: document.querySelector('select[name="hojreh_settings[shop_columns]"]')?.value,
  saved: document.querySelector('.notice-success, #setting-error-settings_updated')?.innerText?.trim(),
})));
// check frontend css var and shop cards
const check=await ctx.newPage();
await check.goto(base+'/shop/',{waitUntil:'networkidle'});
console.log('frontend', await check.evaluate(()=>({
  accent: getComputedStyle(document.documentElement).getPropertyValue('--accent').trim(),
  cols: getComputedStyle(document.documentElement).getPropertyValue('--shop-cols').trim(),
  variant: document.querySelector('.hojreh-card')?.className,
})));
await check.screenshot({path:'screenshots/shop-after-settings.png',fullPage:true});
// restore defaults via admin
await p.goto(base+'/wp-admin/admin.php?page=hojreh-settings',{waitUntil:'networkidle'});
await p.evaluate(()=>{
  document.querySelector('input[name="hojreh_settings[accent]"]').value='#0f766e';
  document.querySelector('select[name="hojreh_settings[default_card]"]').value='minimal';
  document.querySelector('select[name="hojreh_settings[shop_columns]"]').value='4';
});
await p.click('#submit');
await p.waitForLoadState('networkidle');
await p.waitForTimeout(500);
console.log('ERRORS', errors.length?errors:'none');
await b.close();
