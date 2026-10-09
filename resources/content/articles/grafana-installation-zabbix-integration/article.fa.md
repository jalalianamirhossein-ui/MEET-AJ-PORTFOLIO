# آموزش جامع نصب گرافانا روی لینوکس و اتصال به زبیکس همراه با ساخت داشبوردهای مانیتورینگ سازمانی

نصب Grafana OSS روی Ubuntu 24.04، اتصال API به Zabbix 7.0، ساخت داشبورد لینوکس، ویندوز و NOC، امنیت HTTPS و اعتبارسنجی بکاپ و بازیابی.

## ۱. مقدمه: مسئولیت گرافانا و زبیکس

گرافانا برنامه‌ای برای نمایش داده و مشاهده‌پذیری است. بخش Backend احراز هویت، درخواست منبع داده، ذخیره داشبورد و ارزیابی زمان‌بندی‌شده هشدار را انجام می‌دهد و Frontend مرورگر پنل‌ها را نمایش می‌دهد. گرافانا منبع داده را Query می‌کند و جایگزین جمع‌آوری‌کننده مانیتورینگ نیست. در این معماری Agent زبیکس معیارهای سیستم‌عامل را جمع‌آوری می‌کند، Zabbix Server تاریخچه و Trend را در PostgreSQL ذخیره و Trigger را ارزیابی می‌کند و گرافانا با افزونه امضاشده زبیکس و API مبتنی بر HTTPS داده را نمایش می‌دهد.

![جریان داده سازمانی: Agent به زبیکس؛ Query گرافانا از API زبیکس با HTTPS؛ دسترسی اپراتور از Nginx و HTTPS](/assets/img/articles/content/grafana-zabbix-enterprise-architecture.png)

جریان داده سازمانی: Agent به زبیکس؛ Query گرافانا از API زبیکس با HTTPS؛ دسترسی اپراتور از Nginx و HTTPS

| مفهوم | کاربرد عملیاتی |
| --- | --- |
| منبع داده | اتصال تنظیم‌شده مانند Zabbix، Prometheus، PostgreSQL یا Loki؛ Credential روی سرور نگهداری می‌شود. |
| داشبورد / پنل | داشبورد پنل‌ها را مرتب می‌کند؛ پنل ترکیب Query، Transformation، واحد و نوع نمایش است. |
| متغیرها | انتخاب‌گر قابل استفاده مجدد برای گروه میزبان، میزبان، تگ آیتم و Interface؛ متغیر فیلتر است و مرز مجوز نیست. |
| Trigger / Problem زبیکس | زبیکس عبارت آیتم را ارزیابی، رخداد را ثبت و Action و Escalation را اجرا می‌کند. |
| هشدار گرافانا | Rule مستقل سمت سرور با Contact point و Notification policy؛ Query منتخب افزونه از هشدار پشتیبانی می‌کند. |

Grafana OSS داشبورد، نقش پایه سازمان، مجوز پوشه و هشدار را ارائه می‌کند. نسخه Enterprise دارای License قابلیت‌هایی مانند RBAC دقیق و مجوز منبع داده دارد. معماری مانیتورینگ سازمانی الزاماً به نسخه Enterprise نیاز ندارد. Viewer در OSS می‌تواند منبع داده سازمان را Query کند؛ محدود کردن داشبورد داده زیرین را محدود نمی‌کند. برای جداسازی داده از سازمان و منبع داده جدا با کاربر زبیکس دارای محدوده مناسب استفاده کنید.

سازمان‌ها برای صفحه مشترک NOC، مقایسه زیرساخت و برنامه، روند ظرفیت، ارتباط رخداد و نمای مدیریتی سرویس این دو ابزار را یکپارچه می‌کنند. زبیکس مرجع جمع‌آوری و رخداد باقی می‌ماند. دامنه خرابی را مشخص نگه دارید: SQLite گرافانا مخزن Metadata آن است و PostgreSQL نمودار همان دیتابیس موجود زبیکس است. گرافانا در صورت نیاز دسترس‌پذیری می‌تواند دیتابیس PostgreSQL مستقل خود را داشته باشد.

