"""Compile the complete bilingual Grafana runbook into the existing CMS article format."""
from pathlib import Path
from html import escape
import json
import shutil
import zipfile
from article_structure import normalize_html, normalize_markdown

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'grafana-installation-zabbix-integration'
SOURCE = ROOT/'resources/content/articles'/SLUG
TITLE = {'en':'Install Grafana on Ubuntu and Integrate with Zabbix – Complete Enterprise Monitoring Guide',
         'fa':'آموزش جامع نصب گرافانا روی لینوکس و اتصال به زبیکس همراه با ساخت داشبوردهای مانیتورینگ سازمانی'}
DESC = {'en':'Install Grafana OSS on Ubuntu 24.04, connect Zabbix 7.0 via API, build Linux, Windows and NOC dashboards, secure HTTPS and validate backup and recovery.',
        'fa':'نصب Grafana OSS روی Ubuntu 24.04، اتصال API به Zabbix 7.0، ساخت داشبورد لینوکس، ویندوز و NOC، امنیت HTTPS و اعتبارسنجی بکاپ و بازیابی.'}
KEYWORDS = ['Grafana','Zabbix','Monitoring','Linux','DevOps','Infrastructure','Observability','Ubuntu 24.04','Grafana Zabbix plugin','NOC','Grafana backup']
parts, toc = [], []
md = {lang:['# '+TITLE[lang],'',DESC[lang],''] for lang in TITLE}
def dual(tag,en,fa,attrs=''):
    if tag in ('a','figcaption'): return f'<{tag} {attrs}>'+dual('span',en,fa)+f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en,quote=True)}" data-fa="{escape(fa,quote=True)}">{escape(en)}</{tag}>'
def p(en,fa):
    parts.append(dual('p',en,fa))
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend([value,''])
def section(id,en,fa):
    if toc: parts.append('</section>')
    toc.append((id,en,fa));parts.append(f'<section id="{id}">'+dual('h2',en,fa))
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend(['## '+value,''])
def sub(en,fa):
    parts.append(dual('h3',en,fa))
    for lang,value in [('en',en),('fa',fa)]:md[lang].extend(['### '+value,''])
def code(value,lang='bash'):
    value=value.strip();parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value)}</code></pre>')
    for locale in md:md[locale].extend(['```'+lang,value,'```',''])
def filecode(name,lang):code((SOURCE/name).read_text(encoding='utf-8'),lang)
def items(rows):
    parts.append('<ul>'+''.join(dual('li',en,fa) for en,fa in rows)+'</ul>')
    for lang,i in [('en',0),('fa',1)]:md[lang].extend(['- '+row[i] for row in rows]+[''])
def table(headers,rows):
    parts.append('<table><thead><tr>'+''.join(dual('th',*h) for h in headers)+'</tr></thead><tbody>'+''.join('<tr>'+''.join(dual('td',*c) for c in row)+'</tr>' for row in rows)+'</tbody></table>')
    for lang,i in [('en',0),('fa',1)]:md[lang].extend(['| '+' | '.join(h[i] for h in headers)+' |','| '+' | '.join('---' for h in headers)+' |']+['| '+' | '.join(c[i] for c in row)+' |' for row in rows]+[''])
def link(url,en,fa):
    parts.append('<p>'+dual('a',en,fa,f'href="{escape(url,quote=True)}" rel="noopener"')+'</p>')
    target='https://meetaj.ir'+url if url.startswith('/articles/') else url
    for lang,value in [('en',en),('fa',fa)]:md[lang].extend([f'[{value}]({target})',''])
def ref(path,en,fa):link('https://grafana.com/'+path,en,fa)
def figure(name,en,fa):
    src='/assets/img/articles/content/'+name
    anchor=' id="architecture"' if name=='grafana-zabbix-enterprise-architecture.png' else ''
    parts.append(f'<figure{anchor}><img src="{src}" width="1920" height="1080" loading="lazy" decoding="async" alt="{escape(en,quote=True)}" data-en-alt="{escape(en,quote=True)}" data-fa-alt="{escape(fa,quote=True)}">'+dual('figcaption',en,fa)+'</figure>')
    for lang,value in [('en',en),('fa',fa)]:md[lang].extend([f'![{value}]({src})','',value,''])

section('introduction','1. Introduction: Grafana and Zabbix responsibilities','۱. مقدمه: مسئولیت گرافانا و زبیکس')
p('Grafana is a visualization and observability application. Its backend handles authentication, data-source requests, dashboard storage and scheduled alert evaluation; its browser frontend renders panels. Grafana queries data sources rather than replacing the monitoring collectors. In this architecture Zabbix agents collect operating-system metrics, Zabbix Server stores history and trends in PostgreSQL and evaluates triggers, and Grafana visualizes that data through the signed Zabbix app plugin and HTTPS API.',
  'گرافانا برنامه‌ای برای نمایش داده و مشاهده‌پذیری است. بخش Backend احراز هویت، درخواست منبع داده، ذخیره داشبورد و ارزیابی زمان‌بندی‌شده هشدار را انجام می‌دهد و Frontend مرورگر پنل‌ها را نمایش می‌دهد. گرافانا منبع داده را Query می‌کند و جایگزین جمع‌آوری‌کننده مانیتورینگ نیست. در این معماری Agent زبیکس معیارهای سیستم‌عامل را جمع‌آوری می‌کند، Zabbix Server تاریخچه و Trend را در PostgreSQL ذخیره و Trigger را ارزیابی می‌کند و گرافانا با افزونه امضاشده زبیکس و API مبتنی بر HTTPS داده را نمایش می‌دهد.')
figure('grafana-zabbix-enterprise-architecture.png','Enterprise data flow: agents to Zabbix; Grafana queries its HTTPS API; operators use Nginx HTTPS','جریان داده سازمانی: Agent به زبیکس؛ Query گرافانا از API زبیکس با HTTPS؛ دسترسی اپراتور از Nginx و HTTPS')
table([('Concept','مفهوم'),('Operational meaning','کاربرد عملیاتی')],[
 [('Data source','منبع داده'),('A configured connection such as Zabbix, Prometheus, PostgreSQL or Loki. Credentials remain on the server.','اتصال تنظیم‌شده مانند Zabbix، Prometheus، PostgreSQL یا Loki؛ Credential روی سرور نگهداری می‌شود.')],
 [('Dashboard / panel','داشبورد / پنل'),('A dashboard organizes panels. A panel combines queries, transformations, units and a visualization.','داشبورد پنل‌ها را مرتب می‌کند؛ پنل ترکیب Query، Transformation، واحد و نوع نمایش است.')],
 [('Variables','متغیرها'),('Reusable selectors for host groups, hosts, item tags and interfaces. Variables are filters, not authorization boundaries.','انتخاب‌گر قابل استفاده مجدد برای گروه میزبان، میزبان، تگ آیتم و Interface؛ متغیر فیلتر است و مرز مجوز نیست.')],
 [('Zabbix triggers / problems','Trigger / Problem زبیکس'),('Zabbix evaluates item expressions, records incidents and handles actions/escalations.','زبیکس عبارت آیتم را ارزیابی، رخداد را ثبت و Action و Escalation را اجرا می‌کند.')],
 [('Grafana Alerting','هشدار گرافانا'),('Independent server-side rules with contact points and notification policies; selected plugin queries support alerting.','Rule مستقل سمت سرور با Contact point و Notification policy؛ Query منتخب افزونه از هشدار پشتیبانی می‌کند.')]])
p('Grafana OSS includes core dashboards, basic organization roles, folder permissions and alerting. Licensed Enterprise adds granular RBAC, data-source permissions and other enterprise features. An enterprise monitoring architecture does not require the Enterprise edition. OSS Viewers can query organization data sources; restricting a dashboard does not restrict the underlying data. Use separate organizations/data sources with appropriately scoped Zabbix users when data isolation is required.',
  'Grafana OSS داشبورد، نقش پایه سازمان، مجوز پوشه و هشدار را ارائه می‌کند. نسخه Enterprise دارای License قابلیت‌هایی مانند RBAC دقیق و مجوز منبع داده دارد. معماری مانیتورینگ سازمانی الزاماً به نسخه Enterprise نیاز ندارد. Viewer در OSS می‌تواند منبع داده سازمان را Query کند؛ محدود کردن داشبورد داده زیرین را محدود نمی‌کند. برای جداسازی داده از سازمان و منبع داده جدا با کاربر زبیکس دارای محدوده مناسب استفاده کنید.')
p('Organizations integrate them for a shared NOC screen, application and infrastructure comparisons, capacity trends, incident correlation and executive service views. Zabbix remains the collection and incident system of record. Keep failure-domain boundaries visible: Grafana SQLite is its metadata store, while PostgreSQL in the architecture is the existing Zabbix database. Grafana can later use its own PostgreSQL database for availability requirements.',
  'سازمان‌ها برای صفحه مشترک NOC، مقایسه زیرساخت و برنامه، روند ظرفیت، ارتباط رخداد و نمای مدیریتی سرویس این دو ابزار را یکپارچه می‌کنند. زبیکس مرجع جمع‌آوری و رخداد باقی می‌ماند. دامنه خرابی را مشخص نگه دارید: SQLite گرافانا مخزن Metadata آن است و PostgreSQL نمودار همان دیتابیس موجود زبیکس است. گرافانا در صورت نیاز دسترس‌پذیری می‌تواند دیتابیس PostgreSQL مستقل خود را داشته باشد.')
ref('docs/grafana/latest/administration/roles-and-permissions/','Official Grafana roles and edition boundaries','مستند رسمی نقش‌ها و محدودیت نسخه گرافانا')

section('prerequisites','2. Prerequisites and supported versions','۲. پیش‌نیازها و نسخه‌های پشتیبانی‌شده')
p('Use a dedicated Ubuntu Server 24.04 LTS host. Start a modest production deployment at 2 vCPU, 4 GB RAM and 20–40 GB disk, with a separate mounted backup volume; these are planning recommendations, not measured capacity guarantees. Size for concurrent users, panels, refresh rates, alert evaluations and any local logs. Grafana does not copy all Zabbix history into its own database.',
  'از میزبان اختصاصی Ubuntu Server 24.04 LTS استفاده کنید. برای استقرار متوسط از ۲ vCPU، حافظه ۴ گیگابایت و دیسک ۲۰ تا ۴۰ گیگابایت با Volume مستقل بکاپ شروع کنید؛ این مقادیر پیشنهاد ظرفیت هستند و تضمین اندازه‌گیری‌شده نیستند. تعداد کاربر هم‌زمان، پنل، Refresh، ارزیابی هشدار و Log محلی را لحاظ کنید. گرافانا تمام تاریخچه زبیکس را در دیتابیس خود کپی نمی‌کند.')
table([('Component','جزء'),('Baseline','مبنای استقرار')],[
 [('Ubuntu','اوبونتو'),('24.04 LTS, current security updates; systemd, Nginx, trusted TLS.','24.04 LTS با به‌روزرسانی امنیتی جاری؛ systemd، Nginx و TLS معتبر.')],
 [('Grafana OSS','گرافانا OSS'),('13.2.3 stable from the official download selector (released 29 September 2026); install the stable APT candidate.','نسخه پایدار 13.2.3 طبق انتخاب‌گر دانلود رسمی (انتشار ۲۹ سپتامبر ۲۰۲۶)؛ نصب Candidate مخزن پایدار APT.')],
 [('Zabbix app','افزونه زبیکس'),('6.9.1 stable, catalog updated 6 October 2026, Grafana >=11.6.0. ID: alexanderzobnin-zabbix-app.','نسخه پایدار 6.9.1، بروزرسانی کاتالوگ ۶ اکتبر ۲۰۲۶، نیازمند Grafana >=11.6.0؛ شناسه alexanderzobnin-zabbix-app.')],
 [('Zabbix','زبیکس'),('Existing 7.0 LTS Server/Frontend/API with authorized read access; OS templates from 7.0.','Server/Frontend/API موجود شاخه 7.0 LTS با مجوز خواندن؛ Template سیستم‌عامل شاخه 7.0.')]])
p('Versions were verified on 9 October 2026. The plugin minimum is satisfied by 13.2.3 and its documentation explicitly supports Zabbix 7.0 token handling. This is documented compatibility, not a live integration test. Recheck release notes and package candidates before each maintenance window. Never select beta, nightly or release-candidate repositories.',
  'نسخه‌ها در ۹ اکتبر ۲۰۲۶ بررسی شدند. نسخه 13.2.3 حداقل نیاز افزونه را برآورده می‌کند و مستند افزونه رفتار Token در Zabbix 7.0 را صریحاً پوشش می‌دهد. این سازگاری مستند است و آزمون اتصال زنده نیست. پیش از هر پنجره نگهداری Release note و Candidate بسته را دوباره بررسی کنید. مخزن Beta، Nightly یا Release Candidate انتخاب نکنید.')
code('''grafana.example.com  192.0.2.20
zabbix.example.com   192.0.2.10
linux-app-01        198.51.100.21
windows-app-01      198.51.100.22
backup.example.com  203.0.113.30''','text')
p('All IPs above are RFC 5737 documentation addresses and must be replaced. Configure A/AAAA records only for addresses actually routed to the service. Use a real owned domain for a public certificate; example.com cannot be issued to you. Validate DNS, the Zabbix frontend path, outbound API access, trusted CA chain and time synchronization before installing.',
  'همه IPهای بالا از محدوده مستندسازی RFC 5737 هستند و باید جایگزین شوند. رکورد A/AAAA را فقط برای آدرس واقعاً قابل دسترس ثبت کنید. برای Certificate عمومی دامنه تحت مالکیت خود لازم است؛ example.com برای شما صادر نمی‌شود. پیش از نصب DNS، مسیر Frontend زبیکس، دسترسی خروجی API، زنجیره CA معتبر و همگام‌سازی زمان را بررسی کنید.')
code('''hostnamectl
sudo hostnamectl set-hostname grafana.example.com
getent ahosts grafana.example.com zabbix.example.com
timedatectl status
sudo timedatectl set-ntp true
timedatectl timesync-status
curl --fail --silent --show-error --connect-timeout 5 https://zabbix.example.com/ -o /dev/null''')
table([('Direction / port','جهت / پورت'),('Purpose and policy','هدف و سیاست')],[
 [('Operators -> Grafana TCP 443','اپراتور به گرافانا TCP 443'),('HTTPS from management VPN or approved networks.','HTTPS از VPN مدیریتی یا شبکه مجاز.')],
 [('ACME -> Nginx TCP 80','ACME به Nginx TCP 80'),('Required for HTTP-01 validation and renewal; use DNS-01 if inbound HTTP is prohibited.','برای اعتبارسنجی و تمدید HTTP-01 لازم؛ در صورت منع HTTP ورودی از DNS-01 استفاده کنید.')],
 [('Grafana -> Zabbix TCP 443','گرافانا به زبیکس TCP 443'),('HTTPS API only. Port 10051 is not the Grafana data source API.','فقط API مبتنی بر HTTPS؛ پورت 10051 همان API منبع داده نیست.')],
 [('Nginx -> Grafana TCP 3000','Nginx به گرافانا TCP 3000'),('Loopback only; no public firewall allowance.','فقط Loopback؛ بدون مجوز Firewall عمومی.')],
 [('Administration TCP 22','مدیریت TCP 22'),('SSH from management network; preserve a working session before UFW changes.','SSH از شبکه مدیریت؛ پیش از تغییر UFW یک Session فعال حفظ کنید.')],
 [('Optional TCP 5432 / 10051','اختیاری TCP 5432 / 10051'),('5432 only for optional Direct DB; 10051 only for the supplied NOC collector sender, restricted source and TLS PSK.','5432 فقط برای Direct DB اختیاری؛ 10051 فقط Sender جمع‌آوری‌کننده NOC با مبدا محدود و TLS PSK.')]])
ref('grafana/download?edition=oss','Official Grafana OSS stable release selector','انتخاب‌گر رسمی نسخه پایدار Grafana OSS')
ref('grafana/plugins/alexanderzobnin-zabbix-app/','Official signed Zabbix plugin catalog and requirements','کاتالوگ رسمی افزونه امضاشده زبیکس و نیازمندی‌ها')

section('installation','3. Install Grafana on Ubuntu and verify systemd','۳. نصب گرافانا روی اوبونتو و بررسی systemd')
figure('grafana-ubuntu-installation-workflow.png','Ubuntu installation: verify key, stable repository, package, loopback configuration and service health','نصب اوبونتو: بررسی کلید، مخزن پایدار، بسته، تنظیم Loopback و سلامت سرویس')
p('Run the following on the dedicated Grafana host during an approved maintenance window. Inspect pending Ubuntu upgrades and reboot requirements before starting dependent services. Repository signing uses a scoped keyring rather than the deprecated apt-key command. Verify the current primary fingerprint against the official repository page before trusting the key.',
  'دستورات زیر را روی میزبان اختصاصی گرافانا در پنجره نگهداری اجرا کنید. ارتقای معوق اوبونتو و نیاز Restart را پیش از راه‌اندازی سرویس وابسته بررسی کنید. امضای مخزن از Keyring محدود استفاده می‌کند و دستور منسوخ apt-key به کار نمی‌رود. پیش از اعتماد به کلید، Fingerprint اصلی را با صفحه رسمی مخزن تطبیق دهید.')
