# Grafana backup and restore / بکاپ و بازیابی گرافانا

## 13. Grafana backup and practical recovery

![Security and recovery: HTTPS ingress, least privilege, protected encryption key, consistent metadata backup and isolated restore](/assets/img/articles/content/grafana-security-backup-architecture.png)

Security and recovery: HTTPS ingress, least privilege, protected encryption key, consistent metadata backup and isolated restore

Back up Grafana metadata separately from the Zabbix monitoring database. Protect /etc/grafana/grafana.ini, provisioning files, root-only environment files, the encryption secret, systemd overrides, plugin files and version inventory, file-provisioned dashboards, Nginx configuration and TLS certificate/private-key state. UI dashboard exports are useful portable artifacts, but do not contain usable data-source secrets or all organization settings. Export dashboard JSON via the dashboard sharing/export menu or provision reviewed JSON from version control.

### SQLite and PostgreSQL consistency

Copying a live SQLite grafana.db alone can miss journal/WAL state. The supplied backup briefly stops Grafana, creates a SQLite .backup snapshot, checks PRAGMA integrity_check, archives configuration/plugins, and restarts Grafana even on failure. Schedule this maintenance window and silence only planned service-availability checks. It assumes the downloaded sqlite3/path settings and a healthy local service; customize deliberately if using a different path or multi-instance architecture.

For a separate Grafana PostgreSQL metadata database set GRAFANA_DB_TYPE=postgres and PG* values for that database, not Zabbix. pg_dump custom format gives a transaction-consistent logical snapshot; pg_restore decoding checks archive readability, not application recovery. Supply a restricted backup role able to read the Grafana schema and a protected PGPASSFILE; do not print its contents. This script still stops Grafana to align configuration/plugin files with the metadata snapshot. Database administrator-managed roles and TLS settings require their own recovery plan.

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

The timer uses 02:15 UTC plus up to 15 minutes of jitter, independent of host timezone, and catches a missed run after boot. The script refuses an unmounted backup volume, obtains a lock, checks capacity, uses restrictive permissions, writes a hidden partial directory, verifies checksums and publishes atomically. Failure exits nonzero and preserves evidence; alert on failed backup units and aging backups through Zabbix or an external scheduler. Retention is intentionally separate from creation: expire backups only after verified encrypted off-site copies and periodic restore drills.

The backup archive contains API tokens, session/encryption configuration and TLS private keys. Store it on encrypted storage with root-only access. Initialize a restic repository separately, store its password in a protected file, and optionally enable RESTIC_REPOSITORY/RESTIC_PASSWORD_FILE for encrypted off-site upload. Keep recovery keys in a separately accessible vault; test retrieval from the recovery site. A successful backup process or checksum is not proof that Grafana can decrypt credentials after restore.

### Restore to an isolated recovery host

- Prepare Ubuntu 24.04 with the exact recorded Grafana release and signed plugin versions. Block notification egress and automatic collectors/backup timers. Restore original host/domain configuration only inside an isolated network until accepted.
- Recover a complete dated backup and verify SHA256SUMS before extracting. Stop Grafana; archive the recovery host’s current configuration separately. Review the tar listing and extract only a trusted backup to the intended host.
- Restore /etc/grafana including the original secret_key and environment files, plugin files, file dashboards, systemd override and Nginx/certificate state. If external KMS/encryption providers were configured, recover their key access too. Losing the original encryption material requires re-entering data-source secrets.
- For SQLite restore the consistent grafana.db and remove any stale target WAL/SHM only while Grafana is stopped and after preserving the target state. For PostgreSQL restore into an empty Grafana database with a compatible pg_restore and the intended role. Never overwrite the Zabbix database.
- Restore file ownership, validate configuration, reload systemd and start Grafana. Verify login, organization/folder permissions, plugin inventory, Save & test, real Linux/Windows queries, NOC counts/freshness and dashboard variables. Validate certificates and renewal separately.
- Measure RPO from the backup timestamp and RTO to accepted monitoring; compare to business targets. Test one approved notification after unblocking only staging transport, record evidence, then cut over DNS and restore production policies through change control.

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

For PostgreSQL use the alternative below on an empty recovery database after its role has been created. Substitute actual protected connection settings and matching source/target versions. pg_restore returns zero when the dump loads; acceptance still requires the UI/API and decryption checks described above. Never run both SQLite and PostgreSQL restore branches.

```bash
sudo -u postgres createdb -O grafana grafana_recovery
sudo install -o postgres -g postgres -m 0600 /srv/grafana-backups/20261009T021500Z/grafana.pgdump /var/lib/postgresql/grafana-recovery.pgdump
sudo -u postgres pg_restore --exit-on-error --no-owner --no-acl --role=grafana --dbname=grafana_recovery /var/lib/postgresql/grafana-recovery.pgdump
sudo rm -f -- /var/lib/postgresql/grafana-recovery.pgdump
# Point recovered Grafana [database] at this recovery DB before starting.
```

[Official Grafana backup scope and SQLite shutdown guidance](https://grafana.com/docs/grafana/latest/administration/back-up-grafana/)

[SQLite online backup API and consistency](https://www.sqlite.org/backup.html)

[PostgreSQL pg_dump consistency](https://www.postgresql.org/docs/16/app-pgdump.html)

[Restic encrypted off-site backup documentation](https://restic.readthedocs.io/en/stable/)


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

