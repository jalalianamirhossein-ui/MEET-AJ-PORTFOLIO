"""Build the bilingual source consumed by the site's existing article importer."""
from pathlib import Path
from html import escape
import json
import shutil

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'nginx-reverse-proxy-multiple-domains-single-ip-443'
TITLE = {
    'en': 'Nginx Reverse Proxy: Multiple Domains on One IP and Port 443 Using SNI',
    'fa': 'Nginx Reverse Proxy: میزبانی چند دامنه روی یک IP و Port 443 با SNI',
}
DESC = {
    'en': 'Configure five HTTPS domains on one public IP with Nginx SNI, certificates, WebSocket, secure defaults, renewal, testing and enterprise troubleshooting.',
    'fa': 'راه‌اندازی پنج دامنه HTTPS روی یک IP با Nginx و SNI؛ تنظیم Certificate، WebSocket، امنیت، تمدید گواهی، تست و عیب‌یابی عملیاتی در شبکه سازمانی.',
}
KEYWORDS = ['Nginx', 'Reverse Proxy', 'Nginx SNI', 'server_name', 'Multiple Domains', 'Single Public IP', 'HTTPS Reverse Proxy', 'Nginx Virtual Host', 'TLS SNI', 'Nginx Proxy Pass']
SERVICES = [('app', '10.10.10.11', 8080), ('api', '10.10.10.12', 9000), ('jira', '10.10.10.13', 8080), ('grafana', '10.10.10.14', 3000), ('gitlab', '10.10.10.15', 80)]
parts, toc = [], []

def dual(tag, en, fa, attrs=''):
    if tag in ('a', 'figcaption'):
        return f'<{tag} {attrs}>'+dual('span', en, fa)+f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en, quote=True)}" data-fa="{escape(fa, quote=True)}">{escape(en)}</{tag}>'

def p(en, fa):
    parts.append(dual('p', en, fa))

def code(value, lang='bash'):
    parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value.strip())}</code></pre>')

def section(id, en, fa):
    if toc:
        parts.append('</section>')
    toc.append((id, en, fa))
    parts.append(f'<section id="{id}">'+dual('h2', en, fa))

def items(pairs):
    parts.append('<ul>'+''.join(dual('li', en, fa) for en, fa in pairs)+'</ul>')

def link(url, en, fa):
    parts.append('<p>'+dual('a', en, fa, f'href="{url}" rel="noopener"')+'</p>')

def figure(filename, en, fa):
    src = '/assets/img/articles/content/'+filename
    parts.append(f'<figure><img src="{escape(src, quote=True)}" loading="lazy" decoding="async" alt="{escape(en, quote=True)}" data-en-alt="{escape(en, quote=True)}" data-fa-alt="{escape(fa, quote=True)}">'+dual('figcaption',en,fa)+'</figure>')

section('introduction', '1. Introduction', '۱. مقدمه')
code('''                         Internet
                            |
                   Public IP: 203.0.113.10
                            |
                         TCP/443
                            |
                    +---------------+
                    |     NGINX     |
                    | Reverse Proxy |
                    +---------------+
                      /   /   |   \\   \\
                     /   /    |    \\   \\
                    v   v     v     v   v

app.example.com          -> 10.10.10.11:8080
api.example.com          -> 10.10.10.12:9000
jira.example.com         -> 10.10.10.13:8080
grafana.example.com      -> 10.10.10.14:3000
gitlab.example.com       -> 10.10.10.15:80''', 'text')
p('One public IP and one TCP listener can publish independent HTTPS services. This design is a Name-Based HTTPS Reverse Proxy, also called TLS SNI-Based Virtual Hosting. Nginx terminates client TLS and opens a separate HTTP connection to the selected private backend.', 'یک Public IP و یک Listener روی TCP/443 برای انتشار چند سرویس مستقل HTTPS کافی است. نام این معماری Name-Based HTTPS Reverse Proxy یا TLS SNI-Based Virtual Hosting است. Nginx اتصال TLS کاربر را خاتمه می‌دهد و اتصال HTTP جداگانه‌ای به Backend خصوصی سرویس باز می‌کند.')
p('This runbook targets System Administrators, Network Engineers and DevOps Engineers. The configuration requires Nginx 1.19.4 or later with the HTTP SSL module and OpenSSL 1.1.1 or later; use a currently supported distribution and security-patched packages. All commands below run on Ubuntu/Debian unless marked as backend commands.', 'این راهنما برای System Administrator، Network Engineer و DevOps Engineer نوشته شده است. تنظیمات به Nginx نسخه 1.19.4 یا جدیدتر با HTTP SSL Module و OpenSSL نسخه 1.1.1 یا جدیدتر نیاز دارد؛ در محیط واقعی از توزیع پشتیبانی‌شده و بسته‌های دارای وصله امنیتی استفاده کنید. فرمان‌ها روی Ubuntu/Debian اجرا می‌شوند مگر آنکه محل اجرا Backend ذکر شود.')
p('203.0.113.10 and 198.51.100.20 are documentation addresses; example.com is reserved for examples. Replace them with a routable public IP and domains you control. A public CA cannot issue these example certificates for you. Production-ready here means a deployable baseline with explicit prerequisites, not a claim that the sample addresses are live.', 'آدرس‌های 203.0.113.10 و 198.51.100.20 مخصوص مستندسازی هستند و example.com نیز دامنه نمونه است. آن‌ها را با IP عمومی قابل Route و دامنه‌های تحت مالکیت خود جایگزین کنید. CA عمومی این گواهی‌های نمونه را برای شما صادر نمی‌کند. Production-Ready در این مقاله یعنی تنظیمات قابل استقرار با پیش‌نیاز مشخص، نه فعال‌بودن آدرس‌های مثال.')

section('scenario', '2. Scenario', '۲. سناریو')
code('\n'.join(f'{name}.example.com -> {ip}:{port}' for name, ip, port in SERVICES), 'text')
p('All five A records point to 203.0.113.10. HTTPS service traffic enters on TCP/443; TCP/80 below is an optional redirect and HTTP-01 validation listener, not another HTTPS service port. Different domains such as app.example.net use the same pattern with their own DNS records, server_name and certificate.', 'رکورد A هر پنج نام به 203.0.113.10 اشاره می‌کند. ترافیک سرویس HTTPS از TCP/443 وارد می‌شود؛ TCP/80 در ادامه فقط برای Redirect و اعتبارسنجی HTTP-01 است و Port دیگری برای سرویس HTTPS نیست. دامنه مستقل مثل app.example.net هم با DNS، server_name و Certificate مخصوص خود از همین الگو استفاده می‌کند.')

section('architecture', '3. Architecture', '۳. معماری')
figure('Nginx Reverse Proxy Infrastructure Diagram.png', 'Five private services behind one Nginx HTTPS ingress', 'پنج سرویس خصوصی پشت یک ورودی HTTPS در Nginx')
code('''Client
   |
   | TLS SNI: app.example.com
   v
NGINX :443
   |
   | server_name app.example.com
   | HTTP Host: app.example.com
   v
10.10.10.11:8080''', 'text')
p('DNS locates the proxy IP; it does not select a backend or encode a TCP port. A server block defines a virtual service. server_name matches names, while proxy_pass defines the upstream destination inside a location. DNS, certificate coverage and backend routing are separate settings and all must agree.', 'DNS آدرس Proxy را پیدا می‌کند؛ Backend انتخاب نمی‌کند و Port TCP را در رکورد A قرار نمی‌دهد. هر server block یک سرویس مجازی را تعریف می‌کند. server_name نام‌ها را تطبیق می‌دهد و proxy_pass مقصد Upstream را در location تعیین می‌کند. DNS، پوشش Certificate و مسیر Backend تنظیمات جداگانه‌اند و باید با هم سازگار باشند.')
p('The illustrated upstream links are plaintext HTTP. A private VLAN reduces exposure but does not encrypt traffic. Where policy requires encryption inside the network, use HTTPS upstreams with certificate verification, or an authenticated encrypted network transport. TLS passthrough is a different design: this HTTP proxy needs to decrypt requests to inspect HTTP headers.', 'ارتباط Upstream در این سناریو HTTP بدون رمزنگاری است. Private VLAN سطح دسترسی را محدود می‌کند ولی ترافیک را رمز نمی‌کند. اگر Policy سازمان رمزنگاری داخلی می‌خواهد، از HTTPS Upstream با بررسی Certificate یا ارتباط شبکه رمزنگاری‌شده و احراز هویت‌شده استفاده کنید. TLS Passthrough طراحی دیگری است؛ این HTTP Proxy برای بررسی Headerها باید درخواست را رمزگشایی کند.')

section('sni', '4. How SNI Works', '۴. نحوه کار SNI')
figure('Nginx SNI Routing Infographic.png', 'TLS SNI selection precedes HTTP request routing', 'انتخاب TLS با SNI پیش از Routing درخواست HTTP انجام می‌شود')
items([
('The client resolves the name, connects to IP:443 and sends a TLS ClientHello containing SNI, normally the URL hostname.', 'Client نام را Resolve می‌کند، به IP:443 متصل می‌شود و در TLS ClientHello مقدار SNI، معمولاً Hostname آدرس URL، را می‌فرستد.'),
('Nginx starts in the listener default context. During the handshake SNI can select a named virtual server and its certificate before HTTP exists.', 'Nginx ابتدا در Context پیش‌فرض Listener قرار دارد. هنگام Handshake، SNI می‌تواند Virtual Server و Certificate آن را پیش از وجود Request HTTP انتخاب کند.'),
('After TLS completes, Nginx reads the request line and HTTP Host header; HTTP/2 uses :authority. HTTP name selection can change the request server context.', 'پس از تکمیل TLS، Nginx خط درخواست و HTTP Host Header را می‌خواند؛ در HTTP/2 از :authority استفاده می‌شود. انتخاب نام در HTTP می‌تواند Context سرور پردازش Request را تغییر دهد.'),
('server_name is configuration, not a network header. proxy_pass forwards the decrypted request to the configured backend.', 'server_name تنظیم Nginx است، نه Header شبکه. proxy_pass درخواست رمزگشایی‌شده را به Backend تنظیم‌شده می‌فرستد.'),
])
p('SNI and Host are client input, not authentication. They need not match. This baseline rejects a missing or different SNI for each named service with HTTP 421; it prevents a connection for one name being reused to reach another tenant. This is a deliberate policy and can limit HTTP/2 connection coalescing if HTTP/2 is enabled later. A Host-only curl request to an IP does not test correct SNI.', 'SNI و Host ورودی Client هستند و احراز هویت محسوب نمی‌شوند؛ ممکن است با هم متفاوت باشند. این تنظیمات در هر سرویس SNI خالی یا متفاوت را با HTTP 421 رد می‌کند تا اتصال یک نام برای دسترسی به سرویس دیگر استفاده نشود. این Policy آگاهانه است و اگر بعداً HTTP/2 فعال شود می‌تواند Connection Coalescing را محدود کند. اجرای curl به IP با فقط Host Header، تست صحیح SNI نیست.')
link('https://nginx.org/en/docs/http/server_names.html', 'Official reference: virtual server selection and server names', 'مرجع رسمی: انتخاب Virtual Server و server_name')

