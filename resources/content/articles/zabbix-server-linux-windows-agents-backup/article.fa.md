# آموزش جامع نصب و راه‌اندازی Zabbix Server، مانیتورینگ لینوکس و ویندوز، امنیت، بکاپ و بازیابی

نصب Zabbix 7.0 LTS روی Ubuntu 24.04 با PostgreSQL و Nginx، Agent 2 لینوکس و ویندوز، TLS، هشدار، بکاپ روزانه معتبر و بازیابی بحران.

## مقدمه و محدوده استقرار

مانیتورینگ سازمانی Zabbix: نصب Server، Agent لینوکس و ویندوز، امن‌سازی، بکاپ و بازیابی بحران. راهنما استقرار اختصاصی جدید را تا تحویل عملیاتی با فایل تنظیم قابل استفاده مجدد و الزام پذیرش Staging دنبال می‌کند.

## ۱. آماده‌سازی و پیش‌نیاز سرور

همه IPها فضای مستند RFC 5737 هستند، نه آدرس قابل استفاده استقرار. شبکه/دامنه را با مقدار تأییدشده عوض کنید. IP ثابت یا DHCP Reservation، DNS مستقیم/معکوس، Console و Maintenance آماده کنید. AppArmor/Firewall فعال بماند. Netplan ممکن است SSH را قطع کند؛ netplan try از Console و راهنمای IP ثابت موجود استفاده شود.

```bash
sudo apt update
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
sudo aa-status
```

انتظار: DNS به IP رزروشده، Route به Update/DNS/NTP مجاز و chronyc دارای منبع همگام منتخب باشد. منبع NTP تأییدشده در /etc/chrony/chrony.conf تنظیم، Restart و بررسی شود. Reboot لازم در Maintenance و تکرار Check؛ زمان غلط Certificate، Timestamp و Escalation را مختل می‌کند.

| Port | Source و کاربرد |
| --- | --- |
| 22/tcp | فقط VPN مدیریت؛ Port واقعی SSH پیش از UFW لحاظ شود. |
| 443/tcp | شبکه Operator به Frontend HTTPS؛ HTTP Setup فقط Loopback. |
| 10051/tcp | Agent/Proxy فعال مجاز به Server؛ Trapper نیز همین Port. |
| 10050/tcp | Server/Proxy تعیین‌شده به Agent برای Passive. |
| 5432/tcp | PostgreSQL فقط Loopback؛ بدون دسترسی Endpoint/عمومی. |
| خروجی | DNS/NTP مجاز، HTTPS بسته/Telegram، SMTP Relay و Transport بکاپ. |

```bash
# Confirm actual SSH port, VPN access and console recovery before enabling UFW.
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from 192.0.2.0/24 to any port 22 proto tcp
sudo ufw allow from 192.0.2.0/24 to any port 443 proto tcp
sudo ufw allow from 198.51.100.21 to any port 10051 proto tcp
sudo ufw allow from 198.51.100.22 to any port 10051 proto tcp
sudo ufw allow from 203.0.113.20 to any port 10051 proto tcp
sudo ufw enable
sudo ufw status numbered
sudo ss -lntp
```

نقطه شروع نصب متوسط کوچک: 4 vCPU، حافظه 16 GiB، SSD مطمئن، IOPS اندازه‌گیری‌شده و Volume بکاپ Mount‌شده جدا. راهنمای ظرفیت است، نه حداقل/تضمین Throughput. NVPS، تأخیر Write، Queue و Cache اندازه‌گیری شود. ۳۰۰۰ Item هر 60s، برابر ۵۰ مقدار در ثانیه و ۱۲۹٫۶ میلیون نمونه طی ۳۰ روز است. Index، Trend، Log، WAL و فضای Maintenance لحاظ؛ حداقل ۳۰٪ آزاد رزرو کنید. /backup براساس حجم DB و Retention؛ برای مقیاس بزرگ DB جدا و بکاپ فیزیکی.

