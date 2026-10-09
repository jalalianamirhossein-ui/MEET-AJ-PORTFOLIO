"""Build the bilingual Zabbix package using the existing editorial conventions."""
from pathlib import Path
from html import escape
import json, shutil, zipfile
ROOT = Path(__file__).resolve().parents[1]
SLUG = 'zabbix-server-linux-windows-agents-backup'
SOURCE = ROOT / 'resources/content/articles' / SLUG
TITLE = {'en': 'Zabbix Server Installation and Configuration: Linux & Windows Agents, Security, Backup and Recovery', 'fa': 'آموزش جامع نصب و راه‌اندازی Zabbix Server، مانیتورینگ لینوکس و ویندوز، امنیت، بکاپ و بازیابی'}
DESC = {'en': 'Deploy Zabbix 7.0 LTS on Ubuntu 24.04 with PostgreSQL, Nginx, Linux and Windows Agent 2, TLS, alerts, verified daily backups and disaster recovery.', 'fa': 'نصب Zabbix 7.0 LTS روی Ubuntu 24.04 با PostgreSQL و Nginx، Agent 2 لینوکس و ویندوز، TLS، هشدار، بکاپ روزانه معتبر و بازیابی بحران.'}
KEYWORDS = ['Zabbix 7.0 LTS', 'Ubuntu 24.04', 'PostgreSQL 16', 'Nginx', 'PHP-FPM', 'Zabbix Agent 2', 'Windows Server 2022', 'Windows Server 2025', 'TLS PSK', 'pg_dump', 'pgBackRest', 'PITR', 'Disaster Recovery', 'نصب زبیکس', 'بکاپ زبیکس']
parts, toc = [], []
md = {lang: ['# '+TITLE[lang], '', DESC[lang], ''] for lang in TITLE}
def dual(tag, en, fa, attrs=''):
    if tag in ('a','figcaption'): return f'<{tag} {attrs}>'+dual('span',en,fa)+f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en,quote=True)}" data-fa="{escape(fa,quote=True)}">{escape(en)}</{tag}>'
def p(en,fa):
    parts.append(dual('p',en,fa))
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend([value,''])
def section(id,en,fa):
    if toc: parts.append('</section>')
    toc.append((id,en,fa)); parts.append(f'<section id="{id}">'+dual('h2',en,fa))
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend(['## '+value,''])
def sub(en,fa):
    parts.append(dual('h3',en,fa))
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend(['### '+value,''])
def code(value,lang='bash'):
    value=value.strip(); parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value)}</code></pre>')
    for locale in md: md[locale].extend([chr(96)*3+lang,value,chr(96)*3,''])
def filecode(name,lang): code((SOURCE/name).read_text(encoding='utf-8'),lang)
def items(rows):
    parts.append('<ul>'+''.join(dual('li',en,fa) for en,fa in rows)+'</ul>')
    for lang,i in [('en',0),('fa',1)]: md[lang].extend(['- '+row[i] for row in rows]+[''])
def table(headers,rows):
    parts.append('<table><thead><tr>'+''.join(dual('th',*h) for h in headers)+'</tr></thead><tbody>'+''.join('<tr>'+''.join(dual('td',*c) for c in row)+'</tr>' for row in rows)+'</tbody></table>')
    for lang,i in [('en',0),('fa',1)]: md[lang].extend(['| '+' | '.join(h[i] for h in headers)+' |','| '+' | '.join('---' for h in headers)+' |']+['| '+' | '.join(c[i] for c in row)+' |' for row in rows]+[''])
def link(url,en,fa):
    parts.append('<p>'+dual('a',en,fa,f'href="{escape(url,quote=True)}" rel="noopener"')+'</p>')
    target='https://meetaj.ir'+url if url.startswith('/articles/') else url
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend([f'[{value}]({target})',''])
def ref(path,en,fa): link('https://www.zabbix.com/documentation/7.0/en/manual/'+path,en,fa)
def figure(name,en,fa):
    src='/assets/img/articles/content/'+name
    parts.append(f'<figure><img src="{src}" width="1920" height="1080" loading="lazy" decoding="async" alt="{escape(en,quote=True)}" data-en-alt="{escape(en,quote=True)}" data-fa-alt="{escape(fa,quote=True)}">'+dual('figcaption',en,fa)+'</figure>')
    for lang,value in [('en',en),('fa',fa)]: md[lang].extend([f'![{value}]({src})','',value,''])

section('introduction','Introduction and deployment scope','مقدمه و محدوده استقرار')
p('Enterprise Zabbix Monitoring: Server Installation, Linux & Windows Agents, Security Hardening, Backup & Disaster Recovery. This guide follows a fresh dedicated deployment through operational handover, with reusable configuration downloads and explicit staging acceptance requirements.', 'مانیتورینگ سازمانی Zabbix: نصب Server، Agent لینوکس و ویندوز، امن‌سازی، بکاپ و بازیابی بحران. راهنما استقرار اختصاصی جدید را تا تحویل عملیاتی با فایل تنظیم قابل استفاده مجدد و الزام پذیرش Staging دنبال می‌کند.')
section('architecture','1. Enterprise Zabbix architecture','۱. معماری سازمانی Zabbix')
p('This runbook builds a dedicated Ubuntu Server 24.04 monitoring server and enrolls Ubuntu and Windows Server 2022/2025 endpoints. Installation, alerts, security, backups and recovery form one operational lifecycle. The central example is one failure domain; a branch proxy buffers WAN interruptions but does not replace server/database high availability.', 'این راهنما سرور اختصاصی مانیتورینگ Ubuntu Server 24.04 و میزبان Ubuntu و Windows Server 2022/2025 را پوشش می‌دهد. نصب، هشدار، امنیت، بکاپ و بازیابی یک چرخه عملیاتی هستند. سرور مرکزی مثال یک دامنه خرابی است؛ Proxy شعبه قطع WAN را بافر می‌کند اما جایگزین دسترس‌پذیری بالای Server/Database نیست.')
figure('zabbix-enterprise-architecture.png','Enterprise topology: HTTPS operators, TLS agents and branch proxy, local PostgreSQL and off-site backup','توپولوژی سازمانی: اپراتور HTTPS، Agent و Proxy با TLS، PostgreSQL محلی و بکاپ Off-site')
table([('Component','جزء'),('Responsibility','مسئولیت')],[
 [('Server','Server'),('Schedules collection, accepts data, evaluates triggers and executes notification actions.','زمان‌بندی جمع‌آوری، دریافت داده، ارزیابی Trigger و اجرای Action اعلان.')],
 [('Frontend / Nginx / PHP-FPM','Frontend / Nginx / PHP-FPM'),('HTTPS UI and JSON-RPC API; PHP accesses PostgreSQL and selected server functions.','رابط HTTPS و API مبتنی بر JSON-RPC؛ PHP به PostgreSQL و عملکرد منتخب Server دسترسی دارد.')],
 [('PostgreSQL','PostgreSQL'),('Hosts, users, templates, items, triggers, actions, events, history, trends and audit records.','Host، User، Template، Item، Trigger، Action، Event، History، Trend و رکورد Audit.')],
 [('Agent and Agent 2','Agent و Agent 2'),('Classic agent is a lightweight collector; Agent 2 adds plugins. Use one service for these ports; official OS agent templates support Agent 2.','Agent کلاسیک جمع‌آوری‌کننده سبک و Agent 2 دارای Plugin است. برای این Portها یک سرویس انتخاب کنید؛ Template رسمی OS با Agent 2 کار می‌کند.')],
 [('Proxy','Proxy'),('Collects and buffers branch data, forwards to Server; does not evaluate central triggers/actions independently.','جمع‌آوری و بافر داده شعبه و ارسال به Server؛ Trigger/Action مرکزی مستقل اجرا نمی‌کند.')]])
p('An item is a measurement/key such as system.uptime. Templates group reusable items, discovery rules, triggers and graphs. Triggers evaluate stored values; PROBLEM and recovery transitions create events. Problems shows current incidents, Events records transitions, dashboards display health/trends, and actions notify users through media types.', 'Item اندازه‌گیری و Key مانند system.uptime است. Template، Item، Discovery، Trigger و Graph قابل استفاده مجدد را گروه‌بندی می‌کند. Trigger مقدار ذخیره‌شده را ارزیابی؛ تغییر PROBLEM و Recovery رویداد می‌سازد. Problems رخداد جاری، Events تغییر وضعیت، Dashboard سلامت/روند و Action اعلان به کاربر با Media Type را نشان می‌دهد.')
code('''Management VPN 192.0.2.0/24 -> HTTPS 443 -> zabbix.example.com 192.0.2.10
Central: Nginx -> PHP-FPM -> PostgreSQL 127.0.0.1:5432
         Zabbix Server -> PostgreSQL 127.0.0.1:5432
linux-app-01   198.51.100.21 -> TLS 10051 -> Server
windows-app-01 198.51.100.22 -> TLS 10051 -> Server
Server -> TLS 10050 -> agents (optional passive checks)
Branch proxy 203.0.113.20 -> TLS 10051 -> Server
Backup -> encrypted off-site repository 203.0.113.30''','text')
p('Active checks: agent connects to ServerActive TCP/10051, obtains its item list using the exact Hostname and sends values. Passive checks: Server or assigned Proxy connects to agent TCP/10050; Server is the allowed polling-source list. Proxy-assigned hosts must use that proxy in both settings. Neither setting creates a frontend host.', 'Active: Agent به ServerActive روی TCP/10051 وصل، با Hostname دقیق فهرست Item می‌گیرد و مقدار می‌فرستد. Passive: Server یا Proxy تعیین‌شده به TCP/10050 Agent متصل؛ Server فهرست Source مجاز Poll است. میزبان سپرده‌شده به Proxy در هر دو تنظیم از همان Proxy استفاده کند. هیچ تنظیمی Host در Frontend نمی‌سازد.')
ref('concepts','Monitoring concepts','مفاهیم مانیتورینگ')
ref('distributed_monitoring/proxies','Official proxy architecture','معماری رسمی Proxy')

section('prerequisites','2. Server preparation and prerequisites','۲. آماده‌سازی و پیش‌نیاز سرور')
p('Every example address is RFC 5737 documentation space, not a usable deployment address. Replace networks/domain with approved values. Reserve a static IP or DHCP reservation, forward/reverse DNS, console access and a maintenance window. Keep AppArmor/firewalls enabled. Netplan can disconnect SSH: use netplan try from a console and the existing static-IP guide.', 'همه IPها فضای مستند RFC 5737 هستند، نه آدرس قابل استفاده استقرار. شبکه/دامنه را با مقدار تأییدشده عوض کنید. IP ثابت یا DHCP Reservation، DNS مستقیم/معکوس، Console و Maintenance آماده کنید. AppArmor/Firewall فعال بماند. Netplan ممکن است SSH را قطع کند؛ netplan try از Console و راهنمای IP ثابت موجود استفاده شود.')
code('''sudo apt update
sudo apt full-upgrade
sudo hostnamectl set-hostname zabbix.example.com
hostnamectl
getent ahostsv4 zabbix.example.com
ip -br address
ip route
sudo apt install -y chrony ufw curl ca-certificates openssl netcat-openbsd
sudo systemctl enable --now chrony
chronyc tracking
chronyc sources -v
timedatectl
test -e /var/run/reboot-required && cat /var/run/reboot-required || true
free -h
df -hT
lsblk -f
sudo aa-status''')
p('Expected: DNS reaches the reserved IP, routes reach approved update/DNS/NTP services and chronyc has a synchronized selected source. Configure approved NTP sources in /etc/chrony/chrony.conf, restart and recheck. Reboot in maintenance if needed and repeat health checks; clock errors break certificates, timestamps and escalation timing.', 'انتظار: DNS به IP رزروشده، Route به Update/DNS/NTP مجاز و chronyc دارای منبع همگام منتخب باشد. منبع NTP تأییدشده در /etc/chrony/chrony.conf تنظیم، Restart و بررسی شود. Reboot لازم در Maintenance و تکرار Check؛ زمان غلط Certificate، Timestamp و Escalation را مختل می‌کند.')
table([('Port','Port'),('Source / purpose','Source و کاربرد')],[
 [('22/tcp','22/tcp'),('Management VPN only; adapt the actual SSH port before UFW.','فقط VPN مدیریت؛ Port واقعی SSH پیش از UFW لحاظ شود.')],
 [('443/tcp','443/tcp'),('Operator network to HTTPS frontend; setup HTTP remains loopback-only.','شبکه Operator به Frontend HTTPS؛ HTTP Setup فقط Loopback.')],
 [('10051/tcp','10051/tcp'),('Approved active agents/proxies to Server; also used by trappers.','Agent/Proxy فعال مجاز به Server؛ Trapper نیز همین Port.')],
 [('10050/tcp','10050/tcp'),('Assigned Server/Proxy to Agent for passive checks.','Server/Proxy تعیین‌شده به Agent برای Passive.')],
 [('5432/tcp','5432/tcp'),('PostgreSQL loopback only; no endpoint/public exposure.','PostgreSQL فقط Loopback؛ بدون دسترسی Endpoint/عمومی.')],
 [('Egress','خروجی'),('Approved DNS/NTP, HTTPS updates/Telegram, SMTP relay and backup transport.','DNS/NTP مجاز، HTTPS بسته/Telegram، SMTP Relay و Transport بکاپ.')]])
