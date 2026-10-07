"""Build MeetAJ's bilingual UniFi runbook and reproducible technical illustrations."""
from pathlib import Path
from html import escape
import json
import shutil
from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
SLUG = 'ubiquiti-unifi-wireless-mesh-network'
TITLE = {'en': 'How to Build a Ubiquiti UniFi Wireless Mesh Network', 'fa': 'راه‌اندازی Wireless Mesh Network با Ubiquiti UniFi'}
DESC = {'en': 'A production deployment runbook for UniFi mesh: adoption, Mesh Parent and Mesh Connect, 5 GHz backhaul, VLANs, roaming, validation and troubleshooting.', 'fa': 'راهنمای عملیاتی راه‌اندازی Mesh در UniFi؛ Adoption، نقش‌های Mesh Parent و Mesh Connect، بک‌هاول 5GHz، VLAN، Roaming، تست عملکرد و عیب‌یابی.'}
KEYWORDS = ['Ubiquiti', 'UniFi', 'Wireless Mesh', 'Mesh Parent', 'Mesh Connect', 'Wireless Uplink', '5 GHz Backhaul', 'VLAN', 'Network Administration']
REFS = {
 'mesh': ('115002262328-Considerations-for-Optimal-Wireless-Mesh-Networks', 'Optimal Wireless Mesh Networks', 'طراحی و تنظیم Wireless Mesh'),
 'adopt': ('360012622613-UniFi-Device-Adoption', 'Device Adoption', 'Adoption تجهیزات'),
 'wifi': ('26136823938583-Creating-UniFi-WiFi-SSIDs', 'Creating UniFi WiFi SSIDs', 'ایجاد SSID در UniFi'),
 'rf': ('221029967-Optimizing-WiFi-Connectivity-and-Reducing-Latency', 'WiFi Connectivity and Latency', 'پایداری WiFi و کاهش تأخیر'),
 'speed': ('360012947634-Maximizing-Wireless-Speeds', 'Maximizing Wireless Speeds', 'بهینه‌سازی سرعت و اسکن RF'),
 'vlan': ('9761080275607-Creating-Virtual-Networks-VLANs', 'Creating Virtual Networks (VLANs)', 'ایجاد Virtual Network و VLAN'),
 'port': ('26136855808919-Switch-Port-VLAN-Assignment-Trunk-Access-Ports', 'Switch Port VLAN Assignment', 'VLAN پورت‌های Trunk و Access'),
 'guest': ('23948850278295-Best-Practices-Guest-WiFi', 'Best Practices: Guest WiFi', 'امنیت شبکه مهمان'),
 'isolated': ('16230412350487-UniFi-Isolated-Devices', 'UniFi Isolated Devices', 'عیب‌یابی AP با وضعیت Isolated'),
}
parts, toc = [], []
def dual(tag, en, fa, attrs=''):
    if tag in ('a', 'figcaption'):
        return f'<{tag} {attrs}>'+dual('span', en, fa)+f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en, quote=True)}" data-fa="{escape(fa, quote=True)}">{escape(en)}</{tag}>'
def p(en, fa): parts.append(dual('p', en, fa))
def section(id, en, fa):
    if toc: parts.append('</section>')
    toc.append((id, en, fa))
    parts.append(f'<section id="{id}" class="article-section">'+dual('h2', en, fa))
def items(pairs, ordered=False):
    tag = 'ol' if ordered else 'ul'
    parts.append(f'<{tag}>'+''.join(dual('li', a, b) for a,b in pairs)+f'</{tag}>')
def cite(key):
    path,en,fa = REFS[key]
    parts.append('<p class="article-reference">'+dual('a', 'Ubiquiti: '+en, 'مرجع Ubiquiti: '+fa, f'href="https://help.ui.com/hc/en-us/articles/{path}" rel="noopener"')+'</p>')
def code(text, language='text'):
    parts.append(f'<pre dir="ltr"><code class="language-{language}">{escape(text)}</code></pre>')
IMAGES = {
 'banner': ('banners', 'Ubiquiti-UniFi-Wireless-Mesh-Network.png', 'UniFi mesh: one wired parent and two wireless APs', 'شبکه UniFi Mesh با یک Parent کابلی و دو AP بی‌سیم'),
 'topology': ('content', 'UniFi-Mesh-Network-Topology.png', 'Gateway supplies DHCP; both mesh APs uplink directly to the wired parent', 'Gateway سرویس DHCP می‌دهد؛ هر دو Mesh AP مستقیم به Parent کابلی متصل‌اند'),
 'roles': ('content', 'UniFi-Mesh-Parent-Connect-Roles.png', 'Wired AP: Mesh Parent enabled. Wireless APs: Mesh Connect enabled', 'AP کابلی: فعال‌سازی Mesh Parent؛ APهای بی‌سیم: فعال‌سازی Mesh Connect'),
 'rf': ('content', 'UniFi-Mesh-RF-Planning-Backhaul.png', '5 GHz backhaul shares airtime; target parent link RSSI of -60 dBm or better', 'بک‌هاول 5GHz زمان رادیویی را به اشتراک می‌گذارد؛ هدف RSSI لینک Parent برابر ‎-60 dBm یا بهتر است'),
 'trouble': ('content', 'UniFi-Mesh-Troubleshooting-Flow.png', 'Diagnose power, management connectivity, wireless uplink, VLAN and performance in order', 'عیب‌یابی به‌ترتیب برق، ارتباط مدیریتی، Wireless Uplink، VLAN و عملکرد'),
}
def figure(key):
    folder,name,en,fa = IMAGES[key]
    with Image.open(ROOT/'resources/assets/img/articles'/folder/name) as img:
        width,height = img.size
    attrs = ' '.join(f'{a}="{escape(v, quote=True)}"' for a,v in [('alt',en),('title',en),('data-en-alt',en),('data-fa-alt',fa),('data-en-title',en),('data-fa-title',fa)])
    parts.append(f'<figure><img src="/assets/img/articles/{folder}/{name}" width="{width}" height="{height}" loading="lazy" decoding="async" {attrs}>'+dual('figcaption', en, fa)+'</figure>')

section('wireless-mesh', '1. What Is Wireless Mesh in UniFi?', '۱. Wireless Mesh در UniFi چیست؟')
p('UniFi wireless meshing extends the LAN through a radio uplink between APs. A client joins a normal WiFi SSID; its traffic reaches the wired network through the mesh AP and its parent. The SSID is the user access network, while Wireless Uplink is the AP-to-AP transport.', 'Wireless Meshing در UniFi، شبکه LAN را از طریق لینک رادیویی بین APها گسترش می‌دهد. کاربر به SSID معمولی متصل می‌شود و ترافیک او از Mesh AP و Parent به شبکه کابلی می‌رسد. SSID شبکه دسترسی کاربر است و Wireless Uplink ارتباط انتقال ترافیک بین APهاست.')
p('This runbook is for Network Administrators and System Engineers deploying three compatible UniFi APs under one UniFi Network Application. It describes a verified documentation baseline and a target-side acceptance procedure; no live UniFi hardware measurements are claimed.', 'این Runbook برای Network Administrator و System Engineer است که سه AP سازگار UniFi را زیر یک UniFi Network Application راه‌اندازی می‌کنند. مقاله شامل مبنای مستنداتی بررسی‌شده و روش پذیرش در شبکه مقصد است؛ نتیجه آزمایش روی تجهیزات واقعی UniFi ادعا نمی‌شود.')
cite('mesh')