section('dns', '5. DNS Configuration', '۵. تنظیم DNS')
code('\n'.join(f'{name}.example.com    A    203.0.113.10' for name, _, _ in SERVICES), 'text')
code('dig app.example.com +short\ndig api.example.com +short\nnslookup app.example.com')
p('Expected dig output for each name is 203.0.113.10; nslookup also prints the resolver and answer labels. Check from the client network and a public resolver when split DNS is used. Remove stale AAAA records unless a working IPv6 path serves the same names. An IPv6 listen directive alone does not create IPv6 connectivity.', 'خروجی مورد انتظار dig برای هر نام 203.0.113.10 است؛ nslookup مشخصات Resolver و برچسب‌های پاسخ را هم نمایش می‌دهد. در Split DNS از شبکه Client و Resolver عمومی بررسی کنید. AAAA قدیمی را حذف کنید مگر مسیر IPv6 فعال همان نام‌ها را سرویس دهد. صرف listen مربوط به IPv6 اتصال عمومی IPv6 ایجاد نمی‌کند.')
code('203.0.113.10', 'text')

section('installation', '6. Nginx Installation', '۶. نصب Nginx')
code('sudo apt update\nsudo apt install nginx -y\nsudo systemctl enable --now nginx\nsudo apt install curl dnsutils netcat-openbsd openssl -y')
p('apt refreshes the package index and installs Nginx plus diagnostic tools. enable --now starts Nginx and enables startup at boot. Package versions depend on your distribution and configured repositories; verify the installed binary rather than assuming apt installs the latest upstream release.', 'apt فهرست بسته‌ها را تازه می‌کند و Nginx و ابزارهای تشخیص را نصب می‌کند. enable --now سرویس را اجرا و شروع در Boot را فعال می‌کند. نسخه بسته به توزیع و Repository بستگی دارد؛ نسخه Binary نصب‌شده را بررسی کنید و فرض نکنید apt جدیدترین Release بالادستی را نصب می‌کند.')
code('nginx -v\nnginx -V\nopenssl version\ncertbot --version\nsystemctl status nginx --no-pager\nsudo ss -lntp | grep nginx')
p('Run certbot --version after installing Certbot in section 8. Expect active (running), an Nginx version meeting the prerequisite, SSL module support in -V and initially a port 80 listener. Port 443 appears after certificates and the final configuration are installed. nginx -V writes build information to stderr.', 'certbot --version را بعد از نصب Certbot در بخش ۸ اجرا کنید. انتظار active (running)، نسخه Nginx مطابق پیش‌نیاز، پشتیبانی SSL Module در -V و در ابتدا Listener روی Port 80 را دارید. Port 443 پس از نصب Certificate و تنظیم نهایی ظاهر می‌شود. nginx -V اطلاعات Build را روی stderr می‌نویسد.')

section('backend-connectivity', '7. Backend Connectivity Check', '۷. بررسی ارتباط با Backend')
p('Run these checks on the Nginx host before writing proxy configuration. A TCP success proves reachability, not application health. curl -I sends HEAD; 405 can mean the application does not implement HEAD. Retry with GET and the public Host expected by that application.', 'پیش از نوشتن تنظیم Proxy این بررسی‌ها را روی سرور Nginx اجرا کنید. موفقیت TCP فقط Reachability را اثبات می‌کند، نه سلامت Application. curl -I درخواست HEAD می‌فرستد؛ پاسخ 405 ممکن است به معنی پشتیبانی‌نکردن برنامه از HEAD باشد. با GET و Host عمومی مورد انتظار برنامه دوباره بررسی کنید.')
code('\n'.join(f'curl -I --connect-timeout 5 --max-time 15 http://{ip}:{port}' for _, ip, port in SERVICES))
code('nc -zv 10.10.10.11 8080\nnc -zv 10.10.10.12 9000\nip route get 10.10.10.11\ncurl -v --connect-timeout 5 --max-time 15 -H "Host: app.example.com" http://10.10.10.11:8080/')
p('Expected: nc reports succeeded; curl receives an application HTTP response such as 200, 302 or an expected authentication response. ip route get shows the selected interface, gateway when needed and source IP. Record that source IP for backend firewall rules, including any intervening SNAT.', 'انتظار: nc موفقیت succeeded را گزارش می‌کند و curl پاسخ HTTP برنامه مثل 200، 302 یا پاسخ احراز هویت مورد انتظار می‌گیرد. ip route get رابط، Gateway در صورت نیاز و Source IP انتخاب‌شده را نشان می‌دهد. همین Source IP را با درنظرگرفتن SNAT احتمالی برای Rule فایروال Backend ثبت کنید.')
p('If connectivity fails, investigate routing and return routes, firewall/ACL, nftables or iptables, a stopped backend, a wrong port, application bind address and SELinux policy where enabled. Ubuntu/Debian commonly use AppArmor instead; inspect the active security framework. Do not disable enforcement to hide a denial. This failure precedes Nginx proxy processing.', 'اگر ارتباط Fail شد، Routing و مسیر برگشت، Firewall/ACL، nftables یا iptables، توقف Backend، Port اشتباه، Bind Address برنامه و در صورت فعال‌بودن Policy مربوط به SELinux را بررسی کنید. در Ubuntu/Debian معمولاً AppArmor استفاده می‌شود؛ Security Framework فعال را بررسی کنید. برای پنهان‌کردن Denial حفاظت را غیرفعال نکنید. این خرابی قبل از پردازش Proxy در Nginx رخ می‌دهد.')

section('certificates', '8. SSL Certificate', '۸. گواهی SSL و TLS')
code('sudo apt install certbot python3-certbot-nginx -y\ncertbot --version')
p('The Nginx plugin is available, but this runbook uses certonly --webroot to keep configuration changes explicit. Use a public CA such as Let’s Encrypt or a commercial provider for public browsers. An Internal Enterprise CA fits managed clients with its root distributed to their trust stores. Protect private keys and give the Nginx master only the access it needs.', 'Plugin مربوط به Nginx نصب می‌شود، اما این راهنما از certonly --webroot استفاده می‌کند تا تغییرات تنظیمات صریح باشند. برای Browser عمومی از CA عمومی مثل Let’s Encrypt یا صادرکننده تجاری استفاده کنید. Internal Enterprise CA برای Clientهای مدیریت‌شده‌ای مناسب است که Root آن در Trust Store توزیع شده باشد. Private Keyها را محافظت کنید و فقط دسترسی لازم را به Nginx Master بدهید.')
p('Bootstrap first: do not enable a configuration referencing certificate files that do not exist. On a dedicated new proxy, disable the packaged default site after reviewing it, create the webroot, and install this temporary HTTP configuration. On a shared proxy, integrate it with existing listeners instead of disabling active sites. Keep a copy of the current configuration before each change.', 'ابتدا Bootstrap کنید: تنظیماتی را که به Certificate موجودنبود اشاره می‌کند فعال نکنید. روی Proxy جدید و اختصاصی، پس از بررسی Default Site بسته، آن را غیرفعال کنید، Webroot بسازید و این تنظیم HTTP موقت را نصب کنید. روی Proxy مشترک تنظیمات را با Listener موجود ادغام کنید و سایت فعال را غیرفعال نکنید. پیش از هر تغییر از تنظیمات فعلی کپی نگه دارید.')
code('sudo cp -a /etc/nginx /etc/nginx.before-sni\nsudo mkdir -p /var/www/letsencrypt/.well-known/acme-challenge\nsudo unlink /etc/nginx/sites-enabled/default\nsudoedit /etc/nginx/conf.d/reverse-proxy.conf')
p('The unlink command assumes the standard packaged default symlink exists; run it only for that reviewed default. Write the following temporary file, test it and reload. DNS must already resolve to this reachable proxy, and public TCP/80 must reach it for HTTP-01.', 'فرمان unlink وجود Symlink استاندارد Default بسته را فرض می‌کند؛ فقط برای همان Default بررسی‌شده اجرا شود. فایل موقت زیر را بنویسید، تست و Reload کنید. برای HTTP-01 باید DNS قبلاً به این Proxy قابل دسترسی اشاره کند و TCP/80 عمومی به آن برسد.')
names = ' '.join(f'{name}.example.com' for name, _, _ in SERVICES)
bootstrap = f'''server {{
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    return 444;
}}
server {{
    listen 80;
    listen [::]:80;
    server_name {names};
    location ^~ /.well-known/acme-challenge/ {{
        root /var/www/letsencrypt;
        default_type text/plain;
        try_files $uri =404;
    }}
    location / {{ return 404; }}
}}'''
code(bootstrap, 'nginx')
code('sudo nginx -t\nsudo systemctl reload nginx')
code('\n'.join(f'sudo certbot certonly --webroot -w /var/www/letsencrypt --cert-name {name}.example.com -d {name}.example.com' for name, _, _ in SERVICES))
p('Each command requests a separate certificate and interactively asks for account details where necessary. --cert-name fixes the intended lineage name; verify actual paths with certbot certificates, especially if an earlier lineage already exists. For a brand-new issuance, the paths used below follow these names.', 'هر فرمان Certificate جدا درخواست می‌کند و در صورت نیاز اطلاعات Account را تعاملی می‌پرسد. --cert-name نام Lineage مورد نظر را مشخص می‌کند؛ خصوصاً اگر Lineage قبلی وجود دارد، مسیر واقعی را با certbot certificates بررسی کنید. برای صدور جدید، مسیرهای زیر مطابق همین نام‌ها هستند.')
code('sudo certbot certificates')
code('\n'.join(f'/etc/letsencrypt/live/{name}.example.com/fullchain.pem\n/etc/letsencrypt/live/{name}.example.com/privkey.pem' for name, _, _ in SERVICES), 'text')
p('The paths above are expected files, not shell commands. fullchain.pem contains the leaf plus intermediates; privkey.pem is the secret key. Nginx must serve the complete chain in the right order.', 'مسیرهای بالا فایل مورد انتظار هستند و فرمان Shell نیستند. fullchain.pem شامل Leaf و Intermediateها است و privkey.pem کلید محرمانه است. Nginx باید زنجیره کامل را با ترتیب صحیح ارائه کند.')
p('A SAN certificate can cover all five exact names, even across different domains, but renewal and compromise affect the shared group. A wildcard such as *.example.com covers one label level, not example.com or x.app.example.com. Add the apex as a separate SAN when needed. Let’s Encrypt wildcard issuance requires DNS-01. Automate it with your DNS provider’s supported Certbot plugin and narrowly scoped credentials; manual DNS renewal needs a documented automation hook.', 'SAN Certificate می‌تواند هر پنج نام دقیق حتی از دامنه‌های مختلف را پوشش دهد، اما تمدید و افشای کلید روی همه اثر می‌گذارد. Wildcard مثل *.example.com فقط یک سطح را پوشش می‌دهد، نه example.com یا x.app.example.com. در صورت نیاز Apex را SAN جدا اضافه کنید. صدور Wildcard در Let’s Encrypt به DNS-01 نیاز دارد. آن را با Plugin پشتیبانی‌شده Certbot برای DNS Provider و Credential محدود خودکار کنید؛ تمدید DNS دستی به Hook خودکار مستند نیاز دارد.')
p('If only TCP/443 may be exposed, use automated DNS-01 and omit the port 80 listeners. HTTP-01 always validates on port 80. Redirecting it does not eliminate that requirement. This article’s final file intentionally preserves the webroot exception for unattended HTTP-01 renewals.', 'اگر فقط TCP/443 مجاز است، DNS-01 خودکار استفاده کنید و Listenerهای Port 80 را حذف کنید. اعتبارسنجی HTTP-01 همواره از Port 80 شروع می‌شود و Redirect نیاز به آن را حذف نمی‌کند. فایل نهایی این مقاله عمداً استثنای Webroot را برای تمدید HTTP-01 بدون دخالت نگه می‌دارد.')
link('https://letsencrypt.org/docs/challenge-types/', 'Official reference: ACME challenge types', 'مرجع رسمی: انواع ACME Challenge')

