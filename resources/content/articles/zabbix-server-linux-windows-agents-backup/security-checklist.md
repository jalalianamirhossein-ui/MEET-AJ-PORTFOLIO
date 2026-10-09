# Security hardening checklist

English / فارسی

## Acceptance checklist / چک‌لیست پذیرش

- [ ] Trusted HTTPS chain, hostname and renewal tested / زنجیره معتبر HTTPS، نام Host و تمدید آزموده شد
- [ ] Unique per-host PSK or certificate, encrypted agent connections only / PSK یکتا یا Certificate هر Host و ارتباط رمز‌شده
- [ ] Source firewall allowlists and isolated database verified / Allowlist مبدا Firewall و Database ایزوله بررسی شد
- [ ] Restricted database role and protected configuration permissions / Role محدود Database و Permission محافظت‌شده Config
- [ ] Default credentials replaced; RBAC and supported MFA tested / Credential پیش‌فرض تغییر و RBAC و MFA پشتیبانی‌شده آزموده شد
- [ ] Audit logging and expiring API tokens reviewed / Audit Log و Token API با انقضا بررسی شد
- [ ] Backup archives encrypted off-site; keys recoverable from separate vault / Archive بکاپ در Off-site رمز‌شده و Key در Vault مستقل قابل بازیابی
- [ ] Isolated restore and notification safeguards tested / Restore ایزوله و کنترل ایمنی اعلان آزموده شد

## 8. Security hardening

![HTTPS and MFA, scoped roles/API, Agent TLS, firewall allowlists and local PostgreSQL security layers](/assets/img/articles/content/zabbix-security-hardening.png)

HTTPS and MFA, scoped roles/API, Agent TLS, firewall allowlists and local PostgreSQL security layers

### HTTPS and certificate lifecycle

Issue a real FQDN certificate from enterprise CA or supported ACME DNS-01 for private access. Install chain/key below with root 0600 key and 0700 directory; root Nginx master reads it. In the existing packaged Nginx server block replace bootstrap listen/server_name and add directives below; preserve application locations and FastCGI. Do not create competing blocks or generic PHP rules exposing /etc/zabbix.

```nginx
# Inside existing /etc/zabbix/nginx.conf server block:
listen 192.0.2.10:443 ssl;
server_name zabbix.example.com;
ssl_certificate /etc/ssl/zabbix/fullchain.pem;
ssl_certificate_key /etc/ssl/zabbix/privkey.pem;
ssl_protocols TLSv1.2 TLSv1.3;
ssl_session_cache shared:ZabbixTLS:10m;
ssl_session_timeout 1d;
add_header Strict-Transport-Security "max-age=31536000" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "same-origin" always;
# Keep packaged root/index/PHP location/deny rules/fastcgi_pass.
# Enable HSTS only after HTTPS and renewal validation.
```

```bash
sudo install -d -o root -g root -m 0700 /etc/ssl/zabbix
# Securely install approved certificate and key before testing Nginx.
sudo chmod 0600 /etc/ssl/zabbix/privkey.pem
sudo chmod 0644 /etc/ssl/zabbix/fullchain.pem
sudo nginx -t
sudo php-fpm8.3 -t
sudo systemctl reload nginx
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com \
  -CAfile /path/to/enterprise-ca.pem -verify_return_error </dev/null
sudo ss -lntp
sudo ufw status numbered
```

Expected: valid config, HTTPS 200/redirect, verified chain/FQDN; never curl -k. Confirm bootstrap 8080 gone and 5432 loopback-only. Schedule CA/ACME renewal, test it, run nginx -t before reload in deploy hook, and alert on expiry/failure. Set HTTPS PHP pool session.cookie_secure=1, session.cookie_httponly=1, session.cookie_samesite=Lax and retest sessions.

### Agent authentication and least privilege

Unique random per-host PSK, TLSConnect=psk, TLSAccept=psk and matching frontend directions. Shared fleet keys multiply compromise risk. Certificate alternative uses TLSConnect/TLSAccept=cert, TLSCAFile, TLSCertFile, TLSKeyFile and allowed issuer/subject; configure host UI constraints and renewal/revocation. HTTPS and agent certificates are separate. No unencrypted fallback or bypassed peer verification.