code(r'''sudo apt-get update
sudo apt-get upgrade -y
sudo apt-get install -y ca-certificates curl gnupg apt-transport-https nginx ufw sqlite3 python3
sudo install -d -m 0755 /etc/apt/keyrings
keyfile=$(mktemp)
curl --fail --silent --show-error https://apt.grafana.com/gpg.key -o "$keyfile"
gpg --show-keys --with-fingerprint "$keyfile"
fingerprint=$(gpg --show-keys --with-colons "$keyfile" | awk -F: '$1=="fpr" {print $10; exit}')
test "$fingerprint" = B53AE77BADB630A683046005963FA27710458545 || { echo 'STOP: verify key rotation with Grafana' >&2; exit 1; }
sudo install -m 0644 "$keyfile" /etc/apt/keyrings/grafana.asc
rm -- "$keyfile"
printf '%s\n' 'deb [signed-by=/etc/apt/keyrings/grafana.asc] https://apt.grafana.com stable main' | sudo tee /etc/apt/sources.list.d/grafana.list
sudo apt-get update
apt-cache policy grafana
candidate=$(apt-cache policy grafana | awk '/Candidate:/ {print $2}')
case "$candidate" in *beta*|*rc*|*nightly*|*alpha*|\(none\)) echo 'STOP: stable candidate required' >&2; exit 1;; esac
sudo apt-get install -y grafana
dpkg-query -W -f='${Package} ${Version}\n' grafana
grafana --version''')
p('APT verifies repository metadata and package hashes using the scoped signing key. The review-time fingerprint is B53AE77BADB630A683046005963FA27710458545; on a future rotation stop and verify the replacement independently. The stable candidate can advance beyond 13.2.3; record the actual installed version and recheck plugin release notes rather than forcing an obsolete package.',
  'APT با کلید محدود، Metadata و Hash بسته مخزن را بررسی می‌کند. Fingerprint زمان بررسی B53AE77BADB630A683046005963FA27710458545 است؛ در چرخش آینده توقف و کلید جایگزین را مستقل بررسی کنید. Candidate پایدار ممکن است از 13.2.3 جدیدتر شود؛ نسخه نصب‌شده را ثبت و Release note افزونه را بررسی کنید، بسته قدیمی را تحمیل نکنید.')
sub('Configure the instance before operator access','پیکربندی نمونه پیش از دسترسی اپراتور')
p('Download the package into an operator workspace and run subsequent file-copy commands there. Back up the packaged default grafana.ini before replacing it on this new host. Create one unique secret key, protect it and keep it through recovery: changing it later can prevent Grafana from decrypting stored data-source credentials. Do not overwrite an established instance secret.',
  'بسته را در پوشه کار اپراتور دانلود و دستورهای Copy بعدی را از آنجا اجرا کنید. پیش از جایگزینی grafana.ini در میزبان جدید از تنظیم پیش‌فرض بکاپ بگیرید. یک Secret key یکتا بسازید، محافظت کنید و در بازیابی حفظ کنید؛ تغییر بعدی می‌تواند مانع رمزگشایی Credential منبع داده شود. Secret نمونه موجود را بازنویسی نکنید.')
code(r'''sudo cp -a /etc/grafana/grafana.ini /etc/grafana/grafana.ini.before-enterprise
sudo install -d -o root -g grafana -m 0750 /etc/grafana/secrets
sudo bash -c 'umask 027; test ! -e /etc/grafana/secrets/secret_key && openssl rand -hex 32 > /etc/grafana/secrets/secret_key'
sudo chown root:grafana /etc/grafana/secrets/secret_key
sudo chmod 0640 /etc/grafana/secrets/secret_key
sudo install -o root -g grafana -m 0640 grafana.ini /etc/grafana/grafana.ini
sudo systemctl enable --now grafana-server
sudo systemctl status grafana-server --no-pager
sudo systemctl is-enabled grafana-server
sudo journalctl -u grafana-server -n 100 --no-pager
sudo ss -lntp | grep ':3000'
curl --fail --silent --show-error --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health''')
filecode('grafana.ini','ini')
p('enable --now both starts the service and schedules startup at boot. status should report active (running), is-enabled should return enabled, and the health response should contain database: ok and the installed version. ss must show 127.0.0.1:3000, not 0.0.0.0:3000. journalctl shows startup, migration and plugin errors; never publish logs containing credentials. root_url points to the external HTTPS name while protocol stays http because Nginx terminates TLS.',
  'enable --now هم سرویس را راه‌اندازی می‌کند و هم اجرای Boot را فعال می‌کند. status باید active (running)، is-enabled مقدار enabled و پاسخ Health مقدار database: ok و نسخه نصب‌شده را نشان دهد. خروجی ss باید 127.0.0.1:3000 باشد، نه 0.0.0.0:3000. journalctl خطای Startup، Migration و افزونه را نشان می‌دهد؛ Log حاوی Credential منتشر نکنید. root_url نام خارجی HTTPS است و protocol به دلیل پایان TLS در Nginx همچنان http می‌ماند.')
p('For the first administrator login before HTTPS, use a temporary SSH tunnel: ssh -L 3000:127.0.0.1:3000 operator@grafana.example.com. If enforce_domain and secure cookies block HTTP login, complete the HTTPS chapter first; keep the production configuration intact. Open https://grafana.example.com/login after TLS is ready, log in with the new-instance admin/admin defaults and immediately set a unique vault-managed password. Create named administrator accounts and a controlled emergency account; never share the bootstrap password.',
  'برای دسترسی اولیه پیش از HTTPS از تونل موقت SSH استفاده کنید: ssh -L 3000:127.0.0.1:3000 operator@grafana.example.com. اگر enforce_domain و Cookie امن ورود HTTP را مسدود کردند ابتدا فصل HTTPS را کامل کنید؛ تنظیم Production را حفظ کنید. پس از آماده‌شدن TLS آدرس https://grafana.example.com/login را باز، با مقدار اولیه admin/admin نمونه جدید وارد و فوراً رمز یکتای مدیریت‌شده در Vault تعیین کنید. حساب مدیر نام‌دار و حساب اضطراری کنترل‌شده بسازید؛ رمز Bootstrap را به اشتراک نگذارید.')
ref('docs/grafana/latest/setup-grafana/installation/debian/','Official Ubuntu installation instructions','دستور نصب رسمی اوبونتو')
link('https://apt.grafana.com/','Official repository signing-key fingerprint','Fingerprint رسمی کلید امضای مخزن')

section('security','4. Production security, Nginx and HTTPS','۴. امنیت عملیاتی، Nginx و HTTPS')
p('Public port 3000 would bypass Nginx TLS, access controls and logging. Bind Grafana to loopback, permit inbound 443 only from approved management networks and preserve restricted SSH access. Treat IPv6 separately. Port 80 below supports ACME HTTP-01; a VPN-only site should instead obtain a valid organizational CA certificate or use DNS-01 with an approved DNS provider.',
  'پورت عمومی 3000 می‌تواند TLS، کنترل دسترسی و Log در Nginx را دور بزند. گرافانا را به Loopback متصل، ورودی 443 را فقط از شبکه مدیریت مجاز باز و SSH محدود را حفظ کنید. IPv6 را جدا بررسی کنید. پورت 80 زیر برای ACME HTTP-01 است؛ سایت فقط VPN باید Certificate معتبر CA سازمانی یا DNS-01 با Provider DNS مجاز داشته باشد.')
code('''sudo ufw allow from 192.0.2.0/24 to any port 22 proto tcp
sudo ufw allow from 192.0.2.0/24 to any port 443 proto tcp
sudo ufw allow 80/tcp
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw enable
sudo ufw status verbose''')
sub('Bootstrap a valid certificate before enabling the TLS virtual host','صدور Certificate معتبر پیش از فعال‌سازی Virtual host TLS')
p('Replace the domain and certificate paths in both downloaded Nginx files. Install only the bootstrap HTTP site first, because nginx -t cannot load a certificate that does not exist. Confirm no other enabled site uses the same server_name. The following Certbot webroot flow requires public DNS and public TCP 80 reachability; it is not suitable for the RFC 5737 addresses as written.',
  'دامنه و مسیر Certificate هر دو فایل Nginx را جایگزین کنید. ابتدا فقط سایت HTTP Bootstrap را نصب کنید زیرا nginx -t نمی‌تواند Certificate ناموجود را بارگذاری کند. مطمئن شوید سایت فعال دیگری همان server_name را ندارد. روش Webroot در Certbot زیر DNS و TCP 80 عمومی می‌خواهد؛ آدرس RFC 5737 نوشته‌شده برای صدور مناسب نیست.')
filecode('nginx-bootstrap.conf','nginx')
code('''sudo apt-get install -y certbot
sudo install -d -m 0755 /var/www/acme
sudo install -m 0644 nginx-bootstrap.conf /etc/nginx/sites-available/grafana
sudo ln -s /etc/nginx/sites-available/grafana /etc/nginx/sites-enabled/grafana
sudo nginx -t
sudo systemctl reload nginx
sudo certbot certonly --webroot -w /var/www/acme -d grafana.example.com
sudo install -m 0644 nginx-grafana.conf /etc/nginx/sites-available/grafana
sudo nginx -t
sudo systemctl reload nginx
curl --fail --silent --show-error https://grafana.example.com/api/health''')
filecode('nginx-grafana.conf','nginx')
p('The map and upstream belong in the http context; Ubuntu sites-enabled is included there. Forwarded headers are overwritten at the trusted proxy. The /api/live/ location carries the WebSocket Upgrade headers. nginx -t should report syntax is ok and test is successful before reload; this only validates configuration, not certificates issued by a real CA or upstream application health. Keep HSTS only after HTTPS works and plan its cached effect during rollback.',
  'map و upstream داخل Context مربوط به http هستند؛ sites-enabled اوبونتو در همان قسمت Include می‌شود. Headerهای Forwarded در Proxy مورد اعتماد بازنویسی می‌شوند. مسیر /api/live/ هدر Upgrade برای WebSocket را منتقل می‌کند. nginx -t پیش از Reload باید syntax is ok و test is successful گزارش کند؛ این فقط تنظیم را بررسی می‌کند و سلامت برنامه یا صدور CA واقعی را اثبات نمی‌کند. HSTS را پس از کارکرد HTTPS حفظ و اثر Cache آن را در Rollback لحاظ کنید.')
code(r'''sudo install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
printf '%s\n' '#!/bin/sh' 'nginx -t && systemctl reload nginx' | sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx >/dev/null
sudo chmod 0755 /etc/letsencrypt/renewal-hooks/deploy/reload-nginx
sudo systemctl enable --now certbot.timer
sudo certbot renew --dry-run
sudo systemctl list-timers certbot.timer
openssl s_client -connect grafana.example.com:443 -servername grafana.example.com -verify_return_error </dev/null''')
items([
 ('Use cookie_secure=true and SameSite=lax with HTTPS; verify Secure and HttpOnly session attributes in browser developer tools. Disable anonymous access and self-signup.','با HTTPS از cookie_secure=true و SameSite=lax استفاده؛ Secure و HttpOnly را در ابزار مرورگر بررسی کنید. دسترسی ناشناس و ثبت‌نام آزاد را غیرفعال کنید.'),
 ('Assign Viewers to NOC operators, Editors to dashboard authors and organization Admin only to data-source administrators. Server administrator is a separate privileged responsibility.','به اپراتور NOC نقش Viewer، نویسنده داشبورد Editor و فقط مدیر منبع داده نقش Admin سازمان بدهید. مدیر Server مسئولیت ممتاز جداگانه است.'),
 ('Prefer approved OIDC/OAuth or LDAP integration; enforce MFA at the identity provider, test role mapping and account removal. Maintain a audited break-glass account.','OIDC/OAuth یا LDAP مجاز را ترجیح دهید؛ MFA را در IdP اعمال و Role mapping و حذف حساب را آزمون کنید. حساب اضطراری با ممیزی نگه دارید.'),
 ('Install signed plugins from the official catalog, record versions, stage updates, and keep unsigned-plugin exceptions disabled. Do not grant integration users write/acknowledge permissions merely to make a panel work.','افزونه امضاشده از کاتالوگ رسمی نصب، نسخه ثبت و ارتقا در Staging بررسی کنید؛ استثنای افزونه بدون امضا را غیرفعال نگه دارید. برای کارکرد پنل به کاربر اتصال مجوز Write/Acknowledge ندهید.'),
 ('Monitor certificate expiry, renewal failures, failed logins and service health from outside Grafana. Restrict access to configuration, encryption keys and backups.','انقضای Certificate، شکست تمدید، ورود ناموفق و سلامت سرویس را از خارج گرافانا پایش کنید. دسترسی Config، کلید رمزگذاری و بکاپ را محدود کنید.')])
ref('tutorials/run-grafana-behind-a-proxy/','Official reverse-proxy and Grafana Live configuration','تنظیم رسمی Reverse Proxy و Grafana Live')
ref('docs/grafana/latest/setup-grafana/configure-security/','Official Grafana security settings','تنظیم امنیت رسمی گرافانا')
link('https://eff-certbot.readthedocs.io/en/stable/using.html','Certbot webroot, renewal and deploy hooks','Certbot: Webroot، تمدید و Deploy hook')

section('zabbix-plugin','5. Install and enable the official Zabbix plugin','۵. نصب و فعال‌سازی افزونه رسمی زبیکس')
code('''sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins install alexanderzobnin-zabbix-app
sudo systemctl restart grafana-server
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls
sudo journalctl -u grafana-server -n 100 --no-pager
sudo python3 -c 'import json; p=json.load(open("/var/lib/grafana/plugins/alexanderzobnin-zabbix-app/plugin.json")); print(p["id"],p["info"]["version"],p["dependencies"]["grafanaDependency"])' ''')
p('The current CLI is grafana cli; the standalone grafana-cli is deprecated and fails on Grafana 13.x. Explicit pluginsDir matches the Debian package paths. At review time the official stable catalog supplies 6.9.1. Confirm the app ID and dependency from plugin.json and a successful signed-plugin startup in the journal. File installation alone does not enable an app plugin.',
  'CLI جاری grafana cli است؛ grafana-cli مستقل منسوخ است و در Grafana 13.x شکست می‌خورد. pluginsDir صریح با مسیر بسته Debian تطبیق دارد. کاتالوگ پایدار رسمی هنگام بررسی نسخه 6.9.1 ارائه می‌کند. شناسه App و Dependency را از plugin.json و Startup موفق افزونه امضاشده را در Journal بررسی کنید. نصب فایل به‌تنهایی App plugin را فعال نمی‌کند.')
p('In the current UI open Administration > Plugins and data > Plugins, search Zabbix, open its page and click Enable. Then Connections > Add new connection > Zabbix > Add new data source becomes available. For repeatable provisioning install zabbix-app.yaml under /etc/grafana/provisioning/plugins/ and restart. Do not edit packaged defaults or enable obsolete Angular support.',
  'در UI جاری مسیر Administration > Plugins and data > Plugins را باز، Zabbix را جستجو و در صفحه آن Enable را انتخاب کنید. سپس Connections > Add new connection > Zabbix > Add new data source در دسترس می‌شود. برای Provisioning تکرارپذیر zabbix-app.yaml را در /etc/grafana/provisioning/plugins/ نصب و Restart کنید. Default بسته را تغییر ندهید و Angular قدیمی را فعال نکنید.')
filecode('zabbix-app.yaml','yaml')
code('''sudo install -d -o root -g grafana -m 0750 /etc/grafana/provisioning/plugins
sudo install -o root -g grafana -m 0640 zabbix-app.yaml /etc/grafana/provisioning/plugins/zabbix-app.yaml
sudo systemctl restart grafana-server
# Upgrade during a tested maintenance window, after backing up:
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins update alexanderzobnin-zabbix-app
sudo systemctl restart grafana-server''')
p('Stage each update with the same Grafana version, Zabbix API version, dashboards and alert rules. If dependencies are incompatible, restore the previously approved plugin artifact and the matching Grafana/database snapshot. Use the documented CLI version argument to pin an approved plugin, for example plugins install alexanderzobnin-zabbix-app 6.9.1 on a fresh staging installation. Never solve signature or version failures by allowing arbitrary unsigned code.',
  'هر ارتقا را با همان نسخه گرافانا، API زبیکس، داشبورد و Rule هشدار در Staging بررسی کنید. در ناسازگاری Dependency، Artifact افزونه قبلی مجاز و Snapshot هماهنگ Grafana/Database را بازیابی کنید. برای Pin نسخه افزونه از آرگومان نسخه مستند CLI مانند plugins install alexanderzobnin-zabbix-app 6.9.1 در نصب Staging تازه استفاده کنید. شکست امضا یا نسخه را با اجازه اجرای کد بدون امضا حل نکنید.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/','Official Zabbix plugin installation and supported features','نصب افزونه رسمی زبیکس و قابلیت‌های پشتیبانی‌شده')

