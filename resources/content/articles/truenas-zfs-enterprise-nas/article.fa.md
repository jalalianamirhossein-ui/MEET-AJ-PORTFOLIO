# راه‌اندازی TrueNAS از صفر؛ ساخت NAS سازمانی با ZFS

**مخاطب:** مدیران زیرساخت، System Administratorها، DevOps و Network Engineerها؛ سطح Intermediate تا Advanced.

**وضعیت راهنما:** این راهنما برای TrueNAS مبتنی بر Linux نوشته شده است؛ مسیرهای نمونه با مستندات نسخهٔ 25.04 تطبیق داده شده‌اند. نام بعضی منوها ممکن است بین Releaseهای پایدار تغییر جزئی داشته باشد؛ قبل از اجرا، مستندات همان Release را بررسی و در محیط آزمایشی Validate کنید.

![راهکار Enterprise Storage با TrueNAS](/assets/img/articles/banners/TrueNAS%20Enterprise%20Storage%20Solution.png)

*تصویر ۱ — TrueNAS به‌عنوان Storage مرکزی برای File Sharing، Virtualization، Backup و Replication.*

> **هشدار عملیاتی:** RAID و Snapshot به‌تنهایی Backup نیستند. برای داده‌های Production، قانون 3-2-1، نسخه خارج از سایت و Remote Replication را هم‌زمان اجرا کنید.

## مقدمه

TrueNAS یک پلتفرم Storage مبتنی بر ZFS است که SMB، NFS، iSCSI، Snapshot، Replication، ACL، مانیتورینگ و Alerting را در یک رابط مدیریتی ارائه می‌کند. قدرت TrueNAS فقط در ساختن یک File Server نیست؛ ارزش اصلی آن در ترکیب Integrity، Redundancy، مشاهده‌پذیری و Recovery عملیاتی است.

این مقاله یک Runbook کامل از طراحی تا بهره‌برداری است و در تمام مراحل تفاوت **Home Lab** و **Production** را مشخص می‌کند.

## 1. TrueNAS چیست؟

TrueNAS سیستم‌عاملی تخصصی برای ساخت NAS و Storage Server است. در مدل‌های فعلی، ZFS/OpenZFS لایه اصلی مدیریت Pool و حفاظت داده است و سرویس‌های File و Block Storage از طریق Web UI مدیریت می‌شوند.

برای Production، نصب Bare Metal روی سخت‌افزار سروری، ECC RAM، Boot SSDهای Mirror، HBA مناسب، UPS، شبکه مجزا و Backup قابل‌بازیابی را در نظر بگیرید.

## 2. NAS چیست و TrueNAS چه مشکلی را حل می‌کند؟

NAS فایل‌ها را از طریق پروتکل‌هایی مانند SMB و NFS در اختیار کاربران و میزبان‌ها قرار می‌دهد. TrueNAS علاوه بر قابلیت NAS، با iSCSI ذخیره‌سازی بلوکی نیز ارائه می‌کند؛ این کاربرد از نظر معماری در دستهٔ SAN قرار می‌گیرد. یک File Server عمومی معمولاً به RAID Controller، ابزار Backup، ابزار Snapshot، Monitoring و فرآیندهای جداگانه نیاز دارد.

TrueNAS این قابلیت‌ها را به‌صورت یکپارچه ارائه می‌کند، اما جایگزین معماری Backup، Disaster Recovery یا تیم متخصص نیست.

## 3. کاربردهای TrueNAS در سازمان

- **File Server:** فایل‌های واحدها، Project Share و Home Directory با SMB.
- **Central Storage:** Datasetهای مستقل برای تیم‌ها، سرویس‌ها و سطح‌های امنیتی مختلف.
- **Backup Repository:** مقصد Backup نرم‌افزارهایی مانند Veeam یا ابزارهای Native سیستم‌عامل.
- **VMware/Hyper-V Storage:** ارائه NFS یا iSCSI با Storage VLAN مجزا.
- **SMB:** مناسب برای Windows و احراز هویت Local یا Active Directory.
- **NFS:** مناسب برای Linux، Unix و VMware.
- **iSCSI:** Block Storage مبتنی بر ZVOL برای Virtualization و Workloadهای Block-Level.
- **Snapshot:** بازیابی سریع فایل یا Dataset به یک Point-in-Time.
- **Replication:** انتقال Snapshotهای ZFS به TrueNAS دوم در سایت یا Rack جدا.

