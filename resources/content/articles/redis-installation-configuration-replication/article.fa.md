# راه‌اندازی Redis در محیط Production؛ نصب، Hardening، Persistence و Replication

آموزش نصب Redis روی Ubuntu، تنظیم ACL و TLS، مدیریت RDB و AOF، راه‌اندازی Replica، Sentinel، مانیتورینگ و Backup با چک‌لیست Production.

## مقدمه؛ نسخه‌های بررسی‌شده و محدوده استقرار

آخرین نسخه Stable تأییدشده در Release رسمی، Redis Open Source 8.10.2 است که در ۱۷ سپتامبر ۲۰۲۶ منتشر شده است. مبنای سینتکس، Command Reference نسخه 8.10 و redis.conf مربوط به Tag نسخه 8.10.2 است. پیش از استقرار، آخرین Patch امنیتی و Release Notes را دوباره بررسی کنید؛ شماره نسخه Redis Software و Redis Cloud را با Redis Open Source اشتباه نگیرید.

[Release رسمی Redis 8.10.2](https://github.com/redis/redis/releases/tag/8.10.2)

[چرخه پشتیبانی نسخه‌های Redis Open Source](https://redis.io/docs/latest/operate/oss_and_stack/install/version-mgmt/)

Hostهای مثال Ubuntu Server 24.04 LTS دارند که همچنان پشتیبانی می‌شود. جدیدترین LTS در تاریخ بررسی Ubuntu 26.04 است. روی LTS جدیدتر، پشتیبانی مخزن Redis، Codename، نسخه Candidate، Unit سرویس و مسیرها را پیش از اعمال این راهنما تطبیق دهید. بسته مخزن Ubuntu الزاماً آخرین Stable بالادستی نیست. فرمان‌های Linux برای سرورهای Redis شما هستند؛ با مستندات بررسی شده‌اند و اجرای زنده آن‌ها روی Production در Workspace ویندوزی این سایت ادعا نمی‌شود.

[چرخه انتشار و پشتیبانی رسمی Ubuntu](https://ubuntu.com/about/release-cycle)

## ۱. Redis چیست؟

Redis یک In-Memory Data Structure Server است که به‌عنوان Key-Value Database و Cache استفاده می‌شود. علاوه بر String، ساختارهای Hash، List، Set، Sorted Set و Stream دارد؛ بنابراین از یک Cache ساده رشته‌ای فراتر می‌رود. Persistence می‌تواند داده را پس از Restart نگه دارد، اما دوام واقعی به Policy نوشتن و همگام‌سازی وابسته است.

- Cache: ذخیره نتیجه قابل بازسازی با TTL برای کاهش بار Database و API.
- Session Store: اشتراک وضعیت ورود و Session میان Application Serverها.
- Message Broker و Pub/Sub: ارسال سریع پیام زنده به چند مصرف‌کننده؛ Pub/Sub تحویل At-Most-Once دارد و پیام Subscriber قطع‌شده را بازپخش نمی‌کند.
- Queue: استفاده از List یا Stream با Consumer Group؛ Acknowledgment، Retry، Dead Letter و Idempotency را صریح طراحی کنید.
- Rate Limiting: Counter با پنجره زمانی محدود یا Script برای تغییر اتمیک Counter و Expiration.
- Distributed Lock: کلید Expiring با Token مالکیت یکتا، آزادسازی با تأیید مالکیت و در صورت نیاز Fencing؛ Lock ساده در Failover ناهمزمان تضمین ایمنی ندارد.

دسترسی RAM بسیاری از خواندن‌های دیسک را حذف می‌کند، ساختارهای فشرده سربار را کم می‌کنند و Event Loop کارآمد فرمان‌های رایج را پردازش می‌کند. بسیاری از عملیات ساده O(1) هستند. این به معنی سریع‌بودن هر فرمان یا نبود I/O Thread نیست: Collection بزرگ، Script سنگین، Persistence، رفت‌وبرگشت شبکه و فشار حافظه همچنان Latency می‌سازند. Database رابطه‌ای نیز RAM Cache دارد؛ برتری Redis مربوط به Workload و مدل دسترسی مشخص است، نه هر Query ممکن.

[معنای تحویل پیام در Pub/Sub](https://redis.io/docs/latest/develop/pubsub/)

[ملاحظات ایمنی Distributed Lock](https://redis.io/docs/latest/develop/clients/patterns/distributed-locks/)

## ۲. کاربرد Redis در معماری Enterprise

![معماری Production Redis: سرورهای برنامه، Primary، Replica و Database در شبکه خصوصی](/assets/img/articles/content/redis-production-architecture.png)

معماری Production Redis: سرورهای برنامه، Primary، Replica و Database در شبکه خصوصی

```text
User -> Load Balancer -> Application Servers
                              |          |
                              v          v
                         Redis Cache   Database
                              |
                              v
                         Redis Replica
```

Application هم به Redis و هم به Database پایدار متصل است. در Cache-Aside ابتدا Redis خوانده می‌شود؛ در Cache Miss، Database Query می‌شود و نتیجه با TTL در Redis قرار می‌گیرد. Redis یک Hop شبکه اجباری میان برنامه و Database نیست. Invalidation پس از Write، تغییر جزئی TTL برای جلوگیری از انقضای همزمان، کنترل Cache Stampede و Fallback محدود هنگام قطع Redis را طراحی کنید.

- Application Cache، Laravel Cache و API Cache: Prefix و TTL جدا برای هر برنامه؛ پشتیبانی Client از Username و TLS را بررسی کنید.
- Authentication Session و Session Storage: در نیاز به تازگی از Primary بخوانید؛ اثر ازدست‌رفتن Session بر Login را مشخص کنید.
- Queue و Microservices: Job مهم را روی Instance جدا با Policy بدون Eviction قرار دهید؛ Retry مصرف‌کننده و پردازش Idempotent لازم است.
- Rate Limit: عملیات اتمیک و تصمیم روشن برای Allow یا Reject درخواست هنگام خرابی Redis.

Logical Database و Prefix برای سازمان‌دهی کلید مفیدند، اما RAM، Eviction، CPU و Availability را جدا نمی‌کنند. Instance مشترک Cache نباید کلید حیاتی Session، Queue یا Lock را Evict کند. در تفاوت نیازها، Deployment جدا انتخاب کنید.

## پیش‌نیازها و سناریوی استقرار

```text
Application Servers: 10.10.30.21, 10.10.30.22
             |
             v
Redis Primary: 10.10.20.10:6379
             |
             | Asynchronous Replication
             v
Redis Replica: 10.10.20.11:6379

Optional Replica-02: 10.10.20.12:6379
Management jump host: 10.10.40.10
Prometheus host: 10.10.40.20
```

پیش‌نیازها: IP خصوصی ثابت، DNS و Time Sync صحیح، دسترسی SSH یا Console، حساب سرویس مستقل Redis، ظرفیت RAM و SSD اندازه‌گیری‌شده و Maintenance Window. هر دو نود را با Patch یکسان Redis و Moduleهای سازگار شروع کنید. IPهای نمونه را با شبکه خود عوض کنید؛ آدرس‌های تکمیلی بالا برای دقیق‌بودن مثال Firewall تعریف شده‌اند.

## ۳. Redis Replication Architecture

![Replication ناهمزمان Redis از یک Primary به دو Replica فقط‌خواندنی](/assets/img/articles/content/redis-replication-architecture.png)

Replication ناهمزمان Redis از یک Primary به دو Replica فقط‌خواندنی

```text
Applications
                      |
                      v
                 Redis Primary
              10.10.20.10:6379
                      |
           +----------+----------+
           |                     |
           v                     v
       Replica-01             Replica-02
   10.10.20.11:6379       10.10.20.12:6379
```

برای Replica-02 فرآیند Replica را با bind برابر 10.10.20.12 تکرار کنید، Rule صریح روی Primary اضافه و replicaof را همچنان به 10.10.20.10 اشاره دهید. Credentialها را مستقل توزیع و هر دو اتصال را بررسی کنید. هر نود Redis در این معماری کپی کامل Dataset دارد؛ داده میان سه نود تقسیم نمی‌شود.

## ۴. Replication به‌تنهایی High Availability نیست

اگر Primary خراب شود، Replica مستقل خودکار Primary جدید نمی‌شود و Endpoint برنامه خودکار جابه‌جا نمی‌شود. Promotion دستی بدون Fencing مربوط به Primary قبلی در Network Partition می‌تواند دو Primary قابل Write بسازد. Restart خودکار Process نیز Failover نیست. Primary بدون Persistence نباید خالی Restart شود و Replicaهای دارای داده را از Dataset خالی دوباره Sync کند.

Write تأییدشده Primary ممکن است به Replica انتخاب‌شده برای Failover نرسیده باشد. WAIT پنجره را کاهش می‌دهد؛ WAITAOF در تنظیم پشتیبانی‌شده منتظر Acknowledgment مربوط به fsync در AOF روی مشارکت‌کننده‌های مشخص می‌ماند. هیچ‌کدام جای Backup نیست و معماری ناهمزمان را به سیستم Consensus همیشه بدون Loss تبدیل نمی‌کند. RPO/RTO را از نیاز کسب‌وکار انتخاب و بازیابی Crash، خرابی Host و Network Partition را تست کنید.

[Acknowledgment مربوط به Durability با WAITAOF](https://redis.io/docs/latest/commands/waitaof/)

## ۵. تفاوت Replication، Sentinel و Redis Cluster

| معماری | Replication | Failover خودکار | Sharding |
| --- | --- | --- | --- |
| Replication | بله | خیر | خیر |
| Sentinel | بله | بله | خیر |
| Redis Cluster | بله، با Replica | بله، با Replica مناسب و Quorum | بله |

Replication داده را کپی می‌کند. Sentinel، Monitoring، Discovery و Failover را به گروه Primary/Replica بدون Sharding اضافه می‌کند. Redis Cluster کلیدها را میان 16,384 Hash Slot توزیع و از Failover Protocol خود استفاده می‌کند؛ برای Failover مربوط به Cluster به Sentinel نیاز ندارد. Topology رایج شروع Production سه Primary و یک Replica برای هر Primary، مجموعاً شش نود است؛ Primary و Replica متناظر نباید Failure Domain مشترک داشته باشند.

Cluster به Client سازگار نیاز دارد و عملیات Multi-Key را تغییر می‌دهد: کلیدهای مرتبط معمولاً به Hash Tag برای Slot مشترک نیاز دارند. فقط Database 0 دارد. Migration، Resharding و دسترسی به Port Client و Cluster Bus را برنامه‌ریزی کنید. Port پیش‌فرض Bus برابر Port داده به‌اضافه 10000 است؛ برای 6379 معمولاً 16379 می‌شود. ACL Port Client، Bus را احراز هویت نمی‌کند. Redis 8.10.2 صریحاً tls-cluster و cluster-bus-port-protected-mode را توضیح داده است؛ Bus را امن و Segment و گواهی و Client Redirection را تست کنید.

[Topology، Hash Slot و Portهای رسمی Redis Cluster](https://redis.io/docs/latest/operate/oss_and_stack/management/scaling/)

[تغییر امنیت Cluster Bus در Redis 8.10.2](https://github.com/redis/redis/releases/tag/8.10.2)

## ۶. نصب Redis روی Ubuntu

این مراحل را روی هر دو نود اجرا کنید. روش ساده مخزن توزیع ابتدا به‌عنوان گزینه جایگزین آمده است و تضمین نصب آخرین Redis بالادستی نیست. برای نسخه Stable مبنای مقاله، مخزن رسمی APT Redis را انتخاب کنید و قبل از نصب Candidate را بررسی کنید.

### گزینه جایگزین: مخزن Ubuntu

```bash
sudo apt update
apt-cache policy redis-server
sudo apt install redis-server
redis-server --version
```

### نصب از مخزن رسمی Redis

```bash
sudo apt-get update
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
systemctl status redis-server --no-pager
```

اگر مخزن رسمی این Patch را ارائه کند، در redis-server --version مقدار v=8.10.2 و وضعیت سرویس active (running) انتظار می‌رود. INFO server جداگانه redis_version:8.10.2 را می‌دهد. رشته نسخه بسته Epoch و پسوند Distribution دارد؛ یک Package String عمومی برای 8.10.2 نسازید. اگر Candidate قدیمی‌تر است، پشتیبانی مخزن را بررسی کنید و آن را نسخه فعلی معرفی نکنید. فرمان GPG فرض می‌کند Keyring هنوز وجود ندارد؛ در اجرای مجدد Keyring را آگاهانه مقایسه و جایگزین کنید. از signed-by استفاده می‌شود، نه apt-key منسوخ.

نسخه دقیق بسته‌ها را از نود اول ثبت و همان نسخه تأییدشده را روی Replica نصب کنید. سازگاری Client و Module را در Staging تست و Patch امنیتی را با Change Management اعمال کنید. بدون فرآیند وصله‌کردن، بسته‌ها را دائماً Hold نکنید. تا تکمیل ACL و Firewall، Bind را روی Loopback نگه دارید.

[راهنمای رسمی نصب Redis با APT](https://redis.io/docs/latest/operate/oss_and_stack/install/install-stack/apt/)

## ۷. بررسی سرویس Redis

```bash
systemctl status redis-server --no-pager
sudo systemctl show redis-server -p User -p Group -p Type -p ExecStart
sudo ss -lntp | grep ':6379'
redis-cli -h 127.0.0.1 -p 6379 ping
sudo journalctl -u redis-server -n 100 --no-pager
```

```text
active (running)
LISTEN ... 127.0.0.1:6379 ... redis-server
PONG
```

PONG بدون Credential فقط بررسی اولیه محلی است. پس از Hardening باید NOAUTH دریافت شود؛ برای گرفتن PONG از Named User و --askpass استفاده کنید. ss وجود Socket را تأیید می‌کند، نه Authentication یا Replication. Listener نباید Interface عمومی را شامل شود. ExecStart نیز Override خط فرمان و فایل تنظیماتی واقعاً بارگذاری‌شده را نشان می‌دهد.

```bash
redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass PING
```

## ۸. ساختار Configuration و Backup پیش از تغییر

نصب APT معمولاً از /etc/redis/redis.conf استفاده می‌کند؛ با systemctl cat و dpkg -L آن را تأیید کنید. Redis 8.10 از redis.conf استفاده می‌کند؛ مدل redis-full.conf جدا در برخی 8.x قدیمی‌تر مبنای این راهنما نیست. مسیر Moduleها و تنظیمات سرویس بسته را حفظ کنید. Baseline عملیاتی را با include نهایی اعمال کنید و کل فایل بسته را بی‌دلیل جایگزین نکنید.

```bash
systemctl cat redis-server
dpkg -L redis-server | grep -E 'redis.conf|systemd'
sudo cp -a /etc/redis/redis.conf "/etc/redis/redis.conf.backup.$(date -u +%Y%m%dT%H%M%SZ)"
sudo install -d -o redis -g redis -m 750 /var/lib/redis /var/log/redis
sudo install -o root -g redis -m 640 /dev/null /etc/redis/production.conf
sudoedit /etc/redis/production.conf
sudoedit /etc/redis/redis.conf
```

خط زیر را فقط یک بار در انتهای redis.conf اضافه کنید. ابتدا طبق بخش ۱۱ users.acl را بسازید؛ برای یک منبع ACL، تعریف فعال inline user و requirepass قدیمی را حذف کنید. در Deployment موجود از فایل ACL و Config فعلی پیش از تغییر Backup بگیرید. نمونه‌ها برای استقرار جدیدند؛ تبدیل Instance دارای داده از RDB-only به AOF به فرآیند Migration زنده نیاز دارد.

```text
include /etc/redis/production.conf
```

[ساختار رسمی فایل تنظیمات و دوام تغییر Runtime](https://redis.io/docs/latest/operate/oss_and_stack/management/config/)

## ۹. تنظیم Redis Primary

روی 10.10.20.10 قطعه تنظیمات زیر را در /etc/redis/production.conf قرار دهید. فرض مثال Host مستقل با 8 GiB RAM و سقف اولیه 4 GiB برای Dataset است؛ پیش از استفاده عملیاتی سربار Copy-on-Write، Module و Replication را اندازه بگیرید. noeviction مبنای داده‌ای است که نباید بی‌صدا حذف شود؛ پروفایل مستقل Cache در بخش ۲۱ آمده است.

```text
# Final include for a NEW dedicated Redis deployment; preserve package redis.conf.
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
latency-monitor-threshold 100
```

| Directive | کارکرد |
| --- | --- |
| bind | Listener فقط روی Loopback و IP خصوصی مشخص. |
| protected-mode | Guard خطای تنظیم را فعال نگه دارید؛ ACL و Firewall همچنان لازم‌اند. |
| port | TCP/6379 در Baseline خصوصی؛ TLS می‌تواند جایگزین آن شود. |
| timeout | صفر Pool بیکار را حفظ می‌کند؛ Timeout درخواست را در برنامه محدود کنید. |
| tcp-keepalive | ۳۰۰ ثانیه برای تشخیص Peer قطع‌شده؛ با Idle Limit شبکه تطبیق دهید. |
| daemonize / supervised | اجرای Foreground؛ auto محیط Notify مربوط به systemd را تشخیص می‌دهد. |
| loglevel / logfile | سطح notice در فایل قابل نوشتن Redis؛ Log Rotation بسته را بررسی کنید. |
| dir | دایرکتوری پایدار قابل نوشتن؛ ظرفیت دیسک و Permission را پایش کنید. |

Unit با Type=notify ممکن است --supervised systemd را خودش بدهد؛ قرارداد بسته را حفظ کنید. supervised no برای برخی Unitهای Type=simple مناسب است. Unit واقعی را بررسی کنید و daemonize yes یا Type را اجباری تغییر ندهید. در پروفایل logfile "" خروجی به stdout و معمولاً Journal می‌رود؛ مسیر Logging متناسب با Unit را انتخاب و تأیید کنید.

[مرجع تنظیمات Tag نسخه Redis 8.10.2](https://raw.githubusercontent.com/redis/redis/8.10.2/redis.conf)

## ۱۰. Security Hardening و تفکیک شبکه

Redis را حتی با Password مستقیماً روی Internet منتشر نکنید. آن را در VLAN/Subnet خصوصی قرار دهید و Ingress را در Host Firewall و Security Group بالادستی محدود کنید. فقط Application Server و Peerهای Replication به سرویس داده دسترسی داشته باشند. Management و Monitoring به استثنای صریح و محدود نیاز دارند؛ ترجیحاً Agent محلی یا Jump Host کنترل‌شده. protected-mode یک Guard است، نه Firewall یا رمزنگاری.

### مثال Firewall روی Primary

```bash
sudo apt-get install ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow from 10.10.40.10 to any port 22 proto tcp
sudo ufw allow from 10.10.30.21 to 10.10.20.10 port 6379 proto tcp
sudo ufw allow from 10.10.30.22 to 10.10.20.10 port 6379 proto tcp
sudo ufw allow from 10.10.20.11 to 10.10.20.10 port 6379 proto tcp
sudo ufw enable
sudo ufw status numbered
```

پیش از ufw enable، Rule مربوط به Source واقعی SSH فعلی را اضافه و دسترسی Console مستقل را تأیید کنید. Rule عمومی قدیمی برای 6379 یا Subnet را بازبینی کنید؛ افزودن Rule محدود آن را حذف نمی‌کند. روی 10.10.20.11 Policy جدا اعمال کنید: SSH از Jump Host، Read اختیاری برنامه و Peerهای آینده Redis در صورت HA. با Allow خروجی معمول، Replica اتصال به Primary را آغاز می‌کند؛ Primary برای Replication اتصال جدیدی به Replica آغاز نمی‌کند.

- سرویس با User بسته یعنی redis اجرا شود، نه root؛ Config و Credential را به root/redis محدود و مسیر داده و سرویس را جدا کنید.
- Command و Prefix کلید/Channel برنامه را صریح Allow کنید. FLUSHALL، FLUSHDB، CONFIG، MODULE و Scripting مدیریتی بدون نیاز واقعی مجاز نباشند.
- Command Renaming برای Hardening روش منسوخ است؛ از Deny در ACL استفاده کنید. Rename فرمان کنترل یا Replication می‌تواند Sentinel و ابزار عملیاتی را خراب کند.
- Secret را با Secret Manager Rotate کنید، Role برنامه/Admin/Replication را جدا کنید، SSH را محدود و درخواست Denied را تست کنید.

### پروفایل TLS برای شبکه حساس

Baseline روی TCP رمزنگاری نشده است؛ AUTH هم رمز نمی‌شود. برای داده حساس یا Transport غیرقابل اعتماد، Listener هر دو نود را با TLS جایگزین کنید. گواهی CA با DNS/IP SAN صحیح، Private Key قابل خواندن Redis و Client Certificate برای هر Client و Replica مجاز تهیه کنید. قطعه زیر Overlay جایگزین است، نه Listener اضافه بدون رمز.

```text
port 0
tls-port 6379
tls-cert-file /etc/redis/tls/redis.crt
tls-key-file /etc/redis/tls/redis.key
tls-ca-cert-file /etc/redis/tls/ca.crt
tls-auth-clients yes
tls-replication yes
```

```bash
redis-cli --tls -h 10.10.20.10 -p 6379 --cacert /etc/redis/tls/ca.crt   --cert /etc/redis/tls/client.crt --key /etc/redis/tls/client.key   --user admin --askpass PING
```

tls-replication yes باید روی تمام Candidateهای Promotion، از جمله Primary فعلی، آماده باشد. Application Client باید هویت سرور و Chain گواهی را بررسی کند؛ Skip بررسی گواهی را Default نکنید. TLS جای ACL یا Segmentation را نمی‌گیرد. پشتیبانی TLS بسته نصب‌شده و Renewal و Rotation گواهی را تست کنید.

[امنیت رسمی Redis و منسوخ‌شدن Command Renaming](https://redis.io/docs/latest/operate/oss_and_stack/management/security/)

[تنظیم TLS در Redis](https://redis.io/docs/latest/operate/oss_and_stack/management/security/encryption/)

## ۱۱. Authentication با Redis ACL

از Named User در ACL استفاده کنید. requirepass همچنان برای سازگاری User پیش‌فرض پشتیبانی می‌شود؛ طراحی پیشنهادی Multi-User نیست و ادعا نمی‌کنیم حذف شده است. در این راهنما default غیرفعال، Administrator مستقل، عملیات Cache/Session محدود و Replica فقط دارای PING، REPLCONF و PSYNC است. ACL قابلیت داخلی است؛ «ACL فعال است» یعنی User و Permission مؤثر مستقر و بررسی شده‌اند.

روی نود جدید، Script Bash زیر را پیش از Restart با include بخش ۹ اجرا کنید. چهار Secret قوی و مستقل که قبلاً در Secret Manager ذخیره کرده‌اید وارد کنید؛ Secret مربوط به Replication در هر دو نود یکسان باشد. حداقل ۳۲ بایت تصادفی به‌صورت Hex انتخاب عملی است. Script ورودی ضعیف یا غیر Hex را رد می‌کند، Hash را به‌جای متن Password در users.acl می‌نویسد و Secret چاپ نمی‌کند. فایل ACL موجود را عمداً Overwrite نمی‌کند.

```bash
#!/usr/bin/env bash
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
echo "ACL file installed; secrets remain in your secret manager."
```

Role ادمین آگاهانه Privileged است و فقط برای Operations از مسیر Management محدود استفاده می‌شود. برنامه +@all دریافت نمی‌کند. app بالا برای عملیات ساده String Cache/Session است، نه هر Laravel Queue Driver یا Lua Lock؛ Role Worker جدا را از Command واقعی و Prefix مورد نیاز بسازید و با ACL DRYRUN و ترافیک Staging بررسی کنید. روی نود زیر Sentinel، دسترسی عمومی Channel ندهید.

```bash
sudo systemctl restart redis-server
systemctl is-active redis-server
redis-cli --user admin --askpass PING
redis-cli --user admin --askpass ACL LIST
redis-cli --user admin --askpass ACL WHOAMI
redis-cli --user admin --askpass ACL DRYRUN app SET cache:probe ok
redis-cli --user admin --askpass ACL DRYRUN app FLUSHALL
redis-cli --user app --askpass ACL WHOAMI
redis-cli PING
```

```text
active
PONG
ACL LIST: user default off ...; named users with #<sha256> hashes
ACL WHOAMI (admin): admin
ACL DRYRUN app SET cache:probe ok: OK
ACL DRYRUN app FLUSHALL: permission denied (wording may vary)
ACL WHOAMI (app): app
Unauthenticated PING: NOAUTH Authentication required.
```

ACL LIST Ruleها و Hashهای Password را برمی‌گرداند؛ فقط به Admin بدهید و خروجی آن را منتشر نکنید. WHOAMI هویت مؤثر اتصال را نشان می‌دهد. --askpass Password را وارد Argument فرمان و Shell History نمی‌کند. تغییر Runtime با ACL SETUSER به Persistence جدا نیاز دارد؛ CONFIG REWRITE فایل ACL خارجی را ذخیره نمی‌کند. اینجا فایل زیر کنترل root است؛ آن را امن Edit و ACL LOAD احراز هویت‌شده یا Restart اجرا کنید. ACL SAVE به مسیر و دایرکتوری قابل نوشتن Redis نیاز دارد و در این استقرار root-owned نباید موفق فرض شود.

[قواعد ACL، فایل خارجی و Permission مربوط به Replication](https://redis.io/docs/latest/operate/oss_and_stack/management/security/acl/)

[شبیه‌سازی Permission با ACL DRYRUN](https://redis.io/docs/latest/commands/acl-dryrun/)

[مرجع ACL WHOAMI](https://redis.io/docs/latest/commands/acl-whoami/)

[مرجع ACL LIST](https://redis.io/docs/latest/commands/acl-list/)

## ۱۲. Persistence؛ RDB و AOF

### RDB؛ Snapshot در یک لحظه مشخص

RDB یک Snapshot فشرده نگه می‌دارد و برای Backup زمان‌بندی‌شده و Loading سریع مناسب است؛ ممکن است تمام تغییرهای پس از آخرین Snapshot موفق از دست بروند. save 900 1 یعنی شرط حداقل یک تغییر در ۹۰۰ ثانیه، نه Timer بدون شرط. BGSAVE یک Child با Fork می‌سازد؛ برای Copy-on-Write، CPU، پهنای باند دیسک و RAM ذخیره کنید. از SAVE همزمان روی سرویس شلوغ اجتناب کنید.

```bash
redis-cli --user admin --askpass BGSAVE
redis-cli --user admin --askpass INFO persistence
redis-cli --user admin --askpass LASTSAVE
```

```text
rdb_bgsave_in_progress:0
rdb_last_bgsave_status:ok
rdb_last_save_time:<unix_timestamp>
```

این Fieldهای مورد انتظار مربوط به Save تکمیل‌شده‌اند؛ بلافاصله پس از BGSAVE ممکن است Flag مقدار 1 باشد. زمان Save موفق جدیدتر را تأیید کنید؛ بلافاصله پس از فرمان، dump.rdb قدیمی را کپی نکنید.

[فرمان Snapshot پس‌زمینه BGSAVE](https://redis.io/docs/latest/commands/bgsave/)

### AOF؛ ثبت تغییرها در Append Only File

AOF عملیات Write را برای Recovery ثبت می‌کند. appendfsync everysec مصالحه رایج Latency و Durability است: Crash معمولاً حدود آخرین یک ثانیه را از دست می‌دهد و اختلال OS یا Storage می‌تواند پنجره واقعی را بزرگ‌تر کند. always برای هر Batch نوشتن fsync می‌خواهد و Latency بیشتری دارد؛ no زمان Flush را به OS می‌سپارد. تضمین Storage را ارزیابی کنید و هیچ Policy را خودکار Zero-Loss فرض نکنید.

Redis فعلی Multipart AOF دارد: Base File، Incremental Fileها و Manifest زیر appenddirname. با aof-use-rdb-preamble yes ممکن است Base با قالب RDB باشد؛ این معادل dump.rdb زمان‌بندی‌شده جدا نیست. BGREWRITEAOF تاریخچه را فشرده می‌کند و به ظرفیت اضافه نیاز دارد. کپی فقط یک appendonly.aof قدیمی Backup کامل AOF فعلی نیست.

```bash
redis-cli --user admin --askpass INFO persistence
redis-cli --user admin --askpass BGREWRITEAOF
redis-cli --user admin --askpass INFO persistence
```

```text
aof_enabled:1
aof_rewrite_in_progress:0
aof_last_bgrewrite_status:ok
aof_last_write_status:ok
```

| ویژگی | RDB | AOF |
| --- | --- | --- |
| Performance | سربار Write روزمره کم؛ جهش هنگام Fork | وابسته به fsync و بار Rewrite |
| Recovery | آخرین Snapshot موفق | بازیابی Base و تاریخچه Incremental |
| File size | معمولاً کوچک‌تر و فشرده | معمولاً بزرگ‌تر؛ Rewrite آن را فشرده می‌کند |
| Durability | ریسک تغییرهای پس از Snapshot | دوام Write بهتر، وابسته به Policy |
| Production usage | بله، اگر RPO مربوط به Snapshot قابل قبول باشد | بله، اگر دوام Write لازم باشد |

ترکیب RDB و AOF برای Snapshot دوره‌ای قابل انتقال و Recovery بهتر Write مناسب است. با فعال‌بودن هر دو، Redis در Startup معمولاً AOF را بارگذاری می‌کند چون کامل‌تر است. این ترکیب نیاز به Backup و تست Restore را حذف نمی‌کند. در Cache قابل بازسازی می‌توان Persistence را با پذیرش Warm-up و بار Database غیرفعال کرد؛ برای Session یا Job، RPO/RTO صریح تعیین کنید.

### فعال‌کردن امن AOF روی Instance موجود با RDB-only

ابتدا Backup بگیرید. AOF را در Runtime فعال، تکمیل موفق Initial Rewrite و وضعیت سالم AOF را تأیید، سپس appendonly yes را در Config مدیریت‌شده ذخیره کنید. Dataset موجود را ابتدا با AOF تازه و دایرکتوری خالی Restart نکنید؛ Startup ممکن است AOF خالی را انتخاب کند و مسیر Recovery مورد نظر از RDB را از بین ببرد. تغییر یا غیرفعال‌کردن Persistence یک Migration است.

```bash
redis-cli --user admin --askpass CONFIG SET appendonly yes
redis-cli --user admin --askpass INFO persistence
```

[مرجع رسمی RDB، Multipart AOF، Recovery و Migration زنده AOF](https://redis.io/docs/latest/operate/oss_and_stack/management/persistence/)

## ۱۳. راه‌اندازی Redis Replica

روی 10.10.20.11 بسته‌های یکسان، ACL مستقل و مسیر شبکه محدود ایجاد و Baseline زیر را با include نهایی اعمال کنید. Replication، Dataset را منتقل می‌کند، نه فایل ACL یا Config؛ آن‌ها را مستقل Provision کنید. Username برابر repl روی Primary و Secret متناظر در masterauth است. تمام کپی‌های production.conf دارای masterauth فقط برای root/redis قابل خواندن باشند.

```text
# Final include for a NEW dedicated Redis deployment; preserve package redis.conf.
bind 127.0.0.1 10.10.20.11
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
latency-monitor-threshold 100
replicaof 10.10.20.10 6379
masteruser repl
masterauth "REPLACE_WITH_REPLICATION_SECRET"
replica-priority 100
```

REPLACE_WITH_REPLICATION_SECRET جایگزینی اجباری از Secret Manager است، نه Password قابل استفاده. فایل ACL، Hash نگه می‌دارد اما masterauth برای ارسال AUTH به Primary به Secret واقعی نیاز دارد. Marker را پیش از Start حذف کنید و Secret واقعی را در Download عمومی مقاله قرار ندهید. این تنظیمات دائمی‌اند. Full Sync اولیه داده قبلی Replica را جایگزین می‌کند؛ نود دارای داده مستقل ارزشمند را بدون Backup و Migration Plan به Primary متصل نکنید.

```bash
sudo chown root:redis /etc/redis/production.conf /etc/redis/users.acl
sudo chmod 640 /etc/redis/production.conf /etc/redis/users.acl
sudo systemctl restart redis-server
redis-cli --user admin --askpass INFO replication
sudo journalctl -u redis-server -n 100 --no-pager
```

برای تغییر Topology در Runtime پس از تنظیم Authentication، فرمان فعلی REPLICAOF است. تغییر خودکار دائمی نمی‌شود. فایل را صریح مدیریت کنید؛ CONFIG REWRITE به مجوز Write و Policy مربوط به Includeها نیاز دارد. در Runbook اپراتور از فرمان قدیمی SLAVEOF استفاده نکنید.

```bash
redis-cli --user admin --askpass REPLICAOF 10.10.20.10 6379
```

Partial Sync پس از قطعی کوتاه می‌تواند از Backlog Primary جبران کند؛ قطعی طولانی یا تاریخچه ناکافی Full Sync را فعال می‌کند. repl-backlog-size را از نرخ بایت Replication ضرب‌در مدت قطعی قابل تحمل، با حاشیه ظرفیت محاسبه کنید. 64 MiB مثال فقط نقطه شروع است. Full Sync و Persistence هر دو RAM و I/O مصرف می‌کنند.

[سینتکس فعلی REPLICAOF](https://redis.io/docs/latest/commands/replicaof/)

[همگام‌سازی و Authentication در Redis Replication](https://redis.io/docs/latest/operate/oss_and_stack/management/replication/)

## ۱۴. بررسی وضعیت Replication

### روی Replica

```bash
redis-cli -h 127.0.0.1 --user monitor --askpass INFO replication
```

```text
role:slave
master_host:10.10.20.10
master_port:6379
master_link_status:up
master_last_io_seconds_ago:0
master_sync_in_progress:0
slave_read_only:1
```

Redis 8.10 برای سازگاری نام‌های خروجی قدیمی مثل role:slave و slave0 را در INFO حفظ کرده است؛ این Fieldها توصیه به Syntax تنظیمات منسوخ نیستند. master_last_io_seconds_ago با Heartbeat تغییر می‌کند. Link برابر up، Sync تکمیل‌شده و Catch-up پایدار لازم است. Link سالم به‌تنهایی به‌روز بودن هر Write را اثبات نمی‌کند.

### روی Primary

```bash
redis-cli -h 127.0.0.1 --user monitor --askpass INFO replication
```

```text
role:master
connected_slaves:1
slave0:ip=10.10.20.11,port=6379,state=online,offset=<bytes>,lag=<seconds>
master_repl_offset:<bytes>
repl_backlog_active:1
```

در سناریوی دو نود connected_slaves:1 و با افزودن Replica-02 مقدار 2 انتظار می‌رود. lag سن Acknowledgment است، نه اندازه‌گیری دقیق تازگی Read برنامه. Offset هر Replica را با Primary در طول زمان مقایسه کنید؛ اختلاف بایت رو به رشد فشار Catch-up را نشان می‌دهد. ترتیب و Fieldهای خروجی ممکن است تغییر کنند؛ نام Field را Parse کنید، نه موقعیت خط.

[Fieldهای فعلی INFO و نام‌های Legacy خروجی Replication](https://redis.io/docs/latest/commands/info/)

## ۱۵. تست عملی Replication

برای کلید company در مثال از Admin استفاده کنید چون app فقط cache:* و session:* را می‌بیند. روی Primary اتصال Interactive احراز هویت‌شده باز کنید؛ SET و WAIT باید در همان Connection اجرا شوند. WAIT، Acknowledgment مربوط به Write قبلی همین Client را بررسی می‌کند، نه CLI جدا.

```bash
redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass
```

```redis
SET company "MEET AJ"
WAIT 1 5000
```

```text
OK
(integer) 1
```

سپس روی Replica اتصال Admin محلی باز و کلید Replicated را بخوانید. اگر WAIT مقدار 0 داد، قبل از موفق‌دانستن تست بررسی کنید؛ Timeout، SET را Rollback نمی‌کند.

```bash
redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass
```

```redis
GET company
```

```text
"MEET AJ"
```

رفتار Read-Only را روی Replica با Probe بی‌ضرر بررسی کنید؛ پاسخ مورد انتظار READONLY است. پس از بررسی، کلید تست company را از Primary پاک کنید. در تست برنامه واقعی از کلید اختصاصی دارای TTL زیر Prefix مجاز استفاده کنید، نه Probe دائمی بدون محدودیت.

```redis
SET cache:replica-write-probe "blocked" EX 60
```

```text
READONLY You can't write against a read only replica.
```

```bash
redis-cli --user admin --askpass DEL company
```

[معنای Acknowledgment و محدودیت WAIT](https://redis.io/docs/latest/commands/wait/)

## ۱۶. Read From Replica و Read Scaling

Replicaها Dataset Primary را کپی می‌کنند و به‌صورت پیش‌فرض Read-Only هستند. Application می‌تواند Read مناسب را صریح به آن‌ها Route کند؛ Redis خودکار Read را میان Replicaهای Standalone تقسیم نمی‌کند. Replica مستقل برای Read به فرمان مخصوص Cluster یعنی READONLY نیاز ندارد. Client یا Router آگاه از Topology باید تمام Writeها را به Primary فعلی بفرستد.

Replication ناهمزمان است؛ Replica می‌تواند داده قدیمی بدهد، حتی بلافاصله پس از SET موفق روی Primary. Session، وضعیت مجوز، Lock و تصمیم Rate Limit را از Primary بخوانید مگر طراحی Consistency صریحاً داده قدیمی را بپذیرد. WAIT اطمینان Acknowledgment را بیشتر می‌کند، اما Read دلخواه Replica را Linearizable یا Flush دیسک را تضمین نمی‌کند.

در Baseline مقدار replica-serve-stale-data no باعث شکست Read داده هنگام Link قطع یا Sync ناقص می‌شود؛ Lag با Link سالم را حذف نمی‌کند. مقدار پیش‌فرض yes در قطعی ممکن است داده قدیمی سرو کند. Availability و Freshness را آگاهانه انتخاب و روی Replica قطع یا Lagging هشدار بدهید. افزودن Replica سربار شبکه و Replication Primary را بیشتر می‌کند؛ Memory را Shard یا Write Primary را Scale نمی‌کند.

## ۱۷. Redis Sentinel و High Availability

![High Availability در Redis Sentinel با سه Sentinel، یک Primary، دو Replica و Failover خودکار](/assets/img/articles/content/redis-sentinel-high-availability.png)

High Availability در Redis Sentinel با سه Sentinel، یک Primary، دو Replica و Failover خودکار

```text
Application -- discovery --> Sentinel 1 / Sentinel 2 / Sentinel 3
     |
     +---- data connection --> Current Primary
                                  |
                              +---+---+
                              |       |
                           Replica 1 Replica 2
```

- Monitoring: بررسی سلامت Instanceها و Topology.
- Failure Detection: تفکیک Subjective Down هر ناظر از Objective Down مورد تأیید Quorum.
- Automatic Failover: هماهنگی Promotion یک Replica مناسب و Reconfigure نودهای دیگر.
- Primary Discovery: اعلام آدرس Primary فعلی به Client سازگار با Sentinel.

حداقل سه Sentinel در Failure Domain مستقل داشته باشید. طراحی عملی Production شامل یک Primary، دو Replica و سه Sentinel است؛ Sentinelها می‌توانند روی Hostهای Redis اجرا شوند اگر Hostها Failure Domain مستقل دارند. برای سه Sentinel، Quorum برابر 2 تشخیص Objective Failure می‌دهد؛ Majority کل مجموعه نیز برای تأیید Failover لازم است. Quorum و Majority انتخاب رهبر دو شرط متفاوت‌اند. سه Process روی یک Host مقاومت در برابر خرابی Host ایجاد نمی‌کند.

Sentinel یک Control Plane است، نه Data Proxy. ترافیک برنامه مستقیم به Primary کشف‌شده وصل می‌شود. Client سازگار، چند Endpoint کشف، Retry محدود و Credential جدا برای Sentinel و Data Plane لازم‌اند. تمام Primaryهای بالقوه باید User برنامه، Replication و Sentinel، Secret Replication هماهنگ، Persistence و Memory مناسب داشته باشند. پیش از HA Cutover، masteruser/masterauth را روی Primary اولیه نیز آماده کنید تا بعداً بتواند Replica شود.

### مثال Configuration مربوط به Sentinel و پیش‌نیاز امنیتی

```text
# Template fragment: merge into each node-specific Sentinel configuration.
# Replace the auth-pass marker using your secret manager.
port 26379
sentinel monitor redis-prod 10.10.20.10 6379 2
sentinel auth-user redis-prod sentinel-control
sentinel auth-pass redis-prod "REPLACE_WITH_SENTINEL_CONTROL_SECRET"
sentinel down-after-milliseconds redis-prod 5000
sentinel failover-timeout redis-prod 60000
sentinel parallel-syncs redis-prod 1
```

این Fragment، Deployment کامل و Hardening‌شده Sentinel نیست. bind خصوصی مخصوص هر نود و Firewall روی TCP/26379 تنظیم کنید؛ Endpoint و Authentication میان Peerهای Sentinel را طبق مستندات با ACL امن کنید، sentinel-control را روی تمام نودهای Redis بسازید و فایل Config Sentinel را در دایرکتوری قابل نوشتن سرویس قرار دهید چون Topology را Rewrite می‌کند. ترافیک کنترلی به تمام Candidateها و ارتباط میان سه Sentinel لازم است. آستانه ۵ ثانیه فقط مثال است؛ با Pause واقعی CPU/شبکه تطبیق و Failover اشتباه را تست کنید.

به sentinel-control فقط عملیات کنترلی مستند و Channel برابر &__sentinel__:hello بدهید. ACL رسمی Sentinel فعلاً +slaveof را برای فرمان داخلی سازگاری می‌خواهد و ابزار ممکن است +replicaof نیز لازم داشته باشد؛ این استثنای سازگاری صریح است، نه استفاده اپراتور از SLAVEOF منسوخ. Application User نباید روی Channel رزروشده __sentinel__: پیام Publish کند. تغییر Topology Sentinel را با Configuration Manager هماهنگ کنید؛ Deploy فایل ثابت نباید بعد از Failover، Primary قدیمی را برگرداند.

```bash
# After provisioning a Sentinel observer user with these subcommands:
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL CKQUORUM redis-prod
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL GET-MASTER-ADDR-BY-NAME redis-prod
redis-cli -h 127.0.0.1 -p 26379 --user sentinel-observer --askpass SENTINEL REPLICAS redis-prod
```

معیار پذیرش: CKQUORUM وجود Sentinel کافی و Majority را تأیید می‌کند؛ Discovery آدرس و Port فعلی می‌دهد. در Staging با Failure Test بررسی‌شده، Primary را متوقف و Promotion، اتصال مجدد برنامه، ادامه Replication و برگشت Primary قبلی به‌عنوان Replica را تأیید کنید. Network Partition را تست و Write ازدست‌رفته و زمان Recovery را اندازه بگیرید. در پذیرش Tradeoff مربوط به Availability می‌توانید Write را با min-replicas-to-write/min-replicas-max-lag محدود کنید؛ این محدودیت‌ها ریسک را کم می‌کنند، نه اینکه Strong Consistency بسازند.

[الزامات رسمی Sentinel، Quorum، ACL و Failover](https://redis.io/docs/latest/operate/oss_and_stack/management/sentinel/)

[پروتکل Discovery برای Clientهای Sentinel](https://redis.io/docs/latest/develop/reference/sentinel-clients/)

## ۱۸. Monitoring و Metricهای عملیاتی

```bash
redis-cli --user monitor --askpass INFO
redis-cli --user monitor --askpass INFO memory
redis-cli --user monitor --askpass INFO stats
redis-cli --user monitor --askpass INFO clients
redis-cli --user monitor --askpass INFO replication
redis-cli --user monitor --askpass INFO persistence
redis-cli --user monitor --askpass SLOWLOG GET 10
redis-cli --user monitor --askpass LATENCY LATEST
```

| Metric | Field یا روش اندازه‌گیری | تفسیر عملیاتی |
| --- | --- | --- |
| Memory | used_memory, used_memory_rss, maxmemory, mem_not_counted_for_evict | Dataset، RSS واقعی و Buffer خارج از محاسبه را مقایسه کنید. |
| Clients | connected_clients, blocked_clients, rejected_connections | رشد غیرمنتظره می‌تواند Pool Leak یا محدودیت اتصال باشد. |
| Cache hit / miss | keyspace_hits, keyspace_misses | از Delta/Rate بازه استفاده کنید، نه فقط نسبت کل عمر سرویس. |
| Evictions / expiration | evicted_keys, expired_keys | حذف ظرفیت را از انقضای هدفمند TTL تفکیک کنید. |
| Commands/sec | instantaneous_ops_per_sec, total_commands_processed | تغییر بار را با Latency و CPU مقایسه کنید. |
| Replication | master_link_status, connected_slaves, offsets, lag | Link، تعداد Replica و رشد فاصله Offset را بررسی کنید. |
| Persistence | rdb_last_bgsave_status, aof_last_write_status, aof_last_bgrewrite_status | Failure به بررسی دیسک، Permission و Memory نیاز دارد. |
| CPU / host pressure | INFO cpu; host CPU, swap, disk latency | Monitoring سیستم‌عامل را در کنار Redis داشته باشید. |
| Network | total_net_input_bytes, total_net_output_bytes | Rate را محاسبه و Throughput مربوط به Replication را مقایسه کنید. |
| Latency | SLOWLOG, LATENCY LATEST, application p95/p99 | Round Trip سمت Client و اجرای فرمان را جدا اندازه بگیرید. |

```text
Hit Rate  = delta(keyspace_hits) / (delta(keyspace_hits) + delta(keyspace_misses)) * 100
Miss Rate = 100 - Hit Rate
# If no lookups occurred, the ratio is undefined: report no traffic.
# Counter deltas must account for process restarts and counter resets.
```

SLOWLOG زمان اجرای Command را به Microsecond ثبت می‌کند و I/O شبکه/Client را شامل نمی‌شود؛ Log خالی، Latency خوب کاربر را اثبات نمی‌کند. Baseline اجراهای بیش از 10,000 Microsecond را با ظرفیت ۱۲۸ رکورد نگه می‌دارد. latency-monitor-threshold 100 رخداد بیش از ۱۰۰ Millisecond را دنبال می‌کند. Argumentهای Slow Log ممکن است داده حساس داشته باشند؛ Access و Retention را محدود کنید. MONITOR یا KEYS را بررسی روزمره Production شلوغ قرار ندهید.

[زمان‌سنجی و Fieldهای SLOWLOG](https://redis.io/docs/latest/commands/slowlog-get/)

[تشخیص رسمی Latency](https://redis.io/docs/latest/operate/oss_and_stack/management/optimization/latency/)

## ۱۹. Prometheus، Grafana و Alerting

![پایش Redis با Redis Exporter، Prometheus، Grafana و Alertmanager](/assets/img/articles/content/redis-monitoring-architecture.png)

پایش Redis با Redis Exporter، Prometheus، Grafana و Alertmanager

اعداد Dashboard تصویر فقط نمونه‌اند، نه اندازه‌گیری این Deployment. Redis Exporter محلی هر نود را می‌خواند، Prometheus از Endpoint خصوصی /metrics، Scrape می‌کند، Grafana به Prometheus Query می‌زند و Alert Rule از Alertmanager اعلان می‌فرستد. Arrowها جریان داده را نشان می‌دهند؛ Prometheus داده را Pull می‌کند، نه Push ناخواسته دریافت کند. Redis Exporter جزء Third-Party است؛ سازگاری را بررسی و Release تست‌شده آن را مستقل از Redis Pin کنید.

برای Exporter از User ACL اختصاصی منطبق با نسخه و Collector فعال استفاده کنید؛ monitor تشخیصی بالا آگاهانه برای تمام Collectorها کافی نیست. بدون نیاز Key Scan، GET یا EVAL ندهید. نمونه ACL پروژه را بررسی، Collector و Permission غیرضروری را حذف، ACL LOG و Scrape Error را در Staging کنترل و redis_up = 1 را تأیید کنید. Credential را در فایل فقط قابل خواندن Owner یا Secret Mount قرار دهید، نه YAML عمومی Prometheus یا Argument فرآیند.

### تنظیم Scrape در Prometheus

```yaml
# Merge with the existing Prometheus configuration.
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
          node: redis-02
```

روی هر نود یک Exporter با Bind خصوصی یا Collector احراز هویت‌شده اجرا کنید. TCP/9121 فقط از Prometheus برابر 10.10.40.20 مجاز باشد؛ Target دلخواه /scrape و Metric را روی Internet منتشر نکنید. در شبکه حساس Scrape نیز HTTPS احراز هویت‌شده داشته باشد. نود فیزیکی را Label کنید، نه Role ثابت Primary/Replica، چون Failover نقش را عوض می‌کند. پیش از Reload، فایل Merge‌شده را با promtool check config اعتبارسنجی کنید.

```bash
# Run on each Redis node, with its own destination IP:
sudo ufw allow from 10.10.40.20 to 10.10.20.10 port 9121 proto tcp
# Run on the Prometheus host:
promtool check config /etc/prometheus/prometheus.yml
curl -fsS http://10.10.20.10:9121/metrics | grep '^redis_up'
curl -fsS http://10.10.20.11:9121/metrics | grep '^redis_up'
```

### Metricهای Dashboard و مثال PromQL

- Availability: مقدار redis_up و up مربوط به Prometheus؛ سالم‌بودن Process Exporter اتصال Redis را اثبات نمی‌کند.
- Memory/Client/Load: مقادیر redis_memory_used_bytes، redis_memory_max_bytes، redis_connected_clients و redis_commands_processed_total.
- Cache: مقادیر redis_keyspace_hits_total، redis_keyspace_misses_total و redis_evicted_keys_total.
- Replication/Persistence: Role فعلی، تعداد Replica متصل، Link، اختلاف Offset، سن Snapshot و آخرین خطای Persistence.
- Host و Application: CPU، Rate شبکه، Latency و فضای دیسک، Swap، Latency صدک p95/p99 برنامه و نرخ Timeout.

```promql
# Commands per second (per scraped instance)
rate(redis_commands_processed_total{job="redis"}[5m])

# Cache hit percentage; no lookups produce no meaningful ratio
100 * rate(redis_keyspace_hits_total{job="redis"}[5m]) /
(rate(redis_keyspace_hits_total{job="redis"}[5m]) + rate(redis_keyspace_misses_total{job="redis"}[5m]))

# Evictions per second
rate(redis_evicted_keys_total{job="redis"}[5m])

# Redis connectivity alert condition
redis_up{job="redis"} == 0
```

نام، Unit و Label Metric را در /metrics نسخه Pin‌شده تأیید کنید. برای Redis غیرقابل دسترس، Target Scrape غایب، Replica قطع، رشد Offset Gap، Quorum کم، خطای Persistence، Backup قدیمی و فشار پایدار Memory/Latency هشدار بدهید. مثلاً هشدار اتصال پس از ۲ دقیقه و Warning حافظه نزدیک ۸۰ درصد نقطه شروع است؛ با SLO واقعی تنظیم کنید. خطای حیاتی Persistence بررسی فوری می‌خواهد. اعلان را با Owner و Runbook تست کنید؛ صرف Dashboard کافی نیست.

[تنظیمات، ACL و Metricهای مرجع پروژه Redis Exporter](https://github.com/oliver006/redis_exporter)

[تنظیم Scrape رسمی Prometheus](https://prometheus.io/docs/prometheus/latest/configuration/configuration/)

## ۲۰. Backup و Restore

Replication جای Backup نیست. حذف اشتباه، Bug برنامه و Write مخرب به تمام Replicaها منتقل می‌شود. Backup تاریخ‌دار و قابل بازیابی را خارج از Failure Domain Redis، رمزنگاری‌شده و با Access محدود و Retention متناسب با RPO نگه دارید. Backup Replica می‌تواند بار Primary را کم کند اما پیش از Snapshot باید Sync کامل و تازه داشته باشد.

```text
Redis Primary -> Redis Replica -> Completed RDB / Consistent AOF Backup
                                         |
                                         v
                         Encrypted Remote Backup Storage
                                         |
                                         v
                            Isolated Restore Test
```

### مثال: Export یک RDB تازه از Replica

روی Replica، Link سالم و Full Sync تکمیل‌شده را تأیید کنید. redis-cli --rdb با Authentication ادمین، RDB را از Replication Protocol دریافت و پس از انتقال خارج می‌شود؛ بار Snapshot/Full Sync آن را حساب کنید. مسیر Backup محلی فقط برای root بسازید و در Shell اپراتور با umask محدود اجرا کنید. در TLS، Flag گواهی بخش قبل را اضافه کنید.

```bash
redis-cli --user monitor --askpass INFO replication
sudo install -d -o root -g root -m 700 /var/backups/redis
umask 077
backup_stamp=$(date -u +%Y%m%dT%H%M%SZ)
backup_file="/var/backups/redis/replica-${backup_stamp}.rdb"
sudo install -o root -g root -m 600 /dev/null "$backup_file"
sudo redis-cli -h 127.0.0.1 -p 6379 --user admin --askpass --rdb "$backup_file"
sudo chmod 600 "$backup_file"
sudo redis-check-rdb "$backup_file"
sudo sha256sum "$backup_file"
```

خروجی مورد انتظار Transfer کامل و Validation موفق redis-check-rdb است؛ Checksum خرابی بعدی را تشخیص می‌دهد اما کامل‌بودن Recovery را اثبات نمی‌کند. Artifact را با سیستم Backup Remote رمزنگاری‌شده سازمان منتقل و Checksum مقصد را تأیید کنید. این مثال محلی به‌تنهایی Off-Host Backup نیست. برای Automation از Secret Mount/Service Identity استفاده کنید، نه --askpass تعاملی؛ Log بدون Credential نگه دارید.

### Backup سازگار AOF و معیار پذیرش Restore

Backup AOF فعلی باید Manifest و همه Base/Incremental File ارجاع‌شده را داشته باشد. کپی ساده فایل زنده هنگام Rewrite ممکن است File را جا بیندازد یا وضعیت ناسازگار بگیرد. از فرآیند هماهنگ جلوگیری از تغییر File Set/Rewrite یا Snapshot اتمیک Storage/Filesystem با روش Consistency مستند Redis استفاده کنید. Replica اختصاصی Backup که تمیز متوقف شده نیز File Set سازگار می‌دهد؛ Resync و اثر Availability را برنامه‌ریزی کنید.

- Restore روی Host تازه و ایزوله با Redis/Module نسخه تأییدشده؛ Checksum و سازگاری Config را بررسی کنید.
- برای Restore فقط RDB، dump.rdb بازیابی‌شده را ابتدا با appendonly no بارگذاری کنید؛ AOF قبلی نباید آن را Override کند. بعداً AOF را با فرآیند زنده بخش ۱۲ فعال کنید.
- برای Restore AOF، Manifest/File Set کامل را زیر appenddirname با Ownership صحیح قرار دهید؛ با redis-check-aof همان نسخه بررسی کنید.
- تا تأیید نمونه کلید، TTL، Count، Loading Log و Invariant کسب‌وکار، Application قطع باشد. --fix را روی تنها کپی Backup اجرا نکنید.
- زمان واقعی Restore، Loss احتمالی، سن Backup Remote و دسترسی کلید رمزنگاری را اندازه بگیرید؛ Backup قبل از تغییر برای Rollback حفظ شود.

[Modeهای redis-cli شامل Export RDB و Authentication](https://redis.io/docs/latest/develop/tools/cli/)

[Timestamp تکمیل Snapshot با LASTSAVE](https://redis.io/docs/latest/commands/lastsave/)

## ۲۱. Memory Management و Eviction Policy

maxmemory حافظه محاسبه‌شده برای Eviction را محدود می‌کند، نه کل RSS فرآیند یا سقف سخت RAM Host. Buffer مربوط به Replication/AOF، Fragmentation، Module، OS و Copy-on-Write زمان Fork به ظرفیت اضافی نیاز دارند. maxmemory 4gb روی Host دارای 8 GiB فقط مثال اولیه است، نه قانون عمومی ۵۰ درصد. Peak RSS و Copy-on-Write را زیر Rewrite و Full Sync واقعی اندازه بگیرید و پیش از Swap یا OOM Killer هشدار بدهید.

| Policy | رفتار و کاربرد |
| --- | --- |
| noeviction | Write افزاینده حافظه را در سقف رد می‌کند؛ کلید Session/Job حفظ می‌شود اما مدیریت OOM لازم است. |
| allkeys-lru | حذف تقریبی کلیدهای کمتر استفاده‌شده اخیراً از تمام کلیدها؛ شروع مناسب Cache عمومی. |
| allkeys-lfu | حذف تقریبی کلیدهای کمتر استفاده‌شده از نظر دفعات؛ برای Hot Working Set پایدار تست کنید. |
| volatile-lru | LRU فقط روی کلید دارای TTL؛ بدون Candidate رفتار شبیه noeviction دارد. |
| volatile-ttl | حذف کلید دارای TTL با کوتاه‌ترین عمر باقی‌مانده؛ وقتی TTL بیانگر ارزش داده است مفید است. |

### پروفایل عملی Cache مستقل

```text
maxmemory 4gb
maxmemory-policy allkeys-lru
```

برای Instance فقط Cache از allkeys-lru، TTL صریح و کنترل Stampede شروع کنید؛ قبل از آزمایش LFU، Hit Rate، Eviction و بار Fallback Database را مقایسه کنید. TTL تازگی داده و Eviction ظرفیت را کنترل می‌کند. این پروفایل را با Job، Lock یا Session دارای الزام ماندگاری زیر فشار حافظه مشترک نکنید. تغییر Policy به Reset داده نیاز ندارد اما می‌تواند بلافاصله انتخاب کلید Evicted را عوض کند.

Replica معمولاً هنگام Replication، maxmemory را نادیده می‌گیرد و Eviction Primary را اعمال می‌کند؛ ظرفیت باید کل Dataset کپی‌شده و Buffer را پوشش دهد. برای Promotion احتمالی maxmemory/Policy مناسب روی Replica نیز تنظیم کنید. replica-ignore-maxmemory no این رفتار را تغییر می‌دهد و راه‌حل عمومی Replica کم‌ظرفیت نیست.

[Policyهای فعلی Eviction و محاسبه Memory](https://redis.io/docs/latest/develop/reference/eviction/)

### پیش‌نیاز Memory و سرویس در Linux

```bash
sysctl vm.overcommit_memory net.core.somaxconn
systemctl show redis-server -p LimitNOFILE
cat /sys/kernel/mm/transparent_hugepage/enabled
# On a dedicated Redis host, persist the official overcommit recommendation:
printf 'vm.overcommit_memory = 1
' | sudo tee /etc/sysctl.d/99-redis.conf
sudo sysctl -p /etc/sysctl.d/99-redis.conf
```

Redis برای کاهش Fork Failure مقدار vm.overcommit_memory=1 را توصیه می‌کند؛ این Policy کل Host را تغییر می‌دهد و روی Host مشترک باید هماهنگ شود. در Redis 8.10.2 پیش‌فرض disable-thp yes در صورت نیاز اثر نامناسب THP را برای Process Redis محدود می‌کند؛ Config اجراشده و Latency را بررسی کنید، نه اینکه Script قدیمی غیرفعال‌کردن سراسری THP را کورکورانه کپی کنید. File Limit سرویس و Listen Backlog را با بار Client اندازه‌گیری‌شده تطبیق دهید. Swap را مطابق Policy Host برای وضعیت اضطراری ظرفیت Provision کنید اما Swap واقعی Redis را Incident فوری Latency بدانید، نه حاشیه ظرفیت روزمره.

[پیش‌نیازهای رسمی Administration در Linux](https://redis.io/docs/latest/operate/oss_and_stack/management/admin/)

## ۲۲. Runbook عملیاتی Troubleshooting

فرمان Host را محلی روی نود مشکل‌دار اجرا کنید؛ فرمان داده از Named User مشخص‌شده استفاده می‌کند. در TLS به تمام redis-cliها Flag مربوط به CA/Client معتبر اضافه کنید. قبل از Repair، Log و Config فعلی را حفظ کنید. خروجی‌ها Indicator موفقیت/خطای نمونه‌اند و متن دقیق بسته‌ها ممکن است تفاوت داشته باشد.

### Redis Service Down

Root Cause: Directive یا فایل ACL نامعتبر، فایل غیرقابل خواندن، Bind Address غایب، Port Conflict، خطای دیسک یا OOM Killer. فرمان تشخیص:

```bash
systemctl status redis-server --no-pager
sudo journalctl -u redis-server -n 150 --no-pager
sudo tail -n 100 /var/log/redis/redis-server.log
sudo journalctl -k -n 150 --no-pager
sudo -u redis test -r /etc/redis/users.acl
df -h /var/lib/redis
ip -br address
```

Expected Output: سرویس خراب failed/inactive و Log علت مشخص دارد؛ پس از اصلاح active (running)، PONG و Persistence/Replication سالم انتظار می‌رود. Resolution: Directive/ACL خراب، Ownership، IP خصوصی غایب، Conflict یا ظرفیت را اصلاح و طبق Maintenance Plan Restart کنید. بدون خواندن Redis Log Restart تکراری نکنید و برای دورزدن Permission سرویس را root اجرا نکنید.

### Port 6379 Not Listening یا اتصال Remote رد می‌شود

```bash
sudo ss -lntp | grep ':6379'
systemctl cat redis-server
ip -br address
sudo ufw status numbered
# From an authorized application host:
redis-cli -h 10.10.20.10 -p 6379 --user app --askpass PING
```

Root Cause: سرویس قطع، Bind فقط Loopback، Config/Port اشتباه، IP غایب، Route/Firewall مسدود یا Client بدون TLS روی Listener فقط TLS. Expected Output: LISTEN روی 127.0.0.1 و 10.10.20.10 در Primary و PONG از Client مجاز. Timeout معمولاً Drop ترافیک و Refused معمولاً نبود Listener یا Reject فعال است. Resolution: ExecStart و فایل ویرایش‌شده را مقایسه، Bind خصوصی و Source Rule دقیق را اصلاح و Mode صحیح TLS را تست کنید؛ bind 0.0.0.0 همراه Allow عمومی راه‌حل نیست.

### Authentication Error و خطای ACL

```text
NOAUTH Authentication required.
WRONGPASS invalid username-password pair or user is disabled.
NOPERM ...
```

```bash
redis-cli --user admin --askpass ACL WHOAMI
redis-cli --user admin --askpass ACL GETUSER app
redis-cli --user admin --askpass ACL LOG 10
redis-cli --user admin --askpass ACL DRYRUN app GET cache:probe
```

Root Cause: AUTH غایب، Username/Secret Rotate‌شده اشتباه، User غیرفعال، Command/Key/Channel غیرمجاز یا تغییر ACL ذخیره‌نشده. Expected Output: WHOAMI برابر admin، DRYRUN مجاز برابر OK و Activity ردشده در ACL LOG دیده می‌شود. Resolution: Username/Secret Client و ACL مؤثر با فایل مدیریت‌شده را تطبیق و فقط Permission لازم غایب را اضافه کنید. NOAUTH، WRONGPASS و NOPERM علت متفاوت دارند؛ با default on nopass خطا را پنهان نکنید.

### Replica Disconnected یا Sync متوقف

```bash
redis-cli --user monitor --askpass INFO replication
sudo journalctl -u redis-server -n 150 --no-pager
sudo tail -n 100 /var/log/redis/redis-server.log
redis-cli --user admin --askpass CONFIG GET replicaof masteruser
df -h /var/lib/redis
```

Root Cause: Route/Firewall، masterauth/masteruser اشتباه، Permission غایب PSYNC/REPLCONF، TLS/CA ناسازگار، Backlog، RAM یا دیسک ناکافی. Expected Output: master_link_status:up، master_sync_in_progress:0 و master_last_io_seconds_ago کم/پایدار پس از تکمیل؛ Primary باید Replica را online ببیند. Resolution: Log هر دو سمت را بخوانید، مشکل Credential/شبکه/گواهی/فضا را اصلاح، منتظر Sync کامل و Offsetها را مقایسه کنید. Full Sync مکرر می‌تواند Backlog ناکافی یا قطعی ادامه‌دار باشد. replica-serve-stale-data no تا Recovery ممکن است MASTERDOWN بدهد؛ Write Replica را اجباری فعال نکنید.

### Memory Full و خطای OOM

```bash
redis-cli --user monitor --askpass INFO memory
redis-cli --user monitor --askpass INFO stats
redis-cli --user admin --askpass CONFIG GET maxmemory maxmemory-policy
free -h
sudo journalctl -k -n 100 --no-pager
```

Root Cause: رشد Dataset، TTL غایب، رد Write افزاینده با noeviction، Policy volatile بدون Candidate دارای TTL، Fragmentation/فضای Fork یا Replica کوچک. Expected Output خطا: OOM command not allowed when used memory > maxmemory یا شاهد OOM کرنل؛ پس از اصلاح حاشیه ظرفیت پایدار و Write لازم موفق باشد. Resolution: رشد را با Sampling محدود شناسایی، فقط کلید قدیمی تأییدشده را با UNLINK حذف، TTL اضافه و Policy Cache مستقل یا ظرفیت اندازه‌گیری‌شده تنظیم کنید. FLUSHALL درمان روزمره ظرفیت نیست و maxmemory نباید از ایمنی Host فراتر رود.

### Slow Redis و Timeout

```bash
redis-cli --user monitor --askpass SLOWLOG GET 20
redis-cli --user monitor --askpass LATENCY LATEST
redis-cli --user monitor --askpass INFO commandstats
redis-cli --user monitor --askpass INFO clients
redis-cli --user monitor --askpass INFO persistence
# From an authorized client; stop with Ctrl+C after a short sample:
redis-cli -h 10.10.20.10 --user app --askpass --latency
```

Root Cause: Command/Script بزرگ و سنگین، Hot Key، CPU Saturation، Swap، وقفه Fork/Rewrite، Storage کند، شبکه یا Connection Churn. Expected Output: SLOWLOG اجرای Microsecond بالا، Latency Event وقفه سرور و --latency رفت‌وبرگشت PING را نشان می‌دهد. SLOWLOG خالی همراه PING کند، علت Transport/Host/Client را نیز مطرح می‌کند. Resolution: عملیات محدود/SCAN به‌جای KEYS، Value کوچک‌تر، Pool و Batching/Pipelining با اندازه محدود؛ فشار Memory/Disk/Network را رفع و p95/p99 قبل/بعد را مقایسه کنید.

### خطای Persistence و MISCONF

```bash
redis-cli --user monitor --askpass INFO persistence
df -h /var/lib/redis
df -i /var/lib/redis
sudo -u redis test -w /var/lib/redis
sudo tail -n 100 /var/log/redis/redis-server.log
```

Root Cause: دیسک/Inode پر، Mount فقط‌خواندنی، Permission، I/O Error یا Fork ناموفق. Expected Output خطا: rdb_last_bgsave_status:err یا aof_last_write_status:err؛ موفقیت ok و زمان Save جدیدتر دارد. Resolution: Storage/Permission را اصلاح و Snapshot/Rewrite تکمیل‌شده را تأیید کنید. stop-writes-on-bgsave-error را صرفاً غیرفعال و تنها کپی AOF را Repair نکنید؛ Artifact را حفظ و Restore را ابتدا بررسی کنید.

## ۲۳. Production Checklist و معیار پذیرش

- [ ] Redis روی Private Network تفکیک‌شده است.
- [ ] Port 6379 روی Internet و Subnet غیرمجاز بسته است.
- [ ] ACL با Named User مستقر، default غیرفعال و عملیات Denied تست شده است.
- [ ] Authentication قوی، نگهداری Secret و Rotation تنظیم شده است.
- [ ] در نیاز به رمزنگاری، TLS و بررسی Certificate تست شده است.
- [ ] Persistence و مدیریت خطای RDB/AOF با RPO تطبیق دارد.
- [ ] maxmemory، ظرفیت Fork/Buffer و ظرفیت Replica بررسی شده است.
- [ ] Eviction Policy مشخص و Job/Session حیاتی از Cache قابل Evict جداست.
- [ ] Replica فعال و Sync اولیه کامل است.
- [ ] Replication Status، Offset و تست Write/Read بررسی شده است.
- [ ] Backup Remote تاریخ‌دار وجود دارد و Restore ایزوله موفق است.
- [ ] Monitoring هر دو نود Redis و Hostها فعال است.
- [ ] Alerting به Owner می‌رسد و Runbook تست‌شده دارد.
- [ ] Firewall و Permission حساب سرویس بررسی شده است.
- [ ] Configuration Backup و ACL Backup امن گرفته شده است.
- [ ] آخرین Stable Patch، سازگاری Ubuntu LTS و Client/Module دوباره بررسی شده است.
- [ ] نیاز HA با Sentinel/Cluster تست‌شده پوشش دارد؛ Replication تنها، HA معرفی نشده است.
- [ ] فرآیند Maintenance، Failover، Rollback و Recovery ثبت شده است.

## جمع‌بندی؛ پذیرش استقرار

پذیرش استقرار به شاهد نیاز دارد: نسخه نصب‌شده، Listener مجاز، Access احراز هویت‌شده و Denied، Sync کامل، سلامت Persistence، Backup Remote تأییدشده و زمان Recovery را ثبت کنید. Config با Syntax درست بدون این بررسی‌ها برای اعلام تکمیل Production کافی نیست.

استقرار با Primary و Replica خصوصی، حساب ACL مشخص، Persistence بررسی‌شده و Memory محدود شروع می‌شود. Sentinel، Discovery و Failover خودکار و Cluster، Sharding اضافه می‌کند. Eviction مربوط به Cache را از Session/Job حیاتی جدا، Replication را ناهمزمان و Recovery را از Backup مستقل اثبات کنید. در هر Rollout، Patch Stable فعلی Redis و Ubuntu LTS پشتیبانی‌شده را دوباره بررسی کنید.

## پرسش‌های متداول Redis در Production

### آیا apt install redis-server آخرین Redis را نصب می‌کند؟

الزاماً خیر؛ مخزن Distribution ممکن است قدیمی‌تر باشد. مخزن رسمی Redis، apt-cache policy و redis-server --version را بررسی کنید.

### آیا requirepass حذف شده است؟

خیر؛ برای سازگاری User پیش‌فرض ACL باقی است. این طراحی Production از Named User با Least Privilege استفاده می‌کند.

### آیا Replication خودکار Failover انجام می‌دهد؟

Replication مستقل خیر. از Sentinel یا Redis Cluster با Topology مناسب و Client سازگار استفاده کنید.

### آیا Replica داده قدیمی برمی‌گرداند؟

بله؛ Replication ناهمزمان است. داده حساس به تازگی را از Primary بخوانید و Offset و سلامت Link را پایش کنید.

### آیا WAIT ثبت Write روی دیسک را تضمین می‌کند؟

خیر؛ WAIT تأیید Replication برای Write قبلی همان Connection را بررسی می‌کند. WAITAOF به تأیید Flush تنظیم‌شده AOF مربوط است.

### کدام Policy برای شروع Cache مناسب است؟

allkeys-lru با maxmemory صریح و TTL برنامه. Session، Queue و Lock حیاتی را از Cache دارای Eviction جدا کنید.

### آیا Replica همان Backup است؟

خیر؛ Delete و Write خراب نیز Replicate می‌شوند. Backup Remote تاریخ‌دار و تست منظم Recovery روی Host ایزوله لازم است.

### برای Deployment مقاوم چند Sentinel لازم است؟

حداقل سه در Failure Domain مستقل. با سه Sentinel، Quorum معمولاً 2 است و Failover به Majority انتخاب نیز نیاز دارد.

## منابع رسمی و Templateهای استقرار

در بخش‌های مرتبط به مستندات رسمی Redis لینک داده شده است. پیش از هر Release نسخه را دوباره بررسی کنید. Fragmentهای Download هیچ Secret واقعی ندارند. آن‌ها را در Staging بررسی‌شده به‌کار ببرید، Marker اجباری Secret را جایگزین، مسیر Module بسته را حفظ و سرویس مقصد واقعی را پیش از Production تأیید کنید.

[Command Reference نسخه Redis 8.10](https://redis.io/docs/latest/commands/redis-8-10-commands/)

[Release Notes مربوط به Redis Open Source 8.10](https://redis.io/docs/latest/operate/oss_and_stack/stack-with-enterprise/release-notes/redisce/redisos-8.10-release-notes/)

[راهنمای رسمی سیستم‌عامل و Administration](https://redis.io/docs/latest/operate/oss_and_stack/management/admin/)

[Fragment مربوط به Primary](/downloads/redis-installation-configuration-replication/primary-baseline.conf)

[Fragment مربوط به Replica؛ جایگزینی Secret لازم است](/downloads/redis-installation-configuration-replication/replica-baseline.conf)

[Script ایجاد ACL برای نود جدید](/downloads/redis-installation-configuration-replication/bootstrap-acl.sh)

[Overlay مربوط به TLS Listener](/downloads/redis-installation-configuration-replication/tls-overlay.conf)

[پروفایل Memory فقط Cache](/downloads/redis-installation-configuration-replication/cache-memory-overlay.conf)

[Template مانیتور Sentinel؛ استقرار کامل Sentinel نیست](/downloads/redis-installation-configuration-replication/sentinel-monitor-template.conf)

[Fragment مربوط به Scrape در Prometheus](/downloads/redis-installation-configuration-replication/prometheus-scrape.yml)