section('zabbix-connection','6. Connect Grafana to the Zabbix HTTPS API','۶. اتصال گرافانا به API زبیکس با HTTPS')
figure('grafana-zabbix-plugin-integration.png','Integration: dedicated read-only Zabbix user, expiring API token, server-side query and Save & test','یکپارچه‌سازی: کاربر اختصاصی Read-only زبیکس، Token مدت‌دار API، Query سمت سرور و Save & test')
sub('Prepare Zabbix authorization and verify the endpoint','آماده‌سازی مجوز زبیکس و بررسی Endpoint')
p('On the Zabbix host verify systemctl status zabbix-server and the frontend HTTPS response. In Zabbix 7.0 go to Users > User groups and create Grafana readers. Grant Read access under Host permissions only to the approved Linux, Windows and NOC collector groups. Under Users > User roles create a User-type API role with read methods needed by the plugin (hostgroup.get, host.get, hostinterface.get, item.get, history.get, trend.get, trigger.get, problem.get, event.get and supporting read methods such as user.get/valuemap.get when used). Permit API access, deny write methods, and inspect missing-method errors instead of selecting Super admin.',
  'روی میزبان زبیکس systemctl status zabbix-server و پاسخ HTTPS رابط وب را بررسی کنید. در Zabbix 7.0 به Users > User groups بروید و Grafana readers بسازید. در Host permissions فقط به گروه‌های مجاز Linux، Windows و جمع‌آوری‌کننده NOC دسترسی Read بدهید. در Users > User roles نقش از نوع User با Methodهای خواندن موردنیاز افزونه مانند hostgroup.get، host.get، hostinterface.get، item.get، history.get، trend.get، trigger.get، problem.get، event.get و Method کمکی مانند user.get/valuemap.get در صورت استفاده بسازید. API را مجاز، Method نوشتن را ممنوع و خطای Method مفقود را بررسی کنید؛ Super admin انتخاب نکنید.')
p('Create a named integration user under Users > Users, attach that group and role, and keep its interactive password in a vault. An administrator can create an expiring token for that owner under Users > API tokens; a user manages its own tokens under User settings > API tokens when allowed by its role. Generate once, store securely and rotate with overlap. Tokens inherit the owner’s host and role permissions; they do not grant access on their own.',
  'در Users > Users کاربر اتصال نام‌دار بسازید، گروه و Role را متصل و رمز تعاملی را در Vault نگه دارید. مدیر می‌تواند برای صاحب حساب در Users > API tokens توکن با انقضا بسازد؛ خود کاربر در صورت مجوز Role از User settings > API tokens توکن خود را مدیریت می‌کند. یک‌بار Generate، امن ذخیره و با هم‌پوشانی Rotate کنید. Token مجوز میزبان و Role صاحب حساب را به ارث می‌برد و مستقل دسترسی نمی‌دهد.')
code(r'''ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php
curl --fail --silent --show-error --connect-timeout 5 --max-time 30 \
  -H 'Content-Type: application/json-rpc' \
  --data '{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}' \
  "$ZABBIX_API_URL"''')
p('A JSON-RPC result containing 7.0.x confirms the frontend API path; HTML, a login redirect or 404 does not. If the frontend is mounted under /zabbix, repeat with https://zabbix.example.com/zabbix/api_jsonrpc.php. apiinfo.version is unauthenticated and does not prove host permissions or token validity. Never assume the URL path. Verify CA trust with the same host that will run Grafana, and never bypass verification using curl -k or Skip TLS verify.',
  'پاسخ JSON-RPC دارای result شامل 7.0.x مسیر API رابط وب را تأیید می‌کند؛ HTML، Redirect ورود یا 404 تأیید نیست. اگر Frontend زیر /zabbix نصب شده با https://zabbix.example.com/zabbix/api_jsonrpc.php تکرار کنید. apiinfo.version بدون احراز هویت است و مجوز میزبان یا صحت Token را اثبات نمی‌کند. مسیر URL را فرض نکنید. CA trust را از همان میزبان گرافانا بررسی کنید و با curl -k یا Skip TLS verify اعتبارسنجی را دور نزنید.')
p('The download includes a safe API probe using a root-readable token file and HTTPS Authorization header, never a literal token in command history or a screenshot. It prints only API version and visible-host count. Review the role allowlist after adding features; API error details can identify a denied method. Keep Authorization headers preserved through the Zabbix reverse proxy/PHP frontend.',
  'بسته شامل API probe امن است که از فایل Token محدود و Header Authorization روی HTTPS استفاده می‌کند؛ توکن واقعی در History دستور یا Screenshot قرار نمی‌گیرد. فقط نسخه API و تعداد میزبان قابل مشاهده چاپ می‌شود. پس از افزودن قابلیت Allowlist نقش را بررسی کنید؛ جزئیات خطای API می‌تواند Method ممنوع را مشخص کند. Header Authorization را در Reverse Proxy/PHP رابط زبیکس حفظ کنید.')
code('''sudo test -e /etc/grafana/secrets/zabbix-token || sudo install -o root -g root -m 0600 /dev/null /etc/grafana/secrets/zabbix-token
sudoedit /etc/grafana/secrets/zabbix-token
sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py''')
sub('Configure and test the Grafana data source','تنظیم و آزمون منبع داده گرافانا')
table([('Setting','تنظیم'),('Value / verification','مقدار / اعتبارسنجی')],[
 [('Name / URL','نام / URL'),('Zabbix; full verified HTTPS api_jsonrpc.php endpoint.','Zabbix؛ Endpoint کامل و بررسی‌شده api_jsonrpc.php با HTTPS.')],
 [('Auth type / API Token','نوع احراز / Token'),('API token; paste only into the masked secure field.','API token؛ فقط در Field امن و Maskشده وارد کنید.')],
 [('Trends','Trend'),('Enabled; After 7d and Range 4d only when history retention matches.','فعال؛ After برابر 7d و Range برابر 4d فقط در صورت تطبیق Retention تاریخچه.')],
 [('Cache TTL / timeout','Cache TTL / مهلت'),('1h metadata cache; 30s API timeout, queryTimeout 60s.','Cache متادیتا 1h؛ API timeout برابر 30s و queryTimeout برابر 60s.')],
 [('Direct DB / acknowledgments','Direct DB / تأیید رخداد'),('Direct DB off; disable read-only-user acknowledgments on.','Direct DB خاموش؛ منع Acknowledge کاربر Read-only روشن.')]])
p('Open Connections > Data sources > Zabbix after adding it. Set the verified URL, Auth type API token, and Additional settings > Trends / Zabbix API. Click Save & test. Expected success is “Zabbix API version” followed by the detected 7.0 version; wording can vary by patch. Then confirm the host picker lists only authorized groups, run a real CPU item query and verify points arrive. An API-version success alone does not establish working item queries.',
  'پس از افزودن، Connections > Data sources > Zabbix را باز کنید. URL بررسی‌شده، Auth type برابر API token و Additional settings > Trends / Zabbix API را تنظیم کنید. Save & test را بزنید. خروجی موفق مورد انتظار “Zabbix API version” همراه نسخه شناسایی‌شده 7.0 است؛ متن ممکن است بین Patchها تغییر کند. سپس محدودبودن Host picker به گروه مجاز، Query واقعی آیتم CPU و دریافت Point را بررسی کنید. موفقیت نسخه API به‌تنهایی کارکرد Query آیتم را اثبات نمی‌کند.')
filecode('zabbix-datasource.yaml','yaml')
code('''sudo install -m 0600 -o root -g root grafana.env.example /etc/grafana/grafana-integration.env
sudoedit /etc/grafana/grafana-integration.env
sudo install -d -m 0755 /etc/systemd/system/grafana-server.service.d
sudo install -m 0644 grafana-environment.conf /etc/systemd/system/grafana-server.service.d/integration.conf
sudo install -o root -g grafana -m 0640 zabbix-datasource.yaml /etc/grafana/provisioning/datasources/zabbix.yaml
sudo systemctl daemon-reload
sudo systemctl restart grafana-server
sudo journalctl -u grafana-server -n 100 --no-pager''')
p('Replace the example token locally before restarting. systemd reads the protected EnvironmentFile and passes variables to Grafana; provisioning resolves $ZABBIX_API_TOKEN into secureJsonData.apiToken. The single-dollar form preserves literal dollar characters in a token. With editable:false, change the YAML and bump version rather than editing the provisioned source in the UI. A missing or empty environment variable causes authentication failure. Do not display systemctl show Environment or copy the edited file to the public downloads folder.',
  'پیش از Restart توکن نمونه را محلی جایگزین کنید. systemd فایل EnvironmentFile محافظت‌شده را می‌خواند و متغیر را به گرافانا می‌دهد؛ Provisioning مقدار $ZABBIX_API_TOKEN را در secureJsonData.apiToken حل می‌کند. فرم تک‌دلار کاراکتر دلار واقعی توکن را حفظ می‌کند. با editable:false به‌جای ویرایش UI، YAML را تغییر و version را افزایش دهید. متغیر خالی یا مفقود موجب شکست احراز هویت است. systemctl show Environment را نمایش ندهید و فایل ویرایش‌شده را به پوشه دانلود عمومی کپی نکنید.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/configure/','Official authentication, provisioning and Save & test fields','Field رسمی احراز هویت، Provisioning و Save & test')
link('https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/api_tokens','Zabbix 7.0 API-token administration','مدیریت توکن API در Zabbix 7.0')

section('linux-dashboard','7. Build the Linux monitoring dashboard','۷. ساخت داشبورد مانیتورینگ لینوکس')
figure('grafana-linux-windows-monitoring-dashboard.png','Illustrative Linux and Windows dashboard: utilization, filesystem space, interface throughput and availability','نمونه آموزشی داشبورد Linux و Windows: مصرف منابع، فضای فایل‌سیستم، ترافیک Interface و دسترس‌پذیری')
p('Use the hosts already enrolled in Zabbix. Link the official Linux by Zabbix agent template, or its active variant where appropriate, to an Ubuntu host such as linux-app-01. Check Data collection > Hosts > Items and Monitoring > Latest data for supported items and recent values before opening Grafana. Agent 2 can supply the official agent template metrics. Allow low-level discovery to create real filesystem and interface items; do not query unresolved {#IFNAME} or {#FSNAME} prototypes.',
  'از میزبان‌های ثبت‌شده موجود در زبیکس استفاده کنید. Template رسمی Linux by Zabbix agent یا نسخه Active مناسب را به Ubuntu مانند linux-app-01 متصل کنید. پیش از گرافانا در Data collection > Hosts > Items و Monitoring > Latest data آیتم Supported و مقدار تازه را بررسی کنید. Agent 2 می‌تواند معیارهای Template رسمی Agent را ارائه کند. اجازه دهید Low-level discovery آیتم واقعی فایل‌سیستم و Interface بسازد؛ Prototype حل‌نشده {#IFNAME} یا {#FSNAME} را Query نکنید.')
table([('Panel','پنل'),('Existing item / key','آیتم موجود / کلید'),('Visualization / units','نمایش / واحد')],[
 [('CPU utilization','مصرف CPU'),('CPU utilization — system.cpu.util','CPU utilization — system.cpu.util'),('Gauge, percent 0–100','Gauge، درصد ۰ تا ۱۰۰')],
 [('CPU load','بار CPU'),('Load average (1m avg) — system.cpu.load[all,avg1]; also avg5, avg15','Load average (1m avg) — system.cpu.load[all,avg1]؛ همچنین avg5 و avg15'),('Time series, unit none; compare to CPU count','Time series، بدون واحد؛ مقایسه با تعداد CPU')],
 [('RAM / available','RAM / قابل استفاده'),('Memory utilization — vm.memory.utilization; Available memory — vm.memory.size[available]','Memory utilization — vm.memory.utilization؛ Available memory — vm.memory.size[available]'),('Gauge percent / time series bytes','Gauge درصد / Time series بایت')],
 [('Filesystem usage / free','مصرف / فضای فایل‌سیستم'),('FS [/]: Space: Used, in % / Space: Available; vfs.fs.dependent.size[/,pused] / [/,free]','FS [/]: Space: Used, in % / Space: Available؛ vfs.fs.dependent.size[/,pused] / [/,free]'),('Gauge percent / bytes; select discovered mount','Gauge درصد / بایت؛ انتخاب Mount کشف‌شده')],
 [('Network / interface','شبکه / Interface'),('Interface ens18: Bits received / Bits sent — net.if.in[ens18] / net.if.out[ens18] after template preprocessing','Interface ens18: Bits received / Bits sent — net.if.in[ens18] / net.if.out[ens18] پس از Preprocessing تمپلیت'),('Time series bits/sec, already rates','Time series بیت بر ثانیه؛ از قبل Rate هستند')],
 [('Uptime / availability','Uptime / دسترس‌پذیری'),('System uptime — system.uptime; Zabbix agent availability — zabbix[host,agent,available]','System uptime — system.uptime؛ Zabbix agent availability — zabbix[host,agent,available]'),('Stat seconds / 0 unknown, 1 available, 2 unavailable','Stat ثانیه / ۰ نامشخص، ۱ در دسترس، ۲ خارج از دسترس')],
 [('Active problems','رخداد فعال'),('Problems query filtered by Group and Host','Query نوع Problems فیلترشده با Group و Host'),('Zabbix Problems panel, current events','پنل Zabbix Problems، رخداد جاری')]])
p('Names above are examples from the official 7.0 template; discovered interface and mount labels depend on the host. FS used percentage follows the template’s available-space semantics, which may differ from a naïve df calculation on filesystems with reserved blocks. Never graph agent.ping as a down indicator: its last stored value can remain 1 during an outage. Pair availability with Zabbix no-data/availability triggers, problem events and latest timestamp; active-only deployments may need zabbix[host,active_agent,available] rather than the passive availability item.',
  'نام‌های بالا از Template رسمی 7.0 هستند؛ Label کشف‌شده Interface و Mount به میزبان بستگی دارد. درصد مصرف FS از معنای فضای قابل استفاده Template پیروی می‌کند و در فایل‌سیستم دارای Reserved block ممکن است با محاسبه ساده df تفاوت داشته باشد. agent.ping را شاخص Down در نظر نگیرید؛ آخرین مقدار ذخیره‌شده در قطعی می‌تواند ۱ باقی بماند. دسترس‌پذیری را با Trigger مربوط به No-data/Availability، رخداد و Timestamp تازه همراه کنید؛ استقرار فقط Active ممکن است به zabbix[host,active_agent,available] به‌جای آیتم Passive نیاز داشته باشد.')
items([
 ('Open Dashboards > New > New dashboard > Add visualization. Select Zabbix, Query type Metrics, Group Linux servers, Host linux-app-01, Item CPU utilization. Run the query and compare the last value to Zabbix Latest data.','Dashboards > New > New dashboard > Add visualization را باز کنید. Zabbix، Query نوع Metrics، Group برابر Linux servers، Host برابر linux-app-01 و Item برابر CPU utilization انتخاب کنید. Query را اجرا و آخرین مقدار را با Latest data زبیکس مقایسه کنید.'),
 ('Choose Gauge, Unit Percent (0–100), Min 0, Max 100, absolute thresholds green below 80, orange from 80, red from 90. Threshold colors are presentation; they do not create Zabbix triggers or alert rules.','Gauge، واحد Percent (0–100)، حداقل ۰، حداکثر ۱۰۰ و Threshold مطلق سبز زیر ۸۰، نارنجی از ۸۰ و قرمز از ۹۰ تعیین کنید. رنگ Threshold فقط نمایش است و Trigger یا Rule هشدار نمی‌سازد.'),
 ('Add CPU load and available-memory Time series panels, disk Gauge panels, network Time series and uptime/availability Stat panels using the table. Set bytes for memory, bits/sec for network and seconds for uptime.','با جدول پنل Time series بار CPU و حافظه قابل استفاده، Gauge دیسک، Time series شبکه و Stat مربوط به Uptime/Availability بسازید. واحد حافظه بایت، شبکه بیت بر ثانیه و Uptime ثانیه باشد.'),
 ('Add the plugin’s Zabbix Problems visualization with Query type Problems, Show Problems, Group $group, Host $host, all severities and Use time range off for current incidents. Keep the integration read-only.','نمایش Zabbix Problems افزونه با Query نوع Problems، گزینه Show Problems، Group برابر $group و Host برابر $host، همه Severityها و Use time range خاموش برای رخداد جاری اضافه کنید. اتصال را Read-only نگه دارید.'),
 ('Save into Enterprise monitoring with a 6-hour default range and 1-minute refresh. Import linux-dashboard.json using Dashboards > New > Import, or use the supplied file provider. It references the provisioned zabbix-enterprise UID; change every reference if using a different UID.','در Enterprise monitoring با بازه پیش‌فرض ۶ ساعت و Refresh یک دقیقه ذخیره کنید. linux-dashboard.json را از Dashboards > New > Import وارد یا از File provider همراه استفاده کنید. فایل به UID ثابت zabbix-enterprise اشاره می‌کند؛ در UID متفاوت همه Referenceها را تغییر دهید.')])
link('https://github.com/zabbix/zabbix/blob/release/7.0/templates/os/linux/template_os_linux.yaml','Official Zabbix 7.0 Linux template and preprocessing','Template و Preprocessing رسمی Linux در Zabbix 7.0')
sub('Provision reviewed dashboard files','Provisioning فایل داشبورد بررسی‌شده')
code('''sudo install -d -o root -g grafana -m 0750 /var/lib/grafana/dashboards
sudo install -o root -g grafana -m 0640 linux-dashboard.json windows-dashboard.json enterprise-noc-dashboard.json /var/lib/grafana/dashboards/
sudo install -o root -g grafana -m 0640 dashboard-provider.yaml /etc/grafana/provisioning/dashboards/enterprise.yaml
sudo systemctl restart grafana-server''')
filecode('dashboard-provider.yaml','yaml')
p('Use either UI import or file provisioning for the same dashboard UID; avoid competing owners. The provider creates the Enterprise monitoring folder, polls files every 60s and disables UI saves for file-owned dashboards. Keep the NOC overview unused until its collector is installed. Update source JSON in version control and validate it before replacing provisioned files.',
  'برای UID یکسان از UI import یا File provisioning استفاده کنید و مالک هم‌زمان نداشته باشید. Provider پوشه Enterprise monitoring را ایجاد، فایل را هر 60s بررسی و Save در UI برای داشبورد File-owned را غیرفعال می‌کند. Overview مربوط به NOC تا نصب Collector استفاده نشود. JSON منبع را در Version control به‌روز و پیش از جایگزینی بررسی کنید.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/query-editor/','Supported metric and Problems query editor fields','Field پشتیبانی‌شده Query متریک و Problems')

