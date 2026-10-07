"""Generate the reviewed MongoDB runbook for the existing bilingual CMS importer."""
from pathlib import Path
from html import escape
import json

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'mongodb-installation-configuration-production-deployment'
PACKAGE = ROOT / 'resources/content/articles' / SLUG
TITLE = {'en': 'MongoDB Installation, Configuration & Production Deployment Guide', 'fa': 'آموزش نصب، راه‌اندازی و پیکربندی MongoDB در محیط Production'}
DESC = {'en': 'Deploy MongoDB Community on Linux with official repositories, authentication, TLS, replica sets, backups, monitoring and production troubleshooting.', 'fa': 'راهنمای فنی استقرار MongoDB Community روی Linux با مخزن رسمی، احراز هویت، TLS، Replica Set، Backup، مانیتورینگ و عیب‌یابی Production.'}
KEYWORDS = ['MongoDB Community', 'MongoDB Installation', 'MongoDB Production', 'Ubuntu', 'Debian 12', 'RHEL', 'Replica Set', 'MongoDB TLS', 'MongoDB Backup', 'DevOps']
parts, toc, markdown = [], [], {'en': [], 'fa': []}

def dual(tag, en, fa, attrs=''):
    return f'<{tag} {attrs} data-en="{escape(en, quote=True)}" data-fa="{escape(fa, quote=True)}">{escape(en)}</{tag}>'

