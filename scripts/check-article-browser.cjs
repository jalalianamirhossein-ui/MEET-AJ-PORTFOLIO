// Requires exported Blade previews (ARTICLE_PREVIEW_EXPORT=1) and Playwright.
// Set PLAYWRIGHT_MODULE and CHROMIUM_EXECUTABLE when using an external runtime.
const fs=require('fs'),path=require('path'),http=require('http');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const root=process.cwd(),preview=path.join(root,'storage/app/bilingual-preview');
const contracts=JSON.parse(fs.readFileSync(path.join(root,'resources/content/article-technical-contracts.json'),'utf8'));
const slugs=process.env.ARTICLE_BROWSER_SLUGS?process.env.ARTICLE_BROWSER_SLUGS.split(','):Object.keys(contracts.articles);
if(!slugs.length||slugs.some(slug=>!contracts.articles[slug]))throw new Error('Unknown or empty browser article selection');
const reportName=process.env.ARTICLE_BROWSER_REPORT||'enterprise-browser.json';
if(!/^[a-z0-9-]+\.json$/.test(reportName))throw new Error('Browser report must be a simple JSON filename');
const types={'.css':'text/css','.js':'text/javascript','.html':'text/html','.png':'image/png','.svg':'image/svg+xml','.woff2':'font/woff2','.json':'application/json'};
const server=http.createServer((req,res)=>{
 let name=decodeURIComponent(new URL(req.url,'http://127.0.0.1:18081').pathname);
 let base=name.startsWith('/preview/')?preview:path.join(root,'public');
 let file=path.resolve(base,name.startsWith('/preview/')?name.slice(9):'.'+name);
 if(!file.startsWith(base+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()){res.writeHead(404);res.end();return;}
 res.setHeader('Content-Type',types[path.extname(file)]||'application/octet-stream');fs.createReadStream(file).pipe(res);
});
(async()=>{
 await new Promise(resolve=>server.listen(18081,'127.0.0.1',resolve));
 const browser=await chromium.launch({headless:true,...(process.env.CHROMIUM_EXECUTABLE ? {executablePath:process.env.CHROMIUM_EXECUTABLE} : {})});const rows=[];
 try{
 for(const slug of slugs){
  for(const locale of ['en','fa'])for(const mobile of [false,true]){
   const ctx=await browser.newContext({viewport:mobile?{width:390,height:844}:{width:1365,height:900}});
   await ctx.addInitScript(()=>{window.__articleCopied=[];Object.defineProperty(navigator,'clipboard',{configurable:true,value:{writeText:async text=>window.__articleCopied.push(text)}});});
   await ctx.addCookies([{name:'lang',value:locale,url:'http://127.0.0.1:18081'}]);
   const page=await ctx.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
   await page.goto(`http://127.0.0.1:18081/preview/${slug}.${locale}.html`,{waitUntil:'networkidle'});
   await page.waitForFunction(()=>!!window.i18n);
   const artifactIssues=await page.evaluate(async contract=>{
    const issues=[];
    for(const procedure of contract.procedures){
     const target=document.getElementById(procedure.section);if(!target){issues.push('missing procedure '+procedure.section);continue;}
     const section=target.tagName==='SECTION'?target:(target.closest('section')||target);
     const blocks=[...section.querySelectorAll('pre')];const hashes=[];
     for(const block of blocks){
      if(!block.getClientRects().length||getComputedStyle(block).display==='none')issues.push('hidden code '+procedure.section);
      if(!block.closest('.article-code')?.querySelector('.article-copy-button'))issues.push('missing copy button '+procedure.section);
      const digest=await crypto.subtle.digest('SHA-256',new TextEncoder().encode(block.textContent.replace(/\r\n?/g,'\n')));
      hashes.push([...new Uint8Array(digest)].map(v=>v.toString(16).padStart(2,'0')).join(''));
     }
     for(const artifact of procedure.artifacts)if(!hashes.includes(artifact.sha256))issues.push('missing visible artifact '+procedure.section);
    }
    return issues;
   },contracts.articles[slug]);
   // Exercise the actual copy handler without altering the user's clipboard.
   await page.evaluate(()=>{document.querySelector('article .article-copy-button')?.click();});
   await page.waitForFunction(()=>window.__articleCopied.length>0);
   const copyMatches=await page.evaluate(()=>window.__articleCopied[0]===document.querySelector('article .article-code code')?.textContent);
   const initialLanguageFailures=await page.evaluate(()=>{const locale=document.documentElement.lang,norm=v=>v.replace(/\s+/g,' ').trim();return [...document.querySelectorAll('article.article-body [data-'+locale+'], .article-toc-list [data-'+locale+']')].filter(n=>['H2','H3','H4','P','SPAN','A','LI','TH','TD','FIGCAPTION','SUMMARY'].includes(n.tagName)&&!n.closest('pre')).filter(n=>!norm(n.textContent).startsWith(norm(n.getAttribute('data-'+locale)))).map(n=>n.tagName);});
   const before=await page.evaluate(async()=>{
    const article=document.querySelector('article.article-body');
    const ids=[...article.querySelectorAll('[id]')].map(n=>n.id);
    const number=v=>{const normalized=v.replace(/[۰-۹٠-٩]/g,d=>'۰۱۲۳۴۵۶۷۸۹'.includes(d)?'۰۱۲۳۴۵۶۷۸۹'.indexOf(d):'٠١٢٣٤٥٦٧٨٩'.indexOf(d));const m=normalized.trim().match(/^(\d+)[.)]\s/);return m?Number(m[1]):null;};
    const numbers=[...article.querySelectorAll('h2')].map(n=>number(n.textContent)).filter(n=>n!==null);
    const numbering=numbers.some((n,i)=>n!==i+1);
    const tocMismatch=[...document.querySelectorAll('.article-toc-list a')].some(a=>{const target=document.getElementById(decodeURIComponent(a.hash.slice(1)));return !target||a.textContent.replace(/\s+/g,' ').trim()!==target.querySelector('h2')?.textContent.replace(/\s+/g,' ').trim();});
    const canonical=document.querySelector('link[rel=canonical]')?.href;
    const overflow=document.documentElement.scrollWidth>innerWidth+5;
    const codeWhitespace=[...article.querySelectorAll('pre')].map(n=>getComputedStyle(n).whiteSpace);
    const downloads=[...article.querySelectorAll('a[href]')].filter(a=>new URL(a.href).pathname.startsWith('/downloads/'));
    for(const link of downloads)if(!(await fetch(link.href)).ok)throw new Error('Missing download '+link.href);
    const toc=[...document.querySelectorAll('.article-toc-list a')];
    const images=[...document.querySelectorAll('article img[src], .article-hero img[src]')];
    const missing=[];for(const image of images){if(!(await fetch(image.src)).ok)missing.push(image.src);}
    return {numbering,tocMismatch,canonical,overflow,codeWhitespace,lang:document.documentElement.lang,dir:document.documentElement.dir,codeDirections:[...article.querySelectorAll('pre')].map(n=>getComputedStyle(n).direction),ids,code:[...article.querySelectorAll('pre')].map(n=>n.textContent),lastHash:toc.at(-1)?.hash,broken:toc.filter(a=>!document.getElementById(decodeURIComponent(a.hash.slice(1)))).map(a=>a.hash),missing,articleWidth:article.getBoundingClientRect().width};
   });
   await page.locator('article.article-body h2').first().scrollIntoViewIfNeeded();
   if(process.env.ARTICLE_BROWSER_SCREENSHOTS)await page.screenshot({path:path.join(root,`storage/app/enterprise-${slug}-${locale}-${mobile?'mobile':'desktop'}.png`)});
   await page.evaluate(()=>{const d=document.querySelector('.article-toc-disclosure');if(d)d.open=true;[...document.querySelectorAll('.article-toc-list a')].at(-1)?.click();});
   await page.waitForTimeout(700);
   const hash=await page.evaluate(()=>location.hash);
   await page.evaluate(other=>window.i18n.setLanguage(other),locale==='fa'?'en':'fa');
   const after=await page.evaluate(()=>({lang:document.documentElement.lang,dir:document.documentElement.dir,code:[...document.querySelectorAll('article pre')].map(n=>n.textContent),ids:[...document.querySelectorAll('article [id]')].map(n=>n.id)}));
   const issues=[...artifactIssues];if(!copyMatches)issues.push('copied command mismatch');if(before.numbering)issues.push('heading numbering');if(before.tocMismatch)issues.push('TOC label mismatch');if(!before.canonical.endsWith('/articles/'+slug))issues.push('canonical');if(before.overflow)issues.push('viewport overflow');if(before.codeWhitespace.some(v=>!['pre','pre-wrap','break-spaces'].includes(v)))issues.push('code whitespace');if(initialLanguageFailures.length)issues.push('initial translated prose');
   const languageFailures=await page.evaluate(()=>{const locale=document.documentElement.lang,norm=v=>v.replace(/\s+/g,' ').trim();return [...document.querySelectorAll('article.article-body [data-'+locale+'], .article-toc-list [data-'+locale+']')].filter(n=>['H2','H3','H4','P','SPAN','A','LI','TH','TD','FIGCAPTION','SUMMARY'].includes(n.tagName)&&!n.closest('pre')).filter(n=>!norm(n.textContent).startsWith(norm(n.getAttribute('data-'+locale)))).map(n=>({tag:n.tagName,expected:n.getAttribute('data-'+locale).slice(0,80)}));});
   if(languageFailures.length)issues.push('untranslated prose after switch');
   if(before.lang!==locale||before.dir!==(locale==='fa'?'rtl':'ltr'))issues.push('initial locale');
   if(before.codeDirections.some(d=>d!=='ltr'))issues.push('code direction');
   if(before.ids.length!==new Set(before.ids).size||before.broken.length)issues.push('anchors');
   if(before.missing.length)issues.push('images');
   if(hash!==before.lastHash)issues.push('TOC click');
   if(JSON.stringify(before.code)!==JSON.stringify(after.code)||JSON.stringify(before.ids)!==JSON.stringify(after.ids))issues.push('switch integrity');
   if(after.lang===locale||after.dir===before.dir)issues.push('language switch');
   if(errors.length)issues.push('JavaScript error');
   rows.push({slug,locale,mobile,issues,codeBlocks:before.code.length,headingIds:before.ids.length,hash,errors});
   console.log(slug,locale,mobile?'mobile':'desktop',issues.length?issues:'PASS');await ctx.close();
  }
 }
 fs.writeFileSync(path.join(root,'storage/app',reportName),JSON.stringify(rows,null,2));
 if(rows.some(r=>r.issues.length))process.exitCode=1;
 }finally{await browser.close();server.close();}
})().catch(error=>{console.error(error);server.close();process.exitCode=1;});