code('''# Confirm actual SSH port, VPN access and console recovery before enabling UFW.
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from 192.0.2.0/24 to any port 22 proto tcp
sudo ufw allow from 192.0.2.0/24 to any port 443 proto tcp
sudo ufw allow from 198.51.100.21 to any port 10051 proto tcp
sudo ufw allow from 198.51.100.22 to any port 10051 proto tcp
sudo ufw allow from 203.0.113.20 to any port 10051 proto tcp
sudo ufw enable
sudo ufw status numbered
sudo ss -lntp''')
p('Capacity starting point for a modest installation: 4 vCPU, 16 GiB RAM, reliable SSD, measured IOPS and a separately mounted backup volume. This is planning guidance, not a minimum/throughput promise. Measure new values per second, write latency, queue and cache utilization. 3,000 items every 60s give 50 values/s and 129.6 million samples over 30 days. Include indexes, trends, logs, WAL and maintenance headroom; reserve at least 30% free storage. Size /backup for database estimates and retention. Separate the database and adopt physical backups for large installations.', 'نقطه شروع نصب متوسط کوچک: 4 vCPU، حافظه 16 GiB، SSD مطمئن، IOPS اندازه‌گیری‌شده و Volume بکاپ Mount‌شده جدا. راهنمای ظرفیت است، نه حداقل/تضمین Throughput. NVPS، تأخیر Write، Queue و Cache اندازه‌گیری شود. ۳۰۰۰ Item هر 60s، برابر ۵۰ مقدار در ثانیه و ۱۲۹٫۶ میلیون نمونه طی ۳۰ روز است. Index، Trend، Log، WAL و فضای Maintenance لحاظ؛ حداقل ۳۰٪ آزاد رزرو کنید. /backup براساس حجم DB و Retention؛ برای مقیاس بزرگ DB جدا و بکاپ فیزیکی.')
link('/articles/set-static-ip-ubuntu-server-netplan','Related: safe Netplan static IP','مرتبط: IP ثابت ایمن Netplan')
ref('installation/requirements','Official compatibility and sizing','سازگاری و ظرفیت رسمی')

section('configuration','3. Installation: Server, PostgreSQL and the frontend','۳. نصب Server، PostgreSQL و Frontend')
figure('zabbix-server-installation-workflow.png','Ubuntu preparation, official repository, PostgreSQL schema, Zabbix services and HTTPS validation workflow','فرایند آماده‌سازی Ubuntu، Repository رسمی، Schema PostgreSQL، سرویس Zabbix و اعتبارسنجی HTTPS')
p('Verified 9 October 2026: the official lifecycle lists 7.0 as the latest released LTS; the 8.0 release note describes 8.0.0rc1, excluded here. Install the latest stable 7.0 maintenance build from the official repository. PostgreSQL 16, Ubuntu Nginx 1.24 and PHP 8.3 meet 7.0 requirements. Security revisions change: record actual APT candidates/installed versions instead of claiming a fixed revision is current. Keep Server, SQL scripts and Frontend on the same patch.', 'بررسی ۹ اکتبر ۲۰۲۶: Lifecycle رسمی 7.0 را آخرین LTS منتشرشده و Release Note نسخه 8.0 را 8.0.0rc1 معرفی می‌کند که استفاده نمی‌شود. آخرین Maintenance پایدار 7.0 از Repository رسمی نصب شود. PostgreSQL 16، Nginx 1.24 و PHP 8.3 در Ubuntu با نیاز 7.0 سازگارند. Revision امنیت تغییر می‌کند؛ Candidate/Version واقعی APT ثبت شود نه ادعای Revision ثابت جاری. Server، SQL Script و Frontend Patch یکسان داشته باشند.')
link('https://www.zabbix.com/life_cycle_and_release_policy','Official released and planned LTS lifecycle','Lifecycle رسمی LTS منتشرشده و برنامه‌ریزی‌شده')
link('https://www.zabbix.com/release_notes','Release notes: distinguish stable and RC','Release Note: تفکیک Stable و RC')
p('The official Ubuntu Noble amd64 package index was also checked: Server and Agent 2 latest stable entries are 1:7.0.31-1+ubuntu24.04. Release 7.0.31 was published on 22 September 2026. These are verified review-time values; keep the 7.0 repository branch and recheck candidates before each installation. Ubuntu security revisions for PostgreSQL/Nginx/PHP remain selected by maintained Ubuntu 24.04 repositories.', 'Index رسمی Ubuntu Noble amd64 نیز بررسی شد: آخرین Entry پایدار Server و Agent 2 برابر 1:7.0.31-1+ubuntu24.04 است. 7.0.31 در ۲۲ سپتامبر ۲۰۲۶ منتشر شد. این مقدار زمان Review است؛ شاخه Repository برابر 7.0 و Candidate پیش از هر نصب دوباره بررسی شود. Revision امنیت PostgreSQL/Nginx/PHP از Repository نگهداری‌شده Ubuntu 24.04 انتخاب می‌شود.')
link('https://www.zabbix.com/rn/rn7.0.31','Verified stable Zabbix 7.0.31 release notes','Release Note نسخه پایدار تأییدشده 7.0.31')
link('https://repo.zabbix.com/zabbix/7.0/ubuntu/dists/noble/main/binary-amd64/Packages','Official Ubuntu Noble amd64 package index','Index رسمی Package در Ubuntu Noble amd64')
code('''cd /tmp
curl --fail --location --proto '=https' --tlsv1.2 -O \\
  https://repo.zabbix.com/zabbix/7.0/ubuntu/pool/main/z/zabbix-release/zabbix-release_latest_7.0+ubuntu24.04_all.deb
dpkg-deb --info zabbix-release_latest_7.0+ubuntu24.04_all.deb
sudo dpkg -i zabbix-release_latest_7.0+ubuntu24.04_all.deb
sudo apt update
apt-cache policy zabbix-server-pgsql zabbix-agent2 zabbix-frontend-php postgresql-16 nginx php8.3-fpm
# Verify official stable 7.0.x candidates, no beta/RC/devel.
sudo apt install -y postgresql-16 postgresql-client-16 nginx php8.3-fpm \\
  php8.3-pgsql php8.3-bcmath php8.3-mbstring php8.3-xml php8.3-gd php8.3-curl \\
  zabbix-server-pgsql zabbix-frontend-php zabbix-nginx-conf zabbix-sql-scripts zabbix-agent2 zabbix-get
sudo systemctl stop zabbix-server zabbix-agent2 nginx
sudo systemctl enable --now postgresql
pg_lsclusters
dpkg-query -W zabbix-server-pgsql zabbix-agent2 zabbix-frontend-php postgresql-16 nginx php8.3-fpm
zabbix_server -V
zabbix_agent2 -V
php -v
nginx -v''')
p('Verify bootstrap URL against the official selector. HTTPS protects bootstrap transport; APT validates signed repository metadata. Never use trusted=yes or --allow-unauthenticated. Put allowlisted firewall rules in place before package installation because packages can start services; enroll no hosts until TLS is ready.', 'URL نصب Repository با Selector رسمی تطبیق داده شود. HTTPS از Bootstrap Transport و APT از Metadata امضاشده محافظت می‌کند. trusted=yes یا --allow-unauthenticated ممنوع. چون بسته ممکن است Start کند، قبل نصب Firewall محدود برقرار؛ تا TLS آماده نیست Host ثبت نشود.')
link('https://www.zabbix.com/download?zabbix=7.0&os_distribution=ubuntu&os_version=24.04&components=server_frontend_agent&db=pgsql&ws=nginx','Official Ubuntu 24.04 PostgreSQL/Nginx selector','Selector رسمی Ubuntu 24.04 و PostgreSQL/Nginx')
sub('Restricted role, authentication and initial schema','Role محدود، احراز هویت و Schema اولیه')
code(r'''sudo -u postgres psql -X -v ON_ERROR_STOP=1 <<'SQL'
SET password_encryption = 'scram-sha-256';
CREATE ROLE zabbix LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION;
SQL
# Interactive prompt: no password in shell history.
sudo -u postgres psql -X -c '\password zabbix'
sudo -u postgres createdb -O zabbix -E UTF8 -T template0 zabbix
sudo -u postgres psql -X -d zabbix -c 'REVOKE ALL ON DATABASE zabbix FROM PUBLIC;'
sudo -u postgres psql -X -d zabbix -c 'REVOKE CREATE ON SCHEMA public FROM PUBLIC;'
sudo -u postgres psql -X -d zabbix -c 'GRANT ALL ON SCHEMA public TO zabbix;'
sudoedit /etc/postgresql/16/main/postgresql.conf
sudoedit /etc/postgresql/16/main/pg_hba.conf''')
code('''# postgresql.conf
listen_addresses = '127.0.0.1'
password_encryption = 'scram-sha-256'
# pg_hba.conf: place before broader host rules; preserve local admin peer access
local all     postgres                   peer
local zabbix  zabbix                     peer
host  zabbix  zabbix  127.0.0.1/32        scram-sha-256
host  all     all     0.0.0.0/0           reject
host  all     all     ::/0                reject''','conf')
code(r'''sudo pg_ctlcluster 16 main restart
sudo -u postgres psql -X -c "SELECT line_number,error FROM pg_hba_file_rules WHERE error IS NOT NULL;"
sudo -u postgres psql -X -c 'SHOW listen_addresses;'
sudo -u postgres psql -X -c '\du zabbix'
# INITIALIZATION ONLY: NEW EMPTY database, never before a database restore.
set -o pipefail
zcat /usr/share/zabbix-sql-scripts/postgresql/server.sql.gz | sudo -u zabbix psql -X -v ON_ERROR_STOP=1 -d zabbix
sudo -u zabbix psql -X -d zabbix -c 'SELECT mandatory,optional FROM dbversion;'
psql -h 127.0.0.1 -U zabbix -W -d zabbix -c 'SELECT current_user,current_database();'
sudoedit /etc/zabbix/zabbix_server.conf''')
code('''# Merge into package file; avoid duplicate active parameters.
DBHost=127.0.0.1
DBName=zabbix
DBUser=zabbix
# Set the actual DBPassword using sudoedit; never include it in downloads.
ListenIP=127.0.0.1,192.0.2.10
ListenPort=10051
# Preserve package LogFile, PidFile, SocketDir and script paths.''','conf')
p('Expected: no pg_hba error rows, loopback PostgreSQL, one dbversion row, TCP login as zabbix. Put the real DBPassword in root:zabbix 0640 server configuration. Zabbix owns its schema and needs controlled schema upgrades; PostgreSQL superuser, role creation and unrelated database privileges are unnecessary.', 'انتظار: بدون ردیف خطای pg_hba، PostgreSQL محلی، یک ردیف dbversion و TCP Login با zabbix. DBPassword واقعی در فایل root:zabbix با 0640 ثبت شود. Zabbix مالک Schema و نیازمند Upgrade کنترل‌شده است؛ Superuser، ساخت Role و مجوز DB غیرمرتبط لازم نیست.')
sub('Packaged Nginx/PHP-FPM and protected initial administration','Nginx/PHP-FPM بسته و مدیریت اولیه محافظت‌شده')
p('Keep packaged application locations/fastcgi rules. Edit /etc/zabbix/nginx.conf (normally linked in /etc/nginx/conf.d/) to listen 127.0.0.1:8080 and server_name zabbix.example.com. Verify /etc/zabbix/php-fpm.conf is included under /etc/php/8.3/fpm/pool.d/ and its pool user/socket. Nginx must use that actual socket, normally unix:/var/run/php/zabbix.sock. Do not expose the HTTP installer.', 'Location/FastCGI بسته حفظ شود. /etc/zabbix/nginx.conf که معمولاً در /etc/nginx/conf.d/ لینک دارد، با listen برابر 127.0.0.1:8080 و server_name برابر zabbix.example.com تنظیم شود. Include فایل /etc/zabbix/php-fpm.conf در /etc/php/8.3/fpm/pool.d/ و User/Socket بررسی شود. Nginx از Socket واقعی معمولاً unix:/var/run/php/zabbix.sock استفاده کند. Installer HTTP عمومی نباشد.')
code('''sudoedit /etc/zabbix/nginx.conf
sudoedit /etc/zabbix/php-fpm.conf
# Pool: php_value[date.timezone] = Asia/Tehran
# Verify memory_limit >=128M, post_max_size >=16M, max_execution_time >=300,
# max_input_time >=300, upload_max_filesize >=2M.
sudo chown root:zabbix /etc/zabbix/zabbix_server.conf
sudo chmod 0640 /etc/zabbix/zabbix_server.conf
sudo php-fpm8.3 -t
sudo nginx -t
sudo systemctl enable --now php8.3-fpm nginx zabbix-server
sudo systemctl status zabbix-server php8.3-fpm nginx --no-pager
sudo journalctl -u zabbix-server -n 50 --no-pager
sudo tail -n 50 /var/log/zabbix/zabbix_server.log
curl -I -H 'Host: zabbix.example.com' http://127.0.0.1:8080/
# Operator workstation: encrypted tunnel for setup
ssh -L 8080:127.0.0.1:8080 admin@192.0.2.10''')
p('Through the SSH tunnel open http://127.0.0.1:8080 in the workstation browser. Complete prerequisite checks, PostgreSQL 127.0.0.1:5432, database/user zabbix and the real password, server name and time zone. Protect /etc/zabbix/web/zabbix.conf.php with root ownership and only the actual PHP pool group read access (0640); remove web-user write after setup. If manually transferred, delete the workstation copy securely.', 'از Tunnel، http://127.0.0.1:8080 در Browser ایستگاه کاری باز شود. Prerequisite، PostgreSQL روی 127.0.0.1:5432، Database/User برابر zabbix و Password واقعی، نام سرور و Timezone تکمیل شود. /etc/zabbix/web/zabbix.conf.php متعلق به root و فقط گروه واقعی Pool PHP دارای Read با 0640؛ Write وب پس از Setup حذف شود. کپی منتقل‌شده ایستگاه کاری امن حذف شود.')
p('Expected: services active, local HTTP 200/redirect and Reports → System information says Server running. Use initial case-sensitive Admin / zabbix only over the protected setup channel; immediately change the password, create a named administrator, disable unused guest access and enroll MFA. Apply chapter 8 HTTPS before operator access and remove the bootstrap listener. A login does not prove monitoring: verify item arrival and test notifications.', 'انتظار: سرویس Active، HTTP محلی 200/Redirect و Reports → System information برابر Server Running. Admin / zabbix اولیه حساس به حروف فقط در Setup محافظت‌شده؛ فوراً Password تغییر، Administrator نام‌دار، Guest بلااستفاده غیرفعال و MFA فعال شود. قبل دسترسی Operator، HTTPS فصل ۸ و حذف Listener اولیه. Login اثبات مانیتورینگ نیست؛ دریافت Item و اعلان تست شود.')
ref('installation/install_from_packages','Package installation and schema initialization','نصب بسته و Schema اولیه')
ref('installation/frontend','Frontend setup prerequisites','پیش‌نیاز Setup Frontend')

