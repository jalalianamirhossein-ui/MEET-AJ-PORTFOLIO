# Restore and disaster recovery runbook

English / فارسی

## 11. Restore and disaster recovery

![Verified database/config backup, encrypted off-site storage, isolated restore drill and WAL-based PITR recovery chain](/assets/img/articles/content/zabbix-backup-disaster-recovery.png)

Verified database/config backup, encrypted off-site storage, isolated restore drill and WAL-based PITR recovery chain

RPO is maximum acceptable lost data/config interval; RTO is maximum acceptable service recovery time. Daily dumps can lose nearly a day plus transfer/scheduling delay; 24-hour RPO is not guaranteed without age monitoring. PITR approaches available WAL archive coverage. Measure retrieval, install, restore, secret recovery and validation for RTO. Plan downtime, communication and a single cutover authority; never run duplicate collecting/alerting servers with the same identity.

1–2. Prepare Ubuntu 24.04 replacement on an isolated recovery VLAN: patches, IP/DNS, NTP, firewall. Block agent/proxy traffic and external SMTP/Telegram, keep backup timer stopped. Install recorded compatible Zabbix 7.0 patch and PostgreSQL 16/PHP/Nginx. Retrieve selected encrypted snapshot into root-only staging, preserve original, verify SHA256 and archive decoding. Never run older Zabbix on a newer schema; startup can irreversibly upgrade schema. For vulnerable recorded packages rehearse a supported patch upgrade on a clone.

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

3. Do not import initial server.sql.gz before pg_restore. Use PostgreSQL 16 pg_restore or documented compatible newer tools. 4–6. Review staged /etc/zabbix, Nginx/PHP-FPM, TLS keys/chain, PostgreSQL settings and custom alert/external scripts; install selectively with recorded owner/mode, reconcile account IDs, IPs, certificate names and interpreter dependencies. Never overwrite PGDATA or OS config blindly. The following copies overwrite replacement configuration and are for the reviewed replacement only. Set DBName=zabbix_recovery/reset DBPassword in server and frontend and add its limited SCRAM pg_hba rule for the drill.

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

8–10. Check System information/schema, counts against recovery record, historical graphs, fresh Linux/Windows lab data, PROBLEM/OK and notification to an isolated test sink. Test RBAC/MFA, HTTPS, scripts, queue, maintenance/dependencies and backup on replacement. Live hosts have stale data while isolated; use cloned lab identities or controlled rerouting, not duplicate collectors. Record restored last sample and elapsed retrieval/start/validation, achieved RPO/RTO. Then approve IP/DNS cutover, alert routes and timer reactivation; preserve rollback source.

### Destructive commands — approved replacement cutover only

dropdb permanently deletes the existing monitoring database. Keep it separate from the new-name drill; only execute on a confirmed replacement after verified backup, recorded host identity and explicit operational approval. Never on the live source. --clean or extracting config over /etc also overwrites state and needs review.

```bash
# DESTRUCTIVE, intentionally commented: confirmed replacement host only.
sudo systemctl stop nginx zabbix-server
# sudo -u postgres dropdb --if-exists zabbix
# sudo -u postgres createdb -O zabbix -E UTF8 -T template0 zabbix
# Restore the verified dump into this EMPTY DB as above, no initial schema import.
```

[pg_restore compatibility and ownership](https://www.postgresql.org/docs/16/app-pgrestore.html)

[Zabbix schema/version upgrade compatibility](https://www.zabbix.com/documentation/7.0/en/manual/installation/upgrade)


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