section('when-to-mesh', '2. When to Use Mesh and When to Use Ethernet', '۲. Mesh چه زمانی مناسب است و چه زمانی Ethernet لازم است؟')
p('Use mesh to reach a small coverage gap where pulling Ethernet is impractical, provided a suitable parent link exists. Cable APs for predictable capacity, busy offices, latency-sensitive applications and permanent infrastructure. Wireless coverage alone does not establish that a site has enough capacity.', 'Mesh برای پوشش یک محدوده کوچک که کابل‌کشی Ethernet در آن عملی نیست و لینک مناسب به Parent دارد مفید است. برای ظرفیت قابل پیش‌بینی، دفاتر پرترافیک، برنامه‌های حساس به تأخیر و زیرساخت دائمی، APها را کابلی کنید. وجود پوشش WiFi به‌تنهایی ظرفیت کافی سایت را اثبات نمی‌کند.')
p('Each retransmission consumes radio airtime. Ubiquiti warns of roughly halved throughput per wireless hop; treat this as a planning warning, not a guaranteed benchmark. Compare measured application goodput with your wired baseline and test both children together.', 'هر بار ارسال مجدد در مسیر Mesh، Airtime مصرف می‌کند. Ubiquiti درباره کاهش تقریبی توان عملیاتی به نصف در هر Wireless Hop هشدار می‌دهد؛ این هشدار طراحی است و Benchmark تضمین‌شده نیست. Goodput واقعی برنامه را با مبنای کابلی مقایسه کنید و هر دو AP فرزند را هم‌زمان تست کنید.')
cite('speed')

section('architecture', '3. Parent AP and Mesh AP Architecture', '۳. معماری Parent AP و Mesh AP')
figure('topology')
code('UniFi Network Application: manages all three APs\nGateway (routing + DHCP) -- Ethernet -- AP-PARENT\n                                      |\n                                      +~~ wireless ~~ AP-MESH-01\n                                      +~~ wireless ~~ AP-MESH-02\nShared user SSID on all three APs; one wireless hop per child.\nWireless APs still require local electrical power / compatible PoE.')
p('The Cloud Gateway may host Network, or a separate supported Network host may manage an independent Gateway. The parent has a real Ethernet path to the Gateway. Both children should connect directly to that parent. A chain through another child adds a hop and shares that child’s upstream capacity.', 'Cloud Gateway می‌تواند میزبان Network باشد یا یک میزبان پشتیبانی‌شده جدا، Gateway مستقل را مدیریت کند. Parent مسیر Ethernet واقعی تا Gateway دارد. هر دو فرزند بهتر است مستقیم به همین Parent متصل شوند. اتصال زنجیره‌ای از طریق فرزند دیگر، Hop اضافه می‌کند و ظرفیت Upstream آن فرزند را نیز مصرف می‌کند.')
p('The APs bridge user traffic; Gateway DHCP allocates addresses in the selected user network. UniFi Network is the management plane, not the DHCP relay or a per-packet user-traffic tunnel endpoint. Keep the AP management network reachable independently of guest restrictions.', 'APها ترافیک کاربر را Bridge می‌کنند و DHCP روی Gateway در شبکه انتخاب‌شده به کاربر آدرس می‌دهد. UniFi Network بخش مدیریت است و DHCP Relay یا مقصد تونل تک‌تک بسته‌های کاربران نیست. دسترسی شبکه مدیریت APها باید مستقل از محدودیت‌های Guest حفظ شود.')

section('prerequisites', '4. Prerequisites', '۴. پیش‌نیازها')
items([
('Confirm mesh support for the exact AP models and firmware. Do not infer support merely from a UniFi product name; Standalone Mode does not provide this managed mesh deployment.', 'پشتیبانی Mesh را برای مدل دقیق AP و Firmware بررسی کنید. صرف نام UniFi اثبات پشتیبانی نیست؛ این طراحی Mesh مدیریت‌شده در Standalone Mode قابل اجرا نیست.'),
('Use supported Network and AP releases; record versions, save a Network backup and document the current port/VLAN settings before changes.', 'از نسخه‌های پشتیبانی‌شده Network و AP استفاده کنید؛ نسخه‌ها را ثبت کنید، Backup از Network بگیرید و وضعیت پورت و VLAN را پیش از تغییر مستند کنید.'),
('Provide model-compatible PoE and power budget for each AP. For a wireless child, the injector LAN/data input stays disconnected from the LAN; its PoE output powers the AP.', 'برای هر AP، PoE سازگار با مدل و توان کافی تأمین کنید. در AP بی‌سیم، ورودی LAN/Data انژکتور به LAN وصل نمی‌شود و خروجی PoE فقط AP را تغذیه می‌کند.'),
('Verify the parent’s switch port, management DHCP/DNS and access to Network; reserve management addresses if your operational policy requires predictable IPs.', 'پورت سوئیچ Parent، DHCP/DNS مدیریت و دسترسی به Network را بررسی کنید؛ در صورت نیاز عملیاتی به IP ثابت، DHCP Reservation تعریف کنید.'),
('Select the correct regulatory country and legal channels. Survey parent-to-child locations before final mounting, with doors closed and normal sources of interference active.', 'کشور Regulatory صحیح و کانال مجاز را انتخاب کنید. پیش از نصب نهایی، محل Parent و فرزندان را با درهای بسته و منابع معمول تداخل فعال بررسی کنید.'),
])
cite('adopt')

section('adoption', '5. Adopt the Devices', '۵. Adoption تجهیزات')
items([
('Connect and power AP-PARENT through Ethernet. In UniFi Devices, select the discovered AP and adopt it; wait until it is Online and provisioning completes.', 'AP-PARENT را با Ethernet وصل و روشن کنید. در UniFi Devices، AP کشف‌شده را انتخاب و Adopt کنید؛ تا Online شدن و پایان Provisioning صبر کنید.'),
('For a controlled rollout, temporarily wire each child to the same management network, adopt it, apply firmware updates and name it AP-MESH-01 or AP-MESH-02.', 'برای استقرار قابل کنترل، هر فرزند را موقتاً به همان شبکه مدیریت کابل بزنید، Adopt کنید، Firmware را به‌روز کنید و نام AP-MESH-01 یا AP-MESH-02 بدهید.'),
('Apply global meshing and parent roles below. For the planned conversion, provision Mesh Connect on the child while management access is still available, then promptly disconnect its LAN data uplink and retain PoE power. Do not leave it operating with Mesh Connect and a wired uplink. Wait for an Online wireless uplink before moving it to its final location.', 'Meshing سراسری و نقش Parent را طبق مراحل بعد اعمال کنید. برای تبدیل برنامه‌ریزی‌شده، تا دسترسی مدیریت برقرار است Mesh Connect را روی فرزند Provision کنید، سپس بلافاصله LAN Data Uplink آن را قطع و برق PoE را حفظ کنید. فرزند را با Mesh Connect فعال و Uplink کابلی در حالت عملیاتی رها نکنید. پیش از انتقال به محل نهایی، Online شدن با Uplink بی‌سیم را بررسی کنید.'),
('Wireless adoption is also supported: enable global meshing and the parent role, power a factory-default compatible child near the parent, and adopt it when discovered. If it cannot be discovered, use temporary Ethernet to isolate adoption from RF issues.', 'Adoption بی‌سیم نیز پشتیبانی می‌شود: Meshing سراسری و نقش Parent را فعال کنید، فرزند سازگار با تنظیم کارخانه را نزدیک Parent روشن کنید و پس از کشف Adopt کنید. اگر کشف نشد، با Ethernet موقت، مشکل Adoption را از RF جدا کنید.'),
], True)
p('The general Device Adoption page currently names Mesh Connect on the uplink AP. The dedicated mesh guide explicitly assigns Mesh Parent to the uplink source and warns against Mesh Connect on a wired AP. This runbook follows that role-specific guide; do not enable Mesh Connect on the wired parent to work around discovery.', 'صفحه عمومی Device Adoption در حال حاضر Mesh Connect را برای AP بالادستی نام می‌برد. راهنمای اختصاصی Mesh، منبع Uplink را Mesh Parent معرفی می‌کند و درباره Mesh Connect روی AP کابلی هشدار می‌دهد. این مقاله از راهنمای اختصاصی نقش‌ها پیروی می‌کند؛ برای رفع مشکل کشف، Mesh Connect را روی Parent کابلی فعال نکنید.')
cite('adopt'); cite('mesh')

