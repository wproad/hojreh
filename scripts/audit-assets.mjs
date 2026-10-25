import { chromium } from 'playwright';
const base='http://localhost:8080';
const b=await chromium.launch({channel:'chrome',headless:true});
const ctx=await b.newContext({viewport:{width:1280,height:900}});
const p=await ctx.newPage();
async function audit(path){
  const responses=[];
  p.on('response',r=>{const u=r.url(); if(u.startsWith(base)) responses.push({url:u,type:r.request().resourceType(),status:r.status()});});
  await p.goto(base+path,{waitUntil:'networkidle'});
  await p.waitForTimeout(400);
  const data=await p.evaluate(()=>({
    scripts:[...document.querySelectorAll('script[src]')].map(s=>s.src.replace(location.origin,'')),
    styles:[...document.querySelectorAll('link[rel=stylesheet]')].map(s=>s.href.replace(location.origin,'')),
    inlineStyles:[...document.querySelectorAll('style')].map(s=>s.id).filter(Boolean),
    bodyClass:document.body.className,
    htmlBytes:document.documentElement.outerHTML.length,
  }));
  const local=responses.filter(r=>r.url.startsWith(base));
  const transfer=await p.evaluate(()=>performance.getEntriesByType('resource').filter(e=>e.name.startsWith(location.origin)).reduce((a,e)=>a+(e.transferSize||0),0));
  console.log('\n== '+path, JSON.stringify({...data, requestCount:local.length, transferKB:Math.round(transfer/1024)},null,2));
}
for(const path of ['/','/shop/','/product/mug-sunrise/','/cart/','/checkout/']) await audit(path);
await b.close();