def p(en, fa):
    parts.append(dual('p', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: markdown[lang].append(value + '\n')

def section(id, en, fa):
    if toc: parts.append('</section>')
    toc.append((id, en, fa))
    parts.append(f'<section id="{id}">' + dual('h2', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: markdown[lang].append('## ' + value + '\n')

def sub(en, fa):
    parts.append(dual('h3', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: markdown[lang].append('### ' + value + '\n')

def code(value, lang='bash'):
    value = value.strip()
    parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value)}</code></pre>')
    for text in markdown.values(): text.append(f'```{lang}\n{value}\n```\n')

def table(headers, rows):
    parts.append('<div class="table-responsive"><table><thead><tr>' + ''.join(dual('th', *h) for h in headers) + '</tr></thead><tbody>')
    for row in rows: parts.append('<tr>' + ''.join(dual('td', *cell) for cell in row) + '</tr>')
    parts.append('</tbody></table></div>')
    for lang, i in [('en', 0), ('fa', 1)]:
        markdown[lang].append('| ' + ' | '.join(h[i] for h in headers) + ' |\n| ' + ' | '.join('---' for h in headers) + ' |\n' + '\n'.join('| ' + ' | '.join(c[i] for c in row) + ' |' for row in rows) + '\n')

def link(url, en, fa):
    parts.append('<p>' + dual('a', en, fa, f'href="{escape(url, quote=True)}" rel="noopener"') + '</p>')
    for lang, value in [('en', en), ('fa', fa)]: markdown[lang].append(f'[{value}]({url})\n')

def figure(name, en, fa):
    src = '/assets/img/articles/content/' + name
    parts.append(f'<figure><img src="{src}" width="1920" height="1080" loading="lazy" decoding="async" alt="{escape(en, quote=True)}" data-en-alt="{escape(en, quote=True)}" data-fa-alt="{escape(fa, quote=True)}">' + dual('figcaption', en, fa) + '</figure>')
    for lang, value in [('en', en), ('fa', fa)]: markdown[lang].append(f'![{value}]({src})\n')

p('A deployment runbook for SysAdmins, DevOps, Backend and Infrastructure Engineers. Reviewed on 7 October 2026. The official current stable branch is 9.0; its release notes list 9.0.2 as released and 9.0.3 as upcoming. Install the latest signed patch actually available in your selected official repository; an upcoming release is not an installation target.', 'راهنمای عملیاتی برای SysAdmin، DevOps، Backend و Infrastructure Engineer؛ بررسی منابع در ۷ اکتبر ۲۰۲۶. شاخه Stable رسمی فعلی 9.0 است؛ Release Notes نسخه 9.0.2 را منتشرشده و 9.0.3 را Upcoming معرفی می‌کند. جدیدترین Patch امضاشده موجود در مخزن رسمی انتخابی را نصب کنید؛ نسخه Upcoming هدف نصب نیست.')
link('https://www.mongodb.com/docs/manual/release-notes/', 'Official stable release index', 'فهرست رسمی نسخه‌های Stable')
link('https://www.mongodb.com/docs/manual/release-notes/9.0/', 'MongoDB 9.0 patch release notes', 'Release Notes و Patchهای MongoDB 9.0')
table([('Target platform', 'سیستم‌عامل مقصد'), ('Community branch in this guide', 'شاخه Community در این راهنما'), ('Package defaults', 'پیش‌فرض بسته')], [
    [('Ubuntu 22.04 / 24.04 x86_64', 'Ubuntu 22.04 / 24.04 x86_64'), ('9.0', '9.0'), ('mongodb; /var/lib/mongodb', 'mongodb؛ /var/lib/mongodb')],
    [('Debian 12 x86_64', 'Debian 12 x86_64'), ('8.0: 9.x is not supported on Debian 12', '8.0؛ شاخه 9.x روی Debian 12 پشتیبانی نمی‌شود'), ('mongodb; /var/lib/mongodb', 'mongodb؛ /var/lib/mongodb')],
    [('RHEL / Rocky / AlmaLinux 8 or 9 x86_64', 'RHEL / Rocky / AlmaLinux 8 یا 9 x86_64'), ('9.0', '9.0'), ('mongod; /var/lib/mongo', 'mongod؛ /var/lib/mongo')],
])
link('https://www.mongodb.com/docs/community-platform-support/', 'Official Community platform support matrix', 'ماتریس رسمی پشتیبانی پلتفرم‌های Community')
p('Use a fresh supported server, sudo privileges and Bash. Bash blocks run on Linux; JavaScript blocks run inside mongosh. Values in angle brackets are placeholders, never literal credentials: <HOSTNAME>, <MONGODB-IP>, <ADMIN-USER>, <APP-USER>, <STRONG-PASSWORD>, <REPLICA-NAME>. The concrete names mongoAdmin, appuser, appdb and rs0 below are replaceable examples. All sample IPs are private network addresses and must match your own network.', 'از سرور تازه و پشتیبانی‌شده، دسترسی sudo و Bash استفاده کنید. بلوک Bash روی Linux و بلوک JavaScript داخل mongosh اجرا می‌شود. مقادیر داخل <> جایگزین‌شونده‌اند و Credential واقعی نیستند: <HOSTNAME>، <MONGODB-IP>، <ADMIN-USER>، <APP-USER>، <STRONG-PASSWORD>، <REPLICA-NAME>. نام‌های mongoAdmin، appuser، appdb و rs0 مثال قابل جایگزینی هستند. IPهای نمونه خصوصی‌اند و باید با شبکه شما تطبیق داده شوند.')
p('Choose one path: sections 5–14 build and secure a standalone instance; sections 15–16 provision a fresh three-node replica set. Do not blindly apply the fresh-cluster procedure to existing data. A production design also needs measured capacity, recovery objectives, change control and a successful restore drill.', 'یک مسیر انتخاب کنید: بخش‌های ۵ تا ۱۴ برای نصب و ایمن‌سازی Standalone و بخش‌های ۱۵ و ۱۶ برای Replica Set تازه با سه Node هستند. روی دیتابیس موجود مسیر Fresh Cluster را بدون برنامه اجرا نکنید. طراحی Production به ظرفیت‌سنجی، هدف بازیابی، کنترل تغییر و آزمون موفق Restore نیز نیاز دارد.')

section('introduction', '1. What Is MongoDB?', '۱. MongoDB چیست؟')
p('MongoDB is a document-oriented NoSQL database. A database groups collections; each collection stores documents encoded as BSON, a binary format with richer types than JSON. A replica set maintains copies for availability; sharding partitions data across shards for horizontal scale. This guide deploys a replica set, not a sharded cluster.', 'MongoDB یک Document Database از خانواده NoSQL است. Database مجموعه‌ای از Collectionها و هر Collection شامل Documentهای BSON است؛ BSON فرمت باینری با Typeهای گسترده‌تر از JSON است. Replica Set نسخه‌های داده را برای دسترس‌پذیری نگه می‌دارد و Sharding داده را برای مقیاس افقی میان Shardها تقسیم می‌کند. این راهنما Replica Set مستقر می‌کند، نه Sharded Cluster.')
p('SQL systems commonly use tables, rows and joins; MongoDB encourages document models shaped around application access patterns. Flexible documents still need validation and indexes. MongoDB supports transactions, but multi-document transactions do not replace sound modeling and are not available on a standalone deployment.', 'در SQL معمولاً Table، Row و Join محور طراحی‌اند؛ در MongoDB مدل Document بر اساس الگوی دسترسی برنامه طراحی می‌شود. انعطاف Document نیاز به Validation و Index را حذف نمی‌کند. MongoDB از Transaction پشتیبانی می‌کند، اما Transaction چندسندی جای مدل‌سازی درست را نمی‌گیرد و در Standalone در دسترس نیست.')
link('https://www.mongodb.com/docs/manual/core/databases-and-collections/', 'Databases and collections', 'Database و Collection در مستندات رسمی')

section('architecture', '2. Architecture Overview', '۲. نمای معماری')
figure('mongodb-architecture.png', 'MongoDB application, driver, TCP 27017 authentication and BSON document architecture', 'معماری اتصال برنامه و Driver با احراز هویت روی TCP 27017 و ساختار Documentهای BSON در MongoDB')
code(r'''Application
     |
MongoDB Client / Driver
     |
MongoDB Server :27017
     |
Database
 +-- Collections
     +-- Documents

Production:
Application
     |
Replica-aware MongoDB Driver / Connection Pool
     |
Replica Set: rs0
 +-----------+-----------+-----------+
 | Primary   | Secondary | Secondary |
 +-----------+-----------+-----------+''', 'text')
p('The driver discovers all members and routes operations according to read preference and topology. A generic HTTP or round-robin load balancer is not needed in front of a replica set and can hide its topology. Writes normally go to the primary; secondaries replicate and can serve explicitly selected reads, with consistency tradeoffs.', 'Driver همه Memberها را کشف می‌کند و عملیات را با توجه به Read Preference و Topology هدایت می‌کند. Replica Set به Load Balancer عمومی HTTP یا Round-Robin نیاز ندارد و چنین واسطه‌ای می‌تواند Topology را پنهان کند. Write معمولاً به Primary می‌رود؛ Secondaryها Replication انجام می‌دهند و با انتخاب صریح می‌توانند Read را با ملاحظات Consistency پاسخ دهند.')
link('https://www.mongodb.com/docs/manual/replication/', 'MongoDB replication architecture', 'معماری رسمی Replication')

section('prerequisites', '3. Server Requirements', '۳. پیش‌نیاز سرور')
table([('Resource', 'منبع'), ('Planning baseline', 'مبنای برنامه‌ریزی')], [
    [('CPU', 'CPU'), ('64-bit supported microarchitecture; AVX on x86_64. A starting budget of 4–8 vCPU per node is a sizing example, not an official minimum.', 'معماری ۶۴ بیتی پشتیبانی‌شده و AVX روی x86_64. بودجه اولیه ۴ تا ۸ vCPU برای هر Node مثال ظرفیت‌سنجی است، نه حداقل رسمی.')],
    [('RAM', 'RAM'), ('Start capacity testing around 16–32 GiB per node for a modest dedicated service; size for the working set, indexes and filesystem cache.', 'برای سرویس اختصاصی متوسط، ظرفیت‌سنجی را حدود ۱۶ تا ۳۲ GiB در هر Node شروع کنید؛ Working Set، Indexها و File Cache تعیین‌کننده‌اند.')],
    [('Disk and filesystem', 'Disk و File System'), ('Enterprise SSD/NVMe; XFS is recommended for WiredTiger. Reserve space for indexes, journal, oplog, growth and restore staging.', 'SSD/NVMe سازمانی؛ XFS برای WiredTiger توصیه می‌شود. فضای Index، Journal، Oplog، رشد و Restore را لحاظ کنید.')],
    [('Network and DNS', 'شبکه و DNS'), ('Private low-latency network; stable resolvable node names; all advertised members reachable by drivers.', 'شبکه خصوصی کم‌تأخیر، نام Node پایدار و Resolveشدنی، دسترسی Driver به همه Memberهای معرفی‌شده.')],
    [('Time', 'زمان'), ('Use chrony or the distribution time service; verify actual synchronization, not only service enablement.', 'از chrony یا سرویس زمان توزیع استفاده کنید؛ Synchronization واقعی را بررسی کنید، نه صرفاً Enabled بودن سرویس.')],
    [('Swap', 'Swap'), ('Avoid sustained swapping. Evaluate swappiness and OOM behavior with the platform and workload; do not switch swap off on a pressured live host.', 'از Swap مستمر جلوگیری کنید. swappiness و رفتار OOM را با پلتفرم و Workload ارزیابی کنید؛ Swap سرور زنده تحت فشار را ناگهانی خاموش نکنید.')],
])
p('Keep data on reliable dedicated block storage: /var/lib/mongodb on Ubuntu/Debian, /var/lib/mongo on RPM installations. Logs normally use /var/log/mongodb. A separate mount at the existing dbPath avoids accidental path changes. Mount it before initial startup and add a systemd RequiresMountsFor dependency so a missing volume cannot silently create a new database on the root disk. Never format a disk containing data.', 'Data را روی Block Storage اختصاصی و قابل اعتماد قرار دهید: /var/lib/mongodb در Ubuntu/Debian و /var/lib/mongo در نصب RPM. مسیر معمول Log برابر /var/log/mongodb است. Mount جداگانه در همان dbPath از تغییر ناخواسته مسیر جلوگیری می‌کند. پیش از اولین Start آن را Mount و وابستگی RequiresMountsFor در systemd تعریف کنید تا فقدان Volume باعث ساخت دیتابیس جدید روی Root Disk نشود. دیسک دارای داده را Format نکنید.')
link('https://www.mongodb.com/docs/manual/administration/production-notes/', 'Official production hardware, storage and platform notes', 'نکات رسمی سخت‌افزار، Storage و پلتفرم Production')
sub('Service limits and mount dependency', 'محدودیت سرویس و وابستگی Mount')
code(r'''sudo systemctl edit mongod
# Add the following drop-in; use /var/lib/mongo on RPM systems.''')
code(r'''[Unit]
RequiresMountsFor=/var/lib/mongodb

[Service]
LimitNOFILE=64000
LimitNPROC=64000''', 'ini')
code(r'''sudo systemctl daemon-reload
sudo systemctl show mongod -p LimitNOFILE -p LimitNPROC
ulimit -n''')
p('A shell ulimit affects that shell, not an already running systemd service. Confirm the effective limits after restart. For 8.0+ x86_64/ARM64, follow the current TCMalloc guidance: enable THP with the documented defrag settings; the old blanket advice to disable THP is version-dependent. Check the official kernel compatibility notes before changing kernels.', 'ulimit در Shell فقط همان Shell را تغییر می‌دهد و سرویس systemd در حال اجرا را تغییر نمی‌دهد. پس از Restart محدودیت مؤثر را بررسی کنید. در نسخه 8.0 به بعد روی x86_64/ARM64 راهنمای جدید TCMalloc را دنبال کنید: THP را با تنظیمات defrag مستند فعال کنید؛ توصیه قدیمی خاموش‌کردن THP وابسته به نسخه است. پیش از تغییر Kernel نکات رسمی سازگاری را بررسی کنید.')
link('https://www.mongodb.com/docs/manual/reference/ulimit/', 'Official UNIX resource limits', 'محدودیت منابع UNIX')
link('https://www.mongodb.com/docs/manual/administration/tcmalloc-performance/', 'TCMalloc and THP settings for MongoDB 8.0 and later', 'تنظیم TCMalloc و THP برای MongoDB 8.0 و جدیدتر')

section('pre-installation', '4. Pre-Installation Checks', '۴. بررسی پیش از نصب')
code(r'''hostnamectl
cat /etc/os-release
uname -r
free -h
df -h
lsblk
ip addr
timedatectl
lscpu
findmnt -T /var/lib
swapon --show
getent hosts mongo01 mongo02 mongo03''')
p('Check the OS ID/version and architecture against the matrix; verify CPU features and the distribution kernel. Inspect available RAM and swap activity, disk capacity, filesystem and persistent mounts. Confirm the private interface and route, unique hostnames, consistent DNS resolution on clients and nodes, and synchronized clocks. These checks establish capacity and reachability; they do not prove database health.', 'ID و Version سیستم‌عامل و معماری را با ماتریس تطبیق دهید؛ ویژگی CPU و Kernel توزیع را بررسی کنید. RAM آزاد، فعالیت Swap، ظرفیت Disk، File System و Mount پایدار را کنترل کنید. Interface و Route خصوصی، Hostname یکتا، DNS یکسان در Client و Node و ساعت هماهنگ را تأیید کنید. این بررسی‌ها ظرفیت و ارتباط را مشخص می‌کنند، نه سلامت دیتابیس را.')

section('ubuntu-install', '5. Install MongoDB on Ubuntu 22.04 / 24.04', '۵. نصب MongoDB روی Ubuntu 22.04 / 24.04')
p('This fresh-install path uses Community 9.0 and the official repo.mongodb.org repository. Inspect existing MongoDB packages and sources first; do not mix Ubuntu mongodb, Community mongodb-org and Enterprise packages. If GPG verification or repository access fails, fix the cause rather than disabling signature checks.', 'این مسیر نصب تازه از Community 9.0 و مخزن رسمی repo.mongodb.org استفاده می‌کند. ابتدا بسته‌ها و Sourceهای MongoDB موجود را بررسی کنید؛ بسته mongodb توزیع، mongodb-org و Enterprise را مخلوط نکنید. در خطای GPG یا دسترسی مخزن، علت را رفع کنید و بررسی امضا را خاموش نکنید.')
code(r'''sudo apt update
sudo apt install -y gnupg curl ca-certificates
curl -fsSL https://pgp.mongodb.com/server-9.asc -o /tmp/mongodb-server-9.asc
sudo gpg --batch --yes --dearmor \
  -o /usr/share/keyrings/mongodb-server-9.gpg /tmp/mongodb-server-9.asc
sudo chmod 644 /usr/share/keyrings/mongodb-server-9.gpg''')
sub('Ubuntu 24.04: select Noble', 'Ubuntu 24.04: انتخاب Noble')
code(r'''echo 'deb [arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-9.gpg] https://repo.mongodb.org/apt/ubuntu noble/mongodb-org/9.0 multiverse' \
  | sudo tee /etc/apt/sources.list.d/mongodb-org-9.0.list''')
sub('Ubuntu 22.04: select Jammy instead', 'Ubuntu 22.04: انتخاب Jammy به جای Noble')
code(r'''echo 'deb [arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-9.gpg] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/9.0 multiverse' \
  | sudo tee /etc/apt/sources.list.d/mongodb-org-9.0.list''')
p('Run only the repository block matching your OS. The local keyring filename is chosen consistently here as server-9.gpg; both signed-by and the imported file must agree.', 'فقط بلوک Repository متناظر با سیستم‌عامل را اجرا کنید. نام Keyring محلی در این راهنما server-9.gpg انتخاب شده است؛ signed-by و فایل Importشده باید دقیقاً یکسان باشند.')
code(r'''sudo apt update
apt-cache policy mongodb-org mongodb-org-server
sudo apt install -y mongodb-org
sudo systemctl enable --now mongod
sudo systemctl status mongod --no-pager
mongod --version
mongosh --version
mongosh --host 127.0.0.1 --port 27017''')
link('https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=ubuntu&linux-method=pkg', 'Official Community Ubuntu installation', 'راهنمای رسمی نصب Community روی Ubuntu')

section('debian-install', '6. Install MongoDB on Debian 12', '۶. نصب MongoDB روی Debian 12')
p('Debian 12 Bookworm uses the supported Community 8.0 branch. Do not point Bookworm at a Trixie/9.0 repository: 9.x lists Debian 13, not Debian 12. An OS upgrade and a database upgrade are separate controlled changes. This installs the latest available 8.0 patch, not the globally newest major version.', 'Debian 12 Bookworm از شاخه پشتیبانی‌شده Community 8.0 استفاده می‌کند. Bookworm را به مخزن Trixie/9.0 وصل نکنید؛ شاخه 9.x فقط Debian 13 را فهرست کرده است. ارتقای OS و دیتابیس دو تغییر کنترل‌شده جدا هستند. این فرمان جدیدترین Patch موجود 8.0 را نصب می‌کند، نه جدیدترین Major در همه پلتفرم‌ها.')
code(r'''sudo apt update
sudo apt install -y gnupg curl ca-certificates
curl -fsSL https://pgp.mongodb.com/server-8.0.asc -o /tmp/mongodb-server-8.0.asc
sudo gpg --batch --yes --dearmor \
  -o /usr/share/keyrings/mongodb-server-8.0.gpg /tmp/mongodb-server-8.0.asc
sudo chmod 644 /usr/share/keyrings/mongodb-server-8.0.gpg
echo 'deb [arch=amd64 signed-by=/usr/share/keyrings/mongodb-server-8.0.gpg] https://repo.mongodb.org/apt/debian bookworm/mongodb-org/8.0 main' \
  | sudo tee /etc/apt/sources.list.d/mongodb-org-8.0.list
sudo apt update
apt-cache policy mongodb-org mongodb-org-server
sudo apt install -y mongodb-org
sudo systemctl enable --now mongod
sudo systemctl status mongod --no-pager
mongod --version
mongosh''')
link('https://www.mongodb.com/docs/v8.0/tutorial/install-mongodb-on-debian/', 'Official Debian 12 installation for Community 8.0', 'راهنمای رسمی Debian 12 برای Community 8.0')

section('rhel-install', '7. Install on RHEL / Rocky / AlmaLinux', '۷. نصب روی RHEL / Rocky / AlmaLinux')
p('The following is for x86_64 RHEL-compatible major version 9 and Community 9.0. For major version 8, change only redhat/9 to redhat/8 in baseurl. Use the exact supported OS major and architecture; do not paste an RPM repository into an APT host. Keep gpgcheck=1.', 'فرمان زیر برای خانواده سازگار با RHEL نسخه اصلی 9 با معماری x86_64 و Community 9.0 است. برای نسخه اصلی 8 فقط redhat/9 را در baseurl به redhat/8 تغییر دهید. Major و معماری پشتیبانی‌شده دقیق را انتخاب کنید؛ مخزن RPM را در Host مبتنی بر APT قرار ندهید. gpgcheck=1 را حفظ کنید.')
code(r'''sudo tee /etc/yum.repos.d/mongodb-org.repo >/dev/null <<'EOF'
[mongodb-org-9.0]
name=MongoDB Community Repository
baseurl=https://repo.mongodb.org/yum/redhat/9/mongodb-org/9.0/x86_64/
gpgcheck=1
enabled=1
gpgkey=https://pgp.mongodb.com/server-9.asc
EOF
sudo dnf makecache
sudo dnf --showduplicates list mongodb-org
sudo dnf install -y mongodb-org
sudo systemctl enable --now mongod
sudo systemctl status mongod --no-pager
mongod --version
mongosh''')
p('RPM packages run as mongod and default to /var/lib/mongo. Keep SELinux enforcing and use MongoDB’s documented SELinux policy; changed data/log paths or ports can require updated labels and policy rules. Diagnose denials before changing permissions. Do not use setenforce 0 as a production fix.', 'بسته RPM با حساب mongod و مسیر Data پیش‌فرض /var/lib/mongo اجرا می‌شود. SELinux را Enforcing نگه دارید و Policy مستند MongoDB را به کار ببرید؛ تغییر مسیر Data/Log یا Port ممکن است Label و Rule جدید نیاز داشته باشد. پیش از تغییر Permission، Denial را تشخیص دهید. setenforce 0 راه‌حل Production نیست.')
code(r'''getenforce
sudo ausearch -m AVC -ts recent
sudo ls -Zd /var/lib/mongo /var/log/mongodb''')
link('https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=rhel&linux-method=pkg', 'Official Community RHEL installation and SELinux policy', 'نصب رسمی Community روی RHEL و Policy مربوط به SELinux')

section('configuration', '8. MongoDB Configuration', '۸. پیکربندی MongoDB')
p('Edit /etc/mongod.conf using YAML spaces, never tabs. Back up the current file and merge each later fragment into its existing top-level block; duplicate net or security keys are invalid practice. This is a localhost-only bootstrap configuration for Ubuntu/Debian. On RPM systems keep dbPath: /var/lib/mongo and preserve package-required process settings.', 'فایل /etc/mongod.conf را با Space در YAML و بدون Tab ویرایش کنید. ابتدا Backup بگیرید و Fragmentهای بعدی را در بلوک Top-Level موجود ادغام کنید؛ تکرار کلید net یا security روش درستی نیست. نمونه زیر Bootstrap محدود به Localhost برای Ubuntu/Debian است. در RPM مسیر dbPath: /var/lib/mongo و تنظیم Process موردنیاز بسته را حفظ کنید.')
code(r'''sudo cp -a /etc/mongod.conf /etc/mongod.conf.before-hardening
sudoedit /etc/mongod.conf''')
code(r'''storage:
  dbPath: /var/lib/mongodb

systemLog:
  destination: file
  logAppend: true
  path: /var/log/mongodb/mongod.log

net:
  port: 27017
  bindIp: 127.0.0.1

processManagement:
  timeZoneInfo: /usr/share/zoneinfo''', 'yaml')
table([('Parameter', 'پارامتر'), ('Purpose', 'کاربرد')], [
    [('storage.dbPath', 'storage.dbPath'), ('Location of database files; changing it does not migrate data.', 'مسیر فایل‌های دیتابیس؛ تغییر آن داده را مهاجرت نمی‌دهد.')],
    [('systemLog.destination / path', 'systemLog.destination / path'), ('Write logs to the named file.', 'ثبت Log در فایل مشخص‌شده.')],
    [('systemLog.logAppend', 'systemLog.logAppend'), ('Append after restart instead of replacing the existing log.', 'پس از Restart به Log قبلی اضافه می‌کند.')],
    [('net.port / bindIp', 'net.port / bindIp'), ('TCP listener and local server interfaces, not allowed client addresses.', 'Port Listener و Interfaceهای محلی سرور؛ نه فهرست IP مجاز Client.')],
    [('processManagement.timeZoneInfo', 'processManagement.timeZoneInfo'), ('Timezone database used by timezone-aware operations.', 'دیتابیس Timezone مورد استفاده عملیات مرتبط با منطقه زمانی.')],
    [('security / replication / net.tls', 'security / replication / net.tls'), ('Access control, cluster membership and transport encryption added below.', 'کنترل دسترسی، عضویت Cluster و رمزنگاری ارتباط که در ادامه اضافه می‌شوند.')],
])
p('WiredTiger is the default storage engine. Avoid obsolete journal.enabled tuning and arbitrary cacheSizeGB values. Keep memory available for the OS and filesystem cache; inspect effective configuration with getCmdLineOpts using an authorized administrative session. Restart for startup-file changes, then inspect logs immediately.', 'WiredTiger موتور Storage پیش‌فرض است. از تنظیم قدیمی journal.enabled و مقدار دلخواه cacheSizeGB اجتناب کنید. برای OS و File Cache حافظه باقی بگذارید؛ تنظیم مؤثر را با getCmdLineOpts در Session مدیریتی مجاز بررسی کنید. پس از تغییر فایل Startup، Restart و بلافاصله Log را بررسی کنید.')
link('https://www.mongodb.com/docs/manual/reference/configuration-options/', 'Official mongod configuration options', 'پارامترهای رسمی پیکربندی mongod')

section('remote-access', '9. Secure Remote Access', '۹. دسترسی Remote امن')
code(r'''net:
  port: 27017
  bindIp: 127.0.0.1,10.10.10.20''', 'yaml')
p('10.10.10.20 must be an address assigned to this server. bindIp chooses listening interfaces; the firewall restricts client sources. Prepare authentication, TLS and firewall rules before enabling the private listener in sections 10–14. Use a VPN or management network for administration and remove public NAT/port-forwarding rules.', 'آدرس 10.10.10.20 باید روی همین سرور وجود داشته باشد. bindIp انتخاب Interface شنود است؛ محدودیت Source Client با Firewall اعمال می‌شود. پیش از فعال‌کردن Listener خصوصی، احراز هویت، TLS و Ruleهای Firewall بخش‌های ۱۰ تا ۱۴ را آماده کنید. مدیریت را از VPN یا Management Network انجام دهید و NAT یا Port Forward عمومی را حذف کنید.')
p('Security warning: 0.0.0.0 listens on every IPv4 interface, potentially including a public NIC. It is not the default in this guide. An exceptional use requires explicit network isolation, authenticated TLS and verified deny-by-default rules for IPv4 and IPv6; binding alone never authorizes a client.', 'هشدار امنیتی: 0.0.0.0 روی همه Interfaceهای IPv4، از جمله NIC احتمالی Public، شنود می‌کند. در این راهنما پیش‌فرض نیست. استفاده استثنایی به Network Isolation، TLS و احراز هویت و بررسی Ruleهای Deny-by-Default در IPv4 و IPv6 نیاز دارد؛ bindIp به‌تنهایی Client را مجاز نمی‌کند.')
link('https://www.mongodb.com/docs/manual/core/security-mongodb-configuration/', 'IP binding and network configuration', 'تنظیم شبکه و IP Binding')

section('authentication', '10. MongoDB Authentication', '۱۰. احراز هویت MongoDB')
p('For the standalone bootstrap only, keep bindIp at 127.0.0.1 while authorization is still off. Connect locally and create the first administrator. passwordPrompt() asks for <STRONG-PASSWORD> without putting it in command history; a literal pwd: "<STRONG-PASSWORD>" illustrates a placeholder but is not suitable for secret handling.', 'فقط در Bootstrap مسیر Standalone، وقتی Authorization هنوز خاموش است bindIp را 127.0.0.1 نگه دارید. به‌صورت محلی وصل شوید و اولین مدیر را بسازید. passwordPrompt() مقدار <STRONG-PASSWORD> را بدون درج در Command History می‌گیرد؛ pwd: "<STRONG-PASSWORD>" فقط نمایش Placeholder است و برای مدیریت Secret مناسب نیست.')
code(r'''mongosh --host 127.0.0.1 --port 27017''')
code(r'''use admin
db.createUser({
  user: "mongoAdmin",
  pwd: passwordPrompt(),
  roles: [
    { role: "userAdminAnyDatabase", db: "admin" },
    { role: "dbAdminAnyDatabase", db: "admin" },
    { role: "readWriteAnyDatabase", db: "admin" }
  ]
})''', 'javascript')
p('These broad roles are for trusted administration only. userAdminAnyDatabase can grant powerful roles and is effectively an escalation capability; this combination is not identical to root and does not include every cluster operation or backup/restore privilege. Never use this account in applications. Create a separate operational user with clusterAdmin for replica management; clusterMonitor for monitoring; backup and restore for their respective tasks.', 'این Roleهای گسترده فقط برای مدیریت مورداعتماد هستند. userAdminAnyDatabase امکان اعطای Role قدرتمند و عملاً قابلیت افزایش سطح دسترسی دارد؛ ترکیب بالا معادل دقیق root نیست و همه عملیات Cluster یا Backup/Restore را شامل نمی‌شود. در برنامه از آن استفاده نکنید. برای مدیریت Replica حساب عملیاتی با clusterAdmin، برای مانیتورینگ clusterMonitor و برای وظایف Backup و Restore حساب‌های جدا با Role متناظر بسازید.')
code(r'''security:
  authorization: enabled''', 'yaml')
code(r'''sudo systemctl restart mongod
sudo systemctl status mongod --no-pager
mongosh --host 127.0.0.1 -u mongoAdmin -p --authenticationDatabase admin''')
code(r'''use admin
db.runCommand({ connectionStatus: 1 })
db.getSiblingDB("appdb").getCollectionNames()''', 'javascript')
p('Verify that a new unauthenticated session cannot list appdb collections. ping is only a liveness check and can succeed without database authorization. After TLS is enabled use the TLS connection commands in section 14.', 'بررسی کنید Session تازه بدون احراز هویت نتواند Collectionهای appdb را فهرست کند. ping صرفاً Liveness است و ممکن است بدون مجوز دیتابیس موفق شود. پس از فعال‌سازی TLS از فرمان اتصال بخش ۱۴ استفاده کنید.')
link('https://www.mongodb.com/docs/manual/tutorial/enable-authentication/', 'Official access-control bootstrap', 'راهنمای رسمی راه‌اندازی Access Control')
link('https://www.mongodb.com/docs/manual/reference/built-in-roles/', 'Built-in roles and administrative privileges', 'Roleهای داخلی و دسترسی مدیریتی')

section('application-user', '11. Dedicated Application User', '۱۱. حساب اختصاصی Application')
p('In the authenticated administrator shell, create a user in appdb. This user authenticates against appdb and only reads/writes that database. Use separate identities for applications, environments and scheduled jobs. A read-only service should receive read, not readWrite.', 'در Shell مدیر احراز هویت‌شده، User را در appdb بسازید. این حساب در appdb احراز هویت می‌شود و فقط همان Database را می‌خواند و می‌نویسد. برای هر Application، Environment و Job هویت جدا ایجاد کنید. سرویس Read-Only باید read بگیرد، نه readWrite.')
code(r'''use appdb
db.createUser({
  user: "appuser",
  pwd: passwordPrompt(),
  roles: [{ role: "readWrite", db: "appdb" }]
})''', 'javascript')
code(r'''mongosh --host 127.0.0.1 --username appuser --password \
  --authenticationDatabase appdb appdb''')
p('Test the application’s required operations and confirm privileged administration is denied. Least privilege includes database roles, network access and OS access. Rotate credentials through the secret store and update applications without recording passwords in source code.', 'عملیات لازم برنامه را تست و ردشدن عملیات مدیریتی را تأیید کنید. Least Privilege شامل Role دیتابیس، دسترسی شبکه و OS است. Credential را از طریق Secret Store Rotate کنید و بدون ثبت گذرواژه در Source برنامه را به‌روزرسانی کنید.')

section('firewall', '12. Firewall Hardening', '۱۲. ایمن‌سازی Firewall')
p('Allow 27017/TCP only from approved application/management hosts and the replica members. A CIDR is an example; /32 source rules are preferable when host addresses are fixed. An allow rule does not cancel an existing public allow-all rule: audit the complete ruleset and the cloud security group. Keep SSH access and console recovery available before enabling a firewall.', '27017/TCP را فقط برای Hostهای مجاز Application/Management و Memberهای Replica باز کنید. CIDR مثال است؛ برای IP ثابت Ruleهای /32 مناسب‌ترند. Rule محدود، Allow عمومی قبلی را لغو نمی‌کند؛ تمام Ruleها و Cloud Security Group را بررسی کنید. پیش از فعال‌کردن Firewall دسترسی SSH و بازیابی از Console را آماده نگه دارید.')
sub('Ubuntu UFW', 'UFW در Ubuntu')
code(r'''sudo ufw allow OpenSSH
sudo ufw default deny incoming
sudo ufw allow from 10.10.10.0/24 to any port 27017 proto tcp
# On replica nodes, also allow ONLY the three peer addresses:
sudo ufw allow from 10.10.20.11 to any port 27017 proto tcp
sudo ufw allow from 10.10.20.12 to any port 27017 proto tcp
sudo ufw allow from 10.10.20.13 to any port 27017 proto tcp
sudo ufw enable
sudo ufw status verbose''')
sub('RHEL family firewalld', 'firewalld در خانواده RHEL')
code(r'''sudo systemctl enable --now firewalld
sudo firewall-cmd --get-active-zones
# Example assumes the interface is in public; select its actual zone.
sudo firewall-cmd --permanent --zone=public --add-service=ssh
sudo firewall-cmd --permanent --zone=public \
  --add-rich-rule='rule family="ipv4" source address="10.10.10.0/24" port protocol="tcp" port="27017" accept'
for peer in 10.10.20.11 10.10.20.12 10.10.20.13; do
  sudo firewall-cmd --permanent --zone=public \
    --add-rich-rule="rule family=\"ipv4\" source address=\"$peer/32\" port protocol=\"tcp\" port=\"27017\" accept"
done
sudo firewall-cmd --reload
sudo firewall-cmd --zone=public --list-all''')
p('Confirm the zone target is not ACCEPT and that no broad mongodb service/27017 port rule, trusted-zone assignment, external NAT or IPv6 rule bypasses the restriction. Debian may use nftables instead; implement the equivalent source allowlist in the firewall already managed on that host, rather than mixing firewall managers.', 'تأیید کنید Target Zone برابر ACCEPT نباشد و سرویس عمومی mongodb، Rule عمومی 27017، عضویت در Trusted Zone، NAT خارجی یا Rule IPv6 محدودیت را دور نزند. Debian ممکن است nftables داشته باشد؛ Source Allowlist معادل را در Firewall مدیریت‌شده همان Host اعمال کنید و Managerهای Firewall را مخلوط نکنید.')
link('https://ubuntu.com/server/docs/how-to/security/firewalls/', 'Ubuntu official firewall documentation', 'مستندات رسمی Firewall در Ubuntu')
link('https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters', 'Red Hat official firewalld documentation', 'مستندات رسمی firewalld در Red Hat')

section('security', '13. MongoDB Security Hardening', '۱۳. ایمن‌سازی MongoDB')
figure('mongodb-security-architecture.png', 'MongoDB defense in depth with TLS, authentication, firewall, least privilege and blocked public access', 'لایه‌های امنیت MongoDB شامل TLS، احراز هویت، Firewall، Least Privilege و مسدودسازی دسترسی Public')
p('Enforce authentication and least privilege, private network isolation, source-restricted firewall rules, restricted bindIp and TLS. Remove public DNS/NAT exposure. Use long unique secrets and dedicated application users. Patch the OS and signed database packages under change control. Keep administrative access on the management network and restrict host login permissions.', 'احراز هویت و Least Privilege، Network Isolation خصوصی، Firewall محدود به Source، bindIp محدود و TLS را اعمال کنید. دسترسی Public از DNS/NAT را حذف کنید. Secret طولانی یکتا و حساب مجزای Application داشته باشید. وصله OS و بسته امضاشده دیتابیس را با کنترل تغییر نصب کنید. مدیریت را روی Management Network و Login Host را محدود نگه دارید.')
code(r'''ls -ld /var/lib/mongodb
ls -ld /var/log/mongodb
ps aux | grep '[m]ongod'
sudo systemctl show mongod -p User -p Group
# RPM installations:
ls -ld /var/lib/mongo
sudo stat -c '%U:%G %a %n' /etc/mongod.conf /etc/mongodb/*.pem''')
p('mongod must run as its package service user, not root. Data/log directories must be writable by that user and inaccessible to unrelated accounts; private keys and keyfiles must be owner-readable only. Do not repair errors with chmod 777 or an indiscriminate recursive chown. Validate each path, its parent traversal permissions, AppArmor/SELinux policy and the service user.', 'mongod باید با Service User بسته اجرا شود، نه root. Data/Log برای همان User قابل نوشتن و برای حساب نامرتبط غیرقابل دسترسی باشند؛ Private Key و KeyFile فقط توسط مالک قابل خواندن باشند. خطا را با chmod 777 یا chown بازگشتی بی‌هدف رفع نکنید. مسیر دقیق، مجوز Traverse والدها، Policy در AppArmor/SELinux و Service User را بررسی کنید.')
p('MongoDB Community does not provide the Enterprise native database audit facility; enabling an Enterprise-only auditLog setting is not a Community solution. Design OS audit, controlled administrative access, application audit events and protected centralized logs around your requirements. Ordinary mongod logs are not a complete compliance audit trail. Redact and restrict logs because queries can contain sensitive values.', 'MongoDB Community امکانات Native Database Audit نسخه Enterprise را ندارد؛ افزودن auditLog اختصاصی Enterprise راهکار Community نیست. OS Audit، دسترسی کنترل‌شده مدیر، Audit Event برنامه و Log متمرکز محافظت‌شده را با نیاز سازمان طراحی کنید. Log معمول mongod به‌تنهایی Audit Trail کامل Compliance نیست. Log را محدود و Redact کنید چون Query ممکن است مقدار حساس داشته باشد.')
link('https://www.mongodb.com/docs/manual/administration/security-checklist/', 'Official self-managed security checklist', 'چک‌لیست رسمی امنیت Self-Managed')
link('https://www.mongodb.com/docs/manual/core/auditing/', 'MongoDB auditing availability', 'دامنه دسترسی امکانات Audit در MongoDB')

section('tls', '14. TLS Encryption', '۱۴. رمزنگاری TLS')
p('Issue a certificate from an internal or trusted CA for each server. mongodb.pem contains that server’s certificate chain and private key; ca.pem contains CA certificates only. SANs must match the DNS names or IPs used by clients and peers; include localhost/127.0.0.1 only if you will use those names for verified local bootstrap. Never reuse the same private key across nodes.', 'برای هر سرور از Internal CA یا Trusted CA گواهی تهیه کنید. mongodb.pem شامل Certificate Chain و Private Key همان سرور و ca.pem فقط شامل CA Certificate است. SAN باید با DNS/IP استفاده‌شده در Client و Peer تطبیق داشته باشد؛ localhost/127.0.0.1 را فقط در صورت نیاز به Bootstrap محلی معتبر اضافه کنید. Private Key یکسان را بین Nodeها استفاده نکنید.')
code(r'''# Set mongodb on Ubuntu/Debian; set mongod on RPM systems.
MONGO_SERVICE_USER=mongodb
sudo install -d -m 750 -o "$MONGO_SERVICE_USER" -g "$MONGO_SERVICE_USER" /etc/mongodb
# Provision the CA-issued files securely before running these commands.
sudo chown "$MONGO_SERVICE_USER:$MONGO_SERVICE_USER" /etc/mongodb/mongodb.pem
sudo chmod 400 /etc/mongodb/mongodb.pem
sudo chown root:"$MONGO_SERVICE_USER" /etc/mongodb/ca.pem
sudo chmod 640 /etc/mongodb/ca.pem''')
code(r'''net:
  port: 27017
  bindIp: 127.0.0.1,10.10.10.20
  tls:
    mode: requireTLS
    certificateKeyFile: /etc/mongodb/mongodb.pem
    CAFile: /etc/mongodb/ca.pem
    allowConnectionsWithoutCertificates: true

security:
  authorization: enabled''', 'yaml')
p('This TLS+SCRAM profile allows clients without a client certificate while still requiring encrypted transport, a valid server certificate and database credentials. With CAFile, client certificates are otherwise required by default; omit allowConnectionsWithoutCertificates or set false when you deliberately require mutual TLS, and provision a client PEM. A presented invalid certificate is still rejected. Do not enable allowInvalidCertificates or allowInvalidHostnames.', 'این Profile با TLS+SCRAM به Client بدون Client Certificate اجازه اتصال رمزنگاری‌شده می‌دهد، اما Certificate معتبر سرور و Credential دیتابیس همچنان لازم‌اند. با CAFile، به‌صورت پیش‌فرض Client Certificate نیز لازم است؛ برای mTLS عمدی گزینه allowConnectionsWithoutCertificates را حذف یا false و Client PEM را Provision کنید. Certificate ارائه‌شده نامعتبر همچنان رد می‌شود. allowInvalidCertificates یا allowInvalidHostnames را فعال نکنید.')
code(r'''sudo systemctl restart mongod
sudo journalctl -u mongod -n 50 --no-pager
mongosh --host '<HOSTNAME>' --port 27017 --tls \
  --tlsCAFile /etc/mongodb/ca.pem \
  -u mongoAdmin -p --authenticationDatabase admin''')
p('Confirm trusted CA, hostname matching, expiry and that plaintext connections are rejected. Coordinate certificate renewal before expiry. Internal replica TLS must also work in both directions; when a member certificate serves as both server and client, its extended key usage must support serverAuth and clientAuth.', 'CA مورداعتماد، تطبیق Hostname، Expiry و رد اتصال Plaintext را تأیید کنید. Renewal را پیش از انقضا هماهنگ کنید. TLS داخلی Replica نیز باید دوطرفه کار کند؛ وقتی Certificate عضو هم نقش Server و هم Client دارد، EKU باید serverAuth و clientAuth را پشتیبانی کند.')
link('https://www.mongodb.com/docs/manual/tutorial/configure-ssl/', 'Official TLS configuration', 'راهنمای رسمی پیکربندی TLS')

section('replica-set', '15. Production Replica Set', '۱۵. Replica Set در Production')
figure('mongodb-replica-set.png', 'Three-member MongoDB rs0 replica set with primary, secondaries, majority election and automatic failover', 'Replica Set سه‌عضوی rs0 با Primary، Secondaryها، Election اکثریت و Failover خودکار')
code(r'''mongo01 - 10.10.20.11
mongo02 - 10.10.20.12
mongo03 - 10.10.20.13
Replica Set: rs0''', 'text')
p('Use three data-bearing voting members in independent failure domains with matching server versions. Use stable DNS names; mongo01/mongo02/mongo03 are short-name examples, and FQDNs are preferable in a real PKI. DNS must resolve from every member and application host and match certificate SANs. Do not configure member host fields as bare IP addresses.', 'سه Member رأی‌دهنده دارای Data را در Failure Domain مستقل و با Version یکسان قرار دهید. از DNS پایدار استفاده کنید؛ mongo01/mongo02/mongo03 نام کوتاه نمونه‌اند و FQDN در PKI واقعی بهتر است. DNS باید از هر Member و Application Host Resolve و با SAN گواهی مطابق باشد. host اعضا را به‌صورت IP خالی تنظیم نکنید.')
p('Fresh-cluster order: install on all nodes, stop mongod, prepare mounts and TLS certificates, configure the peer/application firewall rules, distribute the single shared keyfile from section 16, then configure and start every member. Never temporarily expose an unauthenticated replica listener. Existing standalone conversion requires backup, a separate migration plan and authenticating with existing users; the localhost exception is unavailable if users already exist.', 'ترتیب Cluster تازه: روی همه Nodeها نصب کنید، mongod را Stop کنید، Mount و Certificateهای TLS را آماده کنید، Ruleهای Firewall برای Peer/Application را اعمال کنید، KeyFile مشترک بخش ۱۶ را توزیع کنید، سپس همه Memberها را Configure و Start کنید. Listener Replica بدون احراز هویت را موقتاً باز نکنید. تبدیل Standalone موجود به Backup، برنامه مهاجرت جدا و اتصال با User موجود نیاز دارد؛ با وجود User قبلی، Localhost Exception فعال نیست.')
code(r'''sudo systemctl stop mongod
getent hosts mongo01 mongo02 mongo03''')
p('After preparing section 16, merge the following on every node. Substitute that node’s private IP: .11, .12 or .13. Use /var/lib/mongo on RPM installations. This is the complete TLS+SCRAM/keyfile teaching profile, with access control enabled from first cluster startup.', 'پس از آماده‌سازی بخش ۱۶، تنظیم زیر را روی هر Node ادغام کنید. IP خصوصی همان Node یعنی .11، .12 یا .13 را قرار دهید. در RPM از /var/lib/mongo استفاده کنید. این Profile کامل آموزشی TLS+SCRAM/KeyFile است و Access Control از اولین Start Cluster فعال است.')
REPLICA_CONFIG = '''storage:
  dbPath: /var/lib/mongodb
systemLog:
  destination: file
  logAppend: true
  path: /var/log/mongodb/mongod.log
net:
  port: 27017
  bindIp: 127.0.0.1,10.10.20.11
  tls:
    mode: requireTLS
    certificateKeyFile: /etc/mongodb/mongodb.pem
    CAFile: /etc/mongodb/ca.pem
    allowConnectionsWithoutCertificates: true
security:
  keyFile: /etc/mongodb/keyfile
  authorization: enabled
replication:
  replSetName: rs0
processManagement:
  timeZoneInfo: /usr/share/zoneinfo'''
code(REPLICA_CONFIG, 'yaml')
code(r'''# Run on each node after certificates and shared keyfile exist.
sudo systemctl restart mongod
sudo systemctl enable mongod
sudo systemctl status mongod --no-pager
# Run locally on mongo01; the certificate must cover 127.0.0.1.
mongosh 'mongodb://127.0.0.1:27017/?directConnection=true' \
  --tls --tlsCAFile /etc/mongodb/ca.pem''')
p('For a fresh cluster with no users, the localhost exception allows initial configuration over loopback. Run rs.initiate once, on mongo01 only. Authentication being enabled does not require disabling it for bootstrap.', 'در Cluster تازه بدون User، Localhost Exception اجازه تنظیم اولیه از Loopback را می‌دهد. rs.initiate را فقط یک بار روی mongo01 اجرا کنید. فعال‌بودن Authentication نیازمند خاموش‌کردن آن برای Bootstrap نیست.')
code(r'''rs.initiate({
  _id: "rs0",
  members: [
    { _id: 0, host: "mongo01:27017" },
    { _id: 1, host: "mongo02:27017" },
    { _id: 2, host: "mongo03:27017" }
  ]
})
rs.status()
db.hello().isWritablePrimary''', 'javascript')
p('Wait for election. If mongo01 is not primary, connect locally on the elected primary using the same loopback TLS method. Create mongoAdmin there with the userAdminAnyDatabase role and the administrator example from section 10, then reconnect with credentials. The exception closes after the first user. Create appuser on the primary once; users replicate, so do not create them separately on every secondary.', 'منتظر Election بمانید. اگر mongo01 Primary نیست، روی Primary منتخب با همان روش TLS محلی متصل شوید. mongoAdmin را با Role مدیریت User و مثال مدیر بخش ۱۰ همان‌جا بسازید و سپس با Credential دوباره وصل شوید. Exception با اولین User بسته می‌شود. appuser را یک بار روی Primary بسازید؛ Userها Replicate می‌شوند و نباید روی هر Secondary جدا ساخته شوند.')
sub('Dedicated cluster operations account', 'حساب جداگانه عملیات Cluster')
code(r'''use admin
db.createUser({
  user: "mongoOps",
  pwd: passwordPrompt(),
  roles: [{ role: "clusterAdmin", db: "admin" }]
})''', 'javascript')
code(r'''mongosh 'mongodb://mongo01:27017,mongo02:27017,mongo03:27017/admin?replicaSet=rs0' \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --username mongoOps --password --authenticationDatabase admin''')
code(r'''rs.status()
rs.conf()
db.hello()
rs.printSecondaryReplicationInfo()''', 'javascript')
p('The primary accepts writes; secondaries asynchronously apply its oplog. A three-voter set needs two votes to elect a primary, and an eligible up-to-date secondary can take over. Losing two members normally removes write availability. Failover includes an election interval and connection recovery: test application retry behavior rather than assuming zero downtime. mongo01 is the illustrated primary, not permanently fixed as primary.', 'Primary عملیات Write را می‌پذیرد و Secondaryها Oplog را به‌صورت Async اعمال می‌کنند. Set با سه رأی‌دهنده برای انتخاب Primary به دو رأی نیاز دارد و Secondary واجدشرایط و به‌روز می‌تواند جایگزین شود. فقدان دو Member معمولاً Write Availability را از بین می‌برد. Failover شامل زمان Election و بازیابی اتصال است؛ Retry برنامه را تست کنید و Downtime صفر فرض نکنید. mongo01 در شکل Primary نمونه است، نه Primary دائمی.')
p('Use explicit majority write concern for writes that require majority acknowledgment and appropriate read concern for consistent reads. Retryable writes help selected operations; application idempotency and transaction handling remain necessary. Size the oplog retention window longer than planned outages and peak lag. Replication copies accidental deletes too; it is not backup.', 'برای Write نیازمند تأیید اکثریت از Write Concern صریح majority و برای Read سازگار از Read Concern متناسب استفاده کنید. Retryable Write به عملیات منتخب کمک می‌کند؛ Idempotency و مدیریت Transaction برنامه همچنان لازم است. پنجره نگهداری Oplog را از Outage برنامه‌ریزی‌شده و Lag اوج بزرگ‌تر بگیرید. Replication حذف اشتباه را نیز کپی می‌کند و Backup نیست.')
link('https://www.mongodb.com/docs/manual/tutorial/deploy-replica-set-with-keyfile-access-control/', 'Official secure replica-set bootstrap and localhost exception', 'Bootstrap امن Replica Set و Localhost Exception در مستندات رسمی')
link('https://www.mongodb.com/docs/manual/core/replica-set-elections/', 'Official replica elections and quorum behavior', 'رفتار رسمی Election و Quorum')
link('https://www.mongodb.com/docs/manual/reference/write-concern/', 'Write concern and majority acknowledgment', 'Write Concern و تأیید اکثریت')

section('replica-security', '16. Replica Set Internal Authentication', '۱۶. احراز هویت داخلی Replica Set')
p('Internal authentication verifies member identity; client authorization is a separate layer. Prepare this section before starting the cluster in section 15. The requested keyfile example is supported, but current MongoDB guidance recommends X.509 membership authentication for production and keyfiles for development/testing. Treat keyfile+TLS as a constrained baseline requiring a documented security decision, not the strongest production profile.', 'Internal Authentication هویت Member را تأیید می‌کند و Client Authorization لایه‌ای جداست. این بخش را پیش از Start Cluster بخش ۱۵ انجام دهید. مثال KeyFile درخواستی پشتیبانی می‌شود، اما راهنمای فعلی MongoDB برای Production، احراز هویت عضویت با X.509 و برای Development/Testing، KeyFile را توصیه می‌کند. KeyFile+TLS را مبنایی با محدودیت و نیازمند تصمیم امنیتی مستند بدانید، نه قوی‌ترین Profile تولید.')
sub('Keyfile example: generate once', 'مثال KeyFile: تولید فقط یک بار')
code(r'''# Generate ONCE on mongo01. Use mongod for RPM installations.
MONGO_SERVICE_USER=mongodb
sudo install -d -m 750 -o "$MONGO_SERVICE_USER" -g "$MONGO_SERVICE_USER" /etc/mongodb
sudo sh -c 'umask 077; openssl rand -base64 756 > /etc/mongodb/keyfile'
sudo chmod 400 /etc/mongodb/keyfile
sudo chown "$MONGO_SERVICE_USER:$MONGO_SERVICE_USER" /etc/mongodb/keyfile''')
p('Deliver exactly this file to /etc/mongodb/keyfile on mongo02 and mongo03 through your authenticated encrypted configuration/secret distribution channel. Apply owner mongodb:mongodb on Debian/Ubuntu or mongod:mongod on RPM, mode 400, on every member. Do not generate a new random key independently on each node and do not publish key bytes or hashes. Ensure the parent directory is traversable by the service user and SELinux permits reads.', 'همین فایل را دقیقاً با کانال توزیع Config/Secret رمزنگاری‌شده و احراز هویت‌شده در /etc/mongodb/keyfile روی mongo02 و mongo03 قرار دهید. مالک را در Debian/Ubuntu برابر mongodb:mongodb و در RPM برابر mongod:mongod و Mode را 400 روی همه Memberها تنظیم کنید. روی هر Node کلید تصادفی مستقل تولید نکنید و Byte یا Hash کلید را منتشر نکنید. Traverse والد و اجازه خواندن SELinux را بررسی کنید.')
code(r'''security:
  keyFile: /etc/mongodb/keyfile
  authorization: enabled
replication:
  replSetName: rs0''', 'yaml')
p('A keyfile enables membership authentication and access control but does not encrypt the wire: retain requireTLS. Plan key rotation with overlapping accepted keys and the official rolling procedure so peers continue to share a key. Store the keyfile outside Git and database backups in an access-controlled secret system.', 'KeyFile احراز هویت عضویت و Access Control را فعال می‌کند اما Wire را رمز نمی‌کند؛ requireTLS را حفظ کنید. Rotation را با کلیدهای پذیرفته‌شده همپوشان و مسیر Rolling رسمی انجام دهید تا Peerها همیشه کلید مشترک داشته باشند. KeyFile را خارج از Git و Backup داده در Secret System با کنترل دسترسی نگه دارید.')
sub('Recommended production alternative: X.509 members', 'گزینه توصیه‌شده Production: اعضا با X.509')
code(r'''security:
  authorization: enabled
  clusterAuthMode: x509
net:
  port: 27017
  bindIp: 127.0.0.1,10.10.20.11
  tls:
    mode: requireTLS
    certificateKeyFile: /etc/mongodb/mongodb.pem
    clusterFile: /etc/mongodb/member.pem
    CAFile: /etc/mongodb/ca.pem
    allowConnectionsWithoutCertificates: true
replication:
  replSetName: rs0''', 'yaml')
p('For a new cluster, choose this alternative instead of keyFile on every member and use the same first-user bootstrap sequence. member.pem is a unique CA-issued member certificate plus key with clientAuth usage; the server certificate needs serverAuth. Membership O/OU/DC attributes must match across member certificates and differ from client identities. SANs must match advertised names. This still permits SCRAM application users over TLS; use dedicated X.509 client identities and mutual TLS if required by policy. Provision and validate the PKI before deployment.', 'در Cluster تازه این گزینه را به جای keyFile روی همه Memberها انتخاب و همان ترتیب Bootstrap اولین User را دنبال کنید. member.pem گواهی و کلید یکتای عضو از CA با کاربرد clientAuth است؛ گواهی سرور serverAuth می‌خواهد. Attributeهای عضویت O/OU/DC باید میان اعضا مطابق و از هویت Client جدا باشند. SAN با نام معرفی‌شده تطبیق کند. این Profile هنوز User برنامه با SCRAM روی TLS را می‌پذیرد؛ در صورت نیاز Policy از هویت Client مجزای X.509 و mTLS استفاده کنید. PKI را پیش از استقرار Provision و اعتبارسنجی کنید.')
link('https://www.mongodb.com/docs/manual/tutorial/configure-x509-member-authentication/', 'Official production X.509 membership authentication', 'احراز هویت عضویت Production با X.509')
link('https://www.mongodb.com/docs/manual/tutorial/rotate-key-replica-set/', 'Replica-set key rotation', 'Rotation کلید Replica Set')

section('connection-string', '17. MongoDB Connection Strings and Secrets', '۱۷. Connection String و Secretها')
sub('Standalone URI', 'URI برای Standalone')
code('mongodb://appuser:<STRONG-PASSWORD>@10.10.10.20:27017/appdb?authSource=appdb&tls=true', 'text')
sub('Replica-set URI', 'URI برای Replica Set')
code('mongodb://appuser:<STRONG-PASSWORD>@mongo01:27017,mongo02:27017,mongo03:27017/appdb?replicaSet=rs0&authSource=appdb&tls=true&retryWrites=true&w=majority', 'text')
p('These URI templates are not literal executable passwords. Percent-encode reserved characters in username/password components, or provide credentials separately through driver options. appuser was created in appdb, so authSource=appdb; administrators created in admin use authSource=admin. Configure the driver’s trusted CA file separately and verify server names; tls=true does not install your internal CA.', 'این URIها Template هستند و Password قابل اجرای واقعی ندارند. کاراکترهای رزروشده Username/Password را Percent-Encode یا Credential را جدا در Driver Option ارائه کنید. appuser در appdb ساخته شده، پس authSource=appdb است؛ مدیر ساخته‌شده در admin از authSource=admin استفاده می‌کند. فایل CA مورداعتماد را جدا در Driver Configure و نام سرور را اعتبارسنجی کنید؛ tls=true، Internal CA شما را نصب نمی‌کند.')
p('Keep real credentials out of Git, logs, shell history and process arguments. Use a vault or CI/CD secret store; environment variables are convenient but visible to privileged processes and can leak in diagnostics. Kubernetes Secrets need RBAC and encryption at rest: base64 encoding is not encryption. Set bounded connection pools, timeouts and retry policies, then test topology discovery and failover from the application network.', 'Credential واقعی را در Git، Log، Shell History و Process Argument قرار ندهید. از Vault یا CI/CD Secret Store استفاده کنید؛ Environment Variable ساده است اما برای Process دارای دسترسی بالا قابل مشاهده و در Diagnostic قابل نشت است. Kubernetes Secret به RBAC و Encryption at Rest نیاز دارد؛ Base64 رمزنگاری نیست. Connection Pool محدود، Timeout و Retry Policy را تنظیم و Topology Discovery و Failover را از شبکه برنامه تست کنید.')
link('https://www.mongodb.com/docs/manual/reference/connection-string/', 'Official MongoDB URI format', 'فرمت رسمی URI اتصال MongoDB')

section('backup', '18. Backup and Tested Recovery', '۱۸. Backup و بازیابی آزموده‌شده')
figure('mongodb-backup-monitoring.png', 'MongoDB backup server and parallel Prometheus Grafana Zabbix and syslog monitoring architecture', 'معماری Backup Server و مسیرهای موازی مانیتورینگ Prometheus، Grafana، Zabbix و Syslog برای MongoDB')
p('Define RPO (acceptable data loss), RTO (recovery time), retention and off-site/immutable copies. Encrypt backup data, restrict access and keep an independent copy outside the MongoDB server’s failure domain. Record tool/server versions and test restores regularly. mongodump and mongorestore belong to MongoDB Database Tools, which have their own version numbering; check their compatibility with your server before scheduling jobs.', 'RPO یعنی داده قابل ازدست‌رفتن، RTO یعنی زمان بازیابی، Retention و نسخه Off-site/Immutable را تعیین کنید. Backup را رمز، دسترسی را محدود و نسخه مستقل را خارج از Failure Domain سرور MongoDB نگه دارید. Version ابزار/سرور را ثبت و Restore را دوره‌ای تست کنید. mongodump و mongorestore از MongoDB Database Tools با Version مستقل هستند؛ سازگاری با سرور را پیش از زمان‌بندی Job بررسی کنید.')
code(r'''mongodump --version
mongorestore --version''')
sub('Dedicated backup and restore identities', 'هویت مجزای Backup و Restore')
code(r'''use admin
db.createUser({
  user: "mongoBackup",
  pwd: passwordPrompt(),
  roles: [{ role: "backup", db: "admin" }]
})
db.createUser({
  user: "mongoRestore",
  pwd: passwordPrompt(),
  roles: [{ role: "restore", db: "admin" }]
})''', 'javascript')
p('Create the restore identity on the isolated restore target, not automatically on every production cluster. The broad mongoAdmin roles shown earlier do not substitute for the backup/restore roles. Omitting password flags below prompts interactively; scheduled jobs must read a mode-600 Tools --config file provisioned by the secret system, never a password embedded in --uri.', 'حساب Restore را در مقصد بازیابی ایزوله بسازید، نه به‌صورت خودکار در همه Clusterهای Production. Roleهای گسترده mongoAdmin در بخش قبل جای backup/restore را نمی‌گیرند. حذف Password Flag در فرمان زیر Prompt ایجاد می‌کند؛ Job زمان‌بندی‌شده باید از فایل Tools --config با Mode برابر 600 که Secret System ایجاد کرده بخواند، نه Password داخل --uri.')
sub('Small database logical backup', 'Backup منطقی برای دیتابیس کوچک')
code(r'''umask 077
BACKUP_DIR="/backup/mongodb/$(date +%F)/$(date +%H%M%S)"
mkdir -p "$BACKUP_DIR"
mongodump \
  --host '<HOSTNAME>' --port 27017 \
  --username mongoBackup --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --db appdb --out "$BACKUP_DIR"''')
p('The backup job OS account must own /backup/mongodb or have explicit write permission. This per-database dump is not an atomic point-in-time image while writes continue: quiesce application writes if cross-collection consistency is required. A successful process exit is necessary but not sufficient: check output, free capacity, restore results and business invariants.', 'حساب OS اجرای Backup باید مالک /backup/mongodb یا دارای مجوز نوشتن صریح باشد. Dump یک Database هنگام ادامه Write، تصویر Atomic از یک لحظه نیست؛ برای Consistency میان Collectionها Write برنامه را متوقف کنید. Exit موفق لازم است اما کافی نیست: خروجی، ظرفیت آزاد، نتیجه Restore و Business Invariantها را بررسی کنید.')
sub('Restore appdb into an isolated target', 'Restore دیتابیس appdb در مقصد ایزوله')
code(r'''mongorestore \
  --host '<HOSTNAME>' --port 27017 \
  --username mongoRestore --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --nsInclude='appdb.*' \
  '/backup/mongodb/<DATE>/<TIME>' ''')
p('Here <HOSTNAME> is the isolated restore host. The example does not drop existing collections: restoring over live data can produce duplicate keys or merge unintended records. Use a clean target and validate indexes, counts and application reads. --drop is destructive and belongs only in an explicitly planned replacement restore.', 'در این مثال <HOSTNAME> مقصد Restore ایزوله است. فرمان Collection موجود را Drop نمی‌کند؛ Restore روی داده زنده می‌تواند Duplicate Key یا ادغام ناخواسته ایجاد کند. از مقصد تمیز استفاده و Index، Count و Read برنامه را کنترل کنید. --drop مخرب است و فقط در برنامه جایگزینی صریح باید استفاده شود.')
sub('Replica-set full dump with oplog capture', 'Dump کامل Replica Set با ثبت Oplog')
code(r'''umask 077
mongodump \
  --host 'rs0/mongo01:27017,mongo02:27017,mongo03:27017' \
  --username mongoBackup --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --oplog --gzip --archive="/backup/mongodb/rs0-$(date +%F-%H%M%S).archive.gz"''')
p('Use --oplog with a full replica-set dump, not --db or filtered collection dumps. Ensure the oplog window covers the entire dump and avoid schema changes/renames during capture. --oplogReplay on restore replays captured operations; it cannot be combined with namespace filtering and requires privileges beyond the built-in restore role. Provision the documented custom replay privileges on the isolated recovery system only.', 'از --oplog برای Dump کامل Replica Set استفاده کنید، نه همراه --db یا Collection Filter. پنجره Oplog باید کل Dump را پوشش دهد و هنگام Capture از Schema Change/Rename اجتناب کنید. --oplogReplay در Restore عملیات ثبت‌شده را Replay می‌کند؛ با Namespace Filter ترکیب نمی‌شود و دسترسی بیشتر از Role داخلی restore نیاز دارد. دسترسی سفارشی مستند Replay را فقط در سیستم بازیابی ایزوله Provision کنید.')
code(r'''# Run only on an isolated clean recovery target with approved replay privileges.
mongorestore \
  --host '<HOSTNAME>' --port 27017 \
  --username '<ADMIN-USER>' --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --gzip --archive='/backup/mongodb/<ARCHIVE>.archive.gz' --oplogReplay''')
p('Large databases need a measured replica/snapshot strategy with atomic data+journal snapshots, consistent topology and an oplog/PITR plan when required. A raw copy of a live dbPath is not a consistent backup. Replicas, snapshots on the same failing storage and backup files without a tested recovery procedure do not meet a recovery objective.', 'برای دیتابیس بزرگ، استراتژی Replica/Snapshot اندازه‌گیری‌شده با Snapshot اتمیک Data و Journal، Topology سازگار و در صورت نیاز برنامه Oplog/PITR لازم است. کپی خام dbPath زنده Backup سازگار نیست. Replica، Snapshot روی همان Storage خراب‌شونده و فایل Backup بدون مسیر بازیابی تست‌شده هدف Recovery را برآورده نمی‌کنند.')
link('https://www.mongodb.com/docs/database-tools/mongodump/', 'Official mongodump options and consistency limitations', 'گزینه‌ها و محدودیت Consistency در mongodump')
link('https://www.mongodb.com/docs/database-tools/mongorestore/', 'Official mongorestore and oplog replay requirements', 'الزامات رسمی mongorestore و Oplog Replay')
link('https://www.mongodb.com/docs/manual/core/backups/', 'Official backup methods', 'روش‌های رسمی Backup')

section('performance', '19. Performance Checks', '۱۹. بررسی Performance')
code(r'''# Install the distribution sysstat package for iostat.
# Ubuntu/Debian: sudo apt install -y sysstat
# RHEL family: sudo dnf install -y sysstat
iostat -xz 1
vmstat 1
free -h
df -h
df -i''')
code(r'''// Run with a monitoring identity; replace users with your collection.
db.serverStatus()
use appdb
db.stats()
db.users.stats()
// Preferred collection statistics for new automation:
db.users.aggregate([{ $collStats: { storageStats: {} } }])''', 'javascript')
p('Use real collection names: db.collection.stats() is a template, not a scan of all collections. The collStats command behind that helper is deprecated; prefer $collStats for new integrations. serverStatus requires suitable privileges such as clusterMonitor, so application credentials are not appropriate for full-server diagnostics.', 'نام Collection واقعی را استفاده کنید: db.collection.stats() Template است، نه بررسی همه Collectionها. فرمان collStats پشت این Helper Deprecated است؛ برای Integration جدید $collStats مناسب‌تر است. serverStatus دسترسی مناسب مانند clusterMonitor می‌خواهد؛ Credential برنامه برای Diagnostic کامل سرور مناسب نیست.')
table([('Signal', 'شاخص'), ('Interpretation and action', 'تفسیر و اقدام')], [
    [('Working set / RAM', 'Working Set / RAM'), ('Frequent data and indexes should fit the usable cache budget; correlate eviction, disk reads and latency before enlarging cache.', 'Data و Index پرتکرار باید در بودجه Cache قابل استفاده قرار گیرد؛ پیش از افزایش Cache، Eviction، Read Disk و Latency را مرتبط بررسی کنید.')],
    [('Disk IOPS / latency', 'IOPS و Latency دیسک'), ('Read await, queue depth and throughput under peak load; %util alone is insufficient for NVMe/RAID.', 'await، عمق صف و Throughput را در Peak Load ببینید؛ %util به‌تنهایی برای NVMe/RAID کافی نیست.')],
    [('Indexes / slow queries', 'Index و Slow Query'), ('Inspect execution plans, scanned/returned ratio and p95/p99 latency; avoid adding indexes blindly.', 'Plan، نسبت Scanned/Returned و Latency p95/p99 را بررسی کنید؛ کورکورانه Index اضافه نکنید.')],
    [('Connections', 'Connection'), ('Track current, available and churn; reuse bounded driver pools rather than opening a connection per request.', 'current، available و نرخ ایجاد را رصد کنید؛ Driver Pool محدود را بازاستفاده کنید، نه یک اتصال در هر Request.')],
    [('Replication capacity', 'ظرفیت Replication'), ('Check lag and oplog window during peak writes and backups; secondaries need comparable CPU and storage.', 'Lag و Oplog Window را هنگام Peak Write و Backup کنترل کنید؛ Secondary به CPU/Storage هم‌سطح نیاز دارد.')],
])
link('https://www.mongodb.com/docs/manual/reference/method/db.stats/', 'Database statistics', 'آمار Database')
link('https://www.mongodb.com/docs/manual/reference/method/db.collection.stats/', 'Collection statistics and deprecation notes', 'آمار Collection و نکات Deprecation')

section('indexing', '20. Indexing and Query Plans', '۲۰. Index و Query Plan')
code(r'''use appdb
db.users.createIndex({ email: 1 })
db.users.getIndexes()
db.users.find({ email: "user@example.com" }).explain("executionStats")''', 'javascript')
p('Inspect nReturned, totalDocsExamined, totalKeysExamined and winningPlan. A selective email lookup should examine a small number of keys/documents. IXSCAN indicates index use; COLLSCAN means a collection scan. A broad query on a tiny collection may legitimately scan, but repeated selective scans on a large production collection can exhaust I/O and CPU.', 'nReturned، totalDocsExamined، totalKeysExamined و winningPlan را بررسی کنید. Lookup انتخاب‌گر email باید تعداد کمی Key/Document بررسی کند. IXSCAN استفاده از Index و COLLSCAN اسکن Collection است. Query گسترده روی Collection کوچک ممکن است به‌درستی Scan شود، اما Scan مکرر انتخاب‌گر روی Collection بزرگ Production می‌تواند I/O و CPU را اشباع کند.')
p('Design compound indexes around filters and sort order; validate with representative data. If email must be unique, clean existing duplicates before creating a unique index. Each index consumes RAM/disk and adds write cost. Build indexes under change control and watch capacity and replication lag; do not describe every scan as an incident.', 'Compound Index را با Filter و Sort طراحی و با داده نماینده تست کنید. اگر email باید Unique باشد، پیش از Unique Index Duplicateهای موجود را پاک‌سازی کنید. هر Index حافظه/دیسک می‌گیرد و هزینه Write دارد. Index را با کنترل تغییر بسازید و ظرفیت و Lag را رصد کنید؛ هر Scan الزاماً Incident نیست.')
link('https://www.mongodb.com/docs/manual/indexes/', 'Official indexing concepts', 'مفاهیم رسمی Indexing')
link('https://www.mongodb.com/docs/manual/reference/method/db.collection.explain/', 'Official explain execution statistics', 'Execution Statistics رسمی در explain')

section('logging', '21. Logging and Slow Queries', '۲۱. Log و Slow Query')
code(r'''sudo tail -f /var/log/mongodb/mongod.log
sudo journalctl -u mongod --no-pager
sudo journalctl -u mongod -n 100 --no-pager''')
p('MongoDB writes structured diagnostic logs. Look for YAML parse failures, missing dbPath, permission denied, Address already in use, bad keyfile ownership, TLS handshake/certificate failures, WiredTiger errors and authentication failures. The first startup error usually explains subsequent service restarts; preserve logs before remediation.', 'MongoDB Diagnostic Log ساختاریافته می‌نویسد. خطای Parse YAML، فقدان dbPath، Permission Denied، Address Already in Use، مالکیت KeyFile، خطای Handshake/Certificate TLS، خطای WiredTiger و Authentication Failure را جست‌وجو کنید. اولین خطای Startup معمولاً Restartهای بعدی را توضیح می‌دهد؛ پیش از اقدام Log را حفظ کنید.')
code(r'''operationProfiling:
  mode: off
  slowOpThresholdMs: 100
  slowOpSampleRate: 0.1''', 'yaml')
p('This example keeps the database profiler off and configures sampled slow diagnostic logging. Treat 100 ms and 10% as starting points tied to your SLO, not universal values. Profiling all operations can increase load and capture sensitive query data. Centralize logs with bounded retention and alerts for failed authentication, storage errors and elections.', 'این نمونه Profiler دیتابیس را خاموش نگه می‌دارد و Sample از Slow Diagnostic Log می‌گیرد. ۱۰۰ میلی‌ثانیه و ۱۰ درصد نقطه آغاز متناسب با SLO هستند، نه عدد جهانی. Profile همه عملیات بار را افزایش می‌دهد و می‌تواند Query حساس ثبت کند. Log را با Retention محدود و Alert احراز هویت ناموفق، خطای Storage و Election متمرکز کنید.')
p('Configure rotation before disk exhaustion. With systemLog.logRotate: reopen, use an external rename/create policy that preserves the service owner, then request logRotate (or the documented SIGUSR1). Do not combine incompatible rename/reopen policies or assume copytruncate is safe. Test rotation and retention on a staging node.', 'Rotation را پیش از پرشدن Disk تنظیم کنید. با systemLog.logRotate: reopen، Policy خارجی Rename/Create با مالک Service User اعمال و سپس logRotate یا SIGUSR1 مستند اجرا کنید. Policyهای ناسازگار rename/reopen را ترکیب نکنید و copytruncate را بی‌خطر فرض نکنید. Rotation و Retention را در Staging تست کنید.')
link('https://www.mongodb.com/docs/manual/tutorial/rotate-log-files/', 'Official MongoDB log rotation', 'Rotation رسمی Log در MongoDB')
link('https://www.mongodb.com/docs/manual/tutorial/manage-the-database-profiler/', 'Official database profiler and slow-operation settings', 'Profiler و تنظیم Slow Operation در مستندات رسمی')

section('monitoring', '22. Monitoring and Alerting', '۲۲. مانیتورینگ و Alert')
code(r'''use admin
db.createUser({
  user: "mongoMonitor",
  pwd: passwordPrompt(),
  roles: [{ role: "clusterMonitor", db: "admin" }]
})''', 'javascript')
p('Use a MongoDB Exporter compatible with your server, Prometheus for collection and Grafana for dashboards; Zabbix can independently monitor MongoDB and host health. These are external monitoring integrations, not bundled MongoDB Community components. Some exporter collectors need extra read privileges: enable only required collectors and grant narrowly scoped roles after reviewing the exact exporter documentation. Keep exporter endpoints and their secrets private.', 'از MongoDB Exporter سازگار با Server، Prometheus برای جمع‌آوری و Grafana برای Dashboard استفاده کنید؛ Zabbix نیز می‌تواند MongoDB و Host Health را مستقل رصد کند. این‌ها Integration خارجی‌اند، نه Component همراه Community. بعضی Collectorها Read Permission بیشتری می‌خواهند؛ فقط Collector لازم را فعال و پس از بررسی مستندات همان Exporter، Role محدود بدهید. Endpoint و Secret مربوط به Exporter خصوصی بماند.')
table([('Metric / event', 'Metric / Event'), ('Alert intent', 'هدف Alert')], [
    [('Connections / operations', 'Connection / Operation'), ('Pool saturation, connection churn and abnormal throughput versus baseline.', 'اشباع Pool، Connection Churn و Throughput غیرعادی نسبت به مبنا.')],
    [('Query latency', 'Query Latency'), ('Sustained p95/p99 SLO breaches; separate reads/writes and timeouts.', 'نقض مستمر SLO در p95/p99؛ تفکیک Read/Write و Timeout.')],
    [('Replication lag / oplog window', 'Replication Lag / Oplog Window'), ('Lag approaching recovery budget or oplog retention; failed sync.', 'نزدیک‌شدن Lag به بودجه Recovery یا Retention Oplog؛ Sync ناموفق.')],
    [('Disk / inodes / I/O latency', 'Disk / Inode / I/O Latency'), ('Capacity and growth forecasts, queue delays and full-volume risk.', 'ظرفیت و پیش‌بینی رشد، تأخیر صف و خطر Volume پر.')],
    [('Memory / page faults / swap', 'Memory / Page Fault / Swap'), ('Correlate OS major faults and swap with cache eviction and disk reads; minor faults alone are not an incident.', 'Major Fault و Swap در OS را با Eviction و Read Disk مرتبط ببینید؛ Minor Fault به‌تنهایی Incident نیست.')],
    [('Replica status / primary elections', 'Replica Status / Primary Election'), ('No primary, unavailable member, repeated elections or unexpected topology changes.', 'نبود Primary، Member خارج از دسترس، Election مکرر یا تغییر غیرمنتظره Topology.')],
    [('Backup / certificates / logs', 'Backup / Certificate / Log'), ('Stale backup, failed restore drill, expiry approaching and security/storage log events.', 'Backup قدیمی، Restore Drill ناموفق، نزدیک‌شدن Expiry و Event امنیت/Storage در Log.')],
])
p('Collect per-member metrics and OS telemetry; monitoring only the primary hides a failed secondary. Define actionable thresholds, owners and runbooks. Test an alert and notification path, then test loss of a node in staging and verify the application recovers and the new primary is detected.', 'Metric هر Member و Telemetry سیستم‌عامل را جمع کنید؛ مانیتور فقط Primary، Secondary خراب را پنهان می‌کند. Threshold عملی، Owner و Runbook تعیین کنید. Alert و مسیر Notification را تست و سپس از دسترس خارج‌شدن یک Node را در Staging و بازیابی برنامه و تشخیص Primary جدید را بررسی کنید.')
link('https://www.mongodb.com/docs/manual/administration/monitoring/', 'Official self-managed monitoring metrics', 'Metricهای رسمی مانیتورینگ Self-Managed')

section('troubleshooting', '23. Troubleshooting', '۲۳. عیب‌یابی')
code(r'''sudo systemctl status mongod --no-pager
sudo journalctl -u mongod -n 100 --no-pager
sudo ss -lntp | grep 27017
ps aux | grep '[m]ongod'
df -h
df -i
free -h''')
table([('Problem', 'مشکل'), ('Cause', 'علت محتمل'), ('Diagnostic', 'تشخیص'), ('Fix', 'راهکار')], [
    [('mongod does not start', 'mongod Start نمی‌شود'), ('YAML error, unsupported CPU/kernel, missing dbPath', 'خطای YAML، CPU/Kernel ناسازگار، فقدان dbPath'), ('journalctl; mongod --version; lscpu', 'journalctl؛ mongod --version؛ lscpu'), ('Fix the first logged error; use supported packages/kernel and mounted storage.', 'اولین خطا را رفع؛ بسته/Kernel پشتیبانی‌شده و Storage Mountشده استفاده کنید.')],
    [('Permission denied', 'Permission Denied'), ('Wrong service owner, PEM/key mode, SELinux/AppArmor denial', 'مالک سرویس یا Mode کلید/PEM اشتباه، Denial در SELinux/AppArmor'), ('stat; namei -l; ausearch -m AVC', 'stat؛ namei -l؛ ausearch -m AVC'), ('Repair exact owner/path/label and parent traversal permissions.', 'مالک، مسیر و Label دقیق و Traverse والد را اصلاح کنید.')],
    [('Port 27017 unavailable', 'Port 27017 اشغال یا بسته است'), ('Another listener or source/zone mismatch', 'Listener دیگر یا Source/Zone نادرست'), ('ss -lntp; firewall rules; private route', 'ss -lntp؛ Rule Firewall؛ Route خصوصی'), ('Resolve the listener conflict or fix the restricted allowlist.', 'تعارض Listener یا Allowlist محدود را اصلاح کنید.')],
    [('Authentication failed', 'Authentication Failed'), ('Wrong secret, user database or authSource', 'Secret، Database کاربر یا authSource اشتباه'), ('connectionStatus after valid login; userAdmin usersInfo', 'connectionStatus پس از Login معتبر؛ usersInfo توسط مدیر'), ('Use appdb for appuser, admin for admins; rotate through authorized admin.', 'برای appuser از appdb و برای مدیر از admin استفاده؛ Rotation با مدیر مجاز انجام دهید.')],
    [('Connection refused / TLS failure', 'Connection Refused / خطای TLS'), ('Stopped service, wrong bindIp, hostname/CA mismatch', 'سرویس Stop، bindIp اشتباه، عدم تطبیق Hostname/CA'), ('systemctl; ss; getent hosts; TLS client logs', 'systemctl؛ ss؛ getent hosts؛ Log Client TLS'), ('Restore listener and DNS; install trusted CA and matching certificate.', 'Listener و DNS را اصلاح؛ CA مورداعتماد و گواهی مطابق نصب کنید.')],
    [('Secondary not syncing', 'Secondary Sync نمی‌شود'), ('Peer unreachable, key/certificate mismatch, lag past oplog', 'Peer غیرقابل دسترسی، عدم تطبیق کلید/گواهی، Lag بیشتر از Oplog'), ('rs.status(); peer logs; disk/lag metrics', 'rs.status()؛ Log Peer؛ Metric Disk/Lag'), ('Fix connectivity/identity/capacity; perform planned initial resync if history is lost.', 'ارتباط/هویت/ظرفیت را رفع؛ در فقدان History، Initial Resync برنامه‌ریزی‌شده انجام دهید.')],
    [('Replica set has no primary', 'Replica Set بدون Primary'), ('Lost voting majority, partition or ineligible members', 'فقدان اکثریت رأی، Partition یا Member فاقدشرایط'), ('rs.status(); rs.conf(); peer DNS/firewall', 'rs.status()؛ rs.conf()؛ DNS/Firewall Peer'), ('Recover majority and eligible members; avoid forced reconfig as a routine fix.', 'اکثریت و Member واجدشرایط را بازیابی؛ Forced Reconfig را راهکار معمول نکنید.')],
    [('Disk full', 'Disk پر'), ('Data/oplog/log growth, dump on data disk, inode exhaustion', 'رشد Data/Oplog/Log، Dump روی Disk داده، Inode پر'), ('df -h; df -i; log/backup sizes', 'df -h؛ df -i؛ اندازه Log/Backup'), ('Expand storage or safely expire approved old backups/logs; never delete WiredTiger files.', 'Storage را توسعه یا Backup/Log قدیمی مجاز را حذف؛ فایل WiredTiger را حذف نکنید.')],
    [('High CPU', 'CPU بالا'), ('Scans, costly aggregation, connection churn or index build', 'Scan، Aggregation پرهزینه، Connection Churn یا Index Build'), ('top; explain; slow logs; connections', 'top؛ explain؛ Slow Log؛ Connection'), ('Tune the query/index/pool and validate under representative load.', 'Query/Index/Pool را بهینه و با Load نماینده اعتبارسنجی کنید.')],
    [('High disk I/O', 'Disk I/O بالا'), ('Working set exceeds cache, slow storage, backup/resync contention', 'Working Set بزرگ‌تر از Cache، Storage کند، تداخل Backup/Resync'), ('iostat -xz; vmstat; cache/lag/latency metrics', 'iostat -xz؛ vmstat؛ Metric Cache/Lag/Latency'), ('Reduce unnecessary scans, isolate backup load and provision measured RAM/IOPS.', 'Scan غیرضروری را کم، بار Backup را جدا و RAM/IOPS اندازه‌گیری‌شده تأمین کنید.')],
])
p('Do not use mongod --repair, forced replica reconfiguration, deletion of data files or turning off authorization as generic repairs. Preserve evidence and the latest recoverable backup. A member that fell behind the oplog may need a planned resync; verify a healthy source and capacity first.', 'mongod --repair، Forced Reconfiguration، حذف فایل Data یا خاموش‌کردن Authorization را Repair عمومی ندانید. Evidence و آخرین Backup قابل بازیابی را حفظ کنید. Member عقب‌مانده از Oplog ممکن است Resync برنامه‌ریزی‌شده بخواهد؛ ابتدا Source سالم و ظرفیت را تأیید کنید.')

section('verification', '24. Verification Checklist', '۲۴. چک‌لیست اعتبارسنجی')
code(r'''systemctl is-active mongod
sudo ss -lntp | grep 27017
# Bare mongosh is only for the initial non-TLS localhost bootstrap.
# For the secured deployment use:
mongosh --host '<HOSTNAME>' --port 27017 --tls \
  --tlsCAFile /etc/mongodb/ca.pem \
  --username appuser --password --authenticationDatabase appdb appdb''')
code(r'''db.runCommand({ ping: 1 })
db.runCommand({ connectionStatus: 1 })
db.getCollectionNames()''', 'javascript')
p('Expect active, listeners only on approved interfaces, ping ok:1 and authenticatedUsers containing appuser in appdb. Test from an allowed application host and confirm a disallowed host cannot connect. Verify unauthenticated collection access and plaintext connections fail. For replicas connect as mongoOps and inspect rs.status(): one primary, two healthy secondaries and acceptable lag.', 'انتظار active، Listener فقط روی Interface مجاز، ping با ok:1 و authenticatedUsers شامل appuser در appdb داشته باشید. از Host برنامه مجاز تست و عدم اتصال Host غیرمجاز را تأیید کنید. دسترسی Collection بدون احراز هویت و اتصال Plaintext باید رد شود. در Replica با mongoOps وصل و rs.status() را بررسی کنید: یک Primary، دو Secondary سالم و Lag قابل‌قبول.')
code(r'''// Authenticated mongoOps replica session:
rs.status()
rs.printSecondaryReplicationInfo()''', 'javascript')
p('Run a controlled failover drill in staging, exercise real application reads/writes with majority acknowledgment, verify alert delivery and restore a backup into a clean isolated target. These tests establish behavior beyond process liveness. Installation/configuration syntax was reviewed against documentation; the Linux deployment commands must still be executed and validated on your target servers.', 'در Staging آزمون Failover کنترل‌شده، Read/Write واقعی برنامه با تأیید اکثریت، دریافت Alert و Restore Backup در مقصد ایزوله تمیز را انجام دهید. این تست‌ها رفتاری فراتر از Process Liveness را مشخص می‌کنند. سینتکس نصب و پیکربندی با مستندات بررسی شده است؛ فرمان‌های استقرار Linux باید روی سرور مقصد شما اجرا و اعتبارسنجی شوند.')

section('best-practices', '25. Production Checklist', '۲۵. چک‌لیست Production')
p('Record evidence, an owner and a review date for each item before go-live. The empty status cells are intentional: reading this article does not make a deployment pass its checks.', 'پیش از Go-Live برای هر مورد Evidence، Owner و تاریخ Review ثبت کنید. سلول Status عمداً خالی است؛ مطالعه مقاله به معنی Passشدن استقرار نیست.')
checklist = [
    ('Authentication Enabled', 'احراز هویت فعال'), ('Dedicated App User', 'حساب اختصاصی Application'),
    ('Port 27017 Restricted', 'محدودبودن Port 27017'), ('bindIp Restricted', 'محدودبودن bindIp'),
    ('TLS Enabled', 'فعال‌بودن TLS'), ('Replica Set Enabled', 'فعال‌بودن Replica Set'),
    ('Backup Configured', 'پیکربندی Backup'), ('Monitoring Enabled', 'فعال‌بودن Monitoring'),
    ('Log Monitoring Enabled', 'فعال‌بودن Log Monitoring'), ('Disk Capacity Checked', 'بررسی ظرفیت Disk'),
    ('NTP Enabled', 'فعال و هماهنگ‌بودن NTP'), ('Firewall Enabled', 'فعال‌بودن Firewall'),
    ('Strong Passwords', 'گذرواژه‌های قوی'), ('Secrets Outside Source Code', 'Secret خارج از Source Code'),
    ('Restore Drill Passed / RPO / RTO Defined', 'Restore Drill موفق و RPO/RTO مشخص'),
    ('Internal Authentication / Certificate Renewal', 'احراز هویت داخلی و Renewal گواهی'),
    ('Failure Domains / Majority / Oplog Window', 'Failure Domain، اکثریت و Oplog Window'),
    ('Service Limits / SELinux / Storage Mounts', 'Service Limit، SELinux و Mountهای Storage'),
]
table([('Item', 'مورد'), ('Status', 'وضعیت')], [[pair, ('', '')] for pair in checklist])

section('conclusion', 'Operational Handover', 'تحویل عملیاتی')
p('Hand over the node inventory, effective configuration, DNS/PKI dependencies, secret owners, alert runbooks, backup retention and tested restore/failover procedures. Freeze the selected repository branch, review release notes before upgrades and maintain a rollback/recovery plan. A secure deployment is an ongoing operational responsibility.', 'Inventory Node، Config مؤثر، وابستگی DNS/PKI، Owner Secret، Runbook Alert، Retention Backup و مسیرهای Restore/Failover تست‌شده را تحویل دهید. شاخه مخزن منتخب را کنترل، پیش از Upgrade، Release Notes را مرور و برنامه Rollback/Recovery نگه دارید. استقرار امن مسئولیت عملیاتی مستمر است.')
link('https://www.mongodb.com/docs/manual/release-notes/9.0-upgrade-from-8.0/', 'Official supported upgrade path from 8.0 to 9.0', 'مسیر رسمی ارتقا از 8.0 به 9.0')

FAQ = [
    ('Can I install MongoDB 9.0 on Debian 12 with these commands?', 'No. The official matrix lists Debian 12 for 8.x and Debian 13 for 9.x. Use the 8.0 Bookworm repository in this guide or plan a supported OS upgrade.', 'آیا با این فرمان‌ها MongoDB 9.0 روی Debian 12 نصب می‌شود؟', 'خیر. ماتریس رسمی Debian 12 را برای 8.x و Debian 13 را برای 9.x فهرست می‌کند. از مخزن 8.0 Bookworm این راهنما یا ارتقای برنامه‌ریزی‌شده OS استفاده کنید.'),
    ('Does bindIp restrict which clients can connect?', 'No. It selects local listening interfaces. Firewall source rules restrict client addresses.', 'آیا bindIp تعیین می‌کند کدام Client وصل شود؟', 'خیر. Interface محلی شنود را انتخاب می‌کند. محدودیت آدرس Client با Source Rule در Firewall اعمال می‌شود.'),
    ('Is a replica set a backup?', 'No. It replicates writes, including accidental deletion. Keep independent backups and validate restores.', 'آیا Replica Set همان Backup است؟', 'خیر. Write از جمله حذف اشتباه را Replicate می‌کند. Backup مستقل نگه دارید و Restore را تست کنید.'),
    ('Do I need a load balancer in front of a replica set?', 'Normally no. A replica-aware driver discovers members and selects the appropriate server.', 'آیا Replica Set به Load Balancer نیاز دارد؟', 'معمولاً خیر. Driver آگاه از Replica، Memberها را کشف و Server مناسب را انتخاب می‌کند.'),
    ('Does a keyfile encrypt replication traffic?', 'No. It authenticates members. TLS encrypts traffic; current official guidance recommends X.509 membership authentication for production.', 'آیا KeyFile ترافیک Replication را رمز می‌کند؟', 'خیر. Memberها را احراز هویت می‌کند. TLS ترافیک را رمز می‌کند؛ راهنمای فعلی رسمی برای عضویت Production، X.509 را توصیه می‌کند.'),
    ('Can mongoAdmin perform every operational task with the three example roles?', 'No. Those roles are broad but do not include all cluster, backup and restore operations. Use dedicated operational roles.', 'آیا mongoAdmin با سه Role مثال همه عملیات را انجام می‌دهد؟', 'خیر. Roleها گسترده‌اند اما همه عملیات Cluster، Backup و Restore را ندارند. Role عملیاتی مجزا بدهید.'),
    ('Should I disable THP for MongoDB 8.0 and 9.0?', 'For supported x86_64/ARM64, follow the newer TCMalloc guidance to enable THP with its documented settings. Older-version recommendations differ.', 'آیا برای MongoDB 8.0 و 9.0 باید THP را خاموش کنم؟', 'روی x86_64/ARM64 پشتیبانی‌شده، راهنمای جدید TCMalloc برای فعال‌کردن THP با تنظیمات مستند را دنبال کنید. توصیه نسخه‌های قدیمی متفاوت است.'),
    ('Is a successful ping enough to approve production?', 'No. Verify authorization, network restrictions, TLS, replica health, application behavior, alerts and an isolated restore drill.', 'آیا ping موفق برای تأیید Production کافی است؟', 'خیر. Authorization، محدودیت شبکه، TLS، سلامت Replica، رفتار برنامه، Alert و Restore Drill ایزوله را بررسی کنید.'),
]
section('faq', 'Frequently Asked Questions', 'پرسش‌های متداول')
for q, a, fq, fa in FAQ:
    sub(q, fq)
    p(a, fa)

section('official-references', 'Official References', 'منابع رسمی')
p('Reviewed on 7 October 2026 using MongoDB documentation and the distribution manuals below. Current Linux installation instructions use a distribution selector; select the matching distribution and package method. The 9.0 Community repository definitions were additionally checked in MongoDB’s official documentation source. The signing-key URL responded successfully; repository metadata requests from this authoring network returned HTTP 403, so live package availability must be verified with apt-cache policy or dnf on the deployment network.', 'بررسی در ۷ اکتبر ۲۰۲۶ با مستندات MongoDB و راهنماهای رسمی توزیع‌های زیر انجام شده است. راهنمای Linux فعلی Selector توزیع دارد؛ توزیع و روش Package متناظر را انتخاب کنید. تعریف مخزن Community 9.0 علاوه بر آن در Source رسمی مستندات MongoDB تطبیق داده شد. URL کلید امضا موفق پاسخ داد؛ درخواست Metadata مخزن از شبکه نگارش HTTP 403 گرفت، بنابراین موجودبودن زنده Package باید با apt-cache policy یا dnf در شبکه استقرار تأیید شود.')
REFERENCES = [
    ('https://www.mongodb.com/docs/manual/release-notes/', 'Stable release index', 'فهرست نسخه Stable'),
    ('https://www.mongodb.com/docs/manual/release-notes/9.0/', '9.0 release notes and known issues', 'Release Notes و Known Issueهای 9.0'),
    ('https://www.mongodb.com/docs/community-platform-support/', 'Community supported platforms', 'پلتفرم‌های پشتیبانی‌شده Community'),
    ('https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=ubuntu&linux-method=pkg', 'Community Ubuntu installation', 'نصب Community در Ubuntu'),
    ('https://www.mongodb.com/docs/v8.0/tutorial/install-mongodb-on-debian/', 'Community 8.0 Debian 12 installation', 'نصب Community 8.0 در Debian 12'),
    ('https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=rhel&linux-method=pkg', 'Community RHEL installation and SELinux', 'نصب Community در RHEL و SELinux'),
    ('https://www.mongodb.com/docs/manual/reference/configuration-options/', 'Configuration file options', 'پارامترهای فایل Config'),
    ('https://www.mongodb.com/docs/manual/tutorial/configure-ssl/', 'TLS configuration', 'پیکربندی TLS'),
    ('https://www.mongodb.com/docs/manual/tutorial/deploy-replica-set-with-keyfile-access-control/', 'Keyfile replica-set bootstrap', 'Bootstrap Replica Set با KeyFile'),
    ('https://www.mongodb.com/docs/manual/tutorial/configure-x509-member-authentication/', 'X.509 member authentication', 'احراز هویت عضو با X.509'),
    ('https://www.mongodb.com/docs/database-tools/mongodump/', 'Database Tools: mongodump', 'Database Tools: mongodump'),
    ('https://www.mongodb.com/docs/database-tools/mongorestore/', 'Database Tools: mongorestore', 'Database Tools: mongorestore'),
    ('https://www.mongodb.com/docs/manual/administration/monitoring/', 'Self-managed monitoring', 'مانیتورینگ Self-Managed'),
    ('https://ubuntu.com/server/docs/how-to/security/firewalls/', 'Ubuntu firewall', 'Firewall رسمی Ubuntu'),
    ('https://www.debian.org/doc/manuals/debian-reference/ch05.en.html', 'Debian network administration', 'مدیریت شبکه Debian'),
    ('https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters', 'RHEL firewalld', 'firewalld رسمی RHEL'),
]
for url, en, fa in REFERENCES: link(url, en, fa)
parts.append('</section>')

banner = '/assets/img/articles/banners/mongodb-installation-production-banner.png'
url = 'https://meetaj.ir/articles/' + SLUG
localizations = {lang: {'title': TITLE[lang], 'meta_title': TITLE[lang], 'description': DESC[lang], 'keywords': KEYWORDS, 'faq': [[q, a] if lang == 'en' else [fq, fa] for q, a, fq, fa in FAQ]} for lang in ['en', 'fa']}
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
<img class="article-hero-thumbnail" src="{banner}" width="1000" height="1000" alt="MongoDB Community Linux production deployment with TLS security, replication and monitoring">
</section><article class="article-body" lang="en" dir="ltr">
{chr(10).join(parts)}
</article></main></body></html>
'''
(ROOT / 'resources/legacy/articles' / f'{SLUG}.html').write_text(html, encoding='utf-8')
PACKAGE.mkdir(parents=True, exist_ok=True)
for lang, body in markdown.items():
    (PACKAGE / f'article.{lang}.md').write_text('# ' + TITLE[lang] + '\n\n' + '\n'.join(body), encoding='utf-8')
(PACKAGE / 'metadata.json').write_text(json.dumps({'slug': SLUG, 'reviewed_at': '2026-10-07', 'server_branches': {'ubuntu': '9.0', 'debian12': '8.0', 'rhel': '9.0'}, 'localizations': localizations, 'references': REFERENCES}, ensure_ascii=False, indent=2), encoding='utf-8')
print(f'Built {SLUG}: {len(toc)} bilingual sections, {html.count("<pre")} code blocks')