[مرتبط: IP ثابت ایمن Netplan](https://meetaj.ir/articles/set-static-ip-ubuntu-server-netplan)

[سازگاری و ظرفیت رسمی](https://www.zabbix.com/documentation/7.0/en/manual/installation/requirements)

## ۲. معماری سازمانی Zabbix

این راهنما سرور اختصاصی مانیتورینگ Ubuntu Server 24.04 و میزبان Ubuntu و Windows Server 2022/2025 را پوشش می‌دهد. نصب، هشدار، امنیت، بکاپ و بازیابی یک چرخه عملیاتی هستند. سرور مرکزی مثال یک دامنه خرابی است؛ Proxy شعبه قطع WAN را بافر می‌کند اما جایگزین دسترس‌پذیری بالای Server/Database نیست.

![توپولوژی سازمانی: اپراتور HTTPS، Agent و Proxy با TLS، PostgreSQL محلی و بکاپ Off-site](/assets/img/articles/content/zabbix-enterprise-architecture.png)

توپولوژی سازمانی: اپراتور HTTPS، Agent و Proxy با TLS، PostgreSQL محلی و بکاپ Off-site

| جزء | مسئولیت |
| --- | --- |
| Server | زمان‌بندی جمع‌آوری، دریافت داده، ارزیابی Trigger و اجرای Action اعلان. |
| Frontend / Nginx / PHP-FPM | رابط HTTPS و API مبتنی بر JSON-RPC؛ PHP به PostgreSQL و عملکرد منتخب Server دسترسی دارد. |
| PostgreSQL | Host، User، Template، Item، Trigger، Action، Event، History، Trend و رکورد Audit. |
| Agent و Agent 2 | Agent کلاسیک جمع‌آوری‌کننده سبک و Agent 2 دارای Plugin است. برای این Portها یک سرویس انتخاب کنید؛ Template رسمی OS با Agent 2 کار می‌کند. |
| Proxy | جمع‌آوری و بافر داده شعبه و ارسال به Server؛ Trigger/Action مرکزی مستقل اجرا نمی‌کند. |

Item اندازه‌گیری و Key مانند system.uptime است. Template، Item، Discovery، Trigger و Graph قابل استفاده مجدد را گروه‌بندی می‌کند. Trigger مقدار ذخیره‌شده را ارزیابی؛ تغییر PROBLEM و Recovery رویداد می‌سازد. Problems رخداد جاری، Events تغییر وضعیت، Dashboard سلامت/روند و Action اعلان به کاربر با Media Type را نشان می‌دهد.

```text
Management VPN 192.0.2.0/24 -> HTTPS 443 -> zabbix.example.com 192.0.2.10
Central: Nginx -> PHP-FPM -> PostgreSQL 127.0.0.1:5432
         Zabbix Server -> PostgreSQL 127.0.0.1:5432
linux-app-01   198.51.100.21 -> TLS 10051 -> Server
windows-app-01 198.51.100.22 -> TLS 10051 -> Server
Server -> TLS 10050 -> agents (optional passive checks)
Branch proxy 203.0.113.20 -> TLS 10051 -> Server
Backup -> encrypted off-site repository 203.0.113.30
```

Active: Agent به ServerActive روی TCP/10051 وصل، با Hostname دقیق فهرست Item می‌گیرد و مقدار می‌فرستد. Passive: Server یا Proxy تعیین‌شده به TCP/10050 Agent متصل؛ Server فهرست Source مجاز Poll است. میزبان سپرده‌شده به Proxy در هر دو تنظیم از همان Proxy استفاده کند. هیچ تنظیمی Host در Frontend نمی‌سازد.

[مفاهیم مانیتورینگ](https://www.zabbix.com/documentation/7.0/en/manual/concepts)

[معماری رسمی Proxy](https://www.zabbix.com/documentation/7.0/en/manual/distributed_monitoring/proxies)

## ۳. نصب Server، PostgreSQL و Frontend

![فرایند آماده‌سازی Ubuntu، Repository رسمی، Schema PostgreSQL، سرویس Zabbix و اعتبارسنجی HTTPS](/assets/img/articles/content/zabbix-server-installation-workflow.png)

فرایند آماده‌سازی Ubuntu، Repository رسمی، Schema PostgreSQL، سرویس Zabbix و اعتبارسنجی HTTPS

Lifecycle رسمی 7.0 را آخرین LTS منتشرشده و Release Note نسخه 8.0 را 8.0.0rc1 معرفی می‌کند که استفاده نمی‌شود. آخرین Maintenance پایدار 7.0 از Repository رسمی نصب شود. PostgreSQL 16، Nginx 1.24 و PHP 8.3 در Ubuntu با نیاز 7.0 سازگارند. Revision امنیت تغییر می‌کند؛ Candidate/Version واقعی APT ثبت شود نه ادعای Revision ثابت جاری. Server، SQL Script و Frontend Patch یکسان داشته باشند.

[Lifecycle رسمی LTS منتشرشده و برنامه‌ریزی‌شده](https://www.zabbix.com/life_cycle_and_release_policy)

[Release Note: تفکیک Stable و RC](https://www.zabbix.com/release_notes)

Index رسمی Ubuntu Noble amd64 نیز بررسی شد: آخرین Entry پایدار Server و Agent 2 برابر 1:7.0.31-1+ubuntu24.04 است. 7.0.31 در ۲۲ سپتامبر ۲۰۲۶ منتشر شد. این مقدار زمان Review است؛ شاخه Repository برابر 7.0 و Candidate پیش از هر نصب دوباره بررسی شود. Revision امنیت PostgreSQL/Nginx/PHP از Repository نگهداری‌شده Ubuntu 24.04 انتخاب می‌شود.

[Release Note نسخه پایدار تأییدشده 7.0.31](https://www.zabbix.com/rn/rn7.0.31)

[Index رسمی Package در Ubuntu Noble amd64](https://repo.zabbix.com/zabbix/7.0/ubuntu/dists/noble/main/binary-amd64/Packages)

```bash
cd /tmp
curl --fail --location --proto '=https' --tlsv1.2 -O \
  https://repo.zabbix.com/zabbix/7.0/ubuntu/pool/main/z/zabbix-release/zabbix-release_latest_7.0+ubuntu24.04_all.deb
dpkg-deb --info zabbix-release_latest_7.0+ubuntu24.04_all.deb
sudo dpkg -i zabbix-release_latest_7.0+ubuntu24.04_all.deb
sudo apt update
apt-cache policy zabbix-server-pgsql zabbix-agent2 zabbix-frontend-php postgresql-16 nginx php8.3-fpm
# Verify official stable 7.0.x candidates, no beta/RC/devel.
sudo apt install -y postgresql-16 postgresql-client-16 nginx php8.3-fpm \
  php8.3-pgsql php8.3-bcmath php8.3-mbstring php8.3-xml php8.3-gd php8.3-curl \
  zabbix-server-pgsql zabbix-frontend-php zabbix-nginx-conf zabbix-sql-scripts zabbix-agent2 zabbix-get
sudo systemctl stop zabbix-server zabbix-agent2 nginx
sudo systemctl enable --now postgresql
pg_lsclusters
dpkg-query -W zabbix-server-pgsql zabbix-agent2 zabbix-frontend-php postgresql-16 nginx php8.3-fpm
zabbix_server -V
zabbix_agent2 -V
php -v
nginx -v
```

URL نصب Repository با Selector رسمی تطبیق داده شود. HTTPS از Bootstrap Transport و APT از Metadata امضاشده محافظت می‌کند. trusted=yes یا --allow-unauthenticated ممنوع. چون بسته ممکن است Start کند، قبل نصب Firewall محدود برقرار؛ تا TLS آماده نیست Host ثبت نشود.

[Selector رسمی Ubuntu 24.04 و PostgreSQL/Nginx](https://www.zabbix.com/download?zabbix=7.0&os_distribution=ubuntu&os_version=24.04&components=server_frontend_agent&db=pgsql&ws=nginx)

### Role محدود، احراز هویت و Schema اولیه

```bash
sudo -u postgres psql -X -v ON_ERROR_STOP=1 <<'SQL'
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
sudoedit /etc/postgresql/16/main/pg_hba.conf
```

```conf
# postgresql.conf
listen_addresses = '127.0.0.1'
password_encryption = 'scram-sha-256'
# pg_hba.conf: place before broader host rules; preserve local admin peer access
local all     postgres                   peer
local zabbix  zabbix                     peer
host  zabbix  zabbix  127.0.0.1/32        scram-sha-256
host  all     all     0.0.0.0/0           reject
host  all     all     ::/0                reject
```

```bash
sudo pg_ctlcluster 16 main restart
sudo -u postgres psql -X -c "SELECT line_number,error FROM pg_hba_file_rules WHERE error IS NOT NULL;"
sudo -u postgres psql -X -c 'SHOW listen_addresses;'
sudo -u postgres psql -X -c '\du zabbix'
# INITIALIZATION ONLY: NEW EMPTY database, never before a database restore.
set -o pipefail
zcat /usr/share/zabbix-sql-scripts/postgresql/server.sql.gz | sudo -u zabbix psql -X -v ON_ERROR_STOP=1 -d zabbix
sudo -u zabbix psql -X -d zabbix -c 'SELECT mandatory,optional FROM dbversion;'
psql -h 127.0.0.1 -U zabbix -W -d zabbix -c 'SELECT current_user,current_database();'
sudoedit /etc/zabbix/zabbix_server.conf
```

```conf
# Merge into package file; avoid duplicate active parameters.
DBHost=127.0.0.1
DBName=zabbix
DBUser=zabbix
# Set the actual DBPassword using sudoedit; never include it in downloads.
ListenIP=127.0.0.1,192.0.2.10
ListenPort=10051
# Preserve package LogFile, PidFile, SocketDir and script paths.
```

انتظار: بدون ردیف خطای pg_hba، PostgreSQL محلی، یک ردیف dbversion و TCP Login با zabbix. DBPassword واقعی در فایل root:zabbix با 0640 ثبت شود. Zabbix مالک Schema و نیازمند Upgrade کنترل‌شده است؛ Superuser، ساخت Role و مجوز DB غیرمرتبط لازم نیست.

### Nginx/PHP-FPM بسته و مدیریت اولیه محافظت‌شده

Location/FastCGI بسته حفظ شود. /etc/zabbix/nginx.conf که معمولاً در /etc/nginx/conf.d/ لینک دارد، با listen برابر 127.0.0.1:8080 و server_name برابر zabbix.example.com تنظیم شود. Include فایل /etc/zabbix/php-fpm.conf در /etc/php/8.3/fpm/pool.d/ و User/Socket بررسی شود. Nginx از Socket واقعی معمولاً unix:/var/run/php/zabbix.sock استفاده کند. Installer HTTP عمومی نباشد.

```bash
sudoedit /etc/zabbix/nginx.conf
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
ssh -L 8080:127.0.0.1:8080 admin@192.0.2.10
```

از Tunnel، http://127.0.0.1:8080 در Browser ایستگاه کاری باز شود. Prerequisite، PostgreSQL روی 127.0.0.1:5432، Database/User برابر zabbix و Password واقعی، نام سرور و Timezone تکمیل شود. /etc/zabbix/web/zabbix.conf.php متعلق به root و فقط گروه واقعی Pool PHP دارای Read با 0640؛ Write وب پس از Setup حذف شود. کپی منتقل‌شده ایستگاه کاری امن حذف شود.

انتظار: سرویس Active، HTTP محلی 200/Redirect و Reports → System information برابر Server Running. Admin / zabbix اولیه حساس به حروف فقط در Setup محافظت‌شده؛ فوراً Password تغییر، Administrator نام‌دار، Guest بلااستفاده غیرفعال و MFA فعال شود. قبل دسترسی Operator، HTTPS فصل ۶ و حذف Listener اولیه. Login اثبات مانیتورینگ نیست؛ دریافت Item و اعلان تست شود.

[نصب بسته و Schema اولیه](https://www.zabbix.com/documentation/7.0/en/manual/installation/install_from_packages)

[پیش‌نیاز Setup Frontend](https://www.zabbix.com/documentation/7.0/en/manual/installation/frontend)

## ۴. نصب Agent 2 روی Ubuntu Linux

![TLS فعال به TCP/10051 و TLS غیرفعال اختیاری روی TCP/10050 برای Agent 2 لینوکس و ویندوز](/assets/img/articles/content/zabbix-linux-windows-agent-topology.png)

TLS فعال به TCP/10051 و TLS غیرفعال اختیاری روی TCP/10050 برای Agent 2 لینوکس و ویندوز

روی linux-app-01، Repository رسمی 7.0 فصل ۳ سپس فقط Agent 2 نصب شود. Server فهرست Source مجاز Passive، ServerActive مقصد Active، Hostname برابر Host name در Frontend نه Visible name و ListenPort برابر 10050 است. ListenIP با IP واقعی Interface جایگزین شود. Agent 2 در Foreground تحت systemd است؛ StartAgents یا Daemonization کلاسیک اضافه نشود.

```bash
sudo apt update
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
nc -vz 192.0.2.10 10051
```

```conf
# Ubuntu 24.04, Zabbix Agent 2 7.0 LTS. Replace documentation IP/name/identity.
LogType=file
LogFile=/var/log/zabbix/zabbix_agent2.log
LogFileSize=10
Server=192.0.2.10
ServerActive=192.0.2.10:10051
Hostname=linux-app-01
ListenIP=198.51.100.21
ListenPort=10050
TLSConnect=psk
TLSAccept=psk
TLSPSKIdentity=linux-app-01-psk
TLSPSKFile=/etc/zabbix/keys/agent2.psk
DenyKey=system.run[*]
UnsafeUserParameters=0
Include=/etc/zabbix/zabbix_agent2.d/*.conf
Include=/etc/zabbix/zabbix_agent2.d/plugins.d/*.conf
# Explicit read-only key for one approved service; no wildcard shell execution.
UserParameter=custom.service.nginx,/usr/bin/systemctl is-active nginx || true
```

Data collection → Hosts → Create host: Host name برابر linux-app-01، Group برابر Linux servers، Monitored by برابر Server، Interface برابر 198.51.100.21:10050. Template برای Active برابر Linux by Zabbix agent active و برای Passive برابر Linux by Zabbix agent. Agent 2 همین Template رسمی را استفاده می‌کند؛ هر دو نوع با Key مشترک همزمان لینک نشود. Encryption هر دو جهت PSK، Identity برابر linux-app-01-psk و Key یکتای تولیدشده؛ انتقال امن از UI محافظت‌شده نه Ticket/Git.

| معیار | Key و اعتبارسنجی |
| --- | --- |
| CPU و Load | system.cpu.util[,idle] و system.cpu.load[all,avg1]؛ بار کنترل‌شده مشاهده شود. |
| RAM | vm.memory.size[available] و vm.memory.size[total]؛ Available شامل Cache قابل بازیافت است. |
| Disk و Filesystem | vfs.fs.discovery و vfs.fs.size[/,pused]؛ Discovery Mount و حذف Filesystem مجازی بررسی شود. |
| شبکه | net.if.discovery، net.if.in[ens160] و net.if.out[ens160]؛ Interface واقعی کشف‌شده استفاده شود. |
| Uptime و Service | system.uptime و custom.service.nginx؛ تنظیم Item سفارشی فصل ۷. |

```bash
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'system.uptime'
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'vfs.fs.size[/,pused]'
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'custom.service.nginx'
# From the authorized Server: provision this per-host PSK copy securely, root-only.
sudo zabbix_get -s 198.51.100.21 -p 10050 -k agent.ping \
  --tls-connect psk --tls-psk-identity linux-app-01-psk --tls-psk-file /root/linux-app-01.psk
```

انتظار: Ping برابر 1، Uptime مثبت، مصرف Filesystem بین ۰ تا ۱۰۰ و سرویس Running برابر active. nc فقط TCP، -t فقط ارزیابی محلی Key و zabbix_get تبادل کاربردی/TLS Passive را ثابت می‌کند. برای پذیرش Active، Timestamp در Monitoring → Latest data پس از Interval جلو برود؛ Graph نمونه می‌خواهد. ZBX Passive وضعیت Active نیست. برای Active-only، Server حذف که Passive Agent 2 را غیرفعال می‌کند، ورودی 10050 حذف و فقط Template Active. Key تشخیصی موقت طبق چرخه Secret حذف شود.

[پارامتر رسمی Agent 2 لینوکس](https://www.zabbix.com/documentation/7.0/en/manual/appendix/config/zabbix_agent2)

[Template رسمی Linux در release/7.0](https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/os/linux?at=refs%2Fheads%2Frelease%2F7.0)

## ۵. نصب Agent 2 روی Windows Server

Windows Server 2022/2025 پشتیبانی‌شده x64 استفاده شود. MSI پایدار Agent 2 نسخه 7.0 از نوع amd64 OpenSSL از صفحه رسمی دریافت شود. SHA256 سازنده، Patch تأییدشده و Thumbprint ناشر Authenticode معتبر ثبت شود. Script به 7.0.22+ نیاز دارد زیرا DONOTSTART آن زمان اضافه شد. Hash از Source نامطمئن کافی نیست؛ Signature معتبر ناشر Zabbix مستقل تأیید شود.

[دانلود رسمی MSI پایدار Agent 2](https://www.zabbix.com/download_agents)

GUI: MSI تأیید، Wizard با Administrator، قبول License و انتخاب Agent 2 در Program Files پیش‌فرض. Server برابر 192.0.2.10، Active برابر 192.0.2.10:10051، نام دقیق windows-app-01 و Identity یکتای TLS PSK. ACL فایل Key/Config و Firewall پیش از Start در معرض شبکه بررسی شود. اگر GUI خودکار Start کرد، Ingress تا تکمیل مسدود. Production تکرارپذیر از CONF و DONOTSTART استفاده کند تا مقدار PSK وارد Property/Log MSI نشود.

```conf
# Windows Server 2022/2025 x64, Agent 2 7.0 LTS; default MSI destination.
LogType=file
LogFile=C:\ProgramData\Zabbix\zabbix_agent2.log
LogFileSize=10
Server=192.0.2.10
ServerActive=192.0.2.10:10051
Hostname=windows-app-01
ListenPort=10050
TLSConnect=psk
TLSAccept=psk
TLSPSKIdentity=windows-app-01-psk
TLSPSKFile=C:\ProgramData\Zabbix\agent2.psk
DenyKey=system.run[*]
UnsafeUserParameters=0
Include=C:\Program Files\Zabbix Agent 2\zabbix_agent2.d\plugins.d\*.conf
```

```powershell
# Elevated PowerShell; approved values come from the deployment record.
$msi = 'C:\Staging\approved-agent2-7.0-windows-amd64-openssl.msi'
Get-FileHash -LiteralPath $msi -Algorithm SHA256
Get-AuthenticodeSignature -LiteralPath $msi | Format-List Status,SignerCertificate
# Run the script with MsiPath, ExpectedSha256, ApprovedSignerThumbprint,
# ConfigPath, PskSourcePath and ServerAddress parameters.
# Silent MSI step AFTER protected PSK/configuration provisioning:
msiexec.exe /i "C:\Staging\approved-agent2.msi" /qn /norestart CONF="C:\ProgramData\Zabbix\install.conf" DONOTSTART=1 /l*v "C:\ProgramData\Zabbix\install.log"
```

Script کامل زیر Hash/Signature/TLS را بررسی، SID مستقل از زبان SYSTEM/Administrators برای ACL، رد نصب موجود، بررسی MSI 0/3010 و هویت سرویس و محدودکردن Defender Firewall به Server در Profile Domain/Private دارد. ورودی تطبیق داده شود؛ MSI دریافت یا Host ثبت نمی‌کند. حساب سرویس سفارشی Read فایل Key/Config و Write Log نیاز دارد. 3010 نیازمند Reboot زمان‌بندی‌شده و بررسی Startup است.

```powershell
#requires -RunAsAdministrator
<# Fresh-install workflow for Windows Server 2022/2025, Agent 2 7.0.22+.
Download the current stable 7.0 x64 OpenSSL MSI from the official site first.
Requires approved SHA256 AND valid Zabbix Authenticode publisher signature.
Never pass PSK values to msiexec arguments or logs. Existing installs are refused.
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory)][string]$MsiPath,
    [Parameter(Mandatory)][ValidatePattern('^[A-Fa-f0-9]{64}$')][string]$ExpectedSha256,
    [Parameter(Mandatory)][string]$ApprovedSignerThumbprint,
    [Parameter(Mandatory)][string]$ConfigPath,
    [Parameter(Mandatory)][string]$PskSourcePath,
    [string]$ServerAddress = '192.0.2.10'
)
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'
if (Get-Service -Name 'Zabbix Agent 2' -ErrorAction SilentlyContinue) { throw 'Existing Agent 2 found; use a reviewed upgrade workflow.' }
if (Get-Service -Name 'Zabbix Agent' -ErrorAction SilentlyContinue) { throw 'Classic agent exists; review port and service migration first.' }
$msi = (Resolve-Path -LiteralPath $MsiPath).Path
$config = (Resolve-Path -LiteralPath $ConfigPath).Path
$pskSource = (Resolve-Path -LiteralPath $PskSourcePath).Path
foreach ($path in @($msi, $config, $pskSource)) {
    if ((Get-Item -LiteralPath $path).Attributes -band [IO.FileAttributes]::ReparsePoint) { throw 'Reparse-point input refused.' }
    if ($path.Contains('"')) { throw 'Invalid path.' }
}
if ((Get-FileHash -LiteralPath $msi -Algorithm SHA256).Hash -ne $ExpectedSha256) { throw 'MSI hash mismatch.' }
$signature = Get-AuthenticodeSignature -LiteralPath $msi
if ($signature.Status -ne 'Valid' -or $signature.SignerCertificate.Thumbprint -ne $ApprovedSignerThumbprint) { throw 'MSI signature/publisher mismatch.' }
$content = Get-Content -LiteralPath $config -Raw
$parsedAddress = $null
if (-not [System.Net.IPAddress]::TryParse($ServerAddress, [ref]$parsedAddress)) { throw 'ServerAddress must be an approved IP address for the firewall allowlist.' }
if ($content -notmatch ('(?m)^Server=' + [regex]::Escape($ServerAddress) + '\r?$')) { throw 'Configuration Server must match the firewall ServerAddress.' }
if ($content -notmatch ('(?m)^ServerActive=' + [regex]::Escape($ServerAddress) + ':10051\r?$')) { throw 'Configuration ServerActive must match the selected monitoring server.' }
foreach ($setting in @('TLSConnect=psk','TLSAccept=psk','TLSPSKFile=C:\ProgramData\Zabbix\agent2.psk','DenyKey=system.run[*]')) {
    if (-not $content.Contains($setting)) { throw "Required configuration missing: $setting" }
}
$psk = (Get-Content -LiteralPath $pskSource -Raw).Trim()
if ($psk -notmatch '^[a-fA-F0-9]{64}$') { throw 'Expected a unique 256-bit hex PSK.' }
$data = 'C:\ProgramData\Zabbix'
if (Test-Path -LiteralPath $data) { throw 'Existing data directory refused; inspect before installation.' }
New-Item -ItemType Directory -Path $data | Out-Null
& icacls.exe $data /inheritance:r /grant:r '*S-1-5-18:(OI)(CI)F' '*S-1-5-32-544:(OI)(CI)F' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'ACL setup failed.' }
[IO.File]::WriteAllText("$data\agent2.psk", $psk + "`r`n", [Text.Encoding]::ASCII)
$psk = $null
Copy-Item -LiteralPath $config -Destination "$data\install.conf"
$arguments = "/i `"$msi`" /qn /norestart /l*v `"$data\install.log`" CONF=`"$data\install.conf`" DONOTSTART=1"
$process = Start-Process -FilePath msiexec.exe -ArgumentList $arguments -Wait -PassThru -WindowStyle Hidden
if ($process.ExitCode -notin @(0,3010)) { throw "MSI installation failed: $($process.ExitCode)" }
$target = 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf'
Copy-Item -LiteralPath "$data\install.conf" -Destination $target -Force
& icacls.exe $target /inheritance:r /grant:r '*S-1-5-18:F' '*S-1-5-32-544:F' | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Configuration ACL setup failed.' }
$service = Get-CimInstance Win32_Service -Filter "Name='Zabbix Agent 2'"
if ($service.StartName -notin @('LocalSystem','NT AUTHORITY\SYSTEM')) { throw 'Adjust ACLs for the actual service account before startup.' }
New-NetFirewallRule -Name 'Zabbix-Agent2-TLS' -DisplayName 'Zabbix Agent 2 TLS from monitoring server' -Direction Inbound -Action Allow -Protocol TCP -LocalPort 10050 -RemoteAddress $ServerAddress -Profile Domain,Private | Out-Null
# Audit and remove any broad installer-created Agent rule before enabling the service.
Get-NetFirewallRule | Where-Object { $_.DisplayName -like '*Zabbix*' -and $_.Name -ne 'Zabbix-Agent2-TLS' } | Disable-NetFirewallRule
Set-Service -Name 'Zabbix Agent 2' -StartupType Automatic
Start-Service -Name 'Zabbix Agent 2'
Get-Service -Name 'Zabbix Agent 2'
Test-NetConnection -ComputerName $ServerAddress -Port 10051
if ($process.ExitCode -eq 3010) { Write-Warning 'MSI requests a reboot; schedule it and validate service startup afterwards.' }
```

```powershell
Get-Service -Name 'Zabbix Agent 2'
Get-CimInstance Win32_Service -Filter "Name='Zabbix Agent 2'" | Select-Object Name,State,StartMode,StartName,PathName
Set-Service -Name 'Zabbix Agent 2' -StartupType Automatic
Restart-Service -Name 'Zabbix Agent 2'
Get-NetTCPConnection -LocalPort 10050 -State Listen
Test-NetConnection -ComputerName 192.0.2.10 -Port 10051
Get-NetFirewallRule -Name 'Zabbix-Agent2-TLS' | Get-NetFirewallAddressFilter
Get-Content 'C:\ProgramData\Zabbix\zabbix_agent2.log' -Tail 50
& 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.exe' -c 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf' -t agent.ping
& 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.exe' -c 'C:\Program Files\Zabbix Agent 2\zabbix_agent2.conf' -t 'service.info[Spooler,state]'
Get-WinEvent -FilterHashtable @{LogName='System'; Level=2} -MaxEvents 5
```

windows-app-01 در Data collection → Hosts با Group برابر Windows servers، Server Monitoring و Interface برابر 198.51.100.22:10050 ثبت شود. Windows by Zabbix agent active یا برای Passive برابر Windows by Zabbix agent لینک، نه هر دو. Encryption هر دو جهت PSK، هویت windows-app-01-psk و Key یکتا منطبق. Latest Data برای CPU، حافظه Available/Total، ظرفیت Disk، ترافیک Network، Service Discovery و system.uptime بررسی. Discovery سرویس به سرویس تجاری ضروری محدود؛ بسیاری از سرویس Windows عمداً Stopped/Trigger-start هستند.

Event Log به Item فعال اضافی نیاز دارد. Item برابر Zabbix agent (active) با Key برابر eventlog[System,,"Error|Critical",,,,skip]، Type برابر Log و Interval برابر 30s بسازید. skip از Replay تاریخچه شروع جلوگیری؛ تصمیم مستند شود. Filter Source/Event ID برای برنامه و رکورد تازه در Latest Data بررسی. فقط مجوز Read لازم خصوصاً Security Log داده شود. انتظار: سرویس Running/Automatic، TcpTestSucceeded=True، agent.ping=1 و Spooler در Running برابر 0؛ موفقیت TCP اثبات تحویل Active نیست.

[Property رسمی MSI و نصب GUI](https://www.zabbix.com/documentation/7.0/en/manual/installation/install_from_packages/win_msi)

[Configuration Agent 2 ویندوز](https://www.zabbix.com/documentation/7.0/en/manual/appendix/config/zabbix_agent2_win)

[Key مربوط به Service و Eventlog ویندوز](https://www.zabbix.com/documentation/7.0/en/manual/config/items/itemtypes/zabbix_agent/win_keys)

[Template رسمی Windows در release/7.0](https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/os/windows?at=refs%2Fheads%2Frelease%2F7.0)

## ۶. امن‌سازی

![لایه امنیت HTTPS و MFA، Role/API محدود، TLS Agent، Allowlist Firewall و PostgreSQL محلی](/assets/img/articles/content/zabbix-security-hardening.png)

لایه امنیت HTTPS و MFA، Role/API محدود، TLS Agent، Allowlist Firewall و PostgreSQL محلی

### HTTPS و چرخه Certificate

Certificate FQDN واقعی از CA سازمان یا ACME DNS-01 پشتیبانی‌شده برای دسترسی خصوصی. Chain/Key در مسیر زیر، Key با root 0600 و Directory با 0700؛ Master Nginx با root می‌خواند. در Server Block موجود، listen/server_name اولیه جایگزین و Directive زیر؛ Location/FastCGI حفظ. Block متعارض یا PHP عمومی افشاکننده /etc/zabbix نباشد.

```nginx
# Inside existing /etc/zabbix/nginx.conf server block:
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
# Enable HSTS only after HTTPS and renewal validation.
```

```bash
sudo install -d -o root -g root -m 0700 /etc/ssl/zabbix
# Securely install approved certificate and key before testing Nginx.
sudo chmod 0600 /etc/ssl/zabbix/privkey.pem
sudo chmod 0644 /etc/ssl/zabbix/fullchain.pem
sudo nginx -t
sudo php-fpm8.3 -t
sudo systemctl reload nginx
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com \
  -CAfile /path/to/enterprise-ca.pem -verify_return_error </dev/null
sudo ss -lntp
sudo ufw status numbered
```

انتظار: Config معتبر، HTTPS 200/Redirect و Chain/FQDN تأیید؛ هرگز curl -k. حذف 8080 اولیه و محلی بودن 5432. Renewal CA/ACME زمان‌بندی و تست، Deploy Hook ابتدا nginx -t سپس Reload و Alert انقضا/شکست. Pool PHP HTTPS با session.cookie_secure=1، session.cookie_httponly=1 و session.cookie_samesite=Lax و تست Session.

### احراز هویت Agent و کمترین مجوز

PSK تصادفی یکتای Host، TLSConnect=psk، TLSAccept=psk و جهت UI منطبق. Key مشترک Fleet ریسک نفوذ را تکثیر می‌کند. گزینه Certificate با TLSConnect/TLSAccept=cert، TLSCAFile، TLSCertFile، TLSKeyFile و Issuer/Subject مجاز؛ Constraint UI و Renewal/Revocation. Certificate HTTPS از Agent جدا است. بدون Fallback ساده یا دورزدن Peer Verification.

```conf
# Replace PSK settings for a reviewed certificate deployment:
TLSConnect=cert
TLSAccept=cert
TLSCAFile=/etc/zabbix/keys/ca.pem
TLSCertFile=/etc/zabbix/keys/agent-chain.pem
TLSKeyFile=/etc/zabbix/keys/agent.key
TLSServerCertIssuer=CN=Monitoring CA,O=Example
TLSServerCertSubject=CN=zabbix.example.com,O=Example
```

- Allowlist Firewall و Server Agent منطبق؛ محدودیت 10051، Discovery و Auto-registration.
- Agent Linux با zabbix، DenyKey=system.run[*]، UnsafeUserParameters=0 و فرمان ثابت بررسی‌شده؛ بدون sudo گسترده/Script قابل نوشتن.
- PostgreSQL محلی SCRAM/Peer، بدون trust؛ DB Remote نیازمند TLS verify-full و Firewall اختصاصی.
- Config، PSK، Credential وب، Script و Backup دارای Owner/Mode محدود؛ Denial AppArmor بررسی و مجوز باریک، نه غیرفعال‌سازی Profile.

### مدیریت قوی، RBAC، MFA، Audit و API

Users → User groups دسترسی Host Group و User roles متد UI/API را کنترل می‌کند. Operator Read-only، مالک سرویس فقط Host خودش و Administrator نام‌دار اندک. حساب اضطراری Vault با Recovery تست‌شده. Zabbix 7.0 با TOTP و Duo: Users → Authentication → MFA، Enroll و الزام روش Group بعد تست Recovery/IdP. Session محدود/حساب راکد حذف.

Administration → General → Audit log فعال، Reports → Audit log بررسی، Retention و ارسال مرکزی Log محافظت‌شده. API: کاربر سرویس کم‌مجوز، Method Allowlist صریح Role، Token انقضادار در Secret Manager و فقط HTTPS. /api_jsonrpc.php با Authorization: Bearer و JSON-RPC؛ Read مجاز و Write ردشده، Rotate/Revoke و Audit. Token ثابت یا Query URL ممنوع.

[احراز هویت PSK](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_pre_shared_keys)

[رمزنگاری Certificate](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_certificates)

[MFA در Zabbix 7.0](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/authentication/mfa)

[کنترل Role/API](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/user_roles)

[احراز هویت API رسمی](https://www.zabbix.com/documentation/7.0/en/manual/api)

[مرجع HTTPS در Nginx](https://nginx.org/en/docs/http/configuring_https_servers.html)

## ۷. مانیتورینگ و Trigger عملی

Threshold با Override Macro در Host تنظیم، نه ویرایش Template سازنده. Item سفارشی سرویس در Template محلی Enterprise Service Checks. در Hosts → Items، Key دقیق، Type برابر Zabbix agent (active)، Value Type و Interval صحیح؛ قبل Trigger چند Sample. Item کشف‌شده دوباره ساخته نشود. Expression دارای Host مشخص و نیازمند Item موجود است.

| Item و Value و Interval | کاربرد |
| --- | --- |
| vfs.fs.size[/,pused] / Float / 60s | ظرفیت Linux؛ استفاده Item کشف‌شده. |
| vfs.fs.size[C:,pused] / Float / 60s | Volume Windows؛ تأیید Key واقعی Discovery. |
| custom.service.nginx / Character / 30s | UserParameter ثابت Read-only، خروجی active/inactive/failed. |
| service.info[Spooler,state] / Unsigned / 30s | Running=0؛ سرویس ضروری انتخاب؛ Spooler مثال Lab. |
| agent.ping / Unsigned / 60s | نبود داده نشان فقدان مسیر پایش است، نه الزاماً خرابی فیزیکی Host. |

```text
# Data collection -> Hosts -> Triggers
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
last(/linux-app-01/custom.service.nginx)<>"active"
```

Trigger نام‌دار با Severity، Expression، Tagهای service/environment/team و Operational Data بسازید. برای Hysteresis، OK event generation=Recovery expression؛ شرط Problem باید False و Recovery True شود. Warning ریسک ظرفیت، Average افت، High فقدان سرویس/پایش لازم و Disaster قطعی تأییدشده حیاتی. Threshold بالاتر Disk و Dependency هشدار پایین‌تر بسازید. Dashboard: Availability/Problem، CPU/Load، Memory، فضای آزاد، Throughput و Queue.

در Staging، Threshold تست موقت کم یا فقط سرویس قابل حذف Stop؛ PROBLEM، Action و Recovery بررسی و Reset شود. Disk Production پر یا سرویس تجاری برای Drill متوقف نشود. برای تفکیک خرابی Agent/Network از سرور مرده، nodata با ICMP/Reachability مستقل ترکیب شود.

[Expression و Recovery پشتیبانی‌شده](https://www.zabbix.com/documentation/7.0/en/manual/config/triggers/expression)

[Key و Type رسمی Agent](https://www.zabbix.com/documentation/7.0/en/manual/config/items/itemtypes/zabbix_agent)

## ۸. هشدار و اعلان

تغییر Trigger رویداد می‌سازد؛ Action با Host Group، Tag و Severity فیلتر و Operation اجرا می‌کند. اعلان به Action فعال منطبق، دریافت‌کننده با Read میزبان، Media فعال و Schedule/Severity منطبق نیاز دارد. Alerts → Media types → Email: Relay مجاز، STARTTLS یا SSL/TLS، بررسی Certificate/Hostname و Credential اختصاصی. Test به گیرنده تأییدشده و اثبات تحویل Mailbox، نه صرف SMTP.

- Users → اپراتور نام‌دار → Media: Email، آدرس مجاز، 1-7,00:00-24:00 و Severity منتخب.
- Alerts → Actions → Trigger actions: environment=production و Severity حداقل Average؛ Step 1 فوراً Operations.
- Default Step Duration برابر 10m؛ Step 2 به On-call و Step 3 Incident Lead؛ Recovery و Update برای Acknowledgement.
- Event ID، Host، Severity و URL نیازمند Login؛ بدون Credential و جزئیات غیرضروری Event Log.

Telegram: Webhook رسمی همراه یا Import Media Type و README شاخه release/7.0. Bot با مراحل مستند BotFather، حفاظت Token، شروع Chat/افزودن به Group مجاز و دریافت Chat ID طبق مرجع رسمی. Parameter Token مستند و Send to برابر Chat ID؛ Test Media سپس Action کامل. HTTPS خروجی و Privacy داده خارجی تأیید. Integration Shell غیرمستند یا Token در URL/Download نباشد.

Data collection → Maintenance: Host/Tag، دوره، Timezone و With/Without Data Collection. With تاریخچه حفظ؛ Without نیازمند بررسی nodata. Pause Operation برای Problem سرکوب‌شده تنظیم. Dependency سرویس به فقدان Host/Network بالادستی از اعلان تکراری جلوگیری می‌کند. Reports → Action log برای Sent/Failed و Problems برای Suppression/Acknowledgement/Recovery.

[Media Type ایمیل](https://www.zabbix.com/documentation/7.0/en/manual/config/notifications/media/email)

[Webhook و README رسمی Telegram](https://git.zabbix.com/projects/ZBX/repos/zabbix/browse/templates/media/telegram?at=refs%2Fheads%2Frelease%2F7.0)

[Action و Escalation](https://www.zabbix.com/documentation/7.0/en/manual/config/notifications/action)

[حالت Collection در Maintenance](https://www.zabbix.com/documentation/7.0/en/manual/maintenance)

[Dependency مربوط به Trigger](https://www.zabbix.com/documentation/7.0/en/manual/config/triggers/dependencies)

## ۹. بکاپ Database و Configuration

PostgreSQL دارایی اصلی Recovery است: Host، Template، User/Permission، Item، Trigger، Action، Event، History، Trend و Audit. Export Template یا /etc/zabbix به‌تنهایی سیستم را برنمی‌گرداند. DB و Config یک مجموعه مستند، Package/Extension/Timezone و Dependency Secret ثبت شود. مثال PostgreSQL 16 بدون TimescaleDB است؛ Extension به روش سازگار مستند خودش نیاز دارد.

```bash
# Protected mounted /backup volume, on the database host.
sudo install -d -o root -g root -m 0700 /backup/zabbix/manual
sudo bash <<'BASH'
set -Eeuo pipefail
umask 077
stamp=$(date -u +%Y%m%dT%H%M%SZ)
target="/backup/zabbix/manual/$stamp"
mkdir -m 0700 "$target"
runuser -u postgres -- pg_dump -h /var/run/postgresql --role=zabbix \
  -d zabbix -Fc -Z 6 --no-owner --no-acl > "$target/zabbix.dump"
pg_restore --list "$target/zabbix.dump" > "$target/database-toc.txt"
pg_restore --file=/dev/null "$target/zabbix.dump"
cd "$target"
sha256sum zabbix.dump database-toc.txt > SHA256SUMS
sha256sum --check SHA256SUMS
BASH
```

pg_dump هنگام Write عادی Snapshot سازگار MVCC می‌گیرد؛ Schema Upgrade همزمان نباشد. Custom Format فشرده و Restore انتخابی/موازی دارد. --no-owner/--no-acl اجازه مالکیت Role محدود بازسازی‌شده می‌دهد. Dump شامل Cluster Role و Config OS نیست؛ Role بازسازی و Config جدا Archive شود. --list بررسی Catalog، --file=/dev/null Decode همه Data و SHA256 خرابی بعدی؛ فقط Restore واقعی ایزوله Recovery را ثابت می‌کند.

Peer محلی با کاربر OS برابر postgres و SET ROLE zabbix بدون Password DB است. Backup Remote نیازمند Role اختصاصی مجاز، TLS verify-full و PGPASSFILE با 0600 خارج Script؛ نه PGPASSWORD در Unit/History/URL. Archive شامل داده حساس/Credential؛ Volume محلی و کپی Off-site رمزنگاری و Recovery Key در Vault جدا.

- Archive از /etc/zabbix شامل Credential وب/PSK، /etc/nginx و تنظیم PHP-FPM/PostgreSQL.
- Chain/Key TLS و Renewal Hook، Script Alert/External، Plugin سفارشی و Dependency.
- مثال Retention: ۱۴ مجموعه روزانه محلی؛ Policy روزانه/هفتگی/ماهانه Off-site جدا براساس الزام/RPO.
- سن/شکست Backup مستقل از Zabbix پایش؛ Archive، Retrieval Off-site و Restore Drill زمان‌بندی‌شده تأیید.

### منطقی در برابر فیزیکی، pgBackRest، WAL و PITR

pg_dump منطقی Object/Data صادر و به PostgreSQL جدیدتر سازگار منتقل می‌کند؛ بین Dumpها بازیابی ندارد. Backup فیزیکی Cluster سازگار و WAL لازم، معمولاً Major/Platform یکسان. tar ساده PGDATA در حال اجرا Backup سازگار نیست. Production بزرگ از pgBackRest برای Full/Differential/Incremental، Repository Remote رمزنگاری‌شده، WAL Archiving پیوسته و PITR تست‌شده؛ Base Backup به کل زنجیره WAL نیاز دارد.

```conf
# DESIGN starting point for a separately reviewed pgBackRest deployment:
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
# pgbackrest --stanza=zabbix --type=time --target='2026-10-09 01:30:00+00' --target-action=pause restore
```

ابتدا Repository محافظت‌شده سپس دستور رسمی Remote Backup/Encryption. pg_stat_archiver و رشد WAL پایش. Timestamp هدف به Cluster ایزوله Restore، Recovery متوقف بررسی و بعد پذیرش Promote. تأخیر Archive تعیین RPO؛ دریافت/Restore Base و Replay تعیین RTO. Physical Restore روی Production فعال ممنوع. مثال pgBackRest طراحی جدا است، نه جای Script منطقی بعدی.

[Format و Consistency در pg_dump](https://www.postgresql.org/docs/16/app-pgdump.html)

[راهبرد رسمی Backup منطقی و فیزیکی](https://www.postgresql.org/docs/16/backup.html)

[راهنمای رسمی pgBackRest و WAL/PITR](https://pgbackrest.org/user-guide.html)

## ۱۰. بکاپ روزانه خودکار

Volume محافظت‌شده رمزنگاری‌شده در /backup Mount؛ در نبود Mount، Job برای حفاظت Disk OS رد می‌شود. systemd EnvironmentFile تنظیم ساده می‌دهد، نه Source قابل اجرا. Script کامل زیر دارای Permission فقط root، flock، بررسی محافظه‌کارانه Disk، Dump/Config فشرده، Decode/Checksum، انتشار Atomic، Log و Exit Code؛ مجموعه ناقص برای تشخیص و هرگز موفق محسوب نمی‌شود.

```bash
#!/usr/bin/env bash
# Ubuntu 24.04 / PostgreSQL 16. Run as root through the supplied systemd unit.
# EnvironmentFile supplies settings; this script never sources executable config.
set -Eeuo pipefail
umask 077
export PATH=/usr/sbin:/usr/bin:/sbin:/bin
BACKUP_ROOT=${BACKUP_ROOT:-/backup/zabbix}
RETENTION_DAYS=${RETENTION_DAYS:-14}
MIN_FREE_MB=${MIN_FREE_MB:-4096}
SPACE_FACTOR=${SPACE_FACTOR:-2}
PGDATABASE=${PGDATABASE:-zabbix}
PGROLE=${PGROLE:-zabbix}
OFFSITE_REQUIRED=${OFFSITE_REQUIRED:-yes}
FAILURE_HOOK=${FAILURE_HOOK:-/usr/local/sbin/zabbix-backup-notify}
stage=''
log_file=''
log() { printf '%s %s\n' "$(date -u +%FT%TZ)" "$*"; }
finish() {
    local rc=$?
    trap - EXIT
    if (( rc != 0 )); then
        log "FAILED exit=$rc; incomplete backups retained for diagnosis; retention skipped"
        if [[ -n "$log_file" && -x "$FAILURE_HOOK" ]]; then
            timeout 30 "$FAILURE_HOOK" "$rc" "$log_file" || log 'Failure notification hook failed'
        fi
    fi
    exit "$rc"
}
trap finish EXIT
[[ $EUID -eq 0 ]] || { log 'Root is required'; exit 77; }
[[ "$BACKUP_ROOT" =~ ^/backup/[a-zA-Z0-9/_-]+$ && "$BACKUP_ROOT" != *'..'* ]] || exit 64
for number in "$RETENTION_DAYS" "$MIN_FREE_MB" "$SPACE_FACTOR"; do
    [[ "$number" =~ ^[1-9][0-9]*$ ]] || exit 64
done
[[ "$PGDATABASE" =~ ^[a-zA-Z0-9_]+$ && "$PGROLE" =~ ^[a-zA-Z0-9_]+$ ]] || exit 64
[[ "$OFFSITE_REQUIRED" == yes || "$OFFSITE_REQUIRED" == no ]] || exit 64
for command in flock runuser pg_dump pg_restore psql tar gzip sha256sum df du find timeout realpath mountpoint; do
    command -v "$command" >/dev/null || { log "Missing command: $command"; exit 69; }
done
install -d -o root -g root -m 0700 /var/lib/zabbix-backup
exec 9>/var/lib/zabbix-backup/backup.lock
flock -n 9 || { log 'Another backup is running'; exit 75; }
# A dedicated mounted backup volume prevents filling the OS root filesystem.
mountpoint -q /backup || { log '/backup must be a mounted backup volume'; exit 73; }
install -d -m 0700 "$BACKUP_ROOT" /var/log/zabbix-backup
[[ "$(realpath -e "$BACKUP_ROOT")" == "$BACKUP_ROOT" ]] || exit 64
[[ "$(stat -c %u "$BACKUP_ROOT")" == 0 && "$(stat -c %a "$BACKUP_ROOT")" == 700 ]] || exit 77
stamp=$(date -u +%Y%m%dT%H%M%SZ)
log_file="/var/log/zabbix-backup/$stamp.log"
exec > >(tee -a "$log_file") 2>&1
log 'Backup started'
if [[ "$OFFSITE_REQUIRED" == yes ]]; then
    command -v restic >/dev/null || exit 69
    : "${RESTIC_REPOSITORY:?Set an encrypted off-site restic repository}"
    : "${RESTIC_PASSWORD_FILE:?Set a protected restic password file}"
    [[ -f "$RESTIC_PASSWORD_FILE" && ! -L "$RESTIC_PASSWORD_FILE" ]] || exit 77
    [[ "$(stat -c %u "$RESTIC_PASSWORD_FILE")" == 0 && "$(stat -c %a "$RESTIC_PASSWORD_FILE")" == 600 ]] || exit 77
fi
# These directories contain secrets. Do not publish this archive or put it in Git.
paths=(etc/zabbix etc/nginx etc/php/8.3/fpm etc/postgresql/16/main etc/ssl/zabbix
       etc/zabbix-backup etc/systemd/system/zabbix-backup.service
       etc/systemd/system/zabbix-backup.timer etc/systemd/system/zabbix-backup-failure.service
       usr/local/sbin/zabbix-backup.sh)
for optional in etc/letsencrypt usr/lib/zabbix/alertscripts usr/lib/zabbix/externalscripts usr/local/lib/zabbix usr/local/sbin/zabbix-backup-notify etc/logrotate.d/zabbix-backup; do
    [[ ! -e "/$optional" ]] || paths+=("$optional")
done
for path in "${paths[@]}"; do [[ -e "/$path" ]] || { log "Missing required path: /$path"; exit 66; }; done
db_bytes=$(runuser -u postgres -- psql -X -At -v ON_ERROR_STOP=1 -d "$PGDATABASE" -c 'SELECT pg_database_size(current_database())')
[[ "$db_bytes" =~ ^[0-9]+$ ]] || exit 65
config_bytes=$(du -scb "${paths[@]/#//}" | tail -n 1 | cut -f 1)
required_mb=$(( (db_bytes * SPACE_FACTOR + config_bytes) / 1048576 + MIN_FREE_MB ))
free_mb=$(df -Pm "$BACKUP_ROOT" | awk 'NR==2 {print $4}')
(( free_mb >= required_mb )) || { log "Insufficient space: free=${free_mb}MiB required=${required_mb}MiB"; exit 73; }
stage=$(mktemp -d "$BACKUP_ROOT/.incomplete-$stamp-XXXXXX")
# The database role owns the schema but has no superuser/createdb/createrole rights.
# pg_dump's MVCC snapshot is consistent while ordinary monitoring writes continue.
runuser -u postgres -- pg_dump -h /var/run/postgresql --role="$PGROLE" \
    -d "$PGDATABASE" --format=custom --compress=6 --no-owner --no-acl > "$stage/zabbix.dump"
# tar exits nonzero if selected files change. Schedule configuration changes separately.
tar --acls --xattrs --numeric-owner -czf "$stage/configuration.tar.gz" -C / "${paths[@]}"
dpkg-query -W -f='${Package}\t${Version}\n' 'zabbix*' 'postgresql*' 'nginx*' 'php8.3*' > "$stage/packages.tsv"
pg_restore --list "$stage/zabbix.dump" > "$stage/database-toc.txt"
# Decode all archive data, not just its table of contents; still not a restore test.
pg_restore --file=/dev/null "$stage/zabbix.dump"
gzip -t "$stage/configuration.tar.gz"
tar -tzf "$stage/configuration.tar.gz" > "$stage/configuration-files.txt"
[[ -s "$stage/zabbix.dump" && -s "$stage/configuration.tar.gz" ]] || exit 65
(
    cd "$stage"
    sha256sum zabbix.dump configuration.tar.gz packages.tsv database-toc.txt configuration-files.txt > SHA256SUMS
    sha256sum --check SHA256SUMS
)
date -u +%FT%TZ > "$stage/VERIFIED"
final="$BACKUP_ROOT/$stamp"
[[ ! -e "$final" ]] || exit 73
mv -- "$stage" "$final"
stage=''
if [[ "$OFFSITE_REQUIRED" == yes ]]; then
    # Configure transport authentication separately (SSH agent or restic credentials).
    # Bounded operation fails the job instead of leaving retention running on timeout.
    timeout 6h restic backup --tag zabbix -- "$final"
    timeout 1h restic check
    date -u +%FT%TZ > "$final/OFFSITE_OK"
fi
date -u +%FT%TZ > "$final/SUCCESS"
# Delete only completed, real timestamp directories AFTER a verified successful backup.
# Exact cutoff uses directory names; altered directory mtimes cannot shorten retention.
cutoff=$(date -u -d "$RETENTION_DAYS days ago" +%Y%m%dT%H%M%SZ)
while IFS= read -r -d '' old; do
    name=${old##*/}
    [[ "$name" =~ ^[0-9]{8}T[0-9]{6}Z$ && "$name" < "$cutoff" ]] || continue
    [[ "$old" != "$final" && -f "$old/SUCCESS" && ! -L "$old" ]] || continue
    [[ "$(realpath -e "$old")" == "$BACKUP_ROOT/$name" ]] || exit 64
    rm -rf --one-file-system -- "$old"
done < <(find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -print0)
date -u +%FT%TZ > "$BACKUP_ROOT/last-success.txt"
# Log retention is independent of backup retention and runs only on success.
find /var/log/zabbix-backup -maxdepth 1 -type f -name '*.log' -mtime +90 -delete
log "SUCCESS backup=$final offsite=$OFFSITE_REQUIRED"
```

Exit Code: 64 تنظیم غلط، 65 Data نامعتبر، 66 فایل غایب، 69 Dependency، 73 Mount/Storage، 75 Overlap، 77 Permission؛ شکست PostgreSQL/tar/restic غیرصفر می‌ماند. EXIT Trap، Hook مستقل با Timeout؛ OnFailure برای Startup/Time-limit. Relay مجاز یا Hook تأییدشده و Alert مستقل سن Success برای Timer اجرا‌نشده/Host مرده؛ ایمیل شکست کافی نیست.

```conf
# Install as /etc/zabbix-backup/backup.env, root:root 0600.
# Plain assignments only; compatible with systemd EnvironmentFile.
BACKUP_ROOT=/backup/zabbix
PGDATABASE=zabbix
PGROLE=zabbix
RETENTION_DAYS=14
MIN_FREE_MB=4096
SPACE_FACTOR=2
OFFSITE_REQUIRED=yes
# Replace with your actual off-site server; RFC 5737 address is documentation only.
RESTIC_REPOSITORY=sftp:backup@203.0.113.30:/srv/restic/zabbix
RESTIC_PASSWORD_FILE=/etc/zabbix-backup/restic-password
FAILURE_HOOK=/usr/local/sbin/zabbix-backup-notify
```

```ini
[Unit]
Description=Verified PostgreSQL and Zabbix configuration backup
Wants=network-online.target
After=network-online.target postgresql.service
RequiresMountsFor=/backup
OnFailure=zabbix-backup-failure.service

[Service]
Type=oneshot
User=root
Group=root
UMask=0077
StateDirectory=zabbix-backup
StateDirectoryMode=0700
EnvironmentFile=/etc/zabbix-backup/backup.env
ExecStart=/usr/local/sbin/zabbix-backup.sh
TimeoutStartSec=8h
Nice=10
IOSchedulingClass=best-effort
IOSchedulingPriority=7
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=read-only
ReadWritePaths=/backup /var/log/zabbix-backup /var/lib/zabbix-backup /var/cache/restic
ProtectKernelTunables=true
ProtectKernelModules=true
ProtectControlGroups=true
RestrictSUIDSGID=true
LockPersonality=true
```

```ini
[Unit]
Description=Daily Zabbix backup at 02:15 UTC

[Timer]
OnCalendar=*-*-* 02:15:00 UTC
RandomizedDelaySec=10m
Persistent=true
Unit=zabbix-backup.service

[Install]
WantedBy=timers.target
```

```ini
[Unit]
Description=Notify independent operations channel of Zabbix backup failure

[Service]
Type=oneshot
User=root
UMask=0077
ExecStart=/usr/local/sbin/zabbix-backup-notify 1 journal:zabbix-backup.service
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
```

```bash
#!/usr/bin/env bash
# Requires mailutils and a separately configured authenticated TLS mail relay.
# Must be root-owned 0750; edit the destination before operational acceptance.
set -euo pipefail
umask 077
rc=${1:-1}
reference=${2:-journal:zabbix-backup.service}
command -v mail >/dev/null || { logger -p daemon.err 'Zabbix backup: notification transport unavailable'; exit 69; }
printf 'Zabbix backup failed on %s. Exit: %s. Evidence: %s\nInspect locally; no credentials or logs are attached.\n' \
    "$(hostname)" "$rc" "$reference" | mail -s 'Zabbix backup failure' ops@example.com
```

```bash
sudo apt install -y restic mailutils
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
systemctl list-timers --all zabbix-backup.timer
```

```bash
# Protected root session; use the SAME non-secret settings as backup.env.
export RESTIC_REPOSITORY='sftp:backup@203.0.113.30:/srv/restic/zabbix'
export RESTIC_PASSWORD_FILE=/etc/zabbix-backup/restic-password
restic init       # ONLY for a newly provisioned empty repository, once
restic snapshots
restic check
# Schedule a separate full-data verification:
restic check --read-data
# Separate approved remote retention policy AFTER verified snapshots:
# restic forget --tag zabbix --keep-daily 14 --keep-weekly 8 --keep-monthly 12 --prune
```

Off-site قبل اولین Job مقداردهی؛ Shell، EnvironmentFile را ارث نمی‌برد. OFFSITE_REQUIRED=yes پیش‌فرض Production: شکست Upload/Check یعنی Job ناموفق و بدون Retention. no فقط Lab مستند یا Transfer مستقل تأییدشده با Age/Failure Monitoring. Retention محلی فقط Timestamp موفق قدیمی طبق Policy پس از جایگزین معتبر و Off-site موفق. Log ۹۰ روز؛ ناقص نیازمند Cleanup بررسی‌شده. Retention Remote جدا و کپی Immutable/Offline؛ Storage قابل نوشتن به‌تنهایی ضد Ransomware نیست. Checksum و Metadata Check جای Full Data/Restore Drill نیست.

[Setup Repository رمزنگاری‌شده restic](https://restic.readthedocs.io/en/stable/030_preparing_a_new_repo.html)

[Integrity و Full Data Check در restic](https://restic.readthedocs.io/en/stable/045_working_with_repos.html)

## ۱۱. Restore و بازیابی بحران

![زنجیره Backup معتبر DB/Config، Off-site رمزنگاری‌شده، Restore Drill ایزوله و PITR مبتنی بر WAL](/assets/img/articles/content/zabbix-backup-disaster-recovery.png)

زنجیره Backup معتبر DB/Config، Off-site رمزنگاری‌شده، Restore Drill ایزوله و PITR مبتنی بر WAL

RPO حداکثر بازه قابل قبول فقدان Data/Config و RTO حداکثر زمان Recovery سرویس. Dump روزانه نزدیک یک روز به‌علاوه تأخیر Transfer/Schedule؛ بدون Age Monitoring، ۲۴ ساعت تضمین نیست. PITR به پوشش WAL Archive نزدیک می‌شود. Retrieval، Install، Restore، Secret Recovery و Validation برای RTO اندازه‌گیری. Downtime، Communication و مرجع واحد Cutover؛ Server تکراری Collect/Alert با یک هویت اجرا نشود.

۱–۲. Ubuntu 24.04 جایگزین در VLAN ایزوله: Patch، IP/DNS، NTP، Firewall. ترافیک Agent/Proxy و SMTP/Telegram خارجی مسدود، Timer متوقف. Patch ثبت‌شده سازگار Zabbix 7.0 و PostgreSQL 16/PHP/Nginx نصب. Snapshot رمزنگاری‌شده منتخب به Staging فقط root، حفظ اصل و بررسی SHA256/Decode. Zabbix قدیمی روی Schema جدید ممنوع؛ Startup ممکن است Upgrade غیرقابل برگشت دهد. Package ثبت‌شده آسیب‌پذیر با Upgrade پشتیبانی‌شده روی Clone تمرین شود.

```bash
# ISOLATED REPLACEMENT ONLY; adapt selected-set to retrieved snapshot.
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
sudo tar --acls --xattrs --numeric-owner -xzf /restore/zabbix/selected-set/configuration.tar.gz -C /restore/configuration
```

۳. قبل pg_restore، server.sql.gz اولیه وارد نشود. pg_restore نسخه 16 یا ابزار جدیدتر سازگار مستند. ۴–۶. /etc/zabbix، Nginx/PHP-FPM، Chain/Key TLS، تنظیم PostgreSQL و Script Alert/External در Staging بررسی؛ انتقال انتخابی با Owner/Mode ثبت‌شده، تطبیق ID حساب، IP، نام Certificate و Interpreter. PGDATA/Config OS کورکورانه Overwrite نشود. کپی‌های زیر Config جایگزین را بازنویسی می‌کنند و فقط برای جایگزین بررسی‌شده‌اند. برای Drill، DBName=zabbix_recovery و DBPassword Reset در Server/Frontend و Rule SCRAM محدود آن.

```bash
# OVERWRITES REPLACEMENT CONFIG: review staged files and destination host first.
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
# host zabbix_recovery zabbix 127.0.0.1/32 scram-sha-256
```

```bash
# 7. Start required services only after configuration review.
sudo pg_ctlcluster 16 main reload
sudo php-fpm8.3 -t
sudo nginx -t
sudo systemctl start php8.3-fpm zabbix-server nginx
sudo systemctl status zabbix-server php8.3-fpm nginx --no-pager
sudo tail -n 100 /var/log/zabbix/zabbix_server.log
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
# Start only disposable lab agents after name/network/TLS validation.
```

۸–۱۰. System Information/Schema، شمارش با رکورد، Graph تاریخی، داده تازه Lab Linux/Windows، PROBLEM/OK و اعلان به Test Sink ایزوله. RBAC/MFA، HTTPS، Script، Queue، Maintenance/Dependency و Backup جایگزین تست. Host زنده در Isolation داده قدیمی دارد؛ هویت Clone Lab یا Reroute کنترل‌شده نه Collector تکراری. آخرین Sample، زمان Retrieval/Start/Validation و RPO/RTO ثبت. سپس تأیید Cutover IP/DNS، Alert و Timer؛ Source Rollback حفظ.

### فرمان مخرب — فقط Cutover جایگزین تأییدشده

dropdb دیتابیس موجود را دائماً حذف می‌کند. جدا از Drill با نام جدید؛ فقط جایگزین تأییدشده با Backup معتبر، هویت Host ثبت‌شده و تأیید عملیاتی صریح. هرگز Source زنده. --clean یا Extract روی /etc نیز Overwrite و نیازمند Review.

```bash
# DESTRUCTIVE, intentionally commented: confirmed replacement host only.
sudo systemctl stop nginx zabbix-server
# sudo -u postgres dropdb --if-exists zabbix
# sudo -u postgres createdb -O zabbix -E UTF8 -T template0 zabbix
# Restore the verified dump into this EMPTY DB as above, no initial schema import.
```

[سازگاری و Ownership در pg_restore](https://www.postgresql.org/docs/16/app-pgrestore.html)

[سازگاری Upgrade مربوط به Schema/Version](https://www.zabbix.com/documentation/7.0/en/manual/installation/upgrade)

## ۱۲. عیب‌یابی Production

نشانه ← علت ریشه‌ای ← فرمان تشخیص ← نتیجه مورد انتظار ← راه‌حل. Expected Result توصیف زیرساخت سالم است نه نتیجه Runtime در Workspace سایت. از Server/Proxy تعیین‌شده تشخیص و پیش از Restart شاهد حفظ شود. رفع شکست با غیرفعال‌کردن TLS/Firewall/AppArmor/احراز هویت ممنوع.

### Server شروع نمی‌شود

نشانه: Unit ناموفق یا Server Down. علت ریشه‌ای: Config، DB/Schema یا تداخل Port.

```bash
sudo systemctl status zabbix-server --no-pager
sudo journalctl -u zabbix-server -n 100 --no-pager
sudo tail -n 100 /var/log/zabbix/zabbix_server.log
sudo ss -lntp
```

نتیجه مورد انتظار: Running، Listener 10051 و بدون Fatal DB. راه‌حل: اولین خطای Log و سازگاری پیش از Restart اصلاح.

### شکست اتصال Database

نشانه: Refused یا Authentication Failed. علت ریشه‌ای: DBHost/Password/HBA غلط یا Cluster متوقف.

```bash
pg_lsclusters
pg_isready -h 127.0.0.1
psql -h 127.0.0.1 -U zabbix -W -d zabbix -c 'SELECT 1;'
sudo -u postgres psql -X -c 'SELECT line_number,error FROM pg_hba_file_rules;'
```

نتیجه مورد انتظار: Online، Accepting و SELECT 1 موفق. راه‌حل: Credential محافظت‌شده و ترتیب SCRAM اصلاح؛ بدون trust.

### Agent در دسترس نیست

نشانه: داده OS قدیمی یا Icon Passive قرمز. علت ریشه‌ای: Agent متوقف، Interface/Allowlist/TLS غلط.

```bash
sudo systemctl status zabbix-agent2 --no-pager
sudo tail -n 50 /var/log/zabbix/zabbix_agent2.log
sudo ufw status numbered
# Windows: Get-Service -Name "Zabbix Agent 2"
```

نتیجه مورد انتظار: Running، Ping رمزنگاری‌شده 1 و داده Active تازه. راه‌حل: مسیر/TLS اصلاح؛ Heartbeat فعال برای Active-only.

### عدم تطابق Hostname

نشانه: Host Not Found در Active. علت ریشه‌ای: Hostname با Host name فنی متفاوت.

```bash
sudo grep '^Hostname=' /etc/zabbix/zabbix_agent2.conf
sudo tail -n 50 /var/log/zabbix/zabbix_agent2.log
```

نتیجه مورد انتظار: نام دقیق حساس به حروف و Host فعال. راه‌حل: Hostname نه Visible name تطبیق؛ Restart و انتظار Refresh.

### Active کار نمی‌کند

نشانه: Passive موفق ولی Active غایب. علت ریشه‌ای: ServerActive/Proxy یا Type Template غلط.

```bash
sudo grep -E '^(ServerActive|Hostname|TLSConnect)=' /etc/zabbix/zabbix_agent2.conf
nc -vz 192.0.2.10 10051
sudo tail -n 50 /var/log/zabbix/zabbix_agent2.log
```

نتیجه مورد انتظار: List/Data فعال در مقصد تعیین‌شده قبول. راه‌حل: مقصد تعیین‌شده، Item/Template فعال و Encryption UI اصلاح.

### مشکل TCP/10050

نشانه: Timeout/Refused در Passive. علت ریشه‌ای: Listener یا Allowlist Poller غایب.

```bash
# On agent:
sudo ss -lntp | grep ':10050'
sudo ufw status numbered
# On assigned Server/Proxy:
nc -vz 198.51.100.21 10050
```

نتیجه مورد انتظار: Agent Listen و Source مجاز دسترسی. راه‌حل: ListenIP/Source Rule اصلاح؛ در Active-only لازم نیست.

### مشکل TCP/10051

نشانه: Timeout/Refused در Active. علت ریشه‌ای: Listener مقصد، Route یا Egress.

```bash
sudo ss -lntp | grep ':10051'
# From agent:
nc -vz 192.0.2.10 10051
ip route get 192.0.2.10
```

نتیجه مورد انتظار: Listener صحیح و Route/TCP مجاز. راه‌حل: مسیر واقعی/Allowlist Source NAT اصلاح با حفظ TLS.

### عدم تطابق TLS PSK

نشانه: خطای Handshake/Unknown Identity. علت ریشه‌ای: Identity/Key/جهت متفاوت یا فایل ناخوانا.

```bash
sudo grep -E '^(TLSConnect|TLSAccept|TLSPSKIdentity|TLSPSKFile)=' /etc/zabbix/zabbix_agent2.conf
sudo -u zabbix test -r /etc/zabbix/keys/agent2.psk
sudo tail -n 50 /var/log/zabbix/zabbix_agent2.log
```

نتیجه مورد انتظار: Key محافظت‌شده خوانا و UI/Agent منطبق. راه‌حل: باز‌تأمین امن Key یکتا، اصلاح جهت و تست TLS.

### Item پشتیبانی نمی‌شود

نشانه: Item برابر Not Supported. علت ریشه‌ای: Key/Type، Permission، Plugin یا Discovery غلط.

```bash
sudo -u zabbix zabbix_agent2 -c /etc/zabbix/zabbix_agent2.conf -t 'custom.service.nginx'
# Read item error under Hosts -> Items.
```

نتیجه مورد انتظار: Value Type‌دار نه ZBX_NOTSUPPORTED. راه‌حل: خطای دقیق با Key نسخه release/7.0 و حداقل دسترسی اصلاح.

### داده دریافت نمی‌شود

نشانه: Timestamp قدیمی Latest Data. علت ریشه‌ای: Host/Item غیرفعال، Maintenance، Queue/Clock.

```bash
sudo tail -n 100 /var/log/zabbix/zabbix_server.log
chronyc tracking
# Frontend: host/item status, Maintenance and Administration -> Queue
```

نتیجه مورد انتظار: Value فعال پس از Interval تازه و Queue محدود. راه‌حل: Collection لازم، Time/Interval اصلاح؛ تأخیر DB بررسی.

### اتمام Storage دیتابیس

نشانه: Disk پر، شکست Insert، رشد Queue. علت ریشه‌ای: رشد History/WAL/Log یا Archiving ناموفق.

```bash
df -hT
df -i
sudo -u postgres psql -X -d zabbix -c 'SELECT pg_size_pretty(pg_database_size(current_database()));'
sudo -u postgres psql -X -c 'SELECT * FROM pg_stat_archiver;'
```

نتیجه مورد انتظار: Block/Inode آزاد و WAL Archiving سالم. راه‌حل: افزایش ایمن، اصلاح Archiving/Retention؛ WAL/DB دستی حذف نشود.

### شکست Backup

نشانه: Unit ناموفق یا Last Success قدیمی. علت ریشه‌ای: Mount/Space، Credential، فایل تغییرکرده یا Off-site.

```bash
sudo systemctl show zabbix-backup.service -p Result -p ExecMainStatus
sudo journalctl -u zabbix-backup.service -n 100 --no-pager
mountpoint /backup
df -h /backup
systemctl list-timers --all zabbix-backup.timer
```

نتیجه مورد انتظار: Result=success، Exit 0، SUCCESS تازه و Snapshot Off-site. راه‌حل: علت Log رفع، قدیمی معتبر حفظ، اجرا/دریافت مجدد و کانال Failure تست.

## ۱۳. چک‌لیست Production

- [ ] LTS/Package پایدار و Advisory ثبت؛ مالک/Window Patch مشخص.
- [ ] DNS/IP ثابت، NTP، IOPS/Capacity، فضای DB/WAL و Mount رمزنگاری‌شده /backup تأیید.
- [ ] Role محدود PostgreSQL، SCRAM/Peer و Loopback تست دسترسی مثبت/منفی موفق.
- [ ] سرویس Server/Frontend/Linux/Windows پس از Reboot؛ Log/Permission منطبق بسته.
- [ ] Template Active/Passive، Hostname/Proxy صحیح و داده تازه CPU/RAM/Disk/Network/Load/Uptime.
- [ ] Service ضروری و Event Log پوشش؛ Exclusion/Threshold بررسی.
- [ ] PROBLEM/OK، Severity، Hysteresis، Maintenance، Dependency/Escalation امن تست.
- [ ] تحویل Action/Recovery ایمیل و Telegram رسمی با Test User مجاز تأیید.
- [ ] Renewal/Chain HTTPS، PSK/Cert یکتا و Firewall محدود موفق؛ بدون Fallback ناامن.
- [ ] Admin نام‌دار، MFA/RBAC/API محدود، Audit Retention، Session و دسترسی اضطراری تست.
- [ ] Timer، Lock، Space/Integrity، Transport شکست و Alert مستقل سن Success تست.
- [ ] Snapshot Off-site رمزنگاری‌شده دریافت؛ Key Vault جدا، Retention/Immutable تأیید.
- [ ] Restore ایزوله DB/Config/TLS/Script، Host/History/Trigger/Notification با RPO/RTO اندازه‌گیری‌شده کافی.
- [ ] Operations مالک Dashboard، Check Storage/Queue/Backup و Runbook رخداد/Capacity/Change/Rollback.

## تحویل عملیاتی و پذیرش Runtime

Workspace سایت، Integration مقاله و Syntax ایستا را بررسی می‌کند؛ Zabbix/PostgreSQL زنده نصب یا Backup واقعی، MSI، اعلان و Restore اجرا نمی‌کند. شاهد پذیرش Staging پیش از Production تکمیل و ثبت شود. Version، مسیر Secret محافظت‌شده، Diagram، Dashboard، مالک Alert، Retention و شاهد Restore اندازه‌گیری‌شده به Operations تحویل شود.

## پرسش‌های متداول

### چرا 7.0 LTS به جای 8.0 RC؟

Lifecycle بررسی‌شده 7.0 را آخرین LTS منتشرشده می‌داند. RC برای Production نیست؛ Stable Selector قبل استقرار دوباره بررسی شود.

### کدام Template با Agent 2 کار می‌کند؟

Template رسمی Linux by Zabbix agent و Windows by Zabbix agent؛ برای Active نوع Active انتخاب شود.

### Port لازم چیست؟

Agent فعال به Server/Proxy روی TCP/10051؛ Poll غیرفعال به Agent روی TCP/10050 با TLS و Allowlist.

### آیا /etc/zabbix به‌تنهایی بکاپ است؟

خیر؛ PostgreSQL همراه Config محافظت‌شده، TLS و Script سفارشی بازیابی شود.

### Script Password DB ذخیره می‌کند؟

خیر؛ Peer محلی با کاربر OS برابر postgres و Role محدود zabbix. Credential Off-site جدا محافظت می‌شود.

### بکاپ قدیمی چه زمانی حذف می‌شود؟

فقط پس از Verification جدید و Off-site لازم موفق، برای مجموعه تکمیل‌شده قدیمی طبق Retention.

### آیا Dump، PITR می‌دهد؟

Dump به Snapshot برمی‌گردد؛ PITR نیازمند Base Backup فیزیکی و زنجیره کامل WAL است.

### Checksum اثبات Recovery است؟

خیر؛ Host، Data، Trigger و Notification در Isolation بازیابی/تأیید و RPO/RTO ثبت شود.

## منابع رسمی، دانلود و مقاله مرتبط

منابع در هر فصل لینک هستند. Template فاقد Credential واقعی و نیازمند آدرس/Secret مجاز و Validation Staging است. فایل منفرد یا بسته کامل دریافت کنید.

[دانلود: Configuration Agent 2 لینوکس](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-agent2-linux.conf)

[دانلود: Configuration Agent 2 ویندوز](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-agent2-windows.conf)

[دانلود: نصب MSI با PowerShell](/downloads/zabbix-server-linux-windows-agents-backup/install-zabbix-agent2.ps1)

[دانلود: Script Bash بکاپ معتبر](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup.sh)

[دانلود: Template محیط بکاپ](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup.env)

[دانلود: سرویس systemd بکاپ](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup.service)

[دانلود: Timer روزانه systemd](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup.timer)

[دانلود: Unit اعلان شکست](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup-failure.service)

[دانلود: Hook مستقل اعلان](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-backup-notify)

[دانلود: Runbook بازیابی](/downloads/zabbix-server-linux-windows-agents-backup/restore-runbook.md)

[دانلود: چک‌لیست امنیت](/downloads/zabbix-server-linux-windows-agents-backup/security-checklist.md)

[دانلود: README بسته](/downloads/zabbix-server-linux-windows-agents-backup/README.md)

[ZIP کامل Configuration و Runbook](/downloads/zabbix-server-linux-windows-agents-backup/zabbix-configuration-package.zip)

[مرتبط: Backup/Recovery سازمانی Oracle](https://meetaj.ir/articles/oracle-database-26ai-installation-oracle-linux)

[مرتبط: امنیت سرویس Tomcat](https://meetaj.ir/articles/apache-tomcat-linux-installation-security-hardening)

[مرتبط: استقرار Production MongoDB](https://meetaj.ir/articles/mongodb-installation-configuration-production-deployment)

[مرتبط: مانیتورینگ Production Redis](https://meetaj.ir/articles/redis-installation-configuration-replication)

[مرتبط: ممیزی امنیت Linux](https://meetaj.ir/articles/linux-security-auditor-bash)

[مرتبط: Nginx روی Ubuntu](https://meetaj.ir/articles/nginx-installation-configuration-ubuntu)

[مرتبط: داشبورد گرافانا متصل به زبیکس](https://meetaj.ir/articles/grafana-installation-zabbix-integration)