section('linux-agent','4. Install Agent 2 on Ubuntu Linux','۴. نصب Agent 2 روی Ubuntu Linux')
figure('zabbix-linux-windows-agent-topology.png','Active TLS to TCP/10051 and optional passive TLS on TCP/10050 for Linux and Windows Agent 2','TLS فعال به TCP/10051 و TLS غیرفعال اختیاری روی TCP/10050 برای Agent 2 لینوکس و ویندوز')
p('On linux-app-01 add the chapter 3 official 7.0 Ubuntu repository, then install only Agent 2. Server is the passive-source allowlist; ServerActive is the active destination; Hostname must match frontend Host name, not Visible name; ListenPort is 10050. Replace ListenIP with the real interface IP. Agent 2 runs in the foreground under systemd; do not add classic-agent StartAgents or daemonization settings.', 'روی linux-app-01، Repository رسمی 7.0 فصل ۳ سپس فقط Agent 2 نصب شود. Server فهرست Source مجاز Passive، ServerActive مقصد Active، Hostname برابر Host name در Frontend نه Visible name و ListenPort برابر 10050 است. ListenIP با IP واقعی Interface جایگزین شود. Agent 2 در Foreground تحت systemd است؛ StartAgents یا Daemonization کلاسیک اضافه نشود.')
code('''sudo apt update
sudo apt install -y zabbix-agent2 openssl
sudo systemctl stop zabbix-agent2
sudo install -d -o root -g zabbix -m 0750 /etc/zabbix/keys
sudo sh -c 'umask 027; openssl rand -hex 32 > /etc/zabbix/keys/agent2.psk'
sudo chown root:zabbix /etc/zabbix/keys/agent2.psk
sudo chmod 0640 /etc/zabbix/keys/agent2.psk
# Adapt the downloaded configuration BEFORE starting the service.
sudo install -o root -g zabbix -m 0640 zabbix-agent2-linux.conf /etc/zabbix/zabbix_agent2.conf
sudoedit /etc/zabbix/zabbix_agent2.conf
sudo ufw allow from 192.0.2.10 to any port 10050 proto tcp
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t agent.ping
sudo systemctl enable --now zabbix-agent2
sudo systemctl status zabbix-agent2 --no-pager
sudo journalctl -u zabbix-agent2 -n 50 --no-pager
sudo ss -lntp | grep ':10050'
nc -vz 192.0.2.10 10051''')
filecode('zabbix-agent2-linux.conf','conf')
p('Data collection → Hosts → Create host: Host name linux-app-01, group Linux servers, monitored by Server, Agent interface 198.51.100.21:10050. Link Linux by Zabbix agent active for active monitoring or Linux by Zabbix agent for passive monitoring. Agent 2 uses these official templates; do not attach both OS variants because keys overlap. Encryption: PSK for connections to and from host, identity linux-app-01-psk, generated per-host key. Transfer the key securely through the protected UI, never tickets/Git.', 'Data collection → Hosts → Create host: Host name برابر linux-app-01، Group برابر Linux servers، Monitored by برابر Server، Interface برابر 198.51.100.21:10050. Template برای Active برابر Linux by Zabbix agent active و برای Passive برابر Linux by Zabbix agent. Agent 2 همین Template رسمی را استفاده می‌کند؛ هر دو نوع با Key مشترک همزمان لینک نشود. Encryption هر دو جهت PSK، Identity برابر linux-app-01-psk و Key یکتای تولیدشده؛ انتقال امن از UI محافظت‌شده نه Ticket/Git.')
table([('Metric','معیار'),('Keys / validation','Key و اعتبارسنجی')],[
 [('CPU / load','CPU و Load'),('system.cpu.util[,idle], system.cpu.load[all,avg1]; observe a controlled workload.','system.cpu.util[,idle] و system.cpu.load[all,avg1]؛ بار کنترل‌شده مشاهده شود.')],
 [('RAM','RAM'),('vm.memory.size[available], vm.memory.size[total]; available memory includes reclaimable cache.','vm.memory.size[available] و vm.memory.size[total]؛ Available شامل Cache قابل بازیافت است.')],
 [('Disk / filesystem','Disk و Filesystem'),('vfs.fs.discovery, vfs.fs.size[/,pused]; review mount discovery and pseudo-filesystem exclusions.','vfs.fs.discovery و vfs.fs.size[/,pused]؛ Discovery Mount و حذف Filesystem مجازی بررسی شود.')],
 [('Network','شبکه'),('net.if.discovery, net.if.in[ens160], net.if.out[ens160]; use actual discovered interface.','net.if.discovery، net.if.in[ens160] و net.if.out[ens160]؛ Interface واقعی کشف‌شده استفاده شود.')],
 [('Uptime / service','Uptime و Service'),('system.uptime, custom.service.nginx; custom item configuration in chapter 6.','system.uptime و custom.service.nginx؛ تنظیم Item سفارشی فصل ۶.')]])
code('''sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'system.uptime'
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'vfs.fs.size[/,pused]'
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'custom.service.nginx'
# From the authorized Server: provision this per-host PSK copy securely, root-only.
sudo zabbix_get -s 198.51.100.21 -p 10050 -k agent.ping \\
  --tls-connect psk --tls-psk-identity linux-app-01-psk --tls-psk-file /root/linux-app-01.psk''')
p('Expected: ping=1, uptime positive, filesystem use 0–100 and service active when running. nc proves only TCP, local -t only key evaluation, zabbix_get a passive application/TLS exchange. Monitoring → Latest data must advance after item/configuration intervals for active acceptance; graphs need samples. Passive ZBX interface status is not active health. For active-only mode omit Server (disables Agent 2 passive checks), remove inbound 10050 and use only the active template. Retire the temporary diagnostic key through the secret lifecycle.', 'انتظار: Ping برابر 1، Uptime مثبت، مصرف Filesystem بین ۰ تا ۱۰۰ و سرویس Running برابر active. nc فقط TCP، -t فقط ارزیابی محلی Key و zabbix_get تبادل کاربردی/TLS Passive را ثابت می‌کند. برای پذیرش Active، Timestamp در Monitoring → Latest data پس از Interval جلو برود؛ Graph نمونه می‌خواهد. ZBX Passive وضعیت Active نیست. برای Active-only، Server حذف که Passive Agent 2 را غیرفعال می‌کند، ورودی 10050 حذف و فقط Template Active. Key تشخیصی موقت طبق چرخه Secret حذف شود.')
ref('appendix/config/zabbix_agent2','Official Linux Agent 2 parameters','پارامتر رسمی Agent 2 لینوکس')
link('https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/os/linux?at=refs%2Fheads%2Frelease%2F7.0','Official release/7.0 Linux templates','Template رسمی Linux در release/7.0')

section('windows-agent','5. Install Agent 2 on Windows Server','۵. نصب Agent 2 روی Windows Server')
p('Use supported x64 Windows Server 2022/2025. Download stable 7.0 Agent 2 amd64 OpenSSL MSI from the official page. Record vendor SHA256, approved patch and verified Authenticode publisher thumbprint. The unattended script requires 7.0.22+ because DONOTSTART was introduced then. A hash obtained from an untrusted source is insufficient; verify the valid Zabbix publisher signature independently.', 'Windows Server 2022/2025 پشتیبانی‌شده x64 استفاده شود. MSI پایدار Agent 2 نسخه 7.0 از نوع amd64 OpenSSL از صفحه رسمی دریافت شود. SHA256 سازنده، Patch تأییدشده و Thumbprint ناشر Authenticode معتبر ثبت شود. Script به 7.0.22+ نیاز دارد زیرا DONOTSTART آن زمان اضافه شد. Hash از Source نامطمئن کافی نیست؛ Signature معتبر ناشر Zabbix مستقل تأیید شود.')
link('https://www.zabbix.com/download_agents','Official stable Agent 2 MSI download','دانلود رسمی MSI پایدار Agent 2')
p('GUI: verify MSI, run wizard as administrator, accept license and select Agent 2 in default Program Files. Enter server 192.0.2.10, active server 192.0.2.10:10051, exact hostname windows-app-01 and unique TLS PSK identity. Protect key/config ACLs and inspect firewall rules before exposed startup. If GUI starts automatically, block network ingress until complete. For reproducible production use CONF plus DONOTSTART so no PSK value enters MSI properties or verbose logs.', 'GUI: MSI تأیید، Wizard با Administrator، قبول License و انتخاب Agent 2 در Program Files پیش‌فرض. Server برابر 192.0.2.10، Active برابر 192.0.2.10:10051، نام دقیق windows-app-01 و Identity یکتای TLS PSK. ACL فایل Key/Config و Firewall پیش از Start در معرض شبکه بررسی شود. اگر GUI خودکار Start کرد، Ingress تا تکمیل مسدود. Production تکرارپذیر از CONF و DONOTSTART استفاده کند تا مقدار PSK وارد Property/Log MSI نشود.')
filecode('zabbix-agent2-windows.conf','conf')
code(r'''# Elevated PowerShell; approved values come from the deployment record.
$msi = 'C:\Staging\approved-agent2-7.0-windows-amd64-openssl.msi'
Get-FileHash -LiteralPath $msi -Algorithm SHA256
Get-AuthenticodeSignature -LiteralPath $msi | Format-List Status,SignerCertificate
# Run the script with MsiPath, ExpectedSha256, ApprovedSignerThumbprint,
# ConfigPath, PskSourcePath and ServerAddress parameters.
# Silent MSI step AFTER protected PSK/configuration provisioning:
msiexec.exe /i "C:\Staging\approved-agent2.msi" /qn /norestart CONF="C:\ProgramData\Zabbix\install.conf" DONOTSTART=1 /l*v "C:\ProgramData\Zabbix\install.log"''','powershell')
p('The full script below validates hash/signature/TLS, uses language-independent SYSTEM/Administrators SIDs for ACLs, refuses existing installations, checks MSI codes 0/3010 and service identity, and restricts Defender Firewall to the monitoring server on Domain/Private profiles. Adapt inputs first; it does not fetch MSI or register hosts. A custom service account needs explicit key/config read and log write rights. Code 3010 requires scheduled reboot and startup verification.', 'Script کامل زیر Hash/Signature/TLS را بررسی، SID مستقل از زبان SYSTEM/Administrators برای ACL، رد نصب موجود، بررسی MSI 0/3010 و هویت سرویس و محدودکردن Defender Firewall به Server در Profile Domain/Private دارد. ورودی تطبیق داده شود؛ MSI دریافت یا Host ثبت نمی‌کند. حساب سرویس سفارشی Read فایل Key/Config و Write Log نیاز دارد. 3010 نیازمند Reboot زمان‌بندی‌شده و بررسی Startup است.')
filecode('install-zabbix-agent2.ps1','powershell')
code(r'''Get-Service -Name 'Zabbix Agent 2'
Get-CimInstance Win32_Service -Filter "Name='Zabbix Agent 2'" | Select-Object Name,State,StartMode,StartName,PathName
Set-Service -Name 'Zabbix Agent 2' -StartupType Automatic
Restart-Service -Name 'Zabbix Agent 2'
Get-NetTCPConnection -LocalPort 10050 -State Listen
Test-NetConnection -ComputerName 192.0.2.10 -Port 10051
Get-NetFirewallRule -Name 'Zabbix-Agent2-TLS' | Get-NetFirewallAddressFilter
Get-Content 'C:\ProgramData\Zabbix\zabbix_agent2.log' -Tail 50
& 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.exe' -c 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf' -t agent.ping
& 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.exe' -c 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf' -t 'service.info[Spooler,state]'
Get-WinEvent -FilterHashtable @{LogName='System'; Level=2} -MaxEvents 5''','powershell')
p('Register windows-app-01 under Data collection → Hosts, group Windows servers, Server monitoring, Agent interface 198.51.100.22:10050. Link Windows by Zabbix agent active, or Windows by Zabbix agent for passive mode, not both. Encryption in both directions: PSK, windows-app-01-psk, matching unique key. Verify Latest data: CPU utilization, available/total memory, disk capacity, network traffic, service discovery and system.uptime. Filter service discovery to required business services; stopped/trigger-started Windows services are often intentional.', 'windows-app-01 در Data collection → Hosts با Group برابر Windows servers، Server Monitoring و Interface برابر 198.51.100.22:10050 ثبت شود. Windows by Zabbix agent active یا برای Passive برابر Windows by Zabbix agent لینک، نه هر دو. Encryption هر دو جهت PSK، هویت windows-app-01-psk و Key یکتا منطبق. Latest Data برای CPU، حافظه Available/Total، ظرفیت Disk، ترافیک Network، Service Discovery و system.uptime بررسی. Discovery سرویس به سرویس تجاری ضروری محدود؛ بسیاری از سرویس Windows عمداً Stopped/Trigger-start هستند.')
p('Event Logs require additional active items. Create Zabbix agent (active) item eventlog[System,,"Error|Critical",,,,skip], information type Log, interval 30s. skip avoids old log replay at startup; document this choice. Add application-specific source/event-ID filters and inspect new records in Latest data. Grant only required log-reading privileges, especially for Security logs. Expected diagnostics: running automatic service, TcpTestSucceeded=True, agent.ping=1 and Spooler state=0 when running; network TCP success alone does not prove active delivery.', 'Event Log به Item فعال اضافی نیاز دارد. Item برابر Zabbix agent (active) با Key برابر eventlog[System,,"Error|Critical",,,,skip]، Type برابر Log و Interval برابر 30s بسازید. skip از Replay تاریخچه شروع جلوگیری؛ تصمیم مستند شود. Filter Source/Event ID برای برنامه و رکورد تازه در Latest Data بررسی. فقط مجوز Read لازم خصوصاً Security Log داده شود. انتظار: سرویس Running/Automatic، TcpTestSucceeded=True، agent.ping=1 و Spooler در Running برابر 0؛ موفقیت TCP اثبات تحویل Active نیست.')
ref('installation/install_from_packages/win_msi','Official MSI properties and GUI installation','Property رسمی MSI و نصب GUI')
ref('appendix/config/zabbix_agent2_win','Windows Agent 2 configuration','Configuration Agent 2 ویندوز')
ref('config/items/itemtypes/zabbix_agent/win_keys','Windows service and eventlog keys','Key مربوط به Service و Eventlog ویندوز')
link('https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/os/windows?at=refs%2Fheads%2Frelease%2F7.0','Official release/7.0 Windows templates','Template رسمی Windows در release/7.0')