section('windows-dashboard','8. Build the Windows Server dashboard','۸. ساخت داشبورد Windows Server')
p('Create a separate Windows dashboard from existing Windows Agent 2 hosts. The official Windows by Zabbix agent template or active variant discovers interfaces, filesystems and services; verify discovery filters and the required agent version in your installed 7.0 template. Review service discovery exclusions so intentional stopped services do not create unnecessary incidents. Grafana selects existing item names; it does not install agents or enable Windows performance counters.',
  'برای میزبان موجود Windows Agent 2 داشبورد Windows جدا بسازید. Template رسمی Windows by Zabbix agent یا نسخه Active، Interface، فایل‌سیستم و سرویس را کشف می‌کند؛ فیلتر Discovery و نسخه Agent لازم را در Template نصب‌شده 7.0 بررسی کنید. استثنای Service discovery را مرور کنید تا سرویس عمداً متوقف‌شده رخداد غیرضروری نسازد. گرافانا آیتم موجود را انتخاب می‌کند و Agent نصب یا Performance counter ویندوز را فعال نمی‌کند.')
table([('Panel','پنل'),('Official 7.0 item selector / key','انتخاب‌گر آیتم رسمی 7.0 / کلید')],[
 [('CPU / RAM','CPU / RAM'),('CPU utilization — system.cpu.util; Memory utilization — vm.memory.util; Used memory — vm.memory.size[used]','CPU utilization — system.cpu.util؛ Memory utilization — vm.memory.util؛ Used memory — vm.memory.size[used]')],
 [('Disk / free space','دیسک / فضای آزاد'),('FS [label(C:)]: Space: Used, in % / Space: Available; dependent filesystem items from vfs.fs.get','FS [label(C:)]: Space: Used, in % / Space: Available؛ آیتم فایل‌سیستم Dependent از vfs.fs.get')],
 [('Network throughput','توان عملیاتی شبکه'),('Interface name(alias): Bits received / Bits sent; discovered net.if.in / net.if.out keys','Interface name(alias): Bits received / Bits sent؛ کلید کشف‌شده net.if.in / net.if.out')],
 [('Windows services','سرویس ویندوز'),('State of service "Spooler" (Print Spooler) — service.info["Spooler",state], when discovered','State of service "Spooler" (Print Spooler) — service.info["Spooler",state] در صورت کشف')],
 [('Uptime / availability','Uptime / دسترس‌پذیری'),('Uptime — system.uptime (Linux item name is different); Zabbix agent availability','Uptime — system.uptime (نام آیتم Linux متفاوت است)؛ Zabbix agent availability')],
 [('Problems / Event Log','Problems / Event Log'),('Current Problems query; Text query for a separately configured active Event Log item','Query رخداد جاری Problems؛ Query نوع Text برای آیتم Active جداگانه Event Log')]])
p('Import windows-dashboard.json, select the Windows servers group and the host, and run each query. Use gauges for CPU/memory/disk, time series for network, and stat/value mappings for service state. service.info state uses 0 for running, 6 for stopped and 255 for no such service; do not reuse availability mappings (1/2) for service states. CPU/Memory thresholds 80/90 are example visualization limits and should be tuned to service baselines.',
  'windows-dashboard.json را Import، گروه Windows servers و میزبان را انتخاب و هر Query را اجرا کنید. CPU/Memory/Disk را Gauge، شبکه را Time series و وضعیت سرویس را Stat با Value mapping نمایش دهید. service.info برای Running مقدار ۰، Stopped مقدار ۶ و سرویس ناموجود ۲۵۵ دارد؛ Mapping دسترس‌پذیری ۱/۲ را برای Service state تکرار نکنید. Threshold نمونه ۸۰/۹۰ برای CPU/Memory صرفاً نمایش است و باید با مبنای سرویس تنظیم شود.')
sub('Add Windows Event Log monitoring explicitly','افزودن صریح مانیتورینگ Windows Event Log')
p('The default OS template does not collect arbitrary Event Logs. On a Windows host with active Agent 2 connectivity, create a Zabbix agent (active) item named Windows Application errors, Type of information Log, Update interval 30s, History 7d and the supported eventlog key below. The skip mode starts with new events; it does not backfill the entire log. Agent privileges must permit reading the selected channel; Security logs require a separate privilege review.',
  'Template پیش‌فرض سیستم‌عامل Event Log دلخواه را جمع‌آوری نمی‌کند. روی میزبان Windows با اتصال Active Agent 2، آیتم Zabbix agent (active) با نام Windows Application errors، نوع اطلاعات Log، فاصله 30s، تاریخچه 7d و کلید eventlog پشتیبانی‌شده زیر بسازید. حالت skip از رخداد جدید شروع می‌کند و کل Log را بازخوانی نمی‌کند. مجوز Agent باید خواندن Channel منتخب را اجازه دهد؛ Security log بررسی مجوز جداگانه می‌خواهد.')
code('eventlog[Application,,"Error|Critical",,,,skip]','text')
p('Add tag component:eventlog to that custom item. Generate one approved test Application error in staging and verify it reaches Latest data before testing the Grafana Text query. The downloaded table panel targets that exact custom name and will legitimately show No data until the item exists and receives a new matching log entry. Event content may be sensitive; restrict users and retention. Keep log events and numeric count-based alerting separate.',
  'تگ component:eventlog را به آیتم سفارشی اضافه کنید. یک Application error مجاز در Staging تولید و قبل از Query نوع Text گرافانا دریافت آن در Latest data را بررسی کنید. پنل Table دانلودشده همان نام سفارشی را Query می‌کند و تا ساخت آیتم و دریافت رخداد جدید مطابق فیلتر، به‌درستی No data نشان می‌دهد. محتوای رخداد ممکن است حساس باشد؛ کاربر و Retention را محدود کنید. رخداد Log و هشدار بر پایه Count عددی را جدا تنظیم کنید.')
link('https://github.com/zabbix/zabbix/blob/release/7.0/templates/os/windows_agent/template_os_windows_agent.yaml','Official Zabbix Windows template: real item names','Template رسمی Windows زبیکس: نام واقعی آیتم')
link('https://www.zabbix.com/documentation/7.0/en/manual/config/items/itemtypes/zabbix_agent/win_keys','Supported Windows agent keys, service.info and eventlog','کلید پشتیبانی‌شده Agent ویندوز، service.info و eventlog')

section('noc-dashboard','9. Enterprise NOC dashboard with implemented counts','۹. داشبورد NOC سازمانی با شمارنده پیاده‌سازی‌شده')
figure('grafana-enterprise-noc-dashboard.png','Illustrative NOC: scoped host counts, unknown availability, critical incidents, top consumers and latest problems','نمونه آموزشی NOC: تعداد میزبان در محدوده، دسترس‌پذیری نامشخص، رخداد بحرانی، مصرف‌کننده برتر و آخرین Problem')
p('The overview is a real dashboard definition backed by the supplied collector, not hard-coded demo numbers. Native plugin Problems queries produce event tables, and Metrics queries return item series; neither is presented here as an automatic total-host inventory counter. The optional noc-collector.py uses host.get and problem.get, then zabbix_sender with TLS PSK to publish eight explicitly defined trapper metrics. It requires no direct database connection.',
  'Overview تعریف واقعی داشبورد با Collector همراه است و عدد نمایشی ثابت ندارد. Query نوع Problems افزونه جدول رخداد و Metrics سری آیتم می‌دهد؛ هیچ‌کدام اینجا به‌عنوان شمارنده خودکار موجودی کل میزبان معرفی نمی‌شود. noc-collector.py اختیاری از host.get و problem.get استفاده و سپس با zabbix_sender و TLS PSK هشت معیار Trapper صریح را منتشر می‌کند. این روش اتصال مستقیم دیتابیس نمی‌خواهد.')
table([('Metric','معیار'),('Implemented definition','تعریف پیاده‌سازی‌شده')],[
 [('Total monitored hosts','کل میزبان مانیتورشده'),('Enabled monitored hosts visible to the API user in configured group IDs, deduplicated by host.get. Exclude the summary host.','میزبان فعال مانیتورشده قابل مشاهده برای کاربر API در ID گروه تعیین‌شده، بدون تکرار از host.get؛ میزبان Summary مستثنا باشد.')],
 [('Available / unavailable / unknown','در دسترس / خارج از دسترس / نامشخص'),('Available if any active-agent/interface state is 1; unavailable if none is 1 and any is 2; unknown otherwise. These partition the total, not a universal application-health SLA.','در دسترس اگر هر وضعیت Active-agent/Interface برابر ۱؛ خارج از دسترس اگر هیچ ۱ و حداقل یک ۲؛ در سایر موارد نامشخص. مجموع برابر کل است و SLA عمومی سلامت برنامه نیست.')],
 [('Active / critical / high problems','Problem فعال / بحرانی / High'),('problem.get countOutput, recent:false, source 0/object 0, same monitored host IDs; critical maps to Zabbix Disaster (5), High is exactly 4. Includes suppressed incidents unless policy explicitly changes.','problem.get با countOutput و recent:false، source 0/object 0 و همان ID میزبان؛ بحرانی برابر Disaster (۵)، High دقیقاً ۴؛ رخداد Suppressed لحاظ می‌شود مگر سیاست صریح تغییر کند.')],
 [('Collector freshness','تازگی Collector'),('Epoch timestamp written after successful API queries; nodata 5-minute trigger in the template. Missing/stale data never implies zero incidents.','Timestamp پس از Query موفق API؛ Trigger پنج دقیقه nodata در Template. داده مفقود یا قدیمی هرگز به معنای صفر رخداد نیست.')],
 [('Top consumers','بیشترین مصرف‌کننده'),('Last non-null metric per series, reduce to rows, sort descending and limit to 10. CPU/RAM per host, disk per filesystem, network per interface.','آخرین مقدار Non-null هر سری، Reduce به Row، Sort نزولی و Limit ده؛ CPU/RAM برای میزبان، دیسک برای FS و شبکه برای Interface.')],
 [('Operational Problems','Problems عملیاتی'),('Supported plugin Problems panel with host, host group, severity, status and acknowledgment columns; all current severities.','پنل Problems افزونه با ستون Host، Host group، Severity، Status و Acknowledgment؛ همه Severity جاری.')]])
sub('Install the NOC summary collector and template','نصب Collector و Template خلاصه NOC')
items([
 ('In Zabbix Data collection > Templates > Import, import zabbix-noc-template.json. Create host group NOC collectors and technical host noc-production, visible name Production NOC summary; link Enterprise NOC summary.','در Data collection > Templates > Import فایل zabbix-noc-template.json را Import کنید. گروه NOC collectors و میزبان فنی noc-production با نام نمایشی Production NOC summary بسازید؛ Enterprise NOC summary را متصل کنید.'),
 ('Set the host macro {$NOC.SENDER.IP} to the real collector source IP; configure incoming encryption PSK, unique identity noc-production and a generated PSK stored in a protected file. Trapper allowed_hosts and the server firewall must both permit that source only.','ماکروی میزبان {$NOC.SENDER.IP} را IP واقعی Collector قرار دهید؛ رمزگذاری ورودی PSK با Identity یکتای noc-production و PSK تولیدشده در فایل محافظت‌شده تنظیم کنید. allowed_hosts آیتم Trapper و Firewall Server فقط همان مبدا را مجاز کنند.'),
 ('Create a separate read-only API token for the collector covering the monitored environment groups; it needs host.get and problem.get. Use hostgroup.get to discover IDs. Create Production/Linux servers and Production/Windows servers group names, or change the dashboard group regex to your convention.','برای Collector توکن API Read-only جدا با گروه‌های محیط مانیتورشده بسازید؛ host.get و problem.get لازم است. ID گروه را با hostgroup.get بگیرید. گروه Production/Linux servers و Production/Windows servers بسازید یا Regex گروه داشبورد را با قرارداد خود تغییر دهید.'),
 ('Copy the protected token and PSK to /etc/grafana/secrets/noc-token and noc.psk with root:noc-collector 0640 permissions. Fill noc.env with actual group IDs, API URL, server address and technical host name. Never put secrets in the supplied public template.','Token و PSK محافظت‌شده را در /etc/grafana/secrets/noc-token و noc.psk با مجوز root:noc-collector 0640 قرار دهید. noc.env را با ID واقعی گروه، API URL، آدرس Server و نام فنی میزبان کامل کنید. Secret را در Template عمومی قرار ندهید.')])
code('''sudo apt-get install -y zabbix-sender
sudo useradd --system --no-create-home --shell /usr/sbin/nologin noc-collector
sudo install -d -o root -g noc-collector -m 0750 /usr/local/lib/grafana-noc
sudo install -m 0755 noc-collector.py /usr/local/lib/grafana-noc/noc-collector.py
sudo install -o root -g noc-collector -m 0640 noc-collector.env.example /etc/grafana/noc.env
sudoedit /etc/grafana/noc.env
sudo install -m 0644 noc-collector.service noc-collector.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl start noc-collector.service
sudo journalctl -u noc-collector.service -n 30 --no-pager
sudo systemctl enable --now noc-collector.timer''')
p('Use a zabbix_sender build supporting TLS PSK, preferably the maintained Zabbix 7.0 package if the official repository is already configured. Successful collection prints Submitted 8 NOC metrics; verify all eight latest values in Zabbix. API failure or an empty permitted host scope fails the collector rather than publishing misleading zeros. Sender rejection also fails the service. Monitor that failure and the template’s nodata trigger through Zabbix actions outside Grafana.',
  'از Build مربوط به zabbix_sender دارای TLS PSK استفاده کنید؛ اگر مخزن رسمی تنظیم است بسته نگهداری‌شده Zabbix 7.0 ترجیح دارد. جمع‌آوری موفق Submitted 8 NOC metrics چاپ می‌کند؛ هر هشت مقدار Latest data را در زبیکس بررسی کنید. شکست API یا محدوده خالی میزبان مجاز باعث شکست Collector می‌شود و صفر گمراه‌کننده منتشر نمی‌شود. Reject در Sender نیز Service را Fail می‌کند. شکست و Trigger مربوط به nodata را با Action زبیکس خارج از گرافانا پایش کنید.')
p('Import enterprise-noc-dashboard.json. Counts follow the Environment selector and its summary host; they remain counts for the collector’s configured scope when you narrow the performance Group/Host selectors. Display that scope prominently and reconcile counts against Zabbix. For Staging create a separate noc-staging host with visible name Staging NOC summary and a separately configured collector. Do not select an environment that has no collector. Ensure known manual/suppressed incidents and a canary unavailable host match the documented counting rules.',
  'enterprise-noc-dashboard.json را Import کنید. شمارنده از Environment و میزبان Summary آن پیروی می‌کند؛ با محدودکردن Group/Host بخش Performance، همچنان شمارنده محدوده تنظیم‌شده Collector باقی می‌ماند. محدوده را واضح نمایش و تعداد را با زبیکس تطبیق دهید. برای Staging میزبان جدا noc-staging با نام نمایشی Staging NOC summary و Collector مستقل بسازید. محیط بدون Collector انتخاب نکنید. رخداد Manual/Suppressed شناخته‌شده و میزبان Canary خارج از دسترس باید با تعریف شمارش تطبیق داشته باشد.')
p('Put overview stats at the top, Top 10 CPU/RAM/filesystem/interface panels in the middle and current Problems at the bottom. Show the last-success timestamp beside host counts, retain No data text and never replace unavailable data with zeros. Make problem acknowledgment a Zabbix-controlled workflow: the read-only Grafana integration cannot acknowledge incidents. API/interface availability describes monitoring transport; add application-specific health metrics for service SLOs.',
  'Statهای Overview را بالا، Top 10 مربوط به CPU/RAM/FS/Interface را وسط و Problems جاری را پایین قرار دهید. Timestamp آخرین موفقیت کنار شمارنده باشد، متن No data حفظ و داده ناموجود با صفر جایگزین نشود. Acknowledge رخداد Workflow کنترل‌شده زبیکس باشد؛ اتصال Read-only گرافانا نمی‌تواند رخداد را تأیید کند. Availability مربوط به API/Interface انتقال مانیتورینگ را نشان می‌دهد؛ برای SLO سرویس معیار سلامت برنامه اضافه کنید.')
link('https://www.zabbix.com/documentation/7.0/en/manual/api/reference/host/get','Zabbix host.get monitored-host scope','محدوده میزبان مانیتورشده در host.get زبیکس')
link('https://www.zabbix.com/documentation/7.0/en/manual/api/reference/problem/get','Zabbix problem.get counting semantics','معنای شمارش problem.get زبیکس')