```conf
# Replace PSK settings for a reviewed certificate deployment:
TLSConnect=cert
TLSAccept=cert
TLSCAFile=/etc/zabbix/keys/ca.pem
TLSCertFile=/etc/zabbix/keys/agent-chain.pem
TLSKeyFile=/etc/zabbix/keys/agent.key
TLSServerCertIssuer=CN=Monitoring CA,O=Example
TLSServerCertSubject=CN=zabbix.example.com,O=Example
```

- Firewall and Agent Server source allowlists agree; restrict 10051, discovery and auto-registration.
- Linux agent runs as zabbix, DenyKey=system.run[*], UnsafeUserParameters=0 and fixed reviewed commands; no broad sudo/writable scripts.
- PostgreSQL local SCRAM/peer, no trust; remote DB needs verify-full TLS and database-only firewall.
- Config, PSKs, web credentials, scripts and backups have restricted ownership/modes; review AppArmor denials, grant narrowly, never disable profile.

### Strong administration, RBAC, MFA, audit and API

Users → User groups controls host-group access; User roles controls UI/API methods. Operators read-only, service owners limited to their hosts, named administrators few. Keep a vaulted emergency account with tested recovery. Zabbix 7.0 supports TOTP and Duo: Users → Authentication → MFA, enroll users and enforce group method after testing recovery/IdP behavior. Limit sessions/remove dormant accounts.

Enable Administration → General → Audit log, review Reports → Audit log, define retention and central protected log forwarding. API: dedicated least-privilege service user, explicit role method allowlist, expiring API token in a secret manager, HTTPS only. Use /api_jsonrpc.php with Authorization: Bearer and JSON-RPC; prove an allowed read and denied write, rotate/revoke and audit. Never hard-code token or use query URLs.

[PSK authentication](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_pre_shared_keys)

[Certificate encryption](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_certificates)

[Zabbix 7.0 MFA](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/authentication/mfa)

[Role/API controls](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/user_roles)