section('monitoring','6. Practical monitoring and triggers','۶. مانیتورینگ و Trigger عملی')
p('Override OS template macros at host level for thresholds; do not edit vendor templates. Put custom service items in a local Enterprise Service Checks template. Under Hosts → Items create exact key, type Zabbix agent (active), correct value type and interval, then wait for samples before triggers. Reuse discovered OS items instead of duplicating keys. The expressions use concrete hosts and require referenced items to exist.', 'Threshold با Override Macro در Host تنظیم، نه ویرایش Template سازنده. Item سفارشی سرویس در Template محلی Enterprise Service Checks. در Hosts → Items، Key دقیق، Type برابر Zabbix agent (active)، Value Type و Interval صحیح؛ قبل Trigger چند Sample. Item کشف‌شده دوباره ساخته نشود. Expression دارای Host مشخص و نیازمند Item موجود است.')
table([('Item / value / interval','Item و Value و Interval'),('Purpose','کاربرد')],[
 [('vfs.fs.size[/,pused] / Float / 60s','vfs.fs.size[/,pused] / Float / 60s'),('Linux capacity; reuse discovered item.','ظرفیت Linux؛ استفاده Item کشف‌شده.')],
 [('vfs.fs.size[C:,pused] / Float / 60s','vfs.fs.size[C:,pused] / Float / 60s'),('Windows volume; confirm actual discovery key.','Volume Windows؛ تأیید Key واقعی Discovery.')],
 [('custom.service.nginx / Character / 30s','custom.service.nginx / Character / 30s'),('Fixed read-only UserParameter returns active/inactive/failed.','UserParameter ثابت Read-only، خروجی active/inactive/failed.')],
 [('service.info[Spooler,state] / Unsigned / 30s','service.info[Spooler,state] / Unsigned / 30s'),('Running=0; select a required service, Spooler is a lab example.','Running=0؛ سرویس ضروری انتخاب؛ Spooler مثال Lab.')],
 [('agent.ping / Unsigned / 60s','agent.ping / Unsigned / 60s'),('Data absence detects monitoring path loss, not necessarily physical host failure.','نبود داده نشان فقدان مسیر پایش است، نه الزاماً خرابی فیزیکی Host.')]])
code('''# Data collection -> Hosts -> Triggers
# Linux disk Warning; use separate Recovery expression below.
min(/linux-app-01/vfs.fs.size[/,pused],5m)>80
# Recovery:
max(/linux-app-01/vfs.fs.size[/,pused],5m)<75
# Windows disk High:
min(/windows-app-01/vfs.fs.size[C:,pused],5m)>90
# Linux CPU High (idle percent below 10 for the whole window):
max(/linux-app-01/system.cpu.util[,idle],5m)<10
# Recovery:
min(/linux-app-01/system.cpu.util[,idle],5m)>20
# Memory Average, less than 10% available:
max(/linux-app-01/vm.memory.size[available],5m)/last(/linux-app-01/vm.memory.size[total])<0.10
# Monitoring path unavailable, High:
nodata(/linux-app-01/agent.ping,5m)=1
# Windows service stopped for three samples, Average:
min(/windows-app-01/service.info[Spooler,state],#3)<>0
# Linux service stopped, Average:
last(/linux-app-01/custom.service.nginx)<>"active"''','text')
p('Create a named trigger with severity, expression, service/environment/team tags and operational data. For hysteresis set OK event generation=Recovery expression: the problem expression must become false and recovery condition true. Warning is capacity risk, Average degradation, High required service/monitoring loss, Disaster confirmed critical business outage. Add a higher disk threshold and dependency from the lower alarm. Dashboard: availability/problems, CPU/load, memory, free space, throughput and server queue.', 'Trigger نام‌دار با Severity، Expression، Tagهای service/environment/team و Operational Data بسازید. برای Hysteresis، OK event generation=Recovery expression؛ شرط Problem باید False و Recovery True شود. Warning ریسک ظرفیت، Average افت، High فقدان سرویس/پایش لازم و Disaster قطعی تأییدشده حیاتی. Threshold بالاتر Disk و Dependency هشدار پایین‌تر بسازید. Dashboard: Availability/Problem، CPU/Load، Memory، فضای آزاد، Throughput و Queue.')
p('In staging temporarily lower a test threshold or stop only a disposable service; verify PROBLEM, action and recovery and reset the test. Never fill a production disk or stop a business service for a drill. Combine nodata with an independent ICMP/reachability item when distinguishing agent/network failure from a dead server.', 'در Staging، Threshold تست موقت کم یا فقط سرویس قابل حذف Stop؛ PROBLEM، Action و Recovery بررسی و Reset شود. Disk Production پر یا سرویس تجاری برای Drill متوقف نشود. برای تفکیک خرابی Agent/Network از سرور مرده، nodata با ICMP/Reachability مستقل ترکیب شود.')
ref('config/triggers/expression','Supported trigger expressions and recovery','Expression و Recovery پشتیبانی‌شده')
ref('config/items/itemtypes/zabbix_agent','Supported agent keys and item types','Key و Type رسمی Agent')

section('notifications','7. Alerts and notifications','۷. هشدار و اعلان')
p('Trigger transitions create events; actions filter host group, tags and severity then run operations. Notification requires an enabled matching action, recipient with host read permission, enabled media, matching schedule/severity. Alerts → Media types → Email: approved SMTP relay, STARTTLS or SSL/TLS, certificate/hostname verification and dedicated credentials. Test with an approved recipient and verify mailbox delivery, not only SMTP submission.', 'تغییر Trigger رویداد می‌سازد؛ Action با Host Group، Tag و Severity فیلتر و Operation اجرا می‌کند. اعلان به Action فعال منطبق، دریافت‌کننده با Read میزبان، Media فعال و Schedule/Severity منطبق نیاز دارد. Alerts → Media types → Email: Relay مجاز، STARTTLS یا SSL/TLS، بررسی Certificate/Hostname و Credential اختصاصی. Test به گیرنده تأییدشده و اثبات تحویل Mailbox، نه صرف SMTP.')
items([
 ('Users → named operator → Media: Email, approved address, 1-7,00:00-24:00 and selected severities.','Users → اپراتور نام‌دار → Media: Email، آدرس مجاز، 1-7,00:00-24:00 و Severity منتخب.'),
 ('Alerts → Actions → Trigger actions: environment=production and severity >= Average; step 1 to Operations immediately.','Alerts → Actions → Trigger actions: environment=production و Severity حداقل Average؛ Step 1 فوراً Operations.'),
 ('Default operation step duration 10m; step 2 on-call, step 3 incident lead; add Recovery and acknowledgement Update operations.','Default Step Duration برابر 10m؛ Step 2 به On-call و Step 3 Incident Lead؛ Recovery و Update برای Acknowledgement.'),
 ('Include event ID, host, severity and authenticated problem URL; exclude credentials and unnecessary event-log details.','Event ID، Host، Severity و URL نیازمند Login؛ بدون Credential و جزئیات غیرضروری Event Log.')])
p('Telegram: use the bundled official webhook or import its release/7.0 media type and README. Create the bot with documented BotFather steps, protect token, initiate the chat/add bot to approved group, obtain chat ID per official instructions. Configure documented token parameter and user Send to chat ID; test media then complete trigger action. Approve outbound HTTPS and privacy for external incident data. Do not write undocumented shell integrations or include tokens in URLs/downloads.', 'Telegram: Webhook رسمی همراه یا Import Media Type و README شاخه release/7.0. Bot با مراحل مستند BotFather، حفاظت Token، شروع Chat/افزودن به Group مجاز و دریافت Chat ID طبق مرجع رسمی. Parameter Token مستند و Send to برابر Chat ID؛ Test Media سپس Action کامل. HTTPS خروجی و Privacy داده خارجی تأیید. Integration Shell غیرمستند یا Token در URL/Download نباشد.')
p('Data collection → Maintenance: hosts/tags, period, time zone and with/without data collection. With collection preserves history; without collection needs nodata review. Configure pause operations for suppressed problems. Dependencies make service alarms depend on upstream host/network loss and avoid duplicate pages. Check Reports → Action log for sent/failed details and Problems for suppression, acknowledgement and recovery.', 'Data collection → Maintenance: Host/Tag، دوره، Timezone و With/Without Data Collection. With تاریخچه حفظ؛ Without نیازمند بررسی nodata. Pause Operation برای Problem سرکوب‌شده تنظیم. Dependency سرویس به فقدان Host/Network بالادستی از اعلان تکراری جلوگیری می‌کند. Reports → Action log برای Sent/Failed و Problems برای Suppression/Acknowledgement/Recovery.')
ref('config/notifications/media/email','Email media type','Media Type ایمیل')
link('https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/media/telegram?at=refs%2Fheads%2Frelease%2F7.0','Official Telegram webhook and README','Webhook و README رسمی Telegram')
ref('config/notifications/action','Actions and escalation','Action و Escalation')
ref('maintenance','Maintenance collection modes','حالت Collection در Maintenance')
ref('config/triggers/dependencies','Trigger dependencies','Dependency مربوط به Trigger')

section('security','8. Security hardening','۸. امن‌سازی')
figure('zabbix-security-hardening.png','HTTPS and MFA, scoped roles/API, Agent TLS, firewall allowlists and local PostgreSQL security layers','لایه امنیت HTTPS و MFA، Role/API محدود، TLS Agent، Allowlist Firewall و PostgreSQL محلی')
sub('HTTPS and certificate lifecycle','HTTPS و چرخه Certificate')
p('Issue a real FQDN certificate from enterprise CA or supported ACME DNS-01 for private access. Install chain/key below with root 0600 key and 0700 directory; root Nginx master reads it. In the existing packaged Nginx server block replace bootstrap listen/server_name and add directives below; preserve application locations and FastCGI. Do not create competing blocks or generic PHP rules exposing /etc/zabbix.', 'Certificate FQDN واقعی از CA سازمان یا ACME DNS-01 پشتیبانی‌شده برای دسترسی خصوصی. Chain/Key در مسیر زیر، Key با root 0600 و Directory با 0700؛ Master Nginx با root می‌خواند. در Server Block موجود، listen/server_name اولیه جایگزین و Directive زیر؛ Location/FastCGI حفظ. Block متعارض یا PHP عمومی افشاکننده /etc/zabbix نباشد.')
code('''# Inside existing /etc/zabbix/nginx.conf server block:
listen 192.0.2.10:443 ssl;
server_name zabbix.example.com;
ssl_certificate /etc/ssl/zabbix/fullchain.pem;
ssl_certificate_key /etc/ssl/zabbix/privkey.pem;
ssl_protocols TLSv1.2 TLSv1.3;
ssl_session_cache shared:ZabbixTLS:10m;
ssl_session_timeout 1d;
add_header Strict-Transport-Security "max-age=31536000" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "same-origin" always;
# Keep packaged root/index/PHP location/deny rules/fastcgi_pass.
# Enable HSTS only after HTTPS and renewal validation.''','nginx')
code('''sudo install -d -o root -g root -m 0700 /etc/ssl/zabbix
# Securely install approved certificate and key before testing Nginx.
sudo chmod 0600 /etc/ssl/zabbix/privkey.pem
sudo chmod 0644 /etc/ssl/zabbix/fullchain.pem
sudo nginx -t
sudo php-fpm8.3 -t
sudo systemctl reload nginx
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com \\
  -CAfile /path/to/enterprise-ca.pem -verify_return_error </dev/null
sudo ss -lntp
sudo ufw status numbered''')
p('Expected: valid config, HTTPS 200/redirect, verified chain/FQDN; never curl -k. Confirm bootstrap 8080 gone and 5432 loopback-only. Schedule CA/ACME renewal, test it, run nginx -t before reload in deploy hook, and alert on expiry/failure. Set HTTPS PHP pool session.cookie_secure=1, session.cookie_httponly=1, session.cookie_samesite=Lax and retest sessions.', 'انتظار: Config معتبر، HTTPS 200/Redirect و Chain/FQDN تأیید؛ هرگز curl -k. حذف 8080 اولیه و محلی بودن 5432. Renewal CA/ACME زمان‌بندی و تست، Deploy Hook ابتدا nginx -t سپس Reload و Alert انقضا/شکست. Pool PHP HTTPS با session.cookie_secure=1، session.cookie_httponly=1 و session.cookie_samesite=Lax و تست Session.')
sub('Agent authentication and least privilege','احراز هویت Agent و کمترین مجوز')
p('Unique random per-host PSK, TLSConnect=psk, TLSAccept=psk and matching frontend directions. Shared fleet keys multiply compromise risk. Certificate alternative uses TLSConnect/TLSAccept=cert, TLSCAFile, TLSCertFile, TLSKeyFile and allowed issuer/subject; configure host UI constraints and renewal/revocation. HTTPS and agent certificates are separate. No unencrypted fallback or bypassed peer verification.', 'PSK تصادفی یکتای Host، TLSConnect=psk، TLSAccept=psk و جهت UI منطبق. Key مشترک Fleet ریسک نفوذ را تکثیر می‌کند. گزینه Certificate با TLSConnect/TLSAccept=cert، TLSCAFile، TLSCertFile، TLSKeyFile و Issuer/Subject مجاز؛ Constraint UI و Renewal/Revocation. Certificate HTTPS از Agent جدا است. بدون Fallback ساده یا دورزدن Peer Verification.')
code('''# Replace PSK settings for a reviewed certificate deployment:
TLSConnect=cert
TLSAccept=cert
TLSCAFile=/etc/zabbix/keys/ca.pem
TLSCertFile=/etc/zabbix/keys/agent-chain.pem
TLSKeyFile=/etc/zabbix/keys/agent.key
TLSServerCertIssuer=CN=Monitoring CA,O=Example
TLSServerCertSubject=CN=zabbix.example.com,O=Example''','conf')
items([
 ('Firewall and Agent Server source allowlists agree; restrict 10051, discovery and auto-registration.','Allowlist Firewall و Server Agent منطبق؛ محدودیت 10051، Discovery و Auto-registration.'),
 ('Linux agent runs as zabbix, DenyKey=system.run[*], UnsafeUserParameters=0 and fixed reviewed commands; no broad sudo/writable scripts.','Agent Linux با zabbix، DenyKey=system.run[*]، UnsafeUserParameters=0 و فرمان ثابت بررسی‌شده؛ بدون sudo گسترده/Script قابل نوشتن.'),
 ('PostgreSQL local SCRAM/peer, no trust; remote DB needs verify-full TLS and database-only firewall.','PostgreSQL محلی SCRAM/Peer، بدون trust؛ DB Remote نیازمند TLS verify-full و Firewall اختصاصی.'),
 ('Config, PSKs, web credentials, scripts and backups have restricted ownership/modes; review AppArmor denials, grant narrowly, never disable profile.','Config، PSK، Credential وب، Script و Backup دارای Owner/Mode محدود؛ Denial AppArmor بررسی و مجوز باریک، نه غیرفعال‌سازی Profile.')])