section('complete-configuration', '9. Complete Nginx Configuration', '۹. تنظیمات کامل Nginx')
link('/downloads/'+SLUG+'/reverse-proxy.conf', 'Download the complete reverse-proxy.conf', 'دانلود فایل کامل reverse-proxy.conf')
link('/downloads/'+SLUG+'/bootstrap-http.conf', 'Download the temporary HTTP bootstrap configuration', 'دانلود تنظیم HTTP موقت برای Bootstrap')
p('After all five certificate/key pairs exist, replace /etc/nginx/conf.d/reverse-proxy.conf with the full file below. Ubuntu/Debian normally include conf.d/*.conf inside http in /etc/nginx/nginx.conf; confirm with nginx -T. Do not wrap this file in another http block. Remove conflicting listeners and duplicate default_server declarations in other included files.', 'پس از موجودبودن هر پنج جفت Certificate و Key، محتوای /etc/nginx/conf.d/reverse-proxy.conf را با فایل کامل زیر جایگزین کنید. در Ubuntu/Debian معمولاً conf.d/*.conf داخل http در /etc/nginx/nginx.conf Include می‌شود؛ با nginx -T بررسی کنید. این فایل را داخل http دیگری نگذارید. Listener متعارض و default_server تکراری را در فایل‌های Include دیگر برطرف کنید.')
p('TLS and server_tokens settings are placed in individual servers because distribution nginx.conf files may already define them in http. Repeating a singleton directive in the same context can fail nginx -t. Keep the default TLS server’s protocol/session policy aligned with every named server. The baseline uses listen 443 ssl and does not enable HTTP/2. If adding HTTP/2, http2 on is available since 1.25.1; the older listen ... http2 parameter is deprecated in newer releases. Do not use the removed ssl on directive.', 'تنظیم TLS و server_tokens در serverهای جدا قرار دارد چون nginx.conf توزیع ممکن است آن‌ها را قبلاً در http تعریف کرده باشد. تکرار Directive تک‌مقداری در همان Context می‌تواند nginx -t را Fail کند. Policy پروتکل و Session در Default TLS را با همه Named Serverها هماهنگ نگه دارید. تنظیم اصلی از listen 443 ssl استفاده می‌کند و HTTP/2 را فعال نمی‌کند. اگر HTTP/2 اضافه می‌کنید، http2 on از 1.25.1 موجود است و پارامتر قدیمی listen ... http2 در نسخه جدید Deprecated شده است. از Directive حذف‌شده ssl on استفاده نکنید.')
config = '''# Included in the http context. Requires Nginx >= 1.19.4.
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}

log_format sni_proxy '$remote_addr [$time_local] "$request" '
                     'host=$host sni=$ssl_server_name status=$status '
                     'bytes=$body_bytes_sent rt=$request_time '
                     'upstream=$upstream_addr us=$upstream_status '
                     'uct=$upstream_connect_time urt=$upstream_response_time';

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
    server_tokens off;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_session_cache shared:SNI_TLS:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;
    ssl_reject_handshake on;
    return 444;
}

server {
    listen 80;
    listen [::]:80;
    server_name app.example.com api.example.com jira.example.com
                grafana.example.com gitlab.example.com;
    server_tokens off;

    location ^~ /.well-known/acme-challenge/ {
        root /var/www/letsencrypt;
        default_type text/plain;
        try_files $uri =404;
    }
    location / {
        return 301 https://$host$request_uri;
    }
}
'''
for name, ip, port in SERVICES:
    config += f'''
server {{
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name {name}.example.com;
    server_tokens off;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_session_cache shared:SNI_TLS:10m;
    ssl_session_timeout 10m;
    ssl_session_tickets off;

    ssl_certificate     /etc/letsencrypt/live/{name}.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{name}.example.com/privkey.pem;

    # Return-only guard: do not route mismatched SNI/HTTP names.
    if ($ssl_server_name != $host) {{ return 421; }}
    if ($host != {name}.example.com) {{ return 421; }}

    client_max_body_size 100M;
    access_log /var/log/nginx/{name}.example.com.access.log sni_proxy;
    error_log  /var/log/nginx/{name}.example.com.error.log warn;

    location / {{
        proxy_pass http://{ip}:{port};
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_connect_timeout 10s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }}
}}
'''
code(config, 'nginx')
p('proxy_pass has no URI suffix here, preserving the original request path and query in this simple location. Adding a URI or a trailing slash can change path replacement behavior. Explicit upstream HTTP/1.1 avoids relying on version-specific defaults. Timeout values are examples: read/send timeouts apply between successive I/O operations, not a total 60-second transaction budget.', 'proxy_pass اینجا URI اضافه ندارد و در این location ساده Path و Query اصلی را حفظ می‌کند. اضافه‌کردن URI یا Slash پایانی می‌تواند رفتار جایگزینی Path را تغییر دهد. HTTP/1.1 صریح برای Upstream وابستگی به Default نسخه را حذف می‌کند. Timeoutها نمونه‌اند؛ Read/Send Timeout فاصله بین عملیات I/O متوالی را محدود می‌کنند، نه کل زمان Transaction را به ۶۰ ثانیه.')
p('Configure each application’s public URL as https://its-name.example.com and trust forwarded headers only from the proxy. Jira needs the appropriate proxy/base URL settings; Grafana needs its root_url; GitLab needs external_url and the supported external-proxy/Workhorse setup. Port 80 on 10.10.10.15 must actually expose the GitLab HTTP entry point. Wrong application settings cause redirect loops, insecure cookies, CSRF failures or incorrect absolute links even when proxy routing works.', 'Public URL هر برنامه را https://نام-آن.example.com تنظیم کنید و Forwarded Header را فقط از Proxy معتبر بپذیرید. Jira به تنظیم Proxy/Base URL، Grafana به root_url و GitLab به external_url و تنظیم پشتیبانی‌شده External Proxy/Workhorse نیاز دارد. Port 80 روی 10.10.10.15 باید واقعاً ورودی HTTP گیت‌لب باشد. تنظیم برنامه اشتباه حتی با Routing صحیح باعث Redirect Loop، Cookie ناامن، خطای CSRF یا لینک Absolute نادرست می‌شود.')
link('https://nginx.org/en/docs/http/ngx_http_proxy_module.html', 'Official reference: proxy module and URI behavior', 'مرجع رسمی: Proxy Module و رفتار URI')

section('http-redirect', '10. HTTP to HTTPS Redirect', '۱۰. هدایت HTTP به HTTPS')
p('The final file redirects ordinary requests and keeps ACME challenges reachable. If certificates use DNS-01 or another issuance method that does not need HTTP-01, replace only the named port 80 block with this simpler version. Do not add a second copy of it.', 'فایل نهایی درخواست معمول را Redirect می‌کند و ACME Challenge را قابل دسترسی نگه می‌دارد. اگر گواهی با DNS-01 یا روش دیگری بدون نیاز به HTTP-01 صادر می‌شود، فقط Block نام‌دار Port 80 را با نسخه ساده زیر جایگزین کنید؛ کپی دوم اضافه نکنید.')
code(f'''server {{
    listen 80;
    listen [::]:80;
    server_name {names};
    return 301 https://$host$request_uri;
}}''', 'nginx')
p('A server-level return executes before a location-based webroot handler, so do not use this replacement for the webroot workflow. 301 is conventional for browser navigation; clients may rewrite POST to GET. Configure API clients to start with HTTPS, or deliberately select 308 when preserving the method is required.', 'return در سطح server پیش از Handler مربوط به Webroot در location اجرا می‌شود؛ بنابراین این جایگزین را برای روش Webroot استفاده نکنید. 301 برای مرورگر رایج است، اما Client ممکن است POST را GET کند. Client API را از ابتدا روی HTTPS تنظیم کنید یا اگر حفظ Method لازم است آگاهانه 308 انتخاب کنید.')

