const fs = require('fs');
const path = require('path');
const { pathToFileURL } = require('url');
const runtime = process.env.CODEX_ARTICLE_NODE_MODULES;
if (!runtime) throw new Error('Set CODEX_ARTICLE_NODE_MODULES to the bundled node_modules directory.');
const sharp = require(path.join(runtime, 'sharp'));
const root = __dirname;
const imgDir = path.join(root, 'images');
fs.mkdirSync(imgDir, { recursive: true });
const esc = s => String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
const text = (x,y,s,size=24,fill='#e7eef8',anchor='start') => `<text x="${x}" y="${y}" font-size="${size}" fill="${fill}" text-anchor="${anchor}">${esc(s)}</text>`;
const rect = (x,y,w,h,fill='#14263d',stroke='#38506b',r=18) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="${fill}" stroke="${stroke}" stroke-width="2"/>`;
const line = (x1,y1,x2,y2,c='#45d3cc',arrow=true) => `<path d="M${x1} ${y1} L${x2} ${y2}" stroke="${c}" stroke-width="3" fill="none" ${arrow?'marker-end="url(#arrow)"':''}/>`;
const card = (x,y,w,h,title,subtitle,c='#45d3cc') => rect(x,y,w,h)+text(x+24,y+40,title,25,c)+text(x+24,y+75,subtitle,19,'#b7c9dd');
const shell = (title,subtitle,body,h=900) => `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="${h}" viewBox="0 0 1600 ${h}" role="img" aria-labelledby="title desc"><title id="title">${esc(title)}</title><desc id="desc">${esc(subtitle)}</desc><defs><linearGradient id="bg" x2="1" y2="1"><stop stop-color="#071323"/><stop offset="1" stop-color="#142c48"/></linearGradient><marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse"><path d="M0 0 L10 5 L0 10 Z" fill="#45d3cc"/></marker></defs><rect width="1600" height="${h}" fill="url(#bg)"/><g font-family="Segoe UI,Arial,sans-serif">${text(70,66,'NETWORK ENGINEERING / FORTIOS 7.4 & 7.6',18,'#45d3cc')}${text(70,132,title,46)}${text(70,178,subtitle,23,'#b7c9dd')}${body}${text(70,h-36,'Technical illustration • outbound internet • independent ISP paths',17,'#9bb1cb')}</g></svg>`;

const images = [];
images.push(['01-banner',shell('FortiGate SD-WAN','Load balancing + performance SLA + automatic failover',
 card(70,255,415,130,'WAN1 / ISP-1','200 Mbps • primary capacity')+
 card(1115,255,415,130,'WAN2 / ISP-2','100 Mbps • additional capacity')+
 line(485,320,615,430)+line(1115,320,985,430)+
 rect(550,420,500,180,'#203956','#45d3cc')+text(800,478,'FortiGate',40,'#ffffff','middle')+text(800,524,'SD-WAN policy engine',26,'#45d3cc','middle')+text(800,563,'Source / destination IP hash',23,'#b7c9dd','middle')+
 line(800,600,800,676)+card(580,680,440,108,'LAN / internal','10.10.10.0/24')+
 card(70,460,380,165,'Performance SLA','Latency • jitter • packet loss')+text(94,580,'Measured per WAN path',20,'#b7c9dd')+
 card(1150,460,380,165,'Session-aware routing','New sessions use eligible paths')+text(1174,580,'Existing flows may reconnect',20,'#ffca7a'))]);

images.push(['02-dual-wan-topology',shell('Dual WAN network topology','internet-zone contains two WAN members; SD-WAN runs inside FortiGate',
 rect(660,230,280,72)+text(800,277,'INTERNET',28,'#e7eef8','middle')+
 line(725,302,430,350,'#45d3cc',false)+line(875,302,1170,350,'#45d3cc',false)+
 card(195,350,470,140,'ISP-1 / wan1 / 200 Mbps','FortiGate: 203.0.113.2/30')+text(219,465,'Gateway: 203.0.113.1',21,'#b7c9dd')+
 card(935,350,470,140,'ISP-2 / wan2 / 100 Mbps','FortiGate: 198.51.100.2/30')+text(959,465,'Gateway: 198.51.100.1',21,'#b7c9dd')+
 line(430,490,655,540)+line(1170,490,945,540)+
 rect(510,540,580,120,'#203956','#45d3cc')+text(800,589,'FortiGate / internet-zone',30,'#ffffff','middle')+text(800,626,'Members 1 and 2 • firewall policy • SNAT',22,'#b7c9dd','middle')+
 line(800,660,800,715)+rect(510,715,580,100)+text(800,757,'internal: 10.10.10.1/24',26,'#45d3cc','middle')+text(800,792,'Clients: 10.10.10.0/24',22,'#b7c9dd','middle'))]);