p('The supplied last-success Stat uses the official scale(1000) query function: Zabbix unixtime values are epoch seconds, while Grafana date units require milliseconds. Keep the conversion when editing the panel and verify its date against the collector log.',
  'Stat آخرین موفقیت همراه از تابع رسمی Query یعنی scale(1000) استفاده می‌کند: مقدار unixtime زبیکس ثانیه Epoch است و واحد تاریخ گرافانا میلی‌ثانیه می‌خواهد. هنگام ویرایش پنل تبدیل را حفظ و تاریخ را با Log جمع‌آوری‌کننده تطبیق دهید.')
section('dashboard-variables','10. Dashboard variables and dynamic host selection','۱۰. متغیر داشبورد و انتخاب پویای میزبان')
p('Open Dashboard settings > Variables > Add variable. Use Query variables with data source Zabbix and the structured query editor shown below. Refresh on dashboard load is enough for most inventory selectors. Set dependent variables after their parents. The current plugin supports legacy brace syntax but automatically converts it; new dashboards use the official structured query fields. Zabbix 7.0 removed Applications: use Item tag for relevant item grouping.',
  'Dashboard settings > Variables > Add variable را باز کنید. Query variable با منبع Zabbix و Editor ساختاریافته زیر بسازید. برای انتخاب‌گر موجودی معمولاً Refresh هنگام Load کافی است. متغیر وابسته بعد از والد قرار گیرد. افزونه جاری فرم Brace قدیمی را پشتیبانی و خودکار تبدیل می‌کند؛ داشبورد جدید از Field رسمی ساختاریافته استفاده می‌کند. Zabbix 7.0 مفهوم Applications را حذف کرده؛ برای گروه‌بندی آیتم از Item tag استفاده کنید.')
table([('Variable','متغیر'),('Official query / filter','Query / فیلتر رسمی')],[
 [('group','group'),('Query Type Group; Group /Linux servers|Windows servers/ (NOC: /^$environment\\/.*/)','Query Type Group؛ Group برابر /Linux servers|Windows servers/ (در NOC: /^$environment\\/.*/)')],
 [('host','host'),('Query Type Host; Group $group; Host /.*/','Query Type Host؛ Group برابر $group؛ Host برابر /.*/')],
 [('item_group','item_group'),('Query Type Item tag; Group $group; Host $host; Item Tag /component:.*/','Query Type Item tag؛ Group برابر $group؛ Host برابر $host؛ Item Tag برابر /component:.*/')],
 [('interface','interface'),('Query Type Item; Group $group; Host $host; Item /Interface .*: Bits received/; returned full item names','Query Type Item؛ Group برابر $group؛ Host برابر $host؛ Item برابر /Interface .*: Bits received/؛ خروجی نام کامل آیتم')],
 [('environment','environment'),('Custom: Production,Staging; group-name convention scopes NOC queries. Not a native inferred environment field.','Custom برابر Production,Staging؛ قرارداد نام گروه محدوده Query NOC را تعیین می‌کند. Field خودکار شناسایی محیط نیست.')]])
code('''{"queryType":"group","group":"/Linux servers|Windows servers/"}
{"queryType":"host","group":"$group","host":"/.*/"}
{"queryType":"itemTag","group":"$group","host":"$host","itemTag":"/component:.*/"}
{"queryType":"item","group":"$group","host":"$host","item":"/Interface .*: Bits received/"}''','json')
p('In Metrics queries set Group $group, Host $host and Item tag $item_group. Enable Multi-value and Include All where useful and use /.*/ as the All value; the plugin handles variable substitution. The downloadable interface selector stores the full received-item name, avoiding brittle regex extraction of Windows GUIDs; use Item $interface in the dedicated inbound traffic panel. The all-interface panel uses /Interface .*: Bits (received|sent)/. Do not apply rate twice to template items already converted to bits/sec.',
  'در Metrics مقدار Group برابر $group، Host برابر $host و Item tag برابر $item_group باشد. در صورت نیاز Multi-value و Include All را فعال و All value را /.*/ تعیین کنید؛ افزونه جایگزینی متغیر را انجام می‌دهد. انتخاب‌گر Interface دانلودشده نام کامل آیتم دریافت را نگه می‌دارد تا استخراج شکننده GUID ویندوز لازم نباشد؛ در پنل اختصاصی ترافیک ورودی Item برابر $interface باشد. پنل همه Interfaceها از /Interface .*: Bits (received|sent)/ استفاده می‌کند. Rate را روی آیتمی که Template از قبل به بیت بر ثانیه تبدیل کرده دوباره اعمال نکنید.')
p('CPU utilization and Memory utilization have common names on Linux and Windows, so changing group/host dynamically updates those panels. OS-specific load and service panels remain in their separate dashboards; Uptime versus System uptime needs /^(System uptime|Uptime)$/ on a mixed-OS dashboard. Tags such as component:cpu can intentionally remove unrelated metrics: restore All if a panel shows No data. Variables improve navigation but cannot hide data from an authorized data-source user.',
  'CPU utilization و Memory utilization در Linux و Windows نام مشترک دارند و تغییر Group/Host پنل را پویا به‌روز می‌کند. Load مخصوص OS و Service در داشبورد جدا باقی می‌ماند؛ برای Uptime در داشبورد مشترک از /^(System uptime|Uptime)$/ استفاده کنید. تگ component:cpu عمداً معیار نامرتبط را حذف می‌کند؛ اگر پنل No data شد All را بازگردانید. متغیر پیمایش را بهتر می‌کند ولی داده را از کاربر مجاز منبع پنهان نمی‌کند.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/template-variables/','Official structured variables and legacy conversion','متغیر ساختاریافته رسمی و تبدیل فرم قدیمی')

section('alerts-events','11. Alerts, events and duplicate-notification control','۱۱. هشدار، رخداد و کنترل اعلان تکراری')
p('Zabbix triggers evaluate monitored items, create PROBLEM/recovery events and dispatch actions through media types. Grafana’s Problems panel reads those events; displaying a red panel does not send a notification. Grafana Alerting evaluates its own server-side rules and routes notifications through contact points and notification policies. Choose one paging owner for each incident: normally Zabbix for OS/template triggers, Grafana for a deliberately separate cross-source rule.',
  'Trigger زبیکس آیتم را ارزیابی، رخداد PROBLEM/Recovery را ایجاد و Action را با Media type ارسال می‌کند. پنل Problems گرافانا این رخداد را می‌خواند؛ پنل قرمز اعلان نمی‌فرستد. Grafana Alerting قانون مستقل سمت سرور را ارزیابی و با Contact point و Notification policy مسیریابی می‌کند. برای هر رخداد یک مالک Paging تعیین کنید: معمولاً زبیکس برای Trigger سیستم‌عامل/Template و گرافانا برای Rule جداگانه بین منابع.')
items([
 ('Problems panel: Query type Problems, Show Problems, severity filters and acknowledgment columns. Acknowledged does not mean resolved; use event status. Set Use time range off for ongoing incidents that began before the selected range.','پنل Problems: Query نوع Problems، Show Problems، فیلتر Severity و ستون Acknowledgment؛ تأیید به معنای حل نیست و Status رخداد را بخوانید. Use time range برای رخداد جاری که پیش از بازه شروع شده خاموش باشد.'),
 ('Problem annotations: Dashboard settings > Annotations > Add annotation query > Zabbix; Group $group, Host $host, Min severity Warning, Show OK events and Show hostname enabled. Leave legacy Application blank on Zabbix 7.0. Correlate CPU spikes with problem/recovery markers.','Annotation رخداد: Dashboard settings > Annotations > Add annotation query > Zabbix؛ Group برابر $group، Host برابر $host، Min severity برابر Warning و Show OK events و Show hostname فعال. Application قدیمی در Zabbix 7.0 خالی باشد. جهش CPU را با Marker رخداد/بازیابی مرتبط کنید.'),
 ('Grafana rule: Alerting > Alert rules > New alert rule. Select a fixed approved Linux host and CPU utilization numeric Metrics query A over 10m; Reduce B = Mean(A), Threshold C = B > 90; set pending period 5m and evaluation interval 1m. Preview before saving.','Rule گرافانا: Alerting > Alert rules > New alert rule؛ میزبان ثابت مجاز Linux و Metrics عددی CPU utilization در Query A با بازه 10m؛ Reduce B برابر Mean(A) و Threshold C برابر B > 90؛ Pending برابر 5m و فاصله ارزیابی 1m. پیش از Save، Preview کنید.'),
 ('Only Metrics and Item ID are supported by plugin alerting. Problems, Triggers, Services, Text and User macros queries are not supported for alert rules; plugin-side processing functions are limited. Use Grafana expressions for reductions and no dashboard variables in server-side alerts.','هشدار افزونه فقط Metrics و Item ID را پشتیبانی می‌کند. Problems، Triggers، Services، Text و User macros برای Rule هشدار پشتیبانی نمی‌شوند و Function پردازش سمت افزونه محدود است. Reduction را با Expression گرافانا انجام دهید و متغیر داشبورد در هشدار سمت سرور به کار نبرید.'),
 ('Set no-data/error handling deliberately; route query failures to the monitoring owner rather than treating them as OK. Add labels owner, environment, service and source=grafana; include a runbook URL and impact text.','رفتار No-data/Error را آگاهانه تعیین کنید؛ شکست Query به مالک مانیتورینگ ارجاع شود و OK تلقی نشود. Label مربوط به owner، environment، service و source=grafana با URL راهنما و اثر رخداد اضافه کنید.')])
sub('Email, Telegram and notification policies','ایمیل، تلگرام و سیاست اعلان')
p('For email configure [smtp] with the approved relay host, from_address, certificate trust and required STARTTLS policy. Store SMTP password using an approved environment/file provider; do not add it to dashboard JSON. Under Alerting > Contact points (or Notification configuration > Contact points in the updated navigation), create an Email contact point and send a test to the approved staging recipient. Under Notification policies route labels such as environment=Production to the right team, with grouping and repeat intervals.',
  'برای ایمیل [smtp] را با Relay مجاز، from_address، CA trust و سیاست STARTTLS لازم تنظیم کنید. رمز SMTP را با Environment/File provider مجاز نگه دارید و در JSON داشبورد قرار ندهید. در Alerting > Contact points (یا Notification configuration > Contact points در پیمایش جدید) Contact point نوع Email بسازید و Test را به گیرنده Staging مجاز ارسال کنید. در Notification policies برچسب مانند environment=Production را با Grouping و Repeat interval به تیم درست مسیریابی کنید.')
code('''[smtp]
enabled = true
host = smtp.example.com:587
user = grafana-notifications
password = $__file{/etc/grafana/secrets/smtp-password}
from_address = grafana@example.com
from_name = Enterprise Monitoring
startTLS_policy = MandatoryStartTLS
skip_verify = false''','ini')
p('Grafana OSS supports a Telegram contact point for Grafana Alertmanager. Create an approved bot with BotFather, add it to the intended chat, obtain the chat ID, and enter bot token only in the secure contact-point field. Use the built-in Test function and then attach the contact point to a rule or policy. Restrict outbound access as required and never paste the bot token into a URL in article commands. Zabbix Telegram media types are a separate notification path.',
  'Grafana OSS برای Grafana Alertmanager از Contact point تلگرام پشتیبانی می‌کند. Bot مجاز با BotFather بسازید، به Chat مقصد اضافه، Chat ID را بگیرید و Bot token را فقط در Field امن Contact point وارد کنید. با Test داخلی بررسی و سپس Contact point را به Rule یا Policy متصل کنید. خروجی شبکه را در صورت نیاز محدود و Bot token را در URL دستور مقاله نگذارید. Media type تلگرام زبیکس مسیر اعلان جداست.')
p('To prevent duplicate paging, do not reproduce an existing Zabbix CPU trigger with the same Grafana recipient. Document rule ownership, use source/service labels for grouping, and coordinate maintenance silences in both systems when both legitimately own different rules. Test one PROBLEM, one recovery and a data-source failure in staging; confirm exactly the intended notifications and on-call escalation. Keep monitoring failure alerts outside the monitored Grafana instance.',
  'برای جلوگیری از Paging تکراری، Trigger موجود CPU زبیکس را با همان گیرنده در گرافانا بازتولید نکنید. مالک Rule را مستند، از Label منبع/سرویس برای Grouping استفاده و در صورت مالکیت Rule متفاوت، Silence نگهداری هر دو سیستم را هماهنگ کنید. یک PROBLEM، یک Recovery و شکست منبع داده را در Staging آزمون و فقط اعلان و Escalation مطلوب را تأیید کنید. هشدار شکست مانیتورینگ خارج از همان نمونه گرافانا باشد.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/alerting/','Official Zabbix plugin alerting limitations','محدودیت رسمی هشدار افزونه زبیکس')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/annotations/','Official problem annotations','Annotation رسمی رخداد')
ref('docs/grafana/latest/alerting/fundamentals/notifications/contact-points/','Supported Grafana contact-point integrations','Integration پشتیبانی‌شده Contact point گرافانا')
ref('blog/how-to-integrate-grafana-alerting-and-telegram/','Official Telegram integration tutorial','راهنمای رسمی اتصال تلگرام')

section('performance','12. Performance optimization and optional Direct DB','۱۲. بهینه‌سازی عملکرد و Direct DB اختیاری')
p('Use Zabbix history for recent fine-grained metrics and trends for hourly aggregates over longer ranges. Grafana does not generate missing trends: numeric items and Zabbix retention must actually provide them. Match Trends After to real history retention, and Range to the dashboard range at which hourly aggregates are acceptable. A 30-day dashboard should not repeatedly fetch millions of one-minute points.',
  'برای معیار تازه دقیق از History زبیکس و برای بازه بلند از Trend با تجمیع ساعتی استفاده کنید. گرافانا Trend ناموجود تولید نمی‌کند؛ آیتم عددی و Retention زبیکس باید آن را فراهم کند. Trends After با Retention واقعی History و Range با بازه قابل قبول برای تجمیع ساعتی هماهنگ باشد. داشبورد ۳۰ روزه نباید مکرراً میلیون‌ها Point یک‌دقیقه‌ای بگیرد.')
items([
 ('Start at 1m dashboard refresh, 6h default range and 30s minimum refresh. Set panel minimum intervals near the item collection interval; widening a range should increase the query interval.','از Refresh یک دقیقه، بازه پیش‌فرض ۶ ساعت و حداقل Refresh برابر 30s شروع کنید. حداقل Interval پنل نزدیک فاصله جمع‌آوری Item باشد؛ بازه گسترده‌تر باید Interval را افزایش دهد.'),
 ('Cache TTL caches item/host metadata, not a guarantee that live metric values are fresh. After enrollment or permission changes allow cache expiry or refresh/restart deliberately.','Cache TTL متادیتای Item/Host را Cache می‌کند و تضمین تازگی مقدار Live نیست. پس از ثبت میزبان یا تغییر مجوز زمان Expiry بدهید یا آگاهانه Refresh/Restart کنید.'),
 ('Avoid All hosts × All items on every panel. Limit groups, use exact names or bounded regex and split dashboards by team/environment. A Top 10 transformation still retrieves its input series: it does not reduce API workload at the source.','از All hosts × All items در هر پنل پرهیز کنید. Group محدود، نام دقیق یا Regex محدود و داشبورد جدا بر اساس تیم/محیط به کار برید. Transformation مربوط به Top 10 همچنان سری ورودی را می‌گیرد و بار API را در منبع کم نمی‌کند.'),
 ('Use Query inspector, browser timing and Grafana journal to distinguish API latency, data volume, transforms and rendering. Query timeout is a guardrail, not a cure. Monitor Zabbix API/PHP workers and PostgreSQL slow queries separately.','با Query inspector، Timing مرورگر و Journal گرافانا تأخیر API، حجم داده، Transform و Rendering را تفکیک کنید. Query timeout کنترل حفاظتی است و درمان نیست. Worker API/PHP زبیکس و Query کند PostgreSQL را مستقل پایش کنید.'),
 ('Leave historical item-value lookup and Host IP options off in Problems queries unless needed. Page large event lists, narrow filters and benchmark concurrency with representative NOC wallboards.','گزینه Historical item-value lookup و Host IP در Problems را مگر در صورت نیاز خاموش نگه دارید. فهرست رخداد بزرگ را صفحه‌بندی، فیلتر محدود و Concurrency را با Wallboard واقعی NOC اندازه‌گیری کنید.')])
sub('Optional PostgreSQL read-only access','دسترسی Read-only اختیاری PostgreSQL')
p('Direct DB is optional and only accelerates history/trend reads. The API is still required for metadata, permissions and Problems. On the existing Zabbix PostgreSQL database create the dedicated role below; do not grant SELECT on every table or use the database owner. A password is set interactively with psql, never embedded in SQL. Enable TLS and a narrow hostssl pg_hba.conf rule plus a source-restricted firewall rule.',
  'Direct DB اختیاری است و فقط خواندن History/Trend را سریع می‌کند. API همچنان برای Metadata، مجوز و Problems لازم است. در دیتابیس PostgreSQL موجود زبیکس Role اختصاصی زیر بسازید؛ SELECT روی همه جدول‌ها ندهید و مالک دیتابیس را استفاده نکنید. رمز به‌صورت تعاملی در psql تعیین شود و در SQL قرار نگیرد. TLS و Rule محدود hostssl در pg_hba.conf با Firewall محدود به مبدا فعال کنید.')