section('default-server', '11. Default Server Security', '۱۱. امنیت Default Server')
p('default_server belongs to an address/port listener. server_name _ is only a conventional unmatched name, not a catch-all mechanism. The explicit HTTP default returns Nginx’s nonstandard 444, closing the connection without an HTTP response. Unknown HTTPS SNI or no SNI is rejected in the TLS handshake by ssl_reject_handshake on. This modern default needs no dummy certificate, as documented by Nginx.', 'default_server متعلق به Listener آدرس/Port است. server_name _ فقط یک نام قراردادی نامنطبق است، نه مکانیزم Catch-All. Default صریح HTTP مقدار غیراستاندارد 444 را برمی‌گرداند و اتصال را بدون Response HTTP می‌بندد. SNI ناشناس یا نبود SNI در HTTPS با ssl_reject_handshake on هنگام TLS Handshake رد می‌شود. طبق مستندات Nginx این Default جدید به Certificate ساختگی نیاز ندارد.')
p('return 444 in the HTTPS default also handles a request whose HTTP name selects that default after a valid named handshake. The return-only guards in every service cover unknown Host fallback and known-name mismatches. Test both paths; SNI selection alone is not an HTTP authorization boundary.', 'return 444 در Default HTTPS درخواست‌هایی را هم پوشش می‌دهد که بعد از Handshake معتبر یک سرویس، نام HTTP آن‌ها Default را انتخاب کند. Guardهای فقط-return در هر سرویس، Fallback مربوط به Host ناشناس و اختلاف نام‌های معتبر را پوشش می‌دهند. هر دو مسیر را تست کنید؛ انتخاب SNI به‌تنهایی مرز Authorization در HTTP نیست.')
p('ssl_reject_handshake appeared in 1.19.4. On an older binary, upgrade to a supported release, or replace only the HTTPS default with the following certificate-bearing fallback. The referenced app certificate must already exist. Unknown clients receive that certificate before closure and can see it; return 444 alone cannot reject an earlier TLS handshake.', 'ssl_reject_handshake از 1.19.4 اضافه شده است. روی Binary قدیمی به Release پشتیبانی‌شده ارتقا دهید یا فقط Default HTTPS را با Fallback دارای گواهی زیر جایگزین کنید. گواهی app باید از قبل موجود باشد. Client ناشناس پیش از بسته‌شدن اتصال آن Certificate را دریافت و مشاهده می‌کند؛ return 444 به‌تنهایی نمی‌تواند Handshake قبلی را رد کند.')
code('''server {
    listen 443 ssl default_server;
    listen [::]:443 ssl default_server;
    server_name _;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_certificate /etc/letsencrypt/live/app.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/app.example.com/privkey.pem;
    return 444;
}''', 'nginx')
link('https://nginx.org/en/docs/http/ngx_http_ssl_module.html#ssl_reject_handshake', 'Official reference: ssl_reject_handshake', 'مرجع رسمی: ssl_reject_handshake')

section('websocket', '12. WebSocket Support and Upload Size', '۱۲. WebSocket و اندازه Upload')
code("map $http_upgrade $connection_upgrade {\n    default upgrade;\n    '' close;\n}\n\n# Inside the existing proxy location:\nproxy_http_version 1.1;\nproxy_set_header Upgrade $http_upgrade;\nproxy_set_header Connection $connection_upgrade;", 'nginx')
p('These directives are already in the full file. map belongs in http, while the headers belong in the proxy location. Upgrade and Connection are hop-by-hop headers and must be forwarded explicitly. Grafana Live, GitLab features, web applications and real-time services may need this. Expect 101 Switching Protocols for an accepted HTTP/1.1 upgrade, not for every ordinary request.', 'این Directiveها در فایل کامل موجودند. map داخل http قرار می‌گیرد و Headerها داخل location مربوط به Proxy. Upgrade و Connection از نوع Hop-by-Hop هستند و باید صریح ارسال شوند. Grafana Live، برخی قابلیت‌های GitLab، Web Application و سرویس Real-Time ممکن است به آن‌ها نیاز داشته باشند. برای Upgrade پذیرفته‌شده HTTP/1.1 انتظار 101 Switching Protocols دارید، نه برای هر درخواست عادی.')
p('An idle upstream WebSocket can close after proxy_read_timeout. Prefer application ping frames, or increase timeout only for the real-time endpoint after measuring its needs. Do not globally stretch timeouts to conceal a slow backend.', 'WebSocket بدون داده از Upstream ممکن است پس از proxy_read_timeout بسته شود. از Ping Frame برنامه استفاده کنید یا فقط Timeout مسیر Real-Time را پس از بررسی نیاز افزایش دهید. برای پنهان‌کردن کندی Backend، Timeout همه سرویس‌ها را افزایش ندهید.')
code('client_max_body_size 100M;', 'nginx')
p('100M is an example per-service limit already set in each server. Choose smaller limits for APIs that only accept small JSON and larger reviewed limits for GitLab artifacts when required. Oversize requests receive 413. Align application limits, request buffering, temporary-disk capacity and upload duration. An unlimited or huge limit without a business need increases resource-exhaustion risk.', '100M محدودیت نمونه هر سرویس است و در هر server تنظیم شده است. برای API با JSON کوچک مقدار کمتر و برای Artifact گیت‌لب در صورت نیاز مقدار بزرگ‌تر بررسی‌شده بگذارید. درخواست بزرگ‌تر پاسخ 413 می‌گیرد. محدودیت برنامه، Request Buffering، ظرفیت دیسک موقت و مدت Upload را هماهنگ کنید. مقدار نامحدود یا بسیار بزرگ بدون نیاز واقعی خطر مصرف منابع را بالا می‌برد.')
link('https://nginx.org/en/docs/http/websocket.html', 'Official reference: WebSocket proxying', 'مرجع رسمی: WebSocket Proxying')

section('logging', '13. Logging', '۱۳. لاگ‌گیری')
code('\n'.join(f'access_log /var/log/nginx/{name}.example.com.access.log sni_proxy;\nerror_log /var/log/nginx/{name}.example.com.error.log warn;' for name, _, _ in SERVICES), 'nginx')
p('Separate virtual-host logs isolate incidents and feed SIEM pipelines. The custom format records Host, SNI, upstream address/status and connect/response timing. A slow request can then be correlated with its exact backend. Handshake failures can appear in the global error log before a named request log exists.', 'لاگ جدا برای Virtual Host حادثه را تفکیک می‌کند و برای SIEM مفید است. فرمت سفارشی Host، SNI، آدرس و Status Upstream و زمان Connect/Response را ثبت می‌کند. درخواست کند را می‌توان به Backend دقیق مرتبط کرد. خطای Handshake ممکن است پیش از ایجاد لاگ Request نام‌دار در Error Log عمومی دیده شود.')
p('Protect log permissions, rotate logs, verify collection and set retention. The request field contains the URL and query, so avoid credentials in URLs and redact sensitive application data in the logging pipeline. Monitor 5xx rates, upstream latency, TLS failures, certificate expiry, disk space and worker/resource pressure.', 'Permission لاگ را محدود کنید، Rotation و جمع‌آوری را تست کنید و Retention تعیین کنید. فیلد Request شامل URL و Query است؛ Credential در URL قرار ندهید و داده حساس برنامه را در Pipeline پالایش کنید. نرخ 5xx، Latency Upstream، خطای TLS، انقضای Certificate، فضای دیسک و فشار Worker/Resource را مانیتور کنید.')

