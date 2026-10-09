# آموزش نصب، راه‌اندازی و پیکربندی MongoDB در محیط Production

راهنمای عملیاتی برای SysAdmin، DevOps، Backend و Infrastructure Engineer؛ شاخه Stable رسمی فعلی 9.0 است؛ Release Notes نسخه 9.0.2 را منتشرشده و 9.0.3 را Upcoming معرفی می‌کند. جدیدترین Patch امضاشده موجود در مخزن رسمی انتخابی را نصب کنید؛ نسخه Upcoming هدف نصب نیست.

[فهرست رسمی نسخه‌های Stable](https://www.mongodb.com/docs/manual/release-notes/)

[Release Notes و Patchهای MongoDB 9.0](https://www.mongodb.com/docs/manual/release-notes/9.0/)

| سیستم‌عامل مقصد | شاخه Community در این راهنما | پیش‌فرض بسته |
| --- | --- | --- |
| Ubuntu 22.04 / 24.04 x86_64 | 9.0 | mongodb؛ /var/lib/mongodb |
| Debian 12 x86_64 | 8.0؛ شاخه 9.x روی Debian 12 پشتیبانی نمی‌شود | mongodb؛ /var/lib/mongodb |
| RHEL / Rocky / AlmaLinux 8 یا 9 x86_64 | 9.0 | mongod؛ /var/lib/mongo |

[ماتریس رسمی پشتیبانی پلتفرم‌های Community](https://www.mongodb.com/docs/community-platform-support/)

از سرور تازه و پشتیبانی‌شده، دسترسی sudo و Bash استفاده کنید. بلوک Bash روی Linux و بلوک JavaScript داخل mongosh اجرا می‌شود. مقادیر داخل <> جایگزین‌شونده‌اند و Credential واقعی نیستند: <HOSTNAME>، <MONGODB-IP>، <ADMIN-USER>، <APP-USER>، <STRONG-PASSWORD>، <REPLICA-NAME>. نام‌های mongoAdmin، appuser، appdb و rs0 مثال قابل جایگزینی هستند. IPهای نمونه خصوصی‌اند و باید با شبکه شما تطبیق داده شوند.

یک مسیر انتخاب کنید: بخش‌های ۵ تا ۱۴ برای نصب و ایمن‌سازی Standalone و بخش‌های ۱۵ و ۱۶ برای Replica Set تازه با سه Node هستند. روی دیتابیس موجود مسیر Fresh Cluster را بدون برنامه اجرا نکنید. طراحی Production به ظرفیت‌سنجی، هدف بازیابی، کنترل تغییر و آزمون موفق Restore نیز نیاز دارد.

## ۱. MongoDB چیست؟

MongoDB یک Document Database از خانواده NoSQL است. Database مجموعه‌ای از Collectionها و هر Collection شامل Documentهای BSON است؛ BSON فرمت باینری با Typeهای گسترده‌تر از JSON است. Replica Set نسخه‌های داده را برای دسترس‌پذیری نگه می‌دارد و Sharding داده را برای مقیاس افقی میان Shardها تقسیم می‌کند. این راهنما Replica Set مستقر می‌کند، نه Sharded Cluster.

در SQL معمولاً Table، Row و Join محور طراحی‌اند؛ در MongoDB مدل Document بر اساس الگوی دسترسی برنامه طراحی می‌شود. انعطاف Document نیاز به Validation و Index را حذف نمی‌کند. MongoDB از Transaction پشتیبانی می‌کند، اما Transaction چندسندی جای مدل‌سازی درست را نمی‌گیرد و در Standalone در دسترس نیست.

[Database و Collection در مستندات رسمی](https://www.mongodb.com/docs/manual/core/databases-and-collections/)

## ۲. پیش‌نیاز سرور

| منبع | مبنای برنامه‌ریزی |
| --- | --- |
| CPU | معماری ۶۴ بیتی پشتیبانی‌شده و AVX روی x86_64. بودجه اولیه ۴ تا ۸ vCPU برای هر Node مثال ظرفیت‌سنجی است، نه حداقل رسمی. |
| RAM | برای سرویس اختصاصی متوسط، ظرفیت‌سنجی را حدود ۱۶ تا ۳۲ GiB در هر Node شروع کنید؛ Working Set، Indexها و File Cache تعیین‌کننده‌اند. |
| Disk و File System | SSD/NVMe سازمانی؛ XFS برای WiredTiger توصیه می‌شود. فضای Index، Journal، Oplog، رشد و Restore را لحاظ کنید. |
| شبکه و DNS | شبکه خصوصی کم‌تأخیر، نام Node پایدار و Resolveشدنی، دسترسی Driver به همه Memberهای معرفی‌شده. |
| زمان | از chrony یا سرویس زمان توزیع استفاده کنید؛ Synchronization واقعی را بررسی کنید، نه صرفاً Enabled بودن سرویس. |
| Swap | از Swap مستمر جلوگیری کنید. swappiness و رفتار OOM را با پلتفرم و Workload ارزیابی کنید؛ Swap سرور زنده تحت فشار را ناگهانی خاموش نکنید. |

Data را روی Block Storage اختصاصی و قابل اعتماد قرار دهید: /var/lib/mongodb در Ubuntu/Debian و /var/lib/mongo در نصب RPM. مسیر معمول Log برابر /var/log/mongodb است. Mount جداگانه در همان dbPath از تغییر ناخواسته مسیر جلوگیری می‌کند. پیش از اولین Start آن را Mount و وابستگی RequiresMountsFor در systemd تعریف کنید تا فقدان Volume باعث ساخت دیتابیس جدید روی Root Disk نشود. دیسک دارای داده را Format نکنید.

[نکات رسمی سخت‌افزار، Storage و پلتفرم Production](https://www.mongodb.com/docs/manual/administration/production-notes/)

### محدودیت سرویس و وابستگی Mount

```bash
sudo systemctl edit mongod
# Add the following drop-in; use /var/lib/mongo on RPM systems.
```

```ini
[Unit]
RequiresMountsFor=/var/lib/mongodb

[Service]
LimitNOFILE=64000
LimitNPROC=64000
```

```bash
sudo systemctl daemon-reload
sudo systemctl show mongod -p LimitNOFILE -p LimitNPROC
ulimit -n
```

ulimit در Shell فقط همان Shell را تغییر می‌دهد و سرویس systemd در حال اجرا را تغییر نمی‌دهد. پس از Restart محدودیت مؤثر را بررسی کنید. در نسخه 8.0 به بعد روی x86_64/ARM64 راهنمای جدید TCMalloc را دنبال کنید: THP را با تنظیمات defrag مستند فعال کنید؛ توصیه قدیمی خاموش‌کردن THP وابسته به نسخه است. پیش از تغییر Kernel نکات رسمی سازگاری را بررسی کنید.

[محدودیت منابع UNIX](https://www.mongodb.com/docs/manual/reference/ulimit/)

[تنظیم TCMalloc و THP برای MongoDB 8.0 و جدیدتر](https://www.mongodb.com/docs/manual/administration/tcmalloc-performance/)

## ۳. نمای معماری

![معماری اتصال برنامه و Driver با احراز هویت روی TCP 27017 و ساختار Documentهای BSON در MongoDB](/assets/img/articles/content/mongodb-architecture.png)

```text
Application
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
 +-----------+-----------+-----------+
```

Driver همه Memberها را کشف می‌کند و عملیات را با توجه به Read Preference و Topology هدایت می‌کند. Replica Set به Load Balancer عمومی HTTP یا Round-Robin نیاز ندارد و چنین واسطه‌ای می‌تواند Topology را پنهان کند. Write معمولاً به Primary می‌رود؛ Secondaryها Replication انجام می‌دهند و با انتخاب صریح می‌توانند Read را با ملاحظات Consistency پاسخ دهند.

[معماری رسمی Replication](https://www.mongodb.com/docs/manual/replication/)

## ۴. بررسی پیش از نصب

```bash
hostnamectl
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
getent hosts mongo01 mongo02 mongo03
```

ID و Version سیستم‌عامل و معماری را با ماتریس تطبیق دهید؛ ویژگی CPU و Kernel توزیع را بررسی کنید. RAM آزاد، فعالیت Swap، ظرفیت Disk، File System و Mount پایدار را کنترل کنید. Interface و Route خصوصی، Hostname یکتا، DNS یکسان در Client و Node و ساعت هماهنگ را تأیید کنید. این بررسی‌ها ظرفیت و ارتباط را مشخص می‌کنند، نه سلامت دیتابیس را.

## ۵. نصب MongoDB روی Ubuntu 22.04 / 24.04

این مسیر نصب تازه از Community 9.0 و مخزن رسمی repo.mongodb.org استفاده می‌کند. ابتدا بسته‌ها و Sourceهای MongoDB موجود را بررسی کنید؛ بسته mongodb توزیع، mongodb-org و Enterprise را مخلوط نکنید. در خطای GPG یا دسترسی مخزن، علت را رفع کنید و بررسی امضا را خاموش نکنید.

```bash
sudo apt update
sudo apt install -y gnupg curl ca-certificates
curl -fsSL https://pgp.mongodb.com/server-9.asc -o /tmp/mongodb-server-9.asc
sudo gpg --batch --yes --dearmor \
  -o /usr/share/keyrings/mongodb-server-9.gpg /tmp/mongodb-server-9.asc
sudo chmod 644 /usr/share/keyrings/mongodb-server-9.gpg
```

### Ubuntu 24.04: انتخاب Noble

```bash
echo 'deb [arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-9.gpg] https://repo.mongodb.org/apt/ubuntu noble/mongodb-org/9.0 multiverse' \
  | sudo tee /etc/apt/sources.list.d/mongodb-org-9.0.list
```

### Ubuntu 22.04: انتخاب Jammy به جای Noble

```bash
echo 'deb [arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-9.gpg] https://repo.mongodb.org/apt/ubuntu jammy/mongodb-org/9.0 multiverse' \
  | sudo tee /etc/apt/sources.list.d/mongodb-org-9.0.list
```

فقط بلوک Repository متناظر با سیستم‌عامل را اجرا کنید. نام Keyring محلی در این راهنما server-9.gpg انتخاب شده است؛ signed-by و فایل Importشده باید دقیقاً یکسان باشند.

```bash
sudo apt update
apt-cache policy mongodb-org mongodb-org-server
sudo apt install -y mongodb-org
sudo systemctl enable --now mongod
sudo systemctl status mongod --no-pager
mongod --version
mongosh --version
mongosh --host 127.0.0.1 --port 27017
```

[راهنمای رسمی نصب Community روی Ubuntu](https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=ubuntu&linux-method=pkg)

## ۶. نصب MongoDB روی Debian 12

Debian 12 Bookworm از شاخه پشتیبانی‌شده Community 8.0 استفاده می‌کند. Bookworm را به مخزن Trixie/9.0 وصل نکنید؛ شاخه 9.x فقط Debian 13 را فهرست کرده است. ارتقای OS و دیتابیس دو تغییر کنترل‌شده جدا هستند. این فرمان جدیدترین Patch موجود 8.0 را نصب می‌کند، نه جدیدترین Major در همه پلتفرم‌ها.

```bash
sudo apt update
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
mongosh
```

[راهنمای رسمی Debian 12 برای Community 8.0](https://www.mongodb.com/docs/v8.0/tutorial/install-mongodb-on-debian/)

## ۷. نصب روی RHEL / Rocky / AlmaLinux

فرمان زیر برای خانواده سازگار با RHEL نسخه اصلی 9 با معماری x86_64 و Community 9.0 است. برای نسخه اصلی 8 فقط redhat/9 را در baseurl به redhat/8 تغییر دهید. Major و معماری پشتیبانی‌شده دقیق را انتخاب کنید؛ مخزن RPM را در Host مبتنی بر APT قرار ندهید. gpgcheck=1 را حفظ کنید.

```bash
sudo tee /etc/yum.repos.d/mongodb-org.repo >/dev/null <<'EOF'
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
mongosh
```

بسته RPM با حساب mongod و مسیر Data پیش‌فرض /var/lib/mongo اجرا می‌شود. SELinux را Enforcing نگه دارید و Policy مستند MongoDB را به کار ببرید؛ تغییر مسیر Data/Log یا Port ممکن است Label و Rule جدید نیاز داشته باشد. پیش از تغییر Permission، Denial را تشخیص دهید. setenforce 0 راه‌حل Production نیست.

```bash
getenforce
sudo ausearch -m AVC -ts recent
sudo ls -Zd /var/lib/mongo /var/log/mongodb
```

[نصب رسمی Community روی RHEL و Policy مربوط به SELinux](https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=rhel&linux-method=pkg)

## ۸. پیکربندی MongoDB

فایل /etc/mongod.conf را با Space در YAML و بدون Tab ویرایش کنید. ابتدا Backup بگیرید و Fragmentهای بعدی را در بلوک Top-Level موجود ادغام کنید؛ تکرار کلید net یا security روش درستی نیست. نمونه زیر Bootstrap محدود به Localhost برای Ubuntu/Debian است. در RPM مسیر dbPath: /var/lib/mongo و تنظیم Process موردنیاز بسته را حفظ کنید.

```bash
sudo cp -a /etc/mongod.conf /etc/mongod.conf.before-hardening
sudoedit /etc/mongod.conf
```

```yaml
storage:
  dbPath: /var/lib/mongodb

systemLog:
  destination: file
  logAppend: true
  path: /var/log/mongodb/mongod.log

net:
  port: 27017
  bindIp: 127.0.0.1

processManagement:
  timeZoneInfo: /usr/share/zoneinfo
```

| پارامتر | کاربرد |
| --- | --- |
| storage.dbPath | مسیر فایل‌های دیتابیس؛ تغییر آن داده را مهاجرت نمی‌دهد. |
| systemLog.destination / path | ثبت Log در فایل مشخص‌شده. |
| systemLog.logAppend | پس از Restart به Log قبلی اضافه می‌کند. |
| net.port / bindIp | Port Listener و Interfaceهای محلی سرور؛ نه فهرست IP مجاز Client. |
| processManagement.timeZoneInfo | دیتابیس Timezone مورد استفاده عملیات مرتبط با منطقه زمانی. |
| security / replication / net.tls | کنترل دسترسی، عضویت Cluster و رمزنگاری ارتباط که در ادامه اضافه می‌شوند. |

WiredTiger موتور Storage پیش‌فرض است. از تنظیم قدیمی journal.enabled و مقدار دلخواه cacheSizeGB اجتناب کنید. برای OS و File Cache حافظه باقی بگذارید؛ تنظیم مؤثر را با getCmdLineOpts در Session مدیریتی مجاز بررسی کنید. پس از تغییر فایل Startup، Restart و بلافاصله Log را بررسی کنید.

[پارامترهای رسمی پیکربندی mongod](https://www.mongodb.com/docs/manual/reference/configuration-options/)

## ۹. احراز هویت MongoDB

فقط در Bootstrap مسیر Standalone، وقتی Authorization هنوز خاموش است bindIp را 127.0.0.1 نگه دارید. به‌صورت محلی وصل شوید و اولین مدیر را بسازید. passwordPrompt() مقدار <STRONG-PASSWORD> را بدون درج در Command History می‌گیرد؛ pwd: "<STRONG-PASSWORD>" فقط نمایش Placeholder است و برای مدیریت Secret مناسب نیست.

```bash
mongosh --host 127.0.0.1 --port 27017
```

```javascript
use admin
db.createUser({
  user: "mongoAdmin",
  pwd: passwordPrompt(),
  roles: [
    { role: "userAdminAnyDatabase", db: "admin" },
    { role: "dbAdminAnyDatabase", db: "admin" },
    { role: "readWriteAnyDatabase", db: "admin" }
  ]
})
```

این Roleهای گسترده فقط برای مدیریت مورداعتماد هستند. userAdminAnyDatabase امکان اعطای Role قدرتمند و عملاً قابلیت افزایش سطح دسترسی دارد؛ ترکیب بالا معادل دقیق root نیست و همه عملیات Cluster یا Backup/Restore را شامل نمی‌شود. در برنامه از آن استفاده نکنید. برای مدیریت Replica حساب عملیاتی با clusterAdmin، برای مانیتورینگ clusterMonitor و برای وظایف Backup و Restore حساب‌های جدا با Role متناظر بسازید.

```yaml
security:
  authorization: enabled
```

```bash
sudo systemctl restart mongod
sudo systemctl status mongod --no-pager
mongosh --host 127.0.0.1 -u mongoAdmin -p --authenticationDatabase admin
```

```javascript
use admin
db.runCommand({ connectionStatus: 1 })
db.getSiblingDB("appdb").getCollectionNames()
```

بررسی کنید Session تازه بدون احراز هویت نتواند Collectionهای appdb را فهرست کند. ping صرفاً Liveness است و ممکن است بدون مجوز دیتابیس موفق شود. پس از فعال‌سازی TLS از فرمان اتصال بخش ۱۳ استفاده کنید.

[راهنمای رسمی راه‌اندازی Access Control](https://www.mongodb.com/docs/manual/tutorial/enable-authentication/)

[Roleهای داخلی و دسترسی مدیریتی](https://www.mongodb.com/docs/manual/reference/built-in-roles/)

## ۱۰. حساب اختصاصی Application

در Shell مدیر احراز هویت‌شده، User را در appdb بسازید. این حساب در appdb احراز هویت می‌شود و فقط همان Database را می‌خواند و می‌نویسد. برای هر Application، Environment و Job هویت جدا ایجاد کنید. سرویس Read-Only باید read بگیرد، نه readWrite.

```javascript
use appdb
db.createUser({
  user: "appuser",
  pwd: passwordPrompt(),
  roles: [{ role: "readWrite", db: "appdb" }]
})
```

```bash
mongosh --host 127.0.0.1 --username appuser --password \
  --authenticationDatabase appdb appdb
```

عملیات لازم برنامه را تست و ردشدن عملیات مدیریتی را تأیید کنید. Least Privilege شامل Role دیتابیس، دسترسی شبکه و OS است. Credential را از طریق Secret Store Rotate کنید و بدون ثبت گذرواژه در Source برنامه را به‌روزرسانی کنید.

## ۱۱. ایمن‌سازی Firewall

27017/TCP را فقط برای Hostهای مجاز Application/Management و Memberهای Replica باز کنید. CIDR مثال است؛ برای IP ثابت Ruleهای /32 مناسب‌ترند. Rule محدود، Allow عمومی قبلی را لغو نمی‌کند؛ تمام Ruleها و Cloud Security Group را بررسی کنید. پیش از فعال‌کردن Firewall دسترسی SSH و بازیابی از Console را آماده نگه دارید.

### UFW در Ubuntu

```bash
sudo ufw allow OpenSSH
sudo ufw default deny incoming
sudo ufw allow from 10.10.10.0/24 to any port 27017 proto tcp
# On replica nodes, also allow ONLY the three peer addresses:
sudo ufw allow from 10.10.20.11 to any port 27017 proto tcp
sudo ufw allow from 10.10.20.12 to any port 27017 proto tcp
sudo ufw allow from 10.10.20.13 to any port 27017 proto tcp
sudo ufw enable
sudo ufw status verbose
```

### firewalld در خانواده RHEL

```bash
sudo systemctl enable --now firewalld
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
sudo firewall-cmd --zone=public --list-all
```

تأیید کنید Target Zone برابر ACCEPT نباشد و سرویس عمومی mongodb، Rule عمومی 27017، عضویت در Trusted Zone، NAT خارجی یا Rule IPv6 محدودیت را دور نزند. Debian ممکن است nftables داشته باشد؛ Source Allowlist معادل را در Firewall مدیریت‌شده همان Host اعمال کنید و Managerهای Firewall را مخلوط نکنید.

[مستندات رسمی Firewall در Ubuntu](https://ubuntu.com/server/docs/how-to/security/firewalls/)

[مستندات رسمی firewalld در Red Hat](https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters)

## ۱۲. ایمن‌سازی MongoDB

![لایه‌های امنیت MongoDB شامل TLS، احراز هویت، Firewall، Least Privilege و مسدودسازی دسترسی Public](/assets/img/articles/content/mongodb-security-architecture.png)

احراز هویت و Least Privilege، Network Isolation خصوصی، Firewall محدود به Source، bindIp محدود و TLS را اعمال کنید. دسترسی Public از DNS/NAT را حذف کنید. Secret طولانی یکتا و حساب مجزای Application داشته باشید. وصله OS و بسته امضاشده دیتابیس را با کنترل تغییر نصب کنید. مدیریت را روی Management Network و Login Host را محدود نگه دارید.

```bash
ls -ld /var/lib/mongodb
ls -ld /var/log/mongodb
ps aux | grep '[m]ongod'
sudo systemctl show mongod -p User -p Group
# RPM installations:
ls -ld /var/lib/mongo
sudo stat -c '%U:%G %a %n' /etc/mongod.conf /etc/mongodb/*.pem
```

mongod باید با Service User بسته اجرا شود، نه root. Data/Log برای همان User قابل نوشتن و برای حساب نامرتبط غیرقابل دسترسی باشند؛ Private Key و KeyFile فقط توسط مالک قابل خواندن باشند. خطا را با chmod 777 یا chown بازگشتی بی‌هدف رفع نکنید. مسیر دقیق، مجوز Traverse والدها، Policy در AppArmor/SELinux و Service User را بررسی کنید.

MongoDB Community امکانات Native Database Audit نسخه Enterprise را ندارد؛ افزودن auditLog اختصاصی Enterprise راهکار Community نیست. OS Audit، دسترسی کنترل‌شده مدیر، Audit Event برنامه و Log متمرکز محافظت‌شده را با نیاز سازمان طراحی کنید. Log معمول mongod به‌تنهایی Audit Trail کامل Compliance نیست. Log را محدود و Redact کنید چون Query ممکن است مقدار حساس داشته باشد.

[چک‌لیست رسمی امنیت Self-Managed](https://www.mongodb.com/docs/manual/administration/security-checklist/)

[دامنه دسترسی امکانات Audit در MongoDB](https://www.mongodb.com/docs/manual/core/auditing/)

## ۱۳. رمزنگاری TLS

برای هر سرور از Internal CA یا Trusted CA گواهی تهیه کنید. mongodb.pem شامل Certificate Chain و Private Key همان سرور و ca.pem فقط شامل CA Certificate است. SAN باید با DNS/IP استفاده‌شده در Client و Peer تطبیق داشته باشد؛ localhost/127.0.0.1 را فقط در صورت نیاز به Bootstrap محلی معتبر اضافه کنید. Private Key یکسان را بین Nodeها استفاده نکنید.

```bash
# Set mongodb on Ubuntu/Debian; set mongod on RPM systems.
MONGO_SERVICE_USER=mongodb
sudo install -d -m 750 -o "$MONGO_SERVICE_USER" -g "$MONGO_SERVICE_USER" /etc/mongodb
# Provision the CA-issued files securely before running these commands.
sudo chown "$MONGO_SERVICE_USER:$MONGO_SERVICE_USER" /etc/mongodb/mongodb.pem
sudo chmod 400 /etc/mongodb/mongodb.pem
sudo chown root:"$MONGO_SERVICE_USER" /etc/mongodb/ca.pem
sudo chmod 640 /etc/mongodb/ca.pem
```

```yaml
net:
  port: 27017
  bindIp: 127.0.0.1,10.10.10.20
  tls:
    mode: requireTLS
    certificateKeyFile: /etc/mongodb/mongodb.pem
    CAFile: /etc/mongodb/ca.pem
    allowConnectionsWithoutCertificates: true

security:
  authorization: enabled
```

این Profile با TLS+SCRAM به Client بدون Client Certificate اجازه اتصال رمزنگاری‌شده می‌دهد، اما Certificate معتبر سرور و Credential دیتابیس همچنان لازم‌اند. با CAFile، به‌صورت پیش‌فرض Client Certificate نیز لازم است؛ برای mTLS عمدی گزینه allowConnectionsWithoutCertificates را حذف یا false و Client PEM را Provision کنید. Certificate ارائه‌شده نامعتبر همچنان رد می‌شود. allowInvalidCertificates یا allowInvalidHostnames را فعال نکنید.

```bash
sudo systemctl restart mongod
sudo journalctl -u mongod -n 50 --no-pager
mongosh --host '<HOSTNAME>' --port 27017 --tls \
  --tlsCAFile /etc/mongodb/ca.pem \
  -u mongoAdmin -p --authenticationDatabase admin
```

CA مورداعتماد، تطبیق Hostname، Expiry و رد اتصال Plaintext را تأیید کنید. Renewal را پیش از انقضا هماهنگ کنید. TLS داخلی Replica نیز باید دوطرفه کار کند؛ وقتی Certificate عضو هم نقش Server و هم Client دارد، EKU باید serverAuth و clientAuth را پشتیبانی کند.

[راهنمای رسمی پیکربندی TLS](https://www.mongodb.com/docs/manual/tutorial/configure-ssl/)

## ۱۴. دسترسی Remote امن

```yaml
net:
  port: 27017
  bindIp: 127.0.0.1,10.10.10.20
```

آدرس 10.10.10.20 باید روی همین سرور وجود داشته باشد. bindIp انتخاب Interface شنود است؛ محدودیت Source Client با Firewall اعمال می‌شود. پیش از فعال‌کردن Listener خصوصی، احراز هویت، TLS و Ruleهای Firewall بخش‌های ۹ تا ۱۳ را آماده کنید. مدیریت را از VPN یا Management Network انجام دهید و NAT یا Port Forward عمومی را حذف کنید.

هشدار امنیتی: 0.0.0.0 روی همه Interfaceهای IPv4، از جمله NIC احتمالی Public، شنود می‌کند. در این راهنما پیش‌فرض نیست. استفاده استثنایی به Network Isolation، TLS و احراز هویت و بررسی Ruleهای Deny-by-Default در IPv4 و IPv6 نیاز دارد؛ bindIp به‌تنهایی Client را مجاز نمی‌کند.

[تنظیم شبکه و IP Binding](https://www.mongodb.com/docs/manual/core/security-mongodb-configuration/)

## ۱۵. احراز هویت داخلی Replica Set

Internal Authentication هویت Member را تأیید می‌کند و Client Authorization لایه‌ای جداست. این بخش را پیش از Start Cluster بخش ۱۶ انجام دهید. مثال KeyFile درخواستی پشتیبانی می‌شود، اما راهنمای فعلی MongoDB برای Production، احراز هویت عضویت با X.509 و برای Development/Testing، KeyFile را توصیه می‌کند. KeyFile+TLS را مبنایی با محدودیت و نیازمند تصمیم امنیتی مستند بدانید، نه قوی‌ترین Profile تولید.

### مثال KeyFile: تولید فقط یک بار

```bash
# Generate ONCE on mongo01. Use mongod for RPM installations.
MONGO_SERVICE_USER=mongodb
sudo install -d -m 750 -o "$MONGO_SERVICE_USER" -g "$MONGO_SERVICE_USER" /etc/mongodb
sudo sh -c 'umask 077; openssl rand -base64 756 > /etc/mongodb/keyfile'
sudo chmod 400 /etc/mongodb/keyfile
sudo chown "$MONGO_SERVICE_USER:$MONGO_SERVICE_USER" /etc/mongodb/keyfile
```

همین فایل را دقیقاً با کانال توزیع Config/Secret رمزنگاری‌شده و احراز هویت‌شده در /etc/mongodb/keyfile روی mongo02 و mongo03 قرار دهید. مالک را در Debian/Ubuntu برابر mongodb:mongodb و در RPM برابر mongod:mongod و Mode را 400 روی همه Memberها تنظیم کنید. روی هر Node کلید تصادفی مستقل تولید نکنید و Byte یا Hash کلید را منتشر نکنید. Traverse والد و اجازه خواندن SELinux را بررسی کنید.

```yaml
security:
  keyFile: /etc/mongodb/keyfile
  authorization: enabled
replication:
  replSetName: rs0
```

KeyFile احراز هویت عضویت و Access Control را فعال می‌کند اما Wire را رمز نمی‌کند؛ requireTLS را حفظ کنید. Rotation را با کلیدهای پذیرفته‌شده همپوشان و مسیر Rolling رسمی انجام دهید تا Peerها همیشه کلید مشترک داشته باشند. KeyFile را خارج از Git و Backup داده در Secret System با کنترل دسترسی نگه دارید.

### گزینه توصیه‌شده Production: اعضا با X.509

```yaml
security:
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
  replSetName: rs0
```

در Cluster تازه این گزینه را به جای keyFile روی همه Memberها انتخاب و همان ترتیب Bootstrap اولین User را دنبال کنید. member.pem گواهی و کلید یکتای عضو از CA با کاربرد clientAuth است؛ گواهی سرور serverAuth می‌خواهد. Attributeهای عضویت O/OU/DC باید میان اعضا مطابق و از هویت Client جدا باشند. SAN با نام معرفی‌شده تطبیق کند. این Profile هنوز User برنامه با SCRAM روی TLS را می‌پذیرد؛ در صورت نیاز Policy از هویت Client مجزای X.509 و mTLS استفاده کنید. PKI را پیش از استقرار Provision و اعتبارسنجی کنید.

[احراز هویت عضویت Production با X.509](https://www.mongodb.com/docs/manual/tutorial/configure-x509-member-authentication/)

[Rotation کلید Replica Set](https://www.mongodb.com/docs/manual/tutorial/rotate-key-replica-set/)

## ۱۶. Replica Set در Production

![Replica Set سه‌عضوی rs0 با Primary، Secondaryها، Election اکثریت و Failover خودکار](/assets/img/articles/content/mongodb-replica-set.png)

```text
mongo01 - 10.10.20.11
mongo02 - 10.10.20.12
mongo03 - 10.10.20.13
Replica Set: rs0
```

سه Member رأی‌دهنده دارای Data را در Failure Domain مستقل و با Version یکسان قرار دهید. از DNS پایدار استفاده کنید؛ mongo01/mongo02/mongo03 نام کوتاه نمونه‌اند و FQDN در PKI واقعی بهتر است. DNS باید از هر Member و Application Host Resolve و با SAN گواهی مطابق باشد. host اعضا را به‌صورت IP خالی تنظیم نکنید.

ترتیب Cluster تازه: روی همه Nodeها نصب کنید، mongod را Stop کنید، Mount و Certificateهای TLS را آماده کنید، Ruleهای Firewall برای Peer/Application را اعمال کنید، KeyFile مشترک بخش ۱۵ را توزیع کنید، سپس همه Memberها را Configure و Start کنید. Listener Replica بدون احراز هویت را موقتاً باز نکنید. تبدیل Standalone موجود به Backup، برنامه مهاجرت جدا و اتصال با User موجود نیاز دارد؛ با وجود User قبلی، Localhost Exception فعال نیست.

```bash
sudo systemctl stop mongod
getent hosts mongo01 mongo02 mongo03
```

پس از آماده‌سازی بخش ۱۵، تنظیم زیر را روی هر Node ادغام کنید. IP خصوصی همان Node یعنی .11، .12 یا .13 را قرار دهید. در RPM از /var/lib/mongo استفاده کنید. این Profile کامل آموزشی TLS+SCRAM/KeyFile است و Access Control از اولین Start Cluster فعال است.

```yaml
storage:
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
  timeZoneInfo: /usr/share/zoneinfo
```

```bash
# Run on each node after certificates and shared keyfile exist.
sudo systemctl restart mongod
sudo systemctl enable mongod
sudo systemctl status mongod --no-pager
# Run locally on mongo01; the certificate must cover 127.0.0.1.
mongosh 'mongodb://127.0.0.1:27017/?directConnection=true' \
  --tls --tlsCAFile /etc/mongodb/ca.pem
```

در Cluster تازه بدون User، Localhost Exception اجازه تنظیم اولیه از Loopback را می‌دهد. rs.initiate را فقط یک بار روی mongo01 اجرا کنید. فعال‌بودن Authentication نیازمند خاموش‌کردن آن برای Bootstrap نیست.

```javascript
rs.initiate({
  _id: "rs0",
  members: [
    { _id: 0, host: "mongo01:27017" },
    { _id: 1, host: "mongo02:27017" },
    { _id: 2, host: "mongo03:27017" }
  ]
})
rs.status()
db.hello().isWritablePrimary
```

منتظر Election بمانید. اگر mongo01 Primary نیست، روی Primary منتخب با همان روش TLS محلی متصل شوید. mongoAdmin را با Role مدیریت User و مثال مدیر بخش ۹ همان‌جا بسازید و سپس با Credential دوباره وصل شوید. Exception با اولین User بسته می‌شود. appuser را یک بار روی Primary بسازید؛ Userها Replicate می‌شوند و نباید روی هر Secondary جدا ساخته شوند.

### حساب جداگانه عملیات Cluster

```javascript
use admin
db.createUser({
  user: "mongoOps",
  pwd: passwordPrompt(),
  roles: [{ role: "clusterAdmin", db: "admin" }]
})
```

```bash
mongosh 'mongodb://mongo01:27017,mongo02:27017,mongo03:27017/admin?replicaSet=rs0' \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --username mongoOps --password --authenticationDatabase admin
```

```javascript
rs.status()
rs.conf()
db.hello()
rs.printSecondaryReplicationInfo()
```

Primary عملیات Write را می‌پذیرد و Secondaryها Oplog را به‌صورت Async اعمال می‌کنند. Set با سه رأی‌دهنده برای انتخاب Primary به دو رأی نیاز دارد و Secondary واجدشرایط و به‌روز می‌تواند جایگزین شود. فقدان دو Member معمولاً Write Availability را از بین می‌برد. Failover شامل زمان Election و بازیابی اتصال است؛ Retry برنامه را تست کنید و Downtime صفر فرض نکنید. mongo01 در شکل Primary نمونه است، نه Primary دائمی.

برای Write نیازمند تأیید اکثریت از Write Concern صریح majority و برای Read سازگار از Read Concern متناسب استفاده کنید. Retryable Write به عملیات منتخب کمک می‌کند؛ Idempotency و مدیریت Transaction برنامه همچنان لازم است. پنجره نگهداری Oplog را از Outage برنامه‌ریزی‌شده و Lag اوج بزرگ‌تر بگیرید. Replication حذف اشتباه را نیز کپی می‌کند و Backup نیست.

[Bootstrap امن Replica Set و Localhost Exception در مستندات رسمی](https://www.mongodb.com/docs/manual/tutorial/deploy-replica-set-with-keyfile-access-control/)

[رفتار رسمی Election و Quorum](https://www.mongodb.com/docs/manual/core/replica-set-elections/)

[Write Concern و تأیید اکثریت](https://www.mongodb.com/docs/manual/reference/write-concern/)

## ۱۷. Connection String و Secretها

### URI برای Standalone

```text
mongodb://appuser:<STRONG-PASSWORD>@10.10.10.20:27017/appdb?authSource=appdb&tls=true
```

### URI برای Replica Set

```text
mongodb://appuser:<STRONG-PASSWORD>@mongo01:27017,mongo02:27017,mongo03:27017/appdb?replicaSet=rs0&authSource=appdb&tls=true&retryWrites=true&w=majority
```

این URIها Template هستند و Password قابل اجرای واقعی ندارند. کاراکترهای رزروشده Username/Password را Percent-Encode یا Credential را جدا در Driver Option ارائه کنید. appuser در appdb ساخته شده، پس authSource=appdb است؛ مدیر ساخته‌شده در admin از authSource=admin استفاده می‌کند. فایل CA مورداعتماد را جدا در Driver Configure و نام سرور را اعتبارسنجی کنید؛ tls=true، Internal CA شما را نصب نمی‌کند.

Credential واقعی را در Git، Log، Shell History و Process Argument قرار ندهید. از Vault یا CI/CD Secret Store استفاده کنید؛ Environment Variable ساده است اما برای Process دارای دسترسی بالا قابل مشاهده و در Diagnostic قابل نشت است. Kubernetes Secret به RBAC و Encryption at Rest نیاز دارد؛ Base64 رمزنگاری نیست. Connection Pool محدود، Timeout و Retry Policy را تنظیم و Topology Discovery و Failover را از شبکه برنامه تست کنید.

[فرمت رسمی URI اتصال MongoDB](https://www.mongodb.com/docs/manual/reference/connection-string/)

## ۱۸. Log و Slow Query

```bash
sudo tail -f /var/log/mongodb/mongod.log
sudo journalctl -u mongod --no-pager
sudo journalctl -u mongod -n 100 --no-pager
```

MongoDB Diagnostic Log ساختاریافته می‌نویسد. خطای Parse YAML، فقدان dbPath، Permission Denied، Address Already in Use، مالکیت KeyFile، خطای Handshake/Certificate TLS، خطای WiredTiger و Authentication Failure را جست‌وجو کنید. اولین خطای Startup معمولاً Restartهای بعدی را توضیح می‌دهد؛ پیش از اقدام Log را حفظ کنید.

```yaml
operationProfiling:
  mode: off
  slowOpThresholdMs: 100
  slowOpSampleRate: 0.1
```

این نمونه Profiler دیتابیس را خاموش نگه می‌دارد و Sample از Slow Diagnostic Log می‌گیرد. ۱۰۰ میلی‌ثانیه و ۱۰ درصد نقطه آغاز متناسب با SLO هستند، نه عدد جهانی. Profile همه عملیات بار را افزایش می‌دهد و می‌تواند Query حساس ثبت کند. Log را با Retention محدود و Alert احراز هویت ناموفق، خطای Storage و Election متمرکز کنید.

Rotation را پیش از پرشدن Disk تنظیم کنید. با systemLog.logRotate: reopen، Policy خارجی Rename/Create با مالک Service User اعمال و سپس logRotate یا SIGUSR1 مستند اجرا کنید. Policyهای ناسازگار rename/reopen را ترکیب نکنید و copytruncate را بی‌خطر فرض نکنید. Rotation و Retention را در Staging تست کنید.

[Rotation رسمی Log در MongoDB](https://www.mongodb.com/docs/manual/tutorial/rotate-log-files/)

[Profiler و تنظیم Slow Operation در مستندات رسمی](https://www.mongodb.com/docs/manual/tutorial/manage-the-database-profiler/)

## ۱۹. مانیتورینگ و Alert

```javascript
use admin
db.createUser({
  user: "mongoMonitor",
  pwd: passwordPrompt(),
  roles: [{ role: "clusterMonitor", db: "admin" }]
})
```

از MongoDB Exporter سازگار با Server، Prometheus برای جمع‌آوری و Grafana برای Dashboard استفاده کنید؛ Zabbix نیز می‌تواند MongoDB و Host Health را مستقل رصد کند. این‌ها Integration خارجی‌اند، نه Component همراه Community. بعضی Collectorها Read Permission بیشتری می‌خواهند؛ فقط Collector لازم را فعال و پس از بررسی مستندات همان Exporter، Role محدود بدهید. Endpoint و Secret مربوط به Exporter خصوصی بماند.

| Metric / Event | هدف Alert |
| --- | --- |
| Connection / Operation | اشباع Pool، Connection Churn و Throughput غیرعادی نسبت به مبنا. |
| Query Latency | نقض مستمر SLO در p95/p99؛ تفکیک Read/Write و Timeout. |
| Replication Lag / Oplog Window | نزدیک‌شدن Lag به بودجه Recovery یا Retention Oplog؛ Sync ناموفق. |
| Disk / Inode / I/O Latency | ظرفیت و پیش‌بینی رشد، تأخیر صف و خطر Volume پر. |
| Memory / Page Fault / Swap | Major Fault و Swap در OS را با Eviction و Read Disk مرتبط ببینید؛ Minor Fault به‌تنهایی Incident نیست. |
| Replica Status / Primary Election | نبود Primary، Member خارج از دسترس، Election مکرر یا تغییر غیرمنتظره Topology. |
| Backup / Certificate / Log | Backup قدیمی، Restore Drill ناموفق، نزدیک‌شدن Expiry و Event امنیت/Storage در Log. |

Metric هر Member و Telemetry سیستم‌عامل را جمع کنید؛ مانیتور فقط Primary، Secondary خراب را پنهان می‌کند. Threshold عملی، Owner و Runbook تعیین کنید. Alert و مسیر Notification را تست و سپس از دسترس خارج‌شدن یک Node را در Staging و بازیابی برنامه و تشخیص Primary جدید را بررسی کنید.

[Metricهای رسمی مانیتورینگ Self-Managed](https://www.mongodb.com/docs/manual/administration/monitoring/)

## ۲۰. Backup و بازیابی آزموده‌شده

![معماری Backup Server و مسیرهای موازی مانیتورینگ Prometheus، Grafana، Zabbix و Syslog برای MongoDB](/assets/img/articles/content/mongodb-backup-monitoring.png)

RPO یعنی داده قابل ازدست‌رفتن، RTO یعنی زمان بازیابی، Retention و نسخه Off-site/Immutable را تعیین کنید. Backup را رمز، دسترسی را محدود و نسخه مستقل را خارج از Failure Domain سرور MongoDB نگه دارید. Version ابزار/سرور را ثبت و Restore را دوره‌ای تست کنید. mongodump و mongorestore از MongoDB Database Tools با Version مستقل هستند؛ سازگاری با سرور را پیش از زمان‌بندی Job بررسی کنید.

```bash
mongodump --version
mongorestore --version
```

### هویت مجزای Backup و Restore

```javascript
use admin
db.createUser({
  user: "mongoBackup",
  pwd: passwordPrompt(),
  roles: [{ role: "backup", db: "admin" }]
})
db.createUser({
  user: "mongoRestore",
  pwd: passwordPrompt(),
  roles: [{ role: "restore", db: "admin" }]
})
```

حساب Restore را در مقصد بازیابی ایزوله بسازید، نه به‌صورت خودکار در همه Clusterهای Production. Roleهای گسترده mongoAdmin در بخش قبل جای backup/restore را نمی‌گیرند. حذف Password Flag در فرمان زیر Prompt ایجاد می‌کند؛ Job زمان‌بندی‌شده باید از فایل Tools --config با Mode برابر 600 که Secret System ایجاد کرده بخواند، نه Password داخل --uri.

### Backup منطقی برای دیتابیس کوچک

```bash
umask 077
BACKUP_DIR="/backup/mongodb/$(date +%F)/$(date +%H%M%S)"
mkdir -p "$BACKUP_DIR"
mongodump \
  --host '<HOSTNAME>' --port 27017 \
  --username mongoBackup --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --db appdb --out "$BACKUP_DIR"
```

حساب OS اجرای Backup باید مالک /backup/mongodb یا دارای مجوز نوشتن صریح باشد. Dump یک Database هنگام ادامه Write، تصویر Atomic از یک لحظه نیست؛ برای Consistency میان Collectionها Write برنامه را متوقف کنید. Exit موفق لازم است اما کافی نیست: خروجی، ظرفیت آزاد، نتیجه Restore و Business Invariantها را بررسی کنید.

### Restore دیتابیس appdb در مقصد ایزوله

```bash
mongorestore \
  --host '<HOSTNAME>' --port 27017 \
  --username mongoRestore --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --nsInclude='appdb.*' \
  '/backup/mongodb/<DATE>/<TIME>'
```

در این مثال <HOSTNAME> مقصد Restore ایزوله است. فرمان Collection موجود را Drop نمی‌کند؛ Restore روی داده زنده می‌تواند Duplicate Key یا ادغام ناخواسته ایجاد کند. از مقصد تمیز استفاده و Index، Count و Read برنامه را کنترل کنید. --drop مخرب است و فقط در برنامه جایگزینی صریح باید استفاده شود.

### Dump کامل Replica Set با ثبت Oplog

```bash
umask 077
mongodump \
  --host 'rs0/mongo01:27017,mongo02:27017,mongo03:27017' \
  --username mongoBackup --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --oplog --gzip --archive="/backup/mongodb/rs0-$(date +%F-%H%M%S).archive.gz"
```

از --oplog برای Dump کامل Replica Set استفاده کنید، نه همراه --db یا Collection Filter. پنجره Oplog باید کل Dump را پوشش دهد و هنگام Capture از Schema Change/Rename اجتناب کنید. --oplogReplay در Restore عملیات ثبت‌شده را Replay می‌کند؛ با Namespace Filter ترکیب نمی‌شود و دسترسی بیشتر از Role داخلی restore نیاز دارد. دسترسی سفارشی مستند Replay را فقط در سیستم بازیابی ایزوله Provision کنید.

```bash
# Run only on an isolated clean recovery target with approved replay privileges.
mongorestore \
  --host '<HOSTNAME>' --port 27017 \
  --username '<ADMIN-USER>' --authenticationDatabase admin \
  --tls --tlsCAFile /etc/mongodb/ca.pem \
  --gzip --archive='/backup/mongodb/<ARCHIVE>.archive.gz' --oplogReplay
```

برای دیتابیس بزرگ، استراتژی Replica/Snapshot اندازه‌گیری‌شده با Snapshot اتمیک Data و Journal، Topology سازگار و در صورت نیاز برنامه Oplog/PITR لازم است. کپی خام dbPath زنده Backup سازگار نیست. Replica، Snapshot روی همان Storage خراب‌شونده و فایل Backup بدون مسیر بازیابی تست‌شده هدف Recovery را برآورده نمی‌کنند.

[گزینه‌ها و محدودیت Consistency در mongodump](https://www.mongodb.com/docs/database-tools/mongodump/)

[الزامات رسمی mongorestore و Oplog Replay](https://www.mongodb.com/docs/database-tools/mongorestore/)

[روش‌های رسمی Backup](https://www.mongodb.com/docs/manual/core/backups/)

## ۲۱. چک‌لیست اعتبارسنجی

```bash
systemctl is-active mongod
sudo ss -lntp | grep 27017
# Bare mongosh is only for the initial non-TLS localhost bootstrap.
# For the secured deployment use:
mongosh --host '<HOSTNAME>' --port 27017 --tls \
  --tlsCAFile /etc/mongodb/ca.pem \
  --username appuser --password --authenticationDatabase appdb appdb
```

```javascript
db.runCommand({ ping: 1 })
db.runCommand({ connectionStatus: 1 })
db.getCollectionNames()
```

انتظار active، Listener فقط روی Interface مجاز، ping با ok:1 و authenticatedUsers شامل appuser در appdb داشته باشید. از Host برنامه مجاز تست و عدم اتصال Host غیرمجاز را تأیید کنید. دسترسی Collection بدون احراز هویت و اتصال Plaintext باید رد شود. در Replica با mongoOps وصل و rs.status() را بررسی کنید: یک Primary، دو Secondary سالم و Lag قابل‌قبول.

```javascript
// Authenticated mongoOps replica session:
rs.status()
rs.printSecondaryReplicationInfo()
```

در Staging آزمون Failover کنترل‌شده، Read/Write واقعی برنامه با تأیید اکثریت، دریافت Alert و Restore Backup در مقصد ایزوله تمیز را انجام دهید. این تست‌ها رفتاری فراتر از Process Liveness را مشخص می‌کنند. سینتکس نصب و پیکربندی با مستندات بررسی شده است؛ فرمان‌های استقرار Linux باید روی سرور مقصد شما اجرا و اعتبارسنجی شوند.

## ۲۲. بررسی Performance

```bash
# Install the distribution sysstat package for iostat.
# Ubuntu/Debian: sudo apt install -y sysstat
# RHEL family: sudo dnf install -y sysstat
iostat -xz 1
vmstat 1
free -h
df -h
df -i
```

```javascript
// Run with a monitoring identity; replace users with your collection.
db.serverStatus()
use appdb
db.stats()
db.users.stats()
// Preferred collection statistics for new automation:
db.users.aggregate([{ $collStats: { storageStats: {} } }])
```

نام Collection واقعی را استفاده کنید: db.collection.stats() Template است، نه بررسی همه Collectionها. فرمان collStats پشت این Helper Deprecated است؛ برای Integration جدید $collStats مناسب‌تر است. serverStatus دسترسی مناسب مانند clusterMonitor می‌خواهد؛ Credential برنامه برای Diagnostic کامل سرور مناسب نیست.

| شاخص | تفسیر و اقدام |
| --- | --- |
| Working Set / RAM | Data و Index پرتکرار باید در بودجه Cache قابل استفاده قرار گیرد؛ پیش از افزایش Cache، Eviction، Read Disk و Latency را مرتبط بررسی کنید. |
| IOPS و Latency دیسک | await، عمق صف و Throughput را در Peak Load ببینید؛ %util به‌تنهایی برای NVMe/RAID کافی نیست. |
| Index و Slow Query | Plan، نسبت Scanned/Returned و Latency p95/p99 را بررسی کنید؛ کورکورانه Index اضافه نکنید. |
| Connection | current، available و نرخ ایجاد را رصد کنید؛ Driver Pool محدود را بازاستفاده کنید، نه یک اتصال در هر Request. |
| ظرفیت Replication | Lag و Oplog Window را هنگام Peak Write و Backup کنترل کنید؛ Secondary به CPU/Storage هم‌سطح نیاز دارد. |

[آمار Database](https://www.mongodb.com/docs/manual/reference/method/db.stats/)

[آمار Collection و نکات Deprecation](https://www.mongodb.com/docs/manual/reference/method/db.collection.stats/)

## ۲۳. Index و Query Plan

```javascript
use appdb
db.users.createIndex({ email: 1 })
db.users.getIndexes()
db.users.find({ email: "user@example.com" }).explain("executionStats")
```

nReturned، totalDocsExamined، totalKeysExamined و winningPlan را بررسی کنید. Lookup انتخاب‌گر email باید تعداد کمی Key/Document بررسی کند. IXSCAN استفاده از Index و COLLSCAN اسکن Collection است. Query گسترده روی Collection کوچک ممکن است به‌درستی Scan شود، اما Scan مکرر انتخاب‌گر روی Collection بزرگ Production می‌تواند I/O و CPU را اشباع کند.

Compound Index را با Filter و Sort طراحی و با داده نماینده تست کنید. اگر email باید Unique باشد، پیش از Unique Index Duplicateهای موجود را پاک‌سازی کنید. هر Index حافظه/دیسک می‌گیرد و هزینه Write دارد. Index را با کنترل تغییر بسازید و ظرفیت و Lag را رصد کنید؛ هر Scan الزاماً Incident نیست.

[مفاهیم رسمی Indexing](https://www.mongodb.com/docs/manual/indexes/)

[Execution Statistics رسمی در explain](https://www.mongodb.com/docs/manual/reference/method/db.collection.explain/)

## ۲۴. عیب‌یابی

```bash
sudo systemctl status mongod --no-pager
sudo journalctl -u mongod -n 100 --no-pager
sudo ss -lntp | grep 27017
ps aux | grep '[m]ongod'
df -h
df -i
free -h
```

| مشکل | علت محتمل | تشخیص | راهکار |
| --- | --- | --- | --- |
| mongod Start نمی‌شود | خطای YAML، CPU/Kernel ناسازگار، فقدان dbPath | journalctl؛ mongod --version؛ lscpu | اولین خطا را رفع؛ بسته/Kernel پشتیبانی‌شده و Storage Mountشده استفاده کنید. |
| Permission Denied | مالک سرویس یا Mode کلید/PEM اشتباه، Denial در SELinux/AppArmor | stat؛ namei -l؛ ausearch -m AVC | مالک، مسیر و Label دقیق و Traverse والد را اصلاح کنید. |
| Port 27017 اشغال یا بسته است | Listener دیگر یا Source/Zone نادرست | ss -lntp؛ Rule Firewall؛ Route خصوصی | تعارض Listener یا Allowlist محدود را اصلاح کنید. |
| Authentication Failed | Secret، Database کاربر یا authSource اشتباه | connectionStatus پس از Login معتبر؛ usersInfo توسط مدیر | برای appuser از appdb و برای مدیر از admin استفاده؛ Rotation با مدیر مجاز انجام دهید. |
| Connection Refused / خطای TLS | سرویس Stop، bindIp اشتباه، عدم تطبیق Hostname/CA | systemctl؛ ss؛ getent hosts؛ Log Client TLS | Listener و DNS را اصلاح؛ CA مورداعتماد و گواهی مطابق نصب کنید. |
| Secondary Sync نمی‌شود | Peer غیرقابل دسترسی، عدم تطبیق کلید/گواهی، Lag بیشتر از Oplog | rs.status()؛ Log Peer؛ Metric Disk/Lag | ارتباط/هویت/ظرفیت را رفع؛ در فقدان History، Initial Resync برنامه‌ریزی‌شده انجام دهید. |
| Replica Set بدون Primary | فقدان اکثریت رأی، Partition یا Member فاقدشرایط | rs.status()؛ rs.conf()؛ DNS/Firewall Peer | اکثریت و Member واجدشرایط را بازیابی؛ Forced Reconfig را راهکار معمول نکنید. |
| Disk پر | رشد Data/Oplog/Log، Dump روی Disk داده، Inode پر | df -h؛ df -i؛ اندازه Log/Backup | Storage را توسعه یا Backup/Log قدیمی مجاز را حذف؛ فایل WiredTiger را حذف نکنید. |
| CPU بالا | Scan، Aggregation پرهزینه، Connection Churn یا Index Build | top؛ explain؛ Slow Log؛ Connection | Query/Index/Pool را بهینه و با Load نماینده اعتبارسنجی کنید. |
| Disk I/O بالا | Working Set بزرگ‌تر از Cache، Storage کند، تداخل Backup/Resync | iostat -xz؛ vmstat؛ Metric Cache/Lag/Latency | Scan غیرضروری را کم، بار Backup را جدا و RAM/IOPS اندازه‌گیری‌شده تأمین کنید. |

mongod --repair، Forced Reconfiguration، حذف فایل Data یا خاموش‌کردن Authorization را Repair عمومی ندانید. Evidence و آخرین Backup قابل بازیابی را حفظ کنید. Member عقب‌مانده از Oplog ممکن است Resync برنامه‌ریزی‌شده بخواهد؛ ابتدا Source سالم و ظرفیت را تأیید کنید.

## ۲۵. چک‌لیست Production

پیش از Go-Live برای هر مورد Evidence، Owner و تاریخ Review ثبت کنید. سلول Status عمداً خالی است؛ مطالعه مقاله به معنی Passشدن استقرار نیست.

| مورد | وضعیت |
| --- | --- |
| احراز هویت فعال |  |
| حساب اختصاصی Application |  |
| محدودبودن Port 27017 |  |
| محدودبودن bindIp |  |
| فعال‌بودن TLS |  |
| فعال‌بودن Replica Set |  |
| پیکربندی Backup |  |
| فعال‌بودن Monitoring |  |
| فعال‌بودن Log Monitoring |  |
| بررسی ظرفیت Disk |  |
| فعال و هماهنگ‌بودن NTP |  |
| فعال‌بودن Firewall |  |
| گذرواژه‌های قوی |  |
| Secret خارج از Source Code |  |
| Restore Drill موفق و RPO/RTO مشخص |  |
| احراز هویت داخلی و Renewal گواهی |  |
| Failure Domain، اکثریت و Oplog Window |  |
| Service Limit، SELinux و Mountهای Storage |  |

## تحویل عملیاتی

Inventory Node، Config مؤثر، وابستگی DNS/PKI، Owner Secret، Runbook Alert، Retention Backup و مسیرهای Restore/Failover تست‌شده را تحویل دهید. شاخه مخزن منتخب را کنترل، پیش از Upgrade، Release Notes را مرور و برنامه Rollback/Recovery نگه دارید. استقرار امن مسئولیت عملیاتی مستمر است.

[مسیر رسمی ارتقا از 8.0 به 9.0](https://www.mongodb.com/docs/manual/release-notes/9.0-upgrade-from-8.0/)

## پرسش‌های متداول

### آیا با این فرمان‌ها MongoDB 9.0 روی Debian 12 نصب می‌شود؟

خیر. ماتریس رسمی Debian 12 را برای 8.x و Debian 13 را برای 9.x فهرست می‌کند. از مخزن 8.0 Bookworm این راهنما یا ارتقای برنامه‌ریزی‌شده OS استفاده کنید.

### آیا bindIp تعیین می‌کند کدام Client وصل شود؟

خیر. Interface محلی شنود را انتخاب می‌کند. محدودیت آدرس Client با Source Rule در Firewall اعمال می‌شود.

### آیا Replica Set همان Backup است؟

خیر. Write از جمله حذف اشتباه را Replicate می‌کند. Backup مستقل نگه دارید و Restore را تست کنید.

### آیا Replica Set به Load Balancer نیاز دارد؟

معمولاً خیر. Driver آگاه از Replica، Memberها را کشف و Server مناسب را انتخاب می‌کند.

### آیا KeyFile ترافیک Replication را رمز می‌کند؟

خیر. Memberها را احراز هویت می‌کند. TLS ترافیک را رمز می‌کند؛ راهنمای فعلی رسمی برای عضویت Production، X.509 را توصیه می‌کند.

### آیا mongoAdmin با سه Role مثال همه عملیات را انجام می‌دهد؟

خیر. Roleها گسترده‌اند اما همه عملیات Cluster، Backup و Restore را ندارند. Role عملیاتی مجزا بدهید.

### آیا برای MongoDB 8.0 و 9.0 باید THP را خاموش کنم؟

روی x86_64/ARM64 پشتیبانی‌شده، راهنمای جدید TCMalloc برای فعال‌کردن THP با تنظیمات مستند را دنبال کنید. توصیه نسخه‌های قدیمی متفاوت است.

### آیا ping موفق برای تأیید Production کافی است؟

خیر. Authorization، محدودیت شبکه، TLS، سلامت Replica، رفتار برنامه، Alert و Restore Drill ایزوله را بررسی کنید.

## منابع رسمی

مراجع این راهنما مستندات MongoDB و راهنماهای رسمی توزیع‌های زیر هستند. راهنمای Linux فعلی Selector توزیع دارد؛ توزیع و روش Package متناظر را انتخاب کنید. تعریف مخزن Community 9.0 علاوه بر آن در Source رسمی مستندات MongoDB تطبیق داده شد. URL کلید امضا موفق پاسخ داد؛ درخواست Metadata مخزن از شبکه نگارش HTTP 403 گرفت، بنابراین موجودبودن زنده Package باید با apt-cache policy یا dnf در شبکه استقرار تأیید شود.

[فهرست نسخه Stable](https://www.mongodb.com/docs/manual/release-notes/)

[Release Notes و Known Issueهای 9.0](https://www.mongodb.com/docs/manual/release-notes/9.0/)

[پلتفرم‌های پشتیبانی‌شده Community](https://www.mongodb.com/docs/community-platform-support/)

[نصب Community در Ubuntu](https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=ubuntu&linux-method=pkg)

[نصب Community 8.0 در Debian 12](https://www.mongodb.com/docs/v8.0/tutorial/install-mongodb-on-debian/)

[نصب Community در RHEL و SELinux](https://www.mongodb.com/docs/manual/administration/install-community-linux/?linux-distro=rhel&linux-method=pkg)

[پارامترهای فایل Config](https://www.mongodb.com/docs/manual/reference/configuration-options/)

[پیکربندی TLS](https://www.mongodb.com/docs/manual/tutorial/configure-ssl/)

[Bootstrap Replica Set با KeyFile](https://www.mongodb.com/docs/manual/tutorial/deploy-replica-set-with-keyfile-access-control/)

[احراز هویت عضو با X.509](https://www.mongodb.com/docs/manual/tutorial/configure-x509-member-authentication/)

[Database Tools: mongodump](https://www.mongodb.com/docs/database-tools/mongodump/)

[Database Tools: mongorestore](https://www.mongodb.com/docs/database-tools/mongorestore/)

[مانیتورینگ Self-Managed](https://www.mongodb.com/docs/manual/administration/monitoring/)

[Firewall رسمی Ubuntu](https://ubuntu.com/server/docs/how-to/security/firewalls/)

[مدیریت شبکه Debian](https://www.debian.org/doc/manuals/debian-reference/ch05.en.html)

[firewalld رسمی RHEL](https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters)