section('ssid', '6. Create the Shared SSID', '۶. ایجاد SSID مشترک')
p('In Settings → WiFi, create a WiFi, choose its SSID and authentication, select the intended Network and include all three APs in its broadcasting selection. Save and verify the same SSID, security and user network on each AP. Start with compatible security defaults; verify older devices before changing authentication settings.', 'در Settings → WiFi یک WiFi بسازید، SSID و احراز هویت را انتخاب کنید، Network هدف را تعیین کنید و هر سه AP را در انتخاب Broadcast قرار دهید. ذخیره کنید و یکسان بودن SSID، امنیت و شبکه کاربران را روی هر AP تأیید کنید. از پیش‌فرض امنیتی سازگار شروع کنید و پیش از تغییر Authentication، دستگاه‌های قدیمی را بررسی کنید.')
p('One ordinary SSID maps to one VLAN. The base scenario uses one shared Corporate SSID. Guest segmentation can add a separate Guest SSID mapped to its own VLAN. A single SSID with different user VLANs needs supported PPSK or RADIUS assignment and is a separate authentication design, not an automatic result of mesh.', 'هر SSID معمولی به یک VLAN نگاشت می‌شود. سناریوی پایه یک SSID مشترک Corporate دارد. برای تفکیک Guest می‌توان SSID جدا با VLAN اختصاصی اضافه کرد. تخصیص VLANهای متفاوت به کاربران یک SSID به PPSK یا RADIUS پشتیبانی‌شده نیاز دارد و طراحی احراز هویت جداگانه است؛ نتیجه خودکار Mesh نیست.')
cite('wifi'); cite('vlan')

section('enable-meshing', '7. Enable Wireless Meshing', '۷. فعال‌سازی Wireless Meshing')
code('Settings → WiFi → Wireless Meshing → Enable')
p('Enable the site-level setting and apply changes. Verify device provisioning finishes. The paths here follow the current Help Center interface labels; option grouping can differ with Network release and AP model. If the setting is absent, check model support, permissions and the installed release before using older UI instructions.', 'تنظیم سراسری سایت را فعال و تغییرات را اعمال کنید. پایان Provisioning تجهیزات را بررسی کنید. مسیرها مطابق برچسب‌های فعلی Help Center هستند؛ دسته‌بندی گزینه‌ها ممکن است با نسخه Network و مدل AP متفاوت باشد. اگر گزینه موجود نبود، پشتیبانی مدل، سطح دسترسی و نسخه نصب‌شده را بررسی کنید و به مسیرهای UI قدیمی متوسل نشوید.')
cite('mesh')

section('mesh-parent', '8. Configure the Wired Mesh Parent', '۸. تنظیم Mesh Parent روی AP کابلی')
figure('roles')
p('The supplied illustrations explain the roles; their interface panels are conceptual, not verified screenshots of the current application. Follow the documented GUI paths in the text. Do not apply illustrated fields such as a parent limit, preferred-parent selector or uplink threshold without verifying that the installed release actually provides them.', 'تصاویر ارائه‌شده برای توضیح نقش‌ها هستند؛ پنل‌های رابط کاربری داخل آن‌ها مفهومی‌اند و Screenshot تأییدشده نسخه فعلی محسوب نمی‌شوند. مسیرهای مستند GUI در متن را دنبال کنید. فیلدهای تصویری مانند محدودیت Parent، انتخاب Preferred Parent یا آستانه Uplink را بدون تأیید وجودشان در نسخه نصب‌شده اعمال نکنید.')
code('UniFi Devices → AP-PARENT → Settings → Mesh Parent → Enable\nUniFi Devices → AP-PARENT → Settings → Mesh Connect → Disable')
p('Confirm Ethernet uplink and actual Gateway reachability first. Mesh Parent allows downstream APs to use this AP as an uplink source. In this one-parent design, enable this role on AP-PARENT. Mesh Connect stays off while it has a wired uplink.', 'ابتدا Ethernet Uplink و دسترسی واقعی به Gateway را تأیید کنید. Mesh Parent اجازه می‌دهد APهای پایین‌دست از این AP به‌عنوان منبع Uplink استفاده کنند. در طراحی تک‌Parent، این نقش را روی AP-PARENT فعال کنید. تا زمانی که Uplink کابلی دارد، Mesh Connect خاموش می‌ماند.')
cite('mesh')

section('mesh-connect', '9. Configure Mesh Connect on Wireless APs', '۹. تنظیم Mesh Connect روی APهای Wireless')
code('UniFi Devices → AP-MESH-01 → Settings → Mesh Connect → Enable\nUniFi Devices → AP-MESH-02 → Settings → Mesh Connect → Enable')
p('For this direct-star topology, leave Mesh Parent disabled on both children to avoid using them as intermediate parents. Keep each child powered with no LAN data uplink. Validate the parent identity after provisioning. When converting a child back to Ethernet, disable its Mesh Connect role and recheck the topology.', 'در این توپولوژی ستاره‌ای مستقیم، Mesh Parent را روی هر دو فرزند خاموش نگه دارید تا نقش Parent میانی نداشته باشند. برق هر فرزند برقرار و LAN Data Uplink قطع باشد. پس از Provisioning، هویت Parent را بررسی کنید. هنگام بازگرداندن فرزند به Ethernet، نقش Mesh Connect آن را خاموش و توپولوژی را دوباره کنترل کنید.')
cite('mesh')

section('wireless-uplink', '10. Verify Wireless Uplink', '۱۰. بررسی Wireless Uplink')
p('Open UniFi Devices and select each child. Inspect its displayed uplink/connection details and confirm a wireless connection to AP-PARENT, Online state and no intermediate AP. Cross-check Topology; do not treat a diagram alone as proof. Record parent name/MAC, backhaul band, channel and reported signal where the model exposes them.', 'در UniFi Devices هر فرزند را انتخاب کنید. جزئیات نمایش‌داده‌شده Uplink/Connection را بررسی کنید و ارتباط بی‌سیم با AP-PARENT، وضعیت Online و نبود AP میانی را تأیید کنید. با Topology تطبیق دهید؛ دیاگرام به‌تنهایی اثبات سلامت نیست. نام/MAC Parent، باند Backhaul، کانال و سیگنال گزارش‌شده را در صورت نمایش توسط مدل ثبت کنید.')
p('Join the shared SSID near each child, verify its serving AP in the client details, obtain a Gateway DHCP lease and reach the intended services. This checks both AP management and the client data path; an Online AP alone is not an acceptance test.', 'نزدیک هر فرزند به SSID مشترک وصل شوید، AP سرویس‌دهنده را در جزئیات Client بررسی کنید، Lease از DHCP Gateway بگیرید و سرویس‌های موردنظر را تست کنید. این کار هم مدیریت AP و هم مسیر دیتای کاربر را می‌سنجد؛ Online بودن AP به‌تنهایی معیار پذیرش نیست.')