filecode('postgres-readonly.sql','sql')
p('In Grafana add a PostgreSQL data source for the Zabbix database with that user, TLS verify-full, the trusted CA and hostname. Save & test it, then select it under the Zabbix data source’s Additional settings > Direct DB Connection. Prefer dbConnectionDatasourceUID for provisioning. Keep these settings absent/disabled in the basic YAML. Restrict access: SQL reads can bypass API host-level permissions and expose all numeric history in the granted tables. Use only for trusted, isolated operators; this edition caveat can make API-only the right choice.',
  'در گرافانا منبع PostgreSQL مربوط به دیتابیس زبیکس را با آن کاربر، TLS verify-full، CA معتبر و Hostname بسازید. Save & test کنید و در Additional settings > Direct DB Connection منبع Zabbix آن را انتخاب کنید. برای Provisioning از dbConnectionDatasourceUID استفاده کنید. این تنظیم در YAML پایه خاموش/غایب بماند. دسترسی را محدود کنید: SQL می‌تواند مجوز سطح Host در API را دور بزند و تمام History عددی جدول مجاز را آشکار کند. فقط برای اپراتور مورد اعتماد و ایزوله استفاده کنید؛ محدودیت نسخه ممکن است انتخاب API-only را مناسب کند.')
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/configure/#configure-direct-db-connection','Official Direct DB and minimum history/trend tables','Direct DB رسمی و حداقل جدول History/Trend')
link('https://www.postgresql.org/docs/16/sql-grant.html','PostgreSQL minimum object privileges','حداقل مجوز Object در PostgreSQL')

section('backup-recovery','13. Grafana backup and practical recovery','۱۳. بکاپ گرافانا و بازیابی عملی')
figure('grafana-security-backup-architecture.png','Security and recovery: HTTPS ingress, least privilege, protected encryption key, consistent metadata backup and isolated restore','امنیت و بازیابی: ورودی HTTPS، حداقل مجوز، کلید رمزگذاری محافظت‌شده، بکاپ سازگار Metadata و Restore ایزوله')
p('Back up Grafana metadata separately from the Zabbix monitoring database. Protect /etc/grafana/grafana.ini, provisioning files, root-only environment files, the encryption secret, systemd overrides, plugin files and version inventory, file-provisioned dashboards, Nginx configuration and TLS certificate/private-key state. UI dashboard exports are useful portable artifacts, but do not contain usable data-source secrets or all organization settings. Export dashboard JSON via the dashboard sharing/export menu or provision reviewed JSON from version control.',
  'Metadata گرافانا را مستقل از دیتابیس مانیتورینگ زبیکس بکاپ بگیرید. /etc/grafana/grafana.ini، Provisioning، Environment محدود به Root، Secret رمزگذاری، Override مربوط به systemd، فایل و Inventory نسخه افزونه، داشبورد File-provisioned، تنظیم Nginx و Certificate/Private key TLS را محافظت کنید. Export JSON داشبورد Artifact قابل انتقال است ولی Secret قابل استفاده منبع داده یا همه تنظیم سازمان را ندارد. JSON را از منوی Sharing/Export داشبورد بگیرید یا JSON بررسی‌شده را از Version control فراهم کنید.')
sub('SQLite and PostgreSQL consistency','سازگاری SQLite و PostgreSQL')
p('Copying a live SQLite grafana.db alone can miss journal/WAL state. The supplied backup briefly stops Grafana, creates a SQLite .backup snapshot, checks PRAGMA integrity_check, archives configuration/plugins, and restarts Grafana even on failure. Schedule this maintenance window and silence only planned service-availability checks. It assumes the downloaded sqlite3/path settings and a healthy local service; customize deliberately if using a different path or multi-instance architecture.',
  'Copy ساده grafana.db زنده می‌تواند وضعیت Journal/WAL را از دست بدهد. Script همراه گرافانا را کوتاه متوقف، Snapshot با SQLite .backup ایجاد، PRAGMA integrity_check را بررسی، Config/Plugin را Archive و حتی در شکست دوباره سرویس را Start می‌کند. پنجره نگهداری را زمان‌بندی و فقط Check دسترس‌پذیری برنامه‌ریزی‌شده را Silence کنید. Script مسیر sqlite3 نمونه و سرویس محلی سالم را فرض می‌کند؛ برای مسیر متفاوت یا معماری چند نمونه آگاهانه تنظیم کنید.')
p('For a separate Grafana PostgreSQL metadata database set GRAFANA_DB_TYPE=postgres and PG* values for that database, not Zabbix. pg_dump custom format gives a transaction-consistent logical snapshot; pg_restore decoding checks archive readability, not application recovery. Supply a restricted backup role able to read the Grafana schema and a protected PGPASSFILE; do not print its contents. This script still stops Grafana to align configuration/plugin files with the metadata snapshot. Database administrator-managed roles and TLS settings require their own recovery plan.',
  'برای دیتابیس مستقل PostgreSQL مربوط به Metadata گرافانا مقدار GRAFANA_DB_TYPE=postgres و PG* همان دیتابیس را تنظیم کنید، نه زبیکس. pg_dump با Custom format Snapshot منطقی Transaction-consistent می‌دهد؛ Decode در pg_restore خوانایی Archive را بررسی می‌کند و بازیابی برنامه نیست. Role محدود Backup با مجوز خواندن Schema گرافانا و PGPASSFILE محافظت‌شده فراهم و محتوا را چاپ نکنید. Script همچنان گرافانا را برای تطبیق Config/Plugin با Metadata متوقف می‌کند. Role و TLS مدیریت‌شده توسط DBA برنامه بازیابی مستقل می‌خواهد.')
filecode('grafana-backup.sh','bash')
code('''sudo install -m 0750 -o root -g root grafana-backup.sh /usr/local/sbin/grafana-backup.sh
sudo install -m 0600 -o root -g root grafana-backup.env.example /etc/grafana/backup.env
sudoedit /etc/grafana/backup.env
# Mount an approved backup volume at /srv/grafana-backups first.
findmnt /srv/grafana-backups
sudo install -m 0644 grafana-backup.service grafana-backup.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl start grafana-backup.service
sudo journalctl -u grafana-backup.service -n 100 --no-pager
sudo systemctl enable --now grafana-backup.timer
sudo systemctl list-timers grafana-backup.timer''')
filecode('grafana-backup.service','ini')
filecode('grafana-backup.timer','ini')
p('The timer uses 02:15 UTC plus up to 15 minutes of jitter, independent of host timezone, and catches a missed run after boot. The script refuses an unmounted backup volume, obtains a lock, checks capacity, uses restrictive permissions, writes a hidden partial directory, verifies checksums and publishes atomically. Failure exits nonzero and preserves evidence; alert on failed backup units and aging backups through Zabbix or an external scheduler. Retention is intentionally separate from creation: expire backups only after verified encrypted off-site copies and periodic restore drills.',
  'Timer ساعت 02:15 UTC با تأخیر تصادفی حداکثر ۱۵ دقیقه مستقل از Timezone میزبان اجرا و Run ازدست‌رفته را پس از Boot جبران می‌کند. Script Volume نصب‌نشده را رد، Lock می‌گیرد، ظرفیت را بررسی، Permission محدود تعیین، پوشه Partial مخفی می‌سازد، Checksum را بررسی و اتمیک منتشر می‌کند. شکست Exit غیرصفر دارد و شواهد حفظ می‌شود؛ Unit شکست‌خورده و بکاپ قدیمی را با زبیکس یا Scheduler خارجی هشدار دهید. Retention مستقل از ساخت است؛ فقط پس از Copy رمز‌شده Off-site معتبر و تمرین Restore دوره‌ای حذف کنید.')
p('The backup archive contains API tokens, session/encryption configuration and TLS private keys. Store it on encrypted storage with root-only access. Initialize a restic repository separately, store its password in a protected file, and optionally enable RESTIC_REPOSITORY/RESTIC_PASSWORD_FILE for encrypted off-site upload. Keep recovery keys in a separately accessible vault; test retrieval from the recovery site. A successful backup process or checksum is not proof that Grafana can decrypt credentials after restore.',
  'Archive بکاپ Token API، تنظیم Session/Encryption و Private key TLS دارد. آن را در Storage رمز‌شده با دسترسی فقط Root نگه دارید. Repository مربوط به restic را جدا Initialize، رمز را در فایل محدود ذخیره و در صورت نیاز RESTIC_REPOSITORY/RESTIC_PASSWORD_FILE را برای Upload رمز‌شده Off-site فعال کنید. کلید بازیابی در Vault مستقل قابل دسترس باشد؛ دریافت از سایت بازیابی را آزمون کنید. Process موفق یا Checksum اثبات رمزگشایی Credential پس از Restore نیست.')
sub('Restore to an isolated recovery host','بازیابی روی میزبان ایزوله')
items([
 ('Prepare Ubuntu 24.04 with the exact recorded Grafana release and signed plugin versions. Block notification egress and automatic collectors/backup timers. Restore original host/domain configuration only inside an isolated network until accepted.','Ubuntu 24.04 با نسخه دقیق ثبت‌شده گرافانا و افزونه امضاشده آماده کنید. خروجی اعلان و Collector/Backup timer خودکار را مسدود کنید. تنظیم Host/Domain اصلی تا پذیرش فقط در شبکه ایزوله بازیابی شود.'),
 ('Recover a complete dated backup and verify SHA256SUMS before extracting. Stop Grafana; archive the recovery host’s current configuration separately. Review the tar listing and extract only a trusted backup to the intended host.','بکاپ تاریخ‌دار کامل را دریافت و پیش از Extract، SHA256SUMS را بررسی کنید. گرافانا را Stop؛ تنظیم جاری میزبان بازیابی را جدا Archive کنید. فهرست Tar را بررسی و فقط Backup مورد اعتماد را روی میزبان هدف Extract کنید.'),
 ('Restore /etc/grafana including the original secret_key and environment files, plugin files, file dashboards, systemd override and Nginx/certificate state. If external KMS/encryption providers were configured, recover their key access too. Losing the original encryption material requires re-entering data-source secrets.','/etc/grafana شامل secret_key اصلی و Environment، Plugin، File dashboard، Override systemd و Nginx/Certificate را بازیابی کنید. اگر KMS/Encryption provider خارجی تنظیم بود دسترسی کلید آن هم لازم است. از دست دادن ماده رمزگذاری اصلی به ثبت مجدد Secret منبع داده نیاز دارد.'),
 ('For SQLite restore the consistent grafana.db and remove any stale target WAL/SHM only while Grafana is stopped and after preserving the target state. For PostgreSQL restore into an empty Grafana database with a compatible pg_restore and the intended role. Never overwrite the Zabbix database.','برای SQLite، grafana.db سازگار را Restore و فقط در حالت Stop و پس از حفظ وضعیت هدف، WAL/SHM قدیمی هدف را حذف کنید. برای PostgreSQL در دیتابیس خالی Grafana با pg_restore سازگار و Role مناسب Restore کنید. دیتابیس زبیکس را بازنویسی نکنید.'),
 ('Restore file ownership, validate configuration, reload systemd and start Grafana. Verify login, organization/folder permissions, plugin inventory, Save & test, real Linux/Windows queries, NOC counts/freshness and dashboard variables. Validate certificates and renewal separately.','Ownership فایل را بازگردانید، Config را بررسی، systemd را Reload و گرافانا را Start کنید. ورود، مجوز سازمان/پوشه، Inventory افزونه، Save & test، Query واقعی Linux/Windows، شمارنده/تازگی NOC و Variable را آزمون کنید. Certificate و تمدید را جدا بررسی کنید.'),
 ('Measure RPO from the backup timestamp and RTO to accepted monitoring; compare to business targets. Test one approved notification after unblocking only staging transport, record evidence, then cut over DNS and restore production policies through change control.','RPO را از Timestamp بکاپ و RTO را تا مانیتورینگ پذیرفته‌شده اندازه‌گیری و با هدف سازمان تطبیق دهید. پس از بازکردن فقط Transport مربوط به Staging یک اعلان مجاز را آزمون، شواهد ثبت و سپس DNS و Policy عملیاتی را با Change control منتقل کنید.')])
code(r'''# On the isolated recovery host; BACKUP_DIR is a trusted verified dated directory.
BACKUP_DIR=/srv/grafana-backups/20261009T021500Z
cd "$BACKUP_DIR"
sudo sha256sum -c SHA256SUMS
sudo systemctl stop grafana-server
sudo tar -tzf config-and-plugins.tar.gz
sudo tar -C / -xzf config-and-plugins.tar.gz
# SQLite path only:
sudo install -o grafana -g grafana -m 0640 grafana.db /var/lib/grafana/grafana.db
sudo rm -f -- /var/lib/grafana/grafana.db-wal /var/lib/grafana/grafana.db-shm
sudo -u grafana sqlite3 /var/lib/grafana/grafana.db 'PRAGMA integrity_check;'
sudo systemctl daemon-reload
sudo nginx -t
sudo systemctl start grafana-server
sudo systemctl status grafana-server --no-pager
curl --fail --silent --show-error --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health''')
p('For PostgreSQL use the alternative below on an empty recovery database after its role has been created. Substitute actual protected connection settings and matching source/target versions. pg_restore returns zero when the dump loads; acceptance still requires the UI/API and decryption checks described above. Never run both SQLite and PostgreSQL restore branches.',
  'برای PostgreSQL روش جایگزین زیر را روی دیتابیس خالی Recovery پس از ایجاد Role اجرا کنید. تنظیم اتصال محافظت‌شده و نسخه هماهنگ مبدا/هدف را جایگزین کنید. pg_restore در بارگذاری موفق Exit صفر می‌دهد؛ پذیرش همچنان بررسی UI/API و رمزگشایی بالاست. هر دو Branch بازیابی SQLite و PostgreSQL را اجرا نکنید.')
code('''sudo -u postgres createdb -O grafana grafana_recovery
sudo install -o postgres -g postgres -m 0600 /srv/grafana-backups/20261009T021500Z/grafana.pgdump /var/lib/postgresql/grafana-recovery.pgdump
sudo -u postgres pg_restore --exit-on-error --no-owner --no-acl --role=grafana --dbname=grafana_recovery /var/lib/postgresql/grafana-recovery.pgdump
sudo rm -f -- /var/lib/postgresql/grafana-recovery.pgdump
# Point recovered Grafana [database] at this recovery DB before starting.''')
ref('docs/grafana/latest/administration/back-up-grafana/','Official Grafana backup scope and SQLite shutdown guidance','محدوده بکاپ رسمی گرافانا و توقف SQLite')
link('https://www.sqlite.org/backup.html','SQLite online backup API and consistency','API بکاپ SQLite و سازگاری')
link('https://www.postgresql.org/docs/16/app-pgdump.html','PostgreSQL pg_dump consistency','سازگاری pg_dump در PostgreSQL')
link('https://restic.readthedocs.io/en/stable/','Restic encrypted off-site backup documentation','مستند بکاپ Off-site رمز‌شده restic')

section('troubleshooting','14. Operational troubleshooting','۱۴. عیب‌یابی عملیاتی')
p('For every failure follow Symptoms → Root Cause → Diagnostic Commands → Expected Output → Resolution. Commands are diagnostics and never contain a literal API token. A health check on apiinfo.version or /api/health proves only that stage; verify a real authorized item query before declaring integration healthy. Inspect logs locally and redact secrets before sharing evidence.',
  'برای هر شکست ترتیب علائم ← علت ریشه‌ای ← دستور تشخیصی ← خروجی مورد انتظار ← راه‌حل را دنبال کنید. دستورها تشخیصی هستند و Token واقعی ندارند. Health در apiinfo.version یا /api/health فقط همان مرحله را اثبات می‌کند؛ پیش از اعلام سلامت اتصال، Query آیتم واقعی مجاز را بررسی کنید. Log را محلی بخوانید و قبل از اشتراک Secret را حذف کنید.')