section('hardening', '14. Security Hardening', '۱۴. تقویت امنیت')
items([
('Expose TCP/443 and only expose TCP/80 when redirect/HTTP-01 is required. Keep SSH and management access on approved VPN or management networks.', 'TCP/443 را منتشر کنید و TCP/80 را فقط برای Redirect/HTTP-01 باز کنید. SSH و Management فقط از VPN یا شبکه مدیریتی مجاز قابل دسترسی باشد.'),
('Keep backends off the Internet. Permit only the proxy’s actual source IP to the required backend ports through the inter-VLAN firewall.', 'Backend مستقیماً از اینترنت قابل دسترسی نباشد. فقط Source IP واقعی Proxy به Port لازم Backend در Firewall بین VLANها مجاز باشد.'),
('Disable old TLS; apply the same explicit policy to the default and named TLS servers. Restrict key access and monitor renewal failure as well as expiry.', 'TLS قدیمی را غیرفعال کنید؛ Policy صریح یکسان روی Default و Named Serverهای TLS بگذارید. دسترسی Key را محدود و هم شکست تمدید و هم انقضا را مانیتور کنید.'),
('server_tokens off reduces version disclosure; it does not remove every server identifier or replace security updates.', 'server_tokens off افشای نسخه را محدود می‌کند؛ همه شناسه‌های سرور را حذف نمی‌کند و جایگزین Update امنیتی نیست.'),
('Restrict Jira, Grafana and GitLab administration through VPN, identity-aware access or carefully tested allow/deny rules; add MFA and application authorization.', 'مدیریت Jira، Grafana و GitLab را با VPN، دسترسی مبتنی بر هویت یا Ruleهای allow/deny تست‌شده محدود کنید؛ MFA و Authorization برنامه هم لازم است.'),
])
code('server_tokens off;\n\n# Example inside a management-only location/server after reviewing source IPs:\nallow 10.10.99.0/24;\ndeny all;', 'nginx')
p('Apply headers against application requirements. A restrictive CSP or frame policy can break embeds and sign-in flows. Enable HSTS only after stable HTTPS rollout and recovery planning. Start with a short max-age, then increase deliberately. includeSubDomains covers every subordinate name; preload requires a separate long-term commitment. Do not copy those options blindly. Consider add_header inheritance in your installed Nginx version and headers already emitted by the application.', 'Headerها را طبق نیاز برنامه اعمال کنید. CSP یا Frame Policy محدود می‌تواند Embed یا Login را خراب کند. HSTS فقط پس از استقرار پایدار HTTPS و برنامه Recovery فعال شود. با max-age کوتاه شروع و آگاهانه افزایش دهید. includeSubDomains همه زیرنام‌ها را پوشش می‌دهد و preload تعهد بلندمدت جدا می‌خواهد؛ آن‌ها را کورکورانه کپی نکنید. Inheritance مربوط به add_header در نسخه نصب‌شده و Header موجود برنامه را بررسی کنید.')
code('''# Optional trial in a reviewed HTTPS server, after HTTPS is stable:
add_header Strict-Transport-Security "max-age=300" always;

# Optional http-context rate-limit zone, not enabled by the main file:
limit_req_zone $binary_remote_addr zone=api_per_ip:10m rate=10r/s;

# In the existing API location if this policy fits actual traffic:
limit_req zone=api_per_ip burst=20 nodelay;
limit_req_status 429;''', 'nginx')
p('Rate limits need load testing: shared NAT clients use one apparent IP and may be penalized together. Start with dry-run observation on versions supporting limit_req_dry_run (1.17.1+), then enforce appropriate endpoint limits. These optional examples are additions in their specified contexts, not a standalone configuration.', 'Rate Limit به Load Test نیاز دارد؛ Clientهای پشت NAT مشترک یک IP دیده می‌شوند و ممکن است همگی محدود شوند. در نسخه پشتیبان limit_req_dry_run یعنی 1.17.1 به بعد با مشاهده Dry-Run شروع و سپس محدودیت مناسب Endpoint را اعمال کنید. مثال‌های اختیاری در Context مشخص اضافه می‌شوند و فایل مستقل نیستند.')
p('The baseline appends X-Forwarded-For as requested, so an incoming header may contain spoofed entries. Applications must trust only known proxies and parse from the trusted end of the chain. At a direct Internet edge, replacing X-Forwarded-For with $remote_addr is a simpler policy. If a load balancer precedes Nginx, configure real_ip only for its exact trusted ranges; never trust arbitrary sources.', 'تنظیم اصلی طبق الگو X-Forwarded-For را Append می‌کند؛ Header ورودی ممکن است مقدار جعلی داشته باشد. برنامه فقط Proxy شناخته‌شده را Trust کند و زنجیره را از سمت معتبر تحلیل کند. در لبه مستقیم اینترنت، جایگزینی X-Forwarded-For با $remote_addr Policy ساده‌تری است. اگر Load Balancer پیش از Nginx دارید، real_ip را فقط برای Range دقیق معتبر تنظیم کنید؛ Source دلخواه را Trust نکنید.')

section('testing', '15. Testing Configuration and Renewal', '۱۵. تست تنظیمات و تمدید')
code('sudo nginx -t')
code('nginx: the configuration file /etc/nginx/nginx.conf syntax is ok\nnginx: configuration file /etc/nginx/nginx.conf test is successful', 'text')
p('nginx -t checks syntax and referenced files, including certificate/key access. It does not prove a backend is healthy. If it fails, fix the reported file and line before reloading. A missing certificate is an issuance/path problem, not a reason to remove TLS security.', 'nginx -t سینتکس و فایل‌های ارجاع‌شده از جمله دسترسی Certificate/Key را بررسی می‌کند؛ سلامت Backend را اثبات نمی‌کند. اگر Fail شد، فایل و خط گزارش‌شده را پیش از Reload اصلاح کنید. نبود Certificate مشکل صدور یا مسیر است، نه دلیل حذف امنیت TLS.')
code('sudo systemctl reload nginx\nsystemctl status nginx --no-pager\nsudo ss -lntp | grep ":443"\nsudo journalctl -u nginx --since "10 minutes ago" --no-pager')
p('Expected: active (running) and listening on 0.0.0.0:443 and [::]:443 where IPv6 is enabled. Reload starts workers using the new configuration while old workers drain existing connections. It is usually preferable to restart, which interrupts the service. Long-lived connections can keep old workers alive; check reload logs and drain behavior during changes.', 'انتظار: active (running) و Listen روی 0.0.0.0:443 و در صورت IPv6 فعال روی [::]:443. Reload Workerهای جدید را با تنظیم تازه اجرا می‌کند و Worker قدیمی Connection موجود را تخلیه می‌کند. معمولاً بر Restart که سرویس را قطع می‌کند ترجیح دارد. Connection طولانی ممکن است Worker قدیمی را نگه دارد؛ لاگ Reload و Drain را هنگام تغییر بررسی کنید.')
code('''sudo install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx >/dev/null <<'SH'
#!/bin/sh
set -eu
/usr/sbin/nginx -t
/usr/bin/systemctl reload nginx
SH
sudo chmod 0750 /etc/letsencrypt/renewal-hooks/deploy/reload-nginx
sudo certbot renew --dry-run
sudo certbot renew --dry-run --run-deploy-hooks
systemctl list-timers --all | grep -i certbot''')
p('The deploy hook tests and reloads Nginx after a successful renewal so new connections see the renewed certificate. Verify executable paths on the target host. renew --dry-run uses the staging CA; --run-deploy-hooks exercises deploy hooks on Certbot versions supporting it. Check certbot renew --help all if unavailable and execute the hook separately. Expect simulated renewals to succeed, then verify the scheduler installed by your package; some installations use cron instead of a systemd timer.', 'Deploy Hook پس از تمدید موفق Nginx را تست و Reload می‌کند تا اتصال جدید گواهی تازه ببیند. مسیر Executableها را روی Host مقصد بررسی کنید. renew --dry-run از CA آزمایشی استفاده می‌کند؛ --run-deploy-hooks در نسخه پشتیبان Certbot، Hook را هم اجرا می‌کند. اگر گزینه موجود نبود certbot renew --help all را ببینید و Hook را جدا اجرا کنید. انتظار موفقیت تمدید شبیه‌سازی‌شده را دارید؛ سپس Scheduler بسته را بررسی کنید. بعضی نصب‌ها به‌جای Systemd Timer از Cron استفاده می‌کنند.')
p('Keep an external certificate-expiry alert and an alert for failed renewals. A timer existing is not proof of successful issuance, reload or external reachability. For rollback, restore the reviewed previous file, run nginx -t, reload and repeat external tests; retain a recovery path independent of this proxy.', 'Alert خارجی انقضای گواهی و Alert شکست تمدید داشته باشید. وجود Timer، موفقیت صدور، Reload یا Reachability بیرونی را اثبات نمی‌کند. برای Rollback فایل قبلی بررسی‌شده را بازگردانید، nginx -t و Reload اجرا کنید و تست خارجی را تکرار کنید؛ مسیر Recovery مستقل از این Proxy نگه دارید.')
link('https://eff-certbot.readthedocs.io/en/stable/using.html', 'Official reference: Certbot issuance, renewal and hooks', 'مرجع رسمی: صدور، تمدید و Hook در Certbot')

section('resolve', '16. curl --resolve: Test Before DNS Cutover', '۱۶. تست با curl --resolve پیش از تغییر DNS')
code('curl -vk \\\n+  --resolve app.example.com:443:203.0.113.10 \\\n+  https://app.example.com/')
p('--resolve overrides the connection address while preserving the URL hostname for SNI and Host. It is useful for migration, pre-production testing, cutover and testing before DNS changes. -v prints diagnostics; -k disables certificate verification and is only a diagnostic option. This command cannot prove certificate trust.', '--resolve آدرس اتصال را Override می‌کند اما Hostname آدرس URL را برای SNI و Host حفظ می‌کند. برای Migration، Pre-production، Cutover و تست قبل از تغییر DNS کاربرد دارد. -v جزئیات را چاپ می‌کند و -k بررسی Certificate را غیرفعال می‌کند و فقط گزینه تشخیصی است. این فرمان Trust گواهی را اثبات نمی‌کند.')
code('curl -v --resolve app.example.com:443:203.0.113.10 https://app.example.com/\n'+ '\n'.join(f'curl --resolve {name}.example.com:443:203.0.113.10 -I https://{name}.example.com/' for name, _, _ in SERVICES))
p('For release acceptance, omit -k and expect certificate verification to pass plus the application’s expected status/content. Use --cacert /path/to/enterprise-root.pem for an approved internal CA. Follow redirects only after inspecting Location: an absolute redirect to another hostname needs its own --resolve entry. For HEAD-incompatible services use GET. Confirm routing from per-host upstream logs, not just five identical login pages.', 'برای پذیرش Release، -k را حذف کنید و انتظار موفقیت بررسی Certificate و Status/Content مورد انتظار برنامه را داشته باشید. برای CA داخلی مورد تأیید از --cacert /path/to/enterprise-root.pem استفاده کنید. Redirect را بعد از بررسی Location دنبال کنید؛ Redirect به Hostname دیگر به --resolve جدا نیاز دارد. برای سرویس بدون HEAD از GET استفاده کنید. Routing را از لاگ Upstream هر Host تأیید کنید، نه صرفاً پنج صفحه Login مشابه.')
code('''curl -I --resolve app.example.com:80:203.0.113.10 http://app.example.com/
curl -v --resolve unknown.example.com:80:203.0.113.10 http://unknown.example.com/
curl -v --resolve unknown.example.com:443:203.0.113.10 https://unknown.example.com/
curl -v --resolve app.example.com:443:203.0.113.10 https://app.example.com/ -H 'Host: api.example.com'
curl -v --resolve app.example.com:443:203.0.113.10 https://app.example.com/ -H 'Host: unknown.example.com' ''')
p('Expected in order: 301 with Location https://app.example.com/; empty reply/closed connection for unknown HTTP; TLS handshake failure for unknown HTTPS SNI; 421 for app SNI with api Host; closure or 421 for an unknown Host after app TLS, never backend content. Test the actual client’s HTTP protocol because the exact failure text varies.', 'به‌ترتیب انتظار دارید: 301 با Location برابر https://app.example.com/؛ پاسخ خالی یا اتصال بسته برای HTTP ناشناس؛ شکست TLS Handshake برای SNI ناشناس؛ 421 برای SNI مربوط به app با Host مربوط به api؛ و بسته‌شدن اتصال یا 421 برای Host ناشناس پس از TLS مربوط به app، بدون محتوای Backend. پروتکل واقعی Client را تست کنید چون متن دقیق خطا متغیر است.')