section('rf-backhaul', '11. RF Design and 5 GHz Backhaul', '۱۱. طراحی RF و Backhaul روی 5GHz')
figure('rf')
p('The RF illustration also shows a chained extension and sample channels/signal labels. The deployment in this runbook uses two direct children instead. Its acceptance target remains -60 dBm or better; pictured channel numbers and weaker signal categories are not configuration prescriptions.', 'تصویر RF یک گسترش زنجیره‌ای و برچسب‌های نمونه کانال/سیگنال را هم نشان می‌دهد. استقرار این مقاله دو فرزند مستقیم دارد. هدف پذیرش همچنان ‎-60 dBm یا بهتر است؛ شماره کانال‌ها و دسته‌بندی سیگنال ضعیف‌تر داخل تصویر دستور تنظیمات نیستند.')
p('UniFi mesh primarily uses 5 GHz. Plan and verify that backhaul on supported equipment; do not assume a dedicated backhaul radio or a configurable 6 GHz uplink on every model. The radio can carry both clients and backhaul, so a good client PHY rate does not guarantee spare upstream airtime.', 'Mesh در UniFi عمدتاً از 5GHz استفاده می‌کند. روی تجهیزات پشتیبانی‌شده این Backhaul را طراحی و تأیید کنید؛ وجود رادیوی اختصاصی Backhaul یا Uplink قابل تنظیم 6GHz را برای همه مدل‌ها فرض نکنید. رادیو می‌تواند هم کاربران و هم Backhaul را حمل کند، بنابراین PHY Rate خوب کاربر تضمین‌کننده Airtime آزاد Upstream نیست.')
p('Locate a child inside reliable parent coverage, before the dead zone. Test mounting height, orientation and obstructions with the final installation conditions. Increasing transmit power alone cannot fix interference, blocked paths or an unbalanced link.', 'فرزند را داخل پوشش قابل اعتماد Parent و پیش از محدوده فاقد سیگنال نصب کنید. ارتفاع، جهت نصب و موانع را در شرایط نهایی آزمایش کنید. افزایش توان ارسال به‌تنهایی تداخل، مسیر مسدود یا لینک نامتوازن را اصلاح نمی‌کند.')
cite('mesh')

section('channels', '12. Channel Planning', '۱۲. Channel Planning')
p('Use UniFi Devices → select AP → AirView → Environment → Airtime Scan when supported. A Full scan interrupts clients on that radio, so schedule it. Record noise and utilization, not just nearby SSID counts. Change one RF variable at a time and repeat the same load test.', 'در صورت پشتیبانی از UniFi Devices → انتخاب AP → AirView → Environment → Airtime Scan استفاده کنید. Full Scan اتصال کاربران آن رادیو را قطع می‌کند؛ زمان‌بندی مناسب داشته باشید. Noise و Utilization را ثبت کنید، نه فقط تعداد SSIDهای اطراف. هر بار یک متغیر RF را تغییر دهید و همان تست بار را تکرار کنید.')
p('For an office baseline, use 20 MHz on 2.4 GHz and assess 40 MHz on 5 GHz; test 80 MHz only where survey and load justify it. Adjust radios in Radios → select radios → Edit Radios or UniFi Devices → select AP → Settings. These are starting choices, not universal throughput settings.', 'برای مبنای دفتر، 20MHz روی 2.4GHz و 40MHz روی 5GHz را ارزیابی کنید؛ فقط اگر Survey و تست بار توجیه کرد، 80MHz را تست کنید. تنظیمات در Radios → انتخاب رادیوها → Edit Radios یا UniFi Devices → انتخاب AP → Settings قرار دارند. این‌ها انتخاب شروع هستند و تنظیم تضمینی سرعت برای همه محیط‌ها نیستند.')
p('A wireless uplink must use a channel compatible with its parent. Do not assign unrelated 5 GHz channels to members sharing that backhaul as if they were independent wired APs. Reuse planning across wired cells remains useful; verify the actual mesh channel after every change. Use legal non-overlapping 2.4 GHz channels for the country; 1/6/11 is the usual 20 MHz plan.', 'Wireless Uplink باید کانالی سازگار با Parent داشته باشد. اعضای دارای Backhaul مشترک را مثل APهای کابلی مستقل روی کانال‌های نامرتبط 5GHz قرار ندهید. برنامه‌ریزی بازاستفاده کانال بین سلول‌های کابلی همچنان مفید است؛ پس از هر تغییر، کانال واقعی Mesh را بررسی کنید. کانال‌های قانونی و غیرهمپوشان 2.4GHz کشور را به‌کار ببرید؛ 1/6/11 الگوی رایج با عرض 20MHz است.')
p('DFS can offer quieter spectrum, but radar detection may force channel changes and disrupt the backhaul. Validate regional and client/AP compatibility and inspect events when a link drops. Do not prescribe a fixed DFS channel without a site survey.', 'DFS می‌تواند طیف خلوت‌تری فراهم کند، اما تشخیص Radar ممکن است تغییر کانال و اختلال Backhaul ایجاد کند. سازگاری منطقه، Client و AP را بررسی کنید و هنگام قطع لینک، Eventها را بخوانید. بدون Survey سایت، کانال DFS ثابت توصیه نکنید.')
cite('speed'); cite('rf')

section('signal', '13. RSSI and Signal Strength', '۱۳. RSSI و Signal Strength')
p('Target parent-to-child RSSI at -60 dBm or better: -55 dBm is stronger than -60 dBm, and -70 dBm is weaker. Measure the AP uplink, not a phone’s client signal. Capture readings under normal occupancy and load; a single quiet-time sample cannot establish link stability.', 'هدف RSSI بین Parent و فرزند، ‎-60 dBm یا بهتر است؛ ‎-55 dBm قوی‌تر از ‎-60 dBm و ‎-70 dBm ضعیف‌تر است. Uplink خود AP را اندازه بگیرید، نه سیگنال Client روی تلفن. در زمان حضور عادی کاربران و زیر بار نیز اندازه‌گیری کنید؛ یک نمونه در محیط خلوت، پایداری لینک را اثبات نمی‌کند.')
p('Assess retries, channel use, latency and loss alongside RSSI. Client Minimum RSSI is a different setting that disconnects weak clients; it does not strengthen the mesh uplink. Avoid applying a blanket threshold to conceal a placement problem.', 'در کنار RSSI، Retry، استفاده کانال، تأخیر و Loss را بررسی کنید. Client Minimum RSSI تنظیم متفاوتی است که Client ضعیف را قطع می‌کند و Uplink Mesh را قوی‌تر نمی‌کند. آستانه یکسان را برای پنهان کردن مشکل جانمایی اعمال نکنید.')
cite('mesh')