PROBLEMS = [
 ('1. Grafana service fails to start','۱. سرویس گرافانا Start نمی‌شود',
  'Service is failed or repeatedly restarting.','سرویس Failed است یا مکرراً Restart می‌شود.',
  'Invalid INI, unreadable encryption-secret file, metadata database permissions or a port conflict.','INI نامعتبر، فایل Secret ناخوانا، مجوز دیتابیس Metadata یا تداخل پورت.',
  'sudo systemctl status grafana-server --no-pager\nsudo journalctl -u grafana-server -n 100 --no-pager\nsudo ss -lntp | grep ":3000"\nsudo -u grafana test -r /etc/grafana/secrets/secret_key',
  'active (running), no startup error, one loopback listener and a successful readability test.','active (running)، بدون خطای Startup، یک Listener در Loopback و موفقیت بررسی Readability.',
  'Correct the specific logged setting/path/ownership, preserve the existing secret, then restart and recheck /api/health.','تنظیم/مسیر/Ownership مشخص‌شده در Log را اصلاح، Secret موجود را حفظ، Restart و /api/health را دوباره بررسی کنید.'),
 ('2. Grafana Web UI is inaccessible','۲. رابط وب گرافانا در دسترس نیست',
  'Browser times out, 502 or redirects to the wrong domain.','Timeout مرورگر، خطای 502 یا Redirect به دامنه اشتباه.',
  'DNS/firewall mismatch, Nginx not listening, wrong root_url, or upstream service down.','عدم تطبیق DNS/Firewall، Listener ناموجود Nginx، root_url اشتباه یا Down بودن Upstream.',
  'getent ahosts grafana.example.com\nsudo ufw status verbose\nsudo nginx -t\ncurl --fail --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health\ncurl --head https://grafana.example.com/login',
  'DNS resolves intended host; nginx -t succeeds; local health 200 and HTTPS login 200 or an expected login redirect.','DNS میزبان مطلوب را حل کند؛ nginx -t موفق؛ Health محلی 200 و ورود HTTPS برابر 200 یا Redirect ورود مورد انتظار.',
  'Fix routing/DNS/allowlist, start the backend and align domain/root_url. Keep port 3000 loopback-only.','Routing/DNS/Allowlist را اصلاح، Backend را Start و domain/root_url را هماهنگ کنید. پورت 3000 فقط Loopback بماند.'),
 ('3. Zabbix plugin does not appear','۳. افزونه زبیکس نمایش داده نمی‌شود',
  'No Zabbix connection option after installation.','پس از نصب گزینه اتصال Zabbix دیده نمی‌شود.',
  'Wrong plugin directory, missing restart, app not enabled or signature rejection.','پوشه افزونه اشتباه، Restart انجام‌نشده، App غیرفعال یا Reject امضا.',
  'sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls\nsudo journalctl -u grafana-server -n 100 --no-pager\nsudo test -f /var/lib/grafana/plugins/alexanderzobnin-zabbix-app/plugin.json',
  'Installed app is listed, manifest exists and journal has no signature/dependency rejection.','App نصب‌شده در فهرست، Manifest موجود و Journal بدون Reject امضا/Dependency.',
  'Install into the configured directory, restart, then Administration > Plugins and data > Plugins > Zabbix > Enable.','در پوشه تنظیم‌شده نصب، Restart و سپس Administration > Plugins and data > Plugins > Zabbix > Enable را انجام دهید.'),
 ('4. Zabbix API authentication fails','۴. احراز هویت API زبیکس شکست می‌خورد',
  'Not authorized, invalid token or expired session on Save & test.','خطای Not authorized، Token نامعتبر یا Session منقضی در Save & test.',
  'Expired token, disabled owner, role API access denial, missing environment or a proxy stripping Authorization.','Token منقضی، صاحب حساب غیرفعال، منع API در Role، Environment مفقود یا حذف Authorization در Proxy.',
  'sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py\nsudo journalctl -u grafana-server -n 50 --no-pager',
  'API version 7.0.x and a nonzero authorized Visible hosts count.','نسخه API برابر 7.0.x و تعداد Visible hosts مجاز غیرصفر.',
  'Regenerate an expiring token for the correct enabled owner, verify role/permissions and forwarded header, update only the protected environment and restart provisioning.','Token مدت‌دار صاحب حساب فعال درست را دوباره Generate، Role/Permission و Header را بررسی و فقط Environment محدود را Update و Provisioning را Restart کنید.'),
 ('5. Zabbix data source returns an error','۵. منبع داده زبیکس Error می‌دهد',
  'Save & test reports JSON parse, method denied, 404 or 502.','Save & test خطای JSON parse، Method denied، 404 یا 502 می‌دهد.',
  'Wrong frontend path, HTML login interception, inaccessible API or denied API method.','مسیر Frontend اشتباه، Login HTML در میانه، API غیرقابل دسترس یا Method ممنوع.',
  'curl --fail --silent --show-error -H "Content-Type: application/json-rpc" --data \'{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}\' https://zabbix.example.com/api_jsonrpc.php\nsudo journalctl -u grafana-server -n 50 --no-pager',
  'JSON result with version, not HTML or a redirect; plugin query inspector shows a valid response.','JSON دارای نسخه، نه HTML یا Redirect؛ Query inspector پاسخ معتبر نشان دهد.',
  'Verify root or /zabbix path, remove inappropriate API login interception and allow required read methods. Retest a permitted item.','مسیر Root یا /zabbix را بررسی، Login نامناسب API را رفع و Method خواندن لازم را مجاز کنید. آیتم مجاز را دوباره Query کنید.'),
 ('6. Dashboard shows No data','۶. داشبورد No data نشان می‌دهد',
  'Empty graph despite a successful API test.','نمودار خالی با وجود تست موفق API.',
  'Wrong item names, uncreated discovery items, disabled/unsupported items, stale data, tag filters or a range outside history.','نام آیتم اشتباه، Discovery ساخته‌نشده، آیتم Disabled/Unsupported، داده قدیمی، فیلتر Tag یا بازه خارج History.',
  'date -u\ntimedatectl status\nsudo journalctl -u grafana-server -n 50 --no-pager',
  'Clocks synchronized; Latest data has a recent supported sample; Query inspector filters match the exact discovered item.','ساعت هماهنگ؛ Latest data نمونه تازه Supported؛ فیلتر Query inspector با آیتم کشف‌شده دقیق مطابق باشد.',
  'Reset item_group/interface selectors, use the real item name, wait for discovery/collection and narrow to a recent range; enable trends only when available.','انتخاب‌گر item_group/interface را Reset، نام واقعی آیتم را استفاده، برای Discovery/Collection صبر و بازه تازه انتخاب کنید؛ Trend فقط در صورت موجودبودن فعال شود.'),
 ('7. Host groups are missing','۷. گروه میزبان دیده نمی‌شود',
  'Picker omits groups present for an administrator in Zabbix.','Picker گروه موجود برای مدیر زبیکس را نشان نمی‌دهد.',
  'Integration owner lacks group Read permission, role restriction or cached metadata.','صاحب حساب اتصال فاقد Read گروه، محدودیت Role یا Metadata Cacheشده.',
  'sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py',
  'Visible-host scope matches the integration user, not the Zabbix super-admin inventory.','محدوده میزبان با کاربر اتصال تطبیق دارد، نه موجودی Super admin زبیکس.',
  'Grant only the approved groups under Host permissions, ensure hostgroup.get/host.get allowed, refresh the variable and allow cache TTL expiry.','فقط گروه مجاز را در Host permissions Read بدهید، hostgroup.get/host.get را مجاز، Variable را Refresh و اجازه Expiry Cache TTL دهید.'),
 ('8. Zabbix API connection times out','۸. اتصال API زبیکس Timeout می‌شود',
  'Queries exceed 30s or intermittent gateway timeouts.','Query از 30s عبور می‌کند یا Gateway timeout متناوب دارد.',
  'Blocked routing/firewall, overloaded PHP/API/database or an unbounded dashboard query.','Routing/Firewall مسدود، بار زیاد PHP/API/Database یا Query نامحدود داشبورد.',
  'getent ahosts zabbix.example.com\ncurl --head --connect-timeout 5 --max-time 30 https://zabbix.example.com/\nsudo journalctl -u grafana-server -n 50 --no-pager',
  'DNS resolves and HTTPS responds promptly; API query duration stays below configured timeout.','DNS حل و HTTPS سریع پاسخ دهد؛ مدت Query API کمتر از Timeout تنظیم‌شده باشد.',
  'Fix network access first, narrow host/item scope and ranges, check Zabbix worker/database load, then tune timeouts based on measurements.','ابتدا شبکه را اصلاح، محدوده Host/Item و بازه را محدود، بار Worker/Database زبیکس را بررسی و سپس Timeout را بر اساس اندازه‌گیری تنظیم کنید.'),
 ('9. HTTPS certificate verification fails','۹. اعتبارسنجی Certificate HTTPS شکست می‌خورد',
  'x509 unknown authority, hostname mismatch or certificate expired.','خطای x509 unknown authority، Hostname mismatch یا Certificate منقضی.',
  'Incomplete certificate chain, untrusted internal CA, wrong SAN/domain or failed renewal.','Chain ناقص، CA داخلی نامعتبر، SAN/Domain اشتباه یا شکست تمدید.',
  'openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com -verify_return_error </dev/null\ntimedatectl status\nsudo certbot certificates',
  'Verify return code: 0 (ok), matching SAN and valid dates; clock correct.','Verify return code: 0 (ok)، SAN منطبق، تاریخ معتبر و ساعت درست.',
  'Serve full chain, install approved CA trust on Grafana and renew valid certs. Do not use Skip TLS verify as production remediation.','Full chain را ارائه، CA trust مجاز را روی گرافانا نصب و Certificate معتبر را تمدید کنید. Skip TLS verify راه‌حل Production نیست.'),
 ('10. Grafana dashboard is slow','۱۰. داشبورد گرافانا کند است',
  'Long spinner, excessive browser memory or API saturation.','Spinner طولانی، حافظه زیاد مرورگر یا اشباع API.',
  'High cardinality, broad history query, fast refresh, expensive transforms/problem lookups.','Cardinality بالا، Query گسترده History، Refresh سریع، Transform/Problem lookup پرهزینه.',
  'sudo journalctl -u grafana-server -n 100 --no-pager\nsudo systemctl status grafana-server --no-pager',
  'Query inspector isolates latency and result size; logs show no repeated timeouts.','Query inspector تأخیر و حجم پاسخ را تفکیک؛ Log بدون Timeout تکراری.',
  'Reduce series/panels, use trends for wide ranges and 1m+ refresh, disable unused event enrichments, benchmark before considering read-only Direct DB.','سری/پنل را کم، Trend برای بازه بلند و Refresh حداقل 1m، Enrichment بی‌استفاده رخداد را خاموش و پیش از Direct DB محدود Benchmark کنید.'),
 ('11. Plugin version incompatibility','۱۱. ناسازگاری نسخه افزونه',
  'Plugin blocked by version requirement, stale Angular code or missing definition.','افزونه با نیاز نسخه، کد Angular قدیمی یا Definition مفقود مسدود است.',
  'Grafana below minimum or mixed/stale plugin artifacts after an upgrade.','گرافانا پایین‌تر از حداقل یا Artifact قدیمی/مختلط پس از ارتقا.',
  'grafana --version\nsudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls\nsudo journalctl -u grafana-server -n 100 --no-pager',
  'Approved stable Grafana and signed React plugin; at review 13.2.3 / 6.9.1 and minimum 11.6.0.','گرافانا پایدار مجاز و افزونه React امضاشده؛ هنگام بررسی 13.2.3 / 6.9.1 و حداقل 11.6.0.',
  'Stage a compatible version pair; preserve old artifact, replace only the stale plugin directory with an approved clean signed installation, restart and retest dashboards/alerts.','زوج نسخه سازگار را در Staging آماده؛ Artifact قدیمی را حفظ، فقط پوشه افزونه قدیمی را با نصب تمیز امضاشده مجاز جایگزین، Restart و داشبورد/هشدار را دوباره آزمون کنید.'),
 ('12. Problems panel displays no events','۱۲. پنل Problems رخداد نشان نمی‌دهد',
  'Panel is empty although Zabbix has active incidents.','پنل خالی است با وجود رخداد فعال در زبیکس.',
  'Wrong query type, severity/acknowledgment/tag/host filters, Use time range hides older incidents or missing event read permissions.','Query type اشتباه، فیلتر Severity/Acknowledgment/Tag/Host، مخفی‌شدن رخداد قدیمی با Use time range یا مجوز خواندن Event مفقود.',
  'sudo journalctl -u grafana-server -n 100 --no-pager',
  'Query inspector uses Problems (5), current Show Problems, Use time range false and returns permitted active events.','Query inspector نوع Problems (5)، Show Problems جاری، Use time range برابر false و رخداد فعال مجاز برگرداند.',
  'Use Zabbix Problems visualization, reset severity/ack/tag filters, allow problem.get/event.get/trigger.get reads, and compare as the integration owner. Zero current problems can be valid.','نمایش Zabbix Problems را انتخاب، فیلتر Severity/Ack/Tag را Reset، خواندن problem.get/event.get/trigger.get را مجاز و با صاحب اتصال مقایسه کنید. صفر رخداد جاری می‌تواند معتبر باشد.')]
for en,fa,sym,fsym,cause,fcause,commands,expected,fexpected,resolution,fresolution in PROBLEMS:
    sub(en,fa)
    p('Symptoms: '+sym,'علائم: '+fsym)
    p('Root Cause: '+cause,'علت ریشه‌ای: '+fcause)
    p('Diagnostic Commands: run locally; inspect the relevant UI query filters as well.','دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.')
    code(commands)
    p('Expected Output: '+expected,'خروجی مورد انتظار: '+fexpected)
    p('Resolution: '+resolution,'راه‌حل: '+fresolution)
ref('docs/plugins/alexanderzobnin-zabbix-app/latest/troubleshooting/','Official Zabbix plugin troubleshooting','عیب‌یابی رسمی افزونه زبیکس')

section('production-checklist','15. Production acceptance checklist','۱۵. چک‌لیست پذیرش Production')
CHECKLIST = [
 ('Installation: stable Grafana/plugin versions recorded, APT key verified, systemd enabled, health and reboot checks completed.','نصب: نسخه پایدار Grafana/Plugin ثبت، کلید APT بررسی، systemd فعال و Health و Reboot آزموده شد.'),
 ('Security: valid HTTPS chain/SAN, renewal tested, loopback-only 3000, approved source firewall and IPv6 policy, no anonymous access/default password.','امنیت: Chain/SAN معتبر HTTPS، تمدید آزموده، 3000 فقط Loopback، Firewall مبدا و IPv6 مجاز، بدون دسترسی ناشناس/رمز پیش‌فرض.'),
 ('Identity: named admins, least privilege roles, IdP MFA and removal tested, emergency access documented; OSS data-source visibility understood.','هویت: مدیر نام‌دار، حداقل مجوز Role، MFA و حذف حساب IdP آزموده، دسترسی اضطراری مستند؛ دیدپذیری منبع در OSS مشخص.'),
 ('Zabbix: verified API path and trusted TLS, scoped owner/token expiry, read permissions and real Save & test plus numeric item queries.','زبیکس: مسیر API و TLS معتبر، صاحب حساب محدود/انقضای Token، مجوز Read و Save & test واقعی با Query آیتم عددی.'),
 ('Linux: CPU/load/RAM/available memory/filesystem/network/uptime/availability values compared with Latest data and unit conversions checked.','لینوکس: CPU/Load/RAM/حافظه قابل استفاده/FS/شبکه/Uptime/Availability با Latest data مقایسه و واحد بررسی شد.'),
 ('Windows: actual discovered services/filesystems/interfaces, valid state mappings, active Event Log item and controlled new-event test completed.','ویندوز: سرویس/FS/Interface واقعی کشف‌شده، State mapping معتبر، آیتم Active مربوط به Event Log و آزمون رخداد جدید کنترل‌شده.'),
 ('NOC: collector scope and permissions reviewed, total=available+unavailable+unknown, Disaster/High counts reconciled, freshness and collector-failure alarms tested.','NOC: محدوده Collector و مجوز بررسی، کل برابر در دسترس+خارج از دسترس+نامشخص، تعداد Disaster/High تطبیق و هشدار تازگی/شکست Collector آزموده.'),
 ('Reliability: variables and mixed-OS shared panels verified, current Problems includes old ongoing events, no-data/error behavior and separate monitoring of Grafana itself.','پایداری: Variable و پنل مشترک OS آزموده، Problems رخداد جاری قدیمی را شامل، رفتار No-data/Error و مانیتورینگ مستقل خود گرافانا.'),
 ('Notifications: one incident owner, contact-point tests authorized, PROBLEM/recovery observed, no duplicate paging, escalation and maintenance policies documented.','اعلان: یک مالک رخداد، Test Contact point مجاز، مشاهده PROBLEM/Recovery، بدون Paging تکراری، Escalation و Policy نگهداری مستند.'),
 ('Performance: representative concurrent dashboards measured, bounded filters, refresh/query interval and history/trend retention matched.','کارایی: داشبورد هم‌زمان واقعی اندازه‌گیری، فیلتر محدود، Refresh/Query interval و Retention History/Trend هماهنگ.'),
 ('Backup: consistent metadata snapshot, complete configuration/secret/plugin/certificate inventory, checksum and encrypted off-site copy with external failure alarms.','بکاپ: Snapshot سازگار Metadata، Inventory کامل Config/Secret/Plugin/Certificate، Checksum و Copy رمز‌شده Off-site با هشدار شکست خارجی.'),
 ('Recovery: isolated restore validates decryption, login, roles, actual data queries, measured RPO/RTO and separate vault key retrieval.','بازیابی: Restore ایزوله رمزگشایی، ورود، Role، Query واقعی، RPO/RTO اندازه‌گیری‌شده و دریافت کلید از Vault جدا را تأیید کند.'),
 ('Operations: owner/on-call, patch window, capacity targets, restore schedule, approved rollback and handover evidence recorded.','عملیات: مالک/On-call، پنجره Patch، هدف ظرفیت، برنامه Restore، Rollback مجاز و شواهد تحویل ثبت شد.')]
items([('[ ] '+en,'[ ] '+fa) for en,fa in CHECKLIST])
p('Acceptance requires an actual Zabbix API connection and representative live data. This article’s downloadable dashboard definitions contain no live metric results and do not claim a successful production integration. Operators must complete the checklist in staging and then their approved production environment.',
  'پذیرش به اتصال واقعی API زبیکس و داده زنده نماینده نیاز دارد. تعریف داشبورد دانلودشده مقاله نتیجه متریک زنده ندارد و ادعای اتصال موفق Production نمی‌کند. اپراتور باید چک‌لیست را در Staging و سپس محیط Production مجاز خود کامل کند.')

