# Production security and recovery checklist / چک‌لیست امنیت و بازیابی

## 15. Production acceptance checklist

- [ ] Installation: stable Grafana/plugin versions recorded, APT key verified, systemd enabled, health and reboot checks completed.
- [ ] Security: valid HTTPS chain/SAN, renewal tested, loopback-only 3000, approved source firewall and IPv6 policy, no anonymous access/default password.
- [ ] Identity: named admins, least privilege roles, IdP MFA and removal tested, emergency access documented; OSS data-source visibility understood.
- [ ] Zabbix: verified API path and trusted TLS, scoped owner/token expiry, read permissions and real Save & test plus numeric item queries.
- [ ] Linux: CPU/load/RAM/available memory/filesystem/network/uptime/availability values compared with Latest data and unit conversions checked.
- [ ] Windows: actual discovered services/filesystems/interfaces, valid state mappings, active Event Log item and controlled new-event test completed.
- [ ] NOC: collector scope and permissions reviewed, total=available+unavailable+unknown, Disaster/High counts reconciled, freshness and collector-failure alarms tested.
- [ ] Reliability: variables and mixed-OS shared panels verified, current Problems includes old ongoing events, no-data/error behavior and separate monitoring of Grafana itself.
- [ ] Notifications: one incident owner, contact-point tests authorized, PROBLEM/recovery observed, no duplicate paging, escalation and maintenance policies documented.
- [ ] Performance: representative concurrent dashboards measured, bounded filters, refresh/query interval and history/trend retention matched.
- [ ] Backup: consistent metadata snapshot, complete configuration/secret/plugin/certificate inventory, checksum and encrypted off-site copy with external failure alarms.
- [ ] Recovery: isolated restore validates decryption, login, roles, actual data queries, measured RPO/RTO and separate vault key retrieval.
- [ ] Operations: owner/on-call, patch window, capacity targets, restore schedule, approved rollback and handover evidence recorded.

Acceptance requires an actual Zabbix API connection and representative live data. This article’s downloadable dashboard definitions contain no live metric results and do not claim a successful production integration. Operators must complete the checklist in staging and then their approved production environment.


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

