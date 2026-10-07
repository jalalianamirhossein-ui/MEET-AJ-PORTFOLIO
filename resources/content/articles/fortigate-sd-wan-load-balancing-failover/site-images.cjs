const fs = require('fs');
const path = require('path');
const root = __dirname;
const workspace = path.resolve(root, '../../../..');
const slug = 'fortigate-sd-wan-load-balancing-failover';
const bannerName = 'Banner – FortiGate SD-WAN Load Balancing.png';
const mappings = [
 ['02-dual-wan-topology.png','fortigate-generated-dual-wan-topology.png','Dual WAN Network Topology.png','توپولوژی Dual WAN با ظرفیت ۲۰۰ و ۱۰۰ Mbps؛ آدرس‌های کنار WAN نمونه Gatewayهای ISP هستند.'],
 ['03-sla-architecture.png','fortigate-generated-sla-architecture.png','FortiGate SD-WAN + Performance SLA Architecture.png','معماری SD-WAN، Performance SLA و Traffic Steering؛ دو Server نمونه در Health Check این مقاله به‌صورت Primary/Secondary استفاده می‌شوند.'],
 ['04-methods-comparison.png','fortigate-generated-methods-comparison.png','Load Balancing Methods Comparison.png','مقایسه تصویری روش‌های تقسیم بار؛ تفاوت Implicit Weight و Explicit Hash در متن توضیح داده شده است.'],
 ['05-troubleshooting-flow.png','fortigate-generated-troubleshooting-flow.png','FortiGate SD-WAN Troubleshooting Flow.png','فلوچارت عیب‌یابی اینترنت و وابستگی‌های Interface، SLA، Rule، Route، Policy و NAT.'],
];
const contentUrl = name => '/assets/img/articles/content/'+encodeURIComponent(name);
const bannerUrl = '/assets/img/articles/banners/'+encodeURIComponent(bannerName);
const assetRoot = path.join(workspace,'resources/assets/img/articles');

function integrate(html) {
 html = html.replaceAll('/assets/img/articles/banners/'+slug+'.png',bannerUrl);
 for (const [oldName,generatedName,userName,caption] of mappings) {
  html = html.replaceAll('/assets/img/articles/content/'+slug+'/'+oldName,contentUrl(generatedName));
  const escaped = contentUrl(generatedName).replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
  if (!html.includes(contentUrl(userName))) {
   html = html.replace(new RegExp('<p>\\s*(<img\\b[^>]*src="'+escaped+'"[^>]*>)\\s*</p>'),
    `<figure class="article-illustration"><img src="${contentUrl(userName)}" alt="${userName.slice(0,-4)}" loading="lazy"><figcaption>${caption}</figcaption></figure>\n<p>$1</p>`);
  }
 }
 if (!html.includes(contentUrl('fortigate-generated-banner.png'))) {
  html = html.replace(/(<article\b[^>]*class="article-body"[^>]*>)/,
   `$1\n<figure class="article-illustration"><img src="${contentUrl('fortigate-generated-banner.png')}" alt="FortiGate SD-WAN technical overview" loading="lazy"><figcaption>نمای فنی مکمل: تقسیم اتصال‌های جدید بین مسیرهای واجد شرایط و پایش کیفیت هر WAN.</figcaption></figure>`);
 }
 return html;
}

function publish() {
 const publicRoot = path.join(workspace,'public/assets/img/articles');
 for (const folder of ['banners','content']) fs.mkdirSync(path.join(publicRoot,folder),{recursive:true});
 fs.copyFileSync(path.join(assetRoot,'banners',bannerName),path.join(publicRoot,'banners',bannerName));
 const names = ['fortigate-generated-banner.png',...mappings.flatMap(m=>[m[1],m[2]])];
 for (const name of names) fs.copyFileSync(path.join(assetRoot,'content',name),path.join(publicRoot,'content',name));
 const articlePath = path.join(workspace,'resources/legacy/articles',slug+'.html');
 fs.writeFileSync(articlePath,integrate(fs.readFileSync(articlePath,'utf8')));
 return articlePath;
}

module.exports = {integrate,publish,mappings,bannerName};
if (require.main === module) console.log('Updated legacy images:',publish());