section('vlans', '14. VLANs Across the Mesh', '۱۴. VLAN در شبکه Mesh')
items([
('In Settings → Networks, create Corporate and, if needed, Guest virtual networks on the UniFi Gateway. Use approved VLAN IDs, distinct non-overlapping subnets and Gateway DHCP scopes; check exclusions and available leases.', 'در Settings → Networks شبکه‌های مجازی Corporate و در صورت نیاز Guest را روی UniFi Gateway بسازید. VLAN ID مصوب، Subnetهای مجزا و غیرهمپوشان و Scopeهای DHCP Gateway را تعریف کنید؛ Exclusion و Lease آزاد را بررسی کنید.'),
('In Settings → WiFi, map each SSID to its intended Network. The supported mesh path bridges the selected client network; it does not create a new DHCP server on each child.', 'در Settings → WiFi هر SSID را به Network موردنظر نگاشت کنید. مسیر Mesh پشتیبانی‌شده شبکه انتخاب‌شده کاربر را Bridge می‌کند و روی هر فرزند DHCP Server جدید نمی‌سازد.'),
('On the parent’s upstream switch port, use Ports → select port → Native VLAN / Network and Tagged VLAN Management. Preserve the actual management network and allow Corporate/Guest tags along every upstream link.', 'روی پورت بالادستی سوئیچ Parent، از Ports → انتخاب پورت → Native VLAN / Network و Tagged VLAN Management استفاده کنید. شبکه واقعی مدیریت را حفظ و Tagهای Corporate/Guest را در همه لینک‌های بالادستی مجاز کنید.'),
('Do not set a tagged SSID network as the AP port’s native network; Ubiquiti warns that this breaks connectivity except for its VLAN 1 case. For a separate management VLAN, align AP IP Settings → Network Override with the intended tagged management path before changing it.', 'شبکه SSID دارای Tag را Native پورت AP نکنید؛ Ubiquiti به‌جز حالت VLAN 1 درباره اختلال اتصال هشدار می‌دهد. برای VLAN مدیریت جدا، پیش از تغییر، IP Settings → Network Override روی AP را با مسیر Tagged مدیریت موردنظر هماهنگ کنید.'),
('For Guest, enable Network Isolation in Settings → Networks and review Client Device Isolation in WiFi settings if client-to-client separation is required. Verify access policy through actual guest clients; a VLAN name alone provides no security boundary.', 'برای Guest، Network Isolation را در Settings → Networks فعال کنید و اگر جداسازی کاربران از یکدیگر لازم است، Client Device Isolation را در تنظیمات WiFi بررسی کنید. Policy دسترسی را با Client واقعی Guest تست کنید؛ نام VLAN به‌تنهایی مرز امنیتی نمی‌سازد.'),
], True)
p('Validate each VLAN from the wired parent and from both children: expected subnet, Gateway, DNS, permitted internal services and blocked guest-to-corporate access. If only wireless clients fail, compare the child’s SSID/network assignment and uplink; if all APs fail, inspect Gateway DHCP and upstream tagging first.', 'هر VLAN را از Parent کابلی و هر دو فرزند تست کنید: Subnet، Gateway، DNS، سرویس‌های داخلی مجاز و مسدود بودن Guest به Corporate. اگر فقط کاربران بی‌سیم فرزندان مشکل دارند، نگاشت SSID/Network و Uplink فرزند را مقایسه کنید؛ اگر همه APها مشکل دارند، ابتدا DHCP Gateway و Tagging بالادستی را بررسی کنید.')
p('Client Device Isolation covers clients on the same AP. For separation across APs, assess supported switch Device Isolation (ACL) and the complete forwarding path, including wireless children. Test guest-to-guest access on the same AP and across different APs; do not claim complete isolation from the WiFi toggle alone.', 'Client Device Isolation کاربران روی همان AP را پوشش می‌دهد. برای جداسازی بین APها، قابلیت پشتیبانی‌شده Device Isolation (ACL) سوئیچ و کل مسیر انتقال، شامل فرزندان بی‌سیم را ارزیابی کنید. دسترسی Guest به Guest را روی یک AP و بین APهای متفاوت تست کنید؛ گزینه WiFi به‌تنهایی اثبات جداسازی کامل نیست.')
cite('vlan'); cite('port'); cite('guest')

section('roaming', '15. Client Roaming Between APs', '۱۵. Roaming کاربران بین APها')
p('Roaming is a client decision. A common SSID, consistent authentication and the same user VLAN permit movement without deliberately changing the subnet, but do not guarantee a lossless handoff. Coverage overlap and usable upstream capacity matter on every AP.', 'تصمیم Roaming را Client می‌گیرد. SSID مشترک، احراز هویت یکسان و VLAN کاربر یکسان امکان جابه‌جایی بدون تغییر عمدی Subnet را می‌دهند، اما Handoff بدون Loss را تضمین نمی‌کنند. همپوشانی پوشش و ظرفیت قابل استفاده Upstream روی همه APها مهم است.')
p('Review Settings → WiFi → select SSID → Advanced for Fast Roaming and BSS Transition. Test the actual client fleet, especially voice handsets and older devices, before rollout. Walk the intended route during an active call and continuous ping; correlate the serving AP change with loss and latency. Mesh reconnection is an AP backhaul event, not client roaming.', 'در Settings → WiFi → انتخاب SSID → Advanced گزینه‌های Fast Roaming و BSS Transition را بررسی کنید. پیش از استقرار عمومی، Clientهای واقعی به‌ویژه تلفن VoIP و دستگاه قدیمی را تست کنید. هنگام تماس فعال و Ping پیوسته مسیر حرکت را طی کنید؛ تغییر AP سرویس‌دهنده را با Loss و تأخیر تطبیق دهید. اتصال مجدد Mesh رخداد Backhaul خود AP است و Roaming کاربر نیست.')
cite('rf')

section('performance', '16. Performance Testing and Acceptance', '۱۶. Performance Testing و معیار پذیرش')
p('Place a test server on the wired LAN and first measure wired client-to-server performance. Repeat near the parent, near each child and with both children active. Keep server, client, test duration and application settings constant. An internet speed test also measures ISP and WAN conditions, so it cannot isolate mesh performance.', 'یک Test Server روی LAN کابلی قرار دهید و ابتدا عملکرد Client کابلی به Server را بسنجید. تست را کنار Parent، کنار هر فرزند و با هر دو فرزند فعال تکرار کنید. Server، Client، مدت و تنظیمات تست ثابت بمانند. تست سرعت اینترنت شرایط ISP و WAN را هم اندازه می‌گیرد و نمی‌تواند عملکرد Mesh را جدا کند.')
code('# On the wired test server (iperf3 installed):\niperf3 -s\n\n# On a LAN/WiFi test client: replace the example with the server IP\niperf3 -c <wired-server-ip> -t 30\niperf3 -c <wired-server-ip> -t 30 -R\n\n# Windows client: replace with the actual Gateway address\nping -n 100 <gateway-ip>\nipconfig /all', 'text')
p('Angle-bracket values are placeholders, not runnable addresses. Install iperf3 separately, allow its TCP 5201 only between the test endpoints and run one test at a time for baseline measurements. The commands are client/server diagnostics, not a UniFi AP CLI configuration. Reverse mode measures the opposite traffic direction.', 'مقادیر داخل علامت زاویه جایگزین‌پذیرند و آدرس قابل اجرا نیستند. iperf3 را جدا نصب کنید، TCP 5201 را فقط بین دو Endpoint تست مجاز کنید و برای مبنا، تست‌ها را یکی‌یکی اجرا کنید. این فرمان‌ها عیب‌یابی روی Client/Server هستند و تنظیم CLI روی UniFi AP نیستند. حالت Reverse جهت مخالف ترافیک را می‌سنجد.')
p('Record per child: parent identity, hop count, RSSI, channel/width, connected users, goodput in both directions, loss, latency distribution and roaming interruptions. Agree application throughput and latency targets before release. As design gates, require both children on the intended one-hop parent, an approximately -60 dBm-or-better link under normal conditions, correct leases on every VLAN and guest policy passing. Repeat under expected concurrent load.', 'برای هر فرزند، هویت Parent، Hop، RSSI، کانال/عرض، کاربران متصل، Goodput در دو جهت، Loss، توزیع تأخیر و وقفه Roaming را ثبت کنید. پیش از بهره‌برداری، هدف سرعت و تأخیر برنامه را مشخص کنید. معیار طراحی شامل اتصال یک‌Hop هر دو فرزند به Parent موردنظر، لینک حدود ‎-60 dBm یا بهتر در شرایط عادی، Lease صحیح روی همه VLANها و موفقیت Policy مهمان است. تست را با بار هم‌زمان مورد انتظار تکرار کنید.')
p('These commands require iperf3, not UniFi shell access. They are based on the upstream iperf3 invocation; practical acceptance targets are engineering recommendations and must be agreed for this site.', 'این فرمان‌ها به iperf3 نیاز دارند، نه دسترسی Shell تجهیزات UniFi. شکل اجرای آن‌ها مبتنی بر مستندات اصلی iperf3 است؛ هدف‌های عملی پذیرش توصیه مهندسی هستند و باید برای همین سایت توافق شوند.')
parts.append('<p>'+dual('a','ESnet: iperf3 documentation','مستندات اصلی iperf3 از ESnet','href="https://software.es.net/iperf/invoking.html" rel="noopener"')+'</p>')

