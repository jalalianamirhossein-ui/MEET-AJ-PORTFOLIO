"""Build the Tomcat bilingual editorial package in the established article format."""
from pathlib import Path
from html import escape
import json
import shutil

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'apache-tomcat-linux-installation-security-hardening'
SOURCE = ROOT / 'resources/content/articles' / SLUG
TITLE = {
    'en': 'Installing, Configuring, and Securing Apache Tomcat on Linux — Production-Ready Guide',
    'fa': 'آموزش نصب، راه‌اندازی و امن‌سازی Apache Tomcat روی لینوکس به‌صورت سرویس',
}
SEO_TITLE = {
    'en': 'Apache Tomcat 11 Installation and Security Hardening on Linux',
    'fa': 'آموزش نصب و امن‌سازی Apache Tomcat روی لینوکس',
}
DESC = {
    'en': 'Deploy Tomcat 11 on Ubuntu with Java 21, a hardened systemd service, Nginx HTTPS, restricted permissions, WAR deployment, monitoring and safe upgrades.',
    'fa': 'نصب Tomcat 11 با Java 21 روی Ubuntu، سرویس امن systemd، پروکسی Nginx و HTTPS، مجوزهای محدود، استقرار WAR، مانیتورینگ و ارتقای امن در محیط Production.',
}
KEYWORDS = ['Apache Tomcat 11', 'Tomcat Linux Installation', 'Tomcat Security Hardening', 'Java 21', 'Ubuntu 24.04', 'RHEL', 'systemd', 'Nginx Reverse Proxy', 'Jakarta Servlet', 'WAR Deployment', 'DevOps', 'امن سازی تامکت', 'نصب Tomcat لینوکس']
parts, toc, configs = [], [], {}
md = {lang: [f'# {TITLE[lang]}', '', DESC[lang], ''] for lang in TITLE}

def dual(tag, en, fa, attrs=''):
    if tag in ('a', 'figcaption'):
        return f'<{tag} {attrs}>' + dual('span', en, fa) + f'</{tag}>'
    return f'<{tag} {attrs} data-en="{escape(en, quote=True)}" data-fa="{escape(fa, quote=True)}">{escape(en)}</{tag}>'