section('openssl', '17. openssl s_client: SNI and Certificate Verification', '۱۷. تست SNI و گواهی با openssl s_client')
code('openssl s_client -connect 203.0.113.10:443 -servername app.example.com\nopenssl s_client -connect 203.0.113.10:443 -servername api.example.com')
p('-connect selects the address and -servername sends SNI. Inspect the presented certificate and chain for each name. Setting SNI alone is not hostname verification; use the strict command below and verify the SAN, dates and chain. s_client may continue after verification errors unless -verify_return_error is supplied.', '-connect آدرس را انتخاب و -servername مقدار SNI را ارسال می‌کند. گواهی و Chain ارائه‌شده را برای هر نام بررسی کنید. تنظیم SNI به‌تنهایی Hostname Verification نیست؛ از فرمان سخت‌گیرانه زیر استفاده و SAN، تاریخ و زنجیره را بررسی کنید. s_client ممکن است بدون -verify_return_error پس از خطای Verification ادامه دهد.')
code('''openssl s_client -connect 203.0.113.10:443 \\
  -servername app.example.com -verify_hostname app.example.com \\
  -verify_return_error -CApath /etc/ssl/certs </dev/null

openssl s_client -connect 203.0.113.10:443 -servername app.example.com </dev/null 2>/dev/null \\
  | openssl x509 -noout -subject -issuer -dates -ext subjectAltName

openssl s_client -connect 203.0.113.10:443 -noservername </dev/null''')
p('With a publicly trusted certificate and the system CA store installed, expect Verify return code: 0 (ok), a SAN covering app.example.com and valid dates. Repeat strict verification for all five names. For a private CA use its trusted -CAfile. The last command should fail the handshake because this baseline rejects clients without SNI.', 'با گواهی Public مورد اعتماد و CA Store سیستم، انتظار Verify return code: 0 (ok)، SAN شامل app.example.com و تاریخ معتبر دارید. تست سخت‌گیرانه را برای هر پنج نام تکرار کنید. برای CA خصوصی از -CAfile معتبر آن استفاده کنید. فرمان آخر باید Handshake را Fail کند چون این تنظیمات Client بدون SNI را رد می‌کند.')
link('https://docs.openssl.org/3.0/man1/openssl-s_client/', 'Official reference: OpenSSL s_client options', 'مرجع رسمی: گزینه‌های OpenSSL s_client')

section('troubleshooting', '18. Troubleshooting', '۱۸. عیب‌یابی')
rows = [
('502 Bad Gateway', 'Backend down, refused connection, invalid upstream response or wrong protocol', 'توقف Backend، اتصال Refused، پاسخ نامعتبر یا Protocol اشتباه', 'curl / nc / error log'),
('504 Gateway Timeout', 'Upstream connect/read timeout; application or dependency latency', 'Timeout اتصال/خواندن Upstream؛ کندی برنامه یا Dependency', 'upstream timing / proxy_read_timeout / backend logs'),
('Wrong Website', 'Wrong server_name, duplicate block, default fallback or wrong app public URL', 'server_name اشتباه، Block تکراری، Default یا Public URL اشتباه برنامه', 'nginx -T / Host / upstream logs'),
('SSL Certificate Wrong', 'Incorrect SNI, certificate path, listener or unreloaded renewal', 'SNI، مسیر Certificate یا Listener اشتباه؛ تمدید بدون Reload', 'openssl s_client / certbot certificates'),
('Connection Refused', 'No listener, backend stopped or active firewall reject', 'نبود Listener، توقف Backend یا Reject فعال Firewall', 'nc / ss / firewall rules'),
('Connection Timeout', 'Silent firewall drop, route failure or asymmetric return path', 'Drop بدون پاسخ Firewall، خرابی Route یا برگشت نامتقارن', 'nc / ip route get / firewall counters'),
('DNS Wrong', 'Incorrect A/AAAA, stale cache or split-DNS mismatch', 'A/AAAA اشتباه، Cache قدیمی یا اختلاف Split DNS', 'dig / nslookup'),
('WebSocket Fail', 'Missing Upgrade headers, origin/auth failure or idle timeout', 'نبود Upgrade Header، خطای Origin/Auth یا Idle Timeout', 'Browser DevTools / 101 / logs'),
('413 Request Entity Too Large', 'Proxy or application body-size limit', 'محدودیت Body در Proxy یا برنامه', 'client_max_body_size / app limits'),
('Redirect Loop / CSRF', 'Wrong forwarded scheme or untrusted proxy/public URL settings', 'Scheme اشتباه یا تنظیم Trust Proxy/Public URL نادرست', 'Location / cookies / application logs'),
('421 Misdirected Request', 'SNI and HTTP host differ under the explicit strict policy', 'اختلاف SNI و Host طبق Policy سخت‌گیرانه', 'curl --resolve / Host / SNI log'),
('ACME Renewal Failed', 'Port 80 blocked, changed DNS, missing webroot exception or DNS credentials', 'Port 80 بسته، DNS تغییرکرده، نبود استثنای Webroot یا Credential DNS', 'certbot renew --dry-run / ACME logs'),
]
parts.append('<div class="table-responsive"><table><thead><tr>'+dual('th','Error','خطا')+dual('th','Likely root cause','علت احتمالی')+dual('th','Diagnostic','تشخیص')+'</tr></thead><tbody>')
for err, en, fa, diag in rows:
    parts.append('<tr><td>'+escape(err)+'</td>'+dual('td',en,fa)+'<td>'+escape(diag)+'</td></tr>')
parts.append('</tbody></table></div>')
code('''curl -v http://10.10.10.11:8080
nc -zv 10.10.10.11 8080
ip route get 10.10.10.11
sudo tail -f /var/log/nginx/error.log /var/log/nginx/app.example.com.error.log''')
p('For 502 start from the Nginx host: verify TCP, HTTP protocol and expected Host, then correlate the upstream address with the per-host error log. The global error log alone may miss errors assigned to a named server. connect() failed (111: Connection refused) suggests no accepting listener or a reject; upstream timed out points to a connect/read stage. Do not increase timeouts until the failing stage and application dependency are known.', 'برای 502 از Host Nginx شروع کنید: TCP، Protocol HTTP و Host مورد انتظار را بررسی و آدرس Upstream را با Error Log هر Host تطبیق دهید. Error Log عمومی ممکن است خطای واگذارشده به Named Server را نداشته باشد. connect() failed (111: Connection refused) نبود Listener پذیرنده یا Reject را مطرح می‌کند؛ upstream timed out به مرحله Connect/Read اشاره دارد. قبل از شناخت مرحله شکست و Dependency برنامه Timeout را بالا نبرید.')
code('sudo nginx -T\nsudo ss -lntp | grep ":443"\n\n# Run on the app backend, not the proxy:\nsudo ss -lntp | grep ":8080"')
p('nginx -T prints the effective configuration expanded across includes, plus a test. It is more useful than reading just one file when finding duplicate names, a packaged default site or inheritance. It prints configuration on disk, not proof that current workers loaded it: confirm the last reload succeeded. Its output may contain credentials in configured headers; redact it before sharing.', 'nginx -T تنظیم نهایی روی دیسک را با همه Includeها چاپ و تست می‌کند. برای پیداکردن نام تکراری، Default Site بسته یا Inheritance از خواندن یک فایل مفیدتر است. خروجی آن اثبات نمی‌کند Worker فعلی همین تنظیمات را بارگذاری کرده؛ موفقیت آخرین Reload را تأیید کنید. ممکن است Credential موجود در Header تنظیم‌شده را چاپ کند؛ پیش از اشتراک‌گذاری پالایش کنید.')
p('A backend bound only to 127.0.0.1:8080 is unreachable from a separate proxy server. Bind it to the intended private interface, or an appropriately firewalled wildcard listener, and recheck. Do not solve this by exposing the backend publicly. A refused connection and a timeout have different meanings; compare source/destination captures and firewall counters when ambiguous.', 'Backend که فقط روی 127.0.0.1:8080 Bind شده از Proxy جدا قابل دسترسی نیست. آن را روی Interface خصوصی مورد نظر یا Listener عمومی محلی با Firewall مناسب Bind و دوباره تست کنید. مشکل را با انتشار عمومی Backend حل نکنید. Refused و Timeout معنای متفاوت دارند؛ در وضعیت مبهم Capture مبدأ/مقصد و Counter فایروال را مقایسه کنید.')

section('root-cause', '19. Root Cause Analysis', '۱۹. تحلیل علت ریشه‌ای')
figure('Nginx Reverse Proxy Troubleshooting Flowchart.png', 'Trace failures from DNS through TLS to the backend application', 'ردیابی خرابی از DNS و TLS تا برنامه Backend')
code('''DNS Resolution
     |
     v
TCP/443 Reachable?
     |
     v
TLS Handshake OK?
     |
     v
Correct SNI Certificate?
     |
     v
Correct Nginx server_name?
     |
     v
Backend Reachable?
     |
     v
Backend Application Healthy?
     |
     v
Proxy Headers Correct?
     |
     v
Application Response''', 'text')
p('At the first failing gate, stop and gather evidence. DNS: compare A and AAAA from the client. TCP: test from outside and inspect listener/firewall/DNAT. TLS: separate protocol negotiation from hostname/chain verification. Routing: compare SNI, Host and upstream log. Backend: run nc and curl from the proxy. Application: inspect health endpoints and dependency logs. Headers: validate public URL, scheme, client-IP trust, origin and cookies.', 'در اولین مرحله ناموفق توقف و شاهد جمع کنید. DNS: A و AAAA را از سمت Client مقایسه کنید. TCP: از بیرون تست و Listener/Firewall/DNAT را بررسی کنید. TLS: مذاکره Protocol را از تأیید Hostname/Chain جدا کنید. Routing: SNI، Host و لاگ Upstream را تطبیق دهید. Backend: از Proxy فرمان nc و curl اجرا کنید. Application: Health Endpoint و لاگ Dependency را بررسی کنید. Headerها: Public URL، Scheme، Trust مربوط به Client IP، Origin و Cookie را اعتبارسنجی کنید.')
p('Incident record example: app returns 502, app error log shows refused 10.10.10.11:8080, nc from proxy fails, backend ss shows 127.0.0.1:8080. Root cause is incorrect bind address after an application update. Correct the private-interface binding, confirm the firewall source allow, retest from the proxy and external client, then document the change and add a remote health check.', 'نمونه ثبت Incident: app پاسخ 502 می‌دهد؛ Error Log اتصال Refused به 10.10.10.11:8080 را نشان می‌دهد؛ nc از Proxy شکست می‌خورد؛ ss روی Backend مقدار 127.0.0.1:8080 دارد. Root Cause تغییر اشتباه Bind Address پس از Update برنامه است. Bind روی Interface خصوصی را اصلاح، Source مجاز Firewall را تأیید و از Proxy و Client بیرونی تست کنید؛ سپس تغییر را ثبت و Health Check از راه دور اضافه کنید.')