sub('Strong administration, RBAC, MFA, audit and API','مدیریت قوی، RBAC، MFA، Audit و API')
p('Users → User groups controls host-group access; User roles controls UI/API methods. Operators read-only, service owners limited to their hosts, named administrators few. Keep a vaulted emergency account with tested recovery. Zabbix 7.0 supports TOTP and Duo: Users → Authentication → MFA, enroll users and enforce group method after testing recovery/IdP behavior. Limit sessions/remove dormant accounts.', 'Users → User groups دسترسی Host Group و User roles متد UI/API را کنترل می‌کند. Operator Read-only، مالک سرویس فقط Host خودش و Administrator نام‌دار اندک. حساب اضطراری Vault با Recovery تست‌شده. Zabbix 7.0 با TOTP و Duo: Users → Authentication → MFA، Enroll و الزام روش Group بعد تست Recovery/IdP. Session محدود/حساب راکد حذف.')
p('Enable Administration → General → Audit log, review Reports → Audit log, define retention and central protected log forwarding. API: dedicated least-privilege service user, explicit role method allowlist, expiring API token in a secret manager, HTTPS only. Use /api_jsonrpc.php with Authorization: Bearer and JSON-RPC; prove an allowed read and denied write, rotate/revoke and audit. Never hard-code token or use query URLs.', 'Administration → General → Audit log فعال، Reports → Audit log بررسی، Retention و ارسال مرکزی Log محافظت‌شده. API: کاربر سرویس کم‌مجوز، Method Allowlist صریح Role، Token انقضادار در Secret Manager و فقط HTTPS. /api_jsonrpc.php با Authorization: Bearer و JSON-RPC؛ Read مجاز و Write ردشده، Rotate/Revoke و Audit. Token ثابت یا Query URL ممنوع.')
ref('encryption/using_pre_shared_keys','PSK authentication','احراز هویت PSK')
ref('encryption/using_certificates','Certificate encryption','رمزنگاری Certificate')
ref('web_interface/frontend_sections/users/authentication/mfa','Zabbix 7.0 MFA','MFA در Zabbix 7.0')
ref('web_interface/frontend_sections/users/user_roles','Role/API controls','کنترل Role/API')
ref('api','Supported API authentication','احراز هویت API رسمی')
link('https://nginx.org/en/docs/http/configuring_https_servers.html','Nginx HTTPS reference','مرجع HTTPS در Nginx')

section('database-backup','9. Database and configuration backup','۹. بکاپ Database و Configuration')
p('PostgreSQL is the core recovery asset: hosts, templates, users/permissions, items, triggers, actions, events, history, trends and audit. Template exports or /etc/zabbix alone cannot restore the system. Back up DB plus configuration as one documented set, record packages/extensions/time zone and secret dependencies. This example uses PostgreSQL 16 without TimescaleDB; extension deployments need their compatible documented procedure.', 'PostgreSQL دارایی اصلی Recovery است: Host، Template، User/Permission، Item، Trigger، Action، Event، History، Trend و Audit. Export Template یا /etc/zabbix به‌تنهایی سیستم را برنمی‌گرداند. DB و Config یک مجموعه مستند، Package/Extension/Timezone و Dependency Secret ثبت شود. مثال PostgreSQL 16 بدون TimescaleDB است؛ Extension به روش سازگار مستند خودش نیاز دارد.')
code('''# Protected mounted /backup volume, on the database host.
sudo install -d -o root -g root -m 0700 /backup/zabbix/manual
sudo bash <<'BASH'
set -Eeuo pipefail
umask 077
stamp=$(date -u +%Y%m%dT%H%M%SZ)
target="/backup/zabbix/manual/$stamp"
mkdir -m 0700 "$target"
runuser -u postgres -- pg_dump -h /var/run/postgresql --role=zabbix \\
  -d zabbix -Fc -Z 6 --no-owner --no-acl > "$target/zabbix.dump"
pg_restore --list "$target/zabbix.dump" > "$target/database-toc.txt"
pg_restore --file=/dev/null "$target/zabbix.dump"
cd "$target"
sha256sum zabbix.dump database-toc.txt > SHA256SUMS
sha256sum --check SHA256SUMS
BASH''')
p('pg_dump uses a consistent MVCC snapshot during ordinary writes; avoid schema upgrades during the dump. Custom format compresses and supports selective/parallel restore. --no-owner/--no-acl allows a recreated restricted role to own objects. Database dump omits cluster roles and OS config: recreate role and archive configuration separately. --list checks catalog; --file=/dev/null decodes all archive data; SHA256 detects later corruption. Only an isolated real restore proves recoverability.', 'pg_dump هنگام Write عادی Snapshot سازگار MVCC می‌گیرد؛ Schema Upgrade همزمان نباشد. Custom Format فشرده و Restore انتخابی/موازی دارد. --no-owner/--no-acl اجازه مالکیت Role محدود بازسازی‌شده می‌دهد. Dump شامل Cluster Role و Config OS نیست؛ Role بازسازی و Config جدا Archive شود. --list بررسی Catalog، --file=/dev/null Decode همه Data و SHA256 خرابی بعدی؛ فقط Restore واقعی ایزوله Recovery را ثابت می‌کند.')
p('Local peer authentication through postgres OS user and SET ROLE zabbix avoids database passwords. Remote backup needs a dedicated authorized role, verify-full TLS and protected 0600 PGPASSFILE outside scripts; never PGPASSWORD in units/history/URLs. Archives contain sensitive data and credentials: encrypt local volume and off-site copies, keep recovery keys separately in a vault.', 'Peer محلی با کاربر OS برابر postgres و SET ROLE zabbix بدون Password DB است. Backup Remote نیازمند Role اختصاصی مجاز، TLS verify-full و PGPASSFILE با 0600 خارج Script؛ نه PGPASSWORD در Unit/History/URL. Archive شامل داده حساس/Credential؛ Volume محلی و کپی Off-site رمزنگاری و Recovery Key در Vault جدا.')
items([
 ('Archive /etc/zabbix including web credentials/PSKs, /etc/nginx, PHP-FPM and PostgreSQL settings.','Archive از /etc/zabbix شامل Credential وب/PSK، /etc/nginx و تنظیم PHP-FPM/PostgreSQL.'),
 ('Include TLS chain/keys and renewal hooks, alert/external scripts, custom plugins and their dependencies.','Chain/Key TLS و Renewal Hook، Script Alert/External، Plugin سفارشی و Dependency.'),
 ('Example retention: 14 daily local sets; off-site daily/weekly/monthly policy defined separately from compliance/RPO needs.','مثال Retention: ۱۴ مجموعه روزانه محلی؛ Policy روزانه/هفتگی/ماهانه Off-site جدا براساس الزام/RPO.'),
 ('Monitor backup age and failure independently of Zabbix; verify archive, off-site retrieval and scheduled restore drills.','سن/شکست Backup مستقل از Zabbix پایش؛ Archive، Retrieval Off-site و Restore Drill زمان‌بندی‌شده تأیید.')])
sub('Logical vs physical, pgBackRest, WAL and PITR','منطقی در برابر فیزیکی، pgBackRest، WAL و PITR')
p('Logical pg_dump exports objects/data and can move to compatible newer PostgreSQL versions; it cannot recover changes between dumps. Physical backup includes a consistent cluster and required WAL, generally needs the same PostgreSQL major/platform. Plain tar of running PGDATA is not a consistent backup. Large production installations should use pgBackRest full/differential/incremental backups, encrypted remote repositories, continuous WAL archiving and tested PITR; retained base backups need their full WAL chain.', 'pg_dump منطقی Object/Data صادر و به PostgreSQL جدیدتر سازگار منتقل می‌کند؛ بین Dumpها بازیابی ندارد. Backup فیزیکی Cluster سازگار و WAL لازم، معمولاً Major/Platform یکسان. tar ساده PGDATA در حال اجرا Backup سازگار نیست. Production بزرگ از pgBackRest برای Full/Differential/Incremental، Repository Remote رمزنگاری‌شده، WAL Archiving پیوسته و PITR تست‌شده؛ Base Backup به کل زنجیره WAL نیاز دارد.')
code('''# DESIGN starting point for a separately reviewed pgBackRest deployment:
# /etc/pgbackrest/pgbackrest.conf
[global]
repo1-path=/srv/pgbackrest
repo1-retention-full=2
[zabbix]
pg1-path=/var/lib/postgresql/16/main
# postgresql.conf: archive_mode change requires restart
wal_level = replica
archive_mode = on
archive_command = 'pgbackrest --stanza=zabbix archive-push %p'
# AFTER dedicated repository ownership, remote access and encryption setup:
# sudo -u postgres pgbackrest --stanza=zabbix stanza-create
# sudo -u postgres pgbackrest --stanza=zabbix check
# sudo -u postgres pgbackrest --stanza=zabbix --type=full backup
# sudo -u postgres pgbackrest --stanza=zabbix info
# ISOLATED EMPTY CLUSTER ONLY, destructive physical restore workflow:
# pgbackrest --stanza=zabbix --type=time --target='2026-10-09 01:30:00+00' --target-action=pause restore''','conf')
p('Provision and protect the repository first, then follow official remote backup/encryption instructions. Monitor pg_stat_archiver failures and WAL growth. Restore a target timestamp to an isolated cluster, inspect paused recovery, promote after acceptance. WAL archive delay controls achievable RPO; base retrieval/restore and replay determine RTO. Never physical-restore over active production. The pgBackRest example is a separate design, not a substitute for the following logical script.', 'ابتدا Repository محافظت‌شده سپس دستور رسمی Remote Backup/Encryption. pg_stat_archiver و رشد WAL پایش. Timestamp هدف به Cluster ایزوله Restore، Recovery متوقف بررسی و بعد پذیرش Promote. تأخیر Archive تعیین RPO؛ دریافت/Restore Base و Replay تعیین RTO. Physical Restore روی Production فعال ممنوع. مثال pgBackRest طراحی جدا است، نه جای Script منطقی بعدی.')
link('https://www.postgresql.org/docs/16/app-pgdump.html','PostgreSQL pg_dump formats and consistency','Format و Consistency در pg_dump')
link('https://www.postgresql.org/docs/16/backup.html','Official logical and physical strategies','راهبرد رسمی Backup منطقی و فیزیکی')
link('https://pgbackrest.org/user-guide.html','Official pgBackRest WAL/PITR guide','راهنمای رسمی pgBackRest و WAL/PITR')

