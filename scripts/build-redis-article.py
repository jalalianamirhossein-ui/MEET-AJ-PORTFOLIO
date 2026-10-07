"""Build Redis editorial sources, bilingual CMS HTML and non-secret runbook downloads."""
from pathlib import Path
from html import escape
import json
import shutil

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'redis-installation-configuration-replication'
SOURCE = ROOT / 'resources/content/articles' / SLUG
TITLE = {
    'fa': 'راه‌اندازی Redis در محیط Production؛ نصب، Hardening، Persistence و Replication',
    'en': 'Redis Installation, Configuration and Replication – Production Deployment Guide',
}
DESC = {
    'fa': 'آموزش نصب Redis روی Ubuntu، تنظیم ACL و TLS، مدیریت RDB و AOF، راه‌اندازی Replica، Sentinel، مانیتورینگ و Backup با چک‌لیست Production.',
    'en': 'Deploy Redis on Ubuntu with ACL and TLS hardening, RDB/AOF persistence, replication, Sentinel, memory policies, monitoring, backups and troubleshooting.',
}
KEYWORDS = ['Redis Installation', 'Redis Configuration', 'Redis Replication', 'Redis Ubuntu', 'Redis ACL', 'Redis Persistence', 'Redis Sentinel', 'Redis Cluster', 'Redis Production', 'Redis Monitoring']
parts, toc = [], []
md = {lang: [f'# {TITLE[lang]}', '', DESC[lang], ''] for lang in TITLE}
configs = {}

def dual(tag, en, fa, attrs=''):
    if tag in ('a', 'figcaption'):
        return f'<{tag} {attrs}>' + dual('span', en, fa) + f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en, quote=True)}" data-fa="{escape(fa, quote=True)}">{escape(en)}</{tag}>'

def p(en, fa):
    parts.append(dual('p', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([value, ''])

def code(value, lang='bash', filename=None):
    value = value.strip()
    parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value)}</code></pre>')
    for locale in md: md[locale].extend([f'```{lang}', value, '```', ''])
    if filename: configs[filename] = value + '\n'