section('troubleshooting', '17. Troubleshooting', '۱۷. عیب‌یابی')
figure('trouble')
TROUBLE = [
('AP Offline', 'AP با وضعیت Offline', 'Check PoE LEDs, injector power, parent Ethernet and Gateway reachability. Compare the last event with power or switch changes. Bring the child near the parent; if still offline, temporarily wire it to check management connectivity before RF tuning.', 'LED و برق PoE، انژکتور، Ethernet Parent و دسترسی Gateway را بررسی کنید. آخرین Event را با تغییر برق یا سوئیچ تطبیق دهید. فرزند را نزدیک Parent ببرید؛ اگر همچنان Offline است، موقتاً کابلی کنید تا پیش از تنظیم RF، ارتباط مدیریت بررسی شود.'),
('Adoption Failed', 'Adoption ناموفق', 'Verify discovery on the intended management network, previous controller ownership, supported firmware and access to Network. Use temporary Ethernet and finish adoption near the parent. Factory reset only after confirming ownership and preserving required configuration.', 'کشف در شبکه مدیریت صحیح، مالکیت Controller قبلی، Firmware پشتیبانی‌شده و دسترسی Network را بررسی کنید. با Ethernet موقت و نزدیک Parent، Adoption را تکمیل کنید. Factory Reset فقط پس از تأیید مالکیت و حفظ تنظیمات لازم انجام شود.'),
('Isolated AP', 'AP با وضعیت Isolated', 'An AP can be visible through another AP while unable to reach Network on its management VLAN. Check recent VLAN/firewall changes and move it closer. If recovery fails, follow Ubiquiti’s reset, UniFi Devices → AP → Settings → Remove, re-adopt sequence; temporary wiring and firmware update are the next recovery path.', 'AP ممکن است از طریق AP دیگر دیده شود اما روی VLAN مدیریت به Network دسترسی نداشته باشد. تغییر VLAN/Firewall را بررسی و AP را نزدیک‌تر کنید. اگر بازیابی نشد، ترتیب رسمی Reset، سپس UniFi Devices → AP → Settings → Remove و Adoption دوباره را دنبال کنید؛ کابل موقت و به‌روزرسانی Firmware مسیر بعدی بازیابی است.'),
('Weak Mesh Signal', 'سیگنال ضعیف Mesh', 'Read parent-link RSSI, not client RSSI. Move the child toward the parent or remove obstructions, then retest noise and load. If the -60 dBm design target cannot be met, add a wired parent nearer the area or install Ethernet.', 'RSSI لینک Parent را بخوانید، نه RSSI کاربر. فرزند را به Parent نزدیک کنید یا مانع را برطرف کنید و Noise و بار را دوباره بسنجید. اگر هدف طراحی ‎-60 dBm محقق نمی‌شود، Parent کابلی نزدیک‌تر اضافه کنید یا Ethernet بکشید.'),
('Frequent Disconnect', 'قطع اتصال مکرر', 'Identify whether only a client roams/drops or the AP uplink disconnects. Correlate timestamps with DFS/radio changes, PoE restarts and firmware events. Restore the last known RF settings and stage firmware changes one AP at a time.', 'مشخص کنید فقط Client جابه‌جا/قطع می‌شود یا Uplink AP قطع می‌شود. زمان رخداد را با DFS/تغییر رادیو، Restart برق PoE و Eventهای Firmware تطبیق دهید. RF قبلی پایدار را بازگردانید و تغییر Firmware را مرحله‌ای روی یک AP انجام دهید.'),
('High Latency', 'تأخیر زیاد', 'Compare ping to the local Gateway and wired server with WAN latency. Test idle and busy conditions; inspect airtime, retries and concurrent child traffic. Reduce contention or wire the affected AP instead of masking a loaded backhaul with a WAN setting.', 'Ping به Gateway محلی و Server کابلی را با WAN مقایسه کنید. در حالت بدون بار و پرترافیک، Airtime، Retry و ترافیک هم‌زمان فرزندان را بسنجید. رقابت رادیویی را کم کنید یا AP را کابلی کنید؛ تنظیم WAN مشکل Backhaul پربار را حل نمی‌کند.'),
('Low Throughput', 'توان عملیاتی پایین', 'Compare local goodput on parent versus each child, both directions. Check client band/capability, hop count, channel width, parent Ethernet speed and load. A displayed PHY rate is not application throughput; widening a congested channel can make the result worse.', 'Goodput محلی Parent و هر فرزند را در دو جهت مقایسه کنید. باند/توانایی Client، Hop، عرض کانال، سرعت Ethernet Parent و بار را بررسی کنید. PHY Rate نمایش‌داده‌شده سرعت واقعی برنامه نیست؛ افزایش عرض کانال شلوغ ممکن است نتیجه را بدتر کند.'),
('Wrong Parent AP', 'انتخاب Parent نامناسب', 'Confirm the actual upstream AP in device details. In this three-AP design, allow Mesh Parent only on AP-PARENT and keep it off on both children. Improve placement and verify selection again. Do not assume every release offers a manual parent-lock control.', 'AP بالادستی واقعی را در جزئیات دستگاه تأیید کنید. در این طراحی سه‌AP، Mesh Parent فقط روی AP-PARENT مجاز باشد و روی فرزندان خاموش بماند. جانمایی را اصلاح و انتخاب را دوباره بررسی کنید. وجود کنترل دستی قفل Parent را برای همه نسخه‌ها فرض نکنید.'),
('Too Many Wireless Hops', 'Wireless Hop بیش از حد', 'Trace every AP to Ethernet. Prefer one hop here and cap the design at two wireless hops; move the child, remove intermediate parent roles or add wired APs. A two-hop cap is not a performance guarantee or a reason to design an avoidable chain.', 'مسیر هر AP تا Ethernet را دنبال کنید. اینجا یک Hop در اولویت است و سقف طراحی دو Wireless Hop است؛ فرزند را جابه‌جا کنید، نقش Parent میانی را حذف کنید یا AP کابلی اضافه کنید. سقف دو Hop تضمین عملکرد یا توجیه زنجیره غیرضروری نیست.'),
('DHCP / VLAN Problems', 'مشکل DHCP / VLAN', 'If the AP is Online but clients get no lease or a wrong subnet, compare SSID Network, native/tagged port settings, AP management override and DHCP scope. Test the same VLAN at the parent; fix the first failing segment. Validate DNS and guest policy after a correct lease is obtained.', 'اگر AP Online است اما Client آدرس نمی‌گیرد یا Subnet اشتباه دارد، Network مربوط به SSID، Native/Tagged پورت، Management Override و Scope DHCP را مقایسه کنید. همان VLAN را روی Parent تست کنید و اولین بخش خراب مسیر را اصلاح کنید. پس از دریافت Lease صحیح، DNS و Policy مهمان را بررسی کنید.'),
]
for en,fa,a,b in TROUBLE:
    parts.append(dual('h3',en,fa)); p(a,b)