images.push(['03-sla-architecture',shell('SD-WAN + Performance SLA architecture','Health and quality inform path selection; firewall policy still controls access',
 card(70,255,430,132,'Independent ISP members','WAN1 / WAN2')+
 card(585,255,430,132,'Internet-Probe','1.1.1.1 then 8.8.8.8')+
 card(1100,255,430,132,'Example SLA target','150 ms / 30 ms / 5% loss')+
 line(500,320,582,320)+line(1015,320,1095,320)+
 text(800,435,'Two servers are failover targets, not simultaneous voting.',24,'#ffca7a','middle')+
 line(800,455,800,508)+rect(365,510,870,108,'#203956','#45d3cc')+text(800,554,'SD-WAN rules / eligible path selection',30,'#ffffff','middle')+text(800,592,'Alive/dead and SLA pass/fail are separate states',23,'#b7c9dd','middle')+
 line(560,618,325,680)+line(1040,618,1275,680)+
 card(90,685,470,114,'Critical applications','Service-specific quality / fallback')+
 card(1040,685,470,114,'General internet','Source-destination hash + SLA')+
 text(800,842,'If both WANs are alive but fail SLA, fallback may still use them.',22,'#ffca7a','middle'),940)]);

const methods = [
 ['Source IP','Client IP hash','Across one source IP'],
 ['Source-destination IP','Source + destination hash','Across the same IP pair'],
 ['Sessions / weight','Session share / ratio','Within each session'],
 ['Volume','Measured traffic volume','Within each session'],
 ['Spillover','Bandwidth threshold','Within each session'],
 ['Best quality / SLA','Measured quality / SLA','Depends on rule / hash'],
];
let comparison = rect(70,235,1460,84,'#203956')+text(96,285,'METHOD',23,'#45d3cc')+text(630,285,'PATH SELECTION',23,'#45d3cc')+text(1120,285,'PERSISTENCE SCOPE',23,'#45d3cc');
methods.forEach((a,i)=>{const y=333+i*76;comparison+=rect(70,y,1460,66,i%2?'#13273e':'#102137','#29425d',10)+text(96,y+42,a[0],25)+text(630,y+42,a[1],23,'#b7c9dd')+text(1120,y+42,a[2],22,'#b7c9dd');});
comparison+=text(70,843,'Weighted implicit rules do not make an explicit source-destination hash rule weighted.',23,'#ffca7a');
images.push(['04-methods-comparison',shell('Load balancing methods comparison','Persistence is conditional on a stable eligible path set; no single-session WAN bonding',comparison,930)]);

const steps = [
 ['Interface up?','Cable / modem / admin status / errors'],
 ['Gateway reachable?','IP / mask / ARP / ISP handoff'],
 ['Performance SLA passed?','Probe availability / upstream quality'],
 ['Correct SD-WAN rule?','Match criteria / rule order'],
 ['Route exists?','Default / distance / route withdrawal'],
 ['Firewall policy matched?','Interfaces / objects / policy order'],
 ['NAT correct?','Outgoing IP / IP pool / central SNAT'],
 ['Session / packet debug','Return path / DNS / MTU / offload'],
];
let flow = text(345,250,'Internet problem',28,'#ffca7a','middle')+text(1060,250,'Investigate when the check fails',25,'#b7c9dd','middle');
steps.forEach((a,i)=>{const y=285+i*97;flow+=rect(70,y,550,72)+text(345,y+46,a[0],25,'#e7eef8','middle')+line(620,y+36,740,y+36)+text(671,y+22,i<7?'NO':'NEXT',17,'#ffca7a','middle')+rect(750,y,780,72,'#192c43','#38506b')+text(775,y+46,a[1],25,'#b7c9dd');if(i<7) flow+=line(345,y+72,345,y+95);});
flow+=text(70,1120,'Use fresh sessions; avoid clearing the entire production session table.',24,'#ffca7a');
images.push(['05-troubleshooting-flow',shell('FortiGate SD-WAN troubleshooting','An investigation sequence, not the internal FortiOS packet-processing order',flow,1200)]);