section('nat', '20. Why NAT Alone Is Not Enough', '۲۰. Why NAT Alone Is Not Enough؛ چرا NAT کافی نیست؟')
figure('NAT vs Nginx Reverse Proxy.png', 'Ordinary NAT forwards by address and port; Nginx processes service names', 'NAT معمولی با آدرس و Port هدایت می‌کند؛ Nginx نام سرویس را پردازش می‌کند')
code('''203.0.113.10:443
    cannot select by domain using ordinary dst-nat:
    app.example.com -> 10.10.10.11:443
    api.example.com -> 10.10.10.12:443
    jira.example.com -> 10.10.10.13:443

Internet
   |
   v
Firewall / NAT
   |
   | TCP 443
   v
Nginx
   |
   +--> Backend A
   +--> Backend B
   +--> Backend C''', 'text')
p('Ordinary NAT operates on Layer 3/4 addresses and ports. The packets for these services share one destination IP and TCP port; a standard dst-nat rule cannot use the URL hostname to choose different backends. Multiple competing DNAT rules do not create name-based HTTPS hosting. Translate TCP/443 to Nginx, then let its TLS/HTTP processing select the certificate and request server. A specialized SNI-aware TCP proxy is another product/design, not ordinary NAT.', 'NAT معمولی روی آدرس و Port لایه ۳/۴ کار می‌کند. Packet این سرویس‌ها IP مقصد و TCP Port یکسان دارد؛ dst-nat استاندارد نمی‌تواند Hostname آدرس URL را برای انتخاب Backend مختلف استفاده کند. چند Rule رقیب DNAT میزبانی HTTPS مبتنی بر نام ایجاد نمی‌کند. TCP/443 را به Nginx ترجمه کنید؛ سپس پردازش TLS/HTTP آن گواهی و سرور Request را انتخاب می‌کند. TCP Proxy تخصصی آگاه از SNI محصول یا طراحی دیگری است، نه NAT معمولی.')

section('enterprise', '21. Enterprise Architecture Example', '۲۱. مثال معماری سازمانی')
code('''Internet
   |
   v
Public IP: 198.51.100.20:443
   |
Nginx Reverse Proxy
   |
Private Server VLAN
   +-- gitlab.company.com  -> 172.16.20.10:80
   +-- grafana.company.com -> 172.16.20.20:3000
   +-- zabbix.company.com  -> 172.16.20.30:8080
   +-- jira.company.com    -> 172.16.20.40:8080
   +-- api.company.com     -> 172.16.20.50:9000''', 'text')
p('All five company A records point to 198.51.100.20. In this variant the public IP is on the reverse-proxy ingress only; backend servers live in a private VLAN with no public IP or independent Internet publishing rule. Give each name an exact server block, appropriate certificate and its stated upstream. Separate management access from public application access.', 'رکورد A هر پنج نام سازمانی به 198.51.100.20 اشاره می‌کند. در این Variant، Public IP فقط روی ورودی Reverse Proxy قرار دارد؛ Backendها داخل Private VLAN هستند و IP عمومی یا Rule انتشار مستقل اینترنت ندارند. برای هر نام server block دقیق، Certificate مناسب و Upstream ذکرشده تعریف کنید. دسترسی مدیریت را از دسترسی عمومی برنامه جدا کنید.')
p('If the perimeter firewall owns the public IP instead, use this alternate ingress topology. The private proxy is 10.10.10.5. Backend addresses below intentionally use the separate 10.10.20.0/24 VLAN; they are a different firewall example, not changes to the main scenario.', 'اگر Public IP روی Firewall مرزی است، از ورودی جایگزین زیر استفاده کنید. IP خصوصی Proxy برابر 10.10.10.5 است. Backend این مثال عمداً در VLAN جدا 10.10.20.0/24 قرار دارد؛ این مثال Firewall مستقل است و آدرس‌های سناریوی اصلی را تغییر نمی‌دهد.')
code('''Internet
   |
   v
Firewall
Public IP: 203.0.113.10
   |
   | DNAT TCP/443
   v
NGINX
10.10.10.5
   +--> 10.10.20.11:8080
   +--> 10.10.20.12:9000
   +--> 10.10.20.13:8080''', 'text')
p('Permit Internet-to-proxy TCP/443 in the firewall forward policy as well as DNAT. Add TCP/80 only for the chosen redirect/ACME workflow. Permit proxy 10.10.10.5 to exactly the backend ports above and established return traffic; deny other sources. Backend firewalls should accept the actual proxy source IP seen after routing/SNAT. Verify return routing and use split DNS or a deliberately configured hairpin path for internal clients.', 'علاوه بر DNAT، Internet به TCP/443 Proxy را در Forward Policy فایروال مجاز کنید. TCP/80 فقط برای روش Redirect/ACME انتخاب‌شده اضافه شود. از Proxy با IP 10.10.10.5 فقط به Portهای Backend بالا و ترافیک برگشتی Established اجازه دهید و Source دیگر را رد کنید. Firewall Backend باید Source واقعی Proxy پس از Routing/SNAT را Accept کند. مسیر برگشت را بررسی و برای Client داخلی از Split DNS یا Hairpin آگاهانه تنظیم‌شده استفاده کنید.')

section('recommendations', '22. Security Recommendations and Operational Limits', '۲۲. توصیه‌های امنیتی و محدودیت عملیاتی')
items([
('Maintain a reviewed change plan, previous configuration, independent console access and a DNS TTL/cutover plan. Test each hostname and backend before and after cutover.', 'Change Plan بررسی‌شده، تنظیم قبلی، Console مستقل و برنامه TTL/Cutover DNS نگه دارید. هر Hostname و Backend را قبل و بعد از تغییر تست کنید.'),
('Use supported OS/Nginx/OpenSSL versions and patch them. Do not assume a syntactically valid configuration proves compliance with your organization’s cipher policy.', 'OS، Nginx و OpenSSL پشتیبانی‌شده استفاده و وصله کنید. معتبر بودن Syntax به معنی انطباق Cipher Policy سازمان نیست.'),
('Use upstream TLS with peer and hostname verification where required. HTTPS on the client side does not encrypt the private HTTP hops shown here.', 'در صورت نیاز از TLS Upstream با تأیید Peer و Hostname استفاده کنید. HTTPS سمت Client ارتباط HTTP خصوصی این سناریو را رمز نمی‌کند.'),
('Plan capacity for concurrent connections, long-lived WebSockets, upload temporary files, bandwidth and backend limits. One proxy and one backend per service are single points of failure.', 'ظرفیت Connection همزمان، WebSocket طولانی، فایل موقت Upload، پهنای باند و محدودیت Backend را برنامه‌ریزی کنید. یک Proxy و یک Backend برای هر سرویس نقطه شکست منفرد هستند.'),
('For high availability, two proxies can share one public virtual IP using an appropriate failover or load-balancing design. Synchronize configuration and certificates securely and test failover; a single public IP does not require a single physical proxy.', 'برای HA دو Proxy می‌توانند با طراحی مناسب Failover یا Load Balancing یک Public Virtual IP مشترک داشته باشند. تنظیمات و Certificate را امن همگام و Failover را تست کنید؛ یک Public IP الزاماً یک Proxy فیزیکی نیست.'),
('GitLab web traffic and HTTPS Git can use this listener. Git over SSH, mail, arbitrary TCP services and GitLab registry/Pages hostnames need their own protocol, name, certificate and port planning.', 'Web و Git روی HTTPS گیت‌لب می‌توانند از این Listener استفاده کنند. Git روی SSH، Mail، TCP دلخواه و نام‌های Registry/Pages گیت‌لب برنامه‌ریزی Protocol، نام، Certificate و Port جدا می‌خواهند.'),
])

section('conclusion', '23. Conclusion', '۲۳. نتیجه‌گیری')
p('DNS sends every name to one ingress. SNI chooses TLS identity during the handshake. HTTP name processing selects the request context, and proxy_pass forwards to the service backend. The production baseline includes an explicit default, name guards, renewable certificates, WebSocket headers, bounded uploads and per-host diagnostics. Release it only after validating all five routes, certificate trust, renewal and the firewall path.', 'DNS همه نام‌ها را به یک ورودی می‌فرستد. SNI هنگام Handshake هویت TLS را انتخاب می‌کند. پردازش نام HTTP، Context درخواست را تعیین و proxy_pass آن را به Backend سرویس ارسال می‌کند. تنظیم عملیاتی شامل Default صریح، Guard نام، گواهی قابل تمدید، Header WebSocket، Upload محدود و تشخیص جدا برای هر Host است. پس از تأیید هر پنج مسیر، Trust گواهی، تمدید و مسیر Firewall آن را عملیاتی کنید.')

section('seo-title', '24. SEO Title', '۲۴. عنوان SEO')
p(TITLE['en'], TITLE['fa'])
p('Suggested slug: '+SLUG, 'Slug پیشنهادی: '+SLUG)
section('meta-description', '25. Meta Description', '۲۵. توضیحات متا')
p(DESC['en'], DESC['fa'])
section('keywords', '26. Keywords', '۲۶. کلمات کلیدی')
code(', '.join(KEYWORDS), 'text')