[Supported API authentication](https://www.zabbix.com/documentation/7.0/en/manual/api)

[Nginx HTTPS reference](https://nginx.org/en/docs/http/configuring_https_servers.html)


## ۸. امن‌سازی

![لایه امنیت HTTPS و MFA، Role/API محدود، TLS Agent، Allowlist Firewall و PostgreSQL محلی](/assets/img/articles/content/zabbix-security-hardening.png)

لایه امنیت HTTPS و MFA، Role/API محدود، TLS Agent، Allowlist Firewall و PostgreSQL محلی

### HTTPS و چرخه Certificate

Certificate FQDN واقعی از CA سازمان یا ACME DNS-01 پشتیبانی‌شده برای دسترسی خصوصی. Chain/Key در مسیر زیر، Key با root 0600 و Directory با 0700؛ Master Nginx با root می‌خواند. در Server Block موجود، listen/server_name اولیه جایگزین و Directive زیر؛ Location/FastCGI حفظ. Block متعارض یا PHP عمومی افشاکننده /etc/zabbix نباشد.

```nginx
# Inside existing /etc/zabbix/nginx.conf server block:
listen 192.0.2.10:443 ssl;
server_name zabbix.example.com;
ssl_certificate /etc/ssl/zabbix/fullchain.pem;
ssl_certificate_key /etc/ssl/zabbix/privkey.pem;
ssl_protocols TLSv1.2 TLSv1.3;
ssl_session_cache shared:ZabbixTLS:10m;
ssl_session_timeout 1d;
add_header Strict-Transport-Security "max-age=31536000" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "same-origin" always;
# Keep packaged root/index/PHP location/deny rules/fastcgi_pass.
# Enable HSTS only after HTTPS and renewal validation.
```

```bash
sudo install -d -o root -g root -m 0700 /etc/ssl/zabbix
# Securely install approved certificate and key before testing Nginx.
sudo chmod 0600 /etc/ssl/zabbix/privkey.pem
sudo chmod 0644 /etc/ssl/zabbix/fullchain.pem
sudo nginx -t
sudo php-fpm8.3 -t
sudo systemctl reload nginx
curl -I --cacert /path/to/enterprise-ca.pem https://zabbix.example.com/
openssl s_client -connect zabbix.example.com:443 -servername zabbix.example.com \
  -CAfile /path/to/enterprise-ca.pem -verify_return_error </dev/null
sudo ss -lntp
sudo ufw status numbered
```

انتظار: Config معتبر، HTTPS 200/Redirect و Chain/FQDN تأیید؛ هرگز curl -k. حذف 8080 اولیه و محلی بودن 5432. Renewal CA/ACME زمان‌بندی و تست، Deploy Hook ابتدا nginx -t سپس Reload و Alert انقضا/شکست. Pool PHP HTTPS با session.cookie_secure=1، session.cookie_httponly=1 و session.cookie_samesite=Lax و تست Session.

### احراز هویت Agent و کمترین مجوز

PSK تصادفی یکتای Host، TLSConnect=psk، TLSAccept=psk و جهت UI منطبق. Key مشترک Fleet ریسک نفوذ را تکثیر می‌کند. گزینه Certificate با TLSConnect/TLSAccept=cert، TLSCAFile، TLSCertFile، TLSKeyFile و Issuer/Subject مجاز؛ Constraint UI و Renewal/Revocation. Certificate HTTPS از Agent جدا است. بدون Fallback ساده یا دورزدن Peer Verification.

```conf
# Replace PSK settings for a reviewed certificate deployment:
TLSConnect=cert
TLSAccept=cert
TLSCAFile=/etc/zabbix/keys/ca.pem
TLSCertFile=/etc/zabbix/keys/agent-chain.pem
TLSKeyFile=/etc/zabbix/keys/agent.key
TLSServerCertIssuer=CN=Monitoring CA,O=Example
TLSServerCertSubject=CN=zabbix.example.com,O=Example
```

- Allowlist Firewall و Server Agent منطبق؛ محدودیت 10051، Discovery و Auto-registration.
- Agent Linux با zabbix، DenyKey=system.run[*]، UnsafeUserParameters=0 و فرمان ثابت بررسی‌شده؛ بدون sudo گسترده/Script قابل نوشتن.
- PostgreSQL محلی SCRAM/Peer، بدون trust؛ DB Remote نیازمند TLS verify-full و Firewall اختصاصی.
- Config، PSK، Credential وب، Script و Backup دارای Owner/Mode محدود؛ Denial AppArmor بررسی و مجوز باریک، نه غیرفعال‌سازی Profile.

### مدیریت قوی، RBAC، MFA، Audit و API

Users → User groups دسترسی Host Group و User roles متد UI/API را کنترل می‌کند. Operator Read-only، مالک سرویس فقط Host خودش و Administrator نام‌دار اندک. حساب اضطراری Vault با Recovery تست‌شده. Zabbix 7.0 با TOTP و Duo: Users → Authentication → MFA، Enroll و الزام روش Group بعد تست Recovery/IdP. Session محدود/حساب راکد حذف.

Administration → General → Audit log فعال، Reports → Audit log بررسی، Retention و ارسال مرکزی Log محافظت‌شده. API: کاربر سرویس کم‌مجوز، Method Allowlist صریح Role، Token انقضادار در Secret Manager و فقط HTTPS. /api_jsonrpc.php با Authorization: Bearer و JSON-RPC؛ Read مجاز و Write ردشده، Rotate/Revoke و Audit. Token ثابت یا Query URL ممنوع.

[احراز هویت PSK](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_pre_shared_keys)

[رمزنگاری Certificate](https://www.zabbix.com/documentation/7.0/en/manual/encryption/using_certificates)

[MFA در Zabbix 7.0](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/authentication/mfa)

[کنترل Role/API](https://www.zabbix.com/documentation/7.0/en/manual/web_interface/frontend_sections/users/user_roles)

[احراز هویت API رسمی](https://www.zabbix.com/documentation/7.0/en/manual/api)

[مرجع HTTPS در Nginx](https://nginx.org/en/docs/http/configuring_https_servers.html)

