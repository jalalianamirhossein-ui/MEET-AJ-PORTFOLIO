# آموزش جامع نصب و راه‌اندازی Oracle Database 26ai روی Oracle Linux همراه با امنیت، بکاپ و بازیابی

نصب Oracle Database 26ai Enterprise روی Oracle Linux 9، راه‌اندازی خودکار سرویس، امنیت، بکاپ RMAN، زمان‌بندی و سناریوهای بازیابی در محیط سازمانی.

## ۱. مقدمه؛ نسخه، Edition و محدوده راهنما

Oracle Database پایگاه داده رابطه‌ای تراکنشی با SQL، PL/SQL، کنترل هم‌زمانی، Recovery و سرویس‌های داده سازمانی است. در بررسی ۹ اکتبر ۲۰۲۶، Oracle AI Database 26ai نسخه عمومی و بلندمدت جاری در مستندات و صفحه دانلود رسمی Linux x86-64 است. نام محصول 26ai به معنی شروع شماره داخلی با 26 نیست؛ رسانه عمومی پایه شماره 23.26.1.0.0 دارد. آخرین Release Update مجاز را پیش از استقرار در My Oracle Support بررسی کنید؛ نام RPM نشان‌دهنده Patch Level نیست.

[ویژگی‌ها و انتشار بلندمدت رسمی Oracle AI Database 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/nfcoa/all-nfg.html)