FAQ = [
('Does DNS select the backend?', 'No. DNS returns the ingress IP; Nginx selects the request server and its proxy_pass destination.', 'آیا DNS، Backend را انتخاب می‌کند؟', 'خیر. DNS آدرس ورودی را برمی‌گرداند؛ Nginx سرور Request و مقصد proxy_pass را انتخاب می‌کند.'),
('Are SNI and Host interchangeable?', 'No. SNI is TLS handshake input; Host is HTTP request input. They can differ, which this configuration rejects.', 'آیا SNI و Host معادل هستند؟', 'خیر. SNI ورودی Handshake در TLS و Host ورودی درخواست HTTP است. ممکن است متفاوت باشند و این تنظیمات اختلاف را رد می‌کند.'),
('Is port 80 mandatory?', 'Not for HTTPS routing. It is required for HTTP-01 and optional for redirects; automated DNS-01 permits a 443-only ingress.', 'آیا Port 80 اجباری است؟', 'برای Routing HTTPS خیر. برای HTTP-01 لازم و برای Redirect اختیاری است؛ DNS-01 خودکار ورودی فقط 443 را ممکن می‌کند.'),
('Can I issue certificates for these exact example addresses?', 'No. Replace documentation IPs and reserved example domains with your own real reachable infrastructure.', 'آیا می‌توان برای همین آدرس‌های نمونه گواهی صادر کرد؟', 'خیر. IP مستنداتی و دامنه رزروشده مثال را با زیرساخت واقعی قابل دسترسی و تحت مالکیت خود جایگزین کنید.'),
('Does a 200 response prove the correct backend?', 'No. Verify service-specific content and the per-host log upstream address, as multiple services can return similar pages.', 'آیا پاسخ 200، Backend صحیح را اثبات می‌کند؟', 'خیر. محتوای مخصوص سرویس و آدرس Upstream لاگ هر Host را بررسی کنید؛ چند سرویس ممکن است صفحه مشابه برگردانند.'),
('Does curl -k validate TLS trust?', 'No. It disables certificate verification. Repeat without -k and verify hostname and chain before release.', 'آیا curl -k اعتماد TLS را بررسی می‌کند؟', 'خیر. بررسی گواهی را غیرفعال می‌کند. پیش از انتشار بدون -k تست و Hostname و Chain را تأیید کنید.'),
('Will a renewal automatically update active Nginx workers?', 'Not with certonly alone. Use a deploy hook that tests and reloads Nginx, then monitor the certificate served externally.', 'آیا تمدید به‌تنهایی گواهی Worker فعال Nginx را عوض می‌کند؟', 'با certonly به‌تنهایی خیر. Deploy Hook برای تست و Reload قرار دهید و گواهی ارائه‌شده از بیرون را مانیتور کنید.'),
('Can ordinary NAT distinguish these domains?', 'No. Ordinary L3/L4 DNAT sees the shared destination IP and port. Forward to Nginx for TLS/HTTP name processing.', 'آیا NAT معمولی این دامنه‌ها را تفکیک می‌کند؟', 'خیر. DNAT معمولی لایه ۳/۴ IP و Port مشترک مقصد را می‌بیند. ترافیک را برای پردازش نام TLS/HTTP به Nginx بفرستید.'),
]
section('faq', 'Frequently Asked Questions', 'پرسش‌های متداول')
for q, a, fq, fa in FAQ:
    parts.append('<div class="article-faq-item">'+dual('h3',q,fq)+dual('p',a,fa)+'</div>')

section('official-references', 'Official References', 'منابع رسمی')
p('Syntax and behavior checked against official documentation on 7 October 2026. Distribution packages can differ from upstream releases; validate the target binary and ACME client before deployment. The Linux deployment commands are for the target host, not commands executed against this Windows website workspace.', 'سینتکس و رفتار با مستندات رسمی در ۷ اکتبر ۲۰۲۶ بررسی شده است. بسته توزیع ممکن است با Release بالادستی متفاوت باشد؛ Binary و ACME Client مقصد را قبل از استقرار اعتبارسنجی کنید. فرمان‌های استقرار Linux برای Host مقصد هستند و روی Workspace ویندوزی این سایت اجرا نشده‌اند.')
for path, en, fa in [
('http/request_processing.html','Nginx request processing','پردازش Request در Nginx'),
('http/server_names.html','Nginx server names','نام سرورها در Nginx'),
('http/configuring_https_servers.html','Nginx HTTPS servers and SNI','HTTPS و SNI در Nginx'),
('http/ngx_http_ssl_module.html','Nginx SSL module and version requirements','SSL Module و پیش‌نیاز نسخه'),
('http/ngx_http_proxy_module.html','Nginx proxy module','Proxy Module'),
('http/websocket.html','Nginx WebSocket proxying','WebSocket در Nginx'),
('http/ngx_http_core_module.html','Nginx core module and body-size limits','Core Module و محدودیت Body'),
('http/ngx_http_log_module.html','Nginx log module','Log Module'),
('http/ngx_http_limit_req_module.html','Nginx request rate limiting','Rate Limit در Nginx'),
('control.html','Nginx reload behavior','رفتار Reload در Nginx'),
]: link('https://nginx.org/en/docs/'+path,en,fa)
for url,en,fa in [
('https://eff-certbot.readthedocs.io/en/stable/using.html','Certbot user guide','راهنمای Certbot'),
('https://letsencrypt.org/docs/challenge-types/','Let’s Encrypt challenges','Challengeهای Let’s Encrypt'),
('https://curl.se/docs/manpage.html','curl manual','راهنمای curl'),
('https://docs.openssl.org/3.0/man1/openssl-s_client/','OpenSSL s_client','راهنمای OpenSSL s_client'),
('https://www.rfc-editor.org/rfc/rfc5737','RFC 5737: documentation IPv4 ranges','RFC 5737: آدرس‌های IPv4 مستنداتی'),
('https://www.rfc-editor.org/rfc/rfc6797','RFC 6797: HSTS','RFC 6797: HSTS'),
('https://grafana.com/docs/grafana/latest/setup-grafana/configure-grafana/#root_url','Grafana root_url','تنظیم root_url در Grafana'),
('https://docs.gitlab.com/omnibus/settings/nginx/','GitLab external Nginx configuration','تنظیم Nginx خارجی برای GitLab'),
('https://confluence.atlassian.com/adminjiraserver/integrating-jira-with-nginx-933695894.html','Atlassian: Jira behind Nginx','Atlassian: Jira پشت Nginx'),
]: link(url,en,fa)
parts.append('</section>')

localizations = {lang: {'title':TITLE[lang], 'meta_title':TITLE[lang], 'description':DESC[lang], 'keywords':KEYWORDS, 'faq':[[q,a] if lang=='en' else [fq,fa] for q,a,fq,fa in FAQ]} for lang in ['en','fa']}
banner = '/assets/img/articles/banners/Nginx Reverse Proxy Routing Infographic.png'
url = 'https://meetaj.ir/articles/'+SLUG
schema = {'@context':'https://schema.org','@type':'Article','headline':TITLE['en'],'description':DESC['en'],'inLanguage':'en','datePublished':'2026-10-07T00:00:00+03:30','dateModified':'2026-10-07','image':banner,'author':{'@type':'Person','name':'AmirHossein Jalalian'}}
nav = ''.join('<li class="article-nav-item">'+dual('a',en,fa,f'href="#{id}"')+'</li>' for id,en,fa in toc)
html = f'''<!doctype html>
<html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{escape(TITLE['en'])}</title>
<meta name="article:content-language" content="en">
<meta name="description" content="{escape(DESC['en'], quote=True)}">
<meta name="keywords" content="{', '.join(KEYWORDS)}">
<meta name="robots" content="index, follow">
<meta property="og:title" content="{escape(TITLE['en'], quote=True)}">
<meta property="og:description" content="{escape(DESC['en'], quote=True)}">
<meta property="og:type" content="article"><meta property="og:image" content="{banner}">
<meta property="og:url" content="{url}"><link rel="canonical" href="{url}">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json">{json.dumps(schema, ensure_ascii=False)}</script>
<script id="article-localizations" type="application/json">{json.dumps(localizations, ensure_ascii=False)}</script>
</head><body class="article-page theme-linux">
<header><nav><ul>{nav}</ul></nav></header><main>
<section class="article-hero article-header hero">
{dual('span','Linux','لینوکس','class="article-category"')}
{dual('h1',TITLE['en'],TITLE['fa'],'class="article-title hero-title"')}
{dual('p',DESC['en'],DESC['fa'],'class="article-excerpt hero-subtitle"')}
<img class="article-hero-thumbnail" src="{banner}" alt="Nginx HTTPS reverse proxy">
</section><article class="article-body" lang="en" dir="ltr">
{chr(10).join(parts)}
</article></main></body></html>
'''
import sys
sys.path.insert(0, str(ROOT / 'scripts'))
from article_structure import normalize_html, normalize_markdown
html = normalize_html(html, SLUG)
(ROOT/'resources/legacy/articles'/f'{SLUG}.html').write_text(html, encoding='utf-8')
download = ROOT/'public/downloads'/SLUG
download.mkdir(parents=True, exist_ok=True)
(download/'reverse-proxy.conf').write_text(config, encoding='utf-8')
(download/'bootstrap-http.conf').write_text(bootstrap+'\n', encoding='utf-8')
for folder, filenames in {
    'banners': ['Nginx Reverse Proxy Routing Infographic.png'],
    'content': ['Nginx Reverse Proxy Infrastructure Diagram.png', 'Nginx SNI Routing Infographic.png', 'Nginx Reverse Proxy Troubleshooting Flowchart.png', 'NAT vs Nginx Reverse Proxy.png'],
}.items():
    for filename in filenames:
        destination = ROOT/'public/assets/img/articles'/folder/filename
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ROOT/'resources/assets/img/articles'/folder/filename, destination)
print(f'Built {SLUG}: {len(toc)} sections, bilingual source and downloadable configs')