def p(en, fa):
    parts.append(dual('p', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([value, ''])

def section(id, en, fa):
    if toc: parts.append('</section>')
    toc.append((id, en, fa))
    parts.append(f'<section id="{id}">' + dual('h2', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend(['## ' + value, ''])

def sub(en, fa):
    parts.append(dual('h3', en, fa))
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend(['### ' + value, ''])

def code(value, lang='bash', filename=None):
    value = value.strip()
    parts.append(f'<pre dir="ltr"><code class="language-{lang}">{escape(value)}</code></pre>')
    for locale in md: md[locale].extend([f'```{lang}', value, '```', ''])
    if filename: configs[filename] = value + '\n'

def items(pairs):
    parts.append('<ul>' + ''.join(dual('li', en, fa) for en, fa in pairs) + '</ul>')
    for lang, index in [('en', 0), ('fa', 1)]: md[lang].extend(['- ' + pair[index] for pair in pairs] + [''])

def table(headers, rows):
    parts.append('<table><thead><tr>' + ''.join(dual('th', *h) for h in headers) + '</tr></thead><tbody>' + ''.join('<tr>' + ''.join(dual('td', *cell) for cell in row) + '</tr>' for row in rows) + '</tbody></table>')
    for lang, index in [('en', 0), ('fa', 1)]:
        md[lang].extend(['| ' + ' | '.join(h[index] for h in headers) + ' |', '| ' + ' | '.join('---' for h in headers) + ' |'] + ['| ' + ' | '.join(c[index] for c in row) + ' |' for row in rows] + [''])

def link(url, en, fa):
    parts.append('<p>' + dual('a', en, fa, f'href="{escape(url, quote=True)}" rel="noopener"') + '</p>')
    editorial_url = 'https://meetaj.ir' + url if url.startswith('/articles/') else url
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([f'[{value}]({editorial_url})', ''])

def ref(path, en, fa): link('https://tomcat.apache.org/' + path, en, fa)

def figure(filename, en, fa):
    src = '/assets/img/articles/content/' + filename
    parts.append(f'<figure><img src="{src}" width="1920" height="1080" loading="lazy" decoding="async" alt="{escape(en, quote=True)}" data-en-alt="{escape(en, quote=True)}" data-fa-alt="{escape(fa, quote=True)}">' + dual('figcaption', en, fa) + '</figure>')
    for lang, value in [('en', en), ('fa', fa)]: md[lang].extend([f'![{value}]({src})', '', value, ''])

section('introduction', '1. Introduction: where Tomcat belongs', '۱. مقدمه؛ جایگاه Tomcat در معماری')
p('Apache Tomcat is a Java web container: its HTTP connector passes requests to Catalina, which manages applications, servlet lifecycles and sessions inside a JVM. Jasper handles JSP compilation. Tomcat implements selected Jakarta EE web specifications; it is not a complete Jakarta EE platform server with every enterprise API. Applications may bundle additional frameworks and libraries.', 'Apache Tomcat یک Web Container جاوا است: Connector درخواست HTTP را به Catalina می‌دهد که Application، چرخه Servlet و Session را در JVM مدیریت می‌کند. Jasper مسئول کامپایل JSP است. Tomcat بخشی از Specificationهای وب Jakarta EE را پیاده می‌کند و Application Server کامل با همه APIهای Enterprise نیست؛ برنامه می‌تواند Framework و Library موردنیاز را همراه خود داشته باشد.')
table([('Component', 'جزء'), ('Responsibility', 'مسئولیت')], [
    [('Tomcat', 'Tomcat'), ('Servlets, JSP and Java web applications inside a JVM; also serves HTTP and static files.', 'اجرای Servlet، JSP و برنامه وب Java در JVM؛ امکان سرو HTTP و فایل ثابت نیز دارد.')],
    [('Apache HTTP Server', 'Apache HTTP Server'), ('General web server with modules, static delivery and reverse proxying; separate product from Tomcat.', 'وب‌سرور عمومی با Module، سرو فایل ثابت و Reverse Proxy؛ محصولی جدا از Tomcat.')],
    [('Nginx', 'Nginx'), ('Edge web server and reverse proxy for TLS termination, routing and request controls; does not execute Java servlets.', 'وب‌سرور و Reverse Proxy لبه برای TLS، Routing و کنترل Request؛ Servlet جاوا اجرا نمی‌کند.')],
])
p('Enterprise uses include internal portals, REST services and vendor WAR applications. Verify the vendor support matrix: an application certified for Tomcat 9 cannot be assumed to work on 11. A single-host deployment is a baseline, not high availability. Larger systems use multiple application nodes, a load balancer, external data stores and an explicit session strategy.', 'کاربردهای Enterprise شامل پورتال داخلی، سرویس REST و نرم‌افزار WAR سازمانی است. Support Matrix سازنده را بررسی کنید: برنامه تأییدشده برای Tomcat 9 الزاماً روی 11 اجرا نمی‌شود. استقرار تک‌سرور مبنای راهنما است و High Availability نیست؛ معماری بزرگ‌تر چند نود، Load Balancer، Data Store خارجی و راهبرد Session مشخص دارد.')
ref('tomcat-11.0-doc/index.html', 'Official Tomcat 11 specifications and documentation', 'Specificationها و مستندات رسمی Tomcat 11')

section('architecture', '2. Architecture and server requirements', '۲. معماری و پیش‌نیازهای سرور')
figure('apache-tomcat-production-architecture.png', 'Internet through firewall and Nginx TLS on 443 to localhost Tomcat 8080 and a Java application in the JVM', 'ارتباط اینترنت از Firewall و Nginx با TLS روی 443 به Tomcat محلی روی 8080 و برنامه Java داخل JVM')
code('Internet -> Firewall -> Nginx HTTPS :443\n                            |\n                            v\n                 Tomcat 127.0.0.1:8080\n                            |\n                            v\n                   Java application (JVM)', 'text')
section('prerequisites', 'Server prerequisites and directory plan', 'پیش‌نیازهای سرور و طرح مسیرها')
p('The worked platform is Ubuntu Server 24.04 LTS on a fresh dedicated host, Bash, sudo and systemd. The RHEL-compatible alternatives target a maintained RHEL 9 family host with Java 21 packages available. Do not combine APT and DNF commands. This guide uses the upstream archive, not the distribution Tomcat service; do not run a packaged Tomcat instance on the same port.', 'پلتفرم اصلی Ubuntu Server 24.04 LTS روی Host جدید و اختصاصی با Bash، sudo و systemd است. گزینه RHEL برای سیستم نگهداری‌شده خانواده RHEL 9 با بسته Java 21 موجود است. فرمان APT و DNF را ترکیب نکنید. این راهنما از Archive بالادستی استفاده می‌کند، نه سرویس Tomcat بسته توزیع؛ Instance بسته را روی همان Port همزمان اجرا نکنید.')
items([
    ('Capacity example: 2–4 vCPU, 4 GiB RAM and SSD storage for a modest application; load-test heap, native memory, threads, disk and response times. These are planning values, not official minimums.', 'مثال ظرفیت: ۲ تا ۴ vCPU، حافظه 4 GiB و SSD برای برنامه کوچک؛ Heap، Native Memory، Thread، دیسک و زمان پاسخ را Load Test کنید. این اعداد برای برنامه‌ریزی‌اند، نه حداقل رسمی.'),
    ('DNS: replace tomcat.example.com with your real domain. A/AAAA records must reach this proxy; remove unusable AAAA records. Synchronize time and permit controlled DNS, NTP and update/ACME egress.', 'DNS: دامنه tomcat.example.com را با دامنه واقعی عوض کنید. A/AAAA باید به Proxy برسد؛ AAAA نامعتبر را حذف کنید. زمان را همگام و خروجی DNS، NTP و Update/ACME را کنترل کنید.'),
    ('Ingress: public HTTPS 443; HTTP 80 for HTTP-01 and redirection; SSH on the actual management port from approved sources. No public 8080, 8009 or 8005.', 'ورودی: HTTPS عمومی 443؛ HTTP روی 80 برای HTTP-01 و Redirect؛ SSH روی Port واقعی مدیریت از Source مجاز. 8080، 8009 و 8005 عمومی نباشند.'),
    ('Change control: console access, staging tests, backups, a maintenance window and application-owner approval for production changes.', 'Change Control: دسترسی Console، تست Staging، Backup، Maintenance Window و تأیید مالک برنامه برای تغییر Production.'),
])
table([('Path', 'مسیر'), ('Ownership and purpose', 'مالکیت و کاربرد')], [
    [('/opt/tomcat/apache-tomcat-11.0.26', '/opt/tomcat/apache-tomcat-11.0.26'), ('root:tomcat; immutable release binaries. /opt/tomcat/current selects CATALINA_HOME.', 'root:tomcat؛ Binary نسخه محافظت‌شده. /opt/tomcat/current انتخاب CATALINA_HOME است.')],
    [('/var/lib/tomcat', '/var/lib/tomcat'), ('CATALINA_BASE; root-owned parent, conf/bin/lib/webapps protected from the service user.', 'CATALINA_BASE؛ Parent متعلق به root و conf/bin/lib/webapps غیرقابل نوشتن برای سرویس.')],
    [('/var/lib/tomcat/{work,temp,data}', '/var/lib/tomcat/{work,temp,data}'), ('tomcat:tomcat; scratch space, JVM temporary files and explicit application state.', 'tomcat:tomcat؛ Scratch، فایل موقت JVM و State صریح برنامه.')],
    [('/var/log/tomcat', '/var/log/tomcat'), ('tomcat:tomcat; access/application logs. BASE/logs is a symlink here.', 'tomcat:tomcat؛ لاگ Access و برنامه. BASE/logs به این مسیر Symlink می‌شود.')],
    [('/etc/tomcat/tomcat.env', '/etc/tomcat/tomcat.env'), ('root:tomcat 0640; reviewed JVM/service settings, no credentials.', 'root:tomcat با 0640؛ تنظیم بررسی‌شده JVM و سرویس، بدون Credential.')],
])
code('hostnamectl\ncat /etc/os-release\nfree -h\ndf -h\ntimedatectl\nsystemd --version\nsudo ss -lntp\ngetent hosts tomcat.example.com')
p('Check mount capacity, existing listeners and kernel security policy before installation. A separate runtime volume must be mounted at the documented path before startup. Treat application uploads and heap dumps as sensitive data, with quotas and restricted retention.', 'ظرفیت Mount، Listener موجود و Policy امنیت Kernel را پیش از نصب بررسی کنید. Volume جدا برای Runtime باید قبل از Start در مسیر مستند Mount شود. Upload برنامه و Heap Dump داده حساس‌اند؛ Quota و Retention محدود تعریف کنید.')

section('versions', '3. Verified stable release and Java compatibility', '۳. نسخه Stable بررسی‌شده و سازگاری Java')
p('Verified on 9 October 2026: the official Apache download and version matrix list Tomcat 11.0.26 as the latest stable 11 release. The example pins that version and uses Java 21 LTS from maintained distribution packages. Java 21 is a deliberate compatible choice, not a claim that it is the newest Java feature release. Recheck Apache security advisories and the stable download page before every installation; a pinned example will age.', 'در ۹ اکتبر ۲۰۲۶، صفحه Download و Version Matrix رسمی Apache نسخه 11.0.26 را آخرین Stable شاخه 11 معرفی می‌کنند. مثال همین نسخه را Pin و Java 21 LTS را از بسته نگهداری‌شده توزیع نصب می‌کند. Java 21 انتخاب سازگار آگاهانه است، نه ادعای جدیدترین Feature Release جاوا. پیش از هر نصب، Security Advisory و صفحه Stable را دوباره بررسی کنید؛ نسخه ثابت مثال با زمان قدیمی می‌شود.')
ref('download-11.cgi', 'Official Tomcat 11 stable download and integrity files', 'دانلود رسمی Stable نسخه Tomcat 11 و فایل‌های Integrity')
ref('whichversion.html', 'Official Java and Servlet compatibility matrix', 'جدول رسمی سازگاری Java و Servlet')
table([('Branch', 'شاخه'), ('Minimum Java / API', 'حداقل Java و API'), ('Migration consequence', 'نتیجه مهاجرت')], [
    [('9.0.x', '9.0.x'), ('Java 8+; Servlet 4.0 / Java EE 8', 'Java 8+؛ Servlet 4.0 و Java EE 8'), ('Legacy web APIs use javax.servlet.*.', 'API وب قدیمی از javax.servlet.* استفاده می‌کند.')],
    [('10.1.x', '10.1.x'), ('Java 11+; Servlet 6.0 / Jakarta EE 10', 'Java 11+؛ Servlet 6.0 و Jakarta EE 10'), ('Jakarta web APIs use jakarta.*; 10.0 is end-of-life.', 'API وب Jakarta از jakarta.* استفاده می‌کند؛ 10.0 به پایان پشتیبانی رسیده است.')],
    [('11.0.x', '11.0.x'), ('Java 17+; Servlet 6.1 / Pages 4.0', 'Java 17+؛ Servlet 6.1 و Pages 4.0'), ('Java 21 meets the runtime minimum; test framework and API changes.', 'Java 21 حداقل Runtime را پوشش می‌دهد؛ تغییر Framework و API را تست کنید.')],
])
p('Rebuild Java EE applications and dependencies for Jakarta APIs, then test them; changing an import name or running a migration tool alone does not prove compatibility. Java SE packages such as javax.sql remain javax.*. Check removed APIs and changed defaults in the Tomcat 11 migration guide, and ensure application bytecode is no newer than the chosen JVM. A WAR containing classes compiled for Java 25 will not run on Java 21.', 'برنامه Java EE و Dependency را برای Jakarta بازسازی و سپس تست کنید؛ تغییر Import یا اجرای Migration Tool به‌تنهایی اثبات سازگاری نیست. Packageهای Java SE مثل javax.sql همچنان javax.* هستند. API حذف‌شده و Default تغییرکرده را در Migration Guide نسخه 11 بررسی کنید؛ Bytecode برنامه از JVM انتخابی جدیدتر نباشد. WAR کامپایل‌شده برای Java 25 روی Java 21 اجرا نمی‌شود.')
ref('migration-11.0.html', 'Tomcat 11 migration and configuration differences', 'مهاجرت Tomcat 11 و تفاوت Configuration')
ref('security-11.html', 'Tomcat 11 security advisories', 'Security Advisoryهای Tomcat 11')

section('java', '4. Install and verify Java 21', '۴. نصب و بررسی Java 21')
sub('Ubuntu 24.04 LTS', 'Ubuntu 24.04 LTS')
code('sudo apt update\nsudo apt upgrade\nsudo apt install -y openjdk-21-jdk-headless ca-certificates curl tar gzip unzip\njava -version\njavac -version\nupdate-alternatives --list java\nsudo update-alternatives --config java')
p('Review package upgrades and any reboot requirement during maintenance. Select the Java 21 alternative if multiple JDKs exist. Install the headless JDK for diagnostic tools and application build compatibility; build release artifacts in CI rather than on the production host.', 'Upgrade بسته و نیاز Reboot را در Maintenance بررسی کنید. در وجود چند JDK، Alternative مربوط به Java 21 را انتخاب کنید. JDK بدون GUI ابزار تشخیص و سازگاری Build را فراهم می‌کند؛ Artifact انتشار را در CI بسازید، نه روی Production.')
sub('RHEL 9 family alternative', 'گزینه جایگزین خانواده RHEL 9')
code('sudo dnf upgrade\nsudo dnf install -y java-21-openjdk-devel ca-certificates curl tar gzip unzip\nsudo alternatives --config java\njava -version\njavac -version')
p('On RHEL, confirm the enabled supported repositories and package candidate for your OS minor release. The generic archive layout below remains the same; firewall, Nginx package configuration and SELinux policies differ.', 'روی RHEL، Repository پشتیبانی‌شده فعال و Candidate بسته را برای Minor Release سیستم بررسی کنید. Layout عمومی Archive در ادامه یکسان است؛ Firewall، Configuration بسته Nginx و Policyهای SELinux تفاوت دارند.')
sub('Resolve JAVA_HOME without assuming CPU architecture', 'تشخیص JAVA_HOME بدون فرض معماری CPU')
code('JAVA_INSTALL_HOME="$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")"\nprintf "JAVA_HOME=%s\\n" "$JAVA_INSTALL_HOME"\n"$JAVA_INSTALL_HOME/bin/java" -version\n"$JAVA_INSTALL_HOME/bin/javac" -version\njava -XshowSettings:properties -version 2>&1 | grep -E "java.home|java.version|os.arch"')
p('Expect Java 21 and a real JDK directory, not /usr/bin. Record the resolved path: systemd does not evaluate $(...) in an EnvironmentFile. The service environment below stores that literal path, so later changes to the shell alternative do not silently switch the service JVM.', 'انتظار Java 21 و مسیر واقعی JDK را دارید، نه /usr/bin. مسیر را ثبت کنید: systemd در EnvironmentFile عبارت $(...) را اجرا نمی‌کند. Environment سرویس در ادامه مسیر Literal را ذخیره می‌کند؛ تغییر Alternative در Shell، JVM سرویس را پنهانی تغییر نمی‌دهد.')
link('https://openjdk.org/projects/jdk/21/', 'OpenJDK JDK 21 project reference', 'مرجع پروژه OpenJDK JDK 21')
link('https://developers.redhat.com/blog/2018/12/10/install-java-rhel8', 'Red Hat: supported OpenJDK package installation and alternatives', 'Red Hat: نصب بسته OpenJDK و Alternativeها')
link('https://docs.oracle.com/en/java/javase/21/docs/specs/man/java.html', 'Java 21 launcher, heap and JVM options reference', 'مرجع Launcher، Heap و گزینه‌های JVM در Java 21')

section('configuration', '5. Install Tomcat with protected release directories', '۵. نصب Tomcat با مسیر نسخه محافظت‌شده')
p('The commands in chapters 5–7 provision a new instance. If the user or paths already exist, stop and inspect them; do not overwrite an operational instance. Run each Bash block in order and stop on any error. No service starts until the hardened server.xml and service unit have been installed.', 'فرمان‌های فصل ۵ تا ۷ Instance جدید می‌سازند. اگر User یا مسیر موجود است، ابتدا آن را بررسی کنید؛ Instance عملیاتی را Overwrite نکنید. Blockهای Bash را به‌ترتیب اجرا و در هر خطا متوقف شوید. تا نصب server.xml امن و Unit سرویس، Start انجام نمی‌شود.')
sub('Create the account and separate writable state', 'ساخت حساب و جداسازی State قابل نوشتن')
code(r'''sudo groupadd --system tomcat
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
id tomcat''')
p('The nologin path above exists on Ubuntu; confirm it on your distribution with command -v nologin and substitute the returned path where needed. Never grant this account sudo. The service can write only runtime state and logs, not bin, lib, conf or webapps. Uploads belong under data or a separately reviewed storage location.', 'مسیر nologin بالا در Ubuntu وجود دارد؛ روی توزیع دیگر با command -v nologin مسیر را بررسی و در صورت نیاز جایگزین کنید. به این حساب sudo ندهید. سرویس فقط در Runtime و Log می‌نویسد، نه bin، lib، conf یا webapps. Upload زیر data یا Storage جدا و بررسی‌شده قرار می‌گیرد.')
sub('Download and verify the official SHA-512 checksum', 'دانلود و بررسی SHA-512 رسمی')
code(r'''set -euo pipefail
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
sudo find /var/lib/tomcat/conf -type f -exec chmod 0640 {} +''')
p('Require an OK checksum result before extraction. The checksum is fetched from Apache, not invented or copied into this guide. SHA-512 detects corruption; fetching both files over HTTPS does not provide independent publisher identity verification. Enterprises needing that assurance should also verify the detached .asc signature with a release-manager key whose fingerprint was independently authenticated. A good signature from an unverified key is insufficient.', 'قبل از Extract باید Checksum نتیجه OK بدهد. Hash از Apache دریافت می‌شود و در این راهنما ساخته یا ثابت نشده است. SHA-512 خرابی فایل را تشخیص می‌دهد؛ دریافت هر دو فایل با HTTPS تأیید مستقل هویت ناشر نیست. سازمان نیازمند این اطمینان باید Signature جداگانه .asc را با کلید Release Manager و Fingerprint مستقل تأییدشده بررسی کند. Good Signature از کلید تأییدنشده کافی نیست.')
p('When Apache rotates this release out of the mirror, select the latest supported stable patch and its official URLs; do not bypass integrity checks or automatically substitute an old archive. The copy into CATALINA_BASE is only for first installation. Future upgrades compare stock configuration changes and preserve reviewed local settings.', 'پس از خروج این Release از Mirror، آخرین Patch Stable پشتیبانی‌شده و URL رسمی آن را انتخاب کنید؛ Integrity را دور نزنید و Archive قدیمی را خودکار جایگزین نکنید. Copy به CATALINA_BASE فقط برای نصب اولیه است. در Upgrade بعدی تغییرات Configuration پیش‌فرض را مقایسه و تنظیم محلی بررسی‌شده را حفظ کنید.')
ref('tomcat-11.0-doc/RUNNING.txt', 'CATALINA_HOME, CATALINA_BASE and startup environment', 'CATALINA_HOME، CATALINA_BASE و Environment اجرا')
link('https://www.apache.org/info/verification.html', 'Apache release verification and trusted signatures', 'بررسی Release و Signature مورد اعتماد Apache')
sub('Write the resolved service environment', 'نوشتن Environment سرویس با مسیر واقعی Java')
code(r'''JAVA_INSTALL_HOME="$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")"
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
sudo -u tomcat test -w /var/lib/tomcat/work''')
p('The 2 GiB heap is an initial budget for the 4 GiB example host. Reserve RAM for metaspace, JIT code, thread stacks, direct buffers, Nginx and the OS. ExitOnOutOfMemoryError allows a failed JVM to be restarted; it is not a memory-leak fix. EnvironmentFile is not a shell script. Do not put secrets here or create a conflicting setenv.sh/JRE_HOME override.', 'Heap برابر 2 GiB بودجه اولیه Host نمونه 4 GiB است. برای Metaspace، JIT، Stack، Direct Buffer، Nginx و OS حافظه باقی بگذارید. ExitOnOutOfMemoryError امکان Restart JVM خراب را می‌دهد و درمان Memory Leak نیست. EnvironmentFile اسکریپت Shell نیست. Secret در آن نگذارید و setenv.sh یا JRE_HOME متعارض ایجاد نکنید.')

section('systemd', '6. Run Tomcat as a hardened systemd service', '۶. اجرای Tomcat به‌صورت سرویس امن systemd')
figure('apache-tomcat-systemd-service.png', 'Linux systemd manages the non-root Tomcat service, Java 21 JVM, application, journal logging and automatic startup', 'مدیریت سرویس غیر root تامکت، JVM جاوا 21، برنامه، Journal و شروع خودکار توسط systemd لینوکس')
p('Create /etc/systemd/system/tomcat.service with the complete unit below. catalina.sh run keeps the JVM in the foreground so systemd tracks the real process. No PID file or forking wrapper is needed. systemd sends SIGTERM for stop/restart; the JVM shutdown hook stops Tomcat even with the shutdown TCP port disabled.', 'فایل /etc/systemd/system/tomcat.service را با Unit کامل زیر بسازید. catalina.sh run، JVM را Foreground نگه می‌دارد تا systemd Process واقعی را دنبال کند. PID File یا Wrapper از نوع Fork نیاز نیست. systemd برای Stop/Restart، SIGTERM می‌فرستد؛ Shutdown Hook جاوا حتی با غیرفعال‌بودن Port خاموشی، Tomcat را متوقف می‌کند.')
code('sudoedit /etc/systemd/system/tomcat.service')
code(r'''[Unit]
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
WantedBy=multi-user.target''', 'ini', 'tomcat.service')
items([
    ('ProtectSystem=strict makes the filesystem read-only in the service namespace; ReadWritePaths reopens only existing runtime directories. File ownership still applies. Application data needs its documented exception, not a writable /opt or conf.', 'ProtectSystem=strict فایل‌سیستم را در Namespace سرویس Read-only می‌کند؛ ReadWritePaths فقط مسیر Runtime موجود را باز می‌کند. Ownership همچنان لازم است. داده برنامه استثنای مستند می‌خواهد، نه /opt یا conf قابل نوشتن.'),
    ('ProtectHome hides home directories; keep the JDK, certificates and required files out of /home. PrivateTmp isolates /tmp and /var/tmp; CATALINA_TMPDIR remains the explicit JVM temporary path.', 'ProtectHome مسیر Home را مخفی می‌کند؛ JDK، Certificate و فایل لازم را زیر /home نگذارید. PrivateTmp، /tmp و /var/tmp را جدا می‌کند؛ CATALINA_TMPDIR مسیر صریح فایل موقت JVM است.'),
    ('NoNewPrivileges, an empty capability set and RestrictSUIDSGID limit privilege gain. Ports above 1024 do not require bind capabilities. JVM JIT requires executable memory, so MemoryDenyWriteExecute is deliberately omitted.', 'NoNewPrivileges، Capability Set خالی و RestrictSUIDSGID افزایش Privilege را محدود می‌کنند. Port بالای 1024 به Capability برای Bind نیاز ندارد. JIT جاوا حافظه قابل اجرا می‌خواهد؛ MemoryDenyWriteExecute عمداً فعال نشده است.'),
    ('LimitNOFILE=16384 is a sample ceiling above the connector connection budget; include database sockets and application files in sizing. Avoid restrictive task/syscall filters without testing native libraries and JVM diagnostics.', 'LimitNOFILE=16384 سقف نمونه بالاتر از بودجه Connection است؛ Socket دیتابیس و فایل برنامه را در Sizing لحاظ کنید. بدون تست Native Library و ابزار تشخیص JVM، Task یا Syscall Filter محدود اعمال نکنید.'),
    ('network-online orders startup after the configured wait-online implementation; it does not prove DNS/database readiness. Application connection retries remain necessary. Start limits stop restart storms.', 'network-online اجرای سرویس را پس از Wait-online تنظیم‌شده مرتب می‌کند و سلامت DNS/Database را تضمین نمی‌کند. Retry اتصال برنامه همچنان لازم است. Start Limit از Restart Storm جلوگیری می‌کند.'),
])
sub('Send container logging to the journal', 'ارسال لاگ Container به Journal')
p('Replace /var/lib/tomcat/conf/logging.properties on this new instance with the console-only JULI configuration below. Container logs then reach journalctl without duplicate JULI files. AccessLogValve still writes separate access logs; application logging frameworks need their own console or protected file configuration.', 'روی Instance جدید، /var/lib/tomcat/conf/logging.properties را با JULI فقط Console زیر جایگزین کنید. لاگ Container بدون فایل تکراری JULI به journalctl می‌رسد. AccessLogValve همچنان فایل Access جدا می‌نویسد؛ Framework لاگ برنامه، Console یا فایل محافظت‌شده خود را نیاز دارد.')
code('sudoedit /var/lib/tomcat/conf/logging.properties')
code(r'''handlers = java.util.logging.ConsoleHandler
.handlers = java.util.logging.ConsoleHandler
.level = INFO
java.util.logging.ConsoleHandler.level = INFO
java.util.logging.ConsoleHandler.formatter = org.apache.juli.OneLineFormatter''', 'properties', 'logging.properties')
code('sudo systemd-analyze verify /etc/systemd/system/tomcat.service\n# Install chapter 7 server.xml before starting this new instance.\nsudo systemctl daemon-reload\nsudo systemctl enable --now tomcat\nsudo systemctl status tomcat --no-pager\nsudo systemctl restart tomcat\nsudo journalctl -u tomcat -n 100 --no-pager\nsudo systemd-analyze security tomcat.service\nsudo systemctl show tomcat -p User -p Group -p MainPID -p LimitNOFILE -p ReadWritePaths')
p('After chapter 7 is installed, expect active (running), user tomcat and a 127.0.0.1:8080 listener. Type=simple active status alone is not HTTP readiness. If start limiting is reached, fix the error, run systemctl reset-failed tomcat and start again. Review the security score as a diagnostic, not a certification; JVM-compatible exceptions affect it.', 'پس از نصب فصل ۷، انتظار active (running)، User برابر tomcat و Listener روی 127.0.0.1:8080 را دارید. Active در Type=simple به‌تنهایی آمادگی HTTP نیست. در رسیدن به Start Limit، خطا را اصلاح و systemctl reset-failed tomcat و Start را اجرا کنید. Security Score ابزار تشخیص است، نه گواهی امنیت؛ استثناهای سازگار با JVM بر آن اثر دارند.')
link('https://manpages.ubuntu.com/manpages/noble/man5/systemd.exec.5.html', 'systemd execution, filesystem sandbox and logging options on Ubuntu 24.04', 'گزینه‌های اجرای systemd، Sandbox فایل‌سیستم و لاگ در Ubuntu 24.04')
link('https://manpages.ubuntu.com/manpages/noble/man5/systemd.service.5.html', 'systemd service lifecycle, restart and stop behavior', 'چرخه سرویس systemd، Restart و Stop')
ref('tomcat-11.0-doc/logging.html', 'Tomcat JULI and application logging', 'لاگ JULI و Application در Tomcat')

section('security', '7. Enterprise security hardening', '۷. امن‌سازی Enterprise')
figure('apache-tomcat-security-hardening.png', 'Tomcat defense in depth: firewall, Nginx TLS, loopback backend, root-owned configuration and a restricted systemd service', 'دفاع چندلایه Tomcat: Firewall، TLS در Nginx، Backend محلی، Configuration متعلق به root و سرویس محدود systemd')
sub('Operating system and deployment boundary', 'مرز سیستم‌عامل و Deployment')
p('Use one non-root account per trust boundary, controlled sudo for operators, maintained OS/JDK packages and a restricted firewall. Protect both release directories and the current symlink from the Tomcat user. Unix permissions plus the systemd namespace protect configuration; they do not isolate mutually hostile applications within the same JVM. Use separate instances or VMs for separate trust domains. Tomcat 11 has removed Java SecurityManager support; do not apply old catalina.policy or -security instructions.', 'برای هر مرز اعتماد حساب غیر root جدا، sudo کنترل‌شده برای Operator، OS/JDK به‌روز و Firewall محدود استفاده کنید. مسیر Release و Symlink مربوط به current برای User سرویس غیرقابل تغییر باشند. Permission لینوکس و Namespace systemd از Configuration حفاظت می‌کنند، اما برنامه‌های نامطمئن در یک JVM را از هم جدا نمی‌کنند. برای Trust Domain جدا، Instance یا VM جدا بسازید. Tomcat 11 پشتیبانی Java SecurityManager را حذف کرده؛ دستور قدیمی catalina.policy یا -security اعمال نکنید.')
sub('Do not deploy bundled administrative applications', 'برنامه مدیریتی بسته را Deploy نکنید')
p('CATALINA_BASE/webapps was created empty. Do not copy ROOT, docs, examples, manager or host-manager from CATALINA_HOME/webapps into it. The installed binary archive can retain those protected files without publishing them because this Host uses the separate BASE appBase. Audit BASE/conf/Catalina/localhost for descriptors referencing them. For an existing installation, remove approved unused applications and descriptors during a controlled change after backup, rather than deleting every webapp.', 'CATALINA_BASE/webapps خالی ساخته شد. ROOT، docs، examples، manager یا host-manager را از CATALINA_HOME/webapps به آن Copy نکنید. Archive می‌تواند فایل محافظت‌شده آن‌ها را نگه دارد بدون انتشار، چون Host از appBase جدا در BASE استفاده می‌کند. BASE/conf/Catalina/localhost را برای Descriptor اشاره‌کننده بررسی کنید. در نصب موجود، فقط برنامه و Descriptor غیرضروری تأییدشده را با Backup در Change کنترل‌شده حذف کنید؛ همه Webappها را پاک نکنید.')
code('sudo ls -la /var/lib/tomcat/webapps /var/lib/tomcat/conf/Catalina/localhost\nsudo -u tomcat test ! -w /opt/tomcat/current/bin/catalina.sh\nsudo -u tomcat test ! -w /etc/tomcat/tomcat.env\nsudo stat -c "%U:%G %a %n" /var/lib/tomcat/conf/server.xml /var/lib/tomcat/webapps /var/log/tomcat')
p('If Manager is explicitly required, use an independent management listener/host on a private network, firewall/VPN allowlists, TLS, named accounts and minimum roles. Restrict manager-script separately from the browser manager-gui role. A loopback-only management valve alone is insufficient when a public local reverse proxy can reach it. This baseline deploys no management apps and also blocks their public paths at Nginx. JMX is not enabled; remote JMX requires authenticated encrypted transport and restricted ports.', 'اگر Manager صریحاً لازم است، Listener/Host مدیریت جدا در Private Network با Firewall/VPN Allowlist، TLS، حساب مشخص و حداقل Role داشته باشید. manager-script را از Role مرورگر manager-gui جدا محدود کنید. Valve فقط Loopback کافی نیست اگر Reverse Proxy عمومی محلی به آن برسد. این Baseline برنامه مدیریتی Deploy نمی‌کند و Nginx مسیر عمومی آن را نیز می‌بندد. JMX فعال نیست؛ JMX راه‌دور به Transport رمزنگاری و احراز هویت‌شده و Port محدود نیاز دارد.')
sub('Complete hardened server.xml for the local proxy', 'server.xml کامل و امن برای Proxy محلی')
p('Write /var/lib/tomcat/conf/server.xml below, replacing the copied default on the new instance. There is one HTTP connector and no AJP connector. port=-1 disables the shutdown listener; use systemctl stop. The fixed public proxy name/scheme/port are appropriate only for this one-domain HTTPS topology.', 'فایل /var/lib/tomcat/conf/server.xml زیر را روی Instance جدید به‌جای Default کپی‌شده بنویسید. فقط یک HTTP Connector دارد و AJP ندارد. port=-1، Listener خاموشی را غیرفعال می‌کند؛ از systemctl stop استفاده کنید. نام، Scheme و Port ثابت Proxy فقط برای همین توپولوژی HTTPS تک‌دامنه مناسب است.')
code('sudoedit /var/lib/tomcat/conf/server.xml')
code(r'''<?xml version="1.0" encoding="UTF-8"?>
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
</Server>''', 'xml', 'server.xml')
p('RemoteIpValve trusts exactly the loopback peer, using the CIDR syntax documented for 11.0.26. Nginx overwrites X-Forwarded-For with the TCP client address. Scheme and port are fixed on this connector, so forwarded scheme/host/port are not used by this valve. Any local process that can connect to 8080 can still forge that IP header; loopback is a host trust boundary, not process authentication. Do not apply this fixed-HTTPS connector to direct public HTTP traffic.', 'RemoteIpValve فقط Peer محلی را با سینتکس CIDR مستند برای 11.0.26 اعتماد می‌کند. Nginx مقدار X-Forwarded-For را با آدرس Client اتصال TCP جایگزین می‌کند. Scheme و Port در Connector ثابت‌اند، بنابراین Valve از Forwarded Scheme/Host/Port استفاده نمی‌کند. Process محلی دارای دسترسی 8080 هنوز می‌تواند Header IP جعل کند؛ Loopback مرز اعتماد Host است، نه احراز هویت Process. این Connector HTTPS ثابت را برای HTTP عمومی مستقیم استفاده نکنید.')
p('autoDeploy=false prevents background hot deployment; deployOnStartup=true loads reviewed artifacts on restart. unpackWARs=false permits read-only WAR deployment and can reduce performance; test the application. deployXML=false ignores embedded META-INF/context.xml, so move required context/JNDI settings into a reviewed root-owned descriptor under conf/Catalina/localhost. Applications needing an expanded directory must be extracted by the deployment operator with read-only ownership before startup, not by granting webapps write access.', 'autoDeploy=false، Hot Deployment پس‌زمینه را می‌بندد؛ deployOnStartup=true در Restart، Artifact بررسی‌شده را بارگذاری می‌کند. unpackWARs=false اجرای WAR با Read-only را ممکن می‌کند و ممکن است Performance را کم کند؛ برنامه را تست کنید. deployXML=false، META-INF/context.xml داخلی را نادیده می‌گیرد؛ Context/JNDI لازم را در Descriptor بررسی‌شده متعلق به root زیر conf/Catalina/localhost منتقل کنید. برنامه نیازمند مسیر Expanded را Operator پیش از Start با Ownership فقط خواندنی Extract کند، نه با Write دادن به webapps.')
p('Thread/connection and request-size settings are starting budgets, not universal capacity values. maxPostSize limits form parameter processing and is not a general upload-size ceiling; enforce multipart limits in application configuration and the Nginx body limit. Access logs omit query strings and credentials but paths can still contain sensitive identifiers. Keep log permissions, rotation, quotas and centralized collection under policy.', 'تنظیم Thread/Connection و اندازه Request بودجه اولیه‌اند، نه ظرفیت عمومی. maxPostSize پردازش Parameter فرم را محدود می‌کند و سقف کلی Upload نیست؛ Multipart Limit را در برنامه و Body Limit را در Nginx اعمال کنید. Access Log، Query String و Credential را ثبت نمی‌کند، اما Path می‌تواند شناسه حساس داشته باشد. Permission، Rotation، Quota و جمع‌آوری مرکزی Log را طبق Policy مدیریت کنید.')
sub('Assign controls to the correct layer', 'قرار دادن کنترل در لایه مسئول')
items([
    ('Tomcat: keep DefaultServlet readonly and directory listings disabled in conf/web.xml; leave crossContext, privileged and allowLinking disabled unless explicitly reviewed. ErrorReportValve hides container stack reports/version; omit connector server disclosure and keep xpoweredBy=false.', 'Tomcat: DefaultServlet را در conf/web.xml به‌صورت readonly و بدون Directory Listing حفظ کنید؛ crossContext، privileged و allowLinking بدون بررسی صریح فعال نشوند. ErrorReportValve گزارش Stack و نسخه Container را مخفی می‌کند؛ Server Disclosure را فعال نکنید و xpoweredBy=false بماند.'),
    ('Application: authentication/authorization, CSRF, input validation, prepared database queries, secure error pages, session fixation protection and Secure/HttpOnly/SameSite cookie policy. Select SameSite according to SSO flows and test redirects behind HTTPS.', 'Application: Authentication/Authorization، CSRF، Validation، Query آماده، Error Page امن، جلوگیری از Session Fixation و Policy کوکی Secure/HttpOnly/SameSite. SameSite را مطابق SSO انتخاب و Redirect پشت HTTPS را تست کنید.'),
    ('Nginx: TLS, public Host routing, body/time limits, chosen response headers and management-path restrictions. A WAF/rate limit is a separate tested control; the basic reverse proxy does not provide one automatically.', 'Nginx: TLS، Routing نام عمومی Host، محدودیت Body/Time، Header پاسخ و محدودیت مسیر مدیریت. WAF/Rate Limit کنترل تست‌شده جداست و Reverse Proxy ساده خودکار آن را فراهم نمی‌کند.'),
    ('OS/network: account privileges, read-only code/configuration, ingress/egress policy, SELinux/AppArmor and protected backups. Hiding a version is defense in depth, not a substitute for patches.', 'OS/Network: Privilege حساب، Code/Config فقط خواندنی، Policy ورودی/خروجی، SELinux/AppArmor و Backup محافظت‌شده. مخفی‌کردن نسخه دفاع تکمیلی است، نه جایگزین Patch.'),
])
ref('tomcat-11.0-doc/security-howto.html', 'Official Tomcat Security Considerations', 'Security Considerations رسمی Tomcat')
ref('tomcat-11.0-doc/config/http.html', 'HTTP connector options and limits', 'گزینه‌ها و محدودیت HTTP Connector')
ref('tomcat-11.0-doc/config/host.html', 'Host deployment and WAR behavior', 'Deployment در Host و رفتار WAR')
ref('tomcat-11.0-doc/config/valve.html', 'Remote IP, error reports and access logging valves', 'Valveهای Remote IP، Error Report و Access Log')
ref('tomcat-11.0-doc/config/server.html', 'Server shutdown-port configuration', 'تنظیم Port خاموشی Server')
code('sudo systemctl daemon-reload\nsudo systemctl enable --now tomcat\nsudo systemctl restart tomcat\nsudo systemctl status tomcat --no-pager\nsudo journalctl -u tomcat -n 100 --no-pager\nsudo ss -lntp | grep -E ":(8080|8005|8009)\\b"\ncurl -I --max-time 10 http://127.0.0.1:8080/')
p('At this point a 404 on / is expected because no ROOT application is deployed. It proves an HTTP response, not application health. Expect only 127.0.0.1:8080 and no 8005/8009 listener. Test manager and host-manager paths again after application deployment and through the public proxy.', 'در این مرحله پاسخ 404 برای / طبیعی است چون ROOT Deploy نشده است. این فقط پاسخ HTTP را اثبات می‌کند، نه سلامت برنامه. فقط 127.0.0.1:8080 و بدون Listener روی 8005/8009 انتظار می‌رود. مسیر manager و host-manager را پس از Deployment و از Proxy عمومی دوباره تست کنید.')

section('nginx-https', '8. Nginx reverse proxy and HTTPS', '۸. Reverse Proxy با Nginx و HTTPS')
p('Nginx and Tomcat run on the same host. Before issuing a certificate, open the chapter 9 HTTP/HTTPS rules and point the real domain to the proxy. tomcat.example.com is a documentation domain and cannot be used to obtain your certificate. On separate hosts use a private backend address, exact proxy-source firewall rules and verified TLS/mTLS between hosts where policy requires it; do not expose a plaintext public backend.', 'Nginx و Tomcat روی یک Host هستند. قبل از صدور Certificate، Ruleهای HTTP/HTTPS فصل ۹ را باز و دامنه واقعی را به Proxy متصل کنید. tomcat.example.com دامنه مستندات است و برای صدور Certificate شما قابل استفاده نیست. روی Host جدا از آدرس خصوصی Backend، Firewall با Source دقیق Proxy و در نیاز Policy از TLS/mTLS معتبر میان Hostها استفاده کنید؛ Backend عمومی Plaintext نسازید.')
sub('Ubuntu installation and HTTP bootstrap', 'نصب Ubuntu و Bootstrap با HTTP')
code(r'''sudo apt install -y nginx certbot
nginx -v
sudo cp -a /etc/nginx "/etc/nginx.before-tomcat.$(date -u +%Y%m%dT%H%M%SZ)"
sudo install -d -o root -g root -m 0755 /var/www/letsencrypt/.well-known/acme-challenge
# Dedicated new host only: remove the reviewed packaged default symlink.
if [ -L /etc/nginx/sites-enabled/default ]; then
  sudo unlink /etc/nginx/sites-enabled/default
fi
sudoedit /etc/nginx/conf.d/tomcat.conf''')
p('Install this temporary HTTP configuration first. Do not reference nonexistent certificate files. The snippets are included inside Nginx http context through conf.d/*.conf; confirm the include with nginx -T. On a shared proxy resolve conflicting server names/default listeners without disabling other sites.', 'ابتدا Configuration موقت HTTP زیر را نصب کنید. به Certificate ناموجود اشاره نکنید. Snippetها از conf.d/*.conf داخل Context مربوط به http در Nginx Include می‌شوند؛ با nginx -T بررسی کنید. روی Proxy مشترک، Conflict نام Server/Default Listener را بدون غیرفعال‌کردن سایت دیگر برطرف کنید.')
code(r'''server {
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
}''', 'nginx', 'nginx-bootstrap.conf')
code(r'''sudo nginx -t
sudo systemctl enable --now nginx
sudo systemctl reload nginx
sudo certbot certonly --webroot -w /var/www/letsencrypt \
  --cert-name tomcat.example.com -d tomcat.example.com
sudo certbot certificates''')
p('Replace the domain in every command and configuration. Certbot prompts for account details and agreement as required. Confirm the actual lineage and fullchain.pem/privkey.pem paths with certbot certificates. HTTP-01 requires public port 80 and working DNS for all advertised addresses; use automated DNS-01 if port 80 cannot be exposed. Keep private keys root-restricted and backups encrypted.', 'دامنه را در همه فرمان و Configuration جایگزین کنید. Certbot در صورت نیاز اطلاعات Account و موافقت را می‌پرسد. Lineage و مسیر واقعی fullchain.pem/privkey.pem را با certbot certificates بررسی کنید. HTTP-01 به Port عمومی 80 و DNS صحیح برای همه آدرس‌های اعلام‌شده نیاز دارد؛ اگر 80 مجاز نیست DNS-01 خودکار به‌کار ببرید. Private Key را محدود به root و Backup را رمزنگاری کنید.')
sub('Complete final HTTPS configuration', 'Configuration کامل نهایی HTTPS')
p('After certificate issuance, replace the bootstrap file with this complete /etc/nginx/conf.d/tomcat.conf. It works with Ubuntu 24.04 Nginx and uses ssl_reject_handshake (Nginx 1.19.4+). TLS ends at Nginx; HTTP over loopback stays on the same host. proxy_pass without a URI preserves /app rather than rewriting its context path.', 'پس از صدور Certificate، Bootstrap را با فایل کامل /etc/nginx/conf.d/tomcat.conf زیر جایگزین کنید. با Nginx در Ubuntu 24.04 سازگار است و ssl_reject_handshake از نسخه 1.19.4+ را استفاده می‌کند. TLS در Nginx پایان می‌یابد؛ HTTP روی Loopback در همان Host می‌ماند. proxy_pass بدون URI مسیر /app را حفظ می‌کند و Context Path را بازنویسی نمی‌کند.')
code(r'''# Included inside the http context; requires existing certificate files.
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
}''', 'nginx', 'nginx-tomcat.conf')
p('For this direct Internet edge, overwrite forwarded headers rather than append client-supplied X-Forwarded-For. Clear Forwarded so a framework cannot independently trust forged values. If a CDN/load balancer precedes Nginx, restrict origin access to it and configure set_real_ip_from for its exact published ranges with the correct real_ip_header; never trust 0.0.0.0/0. Verify $remote_addr and Tomcat logs using a forged-header test. Do not enable two independent forwarding processors in the application and container.', 'در این لبه مستقیم اینترنت، Forwarded Header را جایگزین کنید و X-Forwarded-For ارسالی Client را Append نکنید. Forwarded را پاک کنید تا Framework مستقل به مقدار جعلی اعتماد نکند. اگر CDN/Load Balancer قبل از Nginx است، Origin را به آن محدود و set_real_ip_from را برای Range دقیق منتشرشده با real_ip_header درست تنظیم کنید؛ هرگز 0.0.0.0/0 را Trust نکنید. $remote_addr و لاگ Tomcat را با Header جعلی تست کنید. پردازش Forwarding مستقل در Container و برنامه را همزمان فعال نکنید.')
p('HSTS starts at seven days here; extend it after successful HTTPS and renewal tests. Do not add includeSubDomains or preload without an organization-wide review. SAMEORIGIN may conflict with cross-site embedding; revise deliberately. Design Content-Security-Policy against the application resources, test report-only first and avoid a copied policy that breaks scripts or SSO. The proxy owns the four configured headers and hides duplicates from upstream. Deprecated X-XSS-Protection is not used.', 'HSTS اینجا از هفت روز شروع می‌شود؛ پس از تست HTTPS و تمدید آن را افزایش دهید. includeSubDomains یا preload بدون بررسی سازمانی اضافه نشود. SAMEORIGIN ممکن است با Embedding بین سایت‌ها تعارض داشته باشد؛ آگاهانه تغییر دهید. Content-Security-Policy را مطابق Resource برنامه طراحی، ابتدا Report-only تست و از Policy کپی‌شده خراب‌کننده Script/SSO دوری کنید. چهار Header تنظیم‌شده متعلق به Proxy است و نسخه تکراری Upstream مخفی می‌شود. X-XSS-Protection قدیمی استفاده نشده است.')
p('Timeouts are inactivity intervals for upstream I/O, not a complete end-to-end deadline. Align them with application and database budgets; avoid unbounded request threads. This baseline serves ordinary HTTP applications. WebSocket and SSE endpoints require reviewed Upgrade/Connection handling or buffering/timeouts at their specific locations, rather than enabling arbitrary upgrades globally.', 'Timeoutها فاصله بی‌فعالیتی I/O در Upstream هستند، نه Deadline کامل End-to-end. با بودجه برنامه و Database تطبیق دهید و Request Thread نامحدود نسازید. Baseline برای HTTP معمولی است. Endpointهای WebSocket/SSE به Upgrade/Connection یا Buffering/Timeout بررسی‌شده در Location مشخص نیاز دارند، نه Upgrade عمومی بدون محدودیت.')
code(r'''sudo nginx -t
sudo systemctl reload nginx
curl -I http://tomcat.example.com/app/
curl -I https://tomcat.example.com/app/
curl -I https://tomcat.example.com/manager/html
curl -I -H 'X-Forwarded-For: 198.51.100.123' https://tomcat.example.com/app/
openssl s_client -connect tomcat.example.com:443 -servername tomcat.example.com \
  -verify_hostname tomcat.example.com -verify_return_error </dev/null
sudo tail -n 20 /var/log/nginx/tomcat.access.log
sudo tail -n 20 /var/log/tomcat/access.*.log''')
p('Run public-domain tests after chapter 10 deployment, preferably from a separate client. Expect HTTP redirection, a valid HTTPS chain, application responses, management 404 and the actual client IP in both logs rather than the forged address. HEAD can return 405 in applications that do not implement it; retry with GET. Do not use curl -k as a TLS acceptance test.', 'تست دامنه عمومی را بعد از Deployment فصل ۱۰ و ترجیحاً از Client جدا اجرا کنید. انتظار Redirect HTTP، زنجیره HTTPS معتبر، پاسخ برنامه، 404 مدیریت و IP واقعی Client در هر دو Log به‌جای آدرس جعلی را دارید. HEAD در برنامه فاقد پشتیبانی ممکن است 405 بدهد؛ GET را تست کنید. curl -k تست پذیرش TLS نیست.')
sub('Certificate renewal and reload hook', 'تمدید Certificate و Reload Hook')
code(r'''sudo install -d -m 0755 /etc/letsencrypt/renewal-hooks/deploy
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
sudo /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh''')
p('These timer and binary paths match the Ubuntu package method. Verify the actual scheduler on another distribution or installation method; do not create duplicate renewal jobs. Monitor certificate expiry, failed renewals and the reload hook. Preserve the ACME HTTP location during redirection and future edits.', 'مسیر Binary و Timer مربوط به روش بسته Ubuntu است. روی توزیع یا روش نصب دیگر Scheduler واقعی را بررسی کنید؛ Job تمدید تکراری نسازید. انقضای Certificate، خطای تمدید و Reload Hook را پایش کنید. Location مربوط به ACME در HTTP را هنگام Redirect و تغییر بعدی حفظ کنید.')
link('https://nginx.org/en/docs/http/ngx_http_proxy_module.html', 'Nginx proxy forwarding, URI and timeout reference', 'مرجع Nginx برای Forwarding، URI و Timeout')
link('https://nginx.org/en/docs/http/ngx_http_ssl_module.html', 'Nginx TLS and default handshake behavior', 'TLS و رفتار Handshake پیش‌فرض در Nginx')
link('https://nginx.org/en/docs/http/ngx_http_headers_module.html', 'Nginx response headers and inheritance', 'Header پاسخ و Inheritance در Nginx')
link('https://nginx.org/en/docs/http/ngx_http_realip_module.html', 'Nginx trusted real-IP sources', 'Source مورد اعتماد Real IP در Nginx')
link('https://letsencrypt.org/docs/challenge-types/', 'Let’s Encrypt challenge requirements', 'پیش‌نیاز Challengeهای Let’s Encrypt')
link('https://eff-certbot.readthedocs.io/en/stable/using.html', 'Certbot webroot, renewal and hooks', 'Webroot، تمدید و Hook در Certbot')

section('firewall', '9. Firewall: expose only the edge', '۹. Firewall؛ فقط لبه را منتشر کنید')
p('Apply firewall rules before ACME issuance in chapter 8. Preserve access on the actual SSH port and test a second session before enabling/reloading rules. The commands use SSH 22 as an example. Audit existing allows, rich/direct rules, IPv6, cloud security groups and NAT; adding a deny does not repair every preexisting rule.', 'Ruleهای Firewall را قبل از ACME فصل ۸ اعمال کنید. دسترسی روی SSH واقعی را حفظ و پیش از Enable/Reload، Session دوم را تست کنید. نمونه SSH روی 22 است. Allow موجود، Rich/Direct Rule، IPv6، Cloud Security Group و NAT را بررسی کنید؛ افزودن Deny همه Ruleهای قبلی را اصلاح نمی‌کند.')
sub('Ubuntu UFW', 'UFW در Ubuntu')
code('sudo apt install -y ufw\nsudo ufw status numbered\nsudo ufw allow 22/tcp comment "SSH management - restrict source in production"\nsudo ufw allow 80/tcp comment "ACME and HTTPS redirect"\nsudo ufw allow 443/tcp comment "Nginx HTTPS"\nsudo ufw default deny incoming\nsudo ufw default allow outgoing\nsudo ufw deny 8080/tcp\nsudo ufw deny 8009/tcp\nsudo ufw deny 8005/tcp\nsudo ufw enable\nsudo ufw status verbose')
p('After establishing the real management CIDR, replace the broad SSH allowance with a source-specific rule and verify access before deleting the broad one. Remove any older public 8080 allowance shown by ufw status numbered. UFW normally allows local loopback; the backend remains reachable to local Nginx. The outgoing policy shown is a bootstrap baseline; restrict egress according to application dependencies in enterprise policy.', 'پس از مشخص‌شدن CIDR واقعی مدیریت، SSH عمومی را با Rule محدود به Source جایگزین کنید و قبل از حذف Rule عمومی دسترسی را تست کنید. Allow عمومی قدیمی 8080 را از خروجی ufw status numbered حذف کنید. UFW معمولاً Loopback را مجاز می‌داند؛ Backend برای Nginx محلی قابل دسترس می‌ماند. Policy خروجی بالا Baseline نصب است؛ در سازمان خروجی را مطابق Dependency برنامه محدود کنید.')
sub('RHEL-compatible firewalld alternative', 'گزینه جایگزین firewalld در خانواده RHEL')
code(r'''sudo dnf install -y firewalld
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
sudo firewall-cmd --zone=public --list-rich-rules''')
p('An already-absent remove-port may report NOT_ENABLED; inspect the effective result. Confirm the zone target is not ACCEPT and that no service, rich rule or trusted zone exposes the backend. The same-host design does not need an 8080 allow rule at all. Use either UFW or firewalld on a host, not both. On RHEL install Nginx through the supported distribution repository, adapt conf.d inclusion and certificate scheduling, and check SELinux before proxy validation.', 'remove-port برای Rule ناموجود ممکن است NOT_ENABLED بدهد؛ نتیجه مؤثر را بررسی کنید. Target مربوط به Zone نباید ACCEPT باشد و هیچ Service، Rich Rule یا Trusted Zone نباید Backend را باز کند. طراحی تک‌Host اصلاً Allow برای 8080 نمی‌خواهد. روی Host از UFW یا firewalld استفاده کنید، نه هر دو. در RHEL، Nginx را از مخزن پشتیبانی‌شده توزیع نصب، Include و Scheduler گواهی را تطبیق و SELinux را پیش از تست Proxy بررسی کنید.')
code('sudo ss -lntp | grep -E ":(22|80|443|8080|8005|8009)\\b"\n# From an independent external client using your real domain:\ncurl -I --connect-timeout 5 https://tomcat.example.com/\ncurl -I --connect-timeout 5 http://tomcat.example.com:8080/')
p('The external backend attempt must fail, while HTTPS reaches Nginx. Binding 127.0.0.1 is essential even when a firewall is configured; it also avoids an accidental public IPv6 listener.', 'تلاش خارجی به Backend باید Fail شود و HTTPS به Nginx برسد. Bind روی 127.0.0.1 حتی با Firewall ضروری است؛ همچنین از Listener عمومی IPv6 تصادفی جلوگیری می‌کند.')
link('https://ubuntu.com/server/docs/how-to/security/firewalls/', 'Ubuntu firewall guidance', 'راهنمای Firewall در Ubuntu')
link('https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_firewalls_and_packet_filters/using-and-configuring-firewalld_firewall-packet-filters', 'RHEL firewalld services and zones', 'Service و Zoneهای firewalld در RHEL')

section('deployment', '10. Deploy a reviewed Java WAR', '۱۰. استقرار WAR بررسی‌شده Java')
p('Build app.war in trusted CI for Java 21 and Jakarta Servlet 6.1-compatible frameworks, scan dependencies, verify the approved artifact digest/signature and transfer it to the operator’s home directory. The following assumes ./app.war is that real approved artifact. No fabricated WAR or fixed example checksum is supplied. The deployment operator uses sudo; Tomcat itself cannot deploy new code.', 'app.war را در CI مورد اعتماد برای Java 21 و Framework سازگار با Jakarta Servlet 6.1 بسازید، Dependency را اسکن، Digest/Signature تأییدشده را بررسی و فایل را به Home اپراتور منتقل کنید. در ادامه ./app.war همان Artifact واقعی و تأییدشده است. WAR ساختگی یا Checksum نمونه ثابت ارائه نشده است. Operator با sudo Deploy می‌کند؛ خود Tomcat نمی‌تواند Code جدید مستقر کند.')
code(r'''cd ~
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
curl -I https://tomcat.example.com/app/''')
p('app.war maps to /app; ROOT.war maps to /. With autoDeploy=false a running service will not notice a copied WAR until restart. Keep unpackWARs=false for this archive method. If the application requires exploded files, pre-extract the approved WAR to a clean app directory as root, set directories 0750/files 0640 root:tomcat, and deploy that directory without a competing app.war. Validate archives and file paths before privileged extraction. Never store mutable uploads under the deployment tree.', 'app.war به /app و ROOT.war به / نگاشت می‌شود. با autoDeploy=false، سرویس در حال اجرا WAR کپی‌شده را تا Restart نمی‌بیند. در روش Archive، unpackWARs=false بماند. اگر برنامه فایل Expanded می‌خواهد، WAR تأییدشده را با root در مسیر تمیز app Extract، Directory را 0750 و File را 0640 با root:tomcat تنظیم و Directory را بدون app.war رقیب Deploy کنید. پیش از Extract با Privilege، Archive و مسیر فایل را اعتبارسنجی کنید. Upload متغیر در درخت Deployment ذخیره نشود.')
p('Review startup logs for context errors and test a real application health endpoint plus authenticated user flows, data access and denied authorization. 200 on a static page does not prove database health. If the app has no /app/ index, use its documented route; do not treat every 404 as a failed service. Keep application secrets in an approved secret mechanism outside the WAR, with access restricted to the instance. External root-owned Context descriptors may be used for reviewed resource configuration.', 'لاگ Start را برای خطای Context بررسی و Health Endpoint واقعی، مسیر User احراز هویت‌شده، Data Access و Authorization ردشده را تست کنید. 200 فایل ثابت سلامت Database را اثبات نمی‌کند. اگر /app/ دارای Index نیست، Route مستند برنامه را استفاده کنید؛ هر 404 را خرابی سرویس ندانید. Secret خارج از WAR در مکانیزم تأییدشده و محدود به Instance نگهداری شود. Descriptor خارجی Context متعلق به root برای Resource بررسی‌شده قابل استفاده است.')
ref('tomcat-11.0-doc/deployer-howto.html', 'Official WAR and context deployment reference', 'مرجع رسمی استقرار WAR و Context')

section('troubleshooting', '11. Monitoring and troubleshooting', '۱۱. مانیتورینگ و عیب‌یابی')
code(r'''systemctl status tomcat --no-pager
sudo journalctl -u tomcat -n 100 --no-pager
sudo journalctl -u tomcat -f
sudo ss -lntp
curl -I http://127.0.0.1:8080/
sudo systemctl show tomcat -p MainPID -p NRestarts -p MemoryCurrent
free -h
df -h
sudo journalctl -k --since '1 hour ago'
sudo tail -n 50 /var/log/nginx/tomcat.error.log''')
p('Alert on failed/restarting services, application health/latency, 5xx rate, JVM heap/GC, native RSS, thread and file-descriptor usage, disk/log growth and TLS expiry. Protect centralized logs and configure durable journal retention according to disk budget; default journal persistence varies by distribution. Do not publish unauthenticated JMX or debug ports. Establish baselines before setting alert thresholds.', 'روی سرویس Fail/Restart، Health/Latency برنامه، نرخ 5xx، Heap/GC، RSS بومی، Thread و File Descriptor، رشد دیسک/Log و انقضای TLS Alert بگذارید. Log مرکزی را محافظت و Retention پایدار Journal را مطابق بودجه دیسک تنظیم کنید؛ Persistence پیش‌فرض میان توزیع‌ها تفاوت دارد. JMX بدون Authentication یا Debug Port منتشر نشود. قبل از تعیین Threshold، Baseline ثبت کنید.')
sub('Startup failure or restart loop', 'خطای Start یا Restart Loop')
p('Symptom: failed status or start-limit-hit. Root cause: invalid XML/JVM option, missing Java path or unreadable unit paths. Diagnose with the commands below. Resolution: correct the first startup exception, verify the resolved JDK and XML, restore a reviewed configuration, then reset the failure and restart; do not merely raise the retry limit.', 'نشانه: وضعیت failed یا start-limit-hit. علت: XML یا Option JVM نامعتبر، مسیر Java ناموجود یا مسیر Unit ناخوانا. با فرمان زیر تشخیص دهید. راه‌حل: اولین Exception را اصلاح، JDK و XML را بررسی، Configuration بررسی‌شده را بازگردانید و سپس Reset/Restart کنید؛ صرفاً Retry Limit را بالا نبرید.')
code('sudo journalctl -u tomcat -b -n 150 --no-pager\nsudo systemd-analyze verify /etc/systemd/system/tomcat.service\nsudo systemctl cat tomcat\nsudo cat /etc/tomcat/tomcat.env\nsudo systemctl reset-failed tomcat\nsudo systemctl restart tomcat')
sub('Java API or bytecode incompatibility', 'ناسازگاری API یا Bytecode جاوا')
p('Symptom: UnsupportedClassVersionError or ClassNotFoundException for javax.servlet. Root cause: artifact compiled for a newer JVM, or Java EE dependencies on a Jakarta container. Diagnose the service JVM path and context startup exception. Resolution: rebuild for the chosen Java release and compatible Jakarta dependencies, or use the vendor-supported container branch; do not add obsolete servlet API JARs into Tomcat lib to mask the mismatch.', 'نشانه: UnsupportedClassVersionError یا ClassNotFoundException برای javax.servlet. علت: Artifact برای JVM جدیدتر کامپایل شده یا Dependency از Java EE روی Container Jakarta است. مسیر JVM سرویس و Exception شروع Context را بررسی کنید. راه‌حل: برای Release انتخابی Java و Dependency سازگار Jakarta بازسازی یا Branch پشتیبانی‌شده سازنده را انتخاب کنید؛ برای پوشاندن ناسازگاری، JAR قدیمی Servlet API به lib تامکت اضافه نکنید.')
code('java -version\nsudo journalctl -u tomcat -b --no-pager | grep -E "UnsupportedClassVersion|ClassNotFound|NoClassDefFound|javax.servlet"\nsudo grep JAVA_HOME /etc/tomcat/tomcat.env')
sub('Permission denied or read-only filesystem', 'Permission Denied یا فایل‌سیستم Read-only')
p('Symptom: cannot write a log, temp file or upload. Root cause: wrong ownership, a missing parent traversal permission or a path outside ReadWritePaths. Diagnose permissions and the effective unit. Resolution: move mutable data to the documented data/work/temp/log path and grant only required ownership; add a narrow reviewed exception only if necessary. Never use chmod 777 or recursively chown the release to tomcat.', 'نشانه: عدم Write لاگ، فایل موقت یا Upload. علت: Ownership اشتباه، نبود Traverse روی Parent یا مسیر خارج از ReadWritePaths. Permission و Unit مؤثر را بررسی کنید. راه‌حل: داده متغیر را به data/work/temp/log مستند منتقل و فقط Ownership لازم بدهید؛ در ضرورت استثنای محدود و بررسی‌شده اضافه کنید. chmod 777 یا chown کل Release به tomcat نکنید.')
code('namei -l /var/lib/tomcat/data\nsudo -u tomcat test -w /var/lib/tomcat/data\nsudo systemctl show tomcat -p ReadWritePaths -p ProtectSystem -p ProtectHome\nsudo journalctl -u tomcat -n 100 --no-pager')
sub('Port already in use', 'Port از قبل اشغال است')
p('Symptom: BindException / Address already in use. Root cause: another Tomcat/JVM or service owns 8080. Diagnose the listener PID. Resolution: identify and stop the conflicting instance through its service manager, or intentionally change both connector and proxy upstream; never kill an unidentified process.', 'نشانه: BindException یا Address already in use. علت: Tomcat/JVM یا سرویس دیگر 8080 را گرفته است. PID مربوط به Listener را بررسی کنید. راه‌حل: Instance متعارض را شناسایی و از Service Manager خودش متوقف یا Connector و Upstream را هماهنگ تغییر دهید؛ Process ناشناس را Kill نکنید.')
code('sudo ss -lntp "sport = :8080"\nsystemctl list-units --type=service | grep -i tomcat\nps -eo user,pid,args | grep "[o]rg.apache.catalina.startup.Bootstrap"')
sub('HTTP 403 or 404', 'HTTP 403 یا 404')
p('Symptom: a response arrives but the route is forbidden or absent. Root cause: authorization/valve rules, a missing or failed context, wrong context path, or intentional management blocking. Diagnose direct /app/ versus proxy /app/, deployment logs and application routes. Resolution: correct the route/deployment or legitimate authorization rule; do not enable Manager or disable authentication to solve a 404. Empty / returns 404 by design in this baseline.', 'نشانه: پاسخ می‌رسد ولی Route ممنوع یا ناموجود است. علت: Authorization/Valve، Context ناموجود یا Fail، Context Path اشتباه یا مسدودسازی عمدی مدیریت. /app/ مستقیم و Proxy، Deployment Log و Route برنامه را بررسی کنید. راه‌حل: Route/Deployment یا Rule مجاز Authorization را اصلاح کنید؛ برای رفع 404، Manager فعال یا Authentication غیرفعال نشود. / خالی در این Baseline عمداً 404 می‌دهد.')
code('curl -i http://127.0.0.1:8080/app/\ncurl -i https://tomcat.example.com/app/\nsudo journalctl -u tomcat -n 150 --no-pager\nsudo ls -l /var/lib/tomcat/webapps')
sub('Nginx 502 Bad Gateway', 'خطای Nginx 502 Bad Gateway')
p('Symptom: public 502 while Nginx is running. Root cause: stopped backend, wrong upstream, connection denial or invalid upstream response. Diagnose Nginx error log, direct backend and listeners. Resolution: restore backend health and correct the upstream/mandatory-access policy; increasing proxy_read_timeout does not fix connection refused. A slow upstream normally causes 504 rather than 502.', 'نشانه: 502 عمومی با Nginx فعال. علت: Backend متوقف، Upstream اشتباه، Denial اتصال یا پاسخ نامعتبر Backend. Error Log، Backend مستقیم و Listener را بررسی کنید. راه‌حل: سلامت Backend و Upstream/Policy کنترل دسترسی را اصلاح کنید؛ افزایش proxy_read_timeout، Connection Refused را حل نمی‌کند. Upstream کند معمولاً 504 می‌دهد، نه 502.')
code('sudo tail -n 100 /var/log/nginx/tomcat.error.log\ncurl -v --max-time 10 http://127.0.0.1:8080/app/\nsudo ss -lntp "sport = :8080"\nsudo nginx -t')
sub('Heap exhaustion or kernel OOM kill', 'اتمام Heap یا OOM Kill توسط Kernel')
p('Symptom: OutOfMemoryError, long GC pauses or an abruptly killed JVM. Root cause: heap pressure/leak, excessive concurrency, native allocation or host/cgroup memory exhaustion. Diagnose kernel logs, RSS and effective heap. Resolution: fix the workload/leak, bound requests and choose measured heap/native headroom. Increase Xmx only within the host budget. An optional MemoryMax cgroup ceiling must exceed heap plus native memory and be load-tested.', 'نشانه: OutOfMemoryError، مکث طولانی GC یا Kill ناگهانی JVM. علت: فشار/Leak Heap، Concurrency زیاد، Allocation بومی یا اتمام حافظه Host/Cgroup. Kernel Log، RSS و Heap مؤثر را بررسی کنید. راه‌حل: Workload/Leak را اصلاح، Request را محدود و Heap/Native Headroom را اندازه‌گیری کنید. Xmx فقط در بودجه Host زیاد شود. سقف اختیاری MemoryMax باید از Heap به‌علاوه Native بیشتر و Load Test شده باشد.')
code(r'''sudo journalctl -k --since '1 hour ago' | grep -Ei 'oom|killed process'
sudo systemctl show tomcat -p MainPID -p MemoryCurrent -p MemoryMax
TOMCAT_PID="$(systemctl show tomcat -p MainPID --value)"
ps -o pid,rss,vsz,nlwp,args -p "$TOMCAT_PID"
# Use the exact JAVA_HOME recorded in tomcat.env for JDK tools.
sudo -u tomcat /usr/bin/jcmd "$TOMCAT_PID" GC.heap_info''')
p('On systems where jcmd is not installed as /usr/bin/jcmd, use the resolved JDK bin/jcmd. JVM attach tools can be affected by PrivateTmp and JDK attach paths; a failed attach is not proof that the JVM is unhealthy. Do not remove the sandbox globally for diagnostics. Heap dumps may contain secrets and can exhaust disk: enable -XX:+HeapDumpOnOutOfMemoryError only with a protected HeapDumpPath under data, quotas and a retention plan. Restart after changing CATALINA_OPTS.', 'اگر jcmd در /usr/bin/jcmd نصب نیست، از bin/jcmd مسیر واقعی JDK استفاده کنید. PrivateTmp و مسیر Attach در JDK می‌توانند ابزار Attach را محدود کنند؛ Attach ناموفق اثبات خرابی JVM نیست. Sandbox را برای تشخیص کلی حذف نکنید. Heap Dump ممکن است Secret داشته و دیسک را پر کند؛ -XX:+HeapDumpOnOutOfMemoryError فقط با HeapDumpPath محافظت‌شده زیر data، Quota و Retention فعال شود. بعد از تغییر CATALINA_OPTS، Restart کنید.')
sub('SELinux or AppArmor denial', 'Denial در SELinux یا AppArmor')
link('https://docs.oracle.com/en/java/javase/21/docs/specs/man/jcmd.html', 'Java 21 jcmd diagnostic commands and attach requirements', 'فرمان تشخیص jcmd در Java 21 و پیش‌نیاز Attach')
p('Symptom: permission or proxy connection failure despite correct Unix permissions. Root cause: mandatory access policy, a wrong label or an active profile denying the custom layout. Diagnose audit/kernel logs and process context. Resolution: restore correct labels and implement the smallest approved policy adjustment. Keep enforcement enabled; do not generate and install an unreviewed audit2allow policy.', 'نشانه: خطای Permission یا Proxy با وجود Unix Permission درست. علت: Mandatory Access Policy، Label اشتباه یا Profile فعال که Layout سفارشی را منع می‌کند. Audit/Kernel Log و Process Context را بررسی کنید. راه‌حل: Label درست را بازگردانید و کوچک‌ترین اصلاح Policy تأییدشده را اعمال کنید. Enforcement فعال بماند؛ خروجی audit2allow بررسی‌نشده نصب نشود.')
code(r'''# RHEL family:
getenforce
ps -eZ | grep -E 'nginx|java'
sudo ausearch -m AVC -ts recent
sudo ls -Zd /opt/tomcat /var/lib/tomcat /var/log/tomcat
getsebool httpd_can_network_connect
# Only when the denial and policy review justify outbound proxy connections:
sudo setsebool -P httpd_can_network_connect on

# Ubuntu:
sudo aa-status
sudo journalctl -k --since '1 hour ago' | grep -Ei 'apparmor|DENIED' ''')
p('The SELinux boolean allows broader outbound HTTP-service connectivity and is not a Tomcat sandbox. Review local policy and network egress controls before enabling it. A custom tar installation must have its actual Java/Tomcat SELinux domain verified; copying files does not create a confined domain automatically. Ubuntu only enforces an AppArmor profile for this process if one is actually loaded and attached.', 'Boolean مربوط به SELinux اتصال خروجی گسترده‌تر سرویس HTTP را مجاز می‌کند و Sandbox تامکت نیست. پیش از فعال‌کردن، Policy محلی و کنترل خروجی شبکه را بررسی کنید. نصب tar سفارشی باید Domain واقعی Java/Tomcat در SELinux را تأیید کند؛ Copy فایل خودکار Domain محدود نمی‌سازد. Ubuntu فقط وقتی Profile واقعاً Loaded و Attached است، AppArmor را برای این Process اعمال می‌کند.')
link('https://access.redhat.com/solutions/2980121', 'Red Hat: SELinux proxy connection denials', 'Red Hat: Denial اتصال Proxy در SELinux')
sub('systemd sandbox setup failure', 'خطای راه‌اندازی Sandbox در systemd')
p('Symptom: status=226/NAMESPACE, 200/CHDIR or 203/EXEC. Root cause: missing ReadWritePaths/mount directories, blocked home-based dependencies, unavailable namespace support or an invalid executable. Diagnose the service journal and file paths. Resolution: create/mount the intended paths with correct permissions and use the documented non-home executable; adapt only the incompatible directive in a tested host environment. A container with limited namespace support may require a different isolation design.', 'نشانه: status=226/NAMESPACE، 200/CHDIR یا 203/EXEC. علت: مسیر ReadWritePaths/Mount ناموجود، Dependency زیر Home مسدود، نبود پشتیبانی Namespace یا Executable نامعتبر. Journal و مسیرها را بررسی کنید. راه‌حل: مسیر موردنظر را با Permission درست ایجاد/Mount و Executable خارج از Home استفاده کنید؛ فقط Directive ناسازگار را در Host تست‌شده تطبیق دهید. Container با Namespace محدود ممکن است طراحی Isolation متفاوت بخواهد.')
code('sudo journalctl -u tomcat -b -n 100 --no-pager\nnamei -l /opt/tomcat/current/bin/catalina.sh\nfindmnt -T /var/lib/tomcat\nsudo systemctl cat tomcat\nsudo systemd-analyze verify /etc/systemd/system/tomcat.service')

section('upgrades', '12. Safe upgrades and rollback', '۱۲. ارتقای امن و Rollback')
p('Plan a maintenance window, record the current release/JDK/artifact hashes and test the target patch in staging with the same application and proxy. Read the migration guide, changelog and security advisories; major upgrades require API/framework compatibility checks. Confirm cluster/session serialization compatibility separately if using multiple nodes. Drain traffic and stop the service for the single-host change. A symlink switch is not a zero-downtime deployment.', 'Maintenance Window تعیین، نسخه/JDK/Hash برنامه را ثبت و Patch مقصد را با همان Application و Proxy در Staging تست کنید. Migration Guide، Changelog و Security Advisory را بخوانید؛ Major Upgrade به بررسی API/Framework نیاز دارد. در چند نود، سازگاری Cluster/Session Serialization جدا بررسی شود. برای Change تک‌Host، Traffic را Drain و سرویس را Stop کنید. تغییر Symlink، Deployment بدون Downtime نیست.')
code(r'''set -euo pipefail
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
sudo ls -la "$TOMCAT_BACKUP_DIR"''')
p('Back up external databases/state with their supported consistent backup procedure, and copy encrypted backups off-host with verified restore access. Protect certificates and private keys separately. Configuration/WAR backup does not include a remote database. Application migrations may make rollback incompatible; take a restorable data snapshot and agree on forward recovery versus rollback before changing schemas.', 'Database/State خارجی را با روش Consistent Backup خودش پشتیبان‌گیری و Backup رمزنگاری‌شده را با Restore Access تأییدشده خارج Host نگهداری کنید. Certificate/Private Key جدا حفاظت شود. Backup مربوط به Config/WAR شامل Database راه‌دور نیست. Migration برنامه ممکن است Rollback را ناسازگار کند؛ قبل از تغییر Schema، Snapshot قابل Restore و تصمیم Forward Recovery/Rollback داشته باشید.')
p('Download, verify and extract the approved new stable archive into a new /opt/tomcat/apache-tomcat-VERSION directory using the chapter 5 release steps only. Do not rerun account creation, BASE configuration copy or current symlink creation. Compare the old stock conf to the new stock conf and merge required changes into a staging copy of your local conf. Preserve the previous config and artifact as one rollback set.', 'Archive جدید Stable تأییدشده را با مراحل Release فصل ۵ فقط در مسیر جدید /opt/tomcat/apache-tomcat-VERSION دانلود، Verify و Extract کنید. ساخت Account، Copy مربوط به BASE Config یا ایجاد اولیه current را تکرار نکنید. conf پیش‌فرض قدیم و جدید را مقایسه و تغییر لازم را در Copy Staging از conf محلی Merge کنید. Config و Artifact قبلی را به‌عنوان یک Rollback Set نگه دارید.')
code(r'''# Set this to the REAL release directory you already verified and installed.
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
curl -I https://tomcat.example.com/app/''')
p('The variables in this upgrade sequence belong to the same Bash session; recover the previous path from the backup file if the session is lost. Accept the upgrade only after version, application authorization, HTTPS redirects/cookies, client IP logs, monitoring and actual data flows pass. Retain the previous release until the acceptance window closes.', 'Variableهای این روند Upgrade در یک Bash Session هستند؛ در ازدست‌رفتن Session، مسیر قبلی را از فایل Backup بازیابی کنید. Upgrade فقط پس از تأیید Version، Authorization برنامه، Redirect/Cookieهای HTTPS، IP Client در Log، Monitoring و جریان واقعی داده پذیرفته شود. Release قبلی تا پایان Acceptance Window حفظ شود.')
sub('Rollback when the application/data remain compatible', 'Rollback وقتی Application/Data سازگار مانده‌اند')
code(r'''sudo systemctl stop tomcat
sudo ln -s "$TOMCAT_PREVIOUS_RELEASE" /opt/tomcat/current.rollback
sudo mv -Tf /opt/tomcat/current.rollback /opt/tomcat/current
# Restore the REVIEWED matching old configuration/artifact set when changed.
# Restore external data only through its tested recovery plan, not blind copying.
sudo systemctl start tomcat
sudo journalctl -u tomcat -n 100 --no-pager
curl -I https://tomcat.example.com/app/''')
p('If configuration or application artifacts changed, restoring the symlink alone is not sufficient. Restore the matching backup under change control before startup. Do not overwrite the whole CATALINA_BASE with a new distribution or silently roll back a database schema. Re-run external firewall and security checks after either upgrade or rollback.', 'اگر Configuration یا Artifact برنامه تغییر کرده، برگشت Symlink به‌تنهایی کافی نیست. پیش از Start، Backup هماهنگ را تحت Change Control بازگردانید. کل CATALINA_BASE را با Distribution جدید Overwrite یا Schema دیتابیس را پنهانی Rollback نکنید. بعد از Upgrade یا Rollback، بررسی خارجی Firewall و امنیت را تکرار کنید.')
ref('upgrading.html', 'Official upgrade planning and configuration comparisons', 'برنامه‌ریزی رسمی Upgrade و مقایسه Configuration')
ref('migration-11.0.html', 'Version-specific Tomcat 11 migration requirements', 'الزامات مهاجرت وابسته به نسخه Tomcat 11')

section('best-practices', '13. Production security checklist', '۱۳. چک‌لیست امنیت Production')
items([
    ('[ ] Supported Tomcat patch, maintained Java 21 build and OS security updates are recorded; advisories have an owner and remediation deadline.', '[ ] Patch پشتیبانی‌شده Tomcat، Build نگهداری‌شده Java 21 و Update امنیت OS ثبت شده؛ Advisory مالک و مهلت رفع دارد.'),
    ('[ ] Non-root account has no sudo; binaries/configuration/deployment are root-owned and runtime writes are limited and tested.', '[ ] حساب غیر root فاقد sudo است؛ Binary/Config/Deployment متعلق به root و Write در Runtime محدود و تست‌شده است.'),
    ('[ ] systemd start, stop, reboot startup, restart limits and graceful shutdown pass; sandbox exceptions are documented.', '[ ] Start، Stop، شروع پس از Reboot، Restart Limit و Shutdown درست در systemd تست شده؛ استثنای Sandbox مستند است.'),
    ('[ ] Only Nginx is publicly reachable on 80/443; SSH is source-restricted; 8080 is loopback-only; AJP and shutdown listeners are absent.', '[ ] فقط Nginx روی 80/443 عمومی است؛ SSH به Source محدود، 8080 فقط Loopback و Listener مربوط به AJP/Shutdown غایب است.'),
    ('[ ] TLS chain, hostname, renewal scheduler, reload hook and expiry alert pass; private keys/backups are protected.', '[ ] زنجیره TLS، Hostname، Scheduler تمدید، Reload Hook و Alert انقضا موفق؛ Private Key و Backup محافظت‌شده است.'),
    ('[ ] Forwarded-header spoofing test passes and client IP logging is correct; public management paths and undeployed apps are checked.', '[ ] تست جعل Forwarded Header موفق و IP لاگ صحیح است؛ مسیر عمومی مدیریت و App مستقرنشده بررسی شده است.'),
    ('[ ] Application authentication, authorization, CSRF, cookies, error responses and request/upload limits pass; headers/CSP fit the application.', '[ ] Authentication، Authorization، CSRF، Cookie، Error Response و Request/Upload Limit برنامه تست شده؛ Header/CSP با برنامه سازگار است.'),
    ('[ ] JVM/native memory, GC, service restarts, 5xx/latency, disk/log growth and health checks are monitored with actionable alerts.', '[ ] حافظه JVM/Native، GC، Restart، 5xx/Latency، رشد دیسک/Log و Health Check با Alert عملیاتی پایش می‌شود.'),
    ('[ ] Logs exclude unnecessary secrets, have protected retention/rotation and reach centralized monitoring.', '[ ] Log از Secret غیرضروری خالی، دارای Retention/Rotation محافظت‌شده و متصل به Monitoring مرکزی است.'),
    ('[ ] WAR provenance and vulnerability scan are approved; secrets/uploads remain outside deployment; operator changes are auditable.', '[ ] منشأ WAR و Vulnerability Scan تأیید؛ Secret/Upload خارج Deployment و تغییر Operator قابل Audit است.'),
    ('[ ] Off-host encrypted backups and an isolated restore drill pass; maintenance, data compatibility and rollback are documented.', '[ ] Backup رمزنگاری‌شده خارج Host و Restore Drill ایزوله موفق؛ Maintenance، سازگاری Data و Rollback مستند است.'),
    ('[ ] SELinux/AppArmor status and effective process policy are verified; unrelated applications use separate trust boundaries.', '[ ] وضعیت SELinux/AppArmor و Policy واقعی Process بررسی؛ برنامه با اعتماد متفاوت مرز جدا دارد.'),
])

section('conclusion', 'Operational acceptance', 'پذیرش عملیاتی')
p('Hand over the effective unit, directory ownership, approved version/artifact hashes, proxy trust boundary, renewal schedule, dashboards, incident runbooks and tested restore/rollback evidence. This Windows website workspace validates the article integration; the Linux commands and application behavior still require staging execution on your target platform before production acceptance.', 'Unit مؤثر، Ownership مسیر، Version/Hash تأییدشده، مرز اعتماد Proxy، برنامه تمدید، Dashboard، Runbook رخداد و شاهد Restore/Rollback تست‌شده را تحویل دهید. Workspace ویندوزی این سایت Integration مقاله را اعتبارسنجی می‌کند؛ فرمان Linux و رفتار برنامه پیش از پذیرش Production نیازمند اجرای Staging روی پلتفرم مقصد است.')

FAQ = [
    ('Can Tomcat 11 run on Java 21?', 'Yes. Tomcat 11 requires Java 17 or later. Java 21 meets that requirement; application bytecode and dependencies must also be compatible.', 'آیا Tomcat 11 روی Java 21 اجرا می‌شود؟', 'بله؛ حداقل Java در Tomcat 11 نسخه 17 است. Java 21 سازگار است؛ Bytecode و Dependency برنامه نیز باید سازگار باشند.'),
    ('Will a Tomcat 9 WAR run unchanged on 11?', 'Do not assume it. Java EE web APIs moved from javax.* to jakarta.* and API defaults changed. Rebuild and test the vendor-supported application.', 'آیا WAR مربوط به Tomcat 9 بدون تغییر روی 11 اجرا می‌شود؟', 'فرض نکنید. API وب از javax.* به jakarta.* و بعضی Defaultها تغییر کرده‌اند. برنامه پشتیبانی‌شده سازنده را بازسازی و تست کنید.'),
    ('Why keep CATALINA_HOME separate from CATALINA_BASE?', 'HOME contains release binaries; BASE contains the instance configuration, deployments and runtime paths. Separation supports reviewed upgrades without overwriting production configuration.', 'چرا CATALINA_HOME و CATALINA_BASE جدا هستند؟', 'HOME شامل Binary نسخه و BASE شامل Configuration، Deployment و مسیر Runtime مربوط به Instance است. جداسازی Upgrade بررسی‌شده را بدون Overwrite تنظیم Production ممکن می‌کند.'),
    ('Why does the first localhost request return 404?', 'No ROOT application is installed in the empty protected webapps directory. Deploy the approved application and test its documented context path.', 'چرا درخواست اولیه localhost پاسخ 404 می‌دهد؟', 'در webapps خالی و محافظت‌شده، ROOT نصب نشده است. برنامه تأییدشده را Deploy و Context Path مستندش را تست کنید.'),
    ('How is Tomcat stopped with shutdown port -1?', 'systemd sends SIGTERM to the foreground JVM. The JVM shutdown hook performs Tomcat shutdown; shutdown.sh is not the control method for this unit.', 'با shutdown port برابر -1 چگونه Tomcat متوقف می‌شود؟', 'systemd به JVM Foreground، SIGTERM می‌دهد و Shutdown Hook جاوا، Tomcat را متوقف می‌کند؛ shutdown.sh روش کنترل این Unit نیست.'),
    ('Can the service write to webapps?', 'No. A deployment operator installs reviewed artifacts as root:tomcat. Runtime state belongs in the specifically writable directories.', 'آیا سرویس می‌تواند در webapps بنویسد؟', 'خیر؛ Operator، Artifact بررسی‌شده را با root:tomcat نصب می‌کند. State متغیر در مسیرهای مشخص قابل نوشتن است.'),
    ('Is localhost HTTP safe between Nginx and Tomcat?', 'It stays on the same host and relies on host trust. Separate-host traffic needs a private restricted path and verified encryption where policy requires it.', 'آیا HTTP محلی میان Nginx و Tomcat امن است؟', 'در همان Host باقی می‌ماند و به اعتماد Host وابسته است. ترافیک Host جدا به مسیر خصوصی محدود و در نیاز Policy به رمزنگاری معتبر نیاز دارد.'),
    ('Should Java SecurityManager be enabled?', 'No. Tomcat 11 removed support. Use least privilege, systemd restrictions and separate instances/VMs for distinct application trust boundaries.', 'آیا Java SecurityManager باید فعال شود؟', 'خیر؛ Tomcat 11 پشتیبانی را حذف کرده است. از Least Privilege، محدودیت systemd و Instance/VM جدا برای مرز اعتماد متفاوت استفاده کنید.'),
]
section('faq', 'Frequently asked questions', 'پرسش‌های متداول')
for q, a, fq, fa in FAQ:
    sub(q, fq)
    p(a, fa)

section('official-references', 'Official references, templates and related guides', 'منابع رسمی، Template و راهنماهای مرتبط')
p('Release and configuration references were checked on 9 October 2026. The official references are linked beside their relevant chapters. Recheck the live download, vulnerabilities and vendor support before rollout. Templates below contain no real credentials; adapt the domain, resolved JAVA_HOME and measured capacity before installing them on a reviewed staging host.', 'Release و مرجع Configuration در ۹ اکتبر ۲۰۲۶ بررسی شدند. لینک مرجع رسمی کنار فصل مربوط قرار دارد. پیش از Rollout، Download زنده، Vulnerability و پشتیبانی سازنده را دوباره بررسی کنید. Templateها Credential واقعی ندارند؛ دامنه، JAVA_HOME واقعی و ظرفیت اندازه‌گیری‌شده را قبل از نصب در Host بررسی‌شده Staging تطبیق دهید.')
for filename, en, fa in [
    ('tomcat.service', 'Download hardened systemd unit', 'دانلود Unit امن systemd'),
    ('server.xml', 'Download local-proxy server.xml', 'دانلود server.xml برای Proxy محلی'),
    ('logging.properties', 'Download console JULI configuration', 'دانلود Configuration کنسول JULI'),
    ('nginx-bootstrap.conf', 'Download temporary HTTP bootstrap', 'دانلود Bootstrap موقت HTTP'),
    ('nginx-tomcat.conf', 'Download final Nginx HTTPS configuration', 'دانلود Configuration نهایی Nginx HTTPS'),
]: link(f'/downloads/{SLUG}/{filename}', en, fa)
for slug, en, fa in [
    ('nginx-installation-configuration-ubuntu', 'Related: Nginx installation and configuration on Ubuntu', 'مرتبط: نصب و تنظیم Nginx در Ubuntu'),
    ('nginx-reverse-proxy-multiple-domains-single-ip-443', 'Related: Nginx SNI reverse proxy for multiple domains', 'مرتبط: Reverse Proxy و SNI برای چند دامنه در Nginx'),
    ('linux-security-account-access-management', 'Related: Linux account and access security', 'مرتبط: امنیت حساب و دسترسی Linux'),
    ('linux-security-auditor-bash', 'Related: Linux security auditing with Bash', 'مرتبط: ممیزی امنیت Linux با Bash'),
    ('enable-ssh-linux-complete-guide', 'Related: secure SSH access on Linux', 'مرتبط: دسترسی امن SSH در Linux'),
]: link('/articles/' + slug, en, fa)
parts.append('</section>')

localizations = {lang: {'title': TITLE[lang], 'meta_title': SEO_TITLE[lang], 'description': DESC[lang], 'keywords': [k for k in KEYWORDS if lang == 'fa' or not any('\u0600' <= c <= '\u06ff' for c in k)], 'faq': [[q, a] if lang == 'en' else [fq, fa] for q, a, fq, fa in FAQ]} for lang in ['en', 'fa']}
banner = '/assets/img/articles/banners/apache-tomcat-linux-security-banner.png'
url = 'https://meetaj.ir/articles/' + SLUG
schema = {'@context': 'https://schema.org', '@type': 'Article', 'headline': TITLE['en'], 'description': DESC['en'], 'inLanguage': 'en', 'datePublished': '2026-10-09T00:00:00+03:30', 'dateModified': '2026-10-09', 'image': banner, 'author': {'@type': 'Person', 'name': 'AmirHossein Jalalian'}}
nav = ''.join('<li class="article-nav-item">' + dual('a', en, fa, f'href="#{id}"') + '</li>' for id, en, fa in toc)
html = f'''<!doctype html>
<html lang="en" dir="ltr" data-article-language="en"><head>
<meta charset="UTF-8"><title>{escape(SEO_TITLE['en'])}</title>
<meta name="article:content-language" content="en">
<meta name="description" content="{escape(DESC['en'], quote=True)}">
<meta name="keywords" content="{', '.join(localizations['en']['keywords'])}">
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
{dual('span', 'Linux', 'لینوکس', 'class="article-category"')}
{dual('h1', TITLE['en'], TITLE['fa'], 'class="article-title hero-title"')}
{dual('p', DESC['en'], DESC['fa'], 'class="article-excerpt hero-subtitle"')}
<img class="article-hero-thumbnail" src="{banner}" width="1000" height="1000" alt="Apache Tomcat on a Linux server with Java, systemd and HTTPS security" data-en-alt="Apache Tomcat on a Linux server with Java, systemd and HTTPS security" data-fa-alt="Apache Tomcat روی سرور Linux با Java، سرویس systemd و امنیت HTTPS">
</section><article class="article-body" lang="en" dir="ltr">
{chr(10).join(parts)}
</article></main></body></html>
'''
SOURCE.mkdir(parents=True, exist_ok=True)
(ROOT / 'resources/legacy/articles' / f'{SLUG}.html').write_text(html, encoding='utf-8')
for locale, content in md.items():
    (SOURCE / f'article.{locale}.md').write_text('\n'.join(content), encoding='utf-8')
(SOURCE / 'metadata.json').write_text(json.dumps({'slug': SLUG, 'reviewed_at': '2026-10-09', 'tomcat_version': '11.0.26', 'java_major': 21, 'example_os': 'Ubuntu Server 24.04 LTS', 'localizations': localizations}, ensure_ascii=False, indent=2), encoding='utf-8')
downloads = ROOT / 'public/downloads' / SLUG
downloads.mkdir(parents=True, exist_ok=True)
for filename, value in configs.items():
    (SOURCE / filename).write_text(value, encoding='utf-8')
    (downloads / filename).write_text(value, encoding='utf-8')
for folder, filenames in {
    'banners': ['apache-tomcat-linux-security-banner.png'],
    'content': ['apache-tomcat-production-architecture.png', 'apache-tomcat-systemd-service.png', 'apache-tomcat-security-hardening.png'],
}.items():
    for filename in filenames:
        destination = ROOT / 'public/assets/img/articles' / folder / filename
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ROOT / 'resources/assets/img/articles' / folder / filename, destination)
print(f'Built {SLUG}: {len(toc)} authored sections, bilingual Markdown/CMS HTML, {len(configs)} configuration downloads')