cite('isolated'); cite('adopt'); cite('port')

section('enterprise-design', 'Enterprise Design Recommendations', 'توصیه‌های طراحی سازمانی — Enterprise Design Recommendations')
p('The following are engineering recommendations derived from the documented RF and mesh limitations. A small office can use one wired parent and two direct children for modest traffic after a survey and load test. It accepts a shared bottleneck and a parent failure affecting both children; document that service limitation.', 'توصیه‌های زیر جمع‌بندی مهندسی از محدودیت‌های مستند RF و Mesh هستند. دفتر کوچک می‌تواند پس از Survey و تست بار، از یک Parent کابلی و دو فرزند مستقیم برای ترافیک متوسط استفاده کند. این طراحی گلوگاه مشترک و قطع هر دو فرزند با خرابی Parent را می‌پذیرد؛ این محدودیت سرویس را مستند کنید.')
p('An enterprise design starts with wired backhaul per AP, capacity and coverage surveys, switch PoE budgets, protected power and monitored management. Treat mesh as an exception with a named owner, measured capacity and a plan to cable it. For a high-density or voice-critical floor, assess airtime and concurrent applications rather than purchasing more children for the same parent.', 'طراحی Enterprise با Backhaul کابلی هر AP، Survey ظرفیت و پوشش، بودجه PoE سوئیچ، برق محافظت‌شده و مدیریت مانیتورشده آغاز می‌شود. Mesh استثنایی با مسئول مشخص، ظرفیت اندازه‌گیری‌شده و برنامه کابل‌کشی باشد. برای طبقه پرتراکم یا حساس به تماس صوتی، Airtime و برنامه‌های هم‌زمان را ارزیابی کنید؛ افزودن فرزند بیشتر به همان Parent ظرفیت مستقل ایجاد نمی‌کند.')
p('Separate management, Corporate and Guest policies. Evaluate enterprise authentication/RADIUS, logging and access control against organizational requirements. Monitor parent loss, repeated reconnects, signal degradation, DHCP scope usage and load. Test recovery and changes in a maintenance window; do not assume mesh automatically delivers deterministic failover or controller redundancy.', 'Policyهای مدیریت، Corporate و Guest را جدا طراحی کنید. احراز هویت سازمانی/RADIUS، ثبت رخداد و کنترل دسترسی را با نیاز سازمان بسنجید. قطع Parent، اتصال مجدد مکرر، افت سیگنال، مصرف Scope DHCP و بار را مانیتور کنید. بازیابی و تغییرات را در پنجره نگهداری تست کنید؛ Failover قطعی یا افزونگی Controller را نتیجه خودکار Mesh فرض نکنید.')

section('troubleshooting-checklist', 'Troubleshooting Checklist', 'چک‌لیست عیب‌یابی')
items([
('□ Check power and parent Ethernet before resetting an AP.', '□ پیش از Reset، برق و Ethernet Parent را بررسی کنید.'),
('□ Distinguish AP management failure from client data-path failure.', '□ خطای مدیریت AP را از خطای مسیر دیتای Client جدا کنید.'),
('□ Record the real parent, uplink band, RSSI and wireless hop count.', '□ Parent واقعی، باند Uplink، RSSI و تعداد Wireless Hop را ثبت کنید.'),
('□ Correlate disconnects with RF/DFS, power and configuration events.', '□ قطع اتصال را با Eventهای RF/DFS، برق و تغییر تنظیمات تطبیق دهید.'),
('□ Check SSID-to-Network mapping, management reachability, VLAN tags and DHCP leases.', '□ نگاشت SSID به Network، دسترسی مدیریت، Tagهای VLAN و Leaseهای DHCP را بررسی کنید.'),
('□ Compare local tests at the parent and both children under the same load.', '□ تست محلی Parent و هر دو فرزند را با بار یکسان مقایسه کنید.'),
('□ Use temporary Ethernet to recover access; preserve evidence before reset/re-adoption.', '□ برای بازیابی دسترسی از Ethernet موقت استفاده کنید؛ پیش از Reset/Adoption دوباره، شواهد را نگه دارید.'),
])
section('best-practices', 'Best Practices Checklist', 'چک‌لیست Best Practiceها')
items([
('□ Prefer wired backhaul; deploy mesh where cabling is impractical.', '□ Backhaul کابلی در اولویت است؛ Mesh برای محل فاقد امکان کابل‌کشی باشد.'),
('□ Aim for -60 dBm or better between child and parent.', '□ سیگنال فرزند به Parent حدود ‎-60 dBm یا بهتر باشد.'),
('□ Prefer one wireless hop and do not design more than two.', '□ یک Wireless Hop در اولویت باشد و بیش از دو Hop طراحی نشود.'),
('□ Avoid too many children on one parent; size by measured concurrent load.', '□ فرزندان زیاد روی یک Parent قرار ندهید؛ ظرفیت را با بار هم‌زمان اندازه بگیرید.'),
('□ Survey interference and verify the supported 5 GHz backhaul.', '□ تداخل RF را Survey و Backhaul پشتیبانی‌شده 5GHz را تأیید کنید.'),
('□ Keep Mesh Connect off on APs with wired uplinks.', '□ Mesh Connect روی APهای دارای Wired Uplink خاموش باشد.'),
('□ Verify Corporate/Guest DHCP, security, roaming and application performance before release.', '□ پیش از بهره‌برداری، DHCP، امنیت Corporate/Guest، Roaming و عملکرد برنامه را تأیید کنید.'),
('□ Save versions, backups, baseline results and a recovery plan.', '□ نسخه‌ها، Backupها، نتایج مبنا و برنامه بازیابی را ذخیره کنید.'),
])

