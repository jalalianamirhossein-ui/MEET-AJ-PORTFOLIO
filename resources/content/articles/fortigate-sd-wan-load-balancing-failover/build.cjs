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
  fs.writeFileSync(path.join(imgDir,name+'.svg'),svg);
  await sharp(Buffer.from(svg)).png().toFile(path.join(imgDir,name+'.png'));
 }
 const {marked} = await import(pathToFileURL(require.resolve(path.join(runtime,'marked'))).href);
 const markdown = fs.readFileSync(path.join(root,'article.fa.md'),'utf8');
 const body = marked.parse(markdown.replace(/images\/([\w-]+)\.svg/g,'images/$1.png'));
 fs.writeFileSync(path.join(root,'article-body.fa.html'),body);
 const css = `*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:#f4f7fa;color:#162536;font:18px/2.05 Tahoma,"Segoe UI",sans-serif}main{max-width:1060px;margin:40px auto;background:white;padding:44px;border-radius:18px;box-shadow:0 10px 40px #102b4310}h1{font-size:32px;line-height:1.65}h2{margin-top:54px;padding-bottom:12px;border-bottom:2px solid #e2eaf0;font-size:27px}h3{margin-top:32px;font-size:22px}a{color:#075d9c;overflow-wrap:anywhere}img{width:100%;height:auto;border-radius:12px}pre{direction:ltr;text-align:left;unicode-bidi:isolate;overflow:auto;background:#0b1a2b;color:#e2edf8;padding:24px;border-radius:10px;font:15px/1.7 Consolas,monospace}code{direction:ltr;unicode-bidi:isolate;font-family:Consolas,monospace}p code,li code,td code{display:inline-block;color:#075d9c;background:#eef5fa;padding:0 5px;border-radius:4px}table{width:100%;border-collapse:collapse;font-size:15px;display:block;overflow:auto}th{background:#e8f2f7}td,th{border:1px solid #d8e3ec;padding:10px;min-width:115px;text-align:right}blockquote{border-right:4px solid #168e9c;padding:12px 24px;background:#eff9fa}@media(max-width:640px){main{margin:0;padding:24px 18px;border-radius:0}body{font-size:16px}h1{font-size:26px}h2{font-size:23px}pre{padding:16px;font-size:13px}}`;
 fs.writeFileSync(path.join(root,'article.fa.html'),`<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>لود بالانس و Failover در FortiGate با SD-WAN و SLA</title><meta name="description" content="آموزش عملی Load Balancing و Failover اینترنت در FortiGate با SD-WAN، Performance SLA، تنظیمات CLI و GUI، توزیع Source-Destination و عیب‌یابی Dual WAN."><meta name="keywords" content="FortiGate Load Balancing, FortiGate SD-WAN, FortiGate Failover, FortiGate Performance SLA, FortiGate Dual WAN, FortiGate WAN Load Balancing, SD-WAN Load Balancing, FortiOS SD-WAN"><style>${css}</style></head><body><main>${body}</main></body></html>`);
 const cliBlocks = [...markdown.matchAll(/```fortios\n([\s\S]*?)```/g)].map(m=>m[1]);
 fs.writeFileSync(path.join(root,'base-config.fortios.conf'),cliBlocks.slice(0,6).join('\n'));
 for (const [name] of images) console.log(name+'.svg + .png');
 console.log('HTML and base configuration generated. Total CLI blocks:',cliBlocks.length);
}
main().catch(e=>{console.error(e);process.exitCode=1});