section('automated-backup','10. Automated daily backup','۱۰. بکاپ روزانه خودکار')
p('Mount a protected encrypted volume at /backup; the job refuses an absent mount to protect the OS disk. systemd EnvironmentFile supplies plain settings, not executable shell sourcing. The complete script below provides root-only permissions, flock overlap protection, conservative disk validation, compressed dump/config archive, full decoding/checksums, atomic publication, logs and failure codes. Incomplete sets remain for diagnosis and are never counted successful.', 'Volume محافظت‌شده رمزنگاری‌شده در /backup Mount؛ در نبود Mount، Job برای حفاظت Disk OS رد می‌شود. systemd EnvironmentFile تنظیم ساده می‌دهد، نه Source قابل اجرا. Script کامل زیر دارای Permission فقط root، flock، بررسی محافظه‌کارانه Disk، Dump/Config فشرده، Decode/Checksum، انتشار Atomic، Log و Exit Code؛ مجموعه ناقص برای تشخیص و هرگز موفق محسوب نمی‌شود.')
filecode('zabbix-backup.sh','bash')
p('Exit codes: 64 bad configuration, 65 invalid data, 66 missing file, 69 dependency, 73 mount/storage, 75 overlap, 77 permissions; native PostgreSQL/tar/restic failures stay nonzero. EXIT trap runs a bounded independent hook; OnFailure also catches startup/time-limit failures. Configure approved SMTP relay or replace hook with approved transport; independent stale-success alerts must detect missed timers/dead hosts because failure email alone cannot.', 'Exit Code: 64 تنظیم غلط، 65 Data نامعتبر، 66 فایل غایب، 69 Dependency، 73 Mount/Storage، 75 Overlap، 77 Permission؛ شکست PostgreSQL/tar/restic غیرصفر می‌ماند. EXIT Trap، Hook مستقل با Timeout؛ OnFailure برای Startup/Time-limit. Relay مجاز یا Hook تأییدشده و Alert مستقل سن Success برای Timer اجرا‌نشده/Host مرده؛ ایمیل شکست کافی نیست.')
filecode('zabbix-backup.env','conf')
filecode('zabbix-backup.service','ini')
filecode('zabbix-backup.timer','ini')
filecode('zabbix-backup-failure.service','ini')
filecode('zabbix-backup-notify','bash')
code('''sudo apt install -y restic mailutils
# Mount encrypted /backup through a reviewed fstab entry first.
mountpoint /backup
sudo install -d -m 0700 /backup/zabbix /etc/zabbix-backup
sudo install -d -m 0700 /var/log/zabbix-backup /var/cache/restic
sudo install -o root -g root -m 0750 zabbix-backup.sh /usr/local/sbin/zabbix-backup.sh
sudo install -o root -g root -m 0750 zabbix-backup-notify /usr/local/sbin/zabbix-backup-notify
sudo install -o root -g root -m 0600 zabbix-backup.env /etc/zabbix-backup/backup.env
sudo install -o root -g root -m 0644 zabbix-backup.service zabbix-backup.timer zabbix-backup-failure.service /etc/systemd/system/
sudoedit /etc/zabbix-backup/backup.env
sudoedit /usr/local/sbin/zabbix-backup-notify
# Provision restic-password root:root 0600, SSH key and verified known_hosts.
# Never disable host-key verification; keep recovery keys separately in a vault.
sudo systemctl daemon-reload
sudo systemd-analyze verify /etc/systemd/system/zabbix-backup.service /etc/systemd/system/zabbix-backup.timer /etc/systemd/system/zabbix-backup-failure.service
sudo systemctl start zabbix-backup.service
sudo systemctl show zabbix-backup.service -p Result -p ExecMainStatus
sudo journalctl -u zabbix-backup.service -n 100 --no-pager
sudo cat /backup/zabbix/last-success.txt
sudo systemctl enable --now zabbix-backup.timer
systemctl list-timers --all zabbix-backup.timer''')
code('''# Protected root session; use the SAME non-secret settings as backup.env.
export RESTIC_REPOSITORY='sftp:backup@203.0.113.30:/srv/restic/zabbix'
export RESTIC_PASSWORD_FILE=/etc/zabbix-backup/restic-password
restic init       # ONLY for a newly provisioned empty repository, once
restic snapshots
restic check
# Schedule a separate full-data verification:
restic check --read-data
# Separate approved remote retention policy AFTER verified snapshots:
# restic forget --tag zabbix --keep-daily 14 --keep-weekly 8 --keep-monthly 12 --prune''')
p('Initialize off-site storage before the first job; interactive shells do not inherit systemd EnvironmentFile. OFFSITE_REQUIRED=yes is the production default: upload/check failure fails the job and prevents retention. no is only for a documented isolated lab or independently verified transfer with its own age/failure monitoring. Local retention deletes only successful timestamp directories older than policy after verified replacement and required off-site success. Logs last 90 days; incomplete sets require reviewed cleanup. Keep remote retention separate and retain immutable/offline copies: writable remote storage alone does not prevent ransomware. A checksum and restic metadata check do not replace full-data verification and restore drills.', 'Off-site قبل اولین Job مقداردهی؛ Shell، EnvironmentFile را ارث نمی‌برد. OFFSITE_REQUIRED=yes پیش‌فرض Production: شکست Upload/Check یعنی Job ناموفق و بدون Retention. no فقط Lab مستند یا Transfer مستقل تأییدشده با Age/Failure Monitoring. Retention محلی فقط Timestamp موفق قدیمی طبق Policy پس از جایگزین معتبر و Off-site موفق. Log ۹۰ روز؛ ناقص نیازمند Cleanup بررسی‌شده. Retention Remote جدا و کپی Immutable/Offline؛ Storage قابل نوشتن به‌تنهایی ضد Ransomware نیست. Checksum و Metadata Check جای Full Data/Restore Drill نیست.')
link('https://restic.readthedocs.io/en/stable/030_preparing_a_new_repo.html','Encrypted restic repository setup','Setup Repository رمزنگاری‌شده restic')
link('https://restic.readthedocs.io/en/stable/045_working_with_repos.html','restic integrity and full-data checks','Integrity و Full Data Check در restic')

section('recovery','11. Restore and disaster recovery','۱۱. Restore و بازیابی بحران')
figure('zabbix-backup-disaster-recovery.png','Verified database/config backup, encrypted off-site storage, isolated restore drill and WAL-based PITR recovery chain','زنجیره Backup معتبر DB/Config، Off-site رمزنگاری‌شده، Restore Drill ایزوله و PITR مبتنی بر WAL')
p('RPO is maximum acceptable lost data/config interval; RTO is maximum acceptable service recovery time. Daily dumps can lose nearly a day plus transfer/scheduling delay; 24-hour RPO is not guaranteed without age monitoring. PITR approaches available WAL archive coverage. Measure retrieval, install, restore, secret recovery and validation for RTO. Plan downtime, communication and a single cutover authority; never run duplicate collecting/alerting servers with the same identity.', 'RPO حداکثر بازه قابل قبول فقدان Data/Config و RTO حداکثر زمان Recovery سرویس. Dump روزانه نزدیک یک روز به‌علاوه تأخیر Transfer/Schedule؛ بدون Age Monitoring، ۲۴ ساعت تضمین نیست. PITR به پوشش WAL Archive نزدیک می‌شود. Retrieval، Install، Restore، Secret Recovery و Validation برای RTO اندازه‌گیری. Downtime، Communication و مرجع واحد Cutover؛ Server تکراری Collect/Alert با یک هویت اجرا نشود.')
p('1–2. Prepare Ubuntu 24.04 replacement on an isolated recovery VLAN: patches, IP/DNS, NTP, firewall. Block agent/proxy traffic and external SMTP/Telegram, keep backup timer stopped. Install recorded compatible Zabbix 7.0 patch and PostgreSQL 16/PHP/Nginx. Retrieve selected encrypted snapshot into root-only staging, preserve original, verify SHA256 and archive decoding. Never run older Zabbix on a newer schema; startup can irreversibly upgrade schema. For vulnerable recorded packages rehearse a supported patch upgrade on a clone.', '۱–۲. Ubuntu 24.04 جایگزین در VLAN ایزوله: Patch، IP/DNS، NTP، Firewall. ترافیک Agent/Proxy و SMTP/Telegram خارجی مسدود، Timer متوقف. Patch ثبت‌شده سازگار Zabbix 7.0 و PostgreSQL 16/PHP/Nginx نصب. Snapshot رمزنگاری‌شده منتخب به Staging فقط root، حفظ اصل و بررسی SHA256/Decode. Zabbix قدیمی روی Schema جدید ممنوع؛ Startup ممکن است Upgrade غیرقابل برگشت دهد. Package ثبت‌شده آسیب‌پذیر با Upgrade پشتیبانی‌شده روی Clone تمرین شود.')
code(r'''# ISOLATED REPLACEMENT ONLY; adapt selected-set to retrieved snapshot.
sudo systemctl stop zabbix-server zabbix-agent2 nginx
sudo systemctl disable --now zabbix-backup.timer 2>/dev/null || true
sudo install -d -m 0700 /restore/zabbix
# With protected repository settings, select reviewed snapshot ID:
# restic restore SELECTED_SNAPSHOT_ID --target /restore/zabbix
sudo bash -c 'cd /restore/zabbix/selected-set && sha256sum --check SHA256SUMS'
sudo pg_restore --list /restore/zabbix/selected-set/zabbix.dump
sudo pg_restore --file=/dev/null /restore/zabbix/selected-set/zabbix.dump
sudo gzip -t /restore/zabbix/selected-set/configuration.tar.gz
sudo tar -tzf /restore/zabbix/selected-set/configuration.tar.gz
# 3. Recreate role only if absent, then new EMPTY recovery database.
sudo -u postgres createuser --no-superuser --no-createdb --no-createrole zabbix
sudo -u postgres psql -X -c '\password zabbix'
sudo -u postgres createdb -O zabbix -E UTF8 -T template0 zabbix_recovery
sudo -u postgres psql -X -d zabbix_recovery -c 'REVOKE ALL ON DATABASE zabbix_recovery FROM PUBLIC;'
sudo -u postgres psql -X -d zabbix_recovery -c 'REVOKE CREATE ON SCHEMA public FROM PUBLIC;'
sudo -u postgres psql -X -d zabbix_recovery -c 'GRANT ALL ON SCHEMA public TO zabbix;'
# Pipe stdin because postgres cannot traverse root-only backup directories.
set -o pipefail
sudo cat /restore/zabbix/selected-set/zabbix.dump | sudo -u postgres pg_restore --exit-on-error --no-owner --no-acl --role=zabbix -d zabbix_recovery
sudo -u postgres psql -X -d zabbix_recovery -c 'SELECT mandatory,optional FROM dbversion;'
sudo -u postgres psql -X -d zabbix_recovery -c 'SELECT count(*) FROM hosts;'
sudo -u postgres psql -X -d zabbix_recovery -c 'SELECT count(*) FROM triggers;'
sudo -u postgres psql -X -d zabbix_recovery -c 'ANALYZE;'
# 4–6. Extract to staging, not directly over /etc.
sudo install -d -m 0700 /restore/configuration
sudo tar --acls --xattrs --numeric-owner -xzf /restore/zabbix/selected-set/configuration.tar.gz -C /restore/configuration''')
p('3. Do not import initial server.sql.gz before pg_restore. Use PostgreSQL 16 pg_restore or documented compatible newer tools. 4–6. Review staged /etc/zabbix, Nginx/PHP-FPM, TLS keys/chain, PostgreSQL settings and custom alert/external scripts; install selectively with recorded owner/mode, reconcile account IDs, IPs, certificate names and interpreter dependencies. Never overwrite PGDATA or OS config blindly. The following copies overwrite replacement configuration and are for the reviewed replacement only. Set DBName=zabbix_recovery/reset DBPassword in server and frontend and add its limited SCRAM pg_hba rule for the drill.', '۳. قبل pg_restore، server.sql.gz اولیه وارد نشود. pg_restore نسخه 16 یا ابزار جدیدتر سازگار مستند. ۴–۶. /etc/zabbix، Nginx/PHP-FPM، Chain/Key TLS، تنظیم PostgreSQL و Script Alert/External در Staging بررسی؛ انتقال انتخابی با Owner/Mode ثبت‌شده، تطبیق ID حساب، IP، نام Certificate و Interpreter. PGDATA/Config OS کورکورانه Overwrite نشود. کپی‌های زیر Config جایگزین را بازنویسی می‌کنند و فقط برای جایگزین بررسی‌شده‌اند. برای Drill، DBName=zabbix_recovery و DBPassword Reset در Server/Frontend و Rule SCRAM محدود آن.')
code('''# OVERWRITES REPLACEMENT CONFIG: review staged files and destination host first.
sudo cp -a /restore/configuration/etc/zabbix/. /etc/zabbix/
sudo cp -a /restore/configuration/etc/nginx/. /etc/nginx/
sudo cp -a /restore/configuration/etc/php/8.3/fpm/. /etc/php/8.3/fpm/
sudo install -d -m 0700 /etc/ssl/zabbix
sudo cp -a /restore/configuration/etc/ssl/zabbix/. /etc/ssl/zabbix/
sudo chown root:root /etc/ssl/zabbix/privkey.pem
sudo chmod 0600 /etc/ssl/zabbix/privkey.pem
sudo chown root:zabbix /etc/zabbix/zabbix_server.conf
sudo chmod 0640 /etc/zabbix/zabbix_server.conf
# Reconcile PHP pool group before protecting zabbix.conf.php.
# Optional custom scripts, only if they were in the selected set:
for relative in usr/lib/zabbix/alertscripts usr/lib/zabbix/externalscripts usr/local/lib/zabbix; do
  if sudo test -d "/restore/configuration/$relative"; then
    sudo install -d -o root -g zabbix -m 0750 "/$relative"
    sudo cp -a "/restore/configuration/$relative/." "/$relative/"
  fi
done
# Restore /etc/letsencrypt and renewal hooks only if that CA workflow was used.
# Restore backup scripts/units from the set, but keep its timer disabled during drill.
sudoedit /etc/zabbix/zabbix_server.conf
sudoedit /etc/zabbix/web/zabbix.conf.php
sudoedit /etc/zabbix/nginx.conf
sudoedit /etc/postgresql/16/main/pg_hba.conf
# Add BEFORE rejection rules:
# host zabbix_recovery zabbix 127.0.0.1/32 scram-sha-256''')
code('''# 7. Start required services only after configuration review.
sudo pg_ctlcluster 16 main reload
sudo php-fpm8.3 -t
sudo nginx -t
sudo systemctl start php8.3-fpm zabbix-server nginx
sudo systemctl status zabbix-server php8.3-fpm nginx --no-pager
sudo tail -n 100 /var/log/zabbix/zabbix_server.log
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
# Start only disposable lab agents after name/network/TLS validation.''')
p('8–10. Check System information/schema, counts against recovery record, historical graphs, fresh Linux/Windows lab data, PROBLEM/OK and notification to an isolated test sink. Test RBAC/MFA, HTTPS, scripts, queue, maintenance/dependencies and backup on replacement. Live hosts have stale data while isolated; use cloned lab identities or controlled rerouting, not duplicate collectors. Record restored last sample and elapsed retrieval/start/validation, achieved RPO/RTO. Then approve IP/DNS cutover, alert routes and timer reactivation; preserve rollback source.', '۸–۱۰. System Information/Schema، شمارش با رکورد، Graph تاریخی، داده تازه Lab Linux/Windows، PROBLEM/OK و اعلان به Test Sink ایزوله. RBAC/MFA، HTTPS، Script، Queue، Maintenance/Dependency و Backup جایگزین تست. Host زنده در Isolation داده قدیمی دارد؛ هویت Clone Lab یا Reroute کنترل‌شده نه Collector تکراری. آخرین Sample، زمان Retrieval/Start/Validation و RPO/RTO ثبت. سپس تأیید Cutover IP/DNS، Alert و Timer؛ Source Rollback حفظ.')
sub('Destructive commands — approved replacement cutover only','فرمان مخرب — فقط Cutover جایگزین تأییدشده')
p('dropdb permanently deletes the existing monitoring database. Keep it separate from the new-name drill; only execute on a confirmed replacement after verified backup, recorded host identity and explicit operational approval. Never on the live source. --clean or extracting config over /etc also overwrites state and needs review.', 'dropdb دیتابیس موجود را دائماً حذف می‌کند. جدا از Drill با نام جدید؛ فقط جایگزین تأییدشده با Backup معتبر، هویت Host ثبت‌شده و تأیید عملیاتی صریح. هرگز Source زنده. --clean یا Extract روی /etc نیز Overwrite و نیازمند Review.')
code('''# DESTRUCTIVE, intentionally commented: confirmed replacement host only.
sudo systemctl stop nginx zabbix-server
# sudo -u postgres dropdb --if-exists zabbix
# sudo -u postgres createdb -O zabbix -E UTF8 -T template0 zabbix
# Restore the verified dump into this EMPTY DB as above, no initial schema import.''')
link('https://www.postgresql.org/docs/16/app-pgrestore.html','pg_restore compatibility and ownership','سازگاری و Ownership در pg_restore')
ref('installation/upgrade','Zabbix schema/version upgrade compatibility','سازگاری Upgrade مربوط به Schema/Version')