FAQ = [
('Do wireless APs need power?', 'Yes. No Ethernet data uplink does not mean no power cable. Use compatible PoE or the model’s supported power method.', 'آیا AP بی‌سیم به برق نیاز دارد؟', 'بله؛ نبود Ethernet Data Uplink به معنی نبود کابل برق نیست. از PoE سازگار یا روش تغذیه پشتیبانی‌شده مدل استفاده کنید.'),
('Are Mesh Parent and Mesh Connect the same?', 'No. Mesh Parent serves downstream APs; Mesh Connect permits an AP to uplink wirelessly. Keep Mesh Connect disabled on a wired AP.', 'آیا Mesh Parent و Mesh Connect یکسان‌اند؟', 'خیر؛ Mesh Parent به AP پایین‌دست سرویس می‌دهد و Mesh Connect امکان Uplink بی‌سیم AP را فراهم می‌کند. Mesh Connect روی AP کابلی خاموش باشد.'),
('Does one SSID create the mesh?', 'No. The client SSID and the AP wireless uplink are separate configurations; both must be checked.', 'آیا یک SSID مشترک، Mesh ایجاد می‌کند؟', 'خیر؛ SSID کاربر و Wireless Uplink خود AP تنظیمات جدا هستند و هر دو باید بررسی شوند.'),
('Where does DHCP run?', 'On the Gateway for each configured user network. A mesh child bridges traffic; it does not need a separate DHCP server.', 'DHCP کجا اجرا می‌شود؟', 'روی Gateway برای هر شبکه کاربر تعریف‌شده؛ فرزند Mesh ترافیک را Bridge می‌کند و DHCP Server جدا نیاز ندارد.'),
('Can Corporate and Guest use VLANs?', 'Yes, with supported APs, correct SSID mapping and permitted upstream VLANs. Use distinct SSIDs for simple static assignment, or design supported dynamic assignment separately.', 'آیا Corporate و Guest می‌توانند VLAN داشته باشند؟', 'بله؛ با AP پشتیبانی‌شده، نگاشت SSID صحیح و VLAN مجاز بالادستی. برای نگاشت ثابت ساده، SSID جدا بسازید یا تخصیص پویا پشتیبانی‌شده را جدا طراحی کنید.'),
('Is two hops a recommended default?', 'No. It is the documented planning ceiling. This scenario should use one hop for each child.', 'آیا دو Hop پیش‌فرض پیشنهادی است؟', 'خیر؛ سقف طراحی مستندشده است. در این سناریو هر فرزند بهتر است یک Hop داشته باشد.'),
('Can I force a dedicated 5 GHz backhaul on any AP?', 'Do not assume a dedicated radio or a universal control. UniFi mesh primarily uses 5 GHz; verify the model and its actual uplink.', 'آیا روی هر AP می‌توان Backhaul اختصاصی 5GHz را اجبار کرد؟', 'رادیوی اختصاصی یا کنترل یکسان برای همه مدل‌ها را فرض نکنید. Mesh در UniFi عمدتاً از 5GHz استفاده می‌کند؛ مدل و Uplink واقعی را بررسی کنید.'),
('Does a shared SSID guarantee uninterrupted roaming?', 'No. The client selects its AP. Validate coverage, authentication compatibility and real call/session continuity.', 'آیا SSID مشترک، Roaming بدون وقفه را تضمین می‌کند؟', 'خیر؛ Client، AP را انتخاب می‌کند. پوشش، سازگاری Authentication و تداوم واقعی تماس/Session را تست کنید.'),
]
section('faq','FAQ','پرسش‌های متداول — FAQ')
for q,a,fq,fa in FAQ:
    parts.append('<div class="article-faq-item">'+dual('h3',q,fq)+dual('p',a,fa)+'</div>')
section('conclusion', 'Conclusion', 'نتیجه‌گیری')
p('For this site, deploy one Ethernet parent with Mesh Parent enabled and two directly connected wireless children with Mesh Connect enabled. Confirm DHCP and VLAN forwarding end to end, then accept the design only after RF, simultaneous load and roaming tests pass. Where those results do not meet the application requirement, extend the wired network.', 'در این سایت، یک Parent دارای Ethernet با Mesh Parent فعال و دو فرزند بی‌سیم مستقیم با Mesh Connect فعال راه‌اندازی کنید. DHCP و انتقال VLAN را سرتاسری تأیید کنید و فقط پس از موفقیت تست RF، بار هم‌زمان و Roaming طراحی را بپذیرید. اگر نتایج نیاز برنامه را تأمین نمی‌کند، شبکه کابلی را گسترش دهید.')
section('official-references', 'Official References', 'منابع رسمی')
p('GUI paths and roles checked against Ubiquiti Help Center on 7 October 2026. Verify labels and capability on the installed Network release and exact AP model. The operational test sequence and enterprise recommendations are site engineering guidance; numerical throughput promises are intentionally absent.', 'مسیرهای GUI و نقش‌ها در ۷ اکتبر ۲۰۲۶ با Ubiquiti Help Center بررسی شده‌اند. برچسب‌ها و قابلیت را در نسخه Network نصب‌شده و مدل دقیق AP تأیید کنید. ترتیب تست عملیاتی و توصیه‌های Enterprise راهنمای مهندسی سایت هستند؛ وعده عددی توان عملیاتی ارائه نمی‌شود.')
for key in REFS: cite(key)
parts.append('</section>')

banner = '/assets/img/articles/banners/'+IMAGES['banner'][1]
localizations = {lang: {'title':TITLE[lang], 'meta_title':TITLE[lang], 'description':DESC[lang], 'keywords':KEYWORDS, 'faq':[[q,a] if lang=='en' else [fq,fa] for q,a,fq,fa in FAQ], 'image_alt':IMAGES['banner'][2 if lang=='en' else 3], 'image_title':TITLE[lang], 'image_caption':IMAGES['banner'][2 if lang=='en' else 3]} for lang in ['en','fa']}
schema = {'@context':'https://schema.org','@type':'Article','headline':TITLE['en'],'inLanguage':'en','datePublished':'2026-10-07T00:00:00+03:30','dateModified':'2026-10-07','image':banner,'author':{'@type':'Person','name':'AmirHossein Jalalian'}}
nav = ''.join('<li class="article-nav-item">'+dual('a',en,fa,f'href="#{id}"')+'</li>' for id,en,fa in toc)
html = f'''<!doctype html><html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{TITLE['en']}</title><meta name="article:content-language" content="en">
<meta name="description" content="{DESC['en']}"><meta name="keywords" content="{', '.join(KEYWORDS)}">
<meta property="og:title" content="{TITLE['en']}"><meta property="og:description" content="{DESC['en']}">
<meta property="og:type" content="article"><meta property="og:image" content="{banner}">
<link rel="canonical" href="https://meetaj.ir/articles/{SLUG}"><meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">{json.dumps(schema,ensure_ascii=False)}</script>
<script id="article-localizations" type="application/json">{json.dumps(localizations,ensure_ascii=False)}</script>
</head><body class="article-page theme-other"><header><nav><ul>{nav}</ul></nav></header><main>
<section class="article-hero article-header hero">
{dual('span','Ubiquiti','یوبیکیوتی','class="article-category"')}
{dual('h1',TITLE['en'],TITLE['fa'],'class="article-title hero-title"')}
{dual('p',DESC['en'],DESC['fa'],'class="article-excerpt hero-subtitle"')}
<img class="article-hero-thumbnail" src="{banner}" alt="{escape(IMAGES['banner'][2])}"></section>
<article class="article-body" lang="en" dir="ltr">{chr(10).join(parts)}</article></main></body></html>'''
(ROOT/'resources/legacy/articles'/f'{SLUG}.html').write_text(html,encoding='utf-8')

for folder, name, _, _ in IMAGES.values():
    source = ROOT/'resources/assets/img/articles'/folder/name
    if not source.is_file():
        raise FileNotFoundError(source)
    destination = ROOT/'public/assets/img/articles'/folder/name
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copyfile(source, destination)
print(f'Built {SLUG}: {len(toc)} sections, {len(FAQ)} FAQs, five existing images preserved')