## 4. مزایای TrueNAS نسبت به File Server معمولی

| ویژگی | TrueNAS | Windows File Server | Synology/QNAP |
|---|---|---|---|
| مدل استقرار | Bare Metal یا Appliance؛ VM برای آزمایش و با بررسی محدودیت‌های پشتیبانی | معمولاً VM یا Bare Metal | Appliance یکپارچه |
| File Protocol | SMB، NFS، iSCSI | SMB، NFS با Role/Feature | SMB، NFS، iSCSI |
| File Integrity | Checksum و Self-Healing با ZFS | وابسته به File System/Storage | وابسته به پلتفرم و File System |
| Snapshot/Replication | Native با ZFS | نیازمند ابزار و Storage مناسب | معمولاً ساده‌تر و Vendor-specific |
| انعطاف Hardware | زیاد | زیاد | محدود به Appliance |
| مدیریت | عمیق و مناسب تیم فنی | آشنا برای تیم Microsoft | ساده‌تر برای تیم کوچک |
| پیچیدگی | بیشتر؛ نیازمند دانش ZFS | متوسط | کمتر در سناریوهای رایج |
| هزینه | مناسب Self-Build؛ نسخه و Support را جدا بررسی کنید | License و CAL/Software | هزینه Appliance و Support |
| بهترین کاربرد | Storage مهندسی‌شده و قابل‌کنترل | Domain/File Services متمرکز | SMB سازمانی کوچک تا متوسط |

## 5. حداقل و Recommended Hardware برای Production

### حداقل رسمی برای شروع

- CPU سازگار با x86_64
- حداقل 8 GB RAM
- SSD برای Boot با ظرفیت حداقل 20 GB مطابق راهنمای نصب 25.04؛ دیسک Boot از دیسک‌های Pool جدا باشد
- حداقل دو Disk مشابه برای یک Pool

### پیشنهاد Production

- 32 تا 64 GB یا بیشتر ECC RAM، متناسب با تعداد Disk و Workload
- دو SSD برای Boot به‌صورت Mirror
- HBA در حالت IT/HBA و بدون Hardware RAID پنهان‌کننده Disk
- Diskهای NAS/Enterprise تست‌شده و Burn-in شده
- 10 GbE برای VM، iSCSI و Backup سنگین
- PSU و مسیر برق Redundant، UPS و IPMI/BMC
- Hot-Swap Bay و فضای کافی برای Rebuild و رشد

### Home Lab در برابر Production

در Home Lab می‌توان با 8 تا 16 GB RAM، یک Boot SSD و شبکه 1 GbE یادگیری را شروع کرد. در Production باید Downtime، RPO/RTO، Monitoring، Replace کردن Disk و Restore واقعی را طراحی کنید. USB Flash را برای نصب موقت استفاده کنید، نه Boot دائمی Production.

## 6. معماری ZFS و مفاهیم کلیدی

### Pool و VDEV

Pool فضای ذخیره‌سازی اصلی است و از یک یا چند VDEV ساخته می‌شود. نوع VDEV میزان ظرفیت، Performance و تحمل خرابی را تعیین می‌کند. پس از ایجاد Pool، تغییر بنیادی Layout آسان نیست؛ قبل از Create کردن، Workload و رشد آینده را محاسبه کنید.

### Dataset و ZVOL

Dataset یک File System مستقل با ACL، Quota، Compression و Snapshot جداگانه است. ZVOL یک Block Device مجازی است و عموماً برای iSCSI و بعضی VMها به‌کار می‌رود.

ساختار پیشنهادی:

```text
tank/
├── shares/finance
├── shares/hr
├── shares/projects
├── backups
├── vmstore
└── replication
```

### Mirror و RAIDZ1/2/3

- **Mirror:** شبیه RAID1؛ مناسب IOPS و Latency حساس مانند VM و Database.
- **RAIDZ1:** تحمل خرابی یک Disk؛ برای Diskهای بزرگ و داده‌های مهم با احتیاط.
- **RAIDZ2:** تحمل خرابی دو Disk؛ انتخاب متعادل برای File Serverهای سازمانی.
- **RAIDZ3:** تحمل خرابی سه Disk؛ مناسب Poolهای بزرگ یا آرشیوی با Rebuild طولانی.

### ARC، Compression، Scrub و Snapshot

ARC Cache اصلی ZFS در RAM است. Compression مانند `lz4` معمولاً انتخاب مناسبی است. Scrub Checksumها را بررسی و در صورت وجود Redundancy، خطا را اصلاح می‌کند. Snapshot نسخه Point-in-Time است، اما چون روی همان Pool ذخیره می‌شود، Backup مستقل محسوب نمی‌شود.

Deduplication را بدون محاسبه RAM و تست واقعی فعال نکنید؛ مصرف RAM آن می‌تواند بسیار زیاد باشد.

![معماری ZFS در TrueNAS](/assets/img/articles/content/TrueNAS%20ZFS%20Architecture%20Infographic.png)

*تصویر ۲ — رابطه Pool، VDEV، Dataset، ZVOL و قابلیت‌های حفاظتی ZFS.*

## 7. طراحی شبکه سازمانی

![توپولوژی شبکه TrueNAS Enterprise](/assets/img/articles/content/TrueNAS%20Enterprise%20Network%20Topology.png)

*تصویر ۳ — تفکیک Management، User/LAN، Storage و Backup/Replication.*

```text
Internet → Firewall → Core Switch
                         ├── Management VLAN 10.10.10.0/24
                         ├── User/LAN VLAN 10.10.20.0/24
                         ├── Storage VLAN 10.10.30.0/24
                         └── Backup VLAN 10.10.40.0/24
```

Web UI را فقط روی Management VLAN قرار دهید. SMB روی LAN، NFS/iSCSI روی Storage VLAN و Replication روی Backup VLAN یا لینک اختصاصی قرار گیرد. Storage VLAN را به Internet Route نکنید.

## 8. دانلود ISO و ساخت Bootable USB

1. ISO را فقط از صفحه رسمی TrueNAS دانلود کنید.
2. Checksum یا Signature را بررسی کنید.
3. با Rufus یا Etcher USB نصب بسازید.
4. برای سیستم‌های جدید از GPT/UEFI استفاده کنید.
5. USB را بعد از نصب جدا کنید.

USB نصب با Boot Device دائمی تفاوت دارد؛ Boot Device Production بهتر است SSD داخلی یا M.2 با کیفیت مناسب باشد.

## 9. نصب TrueNAS روی Bare Metal

1. از USB Boot کنید و Install/Upgrade را انتخاب کنید.
2. Boot SSD را انتخاب کنید؛ Diskهای دیتا را هرگز انتخاب نکنید.
3. در صورت وجود دو SSD، هر دو را برای Boot Pool انتخاب کنید.
4. هشدار پاک‌شدن Disk را تأیید کنید.
5. رمز Administrator را تنظیم کنید.
6. UEFI را انتخاب و نصب را کامل کنید.
7. پس از Restart، USB را جدا کنید.
8. IP نمایش‌داده‌شده در Console را در مرورگر باز کنید:

```text
https://<TRUENAS-IP>
```

مسیرهای رسمی: `Storage → Pools`، `Datasets` و `System Settings`.

## 10. تنظیم Static IP، DNS، Gateway و NTP

مسیر GUI:

```text
Network → Interfaces
```

نمونه:

```text
IPv4: 10.10.10.20/24
Gateway: 10.10.10.1
DNS: 10.10.10.10, 10.10.10.11
Hostname: truenas01.example.local
```

NTP را از مسیر `System Settings → Services → NTP` با NTP داخلی سازمان تنظیم کنید. DNS و NTP صحیح برای Active Directory، TLS و Replication ضروری‌اند.

## 11. ساخت Storage Pool

برای چهار Disk هشت ترابایتی File Server:

```text
Storage → Pools → Create Pool
Pool: tank
Layout: RAIDZ2
Compression: lz4
```

Diskهای Boot را انتخاب نکنید. برای VMهای پرتراکنش، دو Mirror VDEV معمولاً Latency و IOPS بهتری از RAIDZ ارائه می‌دهد. Capacity، نوع داده و زمان Rebuild را پیش از Create مقایسه کنید.

## 12. ساخت Dataset

```text
Datasets → Add Dataset
```

برای SMB، Preset را `SMB`؛ برای NFS و iSCSI، `Generic` انتخاب کنید. `Compression=lz4` و `Deduplication=Off` نقطه شروع مناسبی است. برای داده‌های حیاتی Sync را روی مقدار پیش‌فرض Production نگه دارید و آن را بدون آزمون روی Disabled نگذارید.

هر Share باید Child Dataset مستقل داشته باشد؛ Root Dataset یا Pool-level Dataset را Share نکنید.

## 13. ایجاد User و Group

```text
Credentials → Local Users
Credentials → Local Groups
```

گروه‌هایی مانند `finance-rw`، `finance-ro` و `it-admins` بسازید و Permission را به Group بدهید. در سازمان Domainمحور:

```text
Credentials → Directory Services → Active Directory
```

قبل از Join شدن، DNS، NTP، FQDN و دسترسی به Domain Controller را تست کنید.

## 14. Security Hardening

- Web UI را روی Internet منتشر نکنید؛ فقط Management VLAN، VPN یا Bastion.
- از HTTPS و Certificate معتبر سازمانی استفاده کنید.
- Least Privilege و Group-Based ACL را اجرا کنید.
- Admin Account جدا از User Account داشته باشید.
- MFA را در صورت پشتیبانی Release و روش احراز هویت فعال کنید.
- SMB/NFS/iSCSI را روی Subnet مناسب محدود کنید.
- Management، Storage و Backup VLAN را جدا کنید.
- برای Ransomware، Snapshot Retention، Remote Replication و نسخه Offline داشته باشید.
- Serviceهای بلااستفاده را فعال نکنید.

## 15. راه‌اندازی SMB برای Windows

Dataset بسازید:

```text
Name: projects
Preset: SMB
```

سپس:

```text
Shares → Windows Shares (SMB) → Add
Path: /mnt/tank/shares/projects
Name: Projects
```

از `System Settings → Services → SMB` سرویس را Start و در صورت نیاز Start Automatically را فعال کنید. ACL را از `Datasets → Edit Permissions` و بر اساس Group تنظیم کنید.

![راهنمای SMB در TrueNAS](/assets/img/articles/content/TrueNAS%20SMB%20Sharing%20Setup%20Guide.png)

*تصویر ۴ — ساخت Dataset، ACL و SMB Share برای کاربران Windows.*

## 16. Map Network Drive در Windows

در File Explorer، روی This PC گزینه Map Network Drive را انتخاب کنید و مسیر زیر را وارد کنید:

```text
\\truenas01.example.local\Projects
```

با PowerShell:

```powershell
New-PSDrive -Name P -PSProvider FileSystem `
  -Root "\\truenas01.example.local\Projects" -Persist