[مستند رسمی نقش‌ها و محدودیت نسخه گرافانا](https://grafana.com/docs/grafana/latest/administration/roles-and-permissions/)

## ۲. پیش‌نیازها و نسخه‌های پشتیبانی‌شده

از میزبان اختصاصی Ubuntu Server 24.04 LTS استفاده کنید. برای استقرار متوسط از ۲ vCPU، حافظه ۴ گیگابایت و دیسک ۲۰ تا ۴۰ گیگابایت با Volume مستقل بکاپ شروع کنید؛ این مقادیر پیشنهاد ظرفیت هستند و تضمین اندازه‌گیری‌شده نیستند. تعداد کاربر هم‌زمان، پنل، Refresh، ارزیابی هشدار و Log محلی را لحاظ کنید. گرافانا تمام تاریخچه زبیکس را در دیتابیس خود کپی نمی‌کند.

| جزء | مبنای استقرار |
| --- | --- |
| اوبونتو | 24.04 LTS با به‌روزرسانی امنیتی جاری؛ systemd، Nginx و TLS معتبر. |
| گرافانا OSS | نسخه پایدار 13.2.3 طبق انتخاب‌گر دانلود رسمی (انتشار ۲۹ سپتامبر ۲۰۲۶)؛ نصب Candidate مخزن پایدار APT. |
| افزونه زبیکس | نسخه پایدار 6.9.1، بروزرسانی کاتالوگ ۶ اکتبر ۲۰۲۶، نیازمند Grafana >=11.6.0؛ شناسه alexanderzobnin-zabbix-app. |
| زبیکس | Server/Frontend/API موجود شاخه 7.0 LTS با مجوز خواندن؛ Template سیستم‌عامل شاخه 7.0. |

نسخه‌ها در ۹ اکتبر ۲۰۲۶ بررسی شدند. نسخه 13.2.3 حداقل نیاز افزونه را برآورده می‌کند و مستند افزونه رفتار Token در Zabbix 7.0 را صریحاً پوشش می‌دهد. این سازگاری مستند است و آزمون اتصال زنده نیست. پیش از هر پنجره نگهداری Release note و Candidate بسته را دوباره بررسی کنید. مخزن Beta، Nightly یا Release Candidate انتخاب نکنید.

```text
grafana.example.com  192.0.2.20
zabbix.example.com   192.0.2.10
linux-app-01        198.51.100.21
windows-app-01      198.51.100.22
backup.example.com  203.0.113.30
```

همه IPهای بالا از محدوده مستندسازی RFC 5737 هستند و باید جایگزین شوند. رکورد A/AAAA را فقط برای آدرس واقعاً قابل دسترس ثبت کنید. برای Certificate عمومی دامنه تحت مالکیت خود لازم است؛ example.com برای شما صادر نمی‌شود. پیش از نصب DNS، مسیر Frontend زبیکس، دسترسی خروجی API، زنجیره CA معتبر و همگام‌سازی زمان را بررسی کنید.

```bash
hostnamectl
sudo hostnamectl set-hostname grafana.example.com
getent ahosts grafana.example.com zabbix.example.com
timedatectl status
sudo timedatectl set-ntp true
timedatectl timesync-status
curl --fail --silent --show-error --connect-timeout 5 https://zabbix.example.com/ -o /dev/null
```

| جهت / پورت | هدف و سیاست |
| --- | --- |
| اپراتور به گرافانا TCP 443 | HTTPS از VPN مدیریتی یا شبکه مجاز. |
| ACME به Nginx TCP 80 | برای اعتبارسنجی و تمدید HTTP-01 لازم؛ در صورت منع HTTP ورودی از DNS-01 استفاده کنید. |
| گرافانا به زبیکس TCP 443 | فقط API مبتنی بر HTTPS؛ پورت 10051 همان API منبع داده نیست. |
| Nginx به گرافانا TCP 3000 | فقط Loopback؛ بدون مجوز Firewall عمومی. |
| مدیریت TCP 22 | SSH از شبکه مدیریت؛ پیش از تغییر UFW یک Session فعال حفظ کنید. |
| اختیاری TCP 5432 / 10051 | 5432 فقط برای Direct DB اختیاری؛ 10051 فقط Sender جمع‌آوری‌کننده NOC با مبدا محدود و TLS PSK. |

[انتخاب‌گر رسمی نسخه پایدار Grafana OSS](https://grafana.com/grafana/download?edition=oss)

[کاتالوگ رسمی افزونه امضاشده زبیکس و نیازمندی‌ها](https://grafana.com/grafana/plugins/alexanderzobnin-zabbix-app/)

## ۳. نصب گرافانا روی اوبونتو و بررسی systemd

![نصب اوبونتو: بررسی کلید، مخزن پایدار، بسته، تنظیم Loopback و سلامت سرویس](/assets/img/articles/content/grafana-ubuntu-installation-workflow.png)

نصب اوبونتو: بررسی کلید، مخزن پایدار، بسته، تنظیم Loopback و سلامت سرویس

دستورات زیر را روی میزبان اختصاصی گرافانا در پنجره نگهداری اجرا کنید. ارتقای معوق اوبونتو و نیاز Restart را پیش از راه‌اندازی سرویس وابسته بررسی کنید. امضای مخزن از Keyring محدود استفاده می‌کند و دستور منسوخ apt-key به کار نمی‌رود. پیش از اعتماد به کلید، Fingerprint اصلی را با صفحه رسمی مخزن تطبیق دهید.

```bash
sudo apt-get update
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
grafana --version
```

APT با کلید محدود، Metadata و Hash بسته مخزن را بررسی می‌کند. Fingerprint زمان بررسی B53AE77BADB630A683046005963FA27710458545 است؛ در چرخش آینده توقف و کلید جایگزین را مستقل بررسی کنید. Candidate پایدار ممکن است از 13.2.3 جدیدتر شود؛ نسخه نصب‌شده را ثبت و Release note افزونه را بررسی کنید، بسته قدیمی را تحمیل نکنید.

### پیکربندی نمونه پیش از دسترسی اپراتور

بسته را در پوشه کار اپراتور دانلود و دستورهای Copy بعدی را از آنجا اجرا کنید. پیش از جایگزینی grafana.ini در میزبان جدید از تنظیم پیش‌فرض بکاپ بگیرید. یک Secret key یکتا بسازید، محافظت کنید و در بازیابی حفظ کنید؛ تغییر بعدی می‌تواند مانع رمزگشایی Credential منبع داده شود. Secret نمونه موجود را بازنویسی نکنید.

```bash
sudo cp -a /etc/grafana/grafana.ini /etc/grafana/grafana.ini.before-enterprise
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
curl --fail --silent --show-error --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health
```

```ini
[paths]
data = /var/lib/grafana
logs = /var/log/grafana
plugins = /var/lib/grafana/plugins
provisioning = /etc/grafana/provisioning
[server]
protocol = http
http_addr = 127.0.0.1
http_port = 3000
domain = grafana.example.com
enforce_domain = true
root_url = https://grafana.example.com/
[database]
type = sqlite3
path = grafana.db
[security]
secret_key = $__file{/etc/grafana/secrets/secret_key}
cookie_secure = true
cookie_samesite = lax
disable_gravatar = true
[users]
allow_sign_up = false
auto_assign_org_role = Viewer
[auth.anonymous]
enabled = false
[log]
mode = console file
level = info
[dashboards]
min_refresh_interval = 30s
```

enable --now هم سرویس را راه‌اندازی می‌کند و هم اجرای Boot را فعال می‌کند. status باید active (running)، is-enabled مقدار enabled و پاسخ Health مقدار database: ok و نسخه نصب‌شده را نشان دهد. خروجی ss باید 127.0.0.1:3000 باشد، نه 0.0.0.0:3000. journalctl خطای Startup، Migration و افزونه را نشان می‌دهد؛ Log حاوی Credential منتشر نکنید. root_url نام خارجی HTTPS است و protocol به دلیل پایان TLS در Nginx همچنان http می‌ماند.

برای دسترسی اولیه پیش از HTTPS از تونل موقت SSH استفاده کنید: ssh -L 3000:127.0.0.1:3000 operator@grafana.example.com. اگر enforce_domain و Cookie امن ورود HTTP را مسدود کردند ابتدا فصل HTTPS را کامل کنید؛ تنظیم Production را حفظ کنید. پس از آماده‌شدن TLS آدرس https://grafana.example.com/login را باز، با مقدار اولیه admin/admin نمونه جدید وارد و فوراً رمز یکتای مدیریت‌شده در Vault تعیین کنید. حساب مدیر نام‌دار و حساب اضطراری کنترل‌شده بسازید؛ رمز Bootstrap را به اشتراک نگذارید.

[دستور نصب رسمی اوبونتو](https://grafana.com/docs/grafana/latest/setup-grafana/installation/debian/)

[Fingerprint رسمی کلید امضای مخزن](https://apt.grafana.com/)

## ۴. امنیت عملیاتی، Nginx و HTTPS

پورت عمومی 3000 می‌تواند TLS، کنترل دسترسی و Log در Nginx را دور بزند. گرافانا را به Loopback متصل، ورودی 443 را فقط از شبکه مدیریت مجاز باز و SSH محدود را حفظ کنید. IPv6 را جدا بررسی کنید. پورت 80 زیر برای ACME HTTP-01 است؛ سایت فقط VPN باید Certificate معتبر CA سازمانی یا DNS-01 با Provider DNS مجاز داشته باشد.

```bash
sudo ufw allow from 192.0.2.0/24 to any port 22 proto tcp
sudo ufw allow from 192.0.2.0/24 to any port 443 proto tcp
sudo ufw allow 80/tcp
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw enable
sudo ufw status verbose
```

### صدور Certificate معتبر پیش از فعال‌سازی Virtual host TLS

دامنه و مسیر Certificate هر دو فایل Nginx را جایگزین کنید. ابتدا فقط سایت HTTP Bootstrap را نصب کنید زیرا nginx -t نمی‌تواند Certificate ناموجود را بارگذاری کند. مطمئن شوید سایت فعال دیگری همان server_name را ندارد. روش Webroot در Certbot زیر DNS و TCP 80 عمومی می‌خواهد؛ آدرس RFC 5737 نوشته‌شده برای صدور مناسب نیست.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name grafana.example.com;
    location ^~ /.well-known/acme-challenge/ { root /var/www/acme; }
    location / { return 404; }
}
```

```bash
sudo apt-get install -y certbot
sudo install -d -m 0755 /var/www/acme
sudo install -m 0644 nginx-bootstrap.conf /etc/nginx/sites-available/grafana
sudo ln -s /etc/nginx/sites-available/grafana /etc/nginx/sites-enabled/grafana
sudo nginx -t
sudo systemctl reload nginx
sudo certbot certonly --webroot -w /var/www/acme -d grafana.example.com
sudo install -m 0644 nginx-grafana.conf /etc/nginx/sites-available/grafana
sudo nginx -t
sudo systemctl reload nginx
curl --fail --silent --show-error https://grafana.example.com/api/health
```

```nginx
# Include inside nginx's http context (Ubuntu sites-enabled is in http).
map $http_upgrade $grafana_connection_upgrade {
    default upgrade;
    '' close;
}
upstream grafana_backend {
    server 127.0.0.1:3000;
    keepalive 16;
}
server {
    listen 80;
    listen [::]:80;
    server_name grafana.example.com;
    location ^~ /.well-known/acme-challenge/ {
        root /var/www/acme;
    }
    location / { return 301 https://grafana.example.com$request_uri; }
}
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name grafana.example.com;
    ssl_certificate /etc/letsencrypt/live/grafana.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/grafana.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_session_cache shared:GrafanaTLS:10m;
    ssl_session_timeout 1d;
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Content-Type-Options nosniff always;
    # Optional management/VPN allowlist, ONLY after ACME/bootstrap works:
    # allow 192.0.2.0/24;
    # deny all;
    location / {
        proxy_pass http://grafana_backend;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header Connection "";
        proxy_read_timeout 60s;
    }
    location /api/live/ {
        proxy_pass http://grafana_backend;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $grafana_connection_upgrade;
        proxy_read_timeout 3600s;
    }
}
```

map و upstream داخل Context مربوط به http هستند؛ sites-enabled اوبونتو در همان قسمت Include می‌شود. Headerهای Forwarded در Proxy مورد اعتماد بازنویسی می‌شوند. مسیر /api/live/ هدر Upgrade برای WebSocket را منتقل می‌کند. nginx -t پیش از Reload باید syntax is ok و test is successful گزارش کند؛ این فقط تنظیم را بررسی می‌کند و سلامت برنامه یا صدور CA واقعی را اثبات نمی‌کند. HSTS را پس از کارکرد HTTPS حفظ و اثر Cache آن را در Rollback لحاظ کنید.

```bash
sudo install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
printf '%s\n' '#!/bin/sh' 'nginx -t && systemctl reload nginx' | sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx >/dev/null
sudo chmod 0755 /etc/letsencrypt/renewal-hooks/deploy/reload-nginx
sudo systemctl enable --now certbot.timer
sudo certbot renew --dry-run
sudo systemctl list-timers certbot.timer
openssl s_client -connect grafana.example.com:443 -servername grafana.example.com -verify_return_error </dev/null
```

- با HTTPS از cookie_secure=true و SameSite=lax استفاده؛ Secure و HttpOnly را در ابزار مرورگر بررسی کنید. دسترسی ناشناس و ثبت‌نام آزاد را غیرفعال کنید.
- به اپراتور NOC نقش Viewer، نویسنده داشبورد Editor و فقط مدیر منبع داده نقش Admin سازمان بدهید. مدیر Server مسئولیت ممتاز جداگانه است.
- OIDC/OAuth یا LDAP مجاز را ترجیح دهید؛ MFA را در IdP اعمال و Role mapping و حذف حساب را آزمون کنید. حساب اضطراری با ممیزی نگه دارید.
- افزونه امضاشده از کاتالوگ رسمی نصب، نسخه ثبت و ارتقا در Staging بررسی کنید؛ استثنای افزونه بدون امضا را غیرفعال نگه دارید. برای کارکرد پنل به کاربر اتصال مجوز Write/Acknowledge ندهید.
- انقضای Certificate، شکست تمدید، ورود ناموفق و سلامت سرویس را از خارج گرافانا پایش کنید. دسترسی Config، کلید رمزگذاری و بکاپ را محدود کنید.

[تنظیم رسمی Reverse Proxy و Grafana Live](https://grafana.com/tutorials/run-grafana-behind-a-proxy/)

[تنظیم امنیت رسمی گرافانا](https://grafana.com/docs/grafana/latest/setup-grafana/configure-security/)

[Certbot: Webroot، تمدید و Deploy hook](https://eff-certbot.readthedocs.io/en/stable/using.html)

## ۵. نصب و فعال‌سازی افزونه رسمی زبیکس

```bash
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins install alexanderzobnin-zabbix-app
sudo systemctl restart grafana-server
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls
sudo journalctl -u grafana-server -n 100 --no-pager
sudo python3 -c 'import json; p=json.load(open("/var/lib/grafana/plugins/alexanderzobnin-zabbix-app/plugin.json")); print(p["id"],p["info"]["version"],p["dependencies"]["grafanaDependency"])'
```

CLI جاری grafana cli است؛ grafana-cli مستقل منسوخ است و در Grafana 13.x شکست می‌خورد. pluginsDir صریح با مسیر بسته Debian تطبیق دارد. کاتالوگ پایدار رسمی هنگام بررسی نسخه 6.9.1 ارائه می‌کند. شناسه App و Dependency را از plugin.json و Startup موفق افزونه امضاشده را در Journal بررسی کنید. نصب فایل به‌تنهایی App plugin را فعال نمی‌کند.

در UI جاری مسیر Administration > Plugins and data > Plugins را باز، Zabbix را جستجو و در صفحه آن Enable را انتخاب کنید. سپس Connections > Add new connection > Zabbix > Add new data source در دسترس می‌شود. برای Provisioning تکرارپذیر zabbix-app.yaml را در /etc/grafana/provisioning/plugins/ نصب و Restart کنید. Default بسته را تغییر ندهید و Angular قدیمی را فعال نکنید.

```yaml
apiVersion: 1
apps:
  - type: alexanderzobnin-zabbix-app
    org_id: 1
    disabled: false
```

```bash
sudo install -d -o root -g grafana -m 0750 /etc/grafana/provisioning/plugins
sudo install -o root -g grafana -m 0640 zabbix-app.yaml /etc/grafana/provisioning/plugins/zabbix-app.yaml
sudo systemctl restart grafana-server
# Upgrade during a tested maintenance window, after backing up:
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins update alexanderzobnin-zabbix-app
sudo systemctl restart grafana-server
```

هر ارتقا را با همان نسخه گرافانا، API زبیکس، داشبورد و Rule هشدار در Staging بررسی کنید. در ناسازگاری Dependency، Artifact افزونه قبلی مجاز و Snapshot هماهنگ Grafana/Database را بازیابی کنید. برای Pin نسخه افزونه از آرگومان نسخه مستند CLI مانند plugins install alexanderzobnin-zabbix-app 6.9.1 در نصب Staging تازه استفاده کنید. شکست امضا یا نسخه را با اجازه اجرای کد بدون امضا حل نکنید.

[نصب افزونه رسمی زبیکس و قابلیت‌های پشتیبانی‌شده](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/)

## ۶. اتصال گرافانا به API زبیکس با HTTPS

![یکپارچه‌سازی: کاربر اختصاصی Read-only زبیکس، Token مدت‌دار API، Query سمت سرور و Save & test](/assets/img/articles/content/grafana-zabbix-plugin-integration.png)

یکپارچه‌سازی: کاربر اختصاصی Read-only زبیکس، Token مدت‌دار API، Query سمت سرور و Save & test

### آماده‌سازی مجوز زبیکس و بررسی Endpoint

روی میزبان زبیکس systemctl status zabbix-server و پاسخ HTTPS رابط وب را بررسی کنید. در Zabbix 7.0 به Users > User groups بروید و Grafana readers بسازید. در Host permissions فقط به گروه‌های مجاز Linux، Windows و جمع‌آوری‌کننده NOC دسترسی Read بدهید. در Users > User roles نقش از نوع User با Methodهای خواندن موردنیاز افزونه مانند hostgroup.get، host.get، hostinterface.get، item.get، history.get، trend.get، trigger.get، problem.get، event.get و Method کمکی مانند user.get/valuemap.get در صورت استفاده بسازید. API را مجاز، Method نوشتن را ممنوع و خطای Method مفقود را بررسی کنید؛ Super admin انتخاب نکنید.

در Users > Users کاربر اتصال نام‌دار بسازید، گروه و Role را متصل و رمز تعاملی را در Vault نگه دارید. مدیر می‌تواند برای صاحب حساب در Users > API tokens توکن با انقضا بسازد؛ خود کاربر در صورت مجوز Role از User settings > API tokens توکن خود را مدیریت می‌کند. یک‌بار Generate، امن ذخیره و با هم‌پوشانی Rotate کنید. Token مجوز میزبان و Role صاحب حساب را به ارث می‌برد و مستقل دسترسی نمی‌دهد.

```bash
ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php
curl --fail --silent --show-error --connect-timeout 5 --max-time 30 \
  -H 'Content-Type: application/json-rpc' \
  --data '{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}' \
  "$ZABBIX_API_URL"
```

پاسخ JSON-RPC دارای result شامل 7.0.x مسیر API رابط وب را تأیید می‌کند؛ HTML، Redirect ورود یا 404 تأیید نیست. اگر Frontend زیر /zabbix نصب شده با https://zabbix.example.com/zabbix/api_jsonrpc.php تکرار کنید. apiinfo.version بدون احراز هویت است و مجوز میزبان یا صحت Token را اثبات نمی‌کند. مسیر URL را فرض نکنید. CA trust را از همان میزبان گرافانا بررسی کنید و با curl -k یا Skip TLS verify اعتبارسنجی را دور نزنید.

بسته شامل API probe امن است که از فایل Token محدود و Header Authorization روی HTTPS استفاده می‌کند؛ توکن واقعی در History دستور یا Screenshot قرار نمی‌گیرد. فقط نسخه API و تعداد میزبان قابل مشاهده چاپ می‌شود. پس از افزودن قابلیت Allowlist نقش را بررسی کنید؛ جزئیات خطای API می‌تواند Method ممنوع را مشخص کند. Header Authorization را در Reverse Proxy/PHP رابط زبیکس حفظ کنید.

```bash
sudo test -e /etc/grafana/secrets/zabbix-token || sudo install -o root -g root -m 0600 /dev/null /etc/grafana/secrets/zabbix-token
sudoedit /etc/grafana/secrets/zabbix-token
sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py
```

### تنظیم و آزمون منبع داده گرافانا

| تنظیم | مقدار / اعتبارسنجی |
| --- | --- |
| نام / URL | Zabbix؛ Endpoint کامل و بررسی‌شده api_jsonrpc.php با HTTPS. |
| نوع احراز / Token | API token؛ فقط در Field امن و Maskشده وارد کنید. |
| Trend | فعال؛ After برابر 7d و Range برابر 4d فقط در صورت تطبیق Retention تاریخچه. |
| Cache TTL / مهلت | Cache متادیتا 1h؛ API timeout برابر 30s و queryTimeout برابر 60s. |
| Direct DB / تأیید رخداد | Direct DB خاموش؛ منع Acknowledge کاربر Read-only روشن. |

پس از افزودن، Connections > Data sources > Zabbix را باز کنید. URL بررسی‌شده، Auth type برابر API token و Additional settings > Trends / Zabbix API را تنظیم کنید. Save & test را بزنید. خروجی موفق مورد انتظار “Zabbix API version” همراه نسخه شناسایی‌شده 7.0 است؛ متن ممکن است بین Patchها تغییر کند. سپس محدودبودن Host picker به گروه مجاز، Query واقعی آیتم CPU و دریافت Point را بررسی کنید. موفقیت نسخه API به‌تنهایی کارکرد Query آیتم را اثبات نمی‌کند.

```yaml
apiVersion: 1
datasources:
  - name: Zabbix
    uid: zabbix-enterprise
    type: alexanderzobnin-zabbix-datasource
    access: proxy
    url: ${ZABBIX_API_URL}
    isDefault: true
    jsonData:
      authType: token
      trends: true
      trendsFrom: 7d
      trendsRange: 4d
      cacheTTL: 1h
      timeout: 30
      queryTimeout: 60
      dbConnectionEnable: false
      disableReadOnlyUsersAck: true
      disableDataAlignment: false
    secureJsonData:
      apiToken: $ZABBIX_API_TOKEN
    version: 1
    editable: false
```

```bash
sudo install -m 0600 -o root -g root grafana.env.example /etc/grafana/grafana-integration.env
sudoedit /etc/grafana/grafana-integration.env
sudo install -d -m 0755 /etc/systemd/system/grafana-server.service.d
sudo install -m 0644 grafana-environment.conf /etc/systemd/system/grafana-server.service.d/integration.conf
sudo install -o root -g grafana -m 0640 zabbix-datasource.yaml /etc/grafana/provisioning/datasources/zabbix.yaml
sudo systemctl daemon-reload
sudo systemctl restart grafana-server
sudo journalctl -u grafana-server -n 100 --no-pager
```

پیش از Restart توکن نمونه را محلی جایگزین کنید. systemd فایل EnvironmentFile محافظت‌شده را می‌خواند و متغیر را به گرافانا می‌دهد؛ Provisioning مقدار $ZABBIX_API_TOKEN را در secureJsonData.apiToken حل می‌کند. فرم تک‌دلار کاراکتر دلار واقعی توکن را حفظ می‌کند. با editable:false به‌جای ویرایش UI، YAML را تغییر و version را افزایش دهید. متغیر خالی یا مفقود موجب شکست احراز هویت است. systemctl show Environment را نمایش ندهید و فایل ویرایش‌شده را به پوشه دانلود عمومی کپی نکنید.

[Field رسمی احراز هویت، Provisioning و Save & test](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/configure/)

[مدیریت توکن API در Zabbix 7.0](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/api_tokens)

## ۷. ساخت داشبورد مانیتورینگ لینوکس

![نمونه آموزشی داشبورد Linux و Windows: مصرف منابع، فضای فایل‌سیستم، ترافیک Interface و دسترس‌پذیری](/assets/img/articles/content/grafana-linux-windows-monitoring-dashboard.png)

نمونه آموزشی داشبورد Linux و Windows: مصرف منابع، فضای فایل‌سیستم، ترافیک Interface و دسترس‌پذیری

از میزبان‌های ثبت‌شده موجود در زبیکس استفاده کنید. Template رسمی Linux by Zabbix agent یا نسخه Active مناسب را به Ubuntu مانند linux-app-01 متصل کنید. پیش از گرافانا در Data collection > Hosts > Items و Monitoring > Latest data آیتم Supported و مقدار تازه را بررسی کنید. Agent 2 می‌تواند معیارهای Template رسمی Agent را ارائه کند. اجازه دهید Low-level discovery آیتم واقعی فایل‌سیستم و Interface بسازد؛ Prototype حل‌نشده {#IFNAME} یا {#FSNAME} را Query نکنید.

| پنل | آیتم موجود / کلید | نمایش / واحد |
| --- | --- | --- |
| مصرف CPU | CPU utilization — system.cpu.util | Gauge، درصد ۰ تا ۱۰۰ |
| بار CPU | Load average (1m avg) — system.cpu.load[all,avg1]؛ همچنین avg5 و avg15 | Time series، بدون واحد؛ مقایسه با تعداد CPU |
| RAM / قابل استفاده | Memory utilization — vm.memory.utilization؛ Available memory — vm.memory.size[available] | Gauge درصد / Time series بایت |
| مصرف / فضای فایل‌سیستم | FS [/]: Space: Used, in % / Space: Available؛ vfs.fs.dependent.size[/,pused] / [/,free] | Gauge درصد / بایت؛ انتخاب Mount کشف‌شده |
| شبکه / Interface | Interface ens18: Bits received / Bits sent — net.if.in[ens18] / net.if.out[ens18] پس از Preprocessing تمپلیت | Time series بیت بر ثانیه؛ از قبل Rate هستند |
| Uptime / دسترس‌پذیری | System uptime — system.uptime؛ Zabbix agent availability — zabbix[host,agent,available] | Stat ثانیه / ۰ نامشخص، ۱ در دسترس، ۲ خارج از دسترس |
| رخداد فعال | Query نوع Problems فیلترشده با Group و Host | پنل Zabbix Problems، رخداد جاری |

نام‌های بالا از Template رسمی 7.0 هستند؛ Label کشف‌شده Interface و Mount به میزبان بستگی دارد. درصد مصرف FS از معنای فضای قابل استفاده Template پیروی می‌کند و در فایل‌سیستم دارای Reserved block ممکن است با محاسبه ساده df تفاوت داشته باشد. agent.ping را شاخص Down در نظر نگیرید؛ آخرین مقدار ذخیره‌شده در قطعی می‌تواند ۱ باقی بماند. دسترس‌پذیری را با Trigger مربوط به No-data/Availability، رخداد و Timestamp تازه همراه کنید؛ استقرار فقط Active ممکن است به zabbix[host,active_agent,available] به‌جای آیتم Passive نیاز داشته باشد.

- Dashboards > New > New dashboard > Add visualization را باز کنید. Zabbix، Query نوع Metrics، Group برابر Linux servers، Host برابر linux-app-01 و Item برابر CPU utilization انتخاب کنید. Query را اجرا و آخرین مقدار را با Latest data زبیکس مقایسه کنید.
- Gauge، واحد Percent (0–100)، حداقل ۰، حداکثر ۱۰۰ و Threshold مطلق سبز زیر ۸۰، نارنجی از ۸۰ و قرمز از ۹۰ تعیین کنید. رنگ Threshold فقط نمایش است و Trigger یا Rule هشدار نمی‌سازد.
- با جدول پنل Time series بار CPU و حافظه قابل استفاده، Gauge دیسک، Time series شبکه و Stat مربوط به Uptime/Availability بسازید. واحد حافظه بایت، شبکه بیت بر ثانیه و Uptime ثانیه باشد.
- نمایش Zabbix Problems افزونه با Query نوع Problems، گزینه Show Problems، Group برابر $group و Host برابر $host، همه Severityها و Use time range خاموش برای رخداد جاری اضافه کنید. اتصال را Read-only نگه دارید.
- در Enterprise monitoring با بازه پیش‌فرض ۶ ساعت و Refresh یک دقیقه ذخیره کنید. linux-dashboard.json را از Dashboards > New > Import وارد یا از File provider همراه استفاده کنید. فایل به UID ثابت zabbix-enterprise اشاره می‌کند؛ در UID متفاوت همه Referenceها را تغییر دهید.

[Template و Preprocessing رسمی Linux در Zabbix 7.0](https://github.com/zabbix/zabbix/blob/release/7.0/templates/os/linux/template_os_linux.yaml)

### Provisioning فایل داشبورد بررسی‌شده

```bash
sudo install -d -o root -g grafana -m 0750 /var/lib/grafana/dashboards
sudo install -o root -g grafana -m 0640 linux-dashboard.json windows-dashboard.json enterprise-noc-dashboard.json /var/lib/grafana/dashboards/
sudo install -o root -g grafana -m 0640 dashboard-provider.yaml /etc/grafana/provisioning/dashboards/enterprise.yaml
sudo systemctl restart grafana-server
```

```yaml
apiVersion: 1
providers:
  - name: Enterprise monitoring
    orgId: 1
    folder: Enterprise monitoring
    type: file
    disableDeletion: true
    updateIntervalSeconds: 60
    allowUiUpdates: false
    options:
      path: /var/lib/grafana/dashboards
```

برای UID یکسان از UI import یا File provisioning استفاده کنید و مالک هم‌زمان نداشته باشید. Provider پوشه Enterprise monitoring را ایجاد، فایل را هر 60s بررسی و Save در UI برای داشبورد File-owned را غیرفعال می‌کند. Overview مربوط به NOC تا نصب Collector استفاده نشود. JSON منبع را در Version control به‌روز و پیش از جایگزینی بررسی کنید.

[Field پشتیبانی‌شده Query متریک و Problems](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/query-editor/)

## ۸. ساخت داشبورد Windows Server

برای میزبان موجود Windows Agent 2 داشبورد Windows جدا بسازید. Template رسمی Windows by Zabbix agent یا نسخه Active، Interface، فایل‌سیستم و سرویس را کشف می‌کند؛ فیلتر Discovery و نسخه Agent لازم را در Template نصب‌شده 7.0 بررسی کنید. استثنای Service discovery را مرور کنید تا سرویس عمداً متوقف‌شده رخداد غیرضروری نسازد. گرافانا آیتم موجود را انتخاب می‌کند و Agent نصب یا Performance counter ویندوز را فعال نمی‌کند.

| پنل | انتخاب‌گر آیتم رسمی 7.0 / کلید |
| --- | --- |
| CPU / RAM | CPU utilization — system.cpu.util؛ Memory utilization — vm.memory.util؛ Used memory — vm.memory.size[used] |
| دیسک / فضای آزاد | FS [label(C:)]: Space: Used, in % / Space: Available؛ آیتم فایل‌سیستم Dependent از vfs.fs.get |
| توان عملیاتی شبکه | Interface name(alias): Bits received / Bits sent؛ کلید کشف‌شده net.if.in / net.if.out |
| سرویس ویندوز | State of service "Spooler" (Print Spooler) — service.info["Spooler",state] در صورت کشف |
| Uptime / دسترس‌پذیری | Uptime — system.uptime (نام آیتم Linux متفاوت است)؛ Zabbix agent availability |
| Problems / Event Log | Query رخداد جاری Problems؛ Query نوع Text برای آیتم Active جداگانه Event Log |

windows-dashboard.json را Import، گروه Windows servers و میزبان را انتخاب و هر Query را اجرا کنید. CPU/Memory/Disk را Gauge، شبکه را Time series و وضعیت سرویس را Stat با Value mapping نمایش دهید. service.info برای Running مقدار ۰، Stopped مقدار ۶ و سرویس ناموجود ۲۵۵ دارد؛ Mapping دسترس‌پذیری ۱/۲ را برای Service state تکرار نکنید. Threshold نمونه ۸۰/۹۰ برای CPU/Memory صرفاً نمایش است و باید با مبنای سرویس تنظیم شود.

### افزودن صریح مانیتورینگ Windows Event Log

Template پیش‌فرض سیستم‌عامل Event Log دلخواه را جمع‌آوری نمی‌کند. روی میزبان Windows با اتصال Active Agent 2، آیتم Zabbix agent (active) با نام Windows Application errors، نوع اطلاعات Log، فاصله 30s، تاریخچه 7d و کلید eventlog پشتیبانی‌شده زیر بسازید. حالت skip از رخداد جدید شروع می‌کند و کل Log را بازخوانی نمی‌کند. مجوز Agent باید خواندن Channel منتخب را اجازه دهد؛ Security log بررسی مجوز جداگانه می‌خواهد.

```text
eventlog[Application,,"Error|Critical",,,,skip]
```

تگ component:eventlog را به آیتم سفارشی اضافه کنید. یک Application error مجاز در Staging تولید و قبل از Query نوع Text گرافانا دریافت آن در Latest data را بررسی کنید. پنل Table دانلودشده همان نام سفارشی را Query می‌کند و تا ساخت آیتم و دریافت رخداد جدید مطابق فیلتر، به‌درستی No data نشان می‌دهد. محتوای رخداد ممکن است حساس باشد؛ کاربر و Retention را محدود کنید. رخداد Log و هشدار بر پایه Count عددی را جدا تنظیم کنید.

[Template رسمی Windows زبیکس: نام واقعی آیتم](https://github.com/zabbix/zabbix/blob/release/7.0/templates/os/windows_agent/template_os_windows_agent.yaml)

[کلید پشتیبانی‌شده Agent ویندوز، service.info و eventlog](https://www.zabbix.com/documentation/7.0/en/manual/config/items/itemtypes/zabbix_agent/win_keys)

## ۹. داشبورد NOC سازمانی با شمارنده پیاده‌سازی‌شده

![نمونه آموزشی NOC: تعداد میزبان در محدوده، دسترس‌پذیری نامشخص، رخداد بحرانی، مصرف‌کننده برتر و آخرین Problem](/assets/img/articles/content/grafana-enterprise-noc-dashboard.png)

نمونه آموزشی NOC: تعداد میزبان در محدوده، دسترس‌پذیری نامشخص، رخداد بحرانی، مصرف‌کننده برتر و آخرین Problem

Overview تعریف واقعی داشبورد با Collector همراه است و عدد نمایشی ثابت ندارد. Query نوع Problems افزونه جدول رخداد و Metrics سری آیتم می‌دهد؛ هیچ‌کدام اینجا به‌عنوان شمارنده خودکار موجودی کل میزبان معرفی نمی‌شود. noc-collector.py اختیاری از host.get و problem.get استفاده و سپس با zabbix_sender و TLS PSK هشت معیار Trapper صریح را منتشر می‌کند. این روش اتصال مستقیم دیتابیس نمی‌خواهد.

| معیار | تعریف پیاده‌سازی‌شده |
| --- | --- |
| کل میزبان مانیتورشده | میزبان فعال مانیتورشده قابل مشاهده برای کاربر API در ID گروه تعیین‌شده، بدون تکرار از host.get؛ میزبان Summary مستثنا باشد. |
| در دسترس / خارج از دسترس / نامشخص | در دسترس اگر هر وضعیت Active-agent/Interface برابر ۱؛ خارج از دسترس اگر هیچ ۱ و حداقل یک ۲؛ در سایر موارد نامشخص. مجموع برابر کل است و SLA عمومی سلامت برنامه نیست. |
| Problem فعال / بحرانی / High | problem.get با countOutput و recent:false، source 0/object 0 و همان ID میزبان؛ بحرانی برابر Disaster (۵)، High دقیقاً ۴؛ رخداد Suppressed لحاظ می‌شود مگر سیاست صریح تغییر کند. |
| تازگی Collector | Timestamp پس از Query موفق API؛ Trigger پنج دقیقه nodata در Template. داده مفقود یا قدیمی هرگز به معنای صفر رخداد نیست. |
| بیشترین مصرف‌کننده | آخرین مقدار Non-null هر سری، Reduce به Row، Sort نزولی و Limit ده؛ CPU/RAM برای میزبان، دیسک برای FS و شبکه برای Interface. |
| Problems عملیاتی | پنل Problems افزونه با ستون Host، Host group، Severity، Status و Acknowledgment؛ همه Severity جاری. |

### نصب Collector و Template خلاصه NOC

- در Data collection > Templates > Import فایل zabbix-noc-template.json را Import کنید. گروه NOC collectors و میزبان فنی noc-production با نام نمایشی Production NOC summary بسازید؛ Enterprise NOC summary را متصل کنید.
- ماکروی میزبان {$NOC.SENDER.IP} را IP واقعی Collector قرار دهید؛ رمزگذاری ورودی PSK با Identity یکتای noc-production و PSK تولیدشده در فایل محافظت‌شده تنظیم کنید. allowed_hosts آیتم Trapper و Firewall Server فقط همان مبدا را مجاز کنند.
- برای Collector توکن API Read-only جدا با گروه‌های محیط مانیتورشده بسازید؛ host.get و problem.get لازم است. ID گروه را با hostgroup.get بگیرید. گروه Production/Linux servers و Production/Windows servers بسازید یا Regex گروه داشبورد را با قرارداد خود تغییر دهید.
- Token و PSK محافظت‌شده را در /etc/grafana/secrets/noc-token و noc.psk با مجوز root:noc-collector 0640 قرار دهید. noc.env را با ID واقعی گروه، API URL، آدرس Server و نام فنی میزبان کامل کنید. Secret را در Template عمومی قرار ندهید.

```bash
sudo apt-get install -y zabbix-sender
sudo useradd --system --no-create-home --shell /usr/sbin/nologin noc-collector
sudo install -d -o root -g noc-collector -m 0750 /usr/local/lib/grafana-noc
sudo install -m 0755 noc-collector.py /usr/local/lib/grafana-noc/noc-collector.py
sudo install -o root -g noc-collector -m 0640 noc-collector.env.example /etc/grafana/noc.env
sudoedit /etc/grafana/noc.env
sudo install -m 0644 noc-collector.service noc-collector.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl start noc-collector.service
sudo journalctl -u noc-collector.service -n 30 --no-pager
sudo systemctl enable --now noc-collector.timer
```

از Build مربوط به zabbix_sender دارای TLS PSK استفاده کنید؛ اگر مخزن رسمی تنظیم است بسته نگهداری‌شده Zabbix 7.0 ترجیح دارد. جمع‌آوری موفق Submitted 8 NOC metrics چاپ می‌کند؛ هر هشت مقدار Latest data را در زبیکس بررسی کنید. شکست API یا محدوده خالی میزبان مجاز باعث شکست Collector می‌شود و صفر گمراه‌کننده منتشر نمی‌شود. Reject در Sender نیز Service را Fail می‌کند. شکست و Trigger مربوط به nodata را با Action زبیکس خارج از گرافانا پایش کنید.

enterprise-noc-dashboard.json را Import کنید. شمارنده از Environment و میزبان Summary آن پیروی می‌کند؛ با محدودکردن Group/Host بخش Performance، همچنان شمارنده محدوده تنظیم‌شده Collector باقی می‌ماند. محدوده را واضح نمایش و تعداد را با زبیکس تطبیق دهید. برای Staging میزبان جدا noc-staging با نام نمایشی Staging NOC summary و Collector مستقل بسازید. محیط بدون Collector انتخاب نکنید. رخداد Manual/Suppressed شناخته‌شده و میزبان Canary خارج از دسترس باید با تعریف شمارش تطبیق داشته باشد.

Statهای Overview را بالا، Top 10 مربوط به CPU/RAM/FS/Interface را وسط و Problems جاری را پایین قرار دهید. Timestamp آخرین موفقیت کنار شمارنده باشد، متن No data حفظ و داده ناموجود با صفر جایگزین نشود. Acknowledge رخداد Workflow کنترل‌شده زبیکس باشد؛ اتصال Read-only گرافانا نمی‌تواند رخداد را تأیید کند. Availability مربوط به API/Interface انتقال مانیتورینگ را نشان می‌دهد؛ برای SLO سرویس معیار سلامت برنامه اضافه کنید.

[محدوده میزبان مانیتورشده در host.get زبیکس](https://www.zabbix.com/documentation/7.0/en/manual/api/reference/host/get)

[معنای شمارش problem.get زبیکس](https://www.zabbix.com/documentation/7.0/en/manual/api/reference/problem/get)

Stat آخرین موفقیت همراه از تابع رسمی Query یعنی scale(1000) استفاده می‌کند: مقدار unixtime زبیکس ثانیه Epoch است و واحد تاریخ گرافانا میلی‌ثانیه می‌خواهد. هنگام ویرایش پنل تبدیل را حفظ و تاریخ را با Log جمع‌آوری‌کننده تطبیق دهید.

## ۱۰. متغیر داشبورد و انتخاب پویای میزبان

Dashboard settings > Variables > Add variable را باز کنید. Query variable با منبع Zabbix و Editor ساختاریافته زیر بسازید. برای انتخاب‌گر موجودی معمولاً Refresh هنگام Load کافی است. متغیر وابسته بعد از والد قرار گیرد. افزونه جاری فرم Brace قدیمی را پشتیبانی و خودکار تبدیل می‌کند؛ داشبورد جدید از Field رسمی ساختاریافته استفاده می‌کند. Zabbix 7.0 مفهوم Applications را حذف کرده؛ برای گروه‌بندی آیتم از Item tag استفاده کنید.

| متغیر | Query / فیلتر رسمی |
| --- | --- |
| group | Query Type Group؛ Group برابر /Linux servers|Windows servers/ (در NOC: /^$environment\/.*/) |
| host | Query Type Host؛ Group برابر $group؛ Host برابر /.*/ |
| item_group | Query Type Item tag؛ Group برابر $group؛ Host برابر $host؛ Item Tag برابر /component:.*/ |
| interface | Query Type Item؛ Group برابر $group؛ Host برابر $host؛ Item برابر /Interface .*: Bits received/؛ خروجی نام کامل آیتم |
| environment | Custom برابر Production,Staging؛ قرارداد نام گروه محدوده Query NOC را تعیین می‌کند. Field خودکار شناسایی محیط نیست. |

```json
{"queryType":"group","group":"/Linux servers|Windows servers/"}
{"queryType":"host","group":"$group","host":"/.*/"}
{"queryType":"itemTag","group":"$group","host":"$host","itemTag":"/component:.*/"}
{"queryType":"item","group":"$group","host":"$host","item":"/Interface .*: Bits received/"}
```

در Metrics مقدار Group برابر $group، Host برابر $host و Item tag برابر $item_group باشد. در صورت نیاز Multi-value و Include All را فعال و All value را /.*/ تعیین کنید؛ افزونه جایگزینی متغیر را انجام می‌دهد. انتخاب‌گر Interface دانلودشده نام کامل آیتم دریافت را نگه می‌دارد تا استخراج شکننده GUID ویندوز لازم نباشد؛ در پنل اختصاصی ترافیک ورودی Item برابر $interface باشد. پنل همه Interfaceها از /Interface .*: Bits (received|sent)/ استفاده می‌کند. Rate را روی آیتمی که Template از قبل به بیت بر ثانیه تبدیل کرده دوباره اعمال نکنید.

CPU utilization و Memory utilization در Linux و Windows نام مشترک دارند و تغییر Group/Host پنل را پویا به‌روز می‌کند. Load مخصوص OS و Service در داشبورد جدا باقی می‌ماند؛ برای Uptime در داشبورد مشترک از /^(System uptime|Uptime)$/ استفاده کنید. تگ component:cpu عمداً معیار نامرتبط را حذف می‌کند؛ اگر پنل No data شد All را بازگردانید. متغیر پیمایش را بهتر می‌کند ولی داده را از کاربر مجاز منبع پنهان نمی‌کند.

[متغیر ساختاریافته رسمی و تبدیل فرم قدیمی](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/template-variables/)

## ۱۱. هشدار، رخداد و کنترل اعلان تکراری

Trigger زبیکس آیتم را ارزیابی، رخداد PROBLEM/Recovery را ایجاد و Action را با Media type ارسال می‌کند. پنل Problems گرافانا این رخداد را می‌خواند؛ پنل قرمز اعلان نمی‌فرستد. Grafana Alerting قانون مستقل سمت سرور را ارزیابی و با Contact point و Notification policy مسیریابی می‌کند. برای هر رخداد یک مالک Paging تعیین کنید: معمولاً زبیکس برای Trigger سیستم‌عامل/Template و گرافانا برای Rule جداگانه بین منابع.

- پنل Problems: Query نوع Problems، Show Problems، فیلتر Severity و ستون Acknowledgment؛ تأیید به معنای حل نیست و Status رخداد را بخوانید. Use time range برای رخداد جاری که پیش از بازه شروع شده خاموش باشد.
- Annotation رخداد: Dashboard settings > Annotations > Add annotation query > Zabbix؛ Group برابر $group، Host برابر $host، Min severity برابر Warning و Show OK events و Show hostname فعال. Application قدیمی در Zabbix 7.0 خالی باشد. جهش CPU را با Marker رخداد/بازیابی مرتبط کنید.
- Rule گرافانا: Alerting > Alert rules > New alert rule؛ میزبان ثابت مجاز Linux و Metrics عددی CPU utilization در Query A با بازه 10m؛ Reduce B برابر Mean(A) و Threshold C برابر B > 90؛ Pending برابر 5m و فاصله ارزیابی 1m. پیش از Save، Preview کنید.
- هشدار افزونه فقط Metrics و Item ID را پشتیبانی می‌کند. Problems، Triggers، Services، Text و User macros برای Rule هشدار پشتیبانی نمی‌شوند و Function پردازش سمت افزونه محدود است. Reduction را با Expression گرافانا انجام دهید و متغیر داشبورد در هشدار سمت سرور به کار نبرید.
- رفتار No-data/Error را آگاهانه تعیین کنید؛ شکست Query به مالک مانیتورینگ ارجاع شود و OK تلقی نشود. Label مربوط به owner، environment، service و source=grafana با URL راهنما و اثر رخداد اضافه کنید.

### ایمیل، تلگرام و سیاست اعلان

برای ایمیل [smtp] را با Relay مجاز، from_address، CA trust و سیاست STARTTLS لازم تنظیم کنید. رمز SMTP را با Environment/File provider مجاز نگه دارید و در JSON داشبورد قرار ندهید. در Alerting > Contact points (یا Notification configuration > Contact points در پیمایش جدید) Contact point نوع Email بسازید و Test را به گیرنده Staging مجاز ارسال کنید. در Notification policies برچسب مانند environment=Production را با Grouping و Repeat interval به تیم درست مسیریابی کنید.

```ini
[smtp]
enabled = true
host = smtp.example.com:587
user = grafana-notifications
password = $__file{/etc/grafana/secrets/smtp-password}
from_address = grafana@example.com
from_name = Enterprise Monitoring
startTLS_policy = MandatoryStartTLS
skip_verify = false
```

Grafana OSS برای Grafana Alertmanager از Contact point تلگرام پشتیبانی می‌کند. Bot مجاز با BotFather بسازید، به Chat مقصد اضافه، Chat ID را بگیرید و Bot token را فقط در Field امن Contact point وارد کنید. با Test داخلی بررسی و سپس Contact point را به Rule یا Policy متصل کنید. خروجی شبکه را در صورت نیاز محدود و Bot token را در URL دستور مقاله نگذارید. Media type تلگرام زبیکس مسیر اعلان جداست.

برای جلوگیری از Paging تکراری، Trigger موجود CPU زبیکس را با همان گیرنده در گرافانا بازتولید نکنید. مالک Rule را مستند، از Label منبع/سرویس برای Grouping استفاده و در صورت مالکیت Rule متفاوت، Silence نگهداری هر دو سیستم را هماهنگ کنید. یک PROBLEM، یک Recovery و شکست منبع داده را در Staging آزمون و فقط اعلان و Escalation مطلوب را تأیید کنید. هشدار شکست مانیتورینگ خارج از همان نمونه گرافانا باشد.

[محدودیت رسمی هشدار افزونه زبیکس](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/alerting/)

[Annotation رسمی رخداد](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/annotations/)

[Integration پشتیبانی‌شده Contact point گرافانا](https://grafana.com/docs/grafana/latest/alerting/fundamentals/notifications/contact-points/)

[راهنمای رسمی اتصال تلگرام](https://grafana.com/blog/how-to-integrate-grafana-alerting-and-telegram/)

## ۱۲. بهینه‌سازی عملکرد و Direct DB اختیاری

برای معیار تازه دقیق از History زبیکس و برای بازه بلند از Trend با تجمیع ساعتی استفاده کنید. گرافانا Trend ناموجود تولید نمی‌کند؛ آیتم عددی و Retention زبیکس باید آن را فراهم کند. Trends After با Retention واقعی History و Range با بازه قابل قبول برای تجمیع ساعتی هماهنگ باشد. داشبورد ۳۰ روزه نباید مکرراً میلیون‌ها Point یک‌دقیقه‌ای بگیرد.

- از Refresh یک دقیقه، بازه پیش‌فرض ۶ ساعت و حداقل Refresh برابر 30s شروع کنید. حداقل Interval پنل نزدیک فاصله جمع‌آوری Item باشد؛ بازه گسترده‌تر باید Interval را افزایش دهد.
- Cache TTL متادیتای Item/Host را Cache می‌کند و تضمین تازگی مقدار Live نیست. پس از ثبت میزبان یا تغییر مجوز زمان Expiry بدهید یا آگاهانه Refresh/Restart کنید.
- از All hosts × All items در هر پنل پرهیز کنید. Group محدود، نام دقیق یا Regex محدود و داشبورد جدا بر اساس تیم/محیط به کار برید. Transformation مربوط به Top 10 همچنان سری ورودی را می‌گیرد و بار API را در منبع کم نمی‌کند.
- با Query inspector، Timing مرورگر و Journal گرافانا تأخیر API، حجم داده، Transform و Rendering را تفکیک کنید. Query timeout کنترل حفاظتی است و درمان نیست. Worker API/PHP زبیکس و Query کند PostgreSQL را مستقل پایش کنید.
- گزینه Historical item-value lookup و Host IP در Problems را مگر در صورت نیاز خاموش نگه دارید. فهرست رخداد بزرگ را صفحه‌بندی، فیلتر محدود و Concurrency را با Wallboard واقعی NOC اندازه‌گیری کنید.

### دسترسی Read-only اختیاری PostgreSQL

Direct DB اختیاری است و فقط خواندن History/Trend را سریع می‌کند. API همچنان برای Metadata، مجوز و Problems لازم است. در دیتابیس PostgreSQL موجود زبیکس Role اختصاصی زیر بسازید؛ SELECT روی همه جدول‌ها ندهید و مالک دیتابیس را استفاده نکنید. رمز به‌صورت تعاملی در psql تعیین شود و در SQL قرار نگیرد. TLS و Rule محدود hostssl در pg_hba.conf با Firewall محدود به مبدا فعال کنید.

```sql
-- Execute in the Zabbix PostgreSQL database as an administrator.
-- Set a unique password interactively with psql: \password grafana_zabbix_ro
CREATE ROLE grafana_zabbix_ro LOGIN;
GRANT CONNECT ON DATABASE zabbix TO grafana_zabbix_ro;
GRANT USAGE ON SCHEMA public TO grafana_zabbix_ro;
GRANT SELECT ON public.history, public.history_uint, public.trends, public.trends_uint TO grafana_zabbix_ro;
ALTER ROLE grafana_zabbix_ro SET default_transaction_read_only = on;
ALTER ROLE grafana_zabbix_ro SET statement_timeout = '60s';
-- Narrow pg_hba.conf entry (trusted server cert required):
-- hostssl zabbix grafana_zabbix_ro 192.0.2.20/32 scram-sha-256
```

در گرافانا منبع PostgreSQL مربوط به دیتابیس زبیکس را با آن کاربر، TLS verify-full، CA معتبر و Hostname بسازید. Save & test کنید و در Additional settings > Direct DB Connection منبع Zabbix آن را انتخاب کنید. برای Provisioning از dbConnectionDatasourceUID استفاده کنید. این تنظیم در YAML پایه خاموش/غایب بماند. دسترسی را محدود کنید: SQL می‌تواند مجوز سطح Host در API را دور بزند و تمام History عددی جدول مجاز را آشکار کند. فقط برای اپراتور مورد اعتماد و ایزوله استفاده کنید؛ محدودیت نسخه ممکن است انتخاب API-only را مناسب کند.

[Direct DB رسمی و حداقل جدول History/Trend](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/configure/#configure-direct-db-connection)

[حداقل مجوز Object در PostgreSQL](https://www.postgresql.org/docs/16/sql-grant.html)

## ۱۳. بکاپ گرافانا و بازیابی عملی

![امنیت و بازیابی: ورودی HTTPS، حداقل مجوز، کلید رمزگذاری محافظت‌شده، بکاپ سازگار Metadata و Restore ایزوله](/assets/img/articles/content/grafana-security-backup-architecture.png)

امنیت و بازیابی: ورودی HTTPS، حداقل مجوز، کلید رمزگذاری محافظت‌شده، بکاپ سازگار Metadata و Restore ایزوله

Metadata گرافانا را مستقل از دیتابیس مانیتورینگ زبیکس بکاپ بگیرید. /etc/grafana/grafana.ini، Provisioning، Environment محدود به Root، Secret رمزگذاری، Override مربوط به systemd، فایل و Inventory نسخه افزونه، داشبورد File-provisioned، تنظیم Nginx و Certificate/Private key TLS را محافظت کنید. Export JSON داشبورد Artifact قابل انتقال است ولی Secret قابل استفاده منبع داده یا همه تنظیم سازمان را ندارد. JSON را از منوی Sharing/Export داشبورد بگیرید یا JSON بررسی‌شده را از Version control فراهم کنید.

### سازگاری SQLite و PostgreSQL

Copy ساده grafana.db زنده می‌تواند وضعیت Journal/WAL را از دست بدهد. Script همراه گرافانا را کوتاه متوقف، Snapshot با SQLite .backup ایجاد، PRAGMA integrity_check را بررسی، Config/Plugin را Archive و حتی در شکست دوباره سرویس را Start می‌کند. پنجره نگهداری را زمان‌بندی و فقط Check دسترس‌پذیری برنامه‌ریزی‌شده را Silence کنید. Script مسیر sqlite3 نمونه و سرویس محلی سالم را فرض می‌کند؛ برای مسیر متفاوت یا معماری چند نمونه آگاهانه تنظیم کنید.

برای دیتابیس مستقل PostgreSQL مربوط به Metadata گرافانا مقدار GRAFANA_DB_TYPE=postgres و PG* همان دیتابیس را تنظیم کنید، نه زبیکس. pg_dump با Custom format Snapshot منطقی Transaction-consistent می‌دهد؛ Decode در pg_restore خوانایی Archive را بررسی می‌کند و بازیابی برنامه نیست. Role محدود Backup با مجوز خواندن Schema گرافانا و PGPASSFILE محافظت‌شده فراهم و محتوا را چاپ نکنید. Script همچنان گرافانا را برای تطبیق Config/Plugin با Metadata متوقف می‌کند. Role و TLS مدیریت‌شده توسط DBA برنامه بازیابی مستقل می‌خواهد.

```bash
#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
[[ $EUID -eq 0 ]] || { echo 'Run as root' >&2; exit 1; }
: "${BACKUP_ROOT:=/srv/grafana-backups}"
: "${GRAFANA_DB_TYPE:=sqlite3}"
[[ $BACKUP_ROOT == /srv/grafana-backups ]] || { echo 'Review script before changing backup root' >&2; exit 1; }
mountpoint -q "$BACKUP_ROOT" || { echo 'Backup volume is not mounted' >&2; exit 1; }
install -d -m 0700 /run/grafana-backup
exec 9>/run/grafana-backup/lock
flock -n 9 || { echo 'Backup already running' >&2; exit 1; }
stamp=$(date -u +%Y%m%dT%H%M%SZ)
work=$(mktemp -d "$BACKUP_ROOT/.partial.XXXXXX")
stopped=0
cleanup() {
    rc=$?
    trap - EXIT
    if ((stopped)); then
        if ! systemctl start grafana-server; then echo 'CRITICAL: Grafana restart failed' >&2; rc=1; fi
    fi
    if ((rc)); then echo "FAILED; incomplete backup retained at $work" >&2; fi
    exit "$rc"
}
trap cleanup EXIT
systemctl is-active --quiet grafana-server || { echo 'Grafana must be healthy before backup' >&2; exit 1; }
needed=$(du -sk /etc/grafana /var/lib/grafana | awk '{sum+=$1} END {print sum*2+1048576}')
available=$(df -Pk "$BACKUP_ROOT" | awk 'NR==2 {print $4}')
((available > needed)) || { echo 'Insufficient backup capacity' >&2; exit 1; }
# Short maintenance window keeps database, provisioning and plugin files aligned.
stopped=1
systemctl stop grafana-server
if [[ $GRAFANA_DB_TYPE == sqlite3 ]]; then
    sqlite3 /var/lib/grafana/grafana.db ".backup '$work/grafana.db'"
    [[ $(sqlite3 "$work/grafana.db" 'PRAGMA integrity_check;') == ok ]]
elif [[ $GRAFANA_DB_TYPE == postgres ]]; then
    pg_dump --format=custom --no-owner --no-acl --file="$work/grafana.pgdump"
    pg_restore --file=/dev/null "$work/grafana.pgdump"
else
    echo 'Supported database types: sqlite3 or postgres' >&2; exit 1
fi
dpkg-query -W -f='${Package} ${Version}\n' grafana > "$work/grafana-version.txt"
find /var/lib/grafana/plugins -name plugin.json -exec python3 -c \
    'import json,sys; p=json.load(open(sys.argv[1])); print(p["id"],p.get("info",{}).get("version","unknown"))' {} \; > "$work/plugin-inventory.txt"
paths=(etc/grafana var/lib/grafana/plugins)
for path in etc/default/grafana-server etc/systemd/system/grafana-server.service.d var/lib/grafana/dashboards etc/nginx etc/letsencrypt; do
    [[ ! -e /$path ]] || paths+=("$path")
done
# This archive contains credentials, secret keys and TLS private keys: root-only.
tar -C / -czf "$work/config-and-plugins.tar.gz" "${paths[@]}"
tar -tzf "$work/config-and-plugins.tar.gz" > /dev/null
systemctl start grafana-server
stopped=0
systemctl is-active --quiet grafana-server
(cd "$work"; sha256sum ./* > SHA256SUMS; sha256sum -c SHA256SUMS)
final="$BACKUP_ROOT/$stamp"
[[ ! -e $final ]] || { echo 'Backup timestamp collision' >&2; exit 1; }
mv -- "$work" "$final"
work=$final
if [[ -n ${RESTIC_REPOSITORY:-} ]]; then
    : "${RESTIC_PASSWORD_FILE:?Configure protected restic password file}"
    restic backup --tag grafana "$final"
fi
echo "Backup complete: $final"
# Retention intentionally separate: expire only after confirmed off-site recovery tests.
```

```bash
sudo install -m 0750 -o root -g root grafana-backup.sh /usr/local/sbin/grafana-backup.sh
sudo install -m 0600 -o root -g root grafana-backup.env.example /etc/grafana/backup.env
sudoedit /etc/grafana/backup.env
# Mount an approved backup volume at /srv/grafana-backups first.
findmnt /srv/grafana-backups
sudo install -m 0644 grafana-backup.service grafana-backup.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl start grafana-backup.service
sudo journalctl -u grafana-backup.service -n 100 --no-pager
sudo systemctl enable --now grafana-backup.timer
sudo systemctl list-timers grafana-backup.timer
```

```ini
[Unit]
Description=Consistent Grafana metadata and configuration backup
After=network-online.target grafana-server.service
Wants=network-online.target
[Service]
Type=oneshot
User=root
EnvironmentFile=/etc/grafana/backup.env
ExecStart=/usr/local/sbin/grafana-backup.sh
UMask=0077
TimeoutStartSec=20min
PrivateTmp=true
ProtectHome=true
```

```ini
[Unit]
Description=Daily Grafana backup
[Timer]
OnCalendar=*-*-* 02:15:00 UTC
RandomizedDelaySec=15min
Persistent=true
Unit=grafana-backup.service
[Install]
WantedBy=timers.target
```

Timer ساعت 02:15 UTC با تأخیر تصادفی حداکثر ۱۵ دقیقه مستقل از Timezone میزبان اجرا و Run ازدست‌رفته را پس از Boot جبران می‌کند. Script Volume نصب‌نشده را رد، Lock می‌گیرد، ظرفیت را بررسی، Permission محدود تعیین، پوشه Partial مخفی می‌سازد، Checksum را بررسی و اتمیک منتشر می‌کند. شکست Exit غیرصفر دارد و شواهد حفظ می‌شود؛ Unit شکست‌خورده و بکاپ قدیمی را با زبیکس یا Scheduler خارجی هشدار دهید. Retention مستقل از ساخت است؛ فقط پس از Copy رمز‌شده Off-site معتبر و تمرین Restore دوره‌ای حذف کنید.

Archive بکاپ Token API، تنظیم Session/Encryption و Private key TLS دارد. آن را در Storage رمز‌شده با دسترسی فقط Root نگه دارید. Repository مربوط به restic را جدا Initialize، رمز را در فایل محدود ذخیره و در صورت نیاز RESTIC_REPOSITORY/RESTIC_PASSWORD_FILE را برای Upload رمز‌شده Off-site فعال کنید. کلید بازیابی در Vault مستقل قابل دسترس باشد؛ دریافت از سایت بازیابی را آزمون کنید. Process موفق یا Checksum اثبات رمزگشایی Credential پس از Restore نیست.

### بازیابی روی میزبان ایزوله

- Ubuntu 24.04 با نسخه دقیق ثبت‌شده گرافانا و افزونه امضاشده آماده کنید. خروجی اعلان و Collector/Backup timer خودکار را مسدود کنید. تنظیم Host/Domain اصلی تا پذیرش فقط در شبکه ایزوله بازیابی شود.
- بکاپ تاریخ‌دار کامل را دریافت و پیش از Extract، SHA256SUMS را بررسی کنید. گرافانا را Stop؛ تنظیم جاری میزبان بازیابی را جدا Archive کنید. فهرست Tar را بررسی و فقط Backup مورد اعتماد را روی میزبان هدف Extract کنید.
- /etc/grafana شامل secret_key اصلی و Environment، Plugin، File dashboard، Override systemd و Nginx/Certificate را بازیابی کنید. اگر KMS/Encryption provider خارجی تنظیم بود دسترسی کلید آن هم لازم است. از دست دادن ماده رمزگذاری اصلی به ثبت مجدد Secret منبع داده نیاز دارد.
- برای SQLite، grafana.db سازگار را Restore و فقط در حالت Stop و پس از حفظ وضعیت هدف، WAL/SHM قدیمی هدف را حذف کنید. برای PostgreSQL در دیتابیس خالی Grafana با pg_restore سازگار و Role مناسب Restore کنید. دیتابیس زبیکس را بازنویسی نکنید.
- Ownership فایل را بازگردانید، Config را بررسی، systemd را Reload و گرافانا را Start کنید. ورود، مجوز سازمان/پوشه، Inventory افزونه، Save & test، Query واقعی Linux/Windows، شمارنده/تازگی NOC و Variable را آزمون کنید. Certificate و تمدید را جدا بررسی کنید.
- RPO را از Timestamp بکاپ و RTO را تا مانیتورینگ پذیرفته‌شده اندازه‌گیری و با هدف سازمان تطبیق دهید. پس از بازکردن فقط Transport مربوط به Staging یک اعلان مجاز را آزمون، شواهد ثبت و سپس DNS و Policy عملیاتی را با Change control منتقل کنید.

```bash
# On the isolated recovery host; BACKUP_DIR is a trusted verified dated directory.
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
curl --fail --silent --show-error --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health
```

برای PostgreSQL روش جایگزین زیر را روی دیتابیس خالی Recovery پس از ایجاد Role اجرا کنید. تنظیم اتصال محافظت‌شده و نسخه هماهنگ مبدا/هدف را جایگزین کنید. pg_restore در بارگذاری موفق Exit صفر می‌دهد؛ پذیرش همچنان بررسی UI/API و رمزگشایی بالاست. هر دو Branch بازیابی SQLite و PostgreSQL را اجرا نکنید.

```bash
sudo -u postgres createdb -O grafana grafana_recovery
sudo install -o postgres -g postgres -m 0600 /srv/grafana-backups/20261009T021500Z/grafana.pgdump /var/lib/postgresql/grafana-recovery.pgdump
sudo -u postgres pg_restore --exit-on-error --no-owner --no-acl --role=grafana --dbname=grafana_recovery /var/lib/postgresql/grafana-recovery.pgdump
sudo rm -f -- /var/lib/postgresql/grafana-recovery.pgdump
# Point recovered Grafana [database] at this recovery DB before starting.
```

[محدوده بکاپ رسمی گرافانا و توقف SQLite](https://grafana.com/docs/grafana/latest/administration/back-up-grafana/)

[API بکاپ SQLite و سازگاری](https://www.sqlite.org/backup.html)

[سازگاری pg_dump در PostgreSQL](https://www.postgresql.org/docs/16/app-pgdump.html)

[مستند بکاپ Off-site رمز‌شده restic](https://restic.readthedocs.io/en/stable/)

## ۱۴. عیب‌یابی عملیاتی

برای هر شکست ترتیب علائم ← علت ریشه‌ای ← دستور تشخیصی ← خروجی مورد انتظار ← راه‌حل را دنبال کنید. دستورها تشخیصی هستند و Token واقعی ندارند. Health در apiinfo.version یا /api/health فقط همان مرحله را اثبات می‌کند؛ پیش از اعلام سلامت اتصال، Query آیتم واقعی مجاز را بررسی کنید. Log را محلی بخوانید و قبل از اشتراک Secret را حذف کنید.

### ۱. سرویس گرافانا Start نمی‌شود

علائم: سرویس Failed است یا مکرراً Restart می‌شود.

علت ریشه‌ای: INI نامعتبر، فایل Secret ناخوانا، مجوز دیتابیس Metadata یا تداخل پورت.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo systemctl status grafana-server --no-pager
sudo journalctl -u grafana-server -n 100 --no-pager
sudo ss -lntp | grep ":3000"
sudo -u grafana test -r /etc/grafana/secrets/secret_key
```

خروجی مورد انتظار: active (running)، بدون خطای Startup، یک Listener در Loopback و موفقیت بررسی Readability.

راه‌حل: تنظیم/مسیر/Ownership مشخص‌شده در Log را اصلاح، Secret موجود را حفظ، Restart و /api/health را دوباره بررسی کنید.

### ۲. رابط وب گرافانا در دسترس نیست

علائم: Timeout مرورگر، خطای 502 یا Redirect به دامنه اشتباه.

علت ریشه‌ای: عدم تطبیق DNS/Firewall، Listener ناموجود Nginx، root_url اشتباه یا Down بودن Upstream.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
getent ahosts grafana.example.com
sudo ufw status verbose
sudo nginx -t
curl --fail --header "Host: grafana.example.com" http://127.0.0.1:3000/api/health
curl --head https://grafana.example.com/login
```

خروجی مورد انتظار: DNS میزبان مطلوب را حل کند؛ nginx -t موفق؛ Health محلی 200 و ورود HTTPS برابر 200 یا Redirect ورود مورد انتظار.

راه‌حل: Routing/DNS/Allowlist را اصلاح، Backend را Start و domain/root_url را هماهنگ کنید. پورت 3000 فقط Loopback بماند.

### ۳. افزونه زبیکس نمایش داده نمی‌شود

علائم: پس از نصب گزینه اتصال Zabbix دیده نمی‌شود.

علت ریشه‌ای: پوشه افزونه اشتباه، Restart انجام‌نشده، App غیرفعال یا Reject امضا.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls
sudo journalctl -u grafana-server -n 100 --no-pager
sudo test -f /var/lib/grafana/plugins/alexanderzobnin-zabbix-app/plugin.json
```

خروجی مورد انتظار: App نصب‌شده در فهرست، Manifest موجود و Journal بدون Reject امضا/Dependency.

راه‌حل: در پوشه تنظیم‌شده نصب، Restart و سپس Administration > Plugins and data > Plugins > Zabbix > Enable را انجام دهید.

### ۴. احراز هویت API زبیکس شکست می‌خورد

علائم: خطای Not authorized، Token نامعتبر یا Session منقضی در Save & test.

علت ریشه‌ای: Token منقضی، صاحب حساب غیرفعال، منع API در Role، Environment مفقود یا حذف Authorization در Proxy.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py
sudo journalctl -u grafana-server -n 50 --no-pager
```

خروجی مورد انتظار: نسخه API برابر 7.0.x و تعداد Visible hosts مجاز غیرصفر.

راه‌حل: Token مدت‌دار صاحب حساب فعال درست را دوباره Generate، Role/Permission و Header را بررسی و فقط Environment محدود را Update و Provisioning را Restart کنید.

### ۵. منبع داده زبیکس Error می‌دهد

علائم: Save & test خطای JSON parse، Method denied، 404 یا 502 می‌دهد.

علت ریشه‌ای: مسیر Frontend اشتباه، Login HTML در میانه، API غیرقابل دسترس یا Method ممنوع.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
curl --fail --silent --show-error -H "Content-Type: application/json-rpc" --data '{"jsonrpc":"2.0","method":"apiinfo.version","params":{},"id":1}' https://zabbix.example.com/api_jsonrpc.php
sudo journalctl -u grafana-server -n 50 --no-pager
```

خروجی مورد انتظار: JSON دارای نسخه، نه HTML یا Redirect؛ Query inspector پاسخ معتبر نشان دهد.

راه‌حل: مسیر Root یا /zabbix را بررسی، Login نامناسب API را رفع و Method خواندن لازم را مجاز کنید. آیتم مجاز را دوباره Query کنید.

### ۶. داشبورد No data نشان می‌دهد

علائم: نمودار خالی با وجود تست موفق API.

علت ریشه‌ای: نام آیتم اشتباه، Discovery ساخته‌نشده، آیتم Disabled/Unsupported، داده قدیمی، فیلتر Tag یا بازه خارج History.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
date -u
timedatectl status
sudo journalctl -u grafana-server -n 50 --no-pager
```

خروجی مورد انتظار: ساعت هماهنگ؛ Latest data نمونه تازه Supported؛ فیلتر Query inspector با آیتم کشف‌شده دقیق مطابق باشد.

راه‌حل: انتخاب‌گر item_group/interface را Reset، نام واقعی آیتم را استفاده، برای Discovery/Collection صبر و بازه تازه انتخاب کنید؛ Trend فقط در صورت موجودبودن فعال شود.

### ۷. گروه میزبان دیده نمی‌شود

علائم: Picker گروه موجود برای مدیر زبیکس را نشان نمی‌دهد.

علت ریشه‌ای: صاحب حساب اتصال فاقد Read گروه، محدودیت Role یا Metadata Cacheشده.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo ZABBIX_API_URL=https://zabbix.example.com/api_jsonrpc.php ZABBIX_TOKEN_FILE=/etc/grafana/secrets/zabbix-token python3 zabbix-api-probe.py
```

خروجی مورد انتظار: محدوده میزبان با کاربر اتصال تطبیق دارد، نه موجودی Super admin زبیکس.

راه‌حل: فقط گروه مجاز را در Host permissions Read بدهید، hostgroup.get/host.get را مجاز، Variable را Refresh و اجازه Expiry Cache TTL دهید.

### ۸. اتصال API زبیکس Timeout می‌شود

علائم: Query از 30s عبور می‌کند یا Gateway timeout متناوب دارد.

علت ریشه‌ای: Routing/Firewall مسدود، بار زیاد PHP/API/Database یا Query نامحدود داشبورد.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
getent ahosts zabbix.example.com
curl --head --connect-timeout 5 --max-time 30 https://zabbix.example.com/
sudo journalctl -u grafana-server -n 50 --no-pager
```

خروجی مورد انتظار: DNS حل و HTTPS سریع پاسخ دهد؛ مدت Query API کمتر از Timeout تنظیم‌شده باشد.

راه‌حل: ابتدا شبکه را اصلاح، محدوده Host/Item و بازه را محدود، بار Worker/Database زبیکس را بررسی و سپس Timeout را بر اساس اندازه‌گیری تنظیم کنید.

### ۹. اعتبارسنجی Certificate HTTPS شکست می‌خورد

علائم: خطای x509 unknown authority، Hostname mismatch یا Certificate منقضی.

علت ریشه‌ای: Chain ناقص، CA داخلی نامعتبر، SAN/Domain اشتباه یا شکست تمدید.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com -verify_return_error </dev/null
timedatectl status
sudo certbot certificates
```

خروجی مورد انتظار: Verify return code: 0 (ok)، SAN منطبق، تاریخ معتبر و ساعت درست.

راه‌حل: Full chain را ارائه، CA trust مجاز را روی گرافانا نصب و Certificate معتبر را تمدید کنید. Skip TLS verify راه‌حل Production نیست.

### ۱۰. داشبورد گرافانا کند است

علائم: Spinner طولانی، حافظه زیاد مرورگر یا اشباع API.

علت ریشه‌ای: Cardinality بالا، Query گسترده History، Refresh سریع، Transform/Problem lookup پرهزینه.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo journalctl -u grafana-server -n 100 --no-pager
sudo systemctl status grafana-server --no-pager
```

خروجی مورد انتظار: Query inspector تأخیر و حجم پاسخ را تفکیک؛ Log بدون Timeout تکراری.

راه‌حل: سری/پنل را کم، Trend برای بازه بلند و Refresh حداقل 1m، Enrichment بی‌استفاده رخداد را خاموش و پیش از Direct DB محدود Benchmark کنید.

### ۱۱. ناسازگاری نسخه افزونه

علائم: افزونه با نیاز نسخه، کد Angular قدیمی یا Definition مفقود مسدود است.

علت ریشه‌ای: گرافانا پایین‌تر از حداقل یا Artifact قدیمی/مختلط پس از ارتقا.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
grafana --version
sudo grafana cli --pluginsDir /var/lib/grafana/plugins plugins ls
sudo journalctl -u grafana-server -n 100 --no-pager
```

خروجی مورد انتظار: گرافانا پایدار مجاز و افزونه React امضاشده؛ هنگام بررسی 13.2.3 / 6.9.1 و حداقل 11.6.0.

راه‌حل: زوج نسخه سازگار را در Staging آماده؛ Artifact قدیمی را حفظ، فقط پوشه افزونه قدیمی را با نصب تمیز امضاشده مجاز جایگزین، Restart و داشبورد/هشدار را دوباره آزمون کنید.

### ۱۲. پنل Problems رخداد نشان نمی‌دهد

علائم: پنل خالی است با وجود رخداد فعال در زبیکس.

علت ریشه‌ای: Query type اشتباه، فیلتر Severity/Acknowledgment/Tag/Host، مخفی‌شدن رخداد قدیمی با Use time range یا مجوز خواندن Event مفقود.

دستور تشخیصی: محلی اجرا کنید؛ فیلتر Query مرتبط در UI را هم بررسی کنید.

```bash
sudo journalctl -u grafana-server -n 100 --no-pager
```

خروجی مورد انتظار: Query inspector نوع Problems (5)، Show Problems جاری، Use time range برابر false و رخداد فعال مجاز برگرداند.

راه‌حل: نمایش Zabbix Problems را انتخاب، فیلتر Severity/Ack/Tag را Reset، خواندن problem.get/event.get/trigger.get را مجاز و با صاحب اتصال مقایسه کنید. صفر رخداد جاری می‌تواند معتبر باشد.

[عیب‌یابی رسمی افزونه زبیکس](https://grafana.com/docs/plugins/alexanderzobnin-zabbix-app/latest/troubleshooting/)

## ۱۵. چک‌لیست پذیرش Production

- [ ] نصب: نسخه پایدار Grafana/Plugin ثبت، کلید APT بررسی، systemd فعال و Health و Reboot آزموده شد.
- [ ] امنیت: Chain/SAN معتبر HTTPS، تمدید آزموده، 3000 فقط Loopback، Firewall مبدا و IPv6 مجاز، بدون دسترسی ناشناس/رمز پیش‌فرض.
- [ ] هویت: مدیر نام‌دار، حداقل مجوز Role، MFA و حذف حساب IdP آزموده، دسترسی اضطراری مستند؛ دیدپذیری منبع در OSS مشخص.
- [ ] زبیکس: مسیر API و TLS معتبر، صاحب حساب محدود/انقضای Token، مجوز Read و Save & test واقعی با Query آیتم عددی.
- [ ] لینوکس: CPU/Load/RAM/حافظه قابل استفاده/FS/شبکه/Uptime/Availability با Latest data مقایسه و واحد بررسی شد.
- [ ] ویندوز: سرویس/FS/Interface واقعی کشف‌شده، State mapping معتبر، آیتم Active مربوط به Event Log و آزمون رخداد جدید کنترل‌شده.
- [ ] NOC: محدوده Collector و مجوز بررسی، کل برابر در دسترس+خارج از دسترس+نامشخص، تعداد Disaster/High تطبیق و هشدار تازگی/شکست Collector آزموده.
- [ ] پایداری: Variable و پنل مشترک OS آزموده، Problems رخداد جاری قدیمی را شامل، رفتار No-data/Error و مانیتورینگ مستقل خود گرافانا.
- [ ] اعلان: یک مالک رخداد، Test Contact point مجاز، مشاهده PROBLEM/Recovery، بدون Paging تکراری، Escalation و Policy نگهداری مستند.
- [ ] کارایی: داشبورد هم‌زمان واقعی اندازه‌گیری، فیلتر محدود، Refresh/Query interval و Retention History/Trend هماهنگ.
- [ ] بکاپ: Snapshot سازگار Metadata، Inventory کامل Config/Secret/Plugin/Certificate، Checksum و Copy رمز‌شده Off-site با هشدار شکست خارجی.
- [ ] بازیابی: Restore ایزوله رمزگشایی، ورود، Role، Query واقعی، RPO/RTO اندازه‌گیری‌شده و دریافت کلید از Vault جدا را تأیید کند.
- [ ] عملیات: مالک/On-call، پنجره Patch، هدف ظرفیت، برنامه Restore، Rollback مجاز و شواهد تحویل ثبت شد.

پذیرش به اتصال واقعی API زبیکس و داده زنده نماینده نیاز دارد. تعریف داشبورد دانلودشده مقاله نتیجه متریک زنده ندارد و ادعای اتصال موفق Production نمی‌کند. اپراتور باید چک‌لیست را در Staging و سپس محیط Production مجاز خود کامل کند.

## نتیجه‌گیری و تحویل عملیاتی

سامانه مانیتورینگ سازمانی مفید، جمع‌آوری و ارزیابی Trigger قابل اتکای زبیکس را با نمای روشن گرافانا، دسترسی کنترل‌شده و Config قابل بازیابی ترکیب می‌کند. Inventory نسخه واقعی، مالک API محدود، Mapping داشبورد/آیتم، مالک هشدار و شواهد Restore اندازه‌گیری‌شده را تحویل دهید. API-only پیش‌فرض بماند و جزء اختیاری فقط با شناخت هزینه و مرز مجوز اضافه شود.

## پرسش‌های متداول

### آیا گرافانا جایگزین Agent یا Server زبیکس است؟

خیر؛ زبیکس داده را جمع‌آوری، ذخیره و ارزیابی می‌کند و گرافانا API را Query و داده را نمایش می‌دهد.

### آیا Direct DB الزامی است؟

خیر؛ افزونه رسمی با API متصل می‌شود. Direct DB برای عملکرد History/Trend عددی اختیاری و نیازمند Role محدود مستقل است.

### کدام نسخه پایدار بررسی شد؟

Grafana OSS 13.2.3 و افزونه Zabbix 6.9.1 در ۹ اکتبر ۲۰۲۶؛ افزونه حداقل 11.6.0 نیاز دارد و احراز هویت Zabbix 7.0 را مستند می‌کند.

### URL صحیح API زبیکس چیست؟

Mount رابط وب را بررسی کنید؛ /api_jsonrpc.php در Root یا /zabbix/api_jsonrpc.php وقتی همان مسیر نصب است.

### آیا Variable نوع Application در Zabbix 7.0 کاربرد دارد؟

از Item tag استفاده کنید؛ زبیکس از 5.4 Applications را حذف کرده و افزونه جاری Query ساختاریافته Item tag دارد.

### آیا JSON مربوط به NOC تعداد میزبان را فرض می‌کند؟

خیر؛ کد Collector API و Template Trapper همراه، تعداد Host/Problem محدود و تازگی را پیاده می‌کنند؛ پیش از Overview نصب شوند.

### آیا Query نوع Problems هشدار گرافانا می‌سازد؟

خیر؛ هشدار افزونه Metrics و Item ID عددی را پشتیبانی می‌کند؛ اعلان Problem زبیکس با Trigger/Action زبیکس انجام شود.

### چرا Secret رمزگذاری باید بکاپ شود؟

Credential ذخیره‌شده برای بازیابی به ماده رمزگذاری اصلی نیاز دارد؛ JSON داشبورد به‌تنهایی Credential را بازنمی‌گرداند.

## منابع رسمی، دانلود و مقاله‌های مرتبط

منبع رسمی در فصل مرتبط لینک شده است. دانلودها Template تنظیم تألیف‌شده و ساختار واقعی داشبورد هستند و به‌عنوان Export نمونه در حال اجرا معرفی نمی‌شوند. دامنه/IP مستند، ID گروه، Placeholder مربوط به Secret و مسیر Certificate را محلی جایگزین کنید. پیش از Import نام آیتم کشف‌شده واقعی را مقایسه کنید. همه دانلودها در ZIP تنظیم نیز قرار دارند.

[دانلود: Bootstrap HTTP برای Certificate](/downloads/grafana-installation-zabbix-integration/nginx-bootstrap.conf)

[دانلود: Reverse Proxy HTTPS در Nginx](/downloads/grafana-installation-zabbix-integration/nginx-grafana.conf)

[دانلود: تنظیم Production گرافانا](/downloads/grafana-installation-zabbix-integration/grafana.ini)

[دانلود: Provisioning منبع داده با Token API](/downloads/grafana-installation-zabbix-integration/zabbix-datasource.yaml)

[دانلود: Provisioning افزونه App](/downloads/grafana-installation-zabbix-integration/zabbix-app.yaml)

[دانلود: Template محیط محافظت‌شده](/downloads/grafana-installation-zabbix-integration/grafana.env.example)

[دانلود: Override محیط systemd](/downloads/grafana-installation-zabbix-integration/grafana-environment.conf)

[دانلود: Provisioning فایل داشبورد](/downloads/grafana-installation-zabbix-integration/dashboard-provider.yaml)

[دانلود: JSON داشبورد Linux](/downloads/grafana-installation-zabbix-integration/linux-dashboard.json)

[دانلود: JSON داشبورد Windows](/downloads/grafana-installation-zabbix-integration/windows-dashboard.json)

[دانلود: JSON داشبورد NOC سازمانی](/downloads/grafana-installation-zabbix-integration/enterprise-noc-dashboard.json)

[دانلود: Probe امن اتصال API](/downloads/grafana-installation-zabbix-integration/zabbix-api-probe.py)

[دانلود: Collector شمارنده API](/downloads/grafana-installation-zabbix-integration/noc-collector.py)

[دانلود: Template محیط NOC](/downloads/grafana-installation-zabbix-integration/noc-collector.env.example)

[دانلود: Service systemd جمع‌آوری‌کننده NOC](/downloads/grafana-installation-zabbix-integration/noc-collector.service)

[دانلود: Timer جمع‌آوری‌کننده NOC](/downloads/grafana-installation-zabbix-integration/noc-collector.timer)

[دانلود: Template Trapper زبیکس برای NOC](/downloads/grafana-installation-zabbix-integration/zabbix-noc-template.json)

[دانلود: مجوز Read-only اختیاری PostgreSQL](/downloads/grafana-installation-zabbix-integration/postgres-readonly.sql)

[دانلود: Script بکاپ سازگار](/downloads/grafana-installation-zabbix-integration/grafana-backup.sh)

[دانلود: Template محیط بکاپ](/downloads/grafana-installation-zabbix-integration/grafana-backup.env.example)

[دانلود: Service systemd بکاپ](/downloads/grafana-installation-zabbix-integration/grafana-backup.service)

[دانلود: Timer روزانه بکاپ](/downloads/grafana-installation-zabbix-integration/grafana-backup.timer)

[دانلود: دستور بازیابی دوزبانه](/downloads/grafana-installation-zabbix-integration/restore-runbook.md)

[دانلود: چک‌لیست امنیت دوزبانه](/downloads/grafana-installation-zabbix-integration/security-checklist.md)

[دانلود: README بسته و ترتیب استقرار](/downloads/grafana-installation-zabbix-integration/README.md)

[دانلود بسته کامل تنظیمات](/downloads/grafana-installation-zabbix-integration/grafana-configuration-package.zip)

[مرتبط: نصب Zabbix Server، Agent لینوکس/ویندوز و بازیابی بحران](https://meetaj.ir/articles/zabbix-server-linux-windows-agents-backup)

[مرتبط: استقرار سازمانی Oracle Database](https://meetaj.ir/articles/oracle-database-26ai-installation-oracle-linux)

[مرتبط: systemd و امنیت Apache Tomcat](https://meetaj.ir/articles/apache-tomcat-linux-installation-security-hardening)

[مرتبط: مانیتورینگ Production در Redis](https://meetaj.ir/articles/redis-installation-configuration-replication)

[مرتبط: عملیات سازمانی MongoDB](https://meetaj.ir/articles/mongodb-installation-configuration-production-deployment)

[مرتبط: ممیزی امنیت Linux](https://meetaj.ir/articles/linux-security-auditor-bash)

[مرتبط: Nginx روی Ubuntu](https://meetaj.ir/articles/nginx-installation-configuration-ubuntu)
