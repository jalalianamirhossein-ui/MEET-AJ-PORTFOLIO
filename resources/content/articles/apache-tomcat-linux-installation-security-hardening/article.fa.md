# آموزش نصب، راه‌اندازی و امن‌سازی Apache Tomcat روی لینوکس به‌صورت سرویس

نصب Tomcat 11 با Java 21 روی Ubuntu، سرویس امن systemd، پروکسی Nginx و HTTPS، مجوزهای محدود، استقرار WAR، مانیتورینگ و ارتقای امن در محیط Production.

## ۱. مقدمه؛ جایگاه Tomcat در معماری

Apache Tomcat یک Web Container جاوا است: Connector درخواست HTTP را به Catalina می‌دهد که Application، چرخه Servlet و Session را در JVM مدیریت می‌کند. Jasper مسئول کامپایل JSP است. Tomcat بخشی از Specificationهای وب Jakarta EE را پیاده می‌کند و Application Server کامل با همه APIهای Enterprise نیست؛ برنامه می‌تواند Framework و Library موردنیاز را همراه خود داشته باشد.

| جزء | مسئولیت |
| --- | --- |
| Tomcat | اجرای Servlet، JSP و برنامه وب Java در JVM؛ امکان سرو HTTP و فایل ثابت نیز دارد. |
| Apache HTTP Server | وب‌سرور عمومی با Module، سرو فایل ثابت و Reverse Proxy؛ محصولی جدا از Tomcat. |
| Nginx | وب‌سرور و Reverse Proxy لبه برای TLS، Routing و کنترل Request؛ Servlet جاوا اجرا نمی‌کند. |

کاربردهای Enterprise شامل پورتال داخلی، سرویس REST و نرم‌افزار WAR سازمانی است. Support Matrix سازنده را بررسی کنید: برنامه تأییدشده برای Tomcat 9 الزاماً روی 11 اجرا نمی‌شود. استقرار تک‌سرور مبنای راهنما است و High Availability نیست؛ معماری بزرگ‌تر چند نود، Load Balancer، Data Store خارجی و راهبرد Session مشخص دارد.