```

Credentialها را در Script عمومی ذخیره نکنید.

## 17. راه‌اندازی NFS برای Linux و VMware

Dataset با Preset `Generic` بسازید و سپس:

```text
Shares → Unix Shares (NFS) → Add
Path: /mnt/tank/vmware_nfs
Authorized Networks: 10.10.30.0/24
```

در Linux:

```bash
sudo mkdir -p /mnt/truenas
sudo mount -t nfs truenas01:/mnt/tank/vmware_nfs /mnt/truenas
showmount -e truenas01
```

در VMware، از `Add Datastore → NFS` استفاده کنید و فقط Hostهای مجاز Storage VLAN را اجازه دهید.

## 18. iSCSI برای Virtualization

iSCSI Block Storage ارائه می‌کند. اجزای آن شامل Portal، Initiator، Target، Extent و Association است.

1. یک ZVOL بسازید: `Datasets → Add Zvol`.
2. به `Shares → Block Shares (iSCSI)` بروید.
3. Portal و Initiator را تعریف کنید.
4. Target و Extent را بسازید.
5. آن‌ها را Associate کنید.

iSCSI را روی Storage VLAN، با 10 GbE یا بیشتر، Latency کنترل‌شده و در صورت نیاز Multipath اجرا کنید. ZVOL را بیش از ظرفیت واقعی Pool تخصیص ندهید.

## 19. Snapshot خودکار

```text
Data Protection → Periodic Snapshot Tasks → Add
```

نمونه Retention:

```text
Hourly: 24 hours
Daily: 30 days
Weekly: 12 weeks
Monthly: 12 months
```

Snapshot برای Recovery سریع است؛ اگر Pool اصلی از بین برود، Snapshot همان Pool نیز از بین می‌رود.

## 20. ZFS Replication به TrueNAS دوم

ابتدا TrueNAS دوم، SSH Credential، مقصد و فضای کافی را آماده کنید. سپس:

```text
Data Protection → Replication Tasks → Add
Source: tank/shares
Destination: truenas02/tank-replica/shares
Recursive: Enabled
Schedule: every 30 minutes
Transport: SSH
```

مقصد را در سایت دوم یا حداقل Rack/اتاق جدا قرار دهید و Restore را دوره‌ای تست کنید. Replication بدون Retention مناسب ممکن است خطای منطقی یا حذف مخرب را نیز منتقل کند؛ Snapshotهای مقصد را مدیریت کنید.

![Snapshot و Replication در TrueNAS](/assets/img/articles/content/TrueNAS%20Snapshots%20and%20Replication%20Infographic.png)

*تصویر ۵ — تفاوت Snapshot محلی با Replication و نسخه خارج از سایت.*

## 21. Backup از Configuration

```text
System Settings → General → Manage Configuration → Download File
```

فایل Configuration را رمزنگاری و در سیستم مدیریت امن، سایت دوم و Storage خارج از TrueNAS نگهداری کنید. Configuration Backup، Backup داده‌های Pool نیست.

## 22. سناریوی واقعی شرکت متوسط

برای 80 کاربر، 10 VM و حدود 30 TB داده:

```text
TrueNAS-01: 8×12 TB, RAIDZ2, 64 GB ECC, 2×10 GbE, Boot SSD Mirror
TrueNAS-02: 8×12 TB, RAIDZ2, 64 GB ECC, second site
```

Datasetها:

```text
tank/shares/finance
tank/shares/hr
tank/shares/projects
tank/backups
tank/vmware_nfs
tank/replication
```

SMB روی User VLAN، NFS/iSCSI روی Storage VLAN، Snapshot ساعتی و روزانه، Replication هر 30 دقیقه به سایت دوم و یک Copy هفتگی خارج از سایت، الگوی مناسبی برای شروع است. Capacity نهایی را بر اساس داده واقعی، رشد سالانه، Retention و فضای لازم برای Rebuild محاسبه کنید.

## 23. SMART و Scrub

SMART:

```text
Storage → Disks → S.M.A.R.T. Tests
Short: daily or weekly
Long: monthly
```

Scrub:

```text
Data Protection → Scrub Tasks → Add
Pool: tank
Schedule: monthly
```

تست‌های Long را در ساعات کم‌مصرف اجرا کنید و Alertها را به Email یا سیستم Monitoring ارسال کنید.

## 24. Monitoring و Alerting

از مسیرهای زیر استفاده کنید:

```text
Dashboard
System Settings → Alerts
Reporting
```

هشدارهای Pool Degraded، Disk Failure، SMART، Low Space، Scrub Error، Replication Failure، Temperature، Certificate Expiration، DNS/NTP و Service Down را فعال کنید. Email Alert را با SMTP سازمان تست کنید و در صورت نیاز SNMP، Syslog، Prometheus یا Grafana را اضافه کنید.

## 25. Troubleshooting اولیه و Commandهای Diagnostic

```bash
zpool status
zpool list
zfs list
zfs list -o name,used,avail,refer,mountpoint
lsblk
ip addr
ip route
ping <gateway>
dig truenas01.example.local
showmount -e <truenas-ip>
```

در Windows:

```powershell
Test-NetConnection truenas01 -Port 445
```

ابتدا Dashboard، Pool، SMART، فضای آزاد، DNS، NTP، VLAN، Firewall، ACL، Service و آخرین Snapshot/Replication را بررسی کنید. دستورات تغییر‌دهنده ZFS را بدون Runbook و تأیید اجرا نکنید.

## 26. اشتباهات رایج

1. **RAID جای Backup نیست.** در برابر حذف، Ransomware، آتش‌سوزی و خرابی کامل کافی نیست.
2. **Snapshot جای Backup نیست.** Snapshot روی همان Pool قرار دارد.
3. **RAIDZ را برای هر Workload انتخاب نکنید.** VM و Database اغلب به Mirror VDEV نیاز دارند.
4. **USB را Boot Disk دائمی Production نکنید.**
5. **RAM را دست‌کم نگیرید.** ARC، SMB، VM، iSCSI و Deduplication RAM می‌خواهند.
6. **Root Dataset را Share نکنید.** Child Dataset مستقل بسازید.
7. **Deduplication را بدون محاسبه و تست فعال نکنید.**
8. **Replication بدون Restore Test کافی نیست.** Backup باید قابل‌بازیابی باشد.

## 27. Checklist نهایی Production Deployment

- [ ] ECC RAM، Boot SSD Mirror، HBA، UPS و IPMI آماده است.
- [ ] Diskها Burn-in و SMART شده‌اند.
- [ ] Management، User، Storage و Backup VLAN جدا شده‌اند.
- [ ] DNS، FQDN، Gateway و NTP صحیح است.
- [ ] Pool Layout با Workload و Rebuild Window هماهنگ است.
- [ ] Compression فعال و Deduplication آگاهانه انتخاب شده است.
- [ ] Root Dataset Share نشده و Datasetهای مستقل ساخته شده‌اند.
- [ ] ACL و Groupها تست شده‌اند.
- [ ] SMB/NFS/iSCSI فقط به Clientهای مجاز دسترسی دارند.
- [ ] Snapshot Schedule و Retention مستند است.
- [ ] Remote Replication به مقصد دوم فعال است.
- [ ] Backup خارج از سایت و نسخه Offline وجود دارد.
- [ ] Configuration Backup رمزنگاری و خارج از دستگاه ذخیره شده است.
- [ ] SMART، Scrub، Alerting و Monitoring فعال است.
- [ ] Restore و Disaster Recovery به‌صورت عملی تست شده است.
- [ ] RPO، RTO، Runbook تعویض Disk و فرآیند Escalation مستند است.

## چه زمانی TrueNAS انتخاب مناسبی است و چه زمانی نیست؟

TrueNAS انتخاب مناسبی است اگر به کنترل کامل روی Storage، ZFS، Snapshot، Replication، SMB/NFS/iSCSI، انعطاف Hardware و هزینه قابل‌کنترل نیاز دارید و تیم شما دانش Storage، Network و Recovery را دارد.

انتخاب مناسبی نیست اگر تیمی برای مدیریت ZFS ندارید، فقط چند Disk نامشابه و بدون Backup در اختیار است، انتظار Appliance کاملاً بدون طراحی دارید، Web UI قرار است روی Internet منتشر شود، یا بدون HA و سایت دوم به Availability بسیار بالا نیاز دارید. در این شرایط، Appliance پشتیبانی‌شده، Managed Storage یا راهکار Vendorمحور ممکن است انتخاب منطقی‌تری باشد.

## پرسش‌های متداول TrueNAS

### آیا RAIDZ و Snapshot جای نسخهٔ پشتیبان را می‌گیرند؟

خیر. RAIDZ برای تحمل خرابی دیسک و Snapshot برای بازگشت به یک زمان مشخص است. برای خرابی کل Pool، حذف مخرب و حادثهٔ سایت، نسخهٔ مستقل و خارج از سایت لازم است.

### تفاوت Dataset و ZVOL چیست؟

Dataset یک فایل‌سیستم با سهمیه، ACL و Snapshot مستقل است و برای اشتراک SMB یا NFS استفاده می‌شود. ZVOL یک دستگاه بلوکی است و معمولاً پشت Extent در iSCSI قرار می‌گیرد.

### برای ماشین‌های مجازی Mirror مناسب‌تر است یا RAIDZ2؟

انتخاب به IOPS، تأخیر، ظرفیت و الگوی بار بستگی دارد. Mirror VDEVها معمولاً برای بار تصادفی ماشین‌های مجازی مناسب‌اند؛ RAIDZ2 ظرفیت و تحمل خرابی دو دیسک در هر VDEV را ارائه می‌کند. انتخاب نهایی را با بار واقعی آزمایش کنید.

### چرا دیسک Boot باید از دیسک‌های Pool جدا باشد؟

نصب TrueNAS دیسک انتخاب‌شده را پاک می‌کند. جداسازی Boot از داده‌ها خطر انتخاب اشتباه و تداخل نقش دیسک‌ها را کم می‌کند. قبل از نصب، مدل و شمارهٔ سریال دیسک مقصد را بررسی کنید.

### چگونه دسترسی به SMB را محدود کنیم؟

Dataset مستقل بسازید، ACL را به گروه‌های موردنیاز بدهید و دسترسی شبکه به سرویس را محدود کنید. با یک کاربر مجاز و یک کاربر غیرمجاز، خواندن، نوشتن و حذف را آزمایش کنید.

### آیا فعال کردن Deduplication همیشه فضای بیشتری آزاد می‌کند؟

خیر. نتیجه به تکرار واقعی داده‌ها و منابع سیستم بستگی دارد و ممکن است هزینهٔ حافظه و کارایی از صرفه‌جویی بیشتر باشد. بدون اندازه‌گیری و آزمایش، آن را فعال نکنید.

### موفق بودن Replication برای اطمینان از بازیابی کافی است؟

خیر. علاوه بر بررسی Job و Snapshotهای مقصد، بازیابی فایل یا Dataset را عملاً آزمایش کنید. Retention مقصد و کلیدهای رمزنگاری را جدا از سرور اصلی نگهداری کنید.

### هنگام Degraded شدن Pool چه کاری انجام دهیم؟

از Dashboard و zpool status وضعیت را بررسی کنید، دیسک معیوب را با شمارهٔ سریال شناسایی و سلامت Backup را تأیید کنید. تعویض را از روش پشتیبانی‌شدهٔ رابط TrueNAS انجام دهید و پیشرفت Resilver را پایش کنید.

## منابع رسمی

- [TrueNAS Hardware Guide](https://www.truenas.com/docs/scale/25.04/gettingstarted/scalehardwareguide/)
- [Installing TrueNAS](https://www.truenas.com/docs/scale/25.04/gettingstarted/install/installingscale/)
- [Setting Up Storage](https://www.truenas.com/docs/scale/25.04/gettingstarted/configure/setupstoragescale/)
- [Setting Up Data Sharing](https://www.truenas.com/docs/scale/25.04/gettingstarted/configure/setupsharing/)
- [TrueNAS Configuration Instructions](https://www.truenas.com/docs/scale/25.04/gettingstarted/configure/)