section('troubleshooting','12. Production troubleshooting','۱۲. عیب‌یابی Production')
p('Symptoms → Root cause → Diagnostic commands → Expected results → Resolution. Expected results describe healthy infrastructure, not runtime results from this website workspace. Diagnose from the assigned Server/Proxy and save evidence before restarting. Never resolve failures by disabling TLS/firewall/AppArmor/authentication.', 'نشانه ← علت ریشه‌ای ← فرمان تشخیص ← نتیجه مورد انتظار ← راه‌حل. Expected Result توصیف زیرساخت سالم است نه نتیجه Runtime در Workspace سایت. از Server/Proxy تعیین‌شده تشخیص و پیش از Restart شاهد حفظ شود. رفع شکست با غیرفعال‌کردن TLS/Firewall/AppArmor/احراز هویت ممنوع.')
issues = [
 ('Server not starting','Server شروع نمی‌شود','Failed unit / Server down','Unit ناموفق یا Server Down','Config, DB/schema or port conflict','Config، DB/Schema یا تداخل Port','sudo systemctl status zabbix-server --no-pager\nsudo journalctl -u zabbix-server -n 100 --no-pager\nsudo tail -n 100 /var/log/zabbix/zabbix_server.log\nsudo ss -lntp','Running, listener 10051, no fatal DB errors','Running، Listener 10051 و بدون Fatal DB','Fix first logged error and compatibility before restart.','اولین خطای Log و سازگاری پیش از Restart اصلاح.'),
 ('Database connection failure','شکست اتصال Database','Refused / authentication failed','Refused یا Authentication Failed','Wrong DBHost/password/HBA or stopped cluster','DBHost/Password/HBA غلط یا Cluster متوقف',"pg_lsclusters\npg_isready -h 127.0.0.1\npsql -h 127.0.0.1 -U zabbix -W -d zabbix -c 'SELECT 1;'\nsudo -u postgres psql -X -c 'SELECT line_number,error FROM pg_hba_file_rules;'","Online, accepting, SELECT 1 succeeds","Online، Accepting و SELECT 1 موفق",'Repair protected credentials and ordered SCRAM rules; no trust.','Credential محافظت‌شده و ترتیب SCRAM اصلاح؛ بدون trust.'),
 ('Agent unavailable','Agent در دسترس نیست','Stale OS data / passive red icon','داده OS قدیمی یا Icon Passive قرمز','Agent stopped, interface/allowlist/TLS wrong','Agent متوقف، Interface/Allowlist/TLS غلط','sudo systemctl status zabbix-agent2 --no-pager\nsudo tail -n 50 /var/log/zabbix/zabbix_agent2.log\nsudo ufw status numbered\n# Windows: Get-Service -Name "Zabbix Agent 2"','Running, encrypted passive ping=1, active data advances','Running، Ping رمزنگاری‌شده 1 و داده Active تازه','Repair path/TLS; use active heartbeat for active-only hosts.','مسیر/TLS اصلاح؛ Heartbeat فعال برای Active-only.'),
 ('Hostname mismatch','عدم تطابق Hostname','Active host not found','Host Not Found در Active','Hostname differs from technical Host name','Hostname با Host name فنی متفاوت',"sudo grep '^Hostname=' /etc/zabbix/zabbix_agent2.conf\nsudo tail -n 50 /var/log/zabbix/zabbix_agent2.log",'Exact case-sensitive name and enabled host','نام دقیق حساس به حروف و Host فعال','Align Hostname, not Visible name; restart agent and wait refresh.','Hostname نه Visible name تطبیق؛ Restart و انتظار Refresh.'),
 ('Active checks not working','Active کار نمی‌کند','Passive works but active data absent','Passive موفق ولی Active غایب','Wrong ServerActive/proxy or template type','ServerActive/Proxy یا Type Template غلط',"sudo grep -E '^(ServerActive|Hostname|TLSConnect)=' /etc/zabbix/zabbix_agent2.conf\nnc -vz 192.0.2.10 10051\nsudo tail -n 50 /var/log/zabbix/zabbix_agent2.log",'Active list/data accepted by assigned destination','List/Data فعال در مقصد تعیین‌شده قبول','Fix assigned destination, active item/template and UI encryption.','مقصد تعیین‌شده، Item/Template فعال و Encryption UI اصلاح.'),
 ('TCP/10050 problems','مشکل TCP/10050','Passive timeout/refused','Timeout/Refused در Passive','Listener or poller allowlist absent','Listener یا Allowlist Poller غایب',"# On agent:\nsudo ss -lntp | grep ':10050'\nsudo ufw status numbered\n# On assigned Server/Proxy:\nnc -vz 198.51.100.21 10050",'Agent listens; approved source reaches it','Agent Listen و Source مجاز دسترسی','Correct ListenIP/source rule; not needed in active-only design.','ListenIP/Source Rule اصلاح؛ در Active-only لازم نیست.'),
 ('TCP/10051 problems','مشکل TCP/10051','Active timeout/refused','Timeout/Refused در Active','Destination listener, routing or egress','Listener مقصد، Route یا Egress',"sudo ss -lntp | grep ':10051'\n# From agent:\nnc -vz 192.0.2.10 10051\nip route get 192.0.2.10",'Correct listener and allowed route/TCP','Listener صحیح و Route/TCP مجاز','Repair real path/NAT source allowlist while retaining TLS.','مسیر واقعی/Allowlist Source NAT اصلاح با حفظ TLS.'),
 ('TLS PSK mismatch','عدم تطابق TLS PSK','Handshake / unknown identity error','خطای Handshake/Unknown Identity','Identity/key/direction mismatch or unreadable file','Identity/Key/جهت متفاوت یا فایل ناخوانا',"sudo grep -E '^(TLSConnect|TLSAccept|TLSPSKIdentity|TLSPSKFile)=' /etc/zabbix/zabbix_agent2.conf\nsudo -u zabbix test -r /etc/zabbix/keys/agent2.psk\nsudo tail -n 50 /var/log/zabbix/zabbix_agent2.log",'Protected readable key and UI/agent match','Key محافظت‌شده خوانا و UI/Agent منطبق','Securely re-provision per-host key, correct directions, test TLS.','باز‌تأمین امن Key یکتا، اصلاح جهت و تست TLS.'),
 ('Unsupported items','Item پشتیبانی نمی‌شود','Not supported item','Item برابر Not Supported','Key/type, permissions, plugin or discovery mismatch','Key/Type، Permission، Plugin یا Discovery غلط',"sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'custom.service.nginx'\n# Read item error under Hosts -> Items.",'Typed value, not ZBX_NOTSUPPORTED','Value Type‌دار نه ZBX_NOTSUPPORTED','Fix exact error with release/7.0 keys and minimal access.','خطای دقیق با Key نسخه release/7.0 و حداقل دسترسی اصلاح.'),
 ('No data received','داده دریافت نمی‌شود','Stale Latest data timestamps','Timestamp قدیمی Latest Data','Disabled item/host, maintenance, queue/clock','Host/Item غیرفعال، Maintenance، Queue/Clock','sudo tail -n 100 /var/log/zabbix/zabbix_server.log\nchronyc tracking\n# Frontend: host/item status, Maintenance and Administration -> Queue','Enabled values advance after interval, bounded queue','Value فعال پس از Interval تازه و Queue محدود','Restore intended collection, time/interval; investigate DB latency.','Collection لازم، Time/Interval اصلاح؛ تأخیر DB بررسی.'),
 ('Database storage exhaustion','اتمام Storage دیتابیس','Full disk, insert failures, queue growth','Disk پر، شکست Insert، رشد Queue','History/WAL/log growth or failed archiving','رشد History/WAL/Log یا Archiving ناموفق',"df -hT\ndf -i\nsudo -u postgres psql -X -d zabbix -c 'SELECT pg_size_pretty(pg_database_size(current_database()));'\nsudo -u postgres psql -X -c 'SELECT * FROM pg_stat_archiver;'",'Free blocks/inodes, healthy WAL archiving','Block/Inode آزاد و WAL Archiving سالم','Expand safely, repair archiving/retention; never delete WAL/DB files manually.','افزایش ایمن، اصلاح Archiving/Retention؛ WAL/DB دستی حذف نشود.'),
 ('Backup failure','شکست Backup','Failed unit or stale last-success','Unit ناموفق یا Last Success قدیمی','Mount/space, credentials, changed files or off-site failure','Mount/Space، Credential، فایل تغییرکرده یا Off-site','sudo systemctl show zabbix-backup.service -p Result -p ExecMainStatus\nsudo journalctl -u zabbix-backup.service -n 100 --no-pager\nmountpoint /backup\ndf -h /backup\nsystemctl list-timers --all zabbix-backup.timer','Result=success, exit 0, fresh SUCCESS and off-site snapshot','Result=success، Exit 0، SUCCESS تازه و Snapshot Off-site','Fix logged cause, preserve old verified sets, rerun/retrieve and test failure channel.','علت Log رفع، قدیمی معتبر حفظ، اجرا/دریافت مجدد و کانال Failure تست.')
]
for en,fa,sym,fsym,cause,fcause,commands,expected,fexpected,resolution,fresolution in issues:
    sub(en,fa); p('Symptoms: '+sym+'. Root cause: '+cause+'.','نشانه: '+fsym+'. علت ریشه‌ای: '+fcause+'.'); code(commands)
    p('Expected results: '+expected+'. Resolution: '+resolution,'نتیجه مورد انتظار: '+fexpected+'. راه‌حل: '+fresolution)

section('best-practices','13. Production checklist','۱۳. چک‌لیست Production')
items([
 ('[ ] Stable supported LTS/packages and advisories recorded; patch owner/window assigned.','[ ] LTS/Package پایدار و Advisory ثبت؛ مالک/Window Patch مشخص.'),
 ('[ ] DNS/static IP, NTP, IOPS/capacity, DB/WAL headroom and encrypted /backup mount validated.','[ ] DNS/IP ثابت، NTP، IOPS/Capacity، فضای DB/WAL و Mount رمزنگاری‌شده /backup تأیید.'),
 ('[ ] Restricted PostgreSQL role, SCRAM/peer and loopback pass positive/negative access tests.','[ ] Role محدود PostgreSQL، SCRAM/Peer و Loopback تست دسترسی مثبت/منفی موفق.'),
 ('[ ] Server/frontend/Linux/Windows services survive reboot; logs/permissions match package layout.','[ ] سرویس Server/Frontend/Linux/Windows پس از Reboot؛ Log/Permission منطبق بسته.'),
 ('[ ] Correct active/passive templates, hostnames/proxy and fresh CPU/RAM/disk/network/load/uptime data.','[ ] Template Active/Passive، Hostname/Proxy صحیح و داده تازه CPU/RAM/Disk/Network/Load/Uptime.'),
 ('[ ] Required services and Event Logs covered; discovery exclusions/thresholds reviewed.','[ ] Service ضروری و Event Log پوشش؛ Exclusion/Threshold بررسی.'),
 ('[ ] Trigger PROBLEM/OK, severity, hysteresis, maintenance, dependency/escalation tested safely.','[ ] PROBLEM/OK، Severity، Hysteresis، Maintenance، Dependency/Escalation امن تست.'),
 ('[ ] Email/official Telegram action and recovery delivery verified with permitted test users.','[ ] تحویل Action/Recovery ایمیل و Telegram رسمی با Test User مجاز تأیید.'),
 ('[ ] HTTPS renewal/chain, unique Agent PSK/cert and narrow firewall pass; no insecure fallback.','[ ] Renewal/Chain HTTPS، PSK/Cert یکتا و Firewall محدود موفق؛ بدون Fallback ناامن.'),
 ('[ ] Named admins, MFA/RBAC/API scope, audit retention, sessions and emergency access tested.','[ ] Admin نام‌دار، MFA/RBAC/API محدود، Audit Retention، Session و دسترسی اضطراری تست.'),
 ('[ ] Timer, lock, space/integrity checks, failure transport and independent stale-success alerts tested.','[ ] Timer، Lock، Space/Integrity، Transport شکست و Alert مستقل سن Success تست.'),
 ('[ ] Encrypted off-site snapshot retrieved; separate vault keys, retention/immutable copies approved.','[ ] Snapshot Off-site رمزنگاری‌شده دریافت؛ Key Vault جدا، Retention/Immutable تأیید.'),
 ('[ ] Isolated restore of DB/config/TLS/scripts, hosts/history/triggers/notifications meets measured RPO/RTO.','[ ] Restore ایزوله DB/Config/TLS/Script، Host/History/Trigger/Notification با RPO/RTO اندازه‌گیری‌شده کافی.'),
 ('[ ] Operations owns dashboards, storage/queue/backup checks, incident/capacity/change/rollback runbooks.','[ ] Operations مالک Dashboard، Check Storage/Queue/Backup و Runbook رخداد/Capacity/Change/Rollback.')])