section('conclusion','Conclusion and operational handover','نتیجه‌گیری و تحویل عملیاتی')
p('A useful enterprise monitoring stack combines reliable Zabbix collection and trigger evaluation with clear Grafana views, controlled access and recoverable configuration. Hand over the actual version inventory, scoped API ownership, dashboard/item mappings, alert ownership and measured restore evidence. Keep API-only integration as the default and add optional components only when their cost and authorization boundaries are understood.',
  'سامانه مانیتورینگ سازمانی مفید، جمع‌آوری و ارزیابی Trigger قابل اتکای زبیکس را با نمای روشن گرافانا، دسترسی کنترل‌شده و Config قابل بازیابی ترکیب می‌کند. Inventory نسخه واقعی، مالک API محدود، Mapping داشبورد/آیتم، مالک هشدار و شواهد Restore اندازه‌گیری‌شده را تحویل دهید. API-only پیش‌فرض بماند و جزء اختیاری فقط با شناخت هزینه و مرز مجوز اضافه شود.')
FAQ = [
 ('Does Grafana replace Zabbix agents or Server?','No. Zabbix collects, stores and evaluates monitoring data; Grafana queries the Zabbix API and visualizes it.','آیا گرافانا جایگزین Agent یا Server زبیکس است؟','خیر؛ زبیکس داده را جمع‌آوری، ذخیره و ارزیابی می‌کند و گرافانا API را Query و داده را نمایش می‌دهد.'),
 ('Is Direct DB required?','No. The official plugin connects through the API. Direct DB is optional for numeric history/trend performance and needs a separately secured read-only role.','آیا Direct DB الزامی است؟','خیر؛ افزونه رسمی با API متصل می‌شود. Direct DB برای عملکرد History/Trend عددی اختیاری و نیازمند Role محدود مستقل است.'),
 ('Which stable versions were verified?','Grafana OSS 13.2.3 and Zabbix app 6.9.1 on 9 October 2026; the plugin requires Grafana 11.6.0 or later and documents Zabbix 7.0 authentication.','کدام نسخه پایدار بررسی شد؟','Grafana OSS 13.2.3 و افزونه Zabbix 6.9.1 در ۹ اکتبر ۲۰۲۶؛ افزونه حداقل 11.6.0 نیاز دارد و احراز هویت Zabbix 7.0 را مستند می‌کند.'),
 ('What is the correct Zabbix API URL?','Verify the frontend mount: /api_jsonrpc.php at the root or /zabbix/api_jsonrpc.php when that is the installed web path.','URL صحیح API زبیکس چیست؟','Mount رابط وب را بررسی کنید؛ /api_jsonrpc.php در Root یا /zabbix/api_jsonrpc.php وقتی همان مسیر نصب است.'),
 ('Can I use Application variables on Zabbix 7.0?','Use Item tag variables. Zabbix removed Applications in 5.4, and the current plugin exposes a structured item-tag query.','آیا Variable نوع Application در Zabbix 7.0 کاربرد دارد؟','از Item tag استفاده کنید؛ زبیکس از 5.4 Applications را حذف کرده و افزونه جاری Query ساختاریافته Item tag دارد.'),
 ('Does the NOC JSON invent total-host counts?','No. Included API collector code and a trapper template implement scoped host/problem counts and freshness; install them before using the overview panels.','آیا JSON مربوط به NOC تعداد میزبان را فرض می‌کند؟','خیر؛ کد Collector API و Template Trapper همراه، تعداد Host/Problem محدود و تازگی را پیاده می‌کنند؛ پیش از Overview نصب شوند.'),
 ('Can the Problems query create Grafana alerts?','No. Plugin alerting supports numeric Metrics and Item ID queries; use Zabbix triggers/actions for Zabbix problem notifications.','آیا Query نوع Problems هشدار گرافانا می‌سازد؟','خیر؛ هشدار افزونه Metrics و Item ID عددی را پشتیبانی می‌کند؛ اعلان Problem زبیکس با Trigger/Action زبیکس انجام شود.'),
 ('Why must the encryption secret be backed up?','Stored data-source credentials need the original encryption material for recovery. Dashboard JSON alone cannot restore those credentials.','چرا Secret رمزگذاری باید بکاپ شود؟','Credential ذخیره‌شده برای بازیابی به ماده رمزگذاری اصلی نیاز دارد؛ JSON داشبورد به‌تنهایی Credential را بازنمی‌گرداند.')]
section('faq','Frequently asked questions','پرسش‌های متداول')
for q,a,fq,fa in FAQ:sub(q,fq);p(a,fa)

DOWNLOADS=[('nginx-bootstrap.conf','HTTP certificate bootstrap','Bootstrap HTTP برای Certificate'),
 ('nginx-grafana.conf','Nginx HTTPS reverse proxy','Reverse Proxy HTTPS در Nginx'),
 ('grafana.ini','Grafana production configuration','تنظیم Production گرافانا'),
 ('zabbix-datasource.yaml','API-token data source provisioning','Provisioning منبع داده با Token API'),
 ('zabbix-app.yaml','App plugin provisioning','Provisioning افزونه App'),
 ('grafana.env.example','Protected environment template','Template محیط محافظت‌شده'),
 ('grafana-environment.conf','Systemd environment override','Override محیط systemd'),
 ('dashboard-provider.yaml','Dashboard file provisioning','Provisioning فایل داشبورد'),
 ('linux-dashboard.json','Linux dashboard JSON','JSON داشبورد Linux'),
 ('windows-dashboard.json','Windows dashboard JSON','JSON داشبورد Windows'),
 ('enterprise-noc-dashboard.json','Enterprise NOC dashboard JSON','JSON داشبورد NOC سازمانی'),
 ('zabbix-api-probe.py','Safe API connectivity probe','Probe امن اتصال API'),
 ('noc-collector.py','API count collector','Collector شمارنده API'),
 ('noc-collector.env.example','NOC environment template','Template محیط NOC'),
 ('noc-collector.service','NOC collector systemd service','Service systemd جمع‌آوری‌کننده NOC'),
 ('noc-collector.timer','NOC collector timer','Timer جمع‌آوری‌کننده NOC'),
 ('zabbix-noc-template.json','NOC Zabbix trapper template','Template Trapper زبیکس برای NOC'),
 ('postgres-readonly.sql','Optional PostgreSQL read-only grants','مجوز Read-only اختیاری PostgreSQL'),
 ('grafana-backup.sh','Consistent backup script','Script بکاپ سازگار'),
 ('grafana-backup.env.example','Backup environment template','Template محیط بکاپ'),
 ('grafana-backup.service','Backup systemd service','Service systemd بکاپ'),
 ('grafana-backup.timer','Daily backup timer','Timer روزانه بکاپ'),
 ('restore-runbook.md','Bilingual restore instructions','دستور بازیابی دوزبانه'),
 ('security-checklist.md','Bilingual security checklist','چک‌لیست امنیت دوزبانه'),
 ('README.md','Package README and deployment order','README بسته و ترتیب استقرار')]
section('official-references','Official references, downloads and related articles','منابع رسمی، دانلود و مقاله‌های مرتبط')
p('Official references are linked at the relevant chapters. The downloads are authored configuration templates and real dashboard structures, not claimed exports from a running Grafana instance. Replace documentation domains, IPs, group IDs, secret placeholders and certificate paths locally. Compare actual discovered item names before importing. Every download is also included in the configuration ZIP.',
  'منبع رسمی در فصل مرتبط لینک شده است. دانلودها Template تنظیم تألیف‌شده و ساختار واقعی داشبورد هستند و به‌عنوان Export نمونه در حال اجرا معرفی نمی‌شوند. دامنه/IP مستند، ID گروه، Placeholder مربوط به Secret و مسیر Certificate را محلی جایگزین کنید. پیش از Import نام آیتم کشف‌شده واقعی را مقایسه کنید. همه دانلودها در ZIP تنظیم نیز قرار دارند.')
for name,en,fa in DOWNLOADS:link(f'/downloads/{SLUG}/{name}','Download: '+en,'دانلود: '+fa)
link(f'/downloads/{SLUG}/grafana-configuration-package.zip','Download the complete configuration package','دانلود بسته کامل تنظیمات')
for slug,en,fa in [
 ('zabbix-server-linux-windows-agents-backup','Zabbix Server, Linux/Windows agents and disaster recovery','نصب Zabbix Server، Agent لینوکس/ویندوز و بازیابی بحران'),
 ('oracle-database-26ai-installation-oracle-linux','Oracle Database enterprise deployment','استقرار سازمانی Oracle Database'),
 ('apache-tomcat-linux-installation-security-hardening','Apache Tomcat systemd and security','systemd و امنیت Apache Tomcat'),
 ('redis-installation-configuration-replication','Redis production monitoring','مانیتورینگ Production در Redis'),
 ('mongodb-installation-configuration-production-deployment','MongoDB enterprise operations','عملیات سازمانی MongoDB'),
 ('linux-security-auditor-bash','Linux Security Auditor','ممیزی امنیت Linux'),
 ('nginx-installation-configuration-ubuntu','Nginx on Ubuntu','Nginx روی Ubuntu')]:link('/articles/'+slug,'Related: '+en,'مرتبط: '+fa)
parts.append('</section>')

localizations={lang:{'title':TITLE[lang],'meta_title':TITLE[lang],'description':DESC[lang],
 'keywords':KEYWORDS,'faq':[[q,a] if lang=='en' else [fq,fa] for q,a,fq,fa in FAQ],
 'image_alt':'Grafana and Zabbix enterprise monitoring with Linux and Windows servers' if lang=='en' else 'مانیتورینگ سازمانی گرافانا و زبیکس با سرور Linux و Windows',
 'image_title':TITLE[lang], 'image_caption':'Enterprise dashboards, HTTPS security, backup and recovery' if lang=='en' else 'داشبورد سازمانی، امنیت HTTPS، بکاپ و بازیابی'} for lang in TITLE}
banner='/assets/img/articles/banners/grafana-zabbix-enterprise-monitoring-banner.png'
url='https://meetaj.ir/articles/'+SLUG
schema={'@context':'https://schema.org','@type':'Article','headline':TITLE['en'],'description':DESC['en'],
 'inLanguage':'en','datePublished':'2026-10-09T00:00:00+03:30','dateModified':'2026-10-09',
 'image':banner,'mainEntityOfPage':url,'author':{'@type':'Person','name':'AmirHossein Jalalian'}}
faq_schema={'@context':'https://schema.org','@type':'FAQPage','mainEntity':[{'@type':'Question','name':q,'acceptedAnswer':{'@type':'Answer','text':a}} for q,a,fq,fa in FAQ]}
nav=''.join('<li class="article-nav-item">'+dual('a',en,fa,f'href="#{id}"')+'</li>' for id,en,fa in toc)
html=f'''<!doctype html>
<html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{escape(TITLE['en'])}</title>
<meta name="article:content-language" content="en"><meta name="description" content="{escape(DESC['en'],quote=True)}">
<meta name="keywords" content="{', '.join(KEYWORDS)}"><meta name="robots" content="index, follow">
<meta property="og:title" content="{escape(TITLE['en'],quote=True)}"><meta property="og:description" content="{escape(DESC['en'],quote=True)}">
<meta property="og:type" content="article"><meta property="og:image" content="{banner}"><meta property="og:url" content="{url}">
<link rel="canonical" href="{url}"><meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">{json.dumps(schema,ensure_ascii=False)}</script>
<script type="application/ld+json">{json.dumps(faq_schema,ensure_ascii=False)}</script>
<script id="article-localizations" type="application/json">{json.dumps(localizations,ensure_ascii=False)}</script>
</head><body class="article-page theme-linux"><header><nav><ul>{nav}</ul></nav></header><main>
<section class="article-hero article-header hero">
{dual('span','Linux','لینوکس','class="article-category"')}
{dual('h1',TITLE['en'],TITLE['fa'],'class="article-title hero-title"')}
{dual('p',DESC['en'],DESC['fa'],'class="article-excerpt hero-subtitle"')}
<img class="article-hero-thumbnail" src="{banner}" width="1000" height="1000" alt="{localizations['en']['image_alt']}" data-en-alt="{localizations['en']['image_alt']}" data-fa-alt="{localizations['fa']['image_alt']}">
</section><article class="article-body" lang="en" dir="ltr">{chr(10).join(parts)}</article></main></body></html>'''
html=normalize_html(html,SLUG)
(ROOT/'resources/legacy/articles'/f'{SLUG}.html').write_text(html,encoding='utf-8',newline='\n')
for lang,content in md.items():(SOURCE/f'article.{lang}.md').write_text(normalize_markdown('\n'.join(content),SLUG),encoding='utf-8',newline='\n')
for name,id,title in [('restore-runbook.md','backup-recovery','Grafana backup and restore / بکاپ و بازیابی گرافانا'),
                       ('security-checklist.md','production-checklist','Production security and recovery checklist / چک‌لیست امنیت و بازیابی')]:
    blocks=['# '+title,'']
    for lang in ['en','fa']:
        content='\n'.join(md[lang]);start=content.index('## '+next(row[1 if lang=='en' else 2] for row in toc if row[0]==id))
        end=content.find('\n## ',start+4);blocks.extend([content[start:end if end!=-1 else len(content)],''])
    (SOURCE/name).write_text('\n'.join(blocks),encoding='utf-8',newline='\n')
readme='''# Grafana and Zabbix enterprise monitoring package

English / فارسی. Ubuntu Server 24.04 LTS, Grafana OSS stable, existing Zabbix 7.0 LTS.

These are authored configuration templates and valid importable dashboard definitions. They are not fake live exports and contain no metric samples or actual credentials. Refer to [English guide](article.en.md) and [راهنمای فارسی](article.fa.md) for all commands, diagrams, acceptance criteria and official references.

Deployment order / ترتیب استقرار:

1. Verify official versions, DNS, time, APT key and API path / نسخه رسمی، DNS، ساعت، کلید APT و مسیر API.
2. Install Grafana, preserve its unique encryption secret, apply loopback-only config / نصب گرافانا، حفظ Secret یکتا و Config مربوط به Loopback.
3. Bootstrap Nginx certificate, install HTTPS proxy and test renewal / Bootstrap صدور Certificate و Proxy HTTPS و آزمون تمدید.
4. Install signed app, enable it, create read-only Zabbix owner/token / نصب App امضاشده و فعال‌سازی، ساخت صاحب حساب و Token محدود.
5. Fill protected environment locally; apply app/data-source provisioning / تکمیل محیط محدود محلی و Provisioning.
6. Import Linux/Windows JSON, select real discovered items; configure Event Log active item / Import JSON و آیتم واقعی؛ تنظیم آیتم Active مربوط به Event Log.
7. Optional NOC: import trapper template, provision summary host/PSK, configure collector scope and timer, then import NOC JSON / NOC اختیاری: Template، میزبان Summary/PSK، محدوده Collector و Timer سپس JSON.
8. Install backup script/service/timer with mounted storage and encrypted off-site copies / نصب Script و Service و Timer بکاپ با Volume و Copy رمز‌شده Off-site.
9. Complete isolated recovery and [security checklist](security-checklist.md) / بازیابی ایزوله و چک‌لیست امنیت.

Data source UID: zabbix-enterprise. If using another data source, change every datasource reference. Use dashboard-provider.yaml with reviewed JSON under /var/lib/grafana/dashboards. Application selectors are replaced with Item tags on Zabbix 7.0. The interface variable returns full inbound item names. NOC counts require noc-collector.py and zabbix-noc-template.json; Environment scope is independent of narrowed performance filters. No-data/stale values must never be converted to zero.

Runtime acceptance needs a real authorized Zabbix API, live OS items, Grafana rendering, approved notification transport and a restored database. Static checks cannot establish these. Read [restore instructions](restore-runbook.md). Edited credential files and backup archives must never be copied into public downloads or Git.
'''
(SOURCE/'README.md').write_text(readme,encoding='utf-8',newline='\n')
metadata={'slug':SLUG,'canonical_route':'/articles/'+SLUG,'canonical_url':url,'reviewed_at':'2026-10-09',
 'category':'linux','tags':['Grafana','Zabbix','Monitoring','Linux','DevOps','Infrastructure','Observability'],
 'grafana_oss_version':'13.2.3','zabbix_plugin_version':'6.9.1','plugin_grafana_minimum':'11.6.0',
 'zabbix_branch':'7.0 LTS','os':'Ubuntu Server 24.04 LTS','localizations':localizations,
 'runtime_integration_tested':False,'dashboard_provenance':'Authored importable JSON; query schema checked against official v6.9.1 plugin source',
 'version_sources':['https://grafana.com/grafana/download?edition=oss','https://grafana.com/grafana/plugins/alexanderzobnin-zabbix-app/'],
 'sections':[id for id,en,fa in toc],'downloads':[name for name,en,fa in DOWNLOADS]}
(SOURCE/'metadata.json').write_text(json.dumps(metadata,ensure_ascii=False,indent=2)+'\n',encoding='utf-8',newline='\n')
downloads=ROOT/'public/downloads'/SLUG;downloads.mkdir(parents=True,exist_ok=True)
names=[name for name,en,fa in DOWNLOADS]+['article.en.md','article.fa.md','metadata.json','images.json']
for name in names:
    if (SOURCE/name).exists():shutil.copyfile(SOURCE/name,downloads/name)
with zipfile.ZipFile(downloads/'grafana-configuration-package.zip','w',zipfile.ZIP_DEFLATED) as archive:
    for name in names:
        if (SOURCE/name).exists():archive.write(SOURCE/name,name)
shutil.copyfile(downloads/'grafana-configuration-package.zip',SOURCE/'grafana-configuration-package.zip')
print(f'Built {SLUG}: {len(toc)} sections, {len(DOWNLOADS)} individual downloads, bilingual HTML/Markdown and ZIP')