async function main() {
 for (const [name,svg] of images) {
  const svgPath = path.join(imgDir,name+'.svg');
  const pngPath = path.join(imgDir,name+'.png');
  const changed = !fs.existsSync(svgPath) || fs.readFileSync(svgPath,'utf8') !== svg;
  fs.writeFileSync(svgPath,svg);
  if (changed || !fs.existsSync(pngPath)) fs.writeFileSync(pngPath, await sharp(Buffer.from(svg)).png().toBuffer());
 }
 const {marked} = await import(pathToFileURL(require.resolve(path.join(runtime,'marked'))).href);
 const markdown = fs.readFileSync(path.join(root,'article.fa.md'),'utf8').replace(/\r\n/g, '\n');
 const body = marked.parse(markdown.replace(/images\/([\w-]+)\.svg/g,'images/$1.png'));
 fs.writeFileSync(path.join(root,'article-body.fa.html'),body);
 const css = `*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:#f4f7fa;color:#162536;font:18px/2.05 Tahoma,"Segoe UI",sans-serif}main{max-width:1060px;margin:40px auto;background:white;padding:44px;border-radius:18px;box-shadow:0 10px 40px #102b4310}h1{font-size:32px;line-height:1.65}h2{margin-top:54px;padding-bottom:12px;border-bottom:2px solid #e2eaf0;font-size:27px}h3{margin-top:32px;font-size:22px}a{color:#075d9c;overflow-wrap:anywhere}img{width:100%;height:auto;border-radius:12px}pre{direction:ltr;text-align:left;unicode-bidi:isolate;overflow:auto;background:#0b1a2b;color:#e2edf8;padding:24px;border-radius:10px;font:15px/1.7 Consolas,monospace}code{direction:ltr;unicode-bidi:isolate;font-family:Consolas,monospace}p code,li code,td code{display:inline-block;color:#075d9c;background:#eef5fa;padding:0 5px;border-radius:4px}table{width:100%;border-collapse:collapse;font-size:15px;display:block;overflow:auto}th{background:#e8f2f7}td,th{border:1px solid #d8e3ec;padding:10px;min-width:115px;text-align:right}blockquote{border-right:4px solid #168e9c;padding:12px 24px;background:#eff9fa}@media(max-width:640px){main{margin:0;padding:24px 18px;border-radius:0}body{font-size:16px}h1{font-size:26px}h2{font-size:23px}pre{padding:16px;font-size:13px}}`;
 fs.writeFileSync(path.join(root,'article.fa.html'),`<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>لود بالانس و Failover در FortiGate با SD-WAN و SLA</title><meta name="description" content="آموزش عملی Load Balancing و Failover اینترنت در FortiGate با SD-WAN، Performance SLA، تنظیمات CLI و GUI، توزیع Source-Destination و عیب‌یابی Dual WAN."><meta name="keywords" content="FortiGate Load Balancing, FortiGate SD-WAN, FortiGate Failover, FortiGate Performance SLA, FortiGate Dual WAN, FortiGate WAN Load Balancing, SD-WAN Load Balancing, FortiOS SD-WAN"><style>${css}</style></head><body><main>${body}</main></body></html>`);
 const slug = 'fortigate-sd-wan-load-balancing-failover';
 const workspace = path.resolve(root, '../../../..');
 const legacyDir = path.join(workspace, 'resources/legacy/articles');
 const assetDir = path.join(workspace, 'resources/assets/img/articles');
 const bannerUrl = `/assets/img/articles/banners/${slug}.png`;
 const contentUrl = `/assets/img/articles/content/${slug}`;
 const titleFa = markdown.match(/^# (.+)/)[1];
 const titleEn = 'FortiGate Internet Load Balancing and Failover with SD-WAN and Performance SLA';
 const description = 'آموزش عملی Load Balancing و Failover اینترنت در FortiGate با SD-WAN، Performance SLA، تنظیمات CLI و GUI، توزیع Source-Destination و عیب‌یابی Dual WAN.';
 const keywords = 'FortiGate Load Balancing, FortiGate SD-WAN, FortiGate Failover, FortiGate Performance SLA, FortiGate Dual WAN, FortiGate WAN Load Balancing, SD-WAN Load Balancing, FortiOS SD-WAN';
 const canonical = `https://meetaj.ir/articles/${slug}`;
 const headings = [];
 let legacyBody = body.replace(/^<h1>[\s\S]*?<\/h1>\s*/, '')
  .replace(/<p><img src="images\/01-banner.png"[^>]*><\/p>\s*/, '')
  .replace(/<p><em>تصویر ۱[\s\S]*?<\/em><\/p>\s*/, '')
  .replace(/src="images\/([\w-]+)\.png"/g, `src="${contentUrl}/$1.png" loading="lazy"`)
  .replace(/<pre>/g, '<pre dir="ltr">')
  .replace(/<h2>([\s\S]*?)<\/h2>/g, (_, label) => {
   const id = `section-${headings.length + 1}`;
   headings.push({id, label});
   return `<h2 id="${id}">${label}</h2>`;
  });
 const toc = headings.map(({id,label})=>`<li class="article-nav-item"><a href="#${id}">${label}</a></li>`).join('\n');
 const faqSource = markdown.split('## FAQ')[1].split('## SEO')[0];
 const faq = [...faqSource.matchAll(/### (.+)\n+([\s\S]*?)(?=\n### |$)/g)].map(m=>({
  '@type':'Question', name:m[1], acceptedAnswer:{'@type':'Answer',text:m[2].trim()}
 }));
 const schema = {'@context':'https://schema.org','@type':'Article',headline:titleFa,description,inLanguage:'fa',url:canonical,image:bannerUrl};
 const faqSchema = {'@context':'https://schema.org','@type':'FAQPage',inLanguage:'fa',mainEntity:faq};
 fs.mkdirSync(legacyDir,{recursive:true});
 fs.writeFileSync(path.join(legacyDir,slug+'.html'),`<!doctype html>
<html lang="fa" dir="rtl" data-article-language="fa"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>لود بالانس و Failover در FortiGate با SD-WAN و SLA</title>
<meta name="article:content-language" content="fa">
<meta name="description" content="${esc(description)}"><meta name="keywords" content="${esc(keywords)}">
<meta name="robots" content="index, follow">
<meta property="og:title" content="${esc(titleFa)}"><meta property="og:description" content="${esc(description)}">
<meta property="og:type" content="article"><meta property="og:image" content="${bannerUrl}">
<meta property="og:url" content="${canonical}"><link rel="canonical" href="${canonical}">
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="${esc(titleFa)}">
<meta name="twitter:description" content="${esc(description)}"><meta name="twitter:image" content="${bannerUrl}">
<script type="application/ld+json">${JSON.stringify(schema)}</script>
<script type="application/ld+json">${JSON.stringify(faqSchema)}</script>
<style>${css}</style></head><body class="article-page theme-other">
<header><nav aria-label="فهرست مقاله"><ul>${toc}</ul></nav></header><main>
<section class="article-hero article-header hero">
<span class="article-category" data-en="Networking" data-fa="شبکه">شبکه</span>
<h1 class="article-title hero-title" data-en="${esc(titleEn)}" data-fa="${esc(titleFa)}">${esc(titleFa)}</h1>
<p class="article-excerpt hero-subtitle" data-en="A technical guide to dual-WAN load balancing, SLA monitoring, failover and troubleshooting in FortiGate." data-fa="${esc(description)}">${esc(description)}</p>
<figure><img class="article-hero-thumbnail" src="${bannerUrl}" alt="FortiGate SD-WAN Load Balancing" width="1600" height="900">
<figcaption>تصویر ۱ — استفاده همزمان از دو ISP همراه با پایش کیفیت و Failover برای اتصال اینترنت سازمان.</figcaption></figure>
</section><article class="article-body" dir="rtl">${legacyBody}</article></main></body></html>`);
 fs.mkdirSync(path.join(assetDir,'content'),{recursive:true});
 fs.copyFileSync(path.join(imgDir,'01-banner.png'),path.join(assetDir,'content','fortigate-generated-banner.png'));
 const siteImages = require('./site-images.cjs');
 for (const [oldName,generatedName] of siteImages.mappings) fs.copyFileSync(path.join(imgDir,oldName),path.join(assetDir,'content',generatedName));
 siteImages.publish();
 const cliBlocks = [...markdown.matchAll(/```fortios\n([\s\S]*?)```/g)].map(m=>m[1]);
 fs.writeFileSync(path.join(root,'base-config.fortios.conf'),cliBlocks.slice(0,6).join('\n'));
 for (const [name] of images) console.log(name+'.svg + .png');
 console.log('Legacy HTML, image assets and base configuration generated. Total CLI blocks:',cliBlocks.length);
}
main().catch(e=>{console.error(e);process.exitCode=1});