[دانلود Enterprise Linux x86-64 و Checksum رسمی](https://www.oracle.com/database/technologies/oracle26ai-linux-downloads.html)

این Runbook برای استقرار جدید Enterprise Edition تک‌نمونه روی Oracle Linux 9 x86_64 با RPM رسمی EE است. آدرس‌ها، نام‌ها، Quota، زمان‌ها و ظرفیت‌ها مثال‌اند و باید برای محیط شما تأیید شوند. فرمان‌های Linux روی میزبان دیتابیس اجرا می‌شوند، نه Workspace ویندوزی سایت. در این مقاله مستندات و Syntax فایل اجرایی بررسی شده؛ نصب، Startup دیتابیس، اجرای RMAN، TLS و Restore روی سرور واقعی Oracle در این محیط انجام نشده‌اند.

| Edition | کاربرد و محدودیت |
| --- | --- |
| Enterprise Edition | Edition تجاری Production؛ Option و Management Pack ممکن است مجوز اضافه بخواهد. تمام مراحل نصب زیر فقط برای EE است. |
| Free Edition | محصول و Package مستقل با محدودیت 2 CPU، حافظه دیتابیس 2 GB و داده کاربر 12 GB. نام‌های FREE/FREEPDB1 و فرمان سرویس Free در این راهنمای EE کاربرد ندارند. |

[محدودیت‌های رسمی Oracle Database Free](https://www.oracle.com/database/free/faq/)

کاربرد سازمانی شامل ERP، مالی، پردازش سفارش، تحلیل ترکیبی و تجمیع برنامه‌هاست؛ AI Vector Search جست‌وجوی شباهت را اضافه می‌کند. مجوز را با استقرار واقعی و قرارداد، از جمله Virtualization و معیار Processor یا Named User Plus تطبیق دهید. دانلود رسانه به‌تنهایی حق استفاده Production نمی‌دهد. Optionهای فعال را ثبت کنید؛ RMAN پایه و Unified Auditing را از Advanced Security، Advanced Compression و Diagnostics/Tuning Pack دارای مجوز جدا تفکیک کنید.

[مجوز Oracle 26ai؛ Feature، Option و Pack مجاز](https://docs.oracle.com/en/database/oracle/oracle-database/26/dblic/Licensing-Information.html)

## معماری CDB و PDB

Instance شامل SGA و Background Process است و Database از Datafile، Control File و Online Redo تشکیل می‌شود. CDB دارای CDB$ROOT، قالب فقط‌خواندنی PDB$SEED و PDBهای برنامه است. حساب و Schema محلی برنامه در ORCLPDB1 قرار می‌گیرد. PDBها Instance و Failure Domain میزبان را مشترک دارند؛ PDB نود مستقل Availability نیست. برنامه به Service Name مربوط به PDB وصل می‌شود، نه SID ریشه. Script پیکربندی RPM برای ساخت دیتابیس DBCA را فراخوانی می‌کند.

![Oracle Linux میزبان Instance است؛ CDB شامل Root، Seed و PDB برنامه است و برنامه‌ها از طریق سرویس PDB متصل می‌شوند.](/assets/img/articles/content/oracle-database-architecture.png)

Oracle Linux میزبان Instance است؛ CDB شامل Root، Seed و PDB برنامه است و برنامه‌ها از طریق سرویس PDB متصل می‌شوند.

[مفاهیم و معماری فیزیکی Oracle Database](https://docs.oracle.com/en/database/oracle/oracle-database/26/cncpt/introduction-to-oracle-database.html)

[معماری رسمی Multitenant، CDB و PDB](https://docs.oracle.com/en/database/oracle/oracle-database/26/multi/introduction-to-the-multitenant-architecture.html)

## ۲. پیش‌نیازها و بررسی میزبان

چک‌لیست رسمی سرور حداقل نصب 1 GB RAM و پیشنهاد 2 GB دارد؛ Grid Infrastructure حداقل 8 GB می‌خواهد. این‌ها کف نصب‌اند. تخمین شروع عملی این راهنما 4 vCPU و 16–32 GiB RAM است؛ سپس SGA، PGA، Session و IOPS را با تست Workload تعیین کنید. برای x86_64 از CPU/Platform تأییدشده استفاده کنید و پیش‌نیاز Arm را به آن تعمیم ندهید. بودجه ظرفیت Database، FRA و Backup جدا باشد؛ ظرفیت Production را از محدودیت Free تعیین نکنید.

[حداقل سخت‌افزار رسمی](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/server-hardware-checklist-for-oracle-database-installation.html)

جدول پشتیبانی OL9 حداقل پایه OL9.2 با UEK7 نسخه 5.15.0-201.135.6.el9uek یا RHCK نسخه 5.14.0-284.30.1.el9_2 را ذکر می‌کند. از RU 23.26.2 ردیف OL9.6 با UEK8 نسخه 6.12.0-1.23.3.2.el9uek یا جدیدتر نیز اضافه شده است. ترکیب دقیق OS/Kernel/RU را از Certification Matrix جاری انتخاب کنید. اضافه‌شدن ردیف UEK8 اثبات حذف پشتیبانی تمام خانواده‌های Kernel قبلی نیست. OL9 را به Release Level پشتیبانی‌شده جاری Patch کنید.

[Kernelهای OL9 و Packageهای موردنیاز](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/supported-oracle-linux-9-distributions-for-x86-64.html)

```bash
# Database host: root/sudo diagnostics
cat /etc/oracle-release
uname -m
uname -r
lscpu
free -h
swapon --show
df -hT / /opt /tmp
df -i / /opt /tmp
findmnt
hostnamectl
hostname -f
getent ahostsv4 db01.example.com
ip -br address
ip route
timedatectl
chronyc tracking
getenforce
sudo firewall-cmd --get-active-zones
```

خروجی مطلوب: Oracle Linux 9.x، معماری x86_64، Kernel تأییدشده، RAM/Disk/Inode کافی، IP خصوصی ثابت 10.20.30.10، نام db01.example.com که به همان IP Resolve شود، زمان همگام و SELinux در حالت Enforcing. رکورد Forward/Reverse DNS و IP ثابت را با ابزار شبکه سازمان تنظیم کنید. example.com و Subnetهای نمونه را جایگزین کنید. Swap برای میزبان فقط Database: RAM بین 1–2 GB برابر 1.5 برابر RAM؛ بین 2–16 GB برابر RAM؛ بیشتر از 16 GB برابر 16 GB. HugePages و SGA به برنامه‌ریزی Workload جدا نیاز دارند؛ Swap مداوم پذیرفته نیست.

[تنظیم سرور، Swap و برنامه‌ریزی Memory](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/server-configuration-checklist-for-oracle-database-installation.html)

| مسیر | کاربرد و بودجه نمونه |
| --- | --- |
| /opt/oracle | حداقل نرم‌افزار EE برابر 8.3 GB؛ پیشنهاد Oracle حدود 100 GB با فضای Patch است. Inventory و Log را نیز بودجه‌بندی کنید. |
| /oradata | فایل‌های دیتابیس روی SSD؛ نمونه 100 GiB با ظرفیت رشد، Redo و کار موقت. |
| /fra | Recovery File؛ Quota نمونه 100 GiB با فضای فیزیکی بیشتر و Alarm. |
| /backup/oracle | Mount مستقل Backup؛ اندازه زنجیره نگهداری‌شده Level 0/1 و Archive Log به‌اضافه تأخیر انتقال خارج میزبان. |

Filesystemهای تأییدشده را پیش از نصب ایجاد و Mount دائمی کنید؛ Ownership و ترتیب Boot در /etc/fstab را بررسی کنید. در Automation زیر /backup/oracle یک Filesystem مستقل Mountشده است؛ Script در مسیر Mountنشده نمی‌نویسد. Device موجود را با کپی مثال Format نکنید. RMAN از Oracle Home، Wallet، Password File، Config Listener و فایل External Table محافظت نمی‌کند؛ Backup جدا با دسترسی محدود لازم است.

[پیش‌نیاز رسمی فضای نرم‌افزار و ظرفیت Patch](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/storage-checklist-for-oracle-database-installation.html)

## ۳. نصب و پیکربندی دیتابیس

![Platform را بررسی، OL9 و Preinstallation RPM را آماده، رسانه EE را تأیید، CDB/PDB را تنظیم و اتصال را تست کنید.](/assets/img/articles/content/oracle-database-installation-workflow.png)

Platform را بررسی، OL9 و Preinstallation RPM را آماده، رسانه EE را تأیید، CDB/PDB را تنظیم و اتصال را تست کنید.

### مرحله ۱ تا ۳؛ Patch سیستم و RPM رسمی پیش‌نیاز

```bash
sudo dnf upgrade --refresh -y
# Reboot in an approved maintenance window after kernel updates:
sudo reboot
# Reconnect and verify the running kernel, DNS and time again.
sudo dnf install -y oracle-ai-database-preinstall-26ai
sudo dnf install -y firewalld chrony policycoreutils-python-utils util-linux logrotate
sudo systemctl enable --now firewalld chronyd
rpm -q oracle-ai-database-preinstall-26ai
id oracle
getent group oinstall dba
sudo cat /var/log/oracle-ai-database-preinstall-26ai/results/orakernel.log
sudo grep -RE 'oracle|shm|sem|aio-max-nr|file-max|ip_local_port_range' /etc/sysctl.d /etc/security/limits.d
sysctl kernel.sem kernel.shmmax kernel.shmall fs.aio-max-nr fs.file-max net.ipv4.ip_local_port_range
sudo -iu oracle bash -c 'ulimit -Sn; ulimit -Hn; ulimit -Su; ulimit -Hu'
```

Preinstallation RPM وابستگی‌ها، Kernel/Limits و حساب oracle با گروه oinstall و dba را تنظیم می‌کند. فایل و Log حاصل را بررسی کنید و Sysctl دلخواه کپی نکنید. نتیجه مطلوب: Package نصب، oracle عضو گروه نصب/DBA و Limits مطابق چک‌لیست رسمی باشد. حساب OSDBA می‌تواند کل Instance را مدیریت کند؛ Login، SSH Key و Sudo را محدود کنید. گروه‌های OSOPER/OSBACKUPDBA/OSKMDBA را فقط در طراحی بررسی‌شده تفکیک وظایف ایجاد کنید.

[پیکربندی سیستم‌عامل با Preinstallation RPM رسمی](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/automatically-configuring-oracle-linux-with-oracle-preinstallation-rpm.html)

### مرحله ۴ و ۵؛ دریافت و تأیید رسانه Enterprise Edition

از صفحه رسمی لینک‌شده، مجوز مربوط را بپذیرید و RPM مربوط به Enterprise OL9 x86_64 را با مرورگر احراز هویت‌شده یا کانال Artifact مجاز در /var/tmp/oracle-media دریافت کنید. RPM مربوط به Free یا OL8/Arm را جایگزین نکنید. احراز هویت و Redirect مجوز دانلود Oracle، URL ساختگی curl بدون تعامل را نامعتبر می‌کند. فرمان زیر رسانه دقیق OL9 مشاهده‌شده در تاریخ بررسی را تأیید می‌کند؛ اگر صفحه تغییر کرد، Filename و Hash را دوباره از همان صفحه بگیرید.

```bash
sudo install -d -m 0750 /var/tmp/oracle-media
# Place the official downloaded RPM here before continuing.
cd /var/tmp/oracle-media
printf '%s  %s\n' '7405061889cdbf368816be5eea27313cf3be86afc70b85c77ab9a3a0dc216730' 'oracle-ai-database-ee-26ai-1.0-1.el9.x86_64.rpm' | sha256sum --check -
# Expected: oracle-ai-database-ee-26ai-1.0-1.el9.x86_64.rpm: OK
rpm -qpi ./oracle-ai-database-ee-26ai-1.0-1.el9.x86_64.rpm
sudo dnf install -y ./oracle-ai-database-ee-26ai-1.0-1.el9.x86_64.rpm
rpm -q oracle-ai-database-ee-26ai
rpm -ql oracle-ai-database-ee-26ai | grep -E 'init.d|sysconfig|systemd|dbhome'
sudo install -d -o oracle -g oinstall -m 0750 /oradata /fra
sudo install -d -o oracle -g oinstall -m 0700 /backup/oracle
findmnt --target /oradata
findmnt --target /fra
findmnt --mountpoint /backup/oracle
```

[نصب RPM مربوط به EE و Script رسمی Configure](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/running-rpm-packages-to-install-oracle-database.html)

### مرحله ۶ تا ۹؛ Environment، Listener، CDB و PDB اولیه

فایل /etc/sysconfig/oracledb_ORCLCDB-26ai.conf و Script بسته را بررسی کنید. در این مثال فیلد موجود ORACLE_DATA_LOCATION را /oradata قرار دهید و پورت Listener را 1521 نگه دارید. کلید Config حدسی اضافه نکنید. فرمان رسمی Configure با DBCA، دیتابیس ORCLCDB، PDB به نام ORCLPDB1 و Listener را ایجاد می‌کند؛ روی دیتابیس Production موجود دوباره اجرا نکنید. خروجی وضعیت و رفتار مقداردهی Credential را بررسی و Credential مدیریتی اولیه را بلافاصله با SQL*Plus محلی دارای OS Authentication مطابق بخش ۵ Rotate کنید. برای نام/Template سفارشی از مسیر رسمی DBCA/RPM استفاده کنید، نه اجرای دوم Configure.

```bash
sudo cat /etc/sysconfig/oracledb_ORCLCDB-26ai.conf
sudo vi /etc/sysconfig/oracledb_ORCLCDB-26ai.conf
# New deployment ONLY; creates the database and listener:
sudo /etc/init.d/oracledb_ORCLCDB-26ai configure
sudo -iu oracle
# Add this non-secret environment to oracle's ~/.bash_profile:
export ORACLE_BASE=/opt/oracle
export ORACLE_HOME=/opt/oracle/product/26ai/dbhome_1
export ORACLE_SID=ORCLCDB
export PATH="$ORACLE_HOME/bin:$PATH"
export TNS_ADMIN="$ORACLE_HOME/network/admin"
umask 027
test -x "$ORACLE_HOME/bin/sqlplus"
lsnrctl status LISTENER
lsnrctl services LISTENER
sqlplus / as sysdba
```

```sql
-- SQL*Plus as SYSDBA in CDB$ROOT:
SELECT banner_full FROM v$version;
SELECT name, cdb, open_mode, log_mode FROM v$database;
SHOW CON_NAME
SHOW PDBS
ALTER PLUGGABLE DATABASE ORCLPDB1 OPEN;
ALTER PLUGGABLE DATABASE ORCLPDB1 SAVE STATE;
ALTER SYSTEM REGISTER;
SELECT name, network_name, pdb FROM v$services ORDER BY name;
SELECT patch_id, action, status, description FROM dba_registry_sqlpatch ORDER BY action_time;
EXIT
```

اگر SHOW PDBS از قبل READ WRITE نشان می‌دهد، OPEN را اجرا نکنید؛ ORA-65019 یعنی PDB باز است. خروجی مطلوب: CDB=YES، ریشه READ WRITE، Seed برابر READ ONLY، ORCLPDB1 برابر READ WRITE و سرویس PDB با Handler وضعیت READY ثبت شده باشد. SAVE STATE وضعیت باز PDB را برای Restart نگه می‌دارد. مسیر Config واقعی شبکه را با lsnrctl status بررسی و listener.ora را پیش از تغییر بخوانید. پیش از پذیرش Production، RU نصب‌شده را با OPatch Inventory و DBA_REGISTRY_SQLPATCH تأیید کنید.

```bash
# oracle shell: inventory only, no patch application
"$ORACLE_HOME/OPatch/opatch" lsinventory
cat "$TNS_ADMIN/listener.ora"
```

### مرحله ۱۰ و ۱۱؛ بررسی اتصال SQL*Plus محلی و Remote

پس از ایجاد User محدود برنامه در PDB مطابق بخش ۵ و اعمال Firewall Allowlist، فرمان زیر را روی میزبان برنامه مجاز با Oracle Client پشتیبانی‌شده اجرا کنید. SQL*Plus رمز را Prompt می‌کند. نام سرویس PDB باید با خروجی واقعی v$services و lsnrctl services یکسان باشد. موفقیت Probe مربوط به TCP به‌تنهایی اتصال احراز هویت‌شده دیتابیس را اثبات نمی‌کند. این تست اولیه TCP فقط Diagnostic نصب روی شبکه خصوصی است؛ پیش از Traffic عملیاتی، Transport رمز‌شده بخش امنیت را برقرار کنید.

```bash
# Authorized client, password is prompted:
sqlplus -L app_runtime@//db01.example.com:1521/ORCLPDB1
```

```sql
SELECT sys_context('USERENV','CON_NAME') AS pdb,
       sys_context('USERENV','SESSION_USER') AS login_user FROM dual;
-- Expected: ORCLPDB1 and APP_RUNTIME
EXIT
```

## ۴. اجرای Oracle به‌صورت سرویس و تأیید Startup پس از Reboot

![برای Startup از سرویس ارائه‌شده توسط RPM استفاده کنید؛ Listener، Instance و وضعیت ذخیره‌شده PDB را مستقل بررسی کنید.](/assets/img/articles/content/oracle-database-systemd-service.png)

برای Startup از سرویس ارائه‌شده توسط RPM استفاده کنید؛ Listener، Instance و وضعیت ذخیره‌شده PDB را مستقل بررسی کنید.

راهنمای رسمی EE فایل /etc/init.d/oracledb_ORCLCDB-26ai را ارائه می‌کند. در OL9 ممکن است Systemd آن را از طریق سازگاری SysV نمایش دهد؛ SourcePath/FragmentPath را بررسی کنید تا سرویس Loaded از RPM نصب‌شده آمده باشد. نام .service زیر از Script مستندشده گرفته شده و Unit سفارشی نیست. oracle.service ساختگی ایجاد نکنید. اگر ترکیب RPM/OS شما Integration لودشده ندارد، مستندات Package یا Oracle Restart را دنبال کنید و Unit حدسی نسازید.

```bash
# root/sudo; verify the official package owns the script first:
rpm -qf /etc/init.d/oracledb_ORCLCDB-26ai
sudo systemctl daemon-reload
sudo systemctl show oracledb_ORCLCDB-26ai.service -p LoadState -p SourcePath -p FragmentPath
sudo systemctl cat oracledb_ORCLCDB-26ai.service
# Continue only if LoadState=loaded and provenance matches the RPM.
sudo systemctl enable oracledb_ORCLCDB-26ai.service
sudo systemctl is-enabled oracledb_ORCLCDB-26ai.service
sudo systemctl start oracledb_ORCLCDB-26ai.service
sudo systemctl status oracledb_ORCLCDB-26ai.service --no-pager
sudo journalctl -u oracledb_ORCLCDB-26ai.service -b -n 100 --no-pager
# Maintenance window: these commands interrupt all database sessions.
sudo systemctl stop oracledb_ORCLCDB-26ai.service
sudo systemctl start oracledb_ORCLCDB-26ai.service
sudo systemctl restart oracledb_ORCLCDB-26ai.service
```

برای سرویس SysV تولیدشده، systemctl enable کار را به Helper سازگاری Distribution واگذار می‌کند؛ Helper غایب یا Enable ناموفق، موفقیت نیست. Header مربوط به chkconfig/LSB و Package سازگاری Distribution را بررسی کنید. اگر Script بسته و پشتیبانی chkconfig موجود است، مسیر Legacy معادل زیر کاربرد دارد. هر دو مسیر را کورکورانه اجرا نکنید. active (exited) برای Wrapper می‌تواند طبیعی باشد و بازبودن دیتابیس یا سلامت Listener را اثبات نمی‌کند.

```bash
# Conditional SysV compatibility path only:
command -v chkconfig
sudo grep -E 'chkconfig:|BEGIN INIT INFO|Default-Start' /etc/init.d/oracledb_ORCLCDB-26ai
sudo chkconfig --add oracledb_ORCLCDB-26ai
sudo chkconfig oracledb_ORCLCDB-26ai on
sudo chkconfig --list oracledb_ORCLCDB-26ai
# Direct operations provided by the RPM script:
# Inspect its usage/case branches to confirm supported actions:
sudo tail -n 80 /etc/init.d/oracledb_ORCLCDB-26ai
sudo /etc/init.d/oracledb_ORCLCDB-26ai start
sudo /etc/init.d/oracledb_ORCLCDB-26ai stop
```

[فعال‌سازی سرویس و معنای Boot در OL9](https://docs.oracle.com/en/operating-systems/oracle-linux/9/systemd/EnablingandDisablingServices.html)

[عملیات Start، Stop و Status در OL9](https://docs.oracle.com/en/operating-systems/oracle-linux/9/systemd/StartingandStoppingServices.html)

### اثبات Startup خودکار پس از Reboot

```bash
# Approved maintenance window, console access available:
sudo reboot
# Reconnect after boot:
sudo systemctl status oracledb_ORCLCDB-26ai.service --no-pager
sudo journalctl -u oracledb_ORCLCDB-26ai.service -b --no-pager
sudo -iu oracle
lsnrctl status LISTENER
lsnrctl services LISTENER
sqlplus / as sysdba
```

```sql
SELECT status, database_status FROM v$instance;
SELECT open_mode FROM v$database;
SHOW PDBS
EXIT
```

معیار پذیرش: OPEN / ACTIVE، ریشه و ORCLPDB1 برابر READ WRITE، Handler سرویس PDB برابر READY و Query موفق Remote بدون Startup دستی. اگر Boot موفق و Oracle ناموفق است، Mountها را بررسی کنید. Start فعلی و Enable هنگام Boot دو عملیات جدا هستند. پس از Patch، تغییر Oracle Home و Filesystem دوباره بررسی کنید.

### Oracle Restart و SRVCTL برای Availability محلی قوی‌تر

Scriptهای Boot مربوط به RPM عملیات Start/Stop دارند؛ Instance را دائماً مانیتور یا به میزبان دیگر منتقل نمی‌کنند. Oracle Restart همان Grid Infrastructure سرور مستقل است: Database، Listener، ASM و سرویس ثبت‌شده را پایش و با ترتیب Dependency دوباره شروع می‌کند. GI را با فرآیند gridSetup.sh نسخه 26ai نصب و Component موجود را Register کنید. فرمان زیر فقط پس از مالکیت Restart بر Resource و با Owner و Binary/Home صحیح کاربرد دارد. در مهاجرت، Autostart رقیب RPM را غیرفعال کنید. Restart بازیابی محلی را بهبود می‌دهد؛ بقای خرابی میزبان به معماری جداگانه Data Guard/RAC و مجوز مربوط نیاز دارد.

```bash
# Oracle Restart already configured; database unique name verified:
srvctl config database -db ORCLCDB
srvctl enable database -db ORCLCDB
srvctl start database -db ORCLCDB
srvctl status database -db ORCLCDB
srvctl status listener
srvctl stop database -db ORCLCDB -stopoption IMMEDIATE
srvctl start database -db ORCLCDB
```

[مالکیت، Dependency و SRVCTL در Oracle Restart](https://docs.oracle.com/en/database/oracle/oracle-database/26/admin/configuring-automatic-restart-of-an-oracle-database.html)

[نصب GI مستقل نسخه 26ai با gridSetup.sh](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/installing-and-configuring-oracle-grid-infrastructure-for-a-standalone-server.html)

## ۵. امن‌سازی Oracle Database

![شبکه Allowlist برنامه، Transport رمز‌شده، Listener محافظت‌شده، User محدود PDB، Auditing و کلید محافظت‌شده.](/assets/img/articles/content/oracle-database-security-hardening.png)

شبکه Allowlist برنامه، Transport رمز‌شده، Listener محافظت‌شده، User محدود PDB، Auditing و کلید محافظت‌شده.

### حداقل دسترسی، حساب مدیریتی و Policy رمز

SYS را برای مدیریت استثنایی Instance و SYSTEM را برای مدیریت کنترل‌شده نگه دارید؛ برنامه هرگز از این دو استفاده نکند. رمز اولیه مدیر را با Prompt فرمان PASSWORD در SQL*Plus تغییر دهید، نه Argument شل، History یا Source File. دسترسی مدیر Remote، Password File و عضویت OSDBA را بررسی کنید. SYS را قفل یا OS Authentication را با نسخه کپی‌شده حذف نکنید؛ دسترسی اضطراری Auditشده لازم است. Operator نام‌دار، MFA در Jump Host و هویت مستقل برنامه استفاده کنید.

```sql
-- SYSDBA, CDB$ROOT; password prompts do not expose a literal secret:
PASSWORD SYS
PASSWORD SYSTEM
SELECT username, account_status, profile FROM dba_users ORDER BY username;
SELECT username, sysdba, sysoper, sysbackup FROM v$pwfile_users;
ALTER SESSION SET CONTAINER=ORCLPDB1;
-- Verify the shipped password verification function exists before using it:
SELECT object_name, status FROM dba_objects
 WHERE owner='SYS' AND object_name='ORA12C_STRONG_VERIFY_FUNCTION';
CREATE PROFILE aj_app_profile LIMIT
  FAILED_LOGIN_ATTEMPTS 5 PASSWORD_LOCK_TIME 1/24
  PASSWORD_LIFE_TIME 90 PASSWORD_GRACE_TIME 7
  PASSWORD_REUSE_TIME 365 PASSWORD_REUSE_MAX 10
  PASSWORD_VERIFY_FUNCTION ora12c_strong_verify_function;
-- Schema-only identities first; PASSWORD converts the runtime user to password authentication.
CREATE USER app_owner NO AUTHENTICATION
  DEFAULT TABLESPACE USERS QUOTA 500M ON USERS;
CREATE USER app_runtime NO AUTHENTICATION PROFILE aj_app_profile;
PASSWORD app_runtime
CREATE ROLE aj_runtime_role;
GRANT CREATE SESSION TO aj_runtime_role;
GRANT aj_runtime_role TO app_runtime;
-- Object grants only after the application owner has created the approved table:
-- GRANT SELECT, INSERT, UPDATE ON app_owner.orders TO aj_runtime_role;
SELECT username, account_status, authentication_type, profile FROM dba_users
 WHERE username IN ('APP_OWNER','APP_RUNTIME');
-- Incident response / controlled maintenance (interrupts future logins):
ALTER USER app_runtime ACCOUNT LOCK;
-- Only after investigation and any required password rotation:
ALTER USER app_runtime ACCOUNT UNLOCK;
```

به Runtime Role مجوز DBA، UNLIMITED TABLESPACE یا ANY گسترده ندهید. Identity کنترل‌شده Deployment اشیای Schema را بسازد؛ Owner از نوع Schema-only ورود مستقیم ندارد. حساب بلااستفاده را پیش از Lock بررسی کنید؛ حساب Oracle-maintained ممکن است Dependency داشته باشد. انقضای Password به فرآیند Rotation تست‌شده برای Connection Pool نیاز دارد، نه قطعی بی‌برنامه در روز ۹۰. استثنای Profile را ثبت کنید. اگر Verification Function وجود ندارد، مسیر رسمی نصب Password Profile را بررسی و سپس Profile را بسازید.

[احراز هویت و Password Profile رسمی Oracle](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-authentication.html)

[CREATE USER و احراز هویت Schema-only](https://docs.oracle.com/en/database/oracle/oracle-database/26/sqlrf/CREATE-USER.html)

[فرمان PASSWORD در SQL*Plus](https://docs.oracle.com/en/database/oracle/oracle-database/26/sqpug/PASSWORD.html)

### مرز Firewall و Listener

VLAN خصوصی دیتابیس داشته باشید و TCP/1521 را فقط برای Subnet برنامه 10.20.40.0/24 و Source مدیریتی جداگانه مجاز کنید. مثال فرض می‌کند Interface دیتابیس در public است؛ Active Zone را بخوانید و Zone واقعی را جایگزین کنید. Port/Service/Rich Rule عمومی قبلی یا Zone از نوع Trusted می‌تواند Allowlist را بی‌اثر کند؛ تمام Rule مؤثر، ACL بالادستی و مسیر IPv6 را بررسی کنید. هنگام تغییر Policy، دسترسی Management را حفظ و میزبان مجاز و غیرمجاز را تست کنید.

```bash
sudo firewall-cmd --get-active-zones
sudo firewall-cmd --zone=public --list-all
# Remove a broad port allowance IF it currently exists:
if sudo firewall-cmd --permanent --zone=public --query-port=1521/tcp; then
  sudo firewall-cmd --permanent --zone=public --remove-port=1521/tcp
fi
sudo firewall-cmd --permanent --zone=public --add-rich-rule='rule family="ipv4" source address="10.20.40.0/24" port port="1521" protocol="tcp" accept'
sudo firewall-cmd --reload
sudo firewall-cmd --zone=public --list-all
sudo ss -ltnp | grep ':1521'
# Oracle shell:
lsnrctl status LISTENER
lsnrctl services LISTENER
```

Listener را به Hostname/IP خصوصی مجاز Bind کنید؛ IPC و Dynamic Registration تولیدشده را حفظ کنید. مدیریت Listener را Remote عمومی نکنید. محدودیت Registration زیر را به listener.ora موجود اضافه، سپس Reload و Register کنید. این تنظیم درخواست Registration را به آدرس محلی محدود می‌کند و Allowlist ورود Client برنامه نیست؛ پذیرش Client به Firewall/Authentication وابسته است. Listener زیر مالکیت Restart را با Config مربوط به GI و SRVCTL مدیریت کنید و فایل متعارض Database Home نسازید.

```ini
# Fragment to merge into the EXISTING listener.ora:
VALID_NODE_CHECKING_REGISTRATION_LISTENER=ON
```

```bash
lsnrctl reload LISTENER
sqlplus / as sysdba
```

```sql
ALTER SYSTEM REGISTER;
EXIT
```

[Parameterهای Listener و بررسی Registration](https://docs.oracle.com/en/database/oracle/oracle-database/26/netrf/oracle-net-listener-parameters-in-listener-ora.html)

[پیکربندی Zone در Oracle Linux firewalld](https://docs.oracle.com/en/operating-systems/oracle-linux/9/firewall/firewall-ConfiguringfirewalldZones.html)

### SELinux، Ownership و کنترل میزبان

```bash
getenforce
sudo ls -ldZ /opt/oracle /oradata /fra /backup/oracle
sudo restorecon -Rv /opt/oracle /oradata /fra /backup/oracle
sudo ausearch -m AVC,USER_AVC -ts recent
sudo namei -l /backup/oracle
sudo stat -c '%U:%G %a %n' /oradata /fra /backup/oracle
sudo find /oradata /fra /backup/oracle -xdev -type f -perm -0002 -print
```

SELinux را Enforcing و Firewalld را فعال نگه دارید. Mount سفارشی ممکن است به File Context دائمی و Policy مصوب برای Process Domain واقعی نیاز داشته باشد؛ restorecon فقط Label تنظیم‌شده را اعمال می‌کند و Policy دیتابیس نمی‌سازد. AVC، Ownership و Mount Option را بررسی کنید و Denial را خودکار به audit2allow ندهید. Permission کل Oracle Home را با chmod تغییر ندهید؛ Binaryهای Package مجوز ویژه دارند. برای Directory داده 0750، Backup/Wallet برابر 0700 و Secret File در جای مناسب 0600 استفاده و ACL و دسترسی Parent را بررسی کنید.

[مدیریت SELinux در Oracle Linux](https://docs.oracle.com/en/operating-systems/oracle-linux/selinux/)

### Auditing، Transport رمز‌شده و TDE

```sql
-- SYSDBA: review root and each application PDB separately.
ALTER SESSION SET CONTAINER=ORCLPDB1;
SELECT policy_name, enabled_option, entity_name FROM audit_unified_enabled_policies;
-- Enable only if not already enabled:
AUDIT POLICY ORA_LOGON_FAILURES;
CREATE AUDIT POLICY aj_account_changes ACTIONS CREATE USER, ALTER USER, DROP USER;
AUDIT POLICY aj_account_changes;
SELECT event_timestamp, dbusername, action_name, return_code
 FROM unified_audit_trail
 WHERE event_timestamp > SYSTIMESTAMP - INTERVAL '1' DAY
 ORDER BY event_timestamp DESC FETCH FIRST 50 ROWS ONLY;
```

Audit Event را به Collector مرکزی محافظت‌شده ارسال و Failed Login، تغییر حساب/مجوز، Session مدیریتی غیرمنتظره و Backup ناموفق را پایش کنید. Retention، Alert ظرفیت و Archive/Purge با DBMS_AUDIT_MGMT فقط پس از Export موفق تعریف شود. برای پنهان‌کردن مشکل Disk Space، Audit Trail را پاک نکنید. Unified Auditing در EE وجود دارد؛ محصول مستقل Audit Vault/Database Firewall مجوز خود را دارد.

[ایجاد و فعال‌سازی Unified Audit Policy](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-audit-policies.html)

TLS از Transport محافظت و سرور را احراز هویت می‌کند؛ TDE فایل ذخیره‌شده را محافظت می‌کند. برای TLS، Certificate صادرشده توسط CA، Private Key و Chain در Oracle Wallet امن لازم است. در 26ai از Parameterهای TLS_ استفاده کنید؛ نام SSL_ قدیمی Deprecated است. Database Server از WALLET_ROOT و مسیر PDB به‌شکل WALLET_ROOT/<PDB GUID>/tls استفاده می‌کند؛ WALLET_LOCATION برای Listener/Client همچنان معتبر است. Certificate هر دو Server و Listener را با DN Matching تست کنید. Fragment زیر فرض می‌کند Wallet دارای CA از قبل آماده، GUID واقعی جایگزین و پس از تنظیم WALLET_ROOT ثابت دیتابیس Restart شده است. این مثال Integration است، نه Script صدور Certificate.

```sql
-- CDB$ROOT, planned restart required before TLS wallet use:
ALTER SYSTEM SET WALLET_ROOT='/opt/oracle/admin/ORCLCDB/wallet' SCOPE=SPFILE;
SELECT name, RAWTOHEX(guid) AS pdb_guid FROM v$pdbs;
SHOW PARAMETER wallet_root
```

```ini
# Merge into existing listener.ora; replace PDB_GUID with the actual GUID.
# Final example: loopback TCP for local registration, TCPS for remote clients.
# During migration, retain the approved private TCP endpoint until clients move to TCPS.
LISTENER =
  (DESCRIPTION_LIST =
    (DESCRIPTION =
      (ADDRESS = (PROTOCOL = IPC)(KEY = EXTPROC1521))
      (ADDRESS = (PROTOCOL = TCP)(HOST = 127.0.0.1)(PORT = 1521))
      (ADDRESS = (PROTOCOL = TCPS)(HOST = db01.example.com)(PORT = 2484))))
WALLET_LOCATION =
  (SOURCE = (METHOD = FILE)
    (METHOD_DATA = (DIRECTORY = /opt/oracle/admin/ORCLCDB/wallet/PDB_GUID/tls)))
TLS_CLIENT_AUTHENTICATION = FALSE
VALID_NODE_CHECKING_REGISTRATION_LISTENER = ON
# Server sqlnet.ora, one-way TLS with database password authentication:
TLS_CLIENT_AUTHENTICATION = FALSE
# Client sqlnet.ora, CA trust available and supported client:
TLS_SERVER_DN_MATCH = YES
```

```sql
-- CDB$ROOT: pair this with the loopback TCP registration address above.
ALTER SYSTEM SET LOCAL_LISTENER='(ADDRESS=(PROTOCOL=TCP)(HOST=127.0.0.1)(PORT=1521))' SCOPE=BOTH;
ALTER SYSTEM REGISTER;
EXIT
```

```bash
# After wallet provisioning, database restart, listener reload and service registration:
sudo firewall-cmd --permanent --zone=public --add-rich-rule='rule family="ipv4" source address="10.20.40.0/24" port port="2484" protocol="tcp" accept'
sudo firewall-cmd --reload
# Authorized client: CA trust and DN matching must be configured first.
sqlplus -L app_runtime@'tcps://db01.example.com:2484/ORCLPDB1'
```

Certificate Validation موفق و رد Certificate با نام نادرست یا CA نامعتبر را تست کنید. Network Banner نشست را با v$session_connect_info بررسی کنید. پس از مهاجرت تمام Clientها به TCPS، اگر Remote بدون رمز دیگر لازم نیست Rule Allowlist پورت 1521 را حذف و فقط Registration محلی مصوب را حفظ کنید. Socket باز 2484 اثبات امنیت TLS نیست. برای mTLS، Wallet کلاینت و هر دو Endpoint را طبق راهنمای رسمی برای Certificate Authentication تنظیم کنید؛ TLS_CLIENT_AUTHENTICATION=FALSE یعنی TLS یک‌طرفه، نه mTLS.

[Wallet، Parameterهای TLS_ و DN Matching در 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-transport-layer-security-encryption.html)

در EE داخل سازمان، TDE و رمزنگاری RMAN مستقیم روی Disk به Oracle Advanced Security نیاز دارند؛ الگوریتم پیشرفته Compression مربوط به RMAN به Advanced Compression نیاز دارد ولی BASIC چنین نیازی ندارد. TLS/Native Network Encryption را با مجوز TDE اشتباه نگیرید. پیش از فعال‌سازی TDE، WALLET_ROOT/TDE_CONFIGURATION، Master Key برای Containerهای لازم، Rotation و Backup مستقل Wallet را طراحی کنید. ازدست‌رفتن کلید می‌تواند Backup رمز‌شده سالم را غیرقابل بازیابی کند. صرف وجود فرمان نمونه، مجوز فعال‌سازی Option نیست.

[Transparent Data Encryption و مدیریت کلید](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbtde/introduction-to-transparent-data-encryption.html)

### مدیریت Patch و پایش امنیت

```bash
# Read-only inventory as oracle:
"$ORACLE_HOME/OPatch/opatch" lsinventory
"$ORACLE_HOME/OPatch/opatch" version
```

Critical Patch Update، RU مجاز Database/GI، پیش‌نیاز OPatch و Errata سیستم‌عامل را پیگیری کنید. README هر Patch، Shutdown، امکان Rolling، datapatch و Rollback را تعیین می‌کند. Patch را در Staging تمرین، Backup قابل بازیابی و کپی کلید تهیه و پس از اعمال، Binary Inventory و DBA_REGISTRY_SQLPATCH را مقایسه کنید. این مقاله فرمان عمومی حدسی opatch apply ارائه نمی‌کند. Patch عقب‌افتاده، انقضای Certificate، Authentication ناموفق، ظرفیت FRA و تغییر Listener/شبکه غیرمجاز را Alert کنید. AWR/ASH/ADDM و Workflow مربوط به Tuning Pack فقط با مجوز تأییدشده استفاده شود.

[هشدار امنیتی و Critical Patch Update رسمی Oracle](https://www.oracle.com/security-alerts/)

## ۶. پیکربندی Backup با RMAN

![RMAN داده، Control File، SPFILE و Archived Redo را محافظت می‌کند؛ زنجیره Recovery و کلیدها را به Storage مستقل خارج میزبان انتقال دهید.](/assets/img/articles/content/oracle-database-rman-backup-recovery.png)

RMAN داده، Control File، SPFILE و Archived Redo را محافظت می‌کند؛ زنجیره Recovery و کلیدها را به Storage مستقل خارج میزبان انتقال دهید.

RMAN ساختار Blockهای Oracle را می‌شناسد و Backup را در Control File یا Recovery Catalog اختیاری ثبت می‌کند. Backup Set یک یا چند Backup Piece دارد. Full Backup دیتابیس را می‌خواند ولی Parent زنجیره Incremental نیست؛ Level 0 مبنای Level 1 است. Differential Level 1 شامل Block تغییرکرده از آخرین Level 0 یا 1 و Cumulative Level 1 شامل تغییر از Level 0 است. در این مثال Level 0 هفتگی، Level 1 روزانه و Job مربوط به Archive Log هر ۱۵ دقیقه برای RPO نمونه داریم. RPO به انتقال کامل خارج میزبان وابسته است؛ RTO باید با Restore Drill اندازه‌گیری شود.

[مفاهیم Full و Incremental Backup در RMAN](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-backup-concepts.html)

### ARCHIVELOG و Fast Recovery Area

فرمان زیر را در قطعی برنامه‌ریزی‌شده با SYSDBA در CDB$ROOT اجرا کنید؛ همه PDBها بسته می‌شوند. Mount بودن /fra، امکان نوشتن oracle و فضای بیشتر از Quota نمونه 100G را تأیید کنید. Quota به معنی رزرو فضای Disk نیست. FRA پر می‌تواند Archiving و دیتابیس را متوقف کند. ابتدا Size و سپس Destination را تنظیم، ARCHIVELOG را فعال و فوراً Level 0 جدید بگیرید. Backup قبلی NOARCHIVELOG جایگزین Baseline قابل بازیابی جدید نیست.

```sql
-- SYSDBA in CDB$ROOT, approved outage:
ALTER SYSTEM SET DB_RECOVERY_FILE_DEST_SIZE=100G SCOPE=BOTH;
ALTER SYSTEM SET DB_RECOVERY_FILE_DEST='/fra' SCOPE=BOTH;
SHUTDOWN IMMEDIATE;
STARTUP MOUNT;
ALTER DATABASE ARCHIVELOG;
ALTER DATABASE OPEN;
ALTER PLUGGABLE DATABASE ORCLPDB1 OPEN;
ALTER PLUGGABLE DATABASE ORCLPDB1 SAVE STATE;
ARCHIVE LOG LIST
ALTER SYSTEM ARCHIVE LOG CURRENT;
SELECT name, log_mode FROM v$database;
SELECT dbid, name, db_unique_name FROM v$database;
-- Example metadata horizon for the 14-day recovery window and weekly baseline:
ALTER SYSTEM SET CONTROL_FILE_RECORD_KEEP_TIME=28 SCOPE=BOTH;
SELECT name, space_limit, space_used, space_reclaimable FROM v$recovery_file_dest;
SELECT dest_id, status, error FROM v$archive_dest_status WHERE status <> 'INACTIVE';
EXIT
```

[مدیریت ARCHIVELOG و مقصد Redo](https://docs.oracle.com/en/database/oracle/oracle-database/26/admin/managing-archived-redo-log-files.html)

### Config پایدار RMAN و مثال‌های صریح Backup

```bash
# Oracle OS account; local OSDBA authentication, no embedded password:
install -d -m 0700 /backup/oracle/pieces /backup/oracle/logs
rman target /
```

```rman
CONFIGURE DEFAULT DEVICE TYPE TO DISK;
CONFIGURE DEVICE TYPE DISK PARALLELISM 2 BACKUP TYPE TO BACKUPSET;
CONFIGURE CHANNEL DEVICE TYPE DISK FORMAT '/backup/oracle/pieces/%d_%T_%U.bkp';
CONFIGURE CONTROLFILE AUTOBACKUP ON;
CONFIGURE CONTROLFILE AUTOBACKUP FORMAT FOR DEVICE TYPE DISK TO '/backup/oracle/pieces/%F';
CONFIGURE RETENTION POLICY TO RECOVERY WINDOW OF 14 DAYS;
# Single disk destination baseline; does not prove an off-host copy exists.
CONFIGURE ARCHIVELOG DELETION POLICY TO BACKED UP 2 TIMES TO DISK;
SHOW ALL;
# Ordinary full backup: NOT a level 1 parent.
BACKUP DATABASE PLUS ARCHIVELOG;
# Incremental baseline, usually weekly:
BACKUP INCREMENTAL LEVEL 0 DATABASE PLUS ARCHIVELOG;
# Daily differential incremental:
BACKUP INCREMENTAL LEVEL 1 DATABASE PLUS ARCHIVELOG;
# Alternative cumulative policy; do not add blindly to the schedule:
# BACKUP INCREMENTAL LEVEL 1 CUMULATIVE DATABASE PLUS ARCHIVELOG;
BACKUP ARCHIVELOG ALL NOT BACKED UP 2 TIMES TO DEVICE TYPE DISK;
BACKUP CURRENT CONTROLFILE;
BACKUP SPFILE;
LIST BACKUP SUMMARY;
REPORT OBSOLETE;
RESTORE DATABASE VALIDATE;
# Source block check, distinct from backup-read validation:
BACKUP VALIDATE CHECK LOGICAL DATABASE;
EXIT;
```

ابتدا /backup/oracle/pieces را با Permission برابر 0700 بسازید. PLUS ARCHIVELOG، Redo اطراف Backup دیتابیس را ثبت می‌کند؛ Controlfile Autobackup در صورت استفاده Instance از SPFILE، آن را نیز شامل می‌شود. Backup صریح Controlfile/SPFILE به Inventory کمک می‌کند ولی جای ثبت DBID و مسیر Autobackup در مکان مستقل را نمی‌گیرد. SHOW ALL، DBID، Database Unique Name، Home/RU، Platform، فهرست Container و محل Wallet را خارج میزبان ثبت کنید. دو کپی Disk در این Policy ممکن است روی یک Disk باشند و Disaster Recovery را تأمین نمی‌کنند.

[پیکربندی RMAN، FRA و Retention](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/configuring-rman-client-basic.html)

[Retention رکورد Backup در Controlfile و Recovery Catalog](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/maintaining-rman-backups.html)

پنجره Recovery برابر ۱۴ روز ممکن است به Baseline هفتگی قدیمی‌تر از ۱۴ روز نیاز داشته باشد. مثال، CONTROL_FILE_RECORD_KEEP_TIME را ۲۸ روز قرار می‌دهد تا رکورد قابل استفاده مجدد Controlfile از زنجیره قدیمی‌تر باشد؛ این Parameter، Policy حذف فایل Backup یا تضمین مطلق Metadata نیست. پیام بازنویسی رکورد و ظرفیت Controlfile را پایش و برای تاریخچه بزرگ‌تر Recovery Catalog مستقل محافظت‌شده استفاده کنید. خود Catalog را نیز Backup و Resynchronize کنید؛ جای Backup داده را نمی‌گیرد.

[Backup دیتابیس، Archived Redo، Control File و SPFILE](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/backing-up-database.html)

[مرجع فرمان BACKUP در RMAN](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/BACKUP.html)

### Encryption، Retention و تأیید Backup

```rman
# Licensed Advanced Security plus configured/open keystore required:
CONFIGURE ENCRYPTION ALGORITHM 'AES256';
CONFIGURE ENCRYPTION FOR DATABASE ON;
SHOW ENCRYPTION;
# Transparent encryption needs the same recoverable keys on the restore host.
# This affects NEW backups, not existing unencrypted pieces.
EXIT;
```

نسخه 26ai برای Target با COMPATIBLE ≥ 23.0.0 از AES-XTS استفاده می‌کند و الگوریتم پیش‌فرض Backup جدید AES256 است؛ فعال‌کردن Encryption همچنان انتخاب جداگانه است. الگوریتم را در v$rman_encryption_algorithms و آمادگی Keystore را در v$encryption_wallet بررسی کنید. Automation زیر به‌طور پیش‌فرض Transparent Encryption برابر ON دارد و بدون کلید شکست می‌خورد. اگر Advanced Security مجاز نیست، فقط پس از تأیید Storage رمز‌شده و انتقال امن، OFF را صریح انتخاب کنید. Password بکاپ را در Script/Unit/Cron قرار ندهید. Password-mode به Credential امن در Session برای Backup و Recovery نیاز دارد؛ کپی Keystore/Key مستقل باشد.

[Modeها و الگوریتم رمزنگاری RMAN در 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/configuring-rman-client-advanced.html)

Retention پنجره Recovery است، نه عمر ساده فایل؛ Level 0 و Redo قدیمی‌تر ممکن است برای بازیابی داخل پنجره لازم باشند. REPORT OBSOLETE پیش‌نمایش است؛ DELETE OBSOLETE مخرب و وابسته به انتقال خارج میزبان تأییدشده است. CROSSCHECK رکورد Catalog را با Storage قابل دسترسی تطبیق می‌دهد؛ EXPIRED یعنی در زمان Check در دسترس نبوده، نه قدیمی یا امن برای حذف. هنگام Mount/Media Manager موقتاً غایب DELETE EXPIRED اجرا نکنید. FRA می‌تواند Log واجد شرایط را طبق Policy بازپس بگیرد؛ ظرفیت و تأخیر انتقال را بودجه‌بندی کنید زیرا شمارش کپی Disk تأیید Off-host نیست.

```rman
# Read-only selection and backup-read checks:
LIST BACKUP SUMMARY;
RESTORE DATABASE PREVIEW;
RESTORE DATABASE VALIDATE;
RESTORE ARCHIVELOG FROM TIME 'SYSDATE-1' VALIDATE;
# After ensuring all configured storage is available:
CROSSCHECK BACKUP;
REPORT OBSOLETE;
# Destructive, separately approved maintenance ONLY:
# DELETE NOPROMPT OBSOLETE;
# Do not use filesystem find -delete on RMAN pieces.
EXIT;
```

[Validation در RMAN و محدودیت آن](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/validating-database-files-backups.html)

## ۷. Backup خودکار؛ Script محافظت‌شده، Timer و Monitoring

کد زیر Job سفارشی Backup است، نه Unit راه‌اندازی دیتابیس Oracle. با حساب oracle و OS Authentication محلی اجرا می‌شود، Jobها را سری می‌کند، Mount مستقل Backup و فضای آزاد را بررسی و هنگام خطای RMAN Exit غیرصفر می‌دهد. به‌علت دسترسی موجود در نصب پایه RPM از OSDBA استفاده می‌کند؛ برای تفکیک وظایف ابتدا OSBACKUPDBA یا هویت SYSBACKUP با Secure Wallet مصوب را تنظیم و تست کنید. Script رمز دیتابیس ذخیره نمی‌کند. Mode مربوط به Retention مستقل و دارای Guard صریح است؛ موفقیت Backup انتقال کامل Off-host را اثبات نمی‌کند.

```bash
#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
export ORACLE_BASE=/opt/oracle
export ORACLE_HOME=/opt/oracle/product/26ai/dbhome_1
export ORACLE_SID=ORCLCDB
export PATH="$ORACLE_HOME/bin:/usr/sbin:/usr/bin:/sbin:/bin"
export TNS_ADMIN="$ORACLE_HOME/network/admin"
export NLS_LANG=AMERICAN_AMERICA.AL32UTF8
base=/backup/oracle
mode=${1:-}
encryption=${RMAN_ENCRYPTION:-ON}
minimum_kib=${MIN_FREE_KIB:-10485760}
allow_cleanup=${ALLOW_CLEANUP:-no}
case "$mode" in level0|level1|archivelog|retention) ;; *) echo 'Usage: oracle-rman-backup.sh level0|level1|archivelog|retention' >&2; exit 64;; esac
case "$encryption" in ON|OFF) ;; *) echo 'RMAN_ENCRYPTION must be ON or OFF' >&2; exit 64;; esac
[[ "$minimum_kib" =~ ^[0-9]+$ ]] || exit 64
[[ $(id -un) == oracle ]] || { echo 'Run as oracle' >&2; exit 77; }
mountpoint -q "$base" || { echo 'Backup mount missing' >&2; exit 73; }
[[ -x "$ORACLE_HOME/bin/rman" && -d "$base/pieces" && -d "$base/logs" ]] || exit 73
exec 9>"$base/.rman.lock"
flock -n 9 || { echo 'Another RMAN job is running' >&2; exit 75; }
available_kib=$(df -Pk "$base" | awk 'NR==2 {print $4}')
[[ "$available_kib" =~ ^[0-9]+$ ]] || exit 73
if [[ "$mode" != retention && "$available_kib" -lt "$minimum_kib" ]]; then
  echo 'Backup free-space threshold failed' >&2; exit 73
fi
if [[ "$mode" == retention && "$allow_cleanup" != yes ]]; then
  echo 'Cleanup requires verified off-host recovery chain and ALLOW_CLEANUP=yes' >&2; exit 78
fi
cmd=$(mktemp "$base/logs/command.XXXXXX")
runlog=$(mktemp "$base/logs/rman.XXXXXX")
trap 'rm -f -- "$cmd" "$runlog"' EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
{
  printf 'SET ECHO OFF;\n'
  if [[ "$mode" != retention ]]; then
    printf 'SET ENCRYPTION %s;\n' "$encryption"
  fi
  case "$mode" in
    level0|level1)
      level=${mode#level}
      printf "BACKUP INCREMENTAL LEVEL %s DATABASE FORMAT '%s/pieces/%%d_%%T_%%U.bkp' TAG 'AJ_L%s' PLUS ARCHIVELOG FORMAT '%s/pieces/%%d_%%T_%%U.arc';\n" "$level" "$base" "$level" "$base"
      printf "BACKUP CURRENT CONTROLFILE FORMAT '%s/pieces/%%d_%%T_%%U.ctl';\n" "$base"
      printf "BACKUP SPFILE FORMAT '%s/pieces/%%d_%%T_%%U.spf';\n" "$base"
      printf "RESTORE DATABASE VALIDATE;\nRESTORE ARCHIVELOG FROM TIME 'SYSDATE-1' VALIDATE;\n"
      ;;
    archivelog)
      printf 'SQL "ALTER SYSTEM ARCHIVE LOG CURRENT";\n'
      printf "BACKUP ARCHIVELOG ALL NOT BACKED UP 2 TIMES TO DEVICE TYPE DISK FORMAT '%s/pieces/%%d_%%T_%%U.arc' TAG 'AJ_ARCH';\n" "$base"
      ;;
    retention)
      printf 'CROSSCHECK BACKUP;\nREPORT OBSOLETE;\nDELETE NOPROMPT OBSOLETE;\n'
      ;;
  esac
  printf 'EXIT;\n'
} >"$cmd"
rc=0
"$ORACLE_HOME/bin/rman" target / cmdfile="$cmd" log="$runlog" || rc=$?
{
  printf '\n===== %s mode=%s rc=%s =====\n' "$(date --iso-8601=seconds)" "$mode" "$rc"
  cat "$runlog"
} >>"$base/logs/backup.log"
# RMAN warnings require attention too; fail conservatively for these diagnostics.
if (( rc != 0 )) || grep -Eq 'RMAN-[0-9]{5}|ORA-[0-9]{5}' "$runlog"; then
  logger -p user.err -t oracle-rman "FAILED mode=$mode sid=$ORACLE_SID rc=$rc" || true
  echo "RMAN job failed; inspect $base/logs/backup.log" >&2
  exit 1
fi
logger -p user.notice -t oracle-rman "SUCCESS mode=$mode sid=$ORACLE_SID" || true
printf 'RMAN job completed: %s\n' "$mode"
```

Exit Code: مقدار 0 پایان Job؛ 1 خطا/Diagnostic مربوط به RMAN؛ 64 ورودی نامعتبر؛ 73 خرابی Mount/Storage؛ 75 تداخل؛ 77 حساب OS اشتباه؛ 78 Guard پاک‌سازی؛ 130/143 قطع اجرا. حداقل فضای 10 GiB فقط Guard شروع است؛ بر اساس بزرگ‌ترین Level 0 و رشد Redo تعیین کنید. تداخل Failure گزارش می‌شود تا Owner درباره Retry تصمیم بگیرد؛ Backup هم‌زمان رقیب اجرا نکنید. RESTORE VALIDATE قطعات منتخب را می‌خواند ولی Restore Drill نیست. Validation روزانه می‌تواند I/O سنگین داشته باشد؛ آن را اندازه بگیرید و در صورت نیاز Job مستقل Validation با برنامه صریح تعریف کنید.

### نصب فایل بررسی‌شده و تنظیم زمان‌بندی

```bash
# From the directory containing the downloaded/reviewed article templates:
sudo install -d -o oracle -g oinstall -m 0700 /backup/oracle/pieces /backup/oracle/logs
sudo install -o root -g root -m 0755 oracle-rman-backup.sh /usr/local/sbin/oracle-rman-backup.sh
sudo bash -n /usr/local/sbin/oracle-rman-backup.sh
sudo install -o root -g root -m 0644 oracle-rman.env /etc/sysconfig/oracle-rman
sudo install -o root -g root -m 0644 'oracle-rman@.service' /etc/systemd/system/
sudo install -o root -g root -m 0644 oracle-rman-daily.timer oracle-rman-weekly.timer oracle-rman-archivelog.timer /etc/systemd/system/
sudo install -o root -g root -m 0644 oracle-rman.logrotate /etc/logrotate.d/oracle-rman
sudo logrotate --debug /etc/logrotate.d/oracle-rman
sudo systemd-analyze verify '/etc/systemd/system/oracle-rman@.service' /etc/systemd/system/oracle-rman-*.timer
sudo systemctl daemon-reload
# First bootstrap a completed level 0 before the regular schedule:
sudo systemctl start oracle-rman@level0.service
sudo systemctl status oracle-rman@level0.service --no-pager
sudo systemctl enable --now oracle-rman-daily.timer oracle-rman-weekly.timer oracle-rman-archivelog.timer
sudo systemctl list-timers 'oracle-rman*'
sudo journalctl -u 'oracle-rman@*' -n 100 --no-pager
```

```ini
# Non-secret backup policy; approve licensing and usable keystore first.
RMAN_ENCRYPTION=ON
MIN_FREE_KIB=10485760
ALLOW_CLEANUP=no
```

```ini
[Unit]
Description=Custom RMAN backup job (%i), not database startup
RequiresMountsFor=/backup/oracle /oradata /fra
After=local-fs.target

[Service]
Type=oneshot
User=oracle
Group=oinstall
EnvironmentFile=/etc/sysconfig/oracle-rman
ExecStart=/usr/local/sbin/oracle-rman-backup.sh %i
UMask=0077
TimeoutStartSec=12h
Nice=10
StandardOutput=journal
StandardError=journal
```

### Differential Level 1 روزانه؛ دوشنبه تا شنبه ساعت 02:15

```ini
[Unit]
Description=Daily differential level 1: Monday–Saturday, 02:15

[Timer]
OnCalendar=Mon..Sat *-*-* 02:15:00
Persistent=true
AccuracySec=1min
Unit=oracle-rman@level1.service

[Install]
WantedBy=timers.target
```

### Level 0 هفتگی؛ یکشنبه ساعت 02:15

```ini
[Unit]
Description=Weekly level 0: Sunday, 02:15

[Timer]
OnCalendar=Sun *-*-* 02:15:00
Persistent=true
AccuracySec=1min
Unit=oracle-rman@level0.service

[Install]
WantedBy=timers.target
```

### Archived Redo هر ۱۵ دقیقه

```ini
[Unit]
Description=Archived redo every 15 minutes

[Timer]
OnCalendar=*-*-* *:00/15:00
Persistent=true
AccuracySec=1min
Unit=oracle-rman@archivelog.service

[Install]
WantedBy=timers.target
```

```text
/backup/oracle/logs/backup.log {
    daily
    maxsize 100M
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    su oracle oinstall
    create 0600 oracle oinstall
}
```

Calendar از Timezone محلی سرور استفاده می‌کند، نه Timezone بازدیدکننده سایت؛ timedatectl و خروجی systemd-analyze calendar را ثبت کنید. Persistent=true یک Activation ازدست‌رفته را هنگام بازگشت Timer جبران می‌کند ولی همه Intervalهای غایب را تکرار یا RPO را تضمین نمی‌کند. Archive Timer ممکن است با Level 0 طولانی تداخل و Exit 75 بدهد؛ Gap طولانی Archive باید Alert و Retry Policy بررسی‌شده داشته باشد. فقط یک Scheduler استفاده و Cron معادل را هم‌زمان فعال نکنید. Timeout برابر 12h و Nice=10 مثال‌اند و به مدت واقعی Backup وابسته‌اند. Logrotate فعال و وضعیت Job به Collector مانیتور ارسال شود.

```bash
systemd-analyze calendar 'Mon..Sat *-*-* 02:15:00'
systemd-analyze calendar 'Sun *-*-* 02:15:00'
systemd-analyze calendar '*-*-* *:00/15:00'
systemctl --failed
systemctl show oracle-rman@level0.service oracle-rman@level1.service oracle-rman@archivelog.service -p Result -p ExecMainStatus
journalctl -t oracle-rman --since '24 hours ago' --no-pager
# After verified independent off-host copies and retention review ONLY:
# set ALLOW_CLEANUP=yes in /etc/sysconfig/oracle-rman for this controlled action,
# then restore it to no immediately afterward.
# sudo systemctl start oracle-rman@retention.service
```

انتقال Off-host را خودکار با یک فایل Timestamp تأیید نکنید. Checksum/Manifest بررسی‌شده و تأیید Durable از Platform Storage برای هر Piece، Redo لازم و Controlfile/SPFILE داشته باشید؛ Wallet/Config را با فرآیند امن مستقل کپی کنید. Storage مستقل Off-host یا Immutable باید Failure Domain و Credential Domain جدا داشته باشد. Mount محلی Backup یا Replica همه خرابی میزبان، حذف و Ransomware را پوشش نمی‌دهد. Retention فقط پس از تأیید خارجی زنجیره Recovery اجرا می‌شود؛ حذف Archive Input عمداً در Script Backup نیست. رشد FRA را اندازه و Archive Deletion Policy را با نیاز واقعی Data Guard/Off-host تطبیق دهید.

## ۸. سناریوهای Restore و Recovery دیتابیس

عملیات مخرب: SHUTDOWN سرویس را قطع، RESTORE فایل دیتابیس را بازنویسی و OPEN RESETLOGS پس از Recovery ناقص/Controlfile یک Incarnation جدید ایجاد می‌کند. مجوز Recovery بگیرید، Traffic برنامه را ایزوله، Redo باقی‌مانده و فایل آسیب‌دیده را حفظ، DBID/Incarnation و Recovery Point را تأیید و Oracle Home/RU سازگار، Storage Mapping، زنجیره Backup، Controlfile/SPFILE و کلید رمزگشایی را بررسی کنید. مثال‌ها سناریوهای مستقل‌اند؛ همه را به‌شکل Script متوالی اجرا نکنید. روی دیتابیس درحال کار Restore نکنید و Clone ایزوله را به برنامه Production وصل نکنید.

### سناریوی ۱؛ Restore از Full یا Level 0 با Controlfile جاری

```bash
# Connect locally on the recovery target as authorized oracle:
rman target /
```

```rman
# Outage; current controlfile and SPFILE survive, original paths exist:
SHUTDOWN IMMEDIATE;
STARTUP MOUNT;
RESTORE DATABASE PREVIEW;
RESTORE DATABASE;
RECOVER DATABASE;
# ONLY when complete recovery succeeded using the current controlfile:
ALTER DATABASE OPEN;
EXIT;
```

RMAN از Full/Level 0 موجود انتخاب می‌کند؛ برای انتخاب نسخه قدیمی‌تر Tag یا UNTIL بررسی‌شده لازم است. Recovery کامل به تمام Archived Redo لازم و در جای مناسب Online Redo باقی‌مانده نیاز دارد. اگر RMAN Log غایب می‌خواهد، Gap را شناسایی کنید؛ تغییر به RESETLOGS درمان برنامه Recovery کامل ناقص نیست. با Controlfile بازیابی‌شده یا PITR عمدی، فرآیند مستقل RESETLOGS زیر کاربرد دارد.

[پیش‌نیاز و فرآیند Complete Database Recovery](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-complete-database-recovery.html)

### سناریوی ۲؛ Recovery با Backupهای Incremental

```rman
# MOUNTED recovery target; backups copied to the dedicated mount:
CATALOG START WITH '/backup/oracle/pieces/' NOPROMPT;
LIST BACKUP OF DATABASE;
RESTORE DATABASE PREVIEW;
RESTORE DATABASE;
RECOVER DATABASE;
# Current controlfile + complete recovery only:
ALTER DATABASE OPEN;
EXIT;
```

RECOVER DATABASE، Level 1 مناسب و سپس Redo را اعمال می‌کند؛ فایل Incremental را دستی به هم وصل نمی‌کنید. زنجیره Differential به تمام Level 1 مربوط از Parent نیاز دارد؛ Cumulative وابستگی را کم می‌کند ولی همچنان Level 0 و Redo لازم است. CATALOG فقط Piece را Register می‌کند و محتوا را تأیید یا Parent غایب را ایجاد نمی‌کند. Baseline قدیمی کافی برای تمام Recovery Window نگه دارید.

[مرجع RECOVER و اعمال Incremental](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/RECOVER.html)

### سناریوی ۳؛ Restore مربوط به Archived Redo و ادامه Media Recovery

```rman
# MOUNTED recovery target, existing datafiles/controlfile valid:
RUN {
  SET ARCHIVELOG DESTINATION TO '/fra/restored-archivelogs';
  RESTORE ARCHIVELOG FROM SEQUENCE 1200 UNTIL SEQUENCE 1250 THREAD 1;
  RECOVER DATABASE;
}
EXIT;
```

Sequence برابر 1200–1250 مثال اختصاصی محیط است؛ Thread/Sequence واقعی را از Recovery Request و LIST BACKUP OF ARCHIVELOG تعیین کنید. Directory موقت را با Ownership مربوط به oracle و فضای کافی از قبل بسازید. Restore مربوط به Redo، Log را آماده می‌کند؛ RECOVER آن را روی Datafile اعمال می‌کند. Sequence غایب موردنیاز برای SCN مقصد، رسیدن به آن نقطه را ناممکن می‌کند. تنها Online Redo باقی‌مانده را بازنویسی نکنید.

[انتخاب دیتابیس و Archive Log در RESTORE](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/RESTORE.html)

### سناریوی ۴؛ Database Point-in-Time Recovery

```rman
# DESTRUCTIVE: sample time MUST be replaced by the approved database-local time.
SHUTDOWN IMMEDIATE;
STARTUP MOUNT;
RUN {
  SET UNTIL TIME "TO_DATE('2026-10-08 10:30:00','YYYY-MM-DD HH24:MI:SS')";
  RESTORE DATABASE;
  RECOVER DATABASE;
}
ALTER DATABASE OPEN RESETLOGS;
LIST INCARNATION;
# New baseline after the database/PDB acceptance checks:
BACKUP INCREMENTAL LEVEL 0 DATABASE PLUS ARCHIVELOG;
EXIT;
```

نقطه پیش از تراکنش مخرب را انتخاب، Timezone محلی دیتابیس را مشخص و ترجیحاً SCN دقیق را تأیید کنید. SET UNTIL باید پیش از RESTORE و RECOVER باشد تا RMAN، Backup قبل از مقصد را انتخاب کند. PITR تغییر بعد از آن نقطه را کنار می‌گذارد و در این مثال کل CDB را تحت تأثیر قرار می‌دهد. شاهد Incident را حفظ، Incarnation جدید را ثبت، سازگاری PDB/برنامه را تست و Baseline جدید بگیرید. PITR فقط PDB پیش‌نیاز Auxiliary/Undo اضافه دارد و در این مثال کل CDB پوشش داده نشده است.

[Database PITR، SET UNTIL و RESETLOGS](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-performing-flashback-dbpitr.html)

### سناریوی ۵؛ خرابی Storage با فقدان Controlfile و SPFILE

```rman
# Recovery host only. Replace this EXAMPLE DBID before use.
SET DBID 1234567890;
STARTUP FORCE NOMOUNT;
RUN {
  SET CONTROLFILE AUTOBACKUP FORMAT FOR DEVICE TYPE DISK TO '/backup/oracle/pieces/%F';
  RESTORE SPFILE FROM AUTOBACKUP;
}
SHUTDOWN IMMEDIATE;
STARTUP NOMOUNT;
RUN {
  SET CONTROLFILE AUTOBACKUP FORMAT FOR DEVICE TYPE DISK TO '/backup/oracle/pieces/%F';
  RESTORE CONTROLFILE FROM AUTOBACKUP;
}
ALTER DATABASE MOUNT;
CATALOG START WITH '/backup/oracle/pieces/' NOPROMPT;
RESTORE DATABASE PREVIEW;
RESTORE DATABASE;
RECOVER DATABASE;
# Backup controlfile recovery requires RESETLOGS after successful recovery.
ALTER DATABASE OPEN RESETLOGS;
EXIT;
```

این مثال فرض می‌کند مسیر Storage اصلی بازسازی، Autobackup، همه Redo لازم و کلید رمزگشایی در دسترس‌اند. SET DBID باید شناسه واقعی ذخیره‌شده باشد، نه عدد نمونه. بدون SPFILE، RMAN می‌تواند برای جست‌وجوی Autobackup با Parameter File موقت NOMOUNT کند؛ در صورت نیاز PFILE حداقلی بررسی‌شده بدهید. Path و Memory در SPFILE بازیابی‌شده باید برای میزبان Recovery مناسب باشد؛ در غیر این صورت به PFILE بازیابی و ویرایش کنید. جست‌وجوی Autobackup بازه محدود دارد؛ در صورت نیاز MAXDAYS یا Piece دقیق تأییدشده استفاده کنید. برای Path جدید، SET NEWNAME و SWITCH DATAFILE ALL فقط پس از بررسی Mapping تمام Datafileهای CDB/PDB، Controlfile، Redo و Tempfile کاربرد دارد.

[Recovery پیشرفته، فایل غایب و برنامه‌ریزی میزبان Recovery](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-recovery-advanced.html)

### سناریوی ۶ و ۷؛ اعتبارسنجی و Test Restore ایزوله

```rman
LIST BACKUP SUMMARY;
RESTORE DATABASE PREVIEW;
RESTORE DATABASE VALIDATE;
RESTORE ARCHIVELOG FROM TIME 'SYSDATE-1' VALIDATE;
EXIT;
```

Check خوانایی، RTO، سازگاری برنامه یا Disaster Recovery کامل را اثبات نمی‌کند. میزبان ایزوله با Platform پشتیبانی‌شده و RU سازگار، بدون DNS/IP/Service Registration و Integration خروجی Production، Storage مستقل و کلید منتقل‌شده امن بسازید. سناریوی Lost-file/Full/PITR مناسب بالا را با Path بررسی‌شده اجرا کنید؛ Restore دارای شناسه Production باید ایزوله بماند. پیش از بازکردن Clone، Scheduler Job و Integration خارجی را طبق برنامه Parameter/Application بازیابی غیرفعال کنید. Open State همه PDBها، Row Count/قاعده کسب‌وکار، Login، Smoke Query برنامه و دسترسی کلید را تست کنید. مدت Restore، SCN/Time حاصل، Gap مربوط به Redo و Evidence را ثبت و سپس داده تست را امن حذف کنید.

## ۹. Monitoring و عیب‌یابی

### سلامت Database، Session، Tablespace، FRA و Backup

```sql
-- Authorized DBA in CDB$ROOT:
SELECT instance_name, status, database_status, startup_time FROM v$instance;
SELECT name, open_mode, log_mode, current_scn FROM v$database;
SELECT name, open_mode, restricted FROM v$pdbs;
SELECT con_id, username, status, COUNT(*) AS sessions FROM v$session
 WHERE type='USER' GROUP BY con_id, username, status ORDER BY con_id, username;
SELECT sid, serial#, con_id, username, event, blocking_session
 FROM v$session WHERE type='USER' AND status='ACTIVE';
SELECT name, space_limit, space_used, space_reclaimable, number_of_files
 FROM v$recovery_file_dest;
SELECT dest_id, status, destination, error FROM v$archive_dest
 WHERE status <> 'INACTIVE';
SELECT session_key, input_type, status, start_time, end_time,
       output_bytes_display, time_taken_display FROM v$rman_backup_job_details
 ORDER BY start_time DESC FETCH FIRST 20 ROWS ONLY;
SELECT name, value FROM v$diag_info;
-- Application PDB space; allocated/max utilization needs growth-budget interpretation:
ALTER SESSION SET CONTAINER=ORCLPDB1;
SELECT tablespace_name, used_percent FROM dba_tablespace_usage_metrics
 ORDER BY used_percent DESC;
SELECT tablespace_name, file_name, bytes, autoextensible, maxbytes
 FROM dba_data_files ORDER BY tablespace_name;
EXIT
```

```bash
# Host, listener and boot diagnostics:
df -hT /opt/oracle /oradata /fra /backup/oracle
df -i /opt/oracle /oradata /fra /backup/oracle
findmnt --mountpoint /backup/oracle
free -h
iostat -xz 1 5
lsnrctl status LISTENER
lsnrctl services LISTENER
sudo systemctl status oracledb_ORCLCDB-26ai.service --no-pager
sudo journalctl -u oracledb_ORCLCDB-26ai.service -b -n 100 --no-pager
sudo journalctl -k -b -n 100 --no-pager
adrci exec="show homes"
# Interactive ADRCI: choose the ACTUAL database home returned above.
adrci
# ADRCI prompt examples (substitute the actual home path):
# set homepath diag/rdbms/orclcdb/ORCLCDB
# show alert -tail 100
# exit
sudo ausearch -m AVC,USER_AVC -ts recent
sudo tail -n 100 /backup/oracle/logs/backup.log
sudo journalctl -t oracle-rman --since '24 hours ago' --no-pager
```

از v$diag_info برای مسیر واقعی ADR و Alert Log استفاده کنید؛ حروف کوچک/بزرگ مسیر دیتابیس به نصب وابسته است. ADRCI، Home مربوط به Listener را نیز نشان می‌دهد. used_percent مربوط به Tablespace را همراه MAXBYTES مربوط به Autoextend و ظرفیت Filesystem پایش کنید؛ Tablespace ممکن است فضای رشد نشان دهد ولی Volume زیرین پر باشد. Blocked Session مداوم، جهش Active Session، I/O Latency، Job ناموفق، آخرین Backup موفق قدیمی و تأیید Off-host را Alert کنید. Threshold بر اساس Baseline واقعی باشد. این مثال از Dictionary/Dynamic View عادی استفاده می‌کند و به Pack دارای مجوز نیاز ندارد.

[مدیریت Audit و Retention کنترل‌شده](https://docs.oracle.com/en/database/oracle/oracle-database/26/arpls/DBMS_AUDIT_MGMT.html)

[بررسی Alert Log با ADRCI](https://docs.oracle.com/en/database/oracle/oracle-database/26/sutil/oracle-adr-command-interpreter-adrci.html)

| خطا / نشانه | علت و Diagnostic | اقدام اصلاحی |
| --- | --- | --- |
| ORA-01034 / ORA-27101؛ Startup ناموفق | SID/Home اشتباه، Instance خاموش، Mount غایب یا مشکل Memory/Parameter؛ Environment، v$instance در صورت دسترسی، Journal و ADR را بررسی کنید. | Mount لازم و Parameter/Permission مصوب را اصلاح، با Owner سرویس Restart و PDB را بررسی کنید؛ دیتابیس را دوباره نسازید. |
| ORA-12541؛ Listener موجود نیست | IP/Port اشتباه، Listener خاموش، Route یا Firewall؛ lsnrctl status، ss و DNS کلاینت را مقایسه کنید. | Endpoint/Rule دقیق را اصلاح و Listener را با Owner مربوط به RPM یا SRVCTL شروع کنید؛ از Subnet مجاز تست کنید. |
| ORA-12514؛ سرویس ناشناخته | سرویس PDB اشتباه یا PDB بسته/ثبت‌نشده؛ SHOW PDBS، v$services و lsnrctl services را بررسی کنید. | Service Name واقعی PDB را استفاده، PDB را Open/Save State و ALTER SYSTEM REGISTER کنید؛ در تداوم مشکل Endpoint مربوط به local_listener را بررسی کنید. |
| ORA-01017 / ORA-28000 / ORA-28001 | Credential/Container اشتباه، حساب Lock یا Expire؛ account_status را در PDB صحیح و Audit Return Code را بررسی کنید. | Failed Login را بررسی، رمز را با Prompt امن Rotate و Secret Manager را به‌روز کنید؛ Unlock فقط پس از بررسی و Policy را ضعیف نکنید. |
| ORA-19809 / ORA-00257 | Quota/FRA پر یا Archive Failure؛ v$recovery_file_dest، ERROR مقصد Archive و df را بررسی کنید. | دسترسی مقصد یا ظرفیت/Quota مصوب را اصلاح یا پس از Backup تأییدشده Cleanup بررسی‌شده RMAN اجرا کنید؛ Redo لازم را با rm حذف نکنید. |
| RMAN-06023 / RMAN-06025 | Backup مناسب Datafile/Log وجود ندارد یا DBID/Incarnation/UNTIL اشتباه و Piece منتقل‌نشده است. | LIST BACKUP و Preview را بررسی و Piece منتقل‌شده تأییدشده را CATALOG کنید؛ جزء غایب را دریافت یا Recovery Point قابل دستیابی را تأیید کنید. |
| ORA-19504 / ORA-27040؛ نوشتن Backup ناموفق | Mount غایب، Disk/Inode پر، Ownership یا AVC مربوط به SELinux؛ df، namei و Audit Log را بررسی کنید. | Storage یا Ownership/Context محدود را اصلاح و دوباره اجرا کنید؛ Guard مربوط به Storage در Script، Exit 73 دارد؛ chmod 777 یا Disable SELinux نکنید. |
| ORA-28365 / ORA-19913؛ Recovery رمز‌شده ناموفق | Keystore بسته/غایب، تاریخچه Key اشتباه یا Credential رمزنگاری Backup غایب است. | Keystore امن نگهداری‌شده و Key صحیح را Restore، با فرآیند مجاز Open و در محیط ایزوله Retry کنید؛ بدون Key لازم Recovery ممکن نیست. |

[پیام خطا و راهنمای اصلاح رسمی Oracle](https://docs.oracle.com/en/error-help/db/)

## ۱۰. چک‌لیست استقرار Production

- [ ] Certification مربوط به OS/Kernel/RU، مجوز EE و Inventory مربوط به Option تأیید و Hash رسانه ثبت شده است.
- [ ] CPU/RAM/IOPS، Swap، Limits و ظرفیت رشد اندازه‌گیری و Mountهای لازم پس از Boot برقرارند.
- [ ] CDB، PDB، RU مربوط به Binary و SQL Patch Registry با Release مصوب یکسان‌اند.
- [ ] منشأ سرویس و Enable تأیید؛ Reboot، دیتابیس OPEN، PDB دارای State ذخیره‌شده و Query Remote را اثبات کرده است.
- [ ] Listener فقط آدرس مجاز دارد و تست اتصال برنامه و رد Source غیرمجاز کامل است.
- [ ] Firewalld و SELinux فعال؛ ACL، عضویت OSDBA و Permission فایل خصوصی بررسی شده است.
- [ ] User نام‌دار محدود، Rotation رمز، دسترسی اضطراری و Audit Forwarding بررسی شده است.
- [ ] Transport رمز‌شده، CA/DN Validation و Alert انقضا تست؛ کلید TDE/Backup دارای مجوز امن است.
- [ ] ARCHIVELOG/FRA، برنامه Level 0/1، Job پانزده‌دقیقه Redo و زنجیره Recovery بررسی شده است.
- [ ] Failure/Overlap، موفقیت قدیمی، تأخیر Off-host و Alert ظرفیت به Owner مسئول می‌رسد.
- [ ] Piece مستقل Off-host/Immutable، Controlfile/SPFILE و Backup کلید/Config بررسی شده است.
- [ ] Validation خوانایی Backup و Restore Drill ایزوله، RPO/RTO واقعی و سازگاری برنامه را اثبات کرده‌اند.
- [ ] حذف Retention به انتقال زنجیره Recovery تأییدشده وابسته است و Cleanup کور بر اساس عمر فایل وجود ندارد.
- [ ] Monitoring دیتابیس/Tablespace/FRA/Storage، Runbook رخداد و برنامه Patch/Rollback جاری ثبت شده است.

## جمع‌بندی؛ پذیرش به شاهد Recovery نیاز دارد

RPM رسمی EE فقط آغاز استقرار Production است. پذیرش یعنی Platform تأییدشده و Patchشده، Startup پس از Boot اثبات‌شده، دسترسی محدود Client، Transport رمز‌شده، Backup کامل مستقل و Restore ایزوله مطابق RPO/RTO کسب‌وکار. در هر تغییر، Runbook را با Home، RU، Owner سرویس، مجوز و شاهد Recovery واقعی تطبیق دهید.

## پرسش‌های متداول

### آیا Package نسخه 26ai همان Database Free است؟

خیر؛ این راهنما Enterprise Edition را نصب می‌کند. Free دارای Package، نام سرویس و محدودیت مستقل است.

### چرا دانلود 26ai شماره 23.26.1 دارد؟

26ai نام Release محصول است؛ شماره داخلی/RU متفاوت است. نسخه نصب‌شده Binary و SQL Patch را بررسی کنید.

### آیا active در systemctl آمادگی Oracle را اثبات می‌کند؟

خیر؛ Instance، وضعیت PDB، سرویس Listener و Query احراز هویت‌شده Remote را بررسی کنید.

### آیا Oracle Restart، Host Failover می‌دهد؟

Componentها را روی میزبان مستقل پایش و Restart می‌کند. Host Failover به راهکار Availability جدا نیاز دارد.

### آیا Full Backup عادی Parent مربوط به Level 1 است؟

خیر؛ برای زنجیره Level 1 از Baseline نوع Incremental Level 0 استفاده کنید.

### آیا Validation مربوط به RMAN جای Test Restore را می‌گیرد؟

خیر؛ فایل منتخب و خوانایی Backup را بررسی می‌کند. فقط Restore ایزوله، فرآیند Recovery و RTO واقعی را اثبات می‌کند.

### آیا Disk محلی Backup کافی است؟

خیر؛ کپی Off-host مستقل، Redo لازم و Backup کلید/Config با انتقال تأییدشده نگه دارید.

### آیا رمزنگاری Disk در RMAN در EE پایه داخل سازمان شامل است؟

برای این استقرار Oracle Advanced Security لازم است؛ پیش از فعال‌سازی مجوز را بررسی کنید.

## منابع رسمی و Templateهای بررسی‌شده

مستندات در ۹ اکتبر ۲۰۲۶ بررسی شده‌اند. منابع کنار هر فرآیند را بخوانید، Certification/RU جاری MOS و README مربوط به Patch را دوباره بررسی و Config اختصاصی را پیش از استقرار تست کنید. Template قابل دانلود Credential واقعی ندارد. اجرای Linux/Oracle این فایل‌ها در Workspace ویندوزی Repository تست نشده است.

[ویژگی‌ها و انتشار بلندمدت رسمی Oracle AI Database 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/nfcoa/all-nfg.html)

[دانلود Enterprise Linux x86-64 و Checksum رسمی](https://www.oracle.com/database/technologies/oracle26ai-linux-downloads.html)

[محدودیت‌های رسمی Oracle Database Free](https://www.oracle.com/database/free/faq/)

[مجوز Oracle 26ai؛ Feature، Option و Pack مجاز](https://docs.oracle.com/en/database/oracle/oracle-database/26/dblic/Licensing-Information.html)

[مفاهیم و معماری فیزیکی Oracle Database](https://docs.oracle.com/en/database/oracle/oracle-database/26/cncpt/introduction-to-oracle-database.html)

[معماری رسمی Multitenant، CDB و PDB](https://docs.oracle.com/en/database/oracle/oracle-database/26/multi/introduction-to-the-multitenant-architecture.html)

[حداقل سخت‌افزار رسمی](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/server-hardware-checklist-for-oracle-database-installation.html)

[Kernelهای OL9 و Packageهای موردنیاز](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/supported-oracle-linux-9-distributions-for-x86-64.html)

[تنظیم سرور، Swap و برنامه‌ریزی Memory](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/server-configuration-checklist-for-oracle-database-installation.html)

[پیش‌نیاز رسمی فضای نرم‌افزار و ظرفیت Patch](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/storage-checklist-for-oracle-database-installation.html)

[پیکربندی سیستم‌عامل با Preinstallation RPM رسمی](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/automatically-configuring-oracle-linux-with-oracle-preinstallation-rpm.html)

[نصب RPM مربوط به EE و Script رسمی Configure](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/running-rpm-packages-to-install-oracle-database.html)

[فعال‌سازی سرویس و معنای Boot در OL9](https://docs.oracle.com/en/operating-systems/oracle-linux/9/systemd/EnablingandDisablingServices.html)

[عملیات Start، Stop و Status در OL9](https://docs.oracle.com/en/operating-systems/oracle-linux/9/systemd/StartingandStoppingServices.html)

[مالکیت، Dependency و SRVCTL در Oracle Restart](https://docs.oracle.com/en/database/oracle/oracle-database/26/admin/configuring-automatic-restart-of-an-oracle-database.html)

[نصب GI مستقل نسخه 26ai با gridSetup.sh](https://docs.oracle.com/en/database/oracle/oracle-database/26/ladbi/installing-and-configuring-oracle-grid-infrastructure-for-a-standalone-server.html)

[احراز هویت و Password Profile رسمی Oracle](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-authentication.html)

[CREATE USER و احراز هویت Schema-only](https://docs.oracle.com/en/database/oracle/oracle-database/26/sqlrf/CREATE-USER.html)

[فرمان PASSWORD در SQL*Plus](https://docs.oracle.com/en/database/oracle/oracle-database/26/sqpug/PASSWORD.html)

[Parameterهای Listener و بررسی Registration](https://docs.oracle.com/en/database/oracle/oracle-database/26/netrf/oracle-net-listener-parameters-in-listener-ora.html)

[پیکربندی Zone در Oracle Linux firewalld](https://docs.oracle.com/en/operating-systems/oracle-linux/9/firewall/firewall-ConfiguringfirewalldZones.html)

[مدیریت SELinux در Oracle Linux](https://docs.oracle.com/en/operating-systems/oracle-linux/selinux/)

[ایجاد و فعال‌سازی Unified Audit Policy](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-audit-policies.html)

[Wallet، Parameterهای TLS_ و DN Matching در 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbseg/configuring-transport-layer-security-encryption.html)

[Transparent Data Encryption و مدیریت کلید](https://docs.oracle.com/en/database/oracle/oracle-database/26/dbtde/introduction-to-transparent-data-encryption.html)

[هشدار امنیتی و Critical Patch Update رسمی Oracle](https://www.oracle.com/security-alerts/)

[مفاهیم Full و Incremental Backup در RMAN](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-backup-concepts.html)

[مدیریت ARCHIVELOG و مقصد Redo](https://docs.oracle.com/en/database/oracle/oracle-database/26/admin/managing-archived-redo-log-files.html)

[پیکربندی RMAN، FRA و Retention](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/configuring-rman-client-basic.html)

[Retention رکورد Backup در Controlfile و Recovery Catalog](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/maintaining-rman-backups.html)

[Backup دیتابیس، Archived Redo، Control File و SPFILE](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/backing-up-database.html)

[مرجع فرمان BACKUP در RMAN](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/BACKUP.html)

[Modeها و الگوریتم رمزنگاری RMAN در 26ai](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/configuring-rman-client-advanced.html)

[Validation در RMAN و محدودیت آن](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/validating-database-files-backups.html)

[پیش‌نیاز و فرآیند Complete Database Recovery](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-complete-database-recovery.html)

[مرجع RECOVER و اعمال Incremental](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/RECOVER.html)

[انتخاب دیتابیس و Archive Log در RESTORE](https://docs.oracle.com/en/database/oracle/oracle-database/26/rcmrf/RESTORE.html)

[Database PITR، SET UNTIL و RESETLOGS](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-performing-flashback-dbpitr.html)

[Recovery پیشرفته، فایل غایب و برنامه‌ریزی میزبان Recovery](https://docs.oracle.com/en/database/oracle/oracle-database/26/bradv/rman-recovery-advanced.html)

[مدیریت Audit و Retention کنترل‌شده](https://docs.oracle.com/en/database/oracle/oracle-database/26/arpls/DBMS_AUDIT_MGMT.html)

[بررسی Alert Log با ADRCI](https://docs.oracle.com/en/database/oracle/oracle-database/26/sutil/oracle-adr-command-interpreter-adrci.html)

[پیام خطا و راهنمای اصلاح رسمی Oracle](https://docs.oracle.com/en/error-help/db/)

[دانلود Template بررسی‌شده: listener-hardening.ora](/downloads/oracle-database-26ai-installation-oracle-linux/listener-hardening.ora)

[دانلود Template بررسی‌شده: tls-integration.ora](/downloads/oracle-database-26ai-installation-oracle-linux/tls-integration.ora)

[دانلود Template بررسی‌شده: rman-baseline.rman](/downloads/oracle-database-26ai-installation-oracle-linux/rman-baseline.rman)

[دانلود Template بررسی‌شده: oracle-rman-backup.sh](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman-backup.sh)

[دانلود Template بررسی‌شده: oracle-rman.env](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman.env)

[دانلود Template بررسی‌شده: oracle-rman@.service](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman@.service)

[دانلود Template بررسی‌شده: oracle-rman-daily.timer](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman-daily.timer)

[دانلود Template بررسی‌شده: oracle-rman-weekly.timer](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman-weekly.timer)

[دانلود Template بررسی‌شده: oracle-rman-archivelog.timer](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman-archivelog.timer)

[دانلود Template بررسی‌شده: oracle-rman.logrotate](/downloads/oracle-database-26ai-installation-oracle-linux/oracle-rman.logrotate)