def section(id, en, fa):
    if toc: parts.append('</section>')
    toc.append((id, en, fa))
    parts.append(f'<section id="{id}">' + dual('h2', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend(['## ' + value, ''])

def sub(en, fa):
    parts.append(dual('h3', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend(['### ' + value, ''])

def items(pairs):
    parts.append('<ul>' + ''.join(dual('li', en, fa) for en, fa in pairs) + '</ul>')
    for lang, index in [('en', 0), ('fa', 1)]: md[lang].extend(['- ' + pair[index] for pair in pairs] + [''])

def table(headers, rows):
    parts.append('<table><thead><tr>' + ''.join(dual('th', *h) for h in headers) + '</tr></thead><tbody>' + ''.join('<tr>' + ''.join(dual('td', *cell) for cell in row) + '</tr>' for row in rows) + '</tbody></table>')
    for lang, index in [('en', 0), ('fa', 1)]:
        md[lang].extend(['| ' + ' | '.join(h[index] for h in headers) + ' |', '| ' + ' | '.join('---' for h in headers) + ' |'] + ['| ' + ' | '.join(c[index] for c in row) + ' |' for row in rows] + [''])

def link(url, en, fa):
    parts.append('<p>' + dual('a', en, fa, f'href="{escape(url, quote=True)}" rel="noopener"') + '</p>')
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([f'[{value}]({url})', ''])

def ref(path, en, fa): link('https://redis.io/docs/latest/' + path, en, fa)

def figure(filename, en, fa):
    src = '/assets/img/articles/content/' + filename
    parts.append(f'<figure><img src="{src}" width="1920" height="1080" loading="lazy" decoding="async" alt="{escape(en, quote=True)}" data-en-alt="{escape(en, quote=True)}" data-fa-alt="{escape(fa, quote=True)}">' + dual('figcaption', en, fa) + '</figure>')
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([f'![{value}]({src})', '', value, ''])

section('introduction', 'Introduction: verified versions and deployment scope', 'مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار')
p('Reviewed on 7 October 2026: Redis Open Source 8.10.2 is the latest stable release verified on the official release page, published on 17 September 2026. This guide uses the 8.10 command reference and the tagged 8.10.2 redis.conf. Always recheck the latest security patch and release notes before deployment; do not confuse Redis Open Source with Redis Software or Redis Cloud product versions.', 'تاریخ بررسی: ۷ اکتبر ۲۰۲۶، برابر با ۱۵ مهر ۱۴۰۵. آخرین نسخه Stable تأییدشده در Release رسمی، Redis Open Source 8.10.2 است که در ۱۷ سپتامبر ۲۰۲۶ منتشر شده است. مبنای سینتکس، Command Reference نسخه 8.10 و redis.conf مربوط به Tag نسخه 8.10.2 است. پیش از استقرار، آخرین Patch امنیتی و Release Notes را دوباره بررسی کنید؛ شماره نسخه Redis Software و Redis Cloud را با Redis Open Source اشتباه نگیرید.')
link('https://github.com/redis/redis/releases/tag/8.10.2', 'Official Redis 8.10.2 release', 'Release رسمی Redis 8.10.2')
ref('operate/oss_and_stack/install/version-mgmt/', 'Redis Open Source version lifecycle', 'چرخه پشتیبانی نسخه‌های Redis Open Source')
p('The worked hosts use Ubuntu Server 24.04 LTS, which remains supported. Ubuntu 26.04 LTS is the latest LTS at review time. On a newer LTS verify repository support, codename, package candidate, service unit and paths before applying this runbook. A distribution package may be older than the current upstream stable release. All Linux commands target your Redis hosts; they have been checked against documentation, not executed on a live Redis production deployment in this Windows website workspace.', 'Hostهای مثال Ubuntu Server 24.04 LTS دارند که همچنان پشتیبانی می‌شود. جدیدترین LTS در تاریخ بررسی Ubuntu 26.04 است. روی LTS جدیدتر، پشتیبانی مخزن Redis، Codename، نسخه Candidate، Unit سرویس و مسیرها را پیش از اعمال این راهنما تطبیق دهید. بسته مخزن Ubuntu الزاماً آخرین Stable بالادستی نیست. فرمان‌های Linux برای سرورهای Redis شما هستند؛ با مستندات بررسی شده‌اند و اجرای زنده آن‌ها روی Production در Workspace ویندوزی این سایت ادعا نمی‌شود.')
link('https://ubuntu.com/about/release-cycle', 'Canonical Ubuntu release and support cycle', 'چرخه انتشار و پشتیبانی رسمی Ubuntu')
section('prerequisites', 'Prerequisites and deployment scenario', 'پیش‌نیازها و سناریوی استقرار')
code('''Application Servers: 10.10.30.21, 10.10.30.22
             |
             v
Redis Primary: 10.10.20.10:6379
             |
             | Asynchronous Replication
             v
Redis Replica: 10.10.20.11:6379

Optional Replica-02: 10.10.20.12:6379
Management jump host: 10.10.40.10
Prometheus host: 10.10.40.20''', 'text')
p('Prerequisites: static private addresses, reliable DNS/time sync, SSH or console access, a dedicated Redis service account, measured RAM/SSD capacity and an approved maintenance window. Both nodes should start with the same Redis patch version and compatible modules. Change the sample IPs to your network; the additional addresses above make firewall examples concrete.', 'پیش‌نیازها: IP خصوصی ثابت، DNS و Time Sync صحیح، دسترسی SSH یا Console، حساب سرویس مستقل Redis، ظرفیت RAM و SSD اندازه‌گیری‌شده و Maintenance Window. هر دو نود را با Patch یکسان Redis و Moduleهای سازگار شروع کنید. IPهای نمونه را با شبکه خود عوض کنید؛ آدرس‌های تکمیلی بالا برای دقیق‌بودن مثال Firewall تعریف شده‌اند.')

section('what-is-redis', '1. What is Redis?', '۱. Redis چیست؟')
p('Redis is an in-memory data structure server commonly used as a key-value database and cache. Strings, hashes, lists, sets, sorted sets and streams support richer access patterns than a plain string cache. Persistence can retain data across restarts, but durability depends on the configured write and synchronization policy.', 'Redis یک In-Memory Data Structure Server است که به‌عنوان Key-Value Database و Cache استفاده می‌شود. علاوه بر String، ساختارهای Hash، List، Set، Sorted Set و Stream دارد؛ بنابراین از یک Cache ساده رشته‌ای فراتر می‌رود. Persistence می‌تواند داده را پس از Restart نگه دارد، اما دوام واقعی به Policy نوشتن و همگام‌سازی وابسته است.')
items([
('Cache: store recomputable results with TTL to reduce database and API work.', 'Cache: ذخیره نتیجه قابل بازسازی با TTL برای کاهش بار Database و API.'),
('Session store: share login and application session state across application servers.', 'Session Store: اشتراک وضعیت ورود و Session میان Application Serverها.'),
('Message broker and Pub/Sub: fast live fan-out; Pub/Sub is at-most-once and does not replay messages to disconnected subscribers.', 'Message Broker و Pub/Sub: ارسال سریع پیام زنده به چند مصرف‌کننده؛ Pub/Sub تحویل At-Most-Once دارد و پیام Subscriber قطع‌شده را بازپخش نمی‌کند.'),
('Queue: lists or Streams with consumer groups; design acknowledgment, retry, dead-letter handling and idempotency explicitly.', 'Queue: استفاده از List یا Stream با Consumer Group؛ Acknowledgment، Retry، Dead Letter و Idempotency را صریح طراحی کنید.'),
('Rate limiting: counters with bounded windows or scripts that atomically update counters and expiration.', 'Rate Limiting: Counter با پنجره زمانی محدود یا Script برای تغییر اتمیک Counter و Expiration.'),
('Distributed lock: an expiring key with a unique ownership token, verified release and fencing where required; a naive lock is not safe across asynchronous failover.', 'Distributed Lock: کلید Expiring با Token مالکیت یکتا، آزادسازی با تأیید مالکیت و در صورت نیاز Fencing؛ Lock ساده در Failover ناهمزمان تضمین ایمنی ندارد.'),
])
p('RAM access avoids many disk reads, compact data structures reduce overhead, and Redis processes common commands with an efficient event loop. Many simple operations are O(1). This does not mean every command is fast or that Redis has no I/O threads: large collections, expensive scripts, persistence, network round trips and memory pressure still influence latency. Relational databases also cache in RAM; Redis gains speed for a different workload and access model, not for every possible query.', 'دسترسی RAM بسیاری از خواندن‌های دیسک را حذف می‌کند، ساختارهای فشرده سربار را کم می‌کنند و Event Loop کارآمد فرمان‌های رایج را پردازش می‌کند. بسیاری از عملیات ساده O(1) هستند. این به معنی سریع‌بودن هر فرمان یا نبود I/O Thread نیست: Collection بزرگ، Script سنگین، Persistence، رفت‌وبرگشت شبکه و فشار حافظه همچنان Latency می‌سازند. Database رابطه‌ای نیز RAM Cache دارد؛ برتری Redis مربوط به Workload و مدل دسترسی مشخص است، نه هر Query ممکن.')
ref('develop/pubsub/', 'Pub/Sub delivery semantics', 'معنای تحویل پیام در Pub/Sub')
ref('develop/clients/patterns/distributed-locks/', 'Distributed lock safety considerations', 'ملاحظات ایمنی Distributed Lock')

section('enterprise-use', '2. Redis in enterprise architecture', '۲. کاربرد Redis در معماری Enterprise')
figure('redis-production-architecture.png', 'Redis production architecture: application servers, primary, replica and database on a private network', 'معماری Production Redis: سرورهای برنامه، Primary، Replica و Database در شبکه خصوصی')
code('''User -> Load Balancer -> Application Servers
                              |          |
                              v          v
                         Redis Cache   Database
                              |
                              v
                         Redis Replica''', 'text')
p('Applications talk to both Redis and the durable database. In cache-aside, read Redis first; on a miss query the database and fill Redis with a TTL. Redis is not a mandatory network hop between the application and database. Plan cache invalidation after writes, TTL jitter, protection against cache stampedes and a bounded fallback when Redis is unavailable.', 'Application هم به Redis و هم به Database پایدار متصل است. در Cache-Aside ابتدا Redis خوانده می‌شود؛ در Cache Miss، Database Query می‌شود و نتیجه با TTL در Redis قرار می‌گیرد. Redis یک Hop شبکه اجباری میان برنامه و Database نیست. Invalidation پس از Write، تغییر جزئی TTL برای جلوگیری از انقضای همزمان، کنترل Cache Stampede و Fallback محدود هنگام قطع Redis را طراحی کنید.')
items([
('Application, Laravel and API cache: separate prefixes and TTLs for each application; verify client username and TLS support.', 'Application Cache، Laravel Cache و API Cache: Prefix و TTL جدا برای هر برنامه؛ پشتیبانی Client از Username و TLS را بررسی کنید.'),
('Authentication/session storage: use the primary when freshness is required; define the effect of session loss on login.', 'Authentication Session و Session Storage: در نیاز به تازگی از Primary بخوانید؛ اثر ازدست‌رفتن Session بر Login را مشخص کنید.'),
('Queues and microservices: use a separate non-evicting instance for durable jobs, explicit consumer retry policies and idempotent processing.', 'Queue و Microservices: Job مهم را روی Instance جدا با Policy بدون Eviction قرار دهید؛ Retry مصرف‌کننده و پردازش Idempotent لازم است.'),
('Rate limits: use atomic operations and decide whether Redis failure should allow requests or reject them.', 'Rate Limit: عملیات اتمیک و تصمیم روشن برای Allow یا Reject درخواست هنگام خرابی Redis.'),
])
p('Logical databases and key prefixes help organize keys but do not isolate RAM, eviction, CPU or availability. A shared cache instance should not evict critical session, queue or lock keys. Scale the workload and choose a separate deployment where those requirements differ.', 'Logical Database و Prefix برای سازمان‌دهی کلید مفیدند، اما RAM، Eviction، CPU و Availability را جدا نمی‌کنند. Instance مشترک Cache نباید کلید حیاتی Session، Queue یا Lock را Evict کند. در تفاوت نیازها، Deployment جدا انتخاب کنید.')

section('installation', '3. Install Redis on Ubuntu', '۳. نصب Redis روی Ubuntu')
p('Run these commands on both nodes. A simple distribution installation is shown first as an alternative, not as a promise to install the latest upstream Redis. For the stable version used by this guide choose the official Redis APT repository and inspect the candidate before accepting it.', 'این مراحل را روی هر دو نود اجرا کنید. روش ساده مخزن توزیع ابتدا به‌عنوان گزینه جایگزین آمده است و تضمین نصب آخرین Redis بالادستی نیست. برای نسخه Stable مبنای مقاله، مخزن رسمی APT Redis را انتخاب کنید و قبل از نصب Candidate را بررسی کنید.')
sub('Distribution repository alternative', 'گزینه جایگزین: مخزن Ubuntu')
code('sudo apt update\napt-cache policy redis-server\nsudo apt install redis-server\nredis-server --version')
sub('Official Redis APT repository', 'نصب از مخزن رسمی Redis')
code('''sudo apt-get update
sudo apt-get install -y lsb-release curl gpg ca-certificates openssl
curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
sudo chmod 644 /usr/share/keyrings/redis-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/redis.list
sudo apt-get update
apt-cache policy redis redis-server redis-tools
apt-cache madison redis-server
sudo apt-get install redis
sudo systemctl enable --now redis-server
redis-server --version
redis-cli --version
dpkg-query -W redis redis-server redis-tools
systemctl status redis-server --no-pager''')
p('Expected: v=8.10.2 in redis-server --version and active (running) for the service, when that patch is offered by the official repository. INFO server separately reports redis_version:8.10.2. Package strings include an epoch and distribution suffix; do not invent a universal 8.10.2 package string. If the candidate is older, stop and check repository support instead of labelling it current. The GPG command assumes the keyring does not exist; on reruns compare and replace the keyring deliberately. Use signed-by rather than deprecated apt-key.', 'اگر مخزن رسمی این Patch را ارائه کند، در redis-server --version مقدار v=8.10.2 و وضعیت سرویس active (running) انتظار می‌رود. INFO server جداگانه redis_version:8.10.2 را می‌دهد. رشته نسخه بسته Epoch و پسوند Distribution دارد؛ یک Package String عمومی برای 8.10.2 نسازید. اگر Candidate قدیمی‌تر است، پشتیبانی مخزن را بررسی کنید و آن را نسخه فعلی معرفی نکنید. فرمان GPG فرض می‌کند Keyring هنوز وجود ندارد؛ در اجرای مجدد Keyring را آگاهانه مقایسه و جایگزین کنید. از signed-by استفاده می‌شود، نه apt-key منسوخ.')
p('Record the exact package versions from the first node and use the same approved versions on the replica. Test client/module compatibility in staging and roll out security patches through change management. Do not permanently hold packages without a patching process. Keep Redis bound to loopback until ACL and firewall deployment is complete.', 'نسخه دقیق بسته‌ها را از نود اول ثبت و همان نسخه تأییدشده را روی Replica نصب کنید. سازگاری Client و Module را در Staging تست و Patch امنیتی را با Change Management اعمال کنید. بدون فرآیند وصله‌کردن، بسته‌ها را دائماً Hold نکنید. تا تکمیل ACL و Firewall، Bind را روی Loopback نگه دارید.')
ref('operate/oss_and_stack/install/install-stack/apt/', 'Official Redis installation using APT', 'راهنمای رسمی نصب Redis با APT')

section('service-diagnostics', '4. Check the Redis service', '۴. بررسی سرویس Redis')
code('''systemctl status redis-server --no-pager
sudo systemctl show redis-server -p User -p Group -p Type -p ExecStart
sudo ss -lntp | grep ':6379'
redis-cli -h 127.0.0.1 -p 6379 ping
sudo journalctl -u redis-server -n 100 --no-pager''')
code('''active (running)
LISTEN ... 127.0.0.1:6379 ... redis-server
PONG''', 'text')
p('PONG without credentials is an initial local check only. After hardening it should return NOAUTH; use a named user with --askpass to obtain PONG. ss verifies the socket but not authentication or replication. Check that the actual listener never includes a public interface. ExecStart reveals command-line overrides and the configuration file really loaded by the service.', 'PONG بدون Credential فقط بررسی اولیه محلی است. پس از Hardening باید NOAUTH دریافت شود؛ برای گرفتن PONG از Named User و --askpass استفاده کنید. ss وجود Socket را تأیید می‌کند، نه Authentication یا Replication. Listener نباید Interface عمومی را شامل شود. ExecStart نیز Override خط فرمان و فایل تنظیماتی واقعاً بارگذاری‌شده را نشان می‌دهد.')
code('redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass PING')

section('configuration-files', '5. Configuration layout and backup', '۵. ساختار Configuration و Backup پیش از تغییر')
p('APT installations normally use /etc/redis/redis.conf. Verify this with systemctl cat and dpkg -L. Redis 8.10 uses redis.conf; the separate redis-full.conf used in older 8.x distributions is not the default model here. Preserve package module paths and service settings. Apply the operational baseline as a final include rather than replacing the entire package configuration.', 'نصب APT معمولاً از /etc/redis/redis.conf استفاده می‌کند؛ با systemctl cat و dpkg -L آن را تأیید کنید. Redis 8.10 از redis.conf استفاده می‌کند؛ مدل redis-full.conf جدا در برخی 8.x قدیمی‌تر مبنای این راهنما نیست. مسیر Moduleها و تنظیمات سرویس بسته را حفظ کنید. Baseline عملیاتی را با include نهایی اعمال کنید و کل فایل بسته را بی‌دلیل جایگزین نکنید.')
code('''systemctl cat redis-server
dpkg -L redis-server | grep -E 'redis.conf|systemd'
sudo cp -a /etc/redis/redis.conf "/etc/redis/redis.conf.backup.$(date -u +%Y%m%dT%H%M%SZ)"
sudo install -d -o redis -g redis -m 750 /var/lib/redis /var/log/redis
sudo install -o root -g redis -m 640 /dev/null /etc/redis/production.conf
sudoedit /etc/redis/production.conf
sudoedit /etc/redis/redis.conf''')
p('In redis.conf add the line below once, at the end. First create users.acl in section 8; remove any active inline user definitions and legacy requirepass setting to use one ACL source. Backup existing ACL/configuration files before changing an established deployment. These samples are for a new deployment; switching an existing RDB-only instance to AOF needs a live migration procedure.', 'خط زیر را فقط یک بار در انتهای redis.conf اضافه کنید. ابتدا طبق بخش ۸ users.acl را بسازید؛ برای یک منبع ACL، تعریف فعال inline user و requirepass قدیمی را حذف کنید. در Deployment موجود از فایل ACL و Config فعلی پیش از تغییر Backup بگیرید. نمونه‌ها برای استقرار جدیدند؛ تبدیل Instance دارای داده از RDB-only به AOF به فرآیند Migration زنده نیاز دارد.')
code('include /etc/redis/production.conf', 'text')
ref('operate/oss_and_stack/management/config/', 'Official configuration file layout and runtime persistence', 'ساختار رسمی فایل تنظیمات و دوام تغییر Runtime')

section('primary-configuration', '6. Configure the Redis primary', '۶. تنظیم Redis Primary')
p('Write this fragment to /etc/redis/production.conf on 10.10.20.10. The example assumes a dedicated 8 GiB host and an initial 4 GiB dataset limit; measure copy-on-write, modules and replication overhead before using that capacity in production. noeviction is the baseline for data that must not be silently removed; section 18 gives the separate cache profile.', 'روی 10.10.20.10 قطعه تنظیمات زیر را در /etc/redis/production.conf قرار دهید. فرض مثال Host مستقل با 8 GiB RAM و سقف اولیه 4 GiB برای Dataset است؛ پیش از استفاده عملیاتی سربار Copy-on-Write، Module و Replication را اندازه بگیرید. noeviction مبنای داده‌ای است که نباید بی‌صدا حذف شود؛ پروفایل مستقل Cache در بخش ۱۸ آمده است.')
PRIMARY = '''# Final include for a NEW dedicated Redis deployment; preserve package redis.conf.
bind 127.0.0.1 10.10.20.10
protected-mode yes
port 6379
timeout 0
tcp-keepalive 300
daemonize no
supervised auto
loglevel notice
logfile /var/log/redis/redis-server.log
dir /var/lib/redis
aclfile /etc/redis/users.acl
save ""
save 900 1
save 300 10
save 60 10000
dbfilename dump.rdb
rdbcompression yes
rdbchecksum yes
stop-writes-on-bgsave-error yes
appendonly yes
appendfilename "appendonly.aof"
appenddirname "appendonlydir"
appendfsync everysec
aof-use-rdb-preamble yes
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb
maxmemory 4gb
maxmemory-policy noeviction
replica-read-only yes
replica-serve-stale-data no
repl-backlog-size 64mb
slowlog-log-slower-than 10000
slowlog-max-len 128
latency-monitor-threshold 100'''
code(PRIMARY, 'text', 'primary-baseline.conf')
table([('Directive', 'Directive'), ('Purpose', 'کارکرد')], [
[(a,a),(b,c)] for a,b,c in [
('bind', 'Listen only on loopback and the explicit private address.', 'Listener فقط روی Loopback و IP خصوصی مشخص.'),
('protected-mode', 'Keep the misuse guard enabled; still enforce ACL and firewall.', 'Guard خطای تنظیم را فعال نگه دارید؛ ACL و Firewall همچنان لازم‌اند.'),
('port', 'TCP 6379 in this private-network baseline; TLS can replace it.', 'TCP/6379 در Baseline خصوصی؛ TLS می‌تواند جایگزین آن شود.'),
('timeout', '0 preserves idle pools; choose finite client timeouts in the application.', 'صفر Pool بیکار را حفظ می‌کند؛ Timeout درخواست را در برنامه محدود کنید.'),
('tcp-keepalive', '300 seconds detects dead peers; align with network idle limits.', '۳۰۰ ثانیه برای تشخیص Peer قطع‌شده؛ با Idle Limit شبکه تطبیق دهید.'),
('daemonize / supervised', 'Run in foreground; auto detects systemd notification environment.', 'اجرای Foreground؛ auto محیط Notify مربوط به systemd را تشخیص می‌دهد.'),
('loglevel / logfile', 'notice logs into a Redis-writable file; check package log rotation.', 'سطح notice در فایل قابل نوشتن Redis؛ Log Rotation بسته را بررسی کنید.'),
('dir', 'Writable persistent data directory; monitor disk space and permissions.', 'دایرکتوری پایدار قابل نوشتن؛ ظرفیت دیسک و Permission را پایش کنید.'),
]])
p('A Type=notify systemd unit may pass --supervised systemd itself; retain that package contract. supervised no fits some Type=simple units. Inspect the actual unit instead of forcing daemonize yes or changing its type. A logging profile with logfile "" uses stdout and usually the journal; select one supported log path and verify it.', 'Unit با Type=notify ممکن است --supervised systemd را خودش بدهد؛ قرارداد بسته را حفظ کنید. supervised no برای برخی Unitهای Type=simple مناسب است. Unit واقعی را بررسی کنید و daemonize yes یا Type را اجباری تغییر ندهید. در پروفایل logfile "" خروجی به stdout و معمولاً Journal می‌رود؛ مسیر Logging متناسب با Unit را انتخاب و تأیید کنید.')
link('https://raw.githubusercontent.com/redis/redis/8.10.2/redis.conf', 'Tagged Redis 8.10.2 configuration reference', 'مرجع تنظیمات Tag نسخه Redis 8.10.2')

section('security-hardening', '7. Security hardening and network segmentation', '۷. Security Hardening و تفکیک شبکه')
p('Never publish Redis directly on the internet, even with a password. Place it in a private server VLAN/subnet; deny ingress at host firewall and upstream security groups. Allow only the application servers and replication peers to reach the data service. Management and monitoring require explicit narrowly scoped exceptions, preferably local agents or a controlled jump host. protected-mode is a safety guard, not a firewall or encryption layer.', 'Redis را حتی با Password مستقیماً روی Internet منتشر نکنید. آن را در VLAN/Subnet خصوصی قرار دهید و Ingress را در Host Firewall و Security Group بالادستی محدود کنید. فقط Application Server و Peerهای Replication به سرویس داده دسترسی داشته باشند. Management و Monitoring به استثنای صریح و محدود نیاز دارند؛ ترجیحاً Agent محلی یا Jump Host کنترل‌شده. protected-mode یک Guard است، نه Firewall یا رمزنگاری.')
sub('Primary firewall example', 'مثال Firewall روی Primary')
code('''sudo apt-get install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from 10.10.40.10 to any port 22 proto tcp
sudo ufw allow from 10.10.30.21 to 10.10.20.10 port 6379 proto tcp
sudo ufw allow from 10.10.30.22 to 10.10.20.10 port 6379 proto tcp
sudo ufw allow from 10.10.20.11 to 10.10.20.10 port 6379 proto tcp
sudo ufw enable
sudo ufw status numbered''')
p('Before ufw enable, add the rule for your real current SSH source and ensure independent console access. Review existing broad 6379 or subnet allow rules; adding a narrow rule does not remove them. Apply a separate policy on 10.10.20.11: allow SSH from the jump host, optional application reads, and future Redis peers if HA is configured. With normal outgoing allow, the replica initiates the connection to the primary; the primary does not initiate a new replication connection to the replica.', 'پیش از ufw enable، Rule مربوط به Source واقعی SSH فعلی را اضافه و دسترسی Console مستقل را تأیید کنید. Rule عمومی قدیمی برای 6379 یا Subnet را بازبینی کنید؛ افزودن Rule محدود آن را حذف نمی‌کند. روی 10.10.20.11 Policy جدا اعمال کنید: SSH از Jump Host، Read اختیاری برنامه و Peerهای آینده Redis در صورت HA. با Allow خروجی معمول، Replica اتصال به Primary را آغاز می‌کند؛ Primary برای Replication اتصال جدیدی به Replica آغاز نمی‌کند.')
items([
('Run as the packaged redis user, never root; restrict configuration and credential files to root/redis and keep service/data directories separate.', 'سرویس با User بسته یعنی redis اجرا شود، نه root؛ Config و Credential را به root/redis محدود و مسیر داده و سرویس را جدا کنید.'),
('Grant application commands and key/channel prefixes explicitly. Deny FLUSHALL, FLUSHDB, CONFIG, MODULE and administrative scripting unless the role truly needs them.', 'Command و Prefix کلید/Channel برنامه را صریح Allow کنید. FLUSHALL، FLUSHDB، CONFIG، MODULE و Scripting مدیریتی بدون نیاز واقعی مجاز نباشند.'),
('Command renaming is deprecated for hardening; use ACL denial. Renaming replication/control commands can break Sentinel and operational tooling.', 'Command Renaming برای Hardening روش منسوخ است؛ از Deny در ACL استفاده کنید. Rename فرمان کنترل یا Replication می‌تواند Sentinel و ابزار عملیاتی را خراب کند.'),
('Rotate secrets through a secret manager, separate application/admin/replication roles, restrict SSH and test denied requests.', 'Secret را با Secret Manager Rotate کنید، Role برنامه/Admin/Replication را جدا کنید، SSH را محدود و درخواست Denied را تست کنید.'),
])
sub('TLS profile for sensitive networks', 'پروفایل TLS برای شبکه حساس')
p('The baseline TCP connection is unencrypted, including AUTH. For sensitive data or untrusted transport, replace the plaintext listener with TLS on both nodes. Provision CA-signed certificates with correct DNS/IP SANs, Redis-readable private keys and client certificates for each authorized client and replica. The following is an alternative overlay, not an additional plaintext listener.', 'Baseline روی TCP رمزنگاری نشده است؛ AUTH هم رمز نمی‌شود. برای داده حساس یا Transport غیرقابل اعتماد، Listener هر دو نود را با TLS جایگزین کنید. گواهی CA با DNS/IP SAN صحیح، Private Key قابل خواندن Redis و Client Certificate برای هر Client و Replica مجاز تهیه کنید. قطعه زیر Overlay جایگزین است، نه Listener اضافه بدون رمز.')
code('''port 0
tls-port 6379
tls-cert-file /etc/redis/tls/redis.crt
tls-key-file /etc/redis/tls/redis.key
tls-ca-cert-file /etc/redis/tls/ca.crt
tls-auth-clients yes
tls-replication yes''', 'text', 'tls-overlay.conf')
code('''redis-cli --tls -h 10.10.20.10 -p 6379 --cacert /etc/redis/tls/ca.crt \
  --cert /etc/redis/tls/client.crt --key /etc/redis/tls/client.key \
  --user admin --askpass PING''')
p('tls-replication yes must be ready on every promotion candidate, including the current primary. Server identity and certificate chain must be verified by application clients; never make insecure certificate skipping the deployment default. TLS does not replace ACL or segmentation. Verify TLS support in the installed package and test certificate renewal and rotation.', 'tls-replication yes باید روی تمام Candidateهای Promotion، از جمله Primary فعلی، آماده باشد. Application Client باید هویت سرور و Chain گواهی را بررسی کند؛ Skip بررسی گواهی را Default نکنید. TLS جای ACL یا Segmentation را نمی‌گیرد. پشتیبانی TLS بسته نصب‌شده و Renewal و Rotation گواهی را تست کنید.')
ref('operate/oss_and_stack/management/security/', 'Official Redis security and deprecated command renaming', 'امنیت رسمی Redis و منسوخ‌شدن Command Renaming')
ref('operate/oss_and_stack/management/security/encryption/', 'Redis TLS configuration', 'تنظیم TLS در Redis')

section('authentication-acl', '8. Authentication using Redis ACL', '۸. Authentication با Redis ACL')
p('Use named ACL users. requirepass remains supported as a compatibility setting for the default user; it is not the recommended multi-user design and is not claimed to be removed. This guide disables default, supplies a separate administrator, narrowly permits cache/session operations, and gives the replica only PING, REPLCONF and PSYNC. ACL is built in; “ACL enabled” means that effective users and permission rules have been deployed and verified.', 'از Named User در ACL استفاده کنید. requirepass همچنان برای سازگاری User پیش‌فرض پشتیبانی می‌شود؛ طراحی پیشنهادی Multi-User نیست و ادعا نمی‌کنیم حذف شده است. در این راهنما default غیرفعال، Administrator مستقل، عملیات Cache/Session محدود و Replica فقط دارای PING، REPLCONF و PSYNC است. ACL قابلیت داخلی است؛ «ACL فعال است» یعنی User و Permission مؤثر مستقر و بررسی شده‌اند.')
p('On a new node, run the Bash script below BEFORE restarting with the include from section 6. Enter four independent strong secrets already stored in your secret manager; enter the same replication secret on both nodes. At least 32 random bytes encoded as hex is a practical choice. This script rejects weak or non-hex inputs, writes hashes rather than plaintext into users.acl and prints no secrets. It intentionally refuses to overwrite an existing ACL file.', 'روی نود جدید، Script Bash زیر را پیش از Restart با include بخش ۶ اجرا کنید. چهار Secret قوی و مستقل که قبلاً در Secret Manager ذخیره کرده‌اید وارد کنید؛ Secret مربوط به Replication در هر دو نود یکسان باشد. حداقل ۳۲ بایت تصادفی به‌صورت Hex انتخاب عملی است. Script ورودی ضعیف یا غیر Hex را رد می‌کند، Hash را به‌جای متن Password در users.acl می‌نویسد و Secret چاپ نمی‌کند. فایل ACL موجود را عمداً Overwrite نمی‌کند.')
ACL_SCRIPT = r'''#!/usr/bin/env bash
set -euo pipefail
umask 077
if sudo test -e /etc/redis/users.acl; then
  echo "Existing ACL file: back up and review it before editing." >&2
  exit 1
fi
acl_tmp=$(mktemp)
trap 'rm -f "$acl_tmp"; unset acl_secret' EXIT
printf 'user default off resetpass resetkeys resetchannels -@all\n' > "$acl_tmp"
for acl_user in admin app repl monitor; do
  read -r -s -p "${acl_user} secret (64+ hex characters): " acl_secret
  printf '\n'
  [[ "$acl_secret" =~ ^[a-fA-F0-9]{64,}$ ]] || { echo "Invalid secret" >&2; exit 1; }
  acl_hash=$(printf '%s' "$acl_secret" | sha256sum | cut -d ' ' -f1)
  case "$acl_user" in
    admin) acl_rules='~* resetchannels +@all' ;;
    app) acl_rules='~cache:* ~session:* resetchannels -@all +ping +hello +select +client|setname +client|setinfo +acl|whoami +get +set +mget +mset +del +unlink +exists +expire +pexpire +ttl +pttl +incr +incrby +decr +decrby' ;;
    repl) acl_rules='resetkeys resetchannels -@all +ping +replconf +psync' ;;
    monitor) acl_rules='resetkeys resetchannels -@all +ping +info +role +slowlog|get +latency|latest +acl|whoami' ;;
  esac
  printf 'user %s on #%s %s\n' "$acl_user" "$acl_hash" "$acl_rules" >> "$acl_tmp"
  unset acl_secret acl_hash
done
sudo install -o root -g redis -m 640 "$acl_tmp" /etc/redis/users.acl
echo "ACL file installed; secrets remain in your secret manager."'''
code(ACL_SCRIPT, 'bash', 'bootstrap-acl.sh')
p('The administrator role is deliberately privileged and reserved for operations over a restricted management path. No application receives +@all. The app role above fits basic string cache/session operations, not every Laravel queue driver or Lua lock implementation: build separate worker roles from actual commands and key prefixes, using ACL DRYRUN and staging traffic. Do not grant broad channel access on Sentinel-managed nodes.', 'Role ادمین آگاهانه Privileged است و فقط برای Operations از مسیر Management محدود استفاده می‌شود. برنامه +@all دریافت نمی‌کند. app بالا برای عملیات ساده String Cache/Session است، نه هر Laravel Queue Driver یا Lua Lock؛ Role Worker جدا را از Command واقعی و Prefix مورد نیاز بسازید و با ACL DRYRUN و ترافیک Staging بررسی کنید. روی نود زیر Sentinel، دسترسی عمومی Channel ندهید.')
code('''sudo systemctl restart redis-server
systemctl is-active redis-server
redis-cli --user admin --askpass PING
redis-cli --user admin --askpass ACL LIST
redis-cli --user admin --askpass ACL WHOAMI
redis-cli --user admin --askpass ACL DRYRUN app SET cache:probe ok
redis-cli --user admin --askpass ACL DRYRUN app FLUSHALL
redis-cli --user app --askpass ACL WHOAMI
redis-cli PING''')
code('''active
PONG
ACL LIST: user default off ...; named users with #<sha256> hashes
ACL WHOAMI (admin): admin
ACL DRYRUN app SET cache:probe ok: OK
ACL DRYRUN app FLUSHALL: permission denied (wording may vary)
ACL WHOAMI (app): app
Unauthenticated PING: NOAUTH Authentication required.''', 'text')
p('ACL LIST returns rules and password hashes, so limit it to administrators and do not publish its output. WHOAMI shows the effective connection user. --askpass avoids putting passwords into command arguments and shell history. Runtime ACL SETUSER changes must be persisted separately: CONFIG REWRITE does not save an external ACL file. Here the file is managed by root; edit it securely and use authenticated ACL LOAD, or restart. ACL SAVE requires a Redis-writable ACL path and directory and should not be assumed to work with this root-owned deployment.', 'ACL LIST Ruleها و Hashهای Password را برمی‌گرداند؛ فقط به Admin بدهید و خروجی آن را منتشر نکنید. WHOAMI هویت مؤثر اتصال را نشان می‌دهد. --askpass Password را وارد Argument فرمان و Shell History نمی‌کند. تغییر Runtime با ACL SETUSER به Persistence جدا نیاز دارد؛ CONFIG REWRITE فایل ACL خارجی را ذخیره نمی‌کند. اینجا فایل زیر کنترل root است؛ آن را امن Edit و ACL LOAD احراز هویت‌شده یا Restart اجرا کنید. ACL SAVE به مسیر و دایرکتوری قابل نوشتن Redis نیاز دارد و در این استقرار root-owned نباید موفق فرض شود.')
ref('operate/oss_and_stack/management/security/acl/', 'ACL rules, external files and replication permissions', 'قواعد ACL، فایل خارجی و Permission مربوط به Replication')
ref('commands/acl-dryrun/', 'ACL DRYRUN permission simulation', 'شبیه‌سازی Permission با ACL DRYRUN')
ref('commands/acl-whoami/', 'ACL WHOAMI', 'مرجع ACL WHOAMI')
ref('commands/acl-list/', 'ACL LIST', 'مرجع ACL LIST')

section('persistence', '9. Persistence: RDB and AOF', '۹. Persistence؛ RDB و AOF')
sub('RDB: point-in-time snapshots', 'RDB؛ Snapshot در یک لحظه مشخص')
p('RDB stores a compact snapshot. It suits scheduled backups and fast loading, with possible loss of all changes since the last successful snapshot. save 900 1 means a snapshot condition of at least one change in 900 seconds; it is not an unconditional timer. BGSAVE forks a child, so reserve CPU, disk bandwidth and memory for copy-on-write. Avoid synchronous SAVE on a busy service.', 'RDB یک Snapshot فشرده نگه می‌دارد و برای Backup زمان‌بندی‌شده و Loading سریع مناسب است؛ ممکن است تمام تغییرهای پس از آخرین Snapshot موفق از دست بروند. save 900 1 یعنی شرط حداقل یک تغییر در ۹۰۰ ثانیه، نه Timer بدون شرط. BGSAVE یک Child با Fork می‌سازد؛ برای Copy-on-Write، CPU، پهنای باند دیسک و RAM ذخیره کنید. از SAVE همزمان روی سرویس شلوغ اجتناب کنید.')
code('''redis-cli --user admin --askpass BGSAVE
redis-cli --user admin --askpass INFO persistence
redis-cli --user admin --askpass LASTSAVE''')
code('''rdb_bgsave_in_progress:0
rdb_last_bgsave_status:ok
rdb_last_save_time:<unix_timestamp>''', 'text')
p('These expected fields describe a completed save; immediately after BGSAVE the in-progress flag may be 1. Verify a newer successful save time, rather than copying an old dump.rdb immediately after the command.', 'این Fieldهای مورد انتظار مربوط به Save تکمیل‌شده‌اند؛ بلافاصله پس از BGSAVE ممکن است Flag مقدار 1 باشد. زمان Save موفق جدیدتر را تأیید کنید؛ بلافاصله پس از فرمان، dump.rdb قدیمی را کپی نکنید.')
ref('commands/bgsave/', 'BGSAVE background snapshot command', 'فرمان Snapshot پس‌زمینه BGSAVE')
sub('AOF: append-only persistence', 'AOF؛ ثبت تغییرها در Append Only File')
p('AOF records write operations for recovery. appendfsync everysec is a common latency/durability compromise: a crash can normally lose roughly the latest second, and OS/storage stalls can widen the practical loss window. always requests fsync for every write batch at a latency cost; no leaves flush timing to the OS. Evaluate the storage guarantees rather than assuming any policy is zero-loss.', 'AOF عملیات Write را برای Recovery ثبت می‌کند. appendfsync everysec مصالحه رایج Latency و Durability است: Crash معمولاً حدود آخرین یک ثانیه را از دست می‌دهد و اختلال OS یا Storage می‌تواند پنجره واقعی را بزرگ‌تر کند. always برای هر Batch نوشتن fsync می‌خواهد و Latency بیشتری دارد؛ no زمان Flush را به OS می‌سپارد. تضمین Storage را ارزیابی کنید و هیچ Policy را خودکار Zero-Loss فرض نکنید.')
p('Current Redis uses a multipart AOF: a base file, incremental files and a manifest under appenddirname. With aof-use-rdb-preamble yes, the base can be RDB-encoded; this is not the same as a separately scheduled dump.rdb. BGREWRITEAOF compacts the history and also needs capacity. Copying only a legacy appendonly.aof filename is not a complete current AOF backup.', 'Redis فعلی Multipart AOF دارد: Base File، Incremental Fileها و Manifest زیر appenddirname. با aof-use-rdb-preamble yes ممکن است Base با قالب RDB باشد؛ این معادل dump.rdb زمان‌بندی‌شده جدا نیست. BGREWRITEAOF تاریخچه را فشرده می‌کند و به ظرفیت اضافه نیاز دارد. کپی فقط یک appendonly.aof قدیمی Backup کامل AOF فعلی نیست.')
code('''redis-cli --user admin --askpass INFO persistence
redis-cli --user admin --askpass BGREWRITEAOF
redis-cli --user admin --askpass INFO persistence''')
code('''aof_enabled:1
aof_rewrite_in_progress:0
aof_last_bgrewrite_status:ok
aof_last_write_status:ok''', 'text')
table([('Feature', 'ویژگی'), ('RDB', 'RDB'), ('AOF', 'AOF')], [
[(a,a),(b,c),(d,e)] for a,b,c,d,e in [
('Performance', 'Low steady write overhead; fork spikes', 'سربار Write روزمره کم؛ جهش هنگام Fork', 'Depends on fsync and rewrite load', 'وابسته به fsync و بار Rewrite'),
('Recovery', 'Last successful snapshot', 'آخرین Snapshot موفق', 'Replay base and incremental history', 'بازیابی Base و تاریخچه Incremental'),
('File size', 'Usually smaller/compact', 'معمولاً کوچک‌تر و فشرده', 'Often larger; rewrite compacts it', 'معمولاً بزرگ‌تر؛ Rewrite آن را فشرده می‌کند'),
('Durability', 'Loss since latest snapshot', 'ریسک تغییرهای پس از Snapshot', 'Better write durability, policy-dependent', 'دوام Write بهتر، وابسته به Policy'),
('Production usage', 'Yes, if snapshot RPO is acceptable', 'بله، اگر RPO مربوط به Snapshot قابل قبول باشد', 'Yes, if write durability is needed', 'بله، اگر دوام Write لازم باشد'),
]])
p('Combine RDB + AOF when you want periodic portable snapshots plus better write recovery. With both enabled Redis normally loads AOF at startup because it is more complete. This does not remove backup/restore testing. For a recomputable cache, persistence can be intentionally disabled if restart warming and database load are acceptable; for sessions or jobs choose an explicit RPO/RTO.', 'ترکیب RDB و AOF برای Snapshot دوره‌ای قابل انتقال و Recovery بهتر Write مناسب است. با فعال‌بودن هر دو، Redis در Startup معمولاً AOF را بارگذاری می‌کند چون کامل‌تر است. این ترکیب نیاز به Backup و تست Restore را حذف نمی‌کند. در Cache قابل بازسازی می‌توان Persistence را با پذیرش Warm-up و بار Database غیرفعال کرد؛ برای Session یا Job، RPO/RTO صریح تعیین کنید.')
sub('Enable AOF safely on an existing RDB-only instance', 'فعال‌کردن امن AOF روی Instance موجود با RDB-only')
p('Back up first. Enable AOF at runtime, wait for a successful initial rewrite and healthy AOF status, then persist appendonly yes in the managed configuration. Do not first restart an existing dataset with a newly enabled empty AOF directory: startup can select an empty AOF and lose the intended RDB recovery path. Treat disabling or switching persistence as a migration.', 'ابتدا Backup بگیرید. AOF را در Runtime فعال، تکمیل موفق Initial Rewrite و وضعیت سالم AOF را تأیید، سپس appendonly yes را در Config مدیریت‌شده ذخیره کنید. Dataset موجود را ابتدا با AOF تازه و دایرکتوری خالی Restart نکنید؛ Startup ممکن است AOF خالی را انتخاب کند و مسیر Recovery مورد نظر از RDB را از بین ببرد. تغییر یا غیرفعال‌کردن Persistence یک Migration است.')
code('redis-cli --user admin --askpass CONFIG SET appendonly yes\nredis-cli --user admin --askpass INFO persistence')
ref('operate/oss_and_stack/management/persistence/', 'Official RDB, multipart AOF, recovery and live AOF migration', 'مرجع رسمی RDB، Multipart AOF، Recovery و Migration زنده AOF')

section('replica-configuration', '10. Configure the Redis replica', '۱۰. راه‌اندازی Redis Replica')
p('On 10.10.20.11 install the same packages, deploy its own ACL users, protect its network path and apply the baseline below as the final include. Replication transfers datasets, not ACL/configuration files: provision those independently. Use the repl account on the primary and its matching secret in masterauth. All copies of production.conf containing masterauth must be root/redis readable only.', 'روی 10.10.20.11 بسته‌های یکسان، ACL مستقل و مسیر شبکه محدود ایجاد و Baseline زیر را با include نهایی اعمال کنید. Replication، Dataset را منتقل می‌کند، نه فایل ACL یا Config؛ آن‌ها را مستقل Provision کنید. Username برابر repl روی Primary و Secret متناظر در masterauth است. تمام کپی‌های production.conf دارای masterauth فقط برای root/redis قابل خواندن باشند.')
REPLICA = PRIMARY.replace('10.10.20.10', '10.10.20.11') + '''
replicaof 10.10.20.10 6379
masteruser repl
masterauth "REPLACE_WITH_REPLICATION_SECRET"
replica-priority 100'''
code(REPLICA, 'text', 'replica-baseline.conf')
p('REPLACE_WITH_REPLICATION_SECRET is a mandatory secret-manager substitution, not a working example password. The ACL file stores a hash, while masterauth needs the actual secret to send AUTH to the primary. Remove the marker before starting; never put real secrets into public article downloads. This is the persistent configuration. On a new replica initial full synchronization replaces its previous dataset, so do not point a node with valuable independent data at a primary without a backup and migration plan.', 'REPLACE_WITH_REPLICATION_SECRET جایگزینی اجباری از Secret Manager است، نه Password قابل استفاده. فایل ACL، Hash نگه می‌دارد اما masterauth برای ارسال AUTH به Primary به Secret واقعی نیاز دارد. Marker را پیش از Start حذف کنید و Secret واقعی را در Download عمومی مقاله قرار ندهید. این تنظیمات دائمی‌اند. Full Sync اولیه داده قبلی Replica را جایگزین می‌کند؛ نود دارای داده مستقل ارزشمند را بدون Backup و Migration Plan به Primary متصل نکنید.')
code('''sudo chown root:redis /etc/redis/production.conf /etc/redis/users.acl
sudo chmod 640 /etc/redis/production.conf /etc/redis/users.acl
sudo systemctl restart redis-server
redis-cli --user admin --askpass INFO replication
sudo journalctl -u redis-server -n 100 --no-pager''')
p('For a runtime topology change, with authentication already configured, the current command is REPLICAOF. It does not automatically persist the change. Manage the file explicitly; CONFIG REWRITE requires write access and a policy for included files. Do not use the deprecated SLAVEOF command in operator runbooks.', 'برای تغییر Topology در Runtime پس از تنظیم Authentication، فرمان فعلی REPLICAOF است. تغییر خودکار دائمی نمی‌شود. فایل را صریح مدیریت کنید؛ CONFIG REWRITE به مجوز Write و Policy مربوط به Includeها نیاز دارد. در Runbook اپراتور از فرمان قدیمی SLAVEOF استفاده نکنید.')
code('redis-cli --user admin --askpass REPLICAOF 10.10.20.10 6379')
p('Partial synchronization can catch up from the primary backlog after a short outage; a longer outage or missing history triggers a full sync. Size repl-backlog-size from measured replication bytes/sec multiplied by the tolerated disconnect period, with headroom. The 64 MiB example is only a starting point. Full synchronization and persistence both consume memory and I/O.', 'Partial Sync پس از قطعی کوتاه می‌تواند از Backlog Primary جبران کند؛ قطعی طولانی یا تاریخچه ناکافی Full Sync را فعال می‌کند. repl-backlog-size را از نرخ بایت Replication ضرب‌در مدت قطعی قابل تحمل، با حاشیه ظرفیت محاسبه کنید. 64 MiB مثال فقط نقطه شروع است. Full Sync و Persistence هر دو RAM و I/O مصرف می‌کنند.')
ref('commands/replicaof/', 'Current REPLICAOF syntax', 'سینتکس فعلی REPLICAOF')
ref('operate/oss_and_stack/management/replication/', 'Redis synchronization and authentication', 'همگام‌سازی و Authentication در Redis Replication')

section('verify-replication', '11. Verify replication status', '۱۱. بررسی وضعیت Replication')
sub('On the replica', 'روی Replica')
code('redis-cli -h 127.0.0.1 --user monitor --askpass INFO replication')
code('''role:slave
master_host:10.10.20.10
master_port:6379
master_link_status:up
master_last_io_seconds_ago:0
master_sync_in_progress:0
slave_read_only:1''', 'text')
p('Redis 8.10 retains legacy names such as role:slave and slave0 in INFO for compatibility; these output fields are not a recommendation to use deprecated configuration syntax. master_last_io_seconds_ago varies with heartbeat timing. Require an up link, completed sync and stable catch-up. An up link alone does not prove every write is current.', 'Redis 8.10 برای سازگاری نام‌های خروجی قدیمی مثل role:slave و slave0 را در INFO حفظ کرده است؛ این Fieldها توصیه به Syntax تنظیمات منسوخ نیستند. master_last_io_seconds_ago با Heartbeat تغییر می‌کند. Link برابر up، Sync تکمیل‌شده و Catch-up پایدار لازم است. Link سالم به‌تنهایی به‌روز بودن هر Write را اثبات نمی‌کند.')
sub('On the primary', 'روی Primary')
code('redis-cli -h 127.0.0.1 --user monitor --askpass INFO replication')
code('''role:master
connected_slaves:1
slave0:ip=10.10.20.11,port=6379,state=online,offset=<bytes>,lag=<seconds>
master_repl_offset:<bytes>
repl_backlog_active:1''', 'text')
p('Expect connected_slaves:1 for the two-node scenario, or 2 when Replica-02 is added. lag is the age of acknowledgments, not a precise measurement of application read freshness. Compare the primary offset with each replica offset over time; growing byte differences indicate catch-up pressure. Fields and ordering can vary, so monitoring should parse names, not line positions.', 'در سناریوی دو نود connected_slaves:1 و با افزودن Replica-02 مقدار 2 انتظار می‌رود. lag سن Acknowledgment است، نه اندازه‌گیری دقیق تازگی Read برنامه. Offset هر Replica را با Primary در طول زمان مقایسه کنید؛ اختلاف بایت رو به رشد فشار Catch-up را نشان می‌دهد. ترتیب و Fieldهای خروجی ممکن است تغییر کنند؛ نام Field را Parse کنید، نه موقعیت خط.')
ref('commands/info/', 'Current INFO fields and legacy replication output labels', 'Fieldهای فعلی INFO و نام‌های Legacy خروجی Replication')

section('replication-test', '12. Practical replication test', '۱۲. تست عملی Replication')
p('Use an administrator for the requested company key because the app role can access only cache:* and session:*. Open an authenticated interactive connection on the primary; SET and WAIT must run on that SAME connection. WAIT checks acknowledgments for preceding writes from that client, not unrelated CLI connections.', 'برای کلید company در مثال از Admin استفاده کنید چون app فقط cache:* و session:* را می‌بیند. روی Primary اتصال Interactive احراز هویت‌شده باز کنید؛ SET و WAIT باید در همان Connection اجرا شوند. WAIT، Acknowledgment مربوط به Write قبلی همین Client را بررسی می‌کند، نه CLI جدا.')
code('redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass')
code('''SET company "MEET AJ"
WAIT 1 5000''', 'redis')
code('''OK
(integer) 1''', 'text')
p('Then on the replica open an administrator connection locally and read the replicated key. If WAIT returned 0, investigate before treating the test as passed; a timeout does not roll back the SET.', 'سپس روی Replica اتصال Admin محلی باز و کلید Replicated را بخوانید. اگر WAIT مقدار 0 داد، قبل از موفق‌دانستن تست بررسی کنید؛ Timeout، SET را Rollback نمی‌کند.')
code('redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass')
code('GET company', 'redis')
code('"MEET AJ"', 'text')
p('Verify read-only behavior on the replica with a harmless probe; the expected result is READONLY. Clean up the company test on the primary after checking it. In a production application test use a dedicated expiring key under an allowed prefix, not an unbounded permanent probe.', 'رفتار Read-Only را روی Replica با Probe بی‌ضرر بررسی کنید؛ پاسخ مورد انتظار READONLY است. پس از بررسی، کلید تست company را از Primary پاک کنید. در تست برنامه واقعی از کلید اختصاصی دارای TTL زیر Prefix مجاز استفاده کنید، نه Probe دائمی بدون محدودیت.')
code('SET cache:replica-write-probe "blocked" EX 60', 'redis')
code('READONLY You can\'t write against a read only replica.', 'text')
code('redis-cli --user admin --askpass DEL company')
ref('commands/wait/', 'WAIT acknowledgment semantics and limitations', 'معنای Acknowledgment و محدودیت WAIT')

section('read-scaling', '13. Read from replicas', '۱۳. Read From Replica و Read Scaling')
p('Replicas copy the primary dataset and are read-only by default. Applications can explicitly route suitable reads to them for scaling; Redis does not automatically split application reads between standalone replicas. Standalone replicas accept reads without the Cluster-specific READONLY command. Use a client/router that knows the topology and sends every write to the current primary.', 'Replicaها Dataset Primary را کپی می‌کنند و به‌صورت پیش‌فرض Read-Only هستند. Application می‌تواند Read مناسب را صریح به آن‌ها Route کند؛ Redis خودکار Read را میان Replicaهای Standalone تقسیم نمی‌کند. Replica مستقل برای Read به فرمان مخصوص Cluster یعنی READONLY نیاز ندارد. Client یا Router آگاه از Topology باید تمام Writeها را به Primary فعلی بفرستد.')
p('Replication is asynchronous: a replica can return old data, including immediately after a successful SET on the primary. Read sessions, authorization state, locks and rate-limit decisions from the primary unless your consistency design explicitly permits stale data. WAIT improves acknowledgment confidence but does not make arbitrary replica reads linearizable or enforce durable disk flushes.', 'Replication ناهمزمان است؛ Replica می‌تواند داده قدیمی بدهد، حتی بلافاصله پس از SET موفق روی Primary. Session، وضعیت مجوز، Lock و تصمیم Rate Limit را از Primary بخوانید مگر طراحی Consistency صریحاً داده قدیمی را بپذیرد. WAIT اطمینان Acknowledgment را بیشتر می‌کند، اما Read دلخواه Replica را Linearizable یا Flush دیسک را تضمین نمی‌کند.')
p('This baseline sets replica-serve-stale-data no so data reads fail while the primary link is down or synchronization is incomplete; it does not eliminate lag on an up link. The default yes serves possibly stale data during outages. Choose availability versus freshness explicitly and alert on disconnected or lagging replicas. Adding replicas increases primary network and replication overhead; it does not shard memory or scale primary writes.', 'در Baseline مقدار replica-serve-stale-data no باعث شکست Read داده هنگام Link قطع یا Sync ناقص می‌شود؛ Lag با Link سالم را حذف نمی‌کند. مقدار پیش‌فرض yes در قطعی ممکن است داده قدیمی سرو کند. Availability و Freshness را آگاهانه انتخاب و روی Replica قطع یا Lagging هشدار بدهید. افزودن Replica سربار شبکه و Replication Primary را بیشتر می‌کند؛ Memory را Shard یا Write Primary را Scale نمی‌کند.')

section('replication-architecture', '14. Redis replication architecture', '۱۴. Redis Replication Architecture')
figure('redis-replication-architecture.png', 'Redis asynchronous replication from one primary to two read-only replicas', 'Replication ناهمزمان Redis از یک Primary به دو Replica فقط‌خواندنی')
code('''                 Applications
                      |
                      v
                 Redis Primary
              10.10.20.10:6379
                      |
           +----------+----------+
           |                     |
           v                     v
       Replica-01             Replica-02
   10.10.20.11:6379       10.10.20.12:6379''', 'text')
p('For Replica-02 repeat the replica procedure using its own bind address 10.10.20.12, add an explicit allow rule on the primary and keep replicaof pointed at 10.10.20.10. Distribute credentials independently and verify both connections. This architecture holds a full dataset copy on every Redis node; it is not a three-way partition of the dataset.', 'برای Replica-02 فرآیند Replica را با bind برابر 10.10.20.12 تکرار کنید، Rule صریح روی Primary اضافه و replicaof را همچنان به 10.10.20.10 اشاره دهید. Credentialها را مستقل توزیع و هر دو اتصال را بررسی کنید. هر نود Redis در این معماری کپی کامل Dataset دارد؛ داده میان سه نود تقسیم نمی‌شود.')

section('replication-is-not-ha', '15. Replication alone is not high availability', '۱۵. Replication به‌تنهایی High Availability نیست')
p('If the primary fails, a standalone replica does not automatically become the new primary and the application endpoint does not automatically move. Manual promotion without fencing the old primary risks two writable primaries during a partition. Automatic process restart is also not failover. Never let a persistence-disabled primary restart empty and re-synchronize surviving replicas from an empty dataset.', 'اگر Primary خراب شود، Replica مستقل خودکار Primary جدید نمی‌شود و Endpoint برنامه خودکار جابه‌جا نمی‌شود. Promotion دستی بدون Fencing مربوط به Primary قبلی در Network Partition می‌تواند دو Primary قابل Write بسازد. Restart خودکار Process نیز Failover نیست. Primary بدون Persistence نباید خالی Restart شود و Replicaهای دارای داده را از Dataset خالی دوباره Sync کند.')
p('An acknowledged primary write may not have reached the replica chosen for failover. WAIT can reduce that window; WAITAOF can wait for AOF fsync acknowledgments on selected participants when supported and configured. Neither substitutes for a backup or turns this asynchronous architecture into a universally lossless consensus system. Select RPO/RTO from business requirements and test crash, host failure and network partition recovery.', 'Write تأییدشده Primary ممکن است به Replica انتخاب‌شده برای Failover نرسیده باشد. WAIT پنجره را کاهش می‌دهد؛ WAITAOF در تنظیم پشتیبانی‌شده منتظر Acknowledgment مربوط به fsync در AOF روی مشارکت‌کننده‌های مشخص می‌ماند. هیچ‌کدام جای Backup نیست و معماری ناهمزمان را به سیستم Consensus همیشه بدون Loss تبدیل نمی‌کند. RPO/RTO را از نیاز کسب‌وکار انتخاب و بازیابی Crash، خرابی Host و Network Partition را تست کنید.')
ref('commands/waitaof/', 'WAITAOF durability acknowledgments', 'Acknowledgment مربوط به Durability با WAITAOF')

section('sentinel', '16. Redis Sentinel high availability', '۱۶. Redis Sentinel و High Availability')
figure('redis-sentinel-high-availability.png', 'Redis Sentinel high availability with three sentinels, one primary, two replicas and automatic failover', 'High Availability در Redis Sentinel با سه Sentinel، یک Primary، دو Replica و Failover خودکار')
code('''Application -- discovery --> Sentinel 1 / Sentinel 2 / Sentinel 3
     |
     +---- data connection --> Current Primary
                                  |
                              +---+---+
                              |       |
                           Replica 1 Replica 2''', 'text')
items([
('Monitoring: check Redis instance health and topology.', 'Monitoring: بررسی سلامت Instanceها و Topology.'),
('Failure detection: separate subjective down observations from quorum-backed objective down.', 'Failure Detection: تفکیک Subjective Down هر ناظر از Objective Down مورد تأیید Quorum.'),
('Automatic failover: coordinate promotion of a suitable replica and reconfigure the other nodes.', 'Automatic Failover: هماهنگی Promotion یک Replica مناسب و Reconfigure نودهای دیگر.'),
('Primary discovery: tell a Sentinel-aware client the current primary address.', 'Primary Discovery: اعلام آدرس Primary فعلی به Client سازگار با Sentinel.'),
])
p('Use at least three Sentinels in independent failure domains. A practical production topology is one primary, two replicas and three Sentinels; Sentinels can share the Redis hosts if those hosts occupy independent failure domains. For three Sentinels, quorum 2 detects objective failure; a majority of the total Sentinel set is also needed to authorize a failover. Quorum and election majority are different conditions. Running three processes on one host does not satisfy host-failure resilience.', 'حداقل سه Sentinel در Failure Domain مستقل داشته باشید. طراحی عملی Production شامل یک Primary، دو Replica و سه Sentinel است؛ Sentinelها می‌توانند روی Hostهای Redis اجرا شوند اگر Hostها Failure Domain مستقل دارند. برای سه Sentinel، Quorum برابر 2 تشخیص Objective Failure می‌دهد؛ Majority کل مجموعه نیز برای تأیید Failover لازم است. Quorum و Majority انتخاب رهبر دو شرط متفاوت‌اند. سه Process روی یک Host مقاومت در برابر خرابی Host ایجاد نمی‌کند.')
p('Sentinel is a control plane, not a data proxy. Application traffic connects directly to the discovered primary. Use a Sentinel-aware client, multiple discovery endpoints, bounded retries and separate Sentinel/data-plane credentials. Every potential primary must have the application, replication and Sentinel users, matching replication credentials, persistence and memory settings. Ensure masteruser/masterauth also exist on the original primary before HA cutover so it can later become a replica.', 'Sentinel یک Control Plane است، نه Data Proxy. ترافیک برنامه مستقیم به Primary کشف‌شده وصل می‌شود. Client سازگار، چند Endpoint کشف، Retry محدود و Credential جدا برای Sentinel و Data Plane لازم‌اند. تمام Primaryهای بالقوه باید User برنامه، Replication و Sentinel، Secret Replication هماهنگ، Persistence و Memory مناسب داشته باشند. پیش از HA Cutover، masteruser/masterauth را روی Primary اولیه نیز آماده کنید تا بعداً بتواند Replica شود.')
sub('Sentinel configuration example and security prerequisites', 'مثال Configuration مربوط به Sentinel و پیش‌نیاز امنیتی')
code('''# Template fragment: merge into each node-specific Sentinel configuration.
# Replace the auth-pass marker using your secret manager.
port 26379
sentinel monitor redis-prod 10.10.20.10 6379 2
sentinel auth-user redis-prod sentinel-control
sentinel auth-pass redis-prod "REPLACE_WITH_SENTINEL_CONTROL_SECRET"
sentinel down-after-milliseconds redis-prod 5000
sentinel failover-timeout redis-prod 60000
sentinel parallel-syncs redis-prod 1''', 'text', 'sentinel-monitor-template.conf')
p('The fragment is not a complete hardened Sentinel deployment. Set a node-specific private bind and firewall on TCP/26379; secure the Sentinel endpoints and peer authentication with ACL as documented, provision sentinel-control on every Redis node, and use a Redis-writable Sentinel configuration directory because Sentinel rewrites topology. Permit Sentinel control traffic to all promotion candidates and peer communication among the three Sentinels. The 5-second failure threshold is an example: tune it for real network/CPU pauses and test false-positive failovers.', 'این Fragment، Deployment کامل و Hardening‌شده Sentinel نیست. bind خصوصی مخصوص هر نود و Firewall روی TCP/26379 تنظیم کنید؛ Endpoint و Authentication میان Peerهای Sentinel را طبق مستندات با ACL امن کنید، sentinel-control را روی تمام نودهای Redis بسازید و فایل Config Sentinel را در دایرکتوری قابل نوشتن سرویس قرار دهید چون Topology را Rewrite می‌کند. ترافیک کنترلی به تمام Candidateها و ارتباط میان سه Sentinel لازم است. آستانه ۵ ثانیه فقط مثال است؛ با Pause واقعی CPU/شبکه تطبیق و Failover اشتباه را تست کنید.')
p('Grant sentinel-control only the documented control operations and channel &__sentinel__:hello. The official Sentinel ACL currently includes +slaveof for its internal compatibility command and may need +replicaof for tooling; this is an explicit compatibility exception, not operator use of deprecated SLAVEOF. Application users must not publish to the reserved __sentinel__: channels. Keep Sentinel topology changes compatible with your configuration manager; a static file deploy must not restore the old primary after failover.', 'به sentinel-control فقط عملیات کنترلی مستند و Channel برابر &__sentinel__:hello بدهید. ACL رسمی Sentinel فعلاً +slaveof را برای فرمان داخلی سازگاری می‌خواهد و ابزار ممکن است +replicaof نیز لازم داشته باشد؛ این استثنای سازگاری صریح است، نه استفاده اپراتور از SLAVEOF منسوخ. Application User نباید روی Channel رزروشده __sentinel__: پیام Publish کند. تغییر Topology Sentinel را با Configuration Manager هماهنگ کنید؛ Deploy فایل ثابت نباید بعد از Failover، Primary قدیمی را برگرداند.')
code('''# After provisioning a Sentinel observer user with these subcommands:
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL CKQUORUM redis-prod
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL GET-MASTER-ADDR-BY-NAME redis-prod
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL REPLICAS redis-prod''')
p('Acceptance: CKQUORUM confirms enough Sentinels and a majority; discovery returns the current host and port. In staging, stop the primary under a reviewed failure test, verify replica promotion, application reconnection, surviving replication and the old primary returning as a replica. Test a network partition and measure lost writes and recovery time. Restrict writes with min-replicas-to-write/min-replicas-max-lag if the availability tradeoff is acceptable; those limits reduce risk but do not create strong consistency.', 'معیار پذیرش: CKQUORUM وجود Sentinel کافی و Majority را تأیید می‌کند؛ Discovery آدرس و Port فعلی می‌دهد. در Staging با Failure Test بررسی‌شده، Primary را متوقف و Promotion، اتصال مجدد برنامه، ادامه Replication و برگشت Primary قبلی به‌عنوان Replica را تأیید کنید. Network Partition را تست و Write ازدست‌رفته و زمان Recovery را اندازه بگیرید. در پذیرش Tradeoff مربوط به Availability می‌توانید Write را با min-replicas-to-write/min-replicas-max-lag محدود کنید؛ این محدودیت‌ها ریسک را کم می‌کنند، نه اینکه Strong Consistency بسازند.')
ref('operate/oss_and_stack/management/sentinel/', 'Official Sentinel deployment, quorum, ACL and failover requirements', 'الزامات رسمی Sentinel، Quorum، ACL و Failover')
ref('develop/reference/sentinel-clients/', 'Sentinel client discovery protocol', 'پروتکل Discovery برای Clientهای Sentinel')

section('cluster-comparison', '17. Replication vs Sentinel vs Redis Cluster', '۱۷. تفاوت Replication، Sentinel و Redis Cluster')
table([('Architecture', 'معماری'), ('Replication', 'Replication'), ('Automatic failover', 'Failover خودکار'), ('Sharding', 'Sharding')], [
[('Replication','Replication'),('Yes','بله'),('No','خیر'),('No','خیر')],
[('Sentinel','Sentinel'),('Yes','بله'),('Yes','بله'),('No','خیر')],
[('Redis Cluster','Redis Cluster'),('Yes, with replicas','بله، با Replica'),('Yes, with eligible replicas and quorum','بله، با Replica مناسب و Quorum'),('Yes','بله')],
])
p('Replication copies data. Sentinel adds monitoring/discovery/failover around a non-sharded primary-replica group. Redis Cluster distributes keys over 16,384 hash slots and uses its own failover protocol; it does not require Sentinel for Cluster failover. A common production starting topology is three primaries and one replica per primary, six nodes, placed so a primary and its replica do not share a failure domain.', 'Replication داده را کپی می‌کند. Sentinel، Monitoring، Discovery و Failover را به گروه Primary/Replica بدون Sharding اضافه می‌کند. Redis Cluster کلیدها را میان 16,384 Hash Slot توزیع و از Failover Protocol خود استفاده می‌کند؛ برای Failover مربوط به Cluster به Sentinel نیاز ندارد. Topology رایج شروع Production سه Primary و یک Replica برای هر Primary، مجموعاً شش نود است؛ Primary و Replica متناظر نباید Failure Domain مشترک داشته باشند.')
p('Cluster needs a Cluster-aware client and changes multi-key operations: related keys often need hash tags to share a slot. It supports database 0 only. Plan migration, resharding and access to both client and cluster-bus ports. The default bus port is the data port + 10000, so 6379 normally implies 16379. ACL on the client port does not authenticate the bus. Redis 8.10.2 specifically documents tls-cluster and cluster-bus-port-protected-mode; secure and segment that bus, and test certificates and client redirection.', 'Cluster به Client سازگار نیاز دارد و عملیات Multi-Key را تغییر می‌دهد: کلیدهای مرتبط معمولاً به Hash Tag برای Slot مشترک نیاز دارند. فقط Database 0 دارد. Migration، Resharding و دسترسی به Port Client و Cluster Bus را برنامه‌ریزی کنید. Port پیش‌فرض Bus برابر Port داده به‌اضافه 10000 است؛ برای 6379 معمولاً 16379 می‌شود. ACL Port Client، Bus را احراز هویت نمی‌کند. Redis 8.10.2 صریحاً tls-cluster و cluster-bus-port-protected-mode را توضیح داده است؛ Bus را امن و Segment و گواهی و Client Redirection را تست کنید.')
ref('operate/oss_and_stack/management/scaling/', 'Official Redis Cluster topology, hash slots and ports', 'Topology، Hash Slot و Portهای رسمی Redis Cluster')
link('https://github.com/redis/redis/releases/tag/8.10.2', 'Redis 8.10.2 cluster bus security change', 'تغییر امنیت Cluster Bus در Redis 8.10.2')

section('memory-management', '18. Memory management and eviction policy', '۱۸. Memory Management و Eviction Policy')
p('maxmemory limits memory considered by eviction, not the entire process RSS or a hard host RAM cap. Replication/AOF buffers, allocator fragmentation, modules, OS needs and copy-on-write during fork require additional headroom. An 8 GiB host with maxmemory 4gb is an initial example, not a universal 50% sizing rule. Measure peak RSS and copy-on-write under a realistic rewrite/full-sync load, and alert before swap or the OOM killer becomes the limiting mechanism.', 'maxmemory حافظه محاسبه‌شده برای Eviction را محدود می‌کند، نه کل RSS فرآیند یا سقف سخت RAM Host. Buffer مربوط به Replication/AOF، Fragmentation، Module، OS و Copy-on-Write زمان Fork به ظرفیت اضافی نیاز دارند. maxmemory 4gb روی Host دارای 8 GiB فقط مثال اولیه است، نه قانون عمومی ۵۰ درصد. Peak RSS و Copy-on-Write را زیر Rewrite و Full Sync واقعی اندازه بگیرید و پیش از Swap یا OOM Killer هشدار بدهید.')
table([('Policy', 'Policy'), ('Behavior and use', 'رفتار و کاربرد')], [
[(a,a),(b,c)] for a,b,c in [
('noeviction', 'Reject memory-growing writes at the limit; preserves keys for sessions/jobs but requires handling OOM errors.', 'Write افزاینده حافظه را در سقف رد می‌کند؛ کلید Session/Job حفظ می‌شود اما مدیریت OOM لازم است.'),
('allkeys-lru', 'Evict approximately least recently used keys from all keys; a good general cache starting point.', 'حذف تقریبی کلیدهای کمتر استفاده‌شده اخیراً از تمام کلیدها؛ شروع مناسب Cache عمومی.'),
('allkeys-lfu', 'Evict approximately least frequently used keys; test for a stable hot working set.', 'حذف تقریبی کلیدهای کمتر استفاده‌شده از نظر دفعات؛ برای Hot Working Set پایدار تست کنید.'),
('volatile-lru', 'Apply LRU only to keys with TTL; without eligible keys behaves like noeviction.', 'LRU فقط روی کلید دارای TTL؛ بدون Candidate رفتار شبیه noeviction دارد.'),
('volatile-ttl', 'Evict eligible TTL keys with shortest remaining time; useful when TTL encodes value.', 'حذف کلید دارای TTL با کوتاه‌ترین عمر باقی‌مانده؛ وقتی TTL بیانگر ارزش داده است مفید است.'),
]])
sub('Practical cache-only profile', 'پروفایل عملی Cache مستقل')
code('maxmemory 4gb\nmaxmemory-policy allkeys-lru', 'text', 'cache-memory-overlay.conf')
p('For a cache-only instance start with allkeys-lru, explicit TTLs and protection against stampedes; compare hit rate, evictions and database fallback load before trying LFU. TTL controls freshness while eviction controls capacity. Do not share this profile with job/lock/session keys that must survive memory pressure. Changing the policy requires no application data reset but can immediately affect which keys are evicted.', 'برای Instance فقط Cache از allkeys-lru، TTL صریح و کنترل Stampede شروع کنید؛ قبل از آزمایش LFU، Hit Rate، Eviction و بار Fallback Database را مقایسه کنید. TTL تازگی داده و Eviction ظرفیت را کنترل می‌کند. این پروفایل را با Job، Lock یا Session دارای الزام ماندگاری زیر فشار حافظه مشترک نکنید. تغییر Policy به Reset داده نیاز ندارد اما می‌تواند بلافاصله انتخاب کلید Evicted را عوض کند.')
p('Replicas normally ignore maxmemory while replicating and apply primary-driven evictions; capacity must fit the full replicated dataset and its buffers. Keep an appropriate maxmemory/policy on replicas for possible promotion. Setting replica-ignore-maxmemory no changes that behavior and is not a general fix for undersized replicas.', 'Replica معمولاً هنگام Replication، maxmemory را نادیده می‌گیرد و Eviction Primary را اعمال می‌کند؛ ظرفیت باید کل Dataset کپی‌شده و Buffer را پوشش دهد. برای Promotion احتمالی maxmemory/Policy مناسب روی Replica نیز تنظیم کنید. replica-ignore-maxmemory no این رفتار را تغییر می‌دهد و راه‌حل عمومی Replica کم‌ظرفیت نیست.')
ref('develop/reference/eviction/', 'Current eviction policies and memory accounting', 'Policyهای فعلی Eviction و محاسبه Memory')
sub('Linux memory and service prerequisites', 'پیش‌نیاز Memory و سرویس در Linux')
code('''sysctl vm.overcommit_memory net.core.somaxconn
systemctl show redis-server -p LimitNOFILE
cat /sys/kernel/mm/transparent_hugepage/enabled
# On a dedicated Redis host, persist the official overcommit recommendation:
printf 'vm.overcommit_memory = 1\n' | sudo tee /etc/sysctl.d/99-redis.conf
sudo sysctl -p /etc/sysctl.d/99-redis.conf''')
p('Redis recommends vm.overcommit_memory=1 to reduce fork failures; it changes the host policy, so coordinate it on shared hosts. Redis 8.10.2 defaults disable-thp yes to disable problematic THP use for the Redis process when needed; verify the running configuration and latency instead of blindly copying a legacy global THP script. Inspect service file limits and listen backlog against the measured client load. Provision swap according to your host policy as a capacity emergency mechanism, but treat actual Redis swapping as an urgent latency incident, not normal operating headroom.', 'Redis برای کاهش Fork Failure مقدار vm.overcommit_memory=1 را توصیه می‌کند؛ این Policy کل Host را تغییر می‌دهد و روی Host مشترک باید هماهنگ شود. در Redis 8.10.2 پیش‌فرض disable-thp yes در صورت نیاز اثر نامناسب THP را برای Process Redis محدود می‌کند؛ Config اجراشده و Latency را بررسی کنید، نه اینکه Script قدیمی غیرفعال‌کردن سراسری THP را کورکورانه کپی کنید. File Limit سرویس و Listen Backlog را با بار Client اندازه‌گیری‌شده تطبیق دهید. Swap را مطابق Policy Host برای وضعیت اضطراری ظرفیت Provision کنید اما Swap واقعی Redis را Incident فوری Latency بدانید، نه حاشیه ظرفیت روزمره.')
ref('operate/oss_and_stack/management/admin/', 'Redis Linux administration prerequisites', 'پیش‌نیازهای رسمی Administration در Linux')

section('monitoring', '19. Monitoring and operational metrics', '۱۹. Monitoring و Metricهای عملیاتی')
code('''redis-cli --user monitor --askpass INFO
redis-cli --user monitor --askpass INFO memory
redis-cli --user monitor --askpass INFO stats
redis-cli --user monitor --askpass INFO clients
redis-cli --user monitor --askpass INFO replication
redis-cli --user monitor --askpass INFO persistence
redis-cli --user monitor --askpass SLOWLOG GET 10
redis-cli --user monitor --askpass LATENCY LATEST''')
table([('Metric', 'Metric'), ('Fields or measurement', 'Field یا روش اندازه‌گیری'), ('Operational interpretation', 'تفسیر عملیاتی')], [
[(a,a),(b,b),(c,d)] for a,b,c,d in [
('Memory', 'used_memory, used_memory_rss, maxmemory, mem_not_counted_for_evict', 'Compare dataset, actual RSS and excluded buffers.', 'Dataset، RSS واقعی و Buffer خارج از محاسبه را مقایسه کنید.'),
('Clients', 'connected_clients, blocked_clients, rejected_connections', 'Unexpected growth suggests pool leaks or limits.', 'رشد غیرمنتظره می‌تواند Pool Leak یا محدودیت اتصال باشد.'),
('Cache hit / miss', 'keyspace_hits, keyspace_misses', 'Use interval deltas/rates, not only lifetime ratios.', 'از Delta/Rate بازه استفاده کنید، نه فقط نسبت کل عمر سرویس.'),
('Evictions / expiration', 'evicted_keys, expired_keys', 'Distinguish capacity removal from intended TTL expiry.', 'حذف ظرفیت را از انقضای هدفمند TTL تفکیک کنید.'),
('Commands/sec', 'instantaneous_ops_per_sec, total_commands_processed', 'Correlate load changes with latency and CPU.', 'تغییر بار را با Latency و CPU مقایسه کنید.'),
('Replication', 'master_link_status, connected_slaves, offsets, lag', 'Check link state, replica count and growing offset gaps.', 'Link، تعداد Replica و رشد فاصله Offset را بررسی کنید.'),
('Persistence', 'rdb_last_bgsave_status, aof_last_write_status, aof_last_bgrewrite_status', 'Any failure needs disk/permission/memory investigation.', 'Failure به بررسی دیسک، Permission و Memory نیاز دارد.'),
('CPU / host pressure', 'INFO cpu; host CPU, swap, disk latency', 'Use OS monitoring alongside Redis statistics.', 'Monitoring سیستم‌عامل را در کنار Redis داشته باشید.'),
('Network', 'total_net_input_bytes, total_net_output_bytes', 'Use rates and compare replication throughput.', 'Rate را محاسبه و Throughput مربوط به Replication را مقایسه کنید.'),
('Latency', 'SLOWLOG, LATENCY LATEST, application p95/p99', 'Measure client round trips and command execution separately.', 'Round Trip سمت Client و اجرای فرمان را جدا اندازه بگیرید.'),
]])
code('''Hit Rate  = delta(keyspace_hits) / (delta(keyspace_hits) + delta(keyspace_misses)) * 100
Miss Rate = 100 - Hit Rate
# If no lookups occurred, the ratio is undefined: report no traffic.
# Counter deltas must account for process restarts and counter resets.''', 'text')
p('SLOWLOG records command execution time in microseconds and excludes client/network I/O; an empty log does not prove good user latency. This baseline logs executions slower than 10,000 microseconds and keeps 128 entries. latency-monitor-threshold 100 tracks latency events above 100 milliseconds. Slow log arguments can contain sensitive data: restrict access and retention. Avoid routinely running MONITOR or KEYS on busy production instances.', 'SLOWLOG زمان اجرای Command را به Microsecond ثبت می‌کند و I/O شبکه/Client را شامل نمی‌شود؛ Log خالی، Latency خوب کاربر را اثبات نمی‌کند. Baseline اجراهای بیش از 10,000 Microsecond را با ظرفیت ۱۲۸ رکورد نگه می‌دارد. latency-monitor-threshold 100 رخداد بیش از ۱۰۰ Millisecond را دنبال می‌کند. Argumentهای Slow Log ممکن است داده حساس داشته باشند؛ Access و Retention را محدود کنید. MONITOR یا KEYS را بررسی روزمره Production شلوغ قرار ندهید.')
ref('commands/slowlog-get/', 'SLOWLOG timing and fields', 'زمان‌سنجی و Fieldهای SLOWLOG')
ref('operate/oss_and_stack/management/optimization/latency/', 'Official latency diagnosis', 'تشخیص رسمی Latency')

section('prometheus-grafana', '20. Prometheus, Grafana and alerting', '۲۰. Prometheus، Grafana و Alerting')
figure('redis-monitoring-architecture.png', 'Redis monitoring with Redis Exporter, Prometheus, Grafana and Alertmanager', 'پایش Redis با Redis Exporter، Prometheus، Grafana و Alertmanager')
p('The image shows illustrative dashboard values, not measurements from this deployment. A local Redis Exporter reads each node, Prometheus scrapes its private /metrics endpoint, Grafana queries Prometheus, and alert rules send notifications through Alertmanager. The arrows illustrate metric flow; Prometheus pulls metrics rather than receiving an unsolicited push. Redis Exporter is a third-party component; verify compatibility and pin its tested release independently of Redis.', 'اعداد Dashboard تصویر فقط نمونه‌اند، نه اندازه‌گیری این Deployment. Redis Exporter محلی هر نود را می‌خواند، Prometheus از Endpoint خصوصی /metrics، Scrape می‌کند، Grafana به Prometheus Query می‌زند و Alert Rule از Alertmanager اعلان می‌فرستد. Arrowها جریان داده را نشان می‌دهند؛ Prometheus داده را Pull می‌کند، نه Push ناخواسته دریافت کند. Redis Exporter جزء Third-Party است؛ سازگاری را بررسی و Release تست‌شده آن را مستقل از Redis Pin کنید.')
p('Use a dedicated exporter ACL account matched to the selected exporter version and enabled collectors; the diagnostic monitor user above is intentionally too narrow for every exporter collector. Avoid granting key scans, GET or EVAL when not needed. Inspect the upstream ACL example, remove unnecessary collectors/permissions, check ACL LOG and scrape errors in staging, and verify redis_up = 1. Store credentials in an owner-readable file or secret mount; never embed them in public Prometheus YAML or process arguments.', 'برای Exporter از User ACL اختصاصی منطبق با نسخه و Collector فعال استفاده کنید؛ monitor تشخیصی بالا آگاهانه برای تمام Collectorها کافی نیست. بدون نیاز Key Scan، GET یا EVAL ندهید. نمونه ACL پروژه را بررسی، Collector و Permission غیرضروری را حذف، ACL LOG و Scrape Error را در Staging کنترل و redis_up = 1 را تأیید کنید. Credential را در فایل فقط قابل خواندن Owner یا Secret Mount قرار دهید، نه YAML عمومی Prometheus یا Argument فرآیند.')
sub('Prometheus scrape configuration', 'تنظیم Scrape در Prometheus')
code('''# Merge with the existing Prometheus configuration.
scrape_configs:
  - job_name: redis
    scrape_interval: 15s
    scrape_timeout: 10s
    static_configs:
      - targets: ['10.10.20.10:9121']
        labels:
          redis_group: redis-prod
          node: redis-01
      - targets: ['10.10.20.11:9121']
        labels:
          redis_group: redis-prod
          node: redis-02''', 'yaml', 'prometheus-scrape.yml')
p('Run one exporter per node, bound to its private interface or through an authenticated collector. Permit TCP/9121 only from Prometheus 10.10.40.20; do not expose arbitrary /scrape targets or metrics to the internet. In sensitive networks use authenticated HTTPS for scraping too. Label physical nodes, not permanent primary/replica roles, because roles change during failover. Validate the merged configuration with promtool check config before reloading.', 'روی هر نود یک Exporter با Bind خصوصی یا Collector احراز هویت‌شده اجرا کنید. TCP/9121 فقط از Prometheus برابر 10.10.40.20 مجاز باشد؛ Target دلخواه /scrape و Metric را روی Internet منتشر نکنید. در شبکه حساس Scrape نیز HTTPS احراز هویت‌شده داشته باشد. نود فیزیکی را Label کنید، نه Role ثابت Primary/Replica، چون Failover نقش را عوض می‌کند. پیش از Reload، فایل Merge‌شده را با promtool check config اعتبارسنجی کنید.')
code('''# Run on each Redis node, with its own destination IP:
sudo ufw allow from 10.10.40.20 to 10.10.20.10 port 9121 proto tcp
# Run on the Prometheus host:
promtool check config /etc/prometheus/prometheus.yml
curl -fsS http://10.10.20.10:9121/metrics | grep '^redis_up'
curl -fsS http://10.10.20.11:9121/metrics | grep '^redis_up' ''')
sub('Dashboard metrics and example PromQL', 'Metricهای Dashboard و مثال PromQL')
items([
('Availability: redis_up and Prometheus up; exporter process up does not prove Redis is reachable.', 'Availability: مقدار redis_up و up مربوط به Prometheus؛ سالم‌بودن Process Exporter اتصال Redis را اثبات نمی‌کند.'),
('Memory/client/load: redis_memory_used_bytes, redis_memory_max_bytes, redis_connected_clients, redis_commands_processed_total.', 'Memory/Client/Load: مقادیر redis_memory_used_bytes، redis_memory_max_bytes، redis_connected_clients و redis_commands_processed_total.'),
('Cache: redis_keyspace_hits_total, redis_keyspace_misses_total and redis_evicted_keys_total.', 'Cache: مقادیر redis_keyspace_hits_total، redis_keyspace_misses_total و redis_evicted_keys_total.'),
('Replication/persistence: current role, connected replica count, link state, offset gap, snapshot age and last persistence error.', 'Replication/Persistence: Role فعلی، تعداد Replica متصل، Link، اختلاف Offset، سن Snapshot و آخرین خطای Persistence.'),
('Host and application: CPU, network rates, disk latency/free space, swap, p95/p99 application latency and timeout rate.', 'Host و Application: CPU، Rate شبکه، Latency و فضای دیسک، Swap، Latency صدک p95/p99 برنامه و نرخ Timeout.'),
])
code('''# Commands per second (per scraped instance)
rate(redis_commands_processed_total{job="redis"}[5m])

# Cache hit percentage; no lookups produce no meaningful ratio
100 * rate(redis_keyspace_hits_total{job="redis"}[5m]) /
(rate(redis_keyspace_hits_total{job="redis"}[5m]) + rate(redis_keyspace_misses_total{job="redis"}[5m]))

# Evictions per second
rate(redis_evicted_keys_total{job="redis"}[5m])

# Redis connectivity alert condition
redis_up{job="redis"} == 0''', 'promql')
p('Confirm the metric names, units and labels at /metrics for the pinned exporter. Alert on Redis unreachable, missing scrape targets, disconnected replicas, increasing offset gaps, low quorum, persistence errors, stale backups and sustained memory/latency pressure. Start with a 2-minute connectivity alert and memory warning around 80% as examples, then tune against actual SLOs; critical persistence failures need immediate investigation. Route and test notifications with an owner and response runbook, not just a dashboard.', 'نام، Unit و Label Metric را در /metrics نسخه Pin‌شده تأیید کنید. برای Redis غیرقابل دسترس، Target Scrape غایب، Replica قطع، رشد Offset Gap، Quorum کم، خطای Persistence، Backup قدیمی و فشار پایدار Memory/Latency هشدار بدهید. مثلاً هشدار اتصال پس از ۲ دقیقه و Warning حافظه نزدیک ۸۰ درصد نقطه شروع است؛ با SLO واقعی تنظیم کنید. خطای حیاتی Persistence بررسی فوری می‌خواهد. اعلان را با Owner و Runbook تست کنید؛ صرف Dashboard کافی نیست.')
link('https://github.com/oliver006/redis_exporter', 'Redis Exporter upstream configuration, ACL and metrics', 'تنظیمات، ACL و Metricهای مرجع پروژه Redis Exporter')
link('https://prometheus.io/docs/prometheus/latest/configuration/configuration/', 'Official Prometheus scrape configuration', 'تنظیم Scrape رسمی Prometheus')

section('backup-restore', '21. Backup and restore', '۲۱. Backup و Restore')
p('Replication is not backup. Accidental deletion, an application bug and malicious writes can propagate to every replica. Keep dated recoverable backups outside the Redis failure domain, encrypted with restricted access and retention suitable for your RPO. A backup replica can reduce primary load, but must have a current completed sync before snapshotting.', 'Replication جای Backup نیست. حذف اشتباه، Bug برنامه و Write مخرب به تمام Replicaها منتقل می‌شود. Backup تاریخ‌دار و قابل بازیابی را خارج از Failure Domain Redis، رمزنگاری‌شده و با Access محدود و Retention متناسب با RPO نگه دارید. Backup Replica می‌تواند بار Primary را کم کند اما پیش از Snapshot باید Sync کامل و تازه داشته باشد.')
code('''Redis Primary -> Redis Replica -> Completed RDB / Consistent AOF Backup
                                         |
                                         v
                         Encrypted Remote Backup Storage
                                         |
                                         v
                            Isolated Restore Test''', 'text')
sub('Example: export a fresh RDB from the replica', 'مثال: Export یک RDB تازه از Replica')
p('On the replica, ensure the link is up and full sync is finished. The admin-authenticated redis-cli --rdb command receives an RDB through the replication protocol and exits after transfer; account for its snapshot/full-sync load. Create a root-only local backup directory and run from an operator shell with restrictive umask. Under TLS add the certificate flags shown earlier.', 'روی Replica، Link سالم و Full Sync تکمیل‌شده را تأیید کنید. redis-cli --rdb با Authentication ادمین، RDB را از Replication Protocol دریافت و پس از انتقال خارج می‌شود؛ بار Snapshot/Full Sync آن را حساب کنید. مسیر Backup محلی فقط برای root بسازید و در Shell اپراتور با umask محدود اجرا کنید. در TLS، Flag گواهی بخش قبل را اضافه کنید.')
code('''redis-cli --user monitor --askpass INFO replication
sudo install -d -o root -g root -m 700 /var/backups/redis
umask 077
backup_stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup_file="/var/backups/redis/replica-${backup_stamp}.rdb"
sudo install -o root -g root -m 600 /dev/null "$backup_file"
sudo redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass --rdb "$backup_file"
sudo chmod 600 "$backup_file"
sudo redis-check-rdb "$backup_file"
sudo sha256sum "$backup_file"''')
p('Expected: a completed transfer and a successful redis-check-rdb validation; the checksum detects later corruption but does not prove recovery completeness. Copy the artifact to your approved encrypted remote backup system and verify the uploaded checksum. This local example alone is not an off-host backup. Automate with a secret mount/service identity rather than interactive --askpass and retain backup logs without credentials.', 'خروجی مورد انتظار Transfer کامل و Validation موفق redis-check-rdb است؛ Checksum خرابی بعدی را تشخیص می‌دهد اما کامل‌بودن Recovery را اثبات نمی‌کند. Artifact را با سیستم Backup Remote رمزنگاری‌شده سازمان منتقل و Checksum مقصد را تأیید کنید. این مثال محلی به‌تنهایی Off-Host Backup نیست. برای Automation از Secret Mount/Service Identity استفاده کنید، نه --askpass تعاملی؛ Log بدون Credential نگه دارید.')
sub('Consistent AOF backups and restore acceptance', 'Backup سازگار AOF و معیار پذیرش Restore')
p('A current AOF backup must contain the manifest plus every referenced base/incremental file. A naive live copy can miss files during rewrite or capture inconsistent state. Use a coordinated backup procedure that prevents rewrite/file-set changes, or an atomic filesystem/storage snapshot with the documented Redis consistency procedure. A cleanly stopped dedicated backup replica can also provide a consistent file set; plan its resync and production availability impact.', 'Backup AOF فعلی باید Manifest و همه Base/Incremental File ارجاع‌شده را داشته باشد. کپی ساده فایل زنده هنگام Rewrite ممکن است File را جا بیندازد یا وضعیت ناسازگار بگیرد. از فرآیند هماهنگ جلوگیری از تغییر File Set/Rewrite یا Snapshot اتمیک Storage/Filesystem با روش Consistency مستند Redis استفاده کنید. Replica اختصاصی Backup که تمیز متوقف شده نیز File Set سازگار می‌دهد؛ Resync و اثر Availability را برنامه‌ریزی کنید.')
items([
('Restore to a fresh isolated host with the same approved Redis/module versions; verify backup checksum and configuration compatibility.', 'Restore روی Host تازه و ایزوله با Redis/Module نسخه تأییدشده؛ Checksum و سازگاری Config را بررسی کنید.'),
('For an RDB-only restore, load the restored dump.rdb with appendonly no initially; a pre-existing AOF must not override it. Enable AOF later using the live procedure in section 9.', 'برای Restore فقط RDB، dump.rdb بازیابی‌شده را ابتدا با appendonly no بارگذاری کنید؛ AOF قبلی نباید آن را Override کند. بعداً AOF را با فرآیند زنده بخش ۹ فعال کنید.'),
('For AOF restore, restore the complete manifest/file set under appenddirname and correct ownership; validate with the version-matched redis-check-aof tooling.', 'برای Restore AOF، Manifest/File Set کامل را زیر appenddirname با Ownership صحیح قرار دهید؛ با redis-check-aof همان نسخه بررسی کنید.'),
('Keep applications disconnected until key samples, TTLs, counts, loading logs and business invariants pass. Never run repair --fix on the only backup copy.', 'تا تأیید نمونه کلید، TTL، Count، Loading Log و Invariant کسب‌وکار، Application قطع باشد. --fix را روی تنها کپی Backup اجرا نکنید.'),
('Measure actual restore time, potential data loss, remote backup age and encryption-key availability. Preserve a verified pre-change backup for rollback.', 'زمان واقعی Restore، Loss احتمالی، سن Backup Remote و دسترسی کلید رمزنگاری را اندازه بگیرید؛ Backup قبل از تغییر برای Rollback حفظ شود.'),
])
ref('develop/tools/cli/', 'redis-cli modes including RDB export and authentication', 'Modeهای redis-cli شامل Export RDB و Authentication')
ref('commands/lastsave/', 'LASTSAVE snapshot completion timestamp', 'Timestamp تکمیل Snapshot با LASTSAVE')

section('troubleshooting', '22. Production troubleshooting runbook', '۲۲. Runbook عملیاتی Troubleshooting')
p('Run host commands locally on the affected node; data commands use the named user shown. With TLS add the verified CA/client flags to every redis-cli command. Preserve logs and current configuration before applying a repair. The outputs below are representative success/failure indicators, not guaranteed verbatim across packaging variations.', 'فرمان Host را محلی روی نود مشکل‌دار اجرا کنید؛ فرمان داده از Named User مشخص‌شده استفاده می‌کند. در TLS به تمام redis-cliها Flag مربوط به CA/Client معتبر اضافه کنید. قبل از Repair، Log و Config فعلی را حفظ کنید. خروجی‌ها Indicator موفقیت/خطای نمونه‌اند و متن دقیق بسته‌ها ممکن است تفاوت داشته باشد.')
sub('Redis service down', 'Redis Service Down')
p('Root cause: invalid directive or ACL file, unreadable files, a missing bind address, a port conflict, disk errors or the OOM killer. Diagnostic commands:', 'Root Cause: Directive یا فایل ACL نامعتبر، فایل غیرقابل خواندن، Bind Address غایب، Port Conflict، خطای دیسک یا OOM Killer. فرمان تشخیص:')
code('''systemctl status redis-server --no-pager
sudo journalctl -u redis-server -n 150 --no-pager
sudo tail -n 100 /var/log/redis/redis-server.log
sudo journalctl -k -n 150 --no-pager
sudo -u redis test -r /etc/redis/users.acl
df -h /var/lib/redis
ip -br address''')
p('Expected: a failing service shows failed/inactive and a specific log cause; after repair expect active (running), PONG and healthy persistence/replication. Resolution: correct the offending directive or ACL, restore intended ownership or the missing private IP, resolve the conflict or capacity issue, then restart in the maintenance plan. Do not repeatedly restart without reading the Redis log or run it as root to bypass permissions.', 'Expected Output: سرویس خراب failed/inactive و Log علت مشخص دارد؛ پس از اصلاح active (running)، PONG و Persistence/Replication سالم انتظار می‌رود. Resolution: Directive/ACL خراب، Ownership، IP خصوصی غایب، Conflict یا ظرفیت را اصلاح و طبق Maintenance Plan Restart کنید. بدون خواندن Redis Log Restart تکراری نکنید و برای دورزدن Permission سرویس را root اجرا نکنید.')
sub('Port 6379 not listening or remote connection refused', 'Port 6379 Not Listening یا اتصال Remote رد می‌شود')
code('''sudo ss -lntp | grep ':6379'
systemctl cat redis-server
ip -br address
sudo ufw status numbered
# From an authorized application host:
redis-cli -h 10.10.20.10 -p 6379 --user app --askpass PING''')
p('Root cause: service down, bind only to loopback, wrong config/port, missing IP, routing/ACL firewall block, or plaintext client against a TLS-only listener. Expected: LISTEN on 127.0.0.1 and 10.10.20.10 for the primary, and PONG from the permitted client. A timeout often indicates dropped traffic; refusal often means no listener or active reject. Resolution: compare ExecStart with edited paths, correct private binding, approve the exact source rule and test the proper TLS mode; never “fix” it with bind 0.0.0.0 plus a public allow rule.', 'Root Cause: سرویس قطع، Bind فقط Loopback، Config/Port اشتباه، IP غایب، Route/Firewall مسدود یا Client بدون TLS روی Listener فقط TLS. Expected Output: LISTEN روی 127.0.0.1 و 10.10.20.10 در Primary و PONG از Client مجاز. Timeout معمولاً Drop ترافیک و Refused معمولاً نبود Listener یا Reject فعال است. Resolution: ExecStart و فایل ویرایش‌شده را مقایسه، Bind خصوصی و Source Rule دقیق را اصلاح و Mode صحیح TLS را تست کنید؛ bind 0.0.0.0 همراه Allow عمومی راه‌حل نیست.')
sub('Authentication or ACL errors', 'Authentication Error و خطای ACL')
code('''NOAUTH Authentication required.
WRONGPASS invalid username-password pair or user is disabled.
NOPERM ...''', 'text')
code('''redis-cli --user admin --askpass ACL WHOAMI
redis-cli --user admin --askpass ACL GETUSER app
redis-cli --user admin --askpass ACL LOG 10
redis-cli --user admin --askpass ACL DRYRUN app GET cache:probe''')
p('Root cause: absent AUTH, wrong username/rotated secret, disabled user, denied command/key/channel or an unsaved ACL change. Expected: WHOAMI identifies admin, allowed DRYRUN returns OK, rejected activity is visible in ACL LOG. Resolution: update the client username/secret, compare effective ACL with the managed file and grant only the missing required operation. NOAUTH, WRONGPASS and NOPERM are different faults; do not set default on nopass to silence them.', 'Root Cause: AUTH غایب، Username/Secret Rotate‌شده اشتباه، User غیرفعال، Command/Key/Channel غیرمجاز یا تغییر ACL ذخیره‌نشده. Expected Output: WHOAMI برابر admin، DRYRUN مجاز برابر OK و Activity ردشده در ACL LOG دیده می‌شود. Resolution: Username/Secret Client و ACL مؤثر با فایل مدیریت‌شده را تطبیق و فقط Permission لازم غایب را اضافه کنید. NOAUTH، WRONGPASS و NOPERM علت متفاوت دارند؛ با default on nopass خطا را پنهان نکنید.')
sub('Replica disconnected or synchronization stuck', 'Replica Disconnected یا Sync متوقف')
code('''redis-cli --user monitor --askpass INFO replication
sudo journalctl -u redis-server -n 150 --no-pager
sudo tail -n 100 /var/log/redis/redis-server.log
redis-cli --user admin --askpass CONFIG GET replicaof masteruser
df -h /var/lib/redis''')
p('Root cause: routing/firewall, wrong masterauth/masteruser, missing PSYNC/REPLCONF permissions, TLS/CA mismatch, insufficient backlog, RAM or disk. Expected: master_link_status:up, master_sync_in_progress:0 and low/stable master_last_io_seconds_ago after completion; the primary should show the replica online. Resolution: read both sides’ logs, fix the specific credential/network/certificate/space fault, then wait for complete sync and compare offsets. Repeated full sync suggests insufficient backlog or ongoing outages. replica-serve-stale-data no can produce MASTERDOWN until the link recovers; do not force writes on the replica.', 'Root Cause: Route/Firewall، masterauth/masteruser اشتباه، Permission غایب PSYNC/REPLCONF، TLS/CA ناسازگار، Backlog، RAM یا دیسک ناکافی. Expected Output: master_link_status:up، master_sync_in_progress:0 و master_last_io_seconds_ago کم/پایدار پس از تکمیل؛ Primary باید Replica را online ببیند. Resolution: Log هر دو سمت را بخوانید، مشکل Credential/شبکه/گواهی/فضا را اصلاح، منتظر Sync کامل و Offsetها را مقایسه کنید. Full Sync مکرر می‌تواند Backlog ناکافی یا قطعی ادامه‌دار باشد. replica-serve-stale-data no تا Recovery ممکن است MASTERDOWN بدهد؛ Write Replica را اجباری فعال نکنید.')
sub('Memory full or OOM errors', 'Memory Full و خطای OOM')
code('''redis-cli --user monitor --askpass INFO memory
redis-cli --user monitor --askpass INFO stats
redis-cli --user admin --askpass CONFIG GET maxmemory maxmemory-policy
free -h
sudo journalctl -k -n 100 --no-pager''')
p('Root cause: dataset growth, missing TTLs, noeviction rejecting growing writes, volatile policy with no TTL candidates, fragmentation/fork headroom or an undersized replica. Expected failure: OOM command not allowed when used memory > maxmemory, or kernel OOM evidence; after repair expect stable headroom and successful required writes. Resolution: identify growth with bounded key sampling, remove only approved obsolete keys using UNLINK, add TTLs, tune a cache-only policy or add measured capacity. Never FLUSHALL as routine capacity treatment or raise maxmemory beyond host safety.', 'Root Cause: رشد Dataset، TTL غایب، رد Write افزاینده با noeviction، Policy volatile بدون Candidate دارای TTL، Fragmentation/فضای Fork یا Replica کوچک. Expected Output خطا: OOM command not allowed when used memory > maxmemory یا شاهد OOM کرنل؛ پس از اصلاح حاشیه ظرفیت پایدار و Write لازم موفق باشد. Resolution: رشد را با Sampling محدود شناسایی، فقط کلید قدیمی تأییدشده را با UNLINK حذف، TTL اضافه و Policy Cache مستقل یا ظرفیت اندازه‌گیری‌شده تنظیم کنید. FLUSHALL درمان روزمره ظرفیت نیست و maxmemory نباید از ایمنی Host فراتر رود.')
sub('Slow Redis or timeouts', 'Slow Redis و Timeout')
code('''redis-cli --user monitor --askpass SLOWLOG GET 20
redis-cli --user monitor --askpass LATENCY LATEST
redis-cli --user monitor --askpass INFO commandstats
redis-cli --user monitor --askpass INFO clients
redis-cli --user monitor --askpass INFO persistence
# From an authorized client; stop with Ctrl+C after a short sample:
redis-cli -h 10.10.20.10 --user app --askpass --latency''')
p('Root cause: large/expensive commands or scripts, hot keys, CPU saturation, swapping, fork/rewrite stalls, slow storage, network delay or connection churn. Expected: SLOWLOG may reveal high command execution microseconds, latency events reveal server pauses, while --latency samples PING round trips. An empty SLOWLOG with slow PING points toward transport/host/client causes too. Resolution: use bounded operations/SCAN instead of KEYS, smaller values, connection pools and batching/pipelining with bounded batch size; fix memory/disk/network pressure and compare p95/p99 before/after.', 'Root Cause: Command/Script بزرگ و سنگین، Hot Key، CPU Saturation، Swap، وقفه Fork/Rewrite، Storage کند، شبکه یا Connection Churn. Expected Output: SLOWLOG اجرای Microsecond بالا، Latency Event وقفه سرور و --latency رفت‌وبرگشت PING را نشان می‌دهد. SLOWLOG خالی همراه PING کند، علت Transport/Host/Client را نیز مطرح می‌کند. Resolution: عملیات محدود/SCAN به‌جای KEYS، Value کوچک‌تر، Pool و Batching/Pipelining با اندازه محدود؛ فشار Memory/Disk/Network را رفع و p95/p99 قبل/بعد را مقایسه کنید.')
sub('Persistence failure or MISCONF', 'خطای Persistence و MISCONF')
code('''redis-cli --user monitor --askpass INFO persistence
df -h /var/lib/redis
df -i /var/lib/redis
sudo -u redis test -w /var/lib/redis
sudo tail -n 100 /var/log/redis/redis-server.log''')
p('Root cause: full disk/inodes, read-only mount, permissions, I/O error or failed fork. Expected failure: rdb_last_bgsave_status:err or aof_last_write_status:err; success returns ok and a newer save time. Resolution: fix storage/permissions and confirm a completed snapshot/rewrite. Do not simply disable stop-writes-on-bgsave-error or repair the only AOF copy; preserve artifacts and verify restoration first.', 'Root Cause: دیسک/Inode پر، Mount فقط‌خواندنی، Permission، I/O Error یا Fork ناموفق. Expected Output خطا: rdb_last_bgsave_status:err یا aof_last_write_status:err؛ موفقیت ok و زمان Save جدیدتر دارد. Resolution: Storage/Permission را اصلاح و Snapshot/Rewrite تکمیل‌شده را تأیید کنید. stop-writes-on-bgsave-error را صرفاً غیرفعال و تنها کپی AOF را Repair نکنید؛ Artifact را حفظ و Restore را ابتدا بررسی کنید.')

section('production-checklist', '23. Production acceptance checklist', '۲۳. Production Checklist و معیار پذیرش')
items([
('[ ] Redis is on a private segmented network.', '[ ] Redis روی Private Network تفکیک‌شده است.'),
('[ ] TCP/6379 is closed to the internet and unauthorized subnets.', '[ ] Port 6379 روی Internet و Subnet غیرمجاز بسته است.'),
('[ ] Named ACL users are deployed; default is disabled and denied operations are tested.', '[ ] ACL با Named User مستقر، default غیرفعال و عملیات Denied تست شده است.'),
('[ ] Strong authentication, secret storage and rotation are configured.', '[ ] Authentication قوی، نگهداری Secret و Rotation تنظیم شده است.'),
('[ ] TLS and certificate verification are tested wherever policy requires encryption.', '[ ] در نیاز به رمزنگاری، TLS و بررسی Certificate تست شده است.'),
('[ ] RDB/AOF policies and persistence error handling match the required RPO.', '[ ] Persistence و مدیریت خطای RDB/AOF با RPO تطبیق دارد.'),
('[ ] maxmemory, fork/buffer headroom and replica capacity are measured.', '[ ] maxmemory، ظرفیت Fork/Buffer و ظرفیت Replica بررسی شده است.'),
('[ ] Eviction policy is explicit; critical jobs/sessions are isolated from evicting cache.', '[ ] Eviction Policy مشخص و Job/Session حیاتی از Cache قابل Evict جداست.'),
('[ ] Replica is active and initial synchronization is complete.', '[ ] Replica فعال و Sync اولیه کامل است.'),
('[ ] Replication status, offsets and a write/read test pass.', '[ ] Replication Status، Offset و تست Write/Read بررسی شده است.'),
('[ ] Dated remote backup exists and an isolated restore has passed.', '[ ] Backup Remote تاریخ‌دار وجود دارد و Restore ایزوله موفق است.'),
('[ ] Monitoring covers both Redis nodes and the hosts.', '[ ] Monitoring هر دو نود Redis و Hostها فعال است.'),
('[ ] Alerts reach an owner and have a tested operational runbook.', '[ ] Alerting به Owner می‌رسد و Runbook تست‌شده دارد.'),
('[ ] Firewall rules and service-account file permissions are verified.', '[ ] Firewall و Permission حساب سرویس بررسی شده است.'),
('[ ] Configuration and ACL backups are secured before changes.', '[ ] Configuration Backup و ACL Backup امن گرفته شده است.'),
('[ ] Latest stable patch, Ubuntu LTS compatibility and client/module support are rechecked.', '[ ] آخرین Stable Patch، سازگاری Ubuntu LTS و Client/Module دوباره بررسی شده است.'),
('[ ] HA needs are satisfied by tested Sentinel/Cluster; replication alone is not labelled HA.', '[ ] نیاز HA با Sentinel/Cluster تست‌شده پوشش دارد؛ Replication تنها، HA معرفی نشده است.'),
('[ ] Maintenance, failover, rollback and recovery procedures are recorded.', '[ ] فرآیند Maintenance، Failover، Rollback و Recovery ثبت شده است.'),
])
section('conclusion', 'Conclusion: release acceptance', 'جمع‌بندی؛ پذیرش استقرار')
p('Release acceptance is evidence-based: record the installed version, approved listeners, authenticated and denied access, completed sync, persistence health, a verified remote backup and recovery time. A syntax-correct configuration without these checks is not enough to declare a production deployment complete.', 'پذیرش استقرار به شاهد نیاز دارد: نسخه نصب‌شده، Listener مجاز، Access احراز هویت‌شده و Denied، Sync کامل، سلامت Persistence، Backup Remote تأییدشده و زمان Recovery را ثبت کنید. Config با Syntax درست بدون این بررسی‌ها برای اعلام تکمیل Production کافی نیست.')
p('The deployment starts with a private primary and replica, named ACL accounts, verified persistence and bounded memory. Sentinel adds discovery and automatic failover; Cluster adds sharding. Keep cache eviction separate from critical sessions/jobs, treat replication as asynchronous, and prove recovery from independent backups. Recheck the current stable Redis patch and supported Ubuntu LTS on every rollout.', 'استقرار با Primary و Replica خصوصی، حساب ACL مشخص، Persistence بررسی‌شده و Memory محدود شروع می‌شود. Sentinel، Discovery و Failover خودکار و Cluster، Sharding اضافه می‌کند. Eviction مربوط به Cache را از Session/Job حیاتی جدا، Replication را ناهمزمان و Recovery را از Backup مستقل اثبات کنید. در هر Rollout، Patch Stable فعلی Redis و Ubuntu LTS پشتیبانی‌شده را دوباره بررسی کنید.')

FAQ = [
('Does apt install redis-server install the newest Redis?', 'Not necessarily. The distribution repository may lag upstream. Use the official Redis repository, inspect apt-cache policy and verify redis-server --version.', 'آیا apt install redis-server آخرین Redis را نصب می‌کند؟', 'الزاماً خیر؛ مخزن Distribution ممکن است قدیمی‌تر باشد. مخزن رسمی Redis، apt-cache policy و redis-server --version را بررسی کنید.'),
('Is requirepass removed?', 'No. It remains a compatibility setting for the default ACL user. Named ACL users with least privilege are preferred for this production design.', 'آیا requirepass حذف شده است؟', 'خیر؛ برای سازگاری User پیش‌فرض ACL باقی است. این طراحی Production از Named User با Least Privilege استفاده می‌کند.'),
('Does replication provide automatic failover?', 'Standalone replication does not. Use Sentinel or Redis Cluster with an appropriate topology and compatible client.', 'آیا Replication خودکار Failover انجام می‌دهد؟', 'Replication مستقل خیر. از Sentinel یا Redis Cluster با Topology مناسب و Client سازگار استفاده کنید.'),
('Can a replica serve stale reads?', 'Yes. Replication is asynchronous. Read critical freshness-sensitive data from the primary and monitor offsets and link health.', 'آیا Replica داده قدیمی برمی‌گرداند؟', 'بله؛ Replication ناهمزمان است. داده حساس به تازگی را از Primary بخوانید و Offset و سلامت Link را پایش کنید.'),
('Does WAIT guarantee that a write is on disk?', 'No. WAIT checks replication acknowledgments for earlier writes on the same client connection. WAITAOF addresses configured AOF flush acknowledgments.', 'آیا WAIT ثبت Write روی دیسک را تضمین می‌کند؟', 'خیر؛ WAIT تأیید Replication برای Write قبلی همان Connection را بررسی می‌کند. WAITAOF به تأیید Flush تنظیم‌شده AOF مربوط است.'),
('Which policy is a good starting point for a cache?', 'allkeys-lru with an explicit maxmemory and application TTLs. Separate critical session, queue and lock workloads from an evicting cache.', 'کدام Policy برای شروع Cache مناسب است؟', 'allkeys-lru با maxmemory صریح و TTL برنامه. Session، Queue و Lock حیاتی را از Cache دارای Eviction جدا کنید.'),
('Is a replica a backup?', 'No. Deletions and bad writes replicate too. Keep dated remote backups and regularly prove recovery on an isolated host.', 'آیا Replica همان Backup است؟', 'خیر؛ Delete و Write خراب نیز Replicate می‌شوند. Backup Remote تاریخ‌دار و تست منظم Recovery روی Host ایزوله لازم است.'),
('How many Sentinels are required for a resilient deployment?', 'At least three in independent failure domains. With three Sentinels, quorum 2 is typical, and failover also requires an election majority.', 'برای Deployment مقاوم چند Sentinel لازم است؟', 'حداقل سه در Failure Domain مستقل. با سه Sentinel، Quorum معمولاً 2 است و Failover به Majority انتخاب نیز نیاز دارد.'),
]
section('faq', 'Frequently asked questions', 'پرسش‌های متداول Redis در Production')
for q, a, fq, fa in FAQ:
    sub(q, fq)
    p(a, fa)

section('references-downloads', 'Official references and deployment templates', 'منابع رسمی و Templateهای استقرار')
p('The article cites official Redis documentation beside the relevant sections. Stable patch verified against the official repository on 7 October 2026; recheck versions before every release. Downloadable fragments contain no real secrets. Copy them into a reviewed staging deployment, substitute the mandatory secret markers, preserve packaged module paths and validate the actual target service before production.', 'در بخش‌های مرتبط به مستندات رسمی Redis لینک داده شده است. Stable Patch با Repository رسمی در ۷ اکتبر ۲۰۲۶ بررسی شده؛ پیش از هر Release نسخه را دوباره بررسی کنید. Fragmentهای Download هیچ Secret واقعی ندارند. آن‌ها را در Staging بررسی‌شده به‌کار ببرید، Marker اجباری Secret را جایگزین، مسیر Module بسته را حفظ و سرویس مقصد واقعی را پیش از Production تأیید کنید.')
for url, en, fa in [
('https://redis.io/docs/latest/commands/redis-8-10-commands/', 'Redis 8.10 command reference', 'Command Reference نسخه Redis 8.10'),
('https://redis.io/docs/latest/operate/oss_and_stack/stack-with-enterprise/release-notes/redisce/redisos-8.10-release-notes/', 'Redis Open Source 8.10 release notes', 'Release Notes مربوط به Redis Open Source 8.10'),
('https://redis.io/docs/latest/operate/oss_and_stack/management/admin/', 'Redis operating system and administration guidance', 'راهنمای رسمی سیستم‌عامل و Administration'),
]: link(url, en, fa)
for filename, label_en, label_fa in [
('primary-baseline.conf','Primary baseline fragment','Fragment مربوط به Primary'),
('replica-baseline.conf','Replica baseline fragment: secret substitution required','Fragment مربوط به Replica؛ جایگزینی Secret لازم است'),
('bootstrap-acl.sh','New-node ACL bootstrap script','Script ایجاد ACL برای نود جدید'),
('tls-overlay.conf','TLS listener overlay','Overlay مربوط به TLS Listener'),
('cache-memory-overlay.conf','Cache-only memory profile','پروفایل Memory فقط Cache'),
('sentinel-monitor-template.conf','Sentinel monitoring template; not a complete Sentinel deployment','Template مانیتور Sentinel؛ استقرار کامل Sentinel نیست'),
('prometheus-scrape.yml','Prometheus scrape fragment','Fragment مربوط به Scrape در Prometheus'),
]: link(f'/downloads/{SLUG}/{filename}', label_en, label_fa)
parts.append('</section>')

localizations = {lang: {'title': TITLE[lang], 'meta_title': TITLE[lang], 'description': DESC[lang], 'keywords': KEYWORDS, 'faq': [[q, a] if lang == 'en' else [fq, fa] for q, a, fq, fa in FAQ]} for lang in ['en', 'fa']}
banner = '/assets/img/articles/banners/redis-production-banner.png'
url = 'https://meetaj.ir/articles/' + SLUG
schema = {'@context': 'https://schema.org', '@type': 'Article', 'headline': TITLE['en'], 'description': DESC['en'], 'inLanguage': 'en', 'datePublished': '2026-10-07T00:00:00+03:30', 'dateModified': '2026-10-07', 'image': banner, 'author': {'@type': 'Person', 'name': 'AmirHossein Jalalian'}}
nav = ''.join('<li class="article-nav-item">' + dual('a', en, fa, f'href="#{id}"') + '</li>' for id, en, fa in toc)
html = f'''<!doctype html>
<html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{escape(TITLE['en'])}</title>
<meta name="article:content-language" content="en">
<meta name="description" content="{escape(DESC['en'], quote=True)}">
<meta name="keywords" content="{', '.join(KEYWORDS)}">
<meta name="robots" content="index, follow">
<meta property="og:title" content="{escape(TITLE['en'], quote=True)}">
<meta property="og:description" content="{escape(DESC['en'], quote=True)}">
<meta property="og:type" content="article"><meta property="og:image" content="{banner}">
<meta property="og:url" content="{url}"><link rel="canonical" href="{url}">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">{json.dumps(schema, ensure_ascii=False)}</script>
<script id="article-localizations" type="application/json">{json.dumps(localizations, ensure_ascii=False)}</script>
</head><body class="article-page theme-linux">
<header><nav><ul>{nav}</ul></nav></header><main>
<section class="article-hero article-header hero">
{dual('span', 'Linux', 'لینوکس', 'class="article-category"')}
{dual('h1', TITLE['en'], TITLE['fa'], 'class="article-title hero-title"')}
{dual('p', DESC['en'], DESC['fa'], 'class="article-excerpt hero-subtitle"')}
<img class="article-hero-thumbnail" src="{banner}" width="1000" height="1000" alt="Redis installation, configuration and replication production guide">
</section><article class="article-body" lang="en" dir="ltr">
{chr(10).join(parts)}
</article></main></body></html>
'''
SOURCE.mkdir(parents=True, exist_ok=True)
(ROOT / 'resources/legacy/articles' / f'{SLUG}.html').write_text(html, encoding='utf-8')
for locale, content in md.items():
    (SOURCE / f'article.{locale}.md').write_text('\n'.join(content), encoding='utf-8')
(SOURCE / 'metadata.json').write_text(json.dumps({'slug': SLUG, 'reviewed_at': '2026-10-07', 'redis_version': '8.10.2', 'example_os': 'Ubuntu Server 24.04 LTS', 'latest_ubuntu_lts': '26.04', 'localizations': localizations}, ensure_ascii=False, indent=2), encoding='utf-8')
downloads = ROOT / 'public/downloads' / SLUG
downloads.mkdir(parents=True, exist_ok=True)
for filename, value in configs.items():
    (SOURCE / filename).write_text(value, encoding='utf-8')
    (downloads / filename).write_text(value, encoding='utf-8')
for folder, filenames in {
    'banners': ['redis-production-banner.png'],
    'content': ['redis-production-architecture.png', 'redis-replication-architecture.png', 'redis-sentinel-high-availability.png', 'redis-monitoring-architecture.png'],
}.items():
    for filename in filenames:
        destination = ROOT / 'public/assets/img/articles' / folder / filename
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ROOT / 'resources/assets/img/articles' / folder / filename, destination)
print(f'Built {SLUG}: {len(toc)} sections, bilingual Markdown/CMS HTML, {len(configs)} deployment templates')