section('conclusion','Operational handover and runtime acceptance','تحویل عملیاتی و پذیرش Runtime')
p('This website workspace validates article integration and static syntax. It does not install live Zabbix/PostgreSQL or execute a real backup, Windows MSI, notification or restore. Complete and record the staging acceptance evidence before production rollout. Hand over versions, protected secret paths, diagrams, dashboards, alert ownership, backup retention and measured restore evidence to Operations.', 'Workspace سایت، Integration مقاله و Syntax ایستا را بررسی می‌کند؛ Zabbix/PostgreSQL زنده نصب یا Backup واقعی، MSI، اعلان و Restore اجرا نمی‌کند. شاهد پذیرش Staging پیش از Production تکمیل و ثبت شود. Version، مسیر Secret محافظت‌شده، Diagram، Dashboard، مالک Alert، Retention و شاهد Restore اندازه‌گیری‌شده به Operations تحویل شود.')

FAQ = [
 ('Why 7.0 LTS instead of 8.0 RC?','The verified lifecycle lists 7.0 as the latest released LTS. RC is not production; recheck the stable selector before deployment.','چرا 7.0 LTS به جای 8.0 RC؟','Lifecycle بررسی‌شده 7.0 را آخرین LTS منتشرشده می‌داند. RC برای Production نیست؛ Stable Selector قبل استقرار دوباره بررسی شود.'),
 ('Which templates work with Agent 2?','Official Linux by Zabbix agent and Windows by Zabbix agent templates support Agent 2; choose their active variants for active checks.','کدام Template با Agent 2 کار می‌کند؟','Template رسمی Linux by Zabbix agent و Windows by Zabbix agent؛ برای Active نوع Active انتخاب شود.'),
 ('Which ports are required?','Active agents connect to Server/Proxy TCP/10051; passive polling reaches agents TCP/10050, with TLS and source allowlists.','Port لازم چیست؟','Agent فعال به Server/Proxy روی TCP/10051؛ Poll غیرفعال به Agent روی TCP/10050 با TLS و Allowlist.'),
 ('Is /etc/zabbix alone a backup?','No. Recover PostgreSQL together with protected configuration, TLS material and custom scripts.','آیا /etc/zabbix به‌تنهایی بکاپ است؟','خیر؛ PostgreSQL همراه Config محافظت‌شده، TLS و Script سفارشی بازیابی شود.'),
 ('Does the script store DB passwords?','No. Local peer authentication uses postgres OS user and the restricted zabbix role. Off-site credentials are protected separately.','Script Password DB ذخیره می‌کند؟','خیر؛ Peer محلی با کاربر OS برابر postgres و Role محدود zabbix. Credential Off-site جدا محافظت می‌شود.'),
 ('When are old backups deleted?','Only after new archive verification and required off-site success, and only completed sets older than configured retention.','بکاپ قدیمی چه زمانی حذف می‌شود؟','فقط پس از Verification جدید و Off-site لازم موفق، برای مجموعه تکمیل‌شده قدیمی طبق Retention.'),
 ('Can a dump provide PITR?','A dump restores its snapshot. PITR requires a physical base backup and complete WAL archive chain.','آیا Dump، PITR می‌دهد؟','Dump به Snapshot برمی‌گردد؛ PITR نیازمند Base Backup فیزیکی و زنجیره کامل WAL است.'),
 ('Do checksums prove recovery?','No. Restore and validate hosts, data, triggers and notifications in isolation; record achieved RPO/RTO.','Checksum اثبات Recovery است؟','خیر؛ Host، Data، Trigger و Notification در Isolation بازیابی/تأیید و RPO/RTO ثبت شود.')
]
section('faq','Frequently asked questions','پرسش‌های متداول')
for q,a,fq,fa in FAQ: sub(q,fq); p(a,fa)

DOWNLOADS = [
 ('zabbix-agent2-linux.conf','Linux Agent 2 configuration','Configuration Agent 2 لینوکس'),
 ('zabbix-agent2-windows.conf','Windows Agent 2 configuration','Configuration Agent 2 ویندوز'),
 ('install-zabbix-agent2.ps1','PowerShell MSI installation','نصب MSI با PowerShell'),
 ('zabbix-backup.sh','Verified backup Bash script','Script Bash بکاپ معتبر'),
 ('zabbix-backup.env','Backup environment template','Template محیط بکاپ'),
 ('zabbix-backup.service','Backup systemd service','سرویس systemd بکاپ'),
 ('zabbix-backup.timer','Daily systemd timer','Timer روزانه systemd'),
 ('zabbix-backup-failure.service','Failure notification unit','Unit اعلان شکست'),
 ('zabbix-backup-notify','Independent notification hook','Hook مستقل اعلان'),
 ('restore-runbook.md','Restore runbook','Runbook بازیابی'),
 ('security-checklist.md','Security checklist','چک‌لیست امنیت'),
 ('README.md','Package README','README بسته')]
section('official-references','Official references, downloads and related articles','منابع رسمی، دانلود و مقاله مرتبط')
p('References checked on 9 October 2026 are linked in each chapter. Templates contain no real credentials and require approved addressing, secrets and staging validation. Download individual files or the complete package.', 'منابع بررسی‌شده ۹ اکتبر ۲۰۲۶ در هر فصل لینک هستند. Template فاقد Credential واقعی و نیازمند آدرس/Secret مجاز و Validation Staging است. فایل منفرد یا بسته کامل دریافت کنید.')
for name,en,fa in DOWNLOADS: link(f'/downloads/{SLUG}/{name}','Download: '+en,'دانلود: '+fa)
link(f'/downloads/{SLUG}/zabbix-configuration-package.zip','Complete configuration and runbook ZIP','ZIP کامل Configuration و Runbook')
for slug,en,fa in [
 ('oracle-database-26ai-installation-oracle-linux','Oracle enterprise backup and recovery','Backup/Recovery سازمانی Oracle'),
 ('apache-tomcat-linux-installation-security-hardening','Tomcat service security','امنیت سرویس Tomcat'),
 ('mongodb-installation-configuration-production-deployment','MongoDB production deployment','استقرار Production MongoDB'),
 ('redis-installation-configuration-replication','Redis production monitoring','مانیتورینگ Production Redis'),
 ('linux-security-auditor-bash','Linux security audit','ممیزی امنیت Linux'),
 ('nginx-installation-configuration-ubuntu','Nginx on Ubuntu','Nginx روی Ubuntu')]: link('/articles/'+slug,'Related: '+en,'مرتبط: '+fa)
parts.append('</section>')

localizations={lang:{'title':TITLE[lang],'meta_title':TITLE[lang],'description':DESC[lang],'keywords':[k for k in KEYWORDS if lang=='fa' or not any('\u0600'<=c<='\u06ff' for c in k)],'faq':[[q,a] if lang=='en' else [fq,fa] for q,a,fq,fa in FAQ]} for lang in TITLE}
for lang, alt, caption in [
    ('en', 'Enterprise Zabbix monitoring with Linux and Windows agents, security and disaster recovery', 'Enterprise monitoring: Linux and Windows agents, security, backup and recovery'),
    ('fa', 'مانیتورینگ سازمانی Zabbix با Agent لینوکس و ویندوز، امنیت و بازیابی بحران', 'مانیتورینگ سازمانی: Agent لینوکس و ویندوز، امنیت، بکاپ و بازیابی')]:
    localizations[lang].update(image_alt=alt, image_title=TITLE[lang], image_caption=caption)
banner='/assets/img/articles/banners/zabbix-enterprise-monitoring-banner.png'
url='https://meetaj.ir/articles/'+SLUG
schema={'@context':'https://schema.org','@type':'Article','headline':TITLE['en'],'description':DESC['en'],'inLanguage':'en','datePublished':'2026-10-09T00:00:00+03:30','dateModified':'2026-10-09','image':banner,'author':{'@type':'Person','name':'AmirHossein Jalalian'}}
faq_schema={'@context':'https://schema.org','@type':'FAQPage','mainEntity':[{'@type':'Question','name':q,'acceptedAnswer':{'@type':'Answer','text':a}} for q,a,fq,fa in FAQ]}
nav=''.join('<li class="article-nav-item">'+dual('a',en,fa,f'href="#{id}"')+'</li>' for id,en,fa in toc)
html=f'''<!doctype html>
<html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{escape(TITLE['en'])}</title>
<meta name="article:content-language" content="en"><meta name="description" content="{escape(DESC['en'],quote=True)}">
<meta name="keywords" content="{', '.join(localizations['en']['keywords'])}"><meta name="robots" content="index, follow">
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
<img class="article-hero-thumbnail" src="{banner}" width="512" height="512" alt="Enterprise Zabbix monitoring with Linux and Windows agents, security and disaster recovery" data-en-alt="Enterprise Zabbix monitoring with Linux and Windows agents, security and disaster recovery" data-fa-alt="مانیتورینگ سازمانی Zabbix با Agent لینوکس و ویندوز، امنیت و بازیابی بحران">
</section><article class="article-body" lang="en" dir="ltr">{chr(10).join(parts)}</article></main></body></html>'''
SOURCE.mkdir(parents=True,exist_ok=True)
import sys
sys.path.insert(0, str(ROOT / 'scripts'))
from article_structure import normalize_html, normalize_markdown
html = normalize_html(html, SLUG)
(ROOT/'resources/legacy/articles'/f'{SLUG}.html').write_text(html,encoding='utf-8',newline='\n')
for lang,content in md.items(): (SOURCE/f'article.{lang}.md').write_text(normalize_markdown('\n'.join(content), SLUG),encoding='utf-8',newline='\n')
# Export complete bilingual chapters as matching offline operational runbooks.
for name,id,title in [('restore-runbook.md','recovery','Restore and disaster recovery runbook'),('security-checklist.md','security','Security hardening checklist')]:
    blocks=['# '+title,'','English / فارسی','']
    if id == 'security':
        blocks.extend(['## Acceptance checklist / چک‌لیست پذیرش', '',
            '- [ ] Trusted HTTPS chain, hostname and renewal tested / زنجیره معتبر HTTPS، نام Host و تمدید آزموده شد',
            '- [ ] Unique per-host PSK or certificate, encrypted agent connections only / PSK یکتا یا Certificate هر Host و ارتباط رمز‌شده',
            '- [ ] Source firewall allowlists and isolated database verified / Allowlist مبدا Firewall و Database ایزوله بررسی شد',
            '- [ ] Restricted database role and protected configuration permissions / Role محدود Database و Permission محافظت‌شده Config',
            '- [ ] Default credentials replaced; RBAC and supported MFA tested / Credential پیش‌فرض تغییر و RBAC و MFA پشتیبانی‌شده آزموده شد',
            '- [ ] Audit logging and expiring API tokens reviewed / Audit Log و Token API با انقضا بررسی شد',
            '- [ ] Backup archives encrypted off-site; keys recoverable from separate vault / Archive بکاپ در Off-site رمز‌شده و Key در Vault مستقل قابل بازیابی',
            '- [ ] Isolated restore and notification safeguards tested / Restore ایزوله و کنترل ایمنی اعلان آزموده شد', ''])
    for lang in ['en','fa']:
        content='\n'.join(md[lang]); start=content.index('## '+next(row[1 if lang=='en' else 2] for row in toc if row[0]==id))
        end=content.find('\n## ',start+4)
        blocks.extend([content[start:end if end!=-1 else len(content)],''])
    (SOURCE/name).write_text('\n'.join(blocks),encoding='utf-8',newline='\n')
metadata={'slug':SLUG,'reviewed_at':'2026-10-09','zabbix_branch':'7.0 LTS','verified_zabbix_version':'7.0.31','verified_ubuntu_package':'1:7.0.31-1+ubuntu24.04','windows_msi_minimum':'7.0.22','postgresql_major':16,'php_series':'8.3','nginx_series':'1.24','os':'Ubuntu Server 24.04 LTS','localizations':localizations}
(SOURCE/'metadata.json').write_text(json.dumps(metadata,ensure_ascii=False,indent=2)+'\n',encoding='utf-8',newline='\n')
downloads=ROOT/'public/downloads'/SLUG; downloads.mkdir(parents=True,exist_ok=True)
for name,en,fa in DOWNLOADS: shutil.copyfile(SOURCE/name,downloads/name)
with zipfile.ZipFile(downloads/'zabbix-configuration-package.zip','w',zipfile.ZIP_DEFLATED) as archive:
    for name,en,fa in DOWNLOADS: archive.write(SOURCE/name,name)
shutil.copyfile(downloads/'zabbix-configuration-package.zip',SOURCE/'zabbix-configuration-package.zip')
for folder,names in {'banners':['zabbix-enterprise-monitoring-banner.png'],'content':['zabbix-enterprise-architecture.png','zabbix-server-installation-workflow.png','zabbix-linux-windows-agent-topology.png','zabbix-security-hardening.png','zabbix-backup-disaster-recovery.png']}.items():
    for name in names:
        output=ROOT/'public/assets/img/articles'/folder/name; output.parent.mkdir(parents=True,exist_ok=True)
        shutil.copyfile(ROOT/'resources/assets/img/articles'/folder/name,output)
print(f'Built {SLUG}: {len(toc)} sections, {len(DOWNLOADS)} downloads and ZIP, bilingual HTML/Markdown')
