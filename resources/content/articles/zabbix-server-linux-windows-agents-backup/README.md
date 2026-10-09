# Enterprise Zabbix configuration package / بسته تنظیمات زبیکس

Target: Ubuntu Server 24.04, Zabbix 7.0 LTS, PostgreSQL 16, Nginx 1.24, PHP 8.3; Agent 2 Windows Server 2022/2025 x64. Windows unattended installation requires stable 7.0.22 or newer.

هدف: Ubuntu Server 24.04، Zabbix 7.0 LTS، PostgreSQL 16، Nginx 1.24، PHP 8.3 و Agent 2 برای Windows Server 2022/2025. نصب Silent حداقل 7.0.22 پایدار.

Read the [bilingual website article](https://meetaj.ir/articles/zabbix-server-linux-windows-agents-backup) (select English or فارسی), [restore runbook](restore-runbook.md) and [security checklist](security-checklist.md). Replace all RFC 5737 IPs and example.com names. Templates contain no actual credentials and are not an unattended production deployment. Stable Zabbix 7.0.31 was verified on 9 October 2026; recheck official repository candidates before deployment.

مقاله انگلیسی/فارسی، Runbook و چک‌لیست لینک‌شده را بخوانید. IPهای RFC 5737 و example.com جایگزین شوند. Template فاقد Credential واقعی و استقرار خودکار Production نیست.

| File | Installation / purpose |
|---|---|
| zabbix-agent2-linux.conf | /etc/zabbix/zabbix_agent2.conf, root:zabbix 0640; unique protected PSK |
| zabbix-agent2-windows.conf | Default Program Files Agent 2 configuration; SYSTEM/Administrators ACL |
| install-zabbix-agent2.ps1 | Elevated fresh MSI install; verified SHA256/signature; protected existing PSK input |
| zabbix-backup.sh | /usr/local/sbin/zabbix-backup.sh, root:root 0750 |
| zabbix-backup.env | /etc/zabbix-backup/backup.env, root:root 0600; paths/settings, no DB password |
| zabbix-backup.service / .timer | /etc/systemd/system/, root:root 0644 |
| zabbix-backup-failure.service | Independent notification for unit startup/timeout failure |
| zabbix-backup-notify | /usr/local/sbin/, 0750; edit recipient and configure approved mail relay |
| restore-runbook.md | Isolated new-name database restoration; destructive cutover commands separated |
| security-checklist.md | HTTPS, TLS, firewall, database, MFA/RBAC, audit/API controls |

Prepare an encrypted mounted /backup volume, configuration/TLS paths, PostgreSQL peer role access, restic repository/password file and verified SSH authentication. Review backup.env and notification destination. Initialize restic once, run the backup service manually and inspect archive/off-site evidence before enabling the timer. UTC schedule is 02:15 plus up to 10 minutes jitter; Persistent catches a missed run after boot.

Volume رمزنگاری‌شده Mount‌شده /backup، مسیر Config/TLS، Peer Role PostgreSQL، Repository/File Password restic و SSH تأییدشده آماده شود. backup.env و مقصد اعلان Review؛ restic یک‌بار Init، سرویس دستی، بررسی Archive/Off-site سپس Timer. Schedule ساعت 02:15 UTC با حداکثر ۱۰ دقیقه Jitter؛ Persistent اجرای ازدست‌رفته پس از Boot.

The backup requires root, refuses absent backup mounts/invalid paths, prevents overlap, validates free space, compresses and checks archives, and fails if required off-site upload/check fails. Old successful backups are removed only after the new backup succeeds and the retention cutoff is met. Incomplete sets remain for investigation. Local logs last 90 days. Configure remote retention and independent backup-age alerts separately. Checksums/decoding are not a real restore drill.

Backup با root، رد Mount/Path نامعتبر، جلوگیری Overlap، Space Check، Compression/Integrity و شکست در Off-site لازم ناموفق. قدیمی موفق فقط بعد موفقیت جدید و رسیدن Retention حذف. ناقص برای بررسی باقی؛ Log محلی ۹۰ روز. Retention Remote و Alert مستقل سن Backup جدا. Checksum/Decode، Restore Drill واقعی نیست.

Never commit deployed secrets/configuration archives. Keep independent vault access to encryption keys, SSH credentials, TLS/PSK secrets and database reset procedures. Test notification transport, storage-full and overlap failures in staging; retrieve an off-site set and restore it in isolation. These files were authored and statically validated in a Windows website workspace; no real installation, backup or restore is claimed.

Secret/Archive مستقر به Git نرود. Vault مستقل برای Key Encryption، SSH، TLS/PSK و Reset DB. اعلان، Disk Full و Overlap در Staging تست؛ مجموعه Off-site دریافت و Restore ایزوله. فایل در Workspace ویندوزی سایت نوشته/ایستا بررسی شده؛ ادعای نصب/Backup/Restore واقعی ندارد.