[Specificationها و مستندات رسمی Tomcat 11](https://tomcat.apache.org/tomcat-11.0-doc/index.html)

## پیش‌نیازهای سرور و طرح مسیرها

پلتفرم اصلی Ubuntu Server 24.04 LTS روی Host جدید و اختصاصی با Bash، sudo و systemd است. گزینه RHEL برای سیستم نگهداری‌شده خانواده RHEL 9 با بسته Java 21 موجود است. فرمان APT و DNF را ترکیب نکنید. این راهنما از Archive بالادستی استفاده می‌کند، نه سرویس Tomcat بسته توزیع؛ Instance بسته را روی همان Port همزمان اجرا نکنید.

- مثال ظرفیت: ۲ تا ۴ vCPU، حافظه 4 GiB و SSD برای برنامه کوچک؛ Heap، Native Memory، Thread، دیسک و زمان پاسخ را Load Test کنید. این اعداد برای برنامه‌ریزی‌اند، نه حداقل رسمی.
- DNS: دامنه tomcat.example.com را با دامنه واقعی عوض کنید. A/AAAA باید به Proxy برسد؛ AAAA نامعتبر را حذف کنید. زمان را همگام و خروجی DNS، NTP و Update/ACME را کنترل کنید.
- ورودی: HTTPS عمومی 443؛ HTTP روی 80 برای HTTP-01 و Redirect؛ SSH روی Port واقعی مدیریت از Source مجاز. 8080، 8009 و 8005 عمومی نباشند.
- Change Control: دسترسی Console، تست Staging، Backup، Maintenance Window و تأیید مالک برنامه برای تغییر Production.

| مسیر | مالکیت و کاربرد |
| --- | --- |
| /opt/tomcat/apache-tomcat-11.0.26 | root:tomcat؛ Binary نسخه محافظت‌شده. /opt/tomcat/current انتخاب CATALINA_HOME است. |
| /var/lib/tomcat | CATALINA_BASE؛ Parent متعلق به root و conf/bin/lib/webapps غیرقابل نوشتن برای سرویس. |
| /var/lib/tomcat/{work,temp,data} | tomcat:tomcat؛ Scratch، فایل موقت JVM و State صریح برنامه. |
| /var/log/tomcat | tomcat:tomcat؛ لاگ Access و برنامه. BASE/logs به این مسیر Symlink می‌شود. |
| /etc/tomcat/tomcat.env | root:tomcat با 0640؛ تنظیم بررسی‌شده JVM و سرویس، بدون Credential. |

```bash
hostnamectl
cat /etc/os-release
free -h
df -h
timedatectl
systemd --version
sudo ss -lntp
getent hosts tomcat.example.com
```

ظرفیت Mount، Listener موجود و Policy امنیت Kernel را پیش از نصب بررسی کنید. Volume جدا برای Runtime باید قبل از Start در مسیر مستند Mount شود. Upload برنامه و Heap Dump داده حساس‌اند؛ Quota و Retention محدود تعریف کنید.

## ۲. معماری و پیش‌نیازهای سرور

![ارتباط اینترنت از Firewall و Nginx با TLS روی 443 به Tomcat محلی روی 8080 و برنامه Java داخل JVM](/assets/img/articles/content/apache-tomcat-production-architecture.png)

ارتباط اینترنت از Firewall و Nginx با TLS روی 443 به Tomcat محلی روی 8080 و برنامه Java داخل JVM

```text
Internet -> Firewall -> Nginx HTTPS :443
                            |
                            v
                 Tomcat 127.0.0.1:8080
                            |
                            v
                   Java application (JVM)
```

## ۳. نسخه Stable بررسی‌شده و سازگاری Java

صفحه Download و Version Matrix رسمی Apache نسخه 11.0.26 را آخرین Stable شاخه 11 معرفی می‌کنند. مثال همین نسخه را Pin و Java 21 LTS را از بسته نگهداری‌شده توزیع نصب می‌کند. Java 21 انتخاب سازگار آگاهانه است، نه ادعای جدیدترین Feature Release جاوا. پیش از هر نصب، Security Advisory و صفحه Stable را دوباره بررسی کنید؛ نسخه ثابت مثال با زمان قدیمی می‌شود.

[دانلود رسمی Stable نسخه Tomcat 11 و فایل‌های Integrity](https://tomcat.apache.org/download-11.cgi)

[جدول رسمی سازگاری Java و Servlet](https://tomcat.apache.org/whichversion.html)

| شاخه | حداقل Java و API | نتیجه مهاجرت |
| --- | --- | --- |
| 9.0.x | Java 8+؛ Servlet 4.0 و Java EE 8 | API وب قدیمی از javax.servlet.* استفاده می‌کند. |
| 10.1.x | Java 11+؛ Servlet 6.0 و Jakarta EE 10 | API وب Jakarta از jakarta.* استفاده می‌کند؛ 10.0 به پایان پشتیبانی رسیده است. |
| 11.0.x | Java 17+؛ Servlet 6.1 و Pages 4.0 | Java 21 حداقل Runtime را پوشش می‌دهد؛ تغییر Framework و API را تست کنید. |

برنامه Java EE و Dependency را برای Jakarta بازسازی و سپس تست کنید؛ تغییر Import یا اجرای Migration Tool به‌تنهایی اثبات سازگاری نیست. Packageهای Java SE مثل javax.sql همچنان javax.* هستند. API حذف‌شده و Default تغییرکرده را در Migration Guide نسخه 11 بررسی کنید؛ Bytecode برنامه از JVM انتخابی جدیدتر نباشد. WAR کامپایل‌شده برای Java 25 روی Java 21 اجرا نمی‌شود.

[مهاجرت Tomcat 11 و تفاوت Configuration](https://tomcat.apache.org/migration-11.0.html)

[Security Advisoryهای Tomcat 11](https://tomcat.apache.org/security-11.html)

## ۴. نصب و بررسی Java 21

### Ubuntu 24.04 LTS

```bash
sudo apt update
sudo apt upgrade
sudo apt install -y openjdk-21-jdk-headless ca-certificates curl tar gzip unzip
java -version
javac -version
update-alternatives --list java
sudo update-alternatives --config java
```

Upgrade بسته و نیاز Reboot را در Maintenance بررسی کنید. در وجود چند JDK، Alternative مربوط به Java 21 را انتخاب کنید. JDK بدون GUI ابزار تشخیص و سازگاری Build را فراهم می‌کند؛ Artifact انتشار را در CI بسازید، نه روی Production.

### گزینه جایگزین خانواده RHEL 9

```bash
sudo dnf upgrade
sudo dnf install -y java-21-openjdk-devel ca-certificates curl tar gzip unzip
sudo alternatives --config java
java -version
javac -version
```

روی RHEL، Repository پشتیبانی‌شده فعال و Candidate بسته را برای Minor Release سیستم بررسی کنید. Layout عمومی Archive در ادامه یکسان است؛ Firewall، Configuration بسته Nginx و Policyهای SELinux تفاوت دارند.

### تشخیص JAVA_HOME بدون فرض معماری CPU

```bash
JAVA_INSTALL_HOME="$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")"
printf "JAVA_HOME=%s\n" "$JAVA_INSTALL_HOME"
"$JAVA_INSTALL_HOME/bin/java" -version
"$JAVA_INSTALL_HOME/bin/javac" -version
java -XshowSettings:properties -version 2>&1 | grep -E "java.home|java.version|os.arch"
```

انتظار Java 21 و مسیر واقعی JDK را دارید، نه /usr/bin. مسیر را ثبت کنید: systemd در EnvironmentFile عبارت $(...) را اجرا نمی‌کند. Environment سرویس در ادامه مسیر Literal را ذخیره می‌کند؛ تغییر Alternative در Shell، JVM سرویس را پنهانی تغییر نمی‌دهد.

[مرجع پروژه OpenJDK JDK 21](https://openjdk.org/projects/jdk/21/)

[Red Hat: نصب بسته OpenJDK و Alternativeها](https://developers.redhat.com/blog/2018/12/10/install-java-rhel8)

[مرجع Launcher، Heap و گزینه‌های JVM در Java 21](https://docs.oracle.com/en/java/javase/21/docs/specs/man/java.html)

## ۵. نصب Tomcat با مسیر نسخه محافظت‌شده

فرمان‌های فصل ۵ تا ۷ Instance جدید می‌سازند. اگر User یا مسیر موجود است، ابتدا آن را بررسی کنید؛ Instance عملیاتی را Overwrite نکنید. Blockهای Bash را به‌ترتیب اجرا و در هر خطا متوقف شوید. تا نصب server.xml امن و Unit سرویس، Start انجام نمی‌شود.

### ساخت حساب و جداسازی State قابل نوشتن

```bash
sudo groupadd --system tomcat
sudo useradd --system --gid tomcat --home-dir /var/lib/tomcat \
  --no-create-home --shell /usr/sbin/nologin tomcat
sudo install -d -o root -g tomcat -m 0750 /opt/tomcat /var/lib/tomcat
sudo install -d -o root -g tomcat -m 0750 \
  /var/lib/tomcat/bin /var/lib/tomcat/lib /var/lib/tomcat/conf \
  /var/lib/tomcat/conf/Catalina /var/lib/tomcat/conf/Catalina/localhost \
  /var/lib/tomcat/webapps /etc/tomcat
sudo install -d -o tomcat -g tomcat -m 0750 \
  /var/lib/tomcat/work /var/lib/tomcat/temp /var/lib/tomcat/data /var/log/tomcat
sudo ln -s /var/log/tomcat /var/lib/tomcat/logs
getent passwd tomcat
id tomcat
```

مسیر nologin بالا در Ubuntu وجود دارد؛ روی توزیع دیگر با command -v nologin مسیر را بررسی و در صورت نیاز جایگزین کنید. به این حساب sudo ندهید. سرویس فقط در Runtime و Log می‌نویسد، نه bin، lib، conf یا webapps. Upload زیر data یا Storage جدا و بررسی‌شده قرار می‌گیرد.

### دانلود و بررسی SHA-512 رسمی

```bash
set -euo pipefail
TOMCAT_VERSION=11.0.26
TOMCAT_ARCHIVE="apache-tomcat-${TOMCAT_VERSION}.tar.gz"
TOMCAT_DOWNLOAD_DIR="$(mktemp -d)"
cd "$TOMCAT_DOWNLOAD_DIR"
curl --fail --show-error --location --proto '=https' --tlsv1.2 \
  "https://dlcdn.apache.org/tomcat/tomcat-11/v${TOMCAT_VERSION}/bin/${TOMCAT_ARCHIVE}" \
  --output "$TOMCAT_ARCHIVE"
curl --fail --show-error --location --proto '=https' --tlsv1.2 \
  "https://downloads.apache.org/tomcat/tomcat-11/v${TOMCAT_VERSION}/bin/${TOMCAT_ARCHIVE}.sha512" \
  --output "${TOMCAT_ARCHIVE}.sha512"
sha512sum --check "${TOMCAT_ARCHIVE}.sha512"
tar -tzf "$TOMCAT_ARCHIVE" > archive-contents.txt
sed -n '1,10p' archive-contents.txt
sudo test ! -e "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}"
sudo tar --extract --gzip --file "$TOMCAT_ARCHIVE" --directory /opt/tomcat --no-same-owner
sudo chown -R root:tomcat "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}"
sudo find "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}" -type d -exec chmod 0750 {} +
sudo find "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}" -type f -exec chmod 0640 {} +
sudo find "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}/bin" -type f -name '*.sh' -exec chmod 0750 {} +
sudo ln -s "/opt/tomcat/apache-tomcat-${TOMCAT_VERSION}" /opt/tomcat/current
sudo cp -a /opt/tomcat/current/conf/. /var/lib/tomcat/conf/
sudo chown -R root:tomcat /var/lib/tomcat/conf
sudo find /var/lib/tomcat/conf -type d -exec chmod 0750 {} +
sudo find /var/lib/tomcat/conf -type f -exec chmod 0640 {} +
```

قبل از Extract باید Checksum نتیجه OK بدهد. Hash از Apache دریافت می‌شود و در این راهنما ساخته یا ثابت نشده است. SHA-512 خرابی فایل را تشخیص می‌دهد؛ دریافت هر دو فایل با HTTPS تأیید مستقل هویت ناشر نیست. سازمان نیازمند این اطمینان باید Signature جداگانه .asc را با کلید Release Manager و Fingerprint مستقل تأییدشده بررسی کند. Good Signature از کلید تأییدنشده کافی نیست.

پس از خروج این Release از Mirror، آخرین Patch Stable پشتیبانی‌شده و URL رسمی آن را انتخاب کنید؛ Integrity را دور نزنید و Archive قدیمی را خودکار جایگزین نکنید. Copy به CATALINA_BASE فقط برای نصب اولیه است. در Upgrade بعدی تغییرات Configuration پیش‌فرض را مقایسه و تنظیم محلی بررسی‌شده را حفظ کنید.

[CATALINA_HOME، CATALINA_BASE و Environment اجرا](https://tomcat.apache.org/tomcat-11.0-doc/RUNNING.txt)

[بررسی Release و Signature مورد اعتماد Apache](https://www.apache.org/info/verification.html)

### نوشتن Environment سرویس با مسیر واقعی Java

```bash
JAVA_INSTALL_HOME="$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")"
sudo tee /etc/tomcat/tomcat.env >/dev/null <<EOF
JAVA_HOME=$JAVA_INSTALL_HOME
CATALINA_HOME=/opt/tomcat/current
CATALINA_BASE=/var/lib/tomcat
CATALINA_TMPDIR=/var/lib/tomcat/temp
CATALINA_OPTS="-Xms512m -Xmx2g -XX:+ExitOnOutOfMemoryError -Djava.awt.headless=true"
EOF
sudo chown root:tomcat /etc/tomcat/tomcat.env
sudo chmod 0640 /etc/tomcat/tomcat.env
sudo -u tomcat env JAVA_HOME="$JAVA_INSTALL_HOME" \
  CATALINA_HOME=/opt/tomcat/current CATALINA_BASE=/var/lib/tomcat \
  /opt/tomcat/current/bin/version.sh
sudo -u tomcat test ! -w /var/lib/tomcat/conf/server.xml
sudo -u tomcat test ! -w /var/lib/tomcat/webapps
sudo -u tomcat test -w /var/lib/tomcat/work
```

Heap برابر 2 GiB بودجه اولیه Host نمونه 4 GiB است. برای Metaspace، JIT، Stack، Direct Buffer، Nginx و OS حافظه باقی بگذارید. ExitOnOutOfMemoryError امکان Restart JVM خراب را می‌دهد و درمان Memory Leak نیست. EnvironmentFile اسکریپت Shell نیست. Secret در آن نگذارید و setenv.sh یا JRE_HOME متعارض ایجاد نکنید.

## ۶. اجرای Tomcat به‌صورت سرویس امن systemd

![مدیریت سرویس غیر root تامکت، JVM جاوا 21، برنامه، Journal و شروع خودکار توسط systemd لینوکس](/assets/img/articles/content/apache-tomcat-systemd-service.png)

مدیریت سرویس غیر root تامکت، JVM جاوا 21، برنامه، Journal و شروع خودکار توسط systemd لینوکس

فایل /etc/systemd/system/tomcat.service را با Unit کامل زیر بسازید. catalina.sh run، JVM را Foreground نگه می‌دارد تا systemd Process واقعی را دنبال کند. PID File یا Wrapper از نوع Fork نیاز نیست. systemd برای Stop/Restart، SIGTERM می‌فرستد؛ Shutdown Hook جاوا حتی با غیرفعال‌بودن Port خاموشی، Tomcat را متوقف می‌کند.

```bash
sudoedit /etc/systemd/system/tomcat.service
```

```ini
[Unit]
Description=Apache Tomcat Java Web Container
Wants=network-online.target
After=network-online.target
RequiresMountsFor=/opt/tomcat /var/lib/tomcat /var/log/tomcat
StartLimitIntervalSec=60
StartLimitBurst=5

[Service]
Type=simple
User=tomcat
Group=tomcat
EnvironmentFile=/etc/tomcat/tomcat.env
WorkingDirectory=/var/lib/tomcat
ExecStart=/opt/tomcat/current/bin/catalina.sh run
Restart=on-failure
RestartSec=5s
SuccessExitStatus=143
TimeoutStopSec=60s
KillSignal=SIGTERM
KillMode=mixed
UMask=0027
LimitNOFILE=16384
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=true
ReadWritePaths=/var/lib/tomcat/work /var/lib/tomcat/temp /var/lib/tomcat/data /var/log/tomcat
RestrictSUIDSGID=true
CapabilityBoundingSet=
AmbientCapabilities=
StandardOutput=journal
StandardError=journal
SyslogIdentifier=tomcat

[Install]
WantedBy=multi-user.target
```

- ProtectSystem=strict فایل‌سیستم را در Namespace سرویس Read-only می‌کند؛ ReadWritePaths فقط مسیر Runtime موجود را باز می‌کند. Ownership همچنان لازم است. داده برنامه استثنای مستند می‌خواهد، نه /opt یا conf قابل نوشتن.
- ProtectHome مسیر Home را مخفی می‌کند؛ JDK، Certificate و فایل لازم را زیر /home نگذارید. PrivateTmp، /tmp و /var/tmp را جدا می‌کند؛ CATALINA_TMPDIR مسیر صریح فایل موقت JVM است.
- NoNewPrivileges، Capability Set خالی و RestrictSUIDSGID افزایش Privilege را محدود می‌کنند. Port بالای 1024 به Capability برای Bind نیاز ندارد. JIT جاوا حافظه قابل اجرا می‌خواهد؛ MemoryDenyWriteExecute عمداً فعال نشده است.
- LimitNOFILE=16384 سقف نمونه بالاتر از بودجه Connection است؛ Socket دیتابیس و فایل برنامه را در Sizing لحاظ کنید. بدون تست Native Library و ابزار تشخیص JVM، Task یا Syscall Filter محدود اعمال نکنید.
- network-online اجرای سرویس را پس از Wait-online تنظیم‌شده مرتب می‌کند و سلامت DNS/Database را تضمین نمی‌کند. Retry اتصال برنامه همچنان لازم است. Start Limit از Restart Storm جلوگیری می‌کند.

### ارسال لاگ Container به Journal

روی Instance جدید، /var/lib/tomcat/conf/logging.properties را با JULI فقط Console زیر جایگزین کنید. لاگ Container بدون فایل تکراری JULI به journalctl می‌رسد. AccessLogValve همچنان فایل Access جدا می‌نویسد؛ Framework لاگ برنامه، Console یا فایل محافظت‌شده خود را نیاز دارد.

```bash
sudoedit /var/lib/tomcat/conf/logging.properties
```

```properties
handlers = java.util.logging.ConsoleHandler
.handlers = java.util.logging.ConsoleHandler
.level = INFO
java.util.logging.ConsoleHandler.level = INFO
java.util.logging.ConsoleHandler.formatter = org.apache.juli.OneLineFormatter
```

```bash
sudo systemd-analyze verify /etc/systemd/system/tomcat.service
# Install chapter 7 server.xml before starting this new instance.
sudo systemctl daemon-reload
sudo systemctl enable --now tomcat
sudo systemctl status tomcat --no-pager
sudo systemctl restart tomcat
sudo journalctl -u tomcat -n 100 --no-pager
sudo systemd-analyze security tomcat.service
sudo systemctl show tomcat -p User -p Group -p MainPID -p LimitNOFILE -p ReadWritePaths
```

پس از نصب فصل ۷، انتظار active (running)، User برابر tomcat و Listener روی 127.0.0.1:8080 را دارید. Active در Type=simple به‌تنهایی آمادگی HTTP نیست. در رسیدن به Start Limit، خطا را اصلاح و systemctl reset-failed tomcat و Start را اجرا کنید. Security Score ابزار تشخیص است، نه گواهی امنیت؛ استثناهای سازگار با JVM بر آن اثر دارند.

[گزینه‌های اجرای systemd، Sandbox فایل‌سیستم و لاگ در Ubuntu 24.04](https://manpages.ubuntu.com/manpages/noble/man5/systemd.exec.5.html)

[چرخه سرویس systemd، Restart و Stop](https://manpages.ubuntu.com/manpages/noble/man5/systemd.service.5.html)

[لاگ JULI و Application در Tomcat](https://tomcat.apache.org/tomcat-11.0-doc/logging.html)

## ۷. امن‌سازی Enterprise

![دفاع چندلایه Tomcat: Firewall، TLS در Nginx، Backend محلی، Configuration متعلق به root و سرویس محدود systemd](/assets/img/articles/content/apache-tomcat-security-hardening.png)

دفاع چندلایه Tomcat: Firewall، TLS در Nginx، Backend محلی، Configuration متعلق به root و سرویس محدود systemd

### مرز سیستم‌عامل و Deployment

برای هر مرز اعتماد حساب غیر root جدا، sudo کنترل‌شده برای Operator، OS/JDK به‌روز و Firewall محدود استفاده کنید. مسیر Release و Symlink مربوط به current برای User سرویس غیرقابل تغییر باشند. Permission لینوکس و Namespace systemd از Configuration حفاظت می‌کنند، اما برنامه‌های نامطمئن در یک JVM را از هم جدا نمی‌کنند. برای Trust Domain جدا، Instance یا VM جدا بسازید. Tomcat 11 پشتیبانی Java SecurityManager را حذف کرده؛ دستور قدیمی catalina.policy یا -security اعمال نکنید.

### برنامه مدیریتی بسته را Deploy نکنید

CATALINA_BASE/webapps خالی ساخته شد. ROOT، docs، examples، manager یا host-manager را از CATALINA_HOME/webapps به آن Copy نکنید. Archive می‌تواند فایل محافظت‌شده آن‌ها را نگه دارد بدون انتشار، چون Host از appBase جدا در BASE استفاده می‌کند. BASE/conf/Catalina/localhost را برای Descriptor اشاره‌کننده بررسی کنید. در نصب موجود، فقط برنامه و Descriptor غیرضروری تأییدشده را با Backup در Change کنترل‌شده حذف کنید؛ همه Webappها را پاک نکنید.

```bash
sudo ls -la /var/lib/tomcat/webapps /var/lib/tomcat/conf/Catalina/localhost
sudo -u tomcat test ! -w /opt/tomcat/current/bin/catalina.sh
sudo -u tomcat test ! -w /etc/tomcat/tomcat.env
sudo stat -c "%U:%G %a %n" /var/lib/tomcat/conf/server.xml /var/lib/tomcat/webapps /var/log/tomcat
```

اگر Manager صریحاً لازم است، Listener/Host مدیریت جدا در Private Network با Firewall/VPN Allowlist، TLS، حساب مشخص و حداقل Role داشته باشید. manager-script را از Role مرورگر manager-gui جدا محدود کنید. Valve فقط Loopback کافی نیست اگر Reverse Proxy عمومی محلی به آن برسد. این Baseline برنامه مدیریتی Deploy نمی‌کند و Nginx مسیر عمومی آن را نیز می‌بندد. JMX فعال نیست؛ JMX راه‌دور به Transport رمزنگاری و احراز هویت‌شده و Port محدود نیاز دارد.

### server.xml کامل و امن برای Proxy محلی

فایل /var/lib/tomcat/conf/server.xml زیر را روی Instance جدید به‌جای Default کپی‌شده بنویسید. فقط یک HTTP Connector دارد و AJP ندارد. port=-1، Listener خاموشی را غیرفعال می‌کند؛ از systemctl stop استفاده کنید. نام، Scheme و Port ثابت Proxy فقط برای همین توپولوژی HTTPS تک‌دامنه مناسب است.

```bash
sudoedit /var/lib/tomcat/conf/server.xml
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Server port="-1">
  <Listener className="org.apache.catalina.core.JreMemoryLeakPreventionListener" />
  <Listener className="org.apache.catalina.core.ThreadLocalLeakPreventionListener" />
  <Service name="Catalina">
    <Connector address="127.0.0.1" port="8080"
               protocol="org.apache.coyote.http11.Http11NioProtocol"
               connectionTimeout="20000" maxThreads="200"
               maxConnections="2048" acceptCount="100"
               maxParameterCount="1000" maxPostSize="2097152"
               maxHttpRequestHeaderSize="8192"
               allowTrace="false" enableLookups="false"
               URIEncoding="UTF-8" xpoweredBy="false"
               proxyName="tomcat.example.com" proxyPort="443"
               scheme="https" secure="true" />
    <Engine name="Catalina" defaultHost="localhost">
      <Host name="localhost" appBase="webapps"
            autoDeploy="false" deployOnStartup="true"
            unpackWARs="false" deployXML="false">
        <Valve className="org.apache.catalina.valves.RemoteIpValve"
               internalProxies="127.0.0.1/32"
               remoteIpHeader="X-Forwarded-For"
               requestAttributesEnabled="true" />
        <Valve className="org.apache.catalina.valves.ErrorReportValve"
               showReport="false" showServerInfo="false" />
        <Valve className="org.apache.catalina.valves.AccessLogValve"
               directory="logs" prefix="access." suffix=".log"
               rotatable="true" maxDays="14"
               requestAttributesEnabled="true"
               pattern="%a %t &quot;%m %U %H&quot; %s %b %D" />
      </Host>
    </Engine>
  </Service>
</Server>
```

RemoteIpValve فقط Peer محلی را با سینتکس CIDR مستند برای 11.0.26 اعتماد می‌کند. Nginx مقدار X-Forwarded-For را با آدرس Client اتصال TCP جایگزین می‌کند. Scheme و Port در Connector ثابت‌اند، بنابراین Valve از Forwarded Scheme/Host/Port استفاده نمی‌کند. Process محلی دارای دسترسی 8080 هنوز می‌تواند Header IP جعل کند؛ Loopback مرز اعتماد Host است، نه احراز هویت Process. این Connector HTTPS ثابت را برای HTTP عمومی مستقیم استفاده نکنید.

autoDeploy=false، Hot Deployment پس‌زمینه را می‌بندد؛ deployOnStartup=true در Restart، Artifact بررسی‌شده را بارگذاری می‌کند. unpackWARs=false اجرای WAR با Read-only را ممکن می‌کند و ممکن است Performance را کم کند؛ برنامه را تست کنید. deployXML=false، META-INF/context.xml داخلی را نادیده می‌گیرد؛ Context/JNDI لازم را در Descriptor بررسی‌شده متعلق به root زیر conf/Catalina/localhost منتقل کنید. برنامه نیازمند مسیر Expanded را Operator پیش از Start با Ownership فقط خواندنی Extract کند، نه با Write دادن به webapps.

تنظیم Thread/Connection و اندازه Request بودجه اولیه‌اند، نه ظرفیت عمومی. maxPostSize پردازش Parameter فرم را محدود می‌کند و سقف کلی Upload نیست؛ Multipart Limit را در برنامه و Body Limit را در Nginx اعمال کنید. Access Log، Query String و Credential را ثبت نمی‌کند، اما Path می‌تواند شناسه حساس داشته باشد. Permission، Rotation، Quota و جمع‌آوری مرکزی Log را طبق Policy مدیریت کنید.

### قرار دادن کنترل در لایه مسئول

- Tomcat: DefaultServlet را در conf/web.xml به‌صورت readonly و بدون Directory Listing حفظ کنید؛ crossContext، privileged و allowLinking بدون بررسی صریح فعال نشوند. ErrorReportValve گزارش Stack و نسخه Container را مخفی می‌کند؛ Server Disclosure را فعال نکنید و xpoweredBy=false بماند.
- Application: Authentication/Authorization، CSRF، Validation، Query آماده، Error Page امن، جلوگیری از Session Fixation و Policy کوکی Secure/HttpOnly/SameSite. SameSite را مطابق SSO انتخاب و Redirect پشت HTTPS را تست کنید.
- Nginx: TLS، Routing نام عمومی Host، محدودیت Body/Time، Header پاسخ و محدودیت مسیر مدیریت. WAF/Rate Limit کنترل تست‌شده جداست و Reverse Proxy ساده خودکار آن را فراهم نمی‌کند.
- OS/Network: Privilege حساب، Code/Config فقط خواندنی، Policy ورودی/خروجی، SELinux/AppArmor و Backup محافظت‌شده. مخفی‌کردن نسخه دفاع تکمیلی است، نه جایگزین Patch.

[Security Considerations رسمی Tomcat](https://tomcat.apache.org/tomcat-11.0-doc/security-howto.html)

[گزینه‌ها و محدودیت HTTP Connector](https://tomcat.apache.org/tomcat-11.0-doc/config/http.html)

[Deployment در Host و رفتار WAR](https://tomcat.apache.org/tomcat-11.0-doc/config/host.html)

[Valveهای Remote IP، Error Report و Access Log](https://tomcat.apache.org/tomcat-11.0-doc/config/valve.html)

[تنظیم Port خاموشی Server](https://tomcat.apache.org/tomcat-11.0-doc/config/server.html)

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now tomcat
sudo systemctl restart tomcat
sudo systemctl status tomcat --no-pager
sudo journalctl -u tomcat -n 100 --no-pager
sudo ss -lntp | grep -E ":(8080|8005|8009)\b"
curl -I --max-time 10 http://127.0.0.1:8080/
```

در این مرحله پاسخ 404 برای / طبیعی است چون ROOT Deploy نشده است. این فقط پاسخ HTTP را اثبات می‌کند، نه سلامت برنامه. فقط 127.0.0.1:8080 و بدون Listener روی 8005/8009 انتظار می‌رود. مسیر manager و host-manager را پس از Deployment و از Proxy عمومی دوباره تست کنید.

## ۸. Reverse Proxy با Nginx و HTTPS

Nginx و Tomcat روی یک Host هستند. قبل از صدور Certificate، Ruleهای HTTP/HTTPS فصل ۹ را باز و دامنه واقعی را به Proxy متصل کنید. tomcat.example.com دامنه مستندات است و برای صدور Certificate شما قابل استفاده نیست. روی Host جدا از آدرس خصوصی Backend، Firewall با Source دقیق Proxy و در نیاز Policy از TLS/mTLS معتبر میان Hostها استفاده کنید؛ Backend عمومی Plaintext نسازید.

### نصب Ubuntu و Bootstrap با HTTP

```bash
sudo apt install -y nginx certbot
nginx -v
sudo cp -a /etc/nginx "/etc/nginx.before-tomcat.$(date -u +%Y%m%dT%H%M%SZ)"
sudo install -d -o root -g root -m 0755 /var/www/letsencrypt/.well-known/acme-challenge
# Dedicated new host only: remove the reviewed packaged default symlink.
if [ -L /etc/nginx/sites-enabled/default ]; then
  sudo unlink /etc/nginx/sites-enabled/default
fi
sudoedit /etc/nginx/conf.d/tomcat.conf
```

ابتدا Configuration موقت HTTP زیر را نصب کنید. به Certificate ناموجود اشاره نکنید. Snippetها از conf.d/*.conf داخل Context مربوط به http در Nginx Include می‌شوند؛ با nginx -T بررسی کنید. روی Proxy مشترک، Conflict نام Server/Default Listener را بدون غیرفعال‌کردن سایت دیگر برطرف کنید.

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    server_tokens off;
    return 444;
}
server {
    listen 80;
    listen [::]:80;
    server_name tomcat.example.com;
    server_tokens off;
    location ^~ /.well-known/acme-challenge/ {
        root /var/www/letsencrypt;
        default_type text/plain;
        try_files $uri =404;
    }
    location / { return 404; }
}
```

```bash
sudo nginx -t
sudo systemctl enable --now nginx
sudo systemctl reload nginx
sudo certbot certonly --webroot -w /var/www/letsencrypt \
  --cert-name tomcat.example.com -d tomcat.example.com
sudo certbot certificates
```

دامنه را در همه فرمان و Configuration جایگزین کنید. Certbot در صورت نیاز اطلاعات Account و موافقت را می‌پرسد. Lineage و مسیر واقعی fullchain.pem/privkey.pem را با certbot certificates بررسی کنید. HTTP-01 به Port عمومی 80 و DNS صحیح برای همه آدرس‌های اعلام‌شده نیاز دارد؛ اگر 80 مجاز نیست DNS-01 خودکار به‌کار ببرید. Private Key را محدود به root و Backup را رمزنگاری کنید.

### Configuration کامل نهایی HTTPS

پس از صدور Certificate، Bootstrap را با فایل کامل /etc/nginx/conf.d/tomcat.conf زیر جایگزین کنید. با Nginx در Ubuntu 24.04 سازگار است و ssl_reject_handshake از نسخه 1.19.4+ را استفاده می‌کند. TLS در Nginx پایان می‌یابد؛ HTTP روی Loopback در همان Host می‌ماند. proxy_pass بدون URI مسیر /app را حفظ می‌کند و Context Path را بازنویسی نمی‌کند.

```nginx
# Included inside the http context; requires existing certificate files.
log_format tomcat_edge '$remote_addr [$time_local] "$request_method $uri $server_protocol" '
                       'status=$status bytes=$body_bytes_sent rt=$request_time '
                       'upstream=$upstream_addr us=$upstream_status urt=$upstream_response_time';

server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    server_tokens off;
    return 444;
}
server {
    listen 443 ssl default_server;
    listen [::]:443 ssl default_server;
    server_name _;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_reject_handshake on;
    return 444;
}
server {
    listen 80;
    listen [::]:80;
    server_name tomcat.example.com;
    server_tokens off;
    location ^~ /.well-known/acme-challenge/ {
        root /var/www/letsencrypt;
        default_type text/plain;
        try_files $uri =404;
    }
    location / { return 301 https://tomcat.example.com$request_uri; }
}
server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name tomcat.example.com;
    server_tokens off;
    ssl_certificate /etc/letsencrypt/live/tomcat.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tomcat.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_session_cache shared:TomcatTLS:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;
    if ($host != tomcat.example.com) { return 421; }
    if ($ssl_server_name != tomcat.example.com) { return 421; }

    client_max_body_size 10m;
    client_body_timeout 30s;
    add_header Strict-Transport-Security "max-age=604800" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    access_log /var/log/nginx/tomcat.access.log tomcat_edge;
    error_log /var/log/nginx/tomcat.error.log warn;

    location ~ ^/(manager|host-manager)(/|$) { return 404; }
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header Host tomcat.example.com;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host tomcat.example.com;
        proxy_set_header X-Forwarded-Port 443;
        proxy_set_header X-Forwarded-By "";
        proxy_set_header Forwarded "";
        proxy_hide_header X-Powered-By;
        proxy_hide_header Strict-Transport-Security;
        proxy_hide_header X-Content-Type-Options;
        proxy_hide_header X-Frame-Options;
        proxy_hide_header Referrer-Policy;
        proxy_connect_timeout 5s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }
}
```

در این لبه مستقیم اینترنت، Forwarded Header را جایگزین کنید و X-Forwarded-For ارسالی Client را Append نکنید. Forwarded را پاک کنید تا Framework مستقل به مقدار جعلی اعتماد نکند. اگر CDN/Load Balancer قبل از Nginx است، Origin را به آن محدود و set_real_ip_from را برای Range دقیق منتشرشده با real_ip_header درست تنظیم کنید؛ هرگز 0.0.0.0/0 را Trust نکنید. $remote_addr و لاگ Tomcat را با Header جعلی تست کنید. پردازش Forwarding مستقل در Container و برنامه را همزمان فعال نکنید.

HSTS اینجا از هفت روز شروع می‌شود؛ پس از تست HTTPS و تمدید آن را افزایش دهید. includeSubDomains یا preload بدون بررسی سازمانی اضافه نشود. SAMEORIGIN ممکن است با Embedding بین سایت‌ها تعارض داشته باشد؛ آگاهانه تغییر دهید. Content-Security-Policy را مطابق Resource برنامه طراحی، ابتدا Report-only تست و از Policy کپی‌شده خراب‌کننده Script/SSO دوری کنید. چهار Header تنظیم‌شده متعلق به Proxy است و نسخه تکراری Upstream مخفی می‌شود. X-XSS-Protection قدیمی استفاده نشده است.

Timeoutها فاصله بی‌فعالیتی I/O در Upstream هستند، نه Deadline کامل End-to-end. با بودجه برنامه و Database تطبیق دهید و Request Thread نامحدود نسازید. Baseline برای HTTP معمولی است. Endpointهای WebSocket/SSE به Upgrade/Connection یا Buffering/Timeout بررسی‌شده در Location مشخص نیاز دارند، نه Upgrade عمومی بدون محدودیت.

```bash
sudo nginx -t
sudo systemctl reload nginx
curl -I http://tomcat.example.com/app/
curl -I https://tomcat.example.com/app/
curl -I https://tomcat.example.com/manager/html
curl -I -H 'X-Forwarded-For: 198.51.100.123' https://tomcat.example.com/app/
openssl s_client -connect tomcat.example.com:443 -servername tomcat.example.com \
  -verify_hostname tomcat.example.com -verify_return_error </dev/null
sudo tail -n 20 /var/log/nginx/tomcat.access.log
sudo tail -n 20 /var/log/tomcat/access.*.log
```

تست دامنه عمومی را بعد از Deployment فصل ۱۰ و ترجیحاً از Client جدا اجرا کنید. انتظار Redirect HTTP، زنجیره HTTPS معتبر، پاسخ برنامه، 404 مدیریت و IP واقعی Client در هر دو Log به‌جای آدرس جعلی را دارید. HEAD در برنامه فاقد پشتیبانی ممکن است 405 بدهد؛ GET را تست کنید. curl -k تست پذیرش TLS نیست.

### تمدید Certificate و Reload Hook

```bash
sudo install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh >/dev/null <<'EOF'
#!/bin/sh
set -eu
/usr/sbin/nginx -t
/usr/bin/systemctl reload nginx
EOF
sudo chown root:root /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
sudo chmod 0750 /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
sudo systemctl enable --now certbot.timer
systemctl list-timers --all | grep certbot
sudo certbot renew --dry-run
# Exercise the deploy hook separately; dry-run does not normally run deploy hooks.
sudo /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
```

مسیر Binary و Timer مربوط به روش بسته Ubuntu است. روی توزیع یا روش نصب دیگر Scheduler واقعی را بررسی کنید؛ Job تمدید تکراری نسازید. انقضای Certificate، خطای تمدید و Reload Hook را پایش کنید. Location مربوط به ACME در HTTP را هنگام Redirect و تغییر بعدی حفظ کنید.

[مرجع Nginx برای Forwarding، URI و Timeout](https://nginx.org/en/docs/http/ngx_http_proxy_module.html)

[TLS و رفتار Handshake پیش‌فرض در Nginx](https://nginx.org/en/docs/http/ngx_http_ssl_module.html)

[Header پاسخ و Inheritance در Nginx](https://nginx.org/en/docs/http/ngx_http_headers_module.html)

[Source مورد اعتماد Real IP در Nginx](https://nginx.org/en/docs/http/ngx_http_realip_module.html)

[پیش‌نیاز Challengeهای Let’s Encrypt](https://letsencrypt.org/docs/challenge-types/)

[Webroot، تمدید و Hook در Certbot](https://eff-certbot.readthedocs.io/en/stable/using.html)

## ۹. Firewall؛ فقط لبه را منتشر کنید

Ruleهای Firewall را قبل از ACME فصل ۸ اعمال کنید. دسترسی روی SSH واقعی را حفظ و پیش از Enable/Reload، Session دوم را تست کنید. نمونه SSH روی 22 است. Allow موجود، Rich/Direct Rule، IPv6، Cloud Security Group و NAT را بررسی کنید؛ افزودن Deny همه Ruleهای قبلی را اصلاح نمی‌کند.

### UFW در Ubuntu

```bash
sudo apt install -y ufw
sudo ufw status numbered
sudo ufw allow 22/tcp comment "SSH management - restrict source in production"
sudo ufw allow 80/tcp comment "ACME and HTTPS redirect"
sudo ufw allow 443/tcp comment "Nginx HTTPS"
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw deny 8080/tcp
sudo ufw deny 8009/tcp
sudo ufw deny 8005/tcp
sudo ufw enable
sudo ufw status verbose
```

پس از مشخص‌شدن CIDR واقعی مدیریت، SSH عمومی را با Rule محدود به Source جایگزین کنید و قبل از حذف Rule عمومی دسترسی را تست کنید. Allow عمومی قدیمی 8080 را از خروجی ufw status numbered حذف کنید. UFW معمولاً Loopback را مجاز می‌داند؛ Backend برای Nginx محلی قابل دسترس می‌ماند. Policy خروجی بالا Baseline نصب است؛ در سازمان خروجی را مطابق Dependency برنامه محدود کنید.

### گزینه جایگزین firewalld در خانواده RHEL

```bash
sudo dnf install -y firewalld
sudo systemctl enable --now firewalld
sudo firewall-cmd --get-active-zones
# Assumes the public-facing interface is in public; use its actual zone.
sudo firewall-cmd --permanent --zone=public --add-service=ssh
sudo firewall-cmd --permanent --zone=public --add-service=http
sudo firewall-cmd --permanent --zone=public --add-service=https
sudo firewall-cmd --permanent --zone=public --remove-port=8080/tcp
sudo firewall-cmd --permanent --zone=public --remove-port=8009/tcp
sudo firewall-cmd --permanent --zone=public --remove-port=8005/tcp
sudo firewall-cmd --reload
sudo firewall-cmd --zone=public --list-all
sudo firewall-cmd --zone=public --list-rich-rules
```

remove-port برای Rule ناموجود ممکن است NOT_ENABLED بدهد؛ نتیجه مؤثر را بررسی کنید. Target مربوط به Zone نباید ACCEPT باشد و هیچ Service، Rich Rule یا Trusted Zone نباید Backend را باز کند. طراحی تک‌Host اصلاً Allow برای 8080 نمی‌خواهد. روی Host از UFW یا firewalld استفاده کنید، نه هر دو. در RHEL، Nginx را از مخزن پشتیبانی‌شده توزیع نصب، Include و Scheduler گواهی را تطبیق و SELinux را پیش از تست Proxy بررسی کنید.

```bash
sudo ss -lntp | grep -E ":(22|80|443|8080|8005|8009)\b"
# From an independent external client using your real domain:
curl -I --connect-timeout 5 https://tomcat.example.com/
curl -I --connect-timeout 5 http://tomcat.example.com:8080/
```

تلاش خارجی به Backend باید Fail شود و HTTPS به Nginx برسد. Bind روی 127.0.0.1 حتی با Firewall ضروری است؛ همچنین از Listener عمومی IPv6 تصادفی جلوگیری می‌کند.

[راهنمای Firewall در Ubuntu](https://ubuntu.com/server/docs/how-to/security/firewalls/)

[Service و Zoneهای firewalld در RHEL](https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters)

## ۱۰. استقرار WAR بررسی‌شده Java

app.war را در CI مورد اعتماد برای Java 21 و Framework سازگار با Jakarta Servlet 6.1 بسازید، Dependency را اسکن، Digest/Signature تأییدشده را بررسی و فایل را به Home اپراتور منتقل کنید. در ادامه ./app.war همان Artifact واقعی و تأییدشده است. WAR ساختگی یا Checksum نمونه ثابت ارائه نشده است. Operator با sudo Deploy می‌کند؛ خود Tomcat نمی‌تواند Code جدید مستقر کند.

```bash
cd ~
test -f ./app.war
sha256sum ./app.war
unzip -t ./app.war
# Compare the digest above to the independently approved CI release record.
sudo systemctl stop tomcat
sudo install -o root -g tomcat -m 0640 ./app.war /var/lib/tomcat/webapps/app.war
sudo -u tomcat test -r /var/lib/tomcat/webapps/app.war
sudo -u tomcat test ! -w /var/lib/tomcat/webapps/app.war
sudo systemctl start tomcat
sudo journalctl -u tomcat -n 100 --no-pager
curl -I -H 'Host: tomcat.example.com' http://127.0.0.1:8080/app/
curl -I https://tomcat.example.com/app/
```

app.war به /app و ROOT.war به / نگاشت می‌شود. با autoDeploy=false، سرویس در حال اجرا WAR کپی‌شده را تا Restart نمی‌بیند. در روش Archive، unpackWARs=false بماند. اگر برنامه فایل Expanded می‌خواهد، WAR تأییدشده را با root در مسیر تمیز app Extract، Directory را 0750 و File را 0640 با root:tomcat تنظیم و Directory را بدون app.war رقیب Deploy کنید. پیش از Extract با Privilege، Archive و مسیر فایل را اعتبارسنجی کنید. Upload متغیر در درخت Deployment ذخیره نشود.

لاگ Start را برای خطای Context بررسی و Health Endpoint واقعی، مسیر User احراز هویت‌شده، Data Access و Authorization ردشده را تست کنید. 200 فایل ثابت سلامت Database را اثبات نمی‌کند. اگر /app/ دارای Index نیست، Route مستند برنامه را استفاده کنید؛ هر 404 را خرابی سرویس ندانید. Secret خارج از WAR در مکانیزم تأییدشده و محدود به Instance نگهداری شود. Descriptor خارجی Context متعلق به root برای Resource بررسی‌شده قابل استفاده است.

[مرجع رسمی استقرار WAR و Context](https://tomcat.apache.org/tomcat-11.0-doc/deployer-howto.html)

## ۱۱. مانیتورینگ و عیب‌یابی

```bash
systemctl status tomcat --no-pager
sudo journalctl -u tomcat -n 100 --no-pager
sudo journalctl -u tomcat -f
sudo ss -lntp
curl -I http://127.0.0.1:8080/
sudo systemctl show tomcat -p MainPID -p NRestarts -p MemoryCurrent
free -h
df -h
sudo journalctl -k --since '1 hour ago'
sudo tail -n 50 /var/log/nginx/tomcat.error.log
```

روی سرویس Fail/Restart، Health/Latency برنامه، نرخ 5xx، Heap/GC، RSS بومی، Thread و File Descriptor، رشد دیسک/Log و انقضای TLS Alert بگذارید. Log مرکزی را محافظت و Retention پایدار Journal را مطابق بودجه دیسک تنظیم کنید؛ Persistence پیش‌فرض میان توزیع‌ها تفاوت دارد. JMX بدون Authentication یا Debug Port منتشر نشود. قبل از تعیین Threshold، Baseline ثبت کنید.

### خطای Start یا Restart Loop

نشانه: وضعیت failed یا start-limit-hit. علت: XML یا Option JVM نامعتبر، مسیر Java ناموجود یا مسیر Unit ناخوانا. با فرمان زیر تشخیص دهید. راه‌حل: اولین Exception را اصلاح، JDK و XML را بررسی، Configuration بررسی‌شده را بازگردانید و سپس Reset/Restart کنید؛ صرفاً Retry Limit را بالا نبرید.

```bash
sudo journalctl -u tomcat -b -n 150 --no-pager
sudo systemd-analyze verify /etc/systemd/system/tomcat.service
sudo systemctl cat tomcat
sudo cat /etc/tomcat/tomcat.env
sudo systemctl reset-failed tomcat
sudo systemctl restart tomcat
```

### ناسازگاری API یا Bytecode جاوا

نشانه: UnsupportedClassVersionError یا ClassNotFoundException برای javax.servlet. علت: Artifact برای JVM جدیدتر کامپایل شده یا Dependency از Java EE روی Container Jakarta است. مسیر JVM سرویس و Exception شروع Context را بررسی کنید. راه‌حل: برای Release انتخابی Java و Dependency سازگار Jakarta بازسازی یا Branch پشتیبانی‌شده سازنده را انتخاب کنید؛ برای پوشاندن ناسازگاری، JAR قدیمی Servlet API به lib تامکت اضافه نکنید.

```bash
java -version
sudo journalctl -u tomcat -b --no-pager | grep -E "UnsupportedClassVersion|ClassNotFound|NoClassDefFound|javax.servlet"
sudo grep JAVA_HOME /etc/tomcat/tomcat.env
```

### Permission Denied یا فایل‌سیستم Read-only

نشانه: عدم Write لاگ، فایل موقت یا Upload. علت: Ownership اشتباه، نبود Traverse روی Parent یا مسیر خارج از ReadWritePaths. Permission و Unit مؤثر را بررسی کنید. راه‌حل: داده متغیر را به data/work/temp/log مستند منتقل و فقط Ownership لازم بدهید؛ در ضرورت استثنای محدود و بررسی‌شده اضافه کنید. chmod 777 یا chown کل Release به tomcat نکنید.

```bash
namei -l /var/lib/tomcat/data
sudo -u tomcat test -w /var/lib/tomcat/data
sudo systemctl show tomcat -p ReadWritePaths -p ProtectSystem -p ProtectHome
sudo journalctl -u tomcat -n 100 --no-pager
```

### Port از قبل اشغال است

نشانه: BindException یا Address already in use. علت: Tomcat/JVM یا سرویس دیگر 8080 را گرفته است. PID مربوط به Listener را بررسی کنید. راه‌حل: Instance متعارض را شناسایی و از Service Manager خودش متوقف یا Connector و Upstream را هماهنگ تغییر دهید؛ Process ناشناس را Kill نکنید.

```bash
sudo ss -lntp "sport = :8080"
systemctl list-units --type=service | grep -i tomcat
ps -eo user,pid,args | grep "[o]rg.apache.catalina.startup.Bootstrap"
```

### HTTP 403 یا 404

نشانه: پاسخ می‌رسد ولی Route ممنوع یا ناموجود است. علت: Authorization/Valve، Context ناموجود یا Fail، Context Path اشتباه یا مسدودسازی عمدی مدیریت. /app/ مستقیم و Proxy، Deployment Log و Route برنامه را بررسی کنید. راه‌حل: Route/Deployment یا Rule مجاز Authorization را اصلاح کنید؛ برای رفع 404، Manager فعال یا Authentication غیرفعال نشود. / خالی در این Baseline عمداً 404 می‌دهد.

```bash
curl -i http://127.0.0.1:8080/app/
curl -i https://tomcat.example.com/app/
sudo journalctl -u tomcat -n 150 --no-pager
sudo ls -l /var/lib/tomcat/webapps
```

### خطای Nginx 502 Bad Gateway

نشانه: 502 عمومی با Nginx فعال. علت: Backend متوقف، Upstream اشتباه، Denial اتصال یا پاسخ نامعتبر Backend. Error Log، Backend مستقیم و Listener را بررسی کنید. راه‌حل: سلامت Backend و Upstream/Policy کنترل دسترسی را اصلاح کنید؛ افزایش proxy_read_timeout، Connection Refused را حل نمی‌کند. Upstream کند معمولاً 504 می‌دهد، نه 502.

```bash
sudo tail -n 100 /var/log/nginx/tomcat.error.log
curl -v --max-time 10 http://127.0.0.1:8080/app/
sudo ss -lntp "sport = :8080"
sudo nginx -t
```

### اتمام Heap یا OOM Kill توسط Kernel

نشانه: OutOfMemoryError، مکث طولانی GC یا Kill ناگهانی JVM. علت: فشار/Leak Heap، Concurrency زیاد، Allocation بومی یا اتمام حافظه Host/Cgroup. Kernel Log، RSS و Heap مؤثر را بررسی کنید. راه‌حل: Workload/Leak را اصلاح، Request را محدود و Heap/Native Headroom را اندازه‌گیری کنید. Xmx فقط در بودجه Host زیاد شود. سقف اختیاری MemoryMax باید از Heap به‌علاوه Native بیشتر و Load Test شده باشد.

```bash
sudo journalctl -k --since '1 hour ago' | grep -Ei 'oom|killed process'
sudo systemctl show tomcat -p MainPID -p MemoryCurrent -p MemoryMax
TOMCAT_PID="$(systemctl show tomcat -p MainPID --value)"
ps -o pid,rss,vsz,nlwp,args -p "$TOMCAT_PID"
# Use the exact JAVA_HOME recorded in tomcat.env for JDK tools.
sudo -u tomcat /usr/bin/jcmd "$TOMCAT_PID" GC.heap_info
```

اگر jcmd در /usr/bin/jcmd نصب نیست، از bin/jcmd مسیر واقعی JDK استفاده کنید. PrivateTmp و مسیر Attach در JDK می‌توانند ابزار Attach را محدود کنند؛ Attach ناموفق اثبات خرابی JVM نیست. Sandbox را برای تشخیص کلی حذف نکنید. Heap Dump ممکن است Secret داشته و دیسک را پر کند؛ -XX:+HeapDumpOnOutOfMemoryError فقط با HeapDumpPath محافظت‌شده زیر data، Quota و Retention فعال شود. بعد از تغییر CATALINA_OPTS، Restart کنید.

### Denial در SELinux یا AppArmor

[فرمان تشخیص jcmd در Java 21 و پیش‌نیاز Attach](https://docs.oracle.com/en/java/javase/21/docs/specs/man/jcmd.html)

نشانه: خطای Permission یا Proxy با وجود Unix Permission درست. علت: Mandatory Access Policy، Label اشتباه یا Profile فعال که Layout سفارشی را منع می‌کند. Audit/Kernel Log و Process Context را بررسی کنید. راه‌حل: Label درست را بازگردانید و کوچک‌ترین اصلاح Policy تأییدشده را اعمال کنید. Enforcement فعال بماند؛ خروجی audit2allow بررسی‌نشده نصب نشود.

```bash
# RHEL family:
getenforce
ps -eZ | grep -E 'nginx|java'
sudo ausearch -m AVC -ts recent
sudo ls -Zd /opt/tomcat /var/lib/tomcat /var/log/tomcat
getsebool httpd_can_network_connect
# Only when the denial and policy review justify outbound proxy connections:
sudo setsebool -P httpd_can_network_connect on

# Ubuntu:
sudo aa-status
sudo journalctl -k --since '1 hour ago' | grep -Ei 'apparmor|DENIED'
```

Boolean مربوط به SELinux اتصال خروجی گسترده‌تر سرویس HTTP را مجاز می‌کند و Sandbox تامکت نیست. پیش از فعال‌کردن، Policy محلی و کنترل خروجی شبکه را بررسی کنید. نصب tar سفارشی باید Domain واقعی Java/Tomcat در SELinux را تأیید کند؛ Copy فایل خودکار Domain محدود نمی‌سازد. Ubuntu فقط وقتی Profile واقعاً Loaded و Attached است، AppArmor را برای این Process اعمال می‌کند.

[Red Hat: Denial اتصال Proxy در SELinux](https://access.redhat.com/solutions/2980121)

### خطای راه‌اندازی Sandbox در systemd

نشانه: status=226/NAMESPACE، 200/CHDIR یا 203/EXEC. علت: مسیر ReadWritePaths/Mount ناموجود، Dependency زیر Home مسدود، نبود پشتیبانی Namespace یا Executable نامعتبر. Journal و مسیرها را بررسی کنید. راه‌حل: مسیر موردنظر را با Permission درست ایجاد/Mount و Executable خارج از Home استفاده کنید؛ فقط Directive ناسازگار را در Host تست‌شده تطبیق دهید. Container با Namespace محدود ممکن است طراحی Isolation متفاوت بخواهد.

```bash
sudo journalctl -u tomcat -b -n 100 --no-pager
namei -l /opt/tomcat/current/bin/catalina.sh
findmnt -T /var/lib/tomcat
sudo systemctl cat tomcat
sudo systemd-analyze verify /etc/systemd/system/tomcat.service
```

## ۱۲. ارتقای امن و Rollback

Maintenance Window تعیین، نسخه/JDK/Hash برنامه را ثبت و Patch مقصد را با همان Application و Proxy در Staging تست کنید. Migration Guide، Changelog و Security Advisory را بخوانید؛ Major Upgrade به بررسی API/Framework نیاز دارد. در چند نود، سازگاری Cluster/Session Serialization جدا بررسی شود. برای Change تک‌Host، Traffic را Drain و سرویس را Stop کنید. تغییر Symlink، Deployment بدون Downtime نیست.

```bash
set -euo pipefail
TOMCAT_PREVIOUS_RELEASE="$(sudo readlink -f /opt/tomcat/current)"
TOMCAT_BACKUP_DIR="/var/backups/tomcat/$(date -u +%Y%m%dT%H%M%SZ)"
sudo install -d -o root -g root -m 0700 "$TOMCAT_BACKUP_DIR"
sudo systemctl stop tomcat
printf '%s\n' "$TOMCAT_PREVIOUS_RELEASE" | sudo tee "$TOMCAT_BACKUP_DIR/previous-release.txt" >/dev/null
sudo cp -a /var/lib/tomcat/conf /var/lib/tomcat/webapps \
  /etc/tomcat /etc/systemd/system/tomcat.service "$TOMCAT_BACKUP_DIR/"
sudo cp -a /etc/nginx "$TOMCAT_BACKUP_DIR/nginx"
sudo tar -czf "$TOMCAT_BACKUP_DIR/application-data.tar.gz" -C /var/lib/tomcat data
sudo chmod -R go-rwx "$TOMCAT_BACKUP_DIR"
sudo ls -la "$TOMCAT_BACKUP_DIR"
```

Database/State خارجی را با روش Consistent Backup خودش پشتیبان‌گیری و Backup رمزنگاری‌شده را با Restore Access تأییدشده خارج Host نگهداری کنید. Certificate/Private Key جدا حفاظت شود. Backup مربوط به Config/WAR شامل Database راه‌دور نیست. Migration برنامه ممکن است Rollback را ناسازگار کند؛ قبل از تغییر Schema، Snapshot قابل Restore و تصمیم Forward Recovery/Rollback داشته باشید.

Archive جدید Stable تأییدشده را با مراحل Release فصل ۵ فقط در مسیر جدید /opt/tomcat/apache-tomcat-VERSION دانلود، Verify و Extract کنید. ساخت Account، Copy مربوط به BASE Config یا ایجاد اولیه current را تکرار نکنید. conf پیش‌فرض قدیم و جدید را مقایسه و تغییر لازم را در Copy Staging از conf محلی Merge کنید. Config و Artifact قبلی را به‌عنوان یک Rollback Set نگه دارید.

```bash
# Set this to the REAL release directory you already verified and installed.
read -r -p 'Verified new Tomcat release directory: ' TOMCAT_NEW_RELEASE
case "$TOMCAT_NEW_RELEASE" in
  /opt/tomcat/apache-tomcat-*) ;;
  *) echo 'Unexpected release path' >&2; exit 1 ;;
esac
sudo test -d "$TOMCAT_NEW_RELEASE"
sudo test -x "$TOMCAT_NEW_RELEASE/bin/catalina.sh"
sudo diff -ru "$TOMCAT_PREVIOUS_RELEASE/conf" "$TOMCAT_NEW_RELEASE/conf" || test "$?" -eq 1
# Complete the reviewed configuration merge BEFORE the switch.
sudo ln -s "$TOMCAT_NEW_RELEASE" /opt/tomcat/current.next
sudo mv -Tf /opt/tomcat/current.next /opt/tomcat/current
# daemon-reload is necessary only if unit/drop-in files changed.
sudo systemctl daemon-reload
sudo systemctl start tomcat
sudo systemctl status tomcat --no-pager
sudo journalctl -u tomcat -n 100 --no-pager
curl -I http://127.0.0.1:8080/app/
curl -I https://tomcat.example.com/app/
```

Variableهای این روند Upgrade در یک Bash Session هستند؛ در ازدست‌رفتن Session، مسیر قبلی را از فایل Backup بازیابی کنید. Upgrade فقط پس از تأیید Version، Authorization برنامه، Redirect/Cookieهای HTTPS، IP Client در Log، Monitoring و جریان واقعی داده پذیرفته شود. Release قبلی تا پایان Acceptance Window حفظ شود.

### Rollback وقتی Application/Data سازگار مانده‌اند

```bash
sudo systemctl stop tomcat
sudo ln -s "$TOMCAT_PREVIOUS_RELEASE" /opt/tomcat/current.rollback
sudo mv -Tf /opt/tomcat/current.rollback /opt/tomcat/current
# Restore the REVIEWED matching old configuration/artifact set when changed.
# Restore external data only through its tested recovery plan, not blind copying.
sudo systemctl start tomcat
sudo journalctl -u tomcat -n 100 --no-pager
curl -I https://tomcat.example.com/app/
```

اگر Configuration یا Artifact برنامه تغییر کرده، برگشت Symlink به‌تنهایی کافی نیست. پیش از Start، Backup هماهنگ را تحت Change Control بازگردانید. کل CATALINA_BASE را با Distribution جدید Overwrite یا Schema دیتابیس را پنهانی Rollback نکنید. بعد از Upgrade یا Rollback، بررسی خارجی Firewall و امنیت را تکرار کنید.

[برنامه‌ریزی رسمی Upgrade و مقایسه Configuration](https://tomcat.apache.org/upgrading.html)

[الزامات مهاجرت وابسته به نسخه Tomcat 11](https://tomcat.apache.org/migration-11.0.html)

## ۱۳. چک‌لیست امنیت Production

- [ ] Patch پشتیبانی‌شده Tomcat، Build نگهداری‌شده Java 21 و Update امنیت OS ثبت شده؛ Advisory مالک و مهلت رفع دارد.
- [ ] حساب غیر root فاقد sudo است؛ Binary/Config/Deployment متعلق به root و Write در Runtime محدود و تست‌شده است.
- [ ] Start، Stop، شروع پس از Reboot، Restart Limit و Shutdown درست در systemd تست شده؛ استثنای Sandbox مستند است.
- [ ] فقط Nginx روی 80/443 عمومی است؛ SSH به Source محدود، 8080 فقط Loopback و Listener مربوط به AJP/Shutdown غایب است.
- [ ] زنجیره TLS، Hostname، Scheduler تمدید، Reload Hook و Alert انقضا موفق؛ Private Key و Backup محافظت‌شده است.
- [ ] تست جعل Forwarded Header موفق و IP لاگ صحیح است؛ مسیر عمومی مدیریت و App مستقرنشده بررسی شده است.
- [ ] Authentication، Authorization، CSRF، Cookie، Error Response و Request/Upload Limit برنامه تست شده؛ Header/CSP با برنامه سازگار است.
- [ ] حافظه JVM/Native، GC، Restart، 5xx/Latency، رشد دیسک/Log و Health Check با Alert عملیاتی پایش می‌شود.
- [ ] Log از Secret غیرضروری خالی، دارای Retention/Rotation محافظت‌شده و متصل به Monitoring مرکزی است.
- [ ] منشأ WAR و Vulnerability Scan تأیید؛ Secret/Upload خارج Deployment و تغییر Operator قابل Audit است.
- [ ] Backup رمزنگاری‌شده خارج Host و Restore Drill ایزوله موفق؛ Maintenance، سازگاری Data و Rollback مستند است.
- [ ] وضعیت SELinux/AppArmor و Policy واقعی Process بررسی؛ برنامه با اعتماد متفاوت مرز جدا دارد.

## پذیرش عملیاتی

Unit مؤثر، Ownership مسیر، Version/Hash تأییدشده، مرز اعتماد Proxy، برنامه تمدید، Dashboard، Runbook رخداد و شاهد Restore/Rollback تست‌شده را تحویل دهید. Workspace ویندوزی این سایت Integration مقاله را اعتبارسنجی می‌کند؛ فرمان Linux و رفتار برنامه پیش از پذیرش Production نیازمند اجرای Staging روی پلتفرم مقصد است.

## پرسش‌های متداول

### آیا Tomcat 11 روی Java 21 اجرا می‌شود؟

بله؛ حداقل Java در Tomcat 11 نسخه 17 است. Java 21 سازگار است؛ Bytecode و Dependency برنامه نیز باید سازگار باشند.

### آیا WAR مربوط به Tomcat 9 بدون تغییر روی 11 اجرا می‌شود؟

فرض نکنید. API وب از javax.* به jakarta.* و بعضی Defaultها تغییر کرده‌اند. برنامه پشتیبانی‌شده سازنده را بازسازی و تست کنید.

### چرا CATALINA_HOME و CATALINA_BASE جدا هستند؟

HOME شامل Binary نسخه و BASE شامل Configuration، Deployment و مسیر Runtime مربوط به Instance است. جداسازی Upgrade بررسی‌شده را بدون Overwrite تنظیم Production ممکن می‌کند.

### چرا درخواست اولیه localhost پاسخ 404 می‌دهد؟

در webapps خالی و محافظت‌شده، ROOT نصب نشده است. برنامه تأییدشده را Deploy و Context Path مستندش را تست کنید.

### با shutdown port برابر -1 چگونه Tomcat متوقف می‌شود؟

systemd به JVM Foreground، SIGTERM می‌دهد و Shutdown Hook جاوا، Tomcat را متوقف می‌کند؛ shutdown.sh روش کنترل این Unit نیست.

### آیا سرویس می‌تواند در webapps بنویسد؟

خیر؛ Operator، Artifact بررسی‌شده را با root:tomcat نصب می‌کند. State متغیر در مسیرهای مشخص قابل نوشتن است.

### آیا HTTP محلی میان Nginx و Tomcat امن است؟

در همان Host باقی می‌ماند و به اعتماد Host وابسته است. ترافیک Host جدا به مسیر خصوصی محدود و در نیاز Policy به رمزنگاری معتبر نیاز دارد.

### آیا Java SecurityManager باید فعال شود؟

خیر؛ Tomcat 11 پشتیبانی را حذف کرده است. از Least Privilege، محدودیت systemd و Instance/VM جدا برای مرز اعتماد متفاوت استفاده کنید.

## منابع رسمی، Template و راهنماهای مرتبط

Release و مرجع Configuration در ۹ اکتبر ۲۰۲۶ بررسی شدند. لینک مرجع رسمی کنار فصل مربوط قرار دارد. پیش از Rollout، Download زنده، Vulnerability و پشتیبانی سازنده را دوباره بررسی کنید. Templateها Credential واقعی ندارند؛ دامنه، JAVA_HOME واقعی و ظرفیت اندازه‌گیری‌شده را قبل از نصب در Host بررسی‌شده Staging تطبیق دهید.

[دانلود Unit امن systemd](/downloads/apache-tomcat-linux-installation-security-hardening/tomcat.service)

[دانلود server.xml برای Proxy محلی](/downloads/apache-tomcat-linux-installation-security-hardening/server.xml)

[دانلود Configuration کنسول JULI](/downloads/apache-tomcat-linux-installation-security-hardening/logging.properties)

[دانلود Bootstrap موقت HTTP](/downloads/apache-tomcat-linux-installation-security-hardening/nginx-bootstrap.conf)

[دانلود Configuration نهایی Nginx HTTPS](/downloads/apache-tomcat-linux-installation-security-hardening/nginx-tomcat.conf)

[مرتبط: نصب و تنظیم Nginx در Ubuntu](https://meetaj.ir/articles/nginx-installation-configuration-ubuntu)

[مرتبط: Reverse Proxy و SNI برای چند دامنه در Nginx](https://meetaj.ir/articles/nginx-reverse-proxy-multiple-domains-single-ip-443)

[مرتبط: امنیت حساب و دسترسی Linux](https://meetaj.ir/articles/linux-security-account-access-management)

[مرتبط: ممیزی امنیت Linux با Bash](https://meetaj.ir/articles/linux-security-auditor-bash)

[مرتبط: دسترسی امن SSH در Linux](https://meetaj.ir/articles/enable-ssh-linux-complete-guide)
