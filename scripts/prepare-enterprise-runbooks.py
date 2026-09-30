from pathlib import Path
import json

DATA = {}
def add(slug,title,priority,analysis,version,hardware,permissions,intro,scenario,flow,installation,security,monitoring,troubleshooting,recovery,practices,compatibility,sources,faq):
    DATA[slug] = dict(title=title,priority=priority,analysis=analysis,description=intro[:155],keywords=[slug.replace('-',' '),'زیرساخت سازمانی','Production','Troubleshooting'],intro=intro,scenario='سناریوی آموزشی با سازمان فرضی «آریا»: '+scenario,prerequisites=version+'\n\nسخت‌افزار و ظرفیت: '+hardware+'\n\nنرم‌افزار و دسترسی: '+permissions,architecture=flow,installation=installation,security=security,monitoring=monitoring,troubleshooting=troubleshooting,recovery=recovery,practices=practices,compatibility=compatibility,sources=sources,faq=faq)

UBUNTU='Ubuntu Server 24.04 LTS، Bash 5، systemd؛ package patch را پیش از تغییر با dpkg-query ثبت کنید.'
LINUXHW='برای مثال یک VM با ۲ vCPU، ۲ GiB RAM و ۲۰ GiB دیسک؛ این ظرفیت پیشنهادی آزمایشگاه است و sizing سرویس به بار واقعی وابسته است.'
ROS='RouterOS v7؛ patch دقیق و architecture را با /system resource print ثبت کنید. دستورات v6 را بدون بازبینی وارد v7 نکنید.'
ROSHW='روتر یا CHR آزمایشگاهی با پورت‌های مجزا؛ ظرفیت CPU، RAM و connection tracking باید زیر بار واقعی اندازه‌گیری شود.'
ROSPERM='Terminal از شبکه مدیریت، حساب دارای policy لازم برای write؛ Safe Mode با Ctrl+X، export خارج دستگاه و console مستقل پیش از تغییر.'
WIN='Windows Server 2022/2025 یا Windows 11، Windows PowerShell 5.1 x64؛ برای cmdletهای Server نقش و ماژول مربوط لازم است.'
WINHW='میزبان مجاز موجود؛ ابزارهای تشخیصی به سخت‌افزار اضافه نیاز ندارند. برای VM آزمایشگاهی ۲ vCPU، ۴ GiB RAM و ۶۰ GiB دیسک در نظر بگیرید.'
SSH='https://documentation.ubuntu.com/server/how-to/security/openssh-server/'
NETPLAN='https://netplan.readthedocs.io/en/stable/netplan-try/'
FILTER='https://help.mikrotik.com/docs/spaces/ROS/pages/48660574/Filter'

add('enable-ssh-linux-complete-guide','SSH در Production: دسترسی کلیدی، Hardening و بازیابی اتصال',1,
'موضوع: مدیریت SSH؛ هدف: دسترسی راه دور. سطح قبلی: مقدماتی. کمبود: precedence تنظیمات، آزمون نشست دوم، rollback و socket activation. نمونه root login و معرفی تغییر پورت به‌عنوان امنیت باید اصلاح شود.',
UBUNTU,LINUXHW,'openssh-server روی سرور و openssh-client روی workstation؛ sudo و console مجاز. نام opsadmin و آدرس 10.20.30.10 نمونه‌اند و باید با مقادیر سازمان جایگزین شوند.',
'قطع SSH پس از تغییر Authentication می‌تواند یک سرور سالم را از دسترس تیم عملیات خارج کند. هدف این runbook اعمال کنترل دسترسی همراه با آزمون ورود مستقل و مسیر بازیابی است.',
'تیم عملیات ۴۰ VM را از bastion در subnet مدیریت 10.20.30.0/24 اداره می‌کند. ورود عمومی اینترنتی بسته است و تغییر ابتدا روی یک canary انجام می‌شود.',
'Workstation → VPN/MFA → Bastion → Firewall مدیریت → sshd → حساب شخصی → sudo. کلید خصوصی روی workstation می‌ماند و fingerprint سرور از console تأیید می‌شود.',
'''ابتدا نسخه، listener و سرویس را ثبت کنید. نصب فقط بسته SSH را تغییر می‌دهد؛ full upgrade را به این تغییر وابسته نکنید.

```bash
sudo apt-get update
sudo apt-get install -y openssh-server
dpkg-query -W openssh-server
systemctl status ssh.service ssh.socket --no-pager
sudo ss -lntp '( sport = :22 )'
sudo cp -a /etc/ssh /root/ssh-before-change
```

روی workstation کلید passphraseدار بسازید و آن را برای حسابی که از قبل ایجاد شده نصب کنید. fingerprint را قبل از تأیید اتصال مقایسه کنید.

```bash
ssh-keygen -t ed25519 -f "$HOME/.ssh/ops_ed25519"
ssh-copy-id -i "$HOME/.ssh/ops_ed25519.pub" opsadmin@10.20.30.10
ssh -i "$HOME/.ssh/ops_ed25519" -o PasswordAuthentication=no opsadmin@10.20.30.10
```

در همان حساب روی سرور، مالکیت و permission را بررسی کنید. پس از موفقیت نشست دوم، از نشست مدیریتی دارای sudo تنظیم زیر را بنویسید.

```bash
chmod 700 "$HOME/.ssh"
chmod 600 "$HOME/.ssh/authorized_keys"
sudo tee /etc/ssh/sshd_config.d/00-enterprise.conf >/dev/null <<'EOF'
PermitRootLogin no
PubkeyAuthentication yes
PasswordAuthentication no
KbdInteractiveAuthentication no
AllowUsers opsadmin
X11Forwarding no
MaxAuthTries 3
EOF
sudo /usr/sbin/sshd -t
sudo /usr/sbin/sshd -T | grep -E 'permitrootlogin|passwordauthentication|allowusers'
sudo systemctl reload ssh.service
```

sshd -t در موفقیت خروجی ندارد؛ -T باید passwordauthentication no نشان دهد. نشست اول را باز نگه دارید و ورود با کلید و sudo در نشست سوم را امتحان کنید. AllowUsers را پیش از اجرا با فهرست حساب‌های انسانی و automation تطبیق دهید.''',
'محدودیت TCP/22 در firewall بالادستی به bastion اعمال شود. نمونه ufw allow from 10.20.30.0/24 to any port 22 proto tcp فقط روی میزبان با UFW موجود قابل اعمال است؛ firewall خاموش را از راه دور بدون طراحی فعال نکنید. تغییر پورت جای احراز هویت را نمی‌گیرد. MFA روی bastion برقرار باشد؛ اگر PAM MFA روی خود SSH دارید KbdInteractiveAuthentication no را با طراحی آن تطبیق دهید. در سیاست FIPS، پذیرش Ed25519 را بررسی و در صورت نیاز RSA با امضای SHA-2 انتخاب کنید.',
'journalctl -u ssh.service --since "15 minutes ago" منبع بررسی login است. در Zabbix آزمون TCP و آزمون ورود مجاز جدا باشند؛ بیش از ۱۰ failed login در ۵ دقیقه آستانه نمونه است. Alert باید hostname، source IP و زمان UTC داشته باشد؛ private key و token وارد لاگ نشوند.',
'''Connection refused یعنی listener یا مسیر reject؛ timeout بیشتر به routing/drop اشاره دارد. با دستورات زیر ابتدا server و client را جدا بررسی کنید.

```bash
sudo journalctl -u ssh.service -n 100 --no-pager
sudo /usr/sbin/sshd -T
ssh -vvv -i "$HOME/.ssh/ops_ed25519" opsadmin@10.20.30.10
```

Permission denied همراه با bad ownership در log: مالک HOME و .ssh را اصلاح کنید. کلید معتبر ولی ردشده: AllowUsers، Match و تنظیم مؤثر -T -C را بررسی کنید. Ubuntu ممکن است از ssh.socket استفاده کند؛ برای تغییر Port علاوه بر sshd، listener socket را طبق مستندات همان patch بررسی کنید. این runbook پورت را تغییر نمی‌دهد.''',
'''Backup از /etc/ssh حاوی host key است و باید رمزنگاری و محدود شود. برای rollback این تغییر از console یا نشست باز فقط snippet جدید را خارج کنید؛ سپس validate و reload کنید.

```bash
sudo mv /etc/ssh/sshd_config.d/00-enterprise.conf /root/00-enterprise.conf.failed
sudo /usr/sbin/sshd -t
sudo systemctl reload ssh.service
```

اگر تنظیم قبلی تغییر کرده، نسخه ثبت‌شده را از backup برگردانید. بازیابی VM نباید host key تکراری بین سرورهای متفاوت ایجاد کند.''',
'حساب‌های شخصی، کلید با passphrase، حذف کلید کارکنان جداشده و بازبینی sudo را در یک lifecycle نگه دارید. قبل از بستن آخرین نشست، login، privilege و audit را از مسیر واقعی management تأیید کنید.',
'دستورهای Ubuntu را برای RHEL عیناً اجرا نکنید؛ نام سرویس معمولاً sshd و package manager متفاوت است. توصیه قدیمی PermitRootLogin yes برای Production کنار گذاشته شده است. restart لزوماً همه نشست‌های برقرار را قطع نمی‌کند؛ رفتار daemon و systemd باید جدا بررسی شود.',
[('Canonical: OpenSSH',SSH),('OpenBSD: sshd_config','https://man.openbsd.org/sshd_config')],
[['آیا تغییر پورت SSH کافی است؟','خیر؛ کنترل مبدا، کلید، مدیریت حساب و ثبت رویداد لازم است.'],['قبل از غیرفعال‌کردن Password چه چیزی باید تست شود؟','ورود با کلید و sudo در نشست مستقل همراه با دسترسی console.']])

add('set-static-ip-ubuntu-server-netplan','Netplan در Ubuntu Server: تغییر IP با Validation و Rollback',1,
'موضوع: IP ثابت؛ هدف: پیکربندی شبکه. سطح قبلی: متوسط. کمبود: merge فایل‌ها، cloud-init، مالکیت DNS و rollback قابل اثبات.',
UBUNTU,LINUXHW,'netplan.io و systemd-networkd؛ sudo، console و رزرو IP در IPAM. ens160، 10.20.30.10، gateway و DNS زیر نمونه کامل آزمایشگاه‌اند.',
'تغییر IP از SSH می‌تواند management، DNS و مسیر backup را هم‌زمان قطع کند. Netplan باید با مالکیت روشن فایل‌ها، preflight و تأیید از یک کلاینت مستقل اعمال شود.',
'VM برنامه در VLAN 30 از DHCP به 10.20.30.10/24 منتقل می‌شود؛ IP در NetBox رزرو شده و تیم شبکه trunk و ACL را تأیید کرده است.',
'Netplan YAML → generator → systemd-networkd → NIC/VLAN → gateway. systemd-resolved مالک DNS است. فایل‌های چندگانه merge می‌شوند؛ نوشتن فایل جدید لزوماً تنظیم قبلی را حذف نمی‌کند.',
'''از console وضعیت را ثبت و کل پوشه را backup کنید.

```bash
ip -br address
ip route
resolvectl status
sudo netplan get
sudo cp -a /etc/netplan /root/netplan-before-change
```

نمونه زیر فقط برای VM تک NIC و یک فایل مالک شبکه است. فایل فعال قبلی را شناسایی کنید؛ YAML متداخل باقی نگذارید. اگر cloud-init آن را تولید می‌کند ابتدا مالکیت را از cloud به سیستم منتقل کنید یا تغییر را در منبع cloud انجام دهید.

```bash
sudo tee /etc/netplan/01-production.yaml >/dev/null <<'EOF'
network:
  version: 2
  renderer: networkd
  ethernets:
    ens160:
      dhcp4: false
      addresses: [10.20.30.10/24]
      routes:
        - to: default
          via: 10.20.30.1
      nameservers:
        addresses: [10.20.30.53, 10.20.30.54]
        search: [corp.example.com]
EOF
sudo chmod 600 /etc/netplan/01-production.yaml
sudo netplan generate
sudo netplan try --timeout 120
```

پیش از تأیید try از کلاینت دوم gateway، SSH و DNS را آزمایش کنید. انتظار: آدرس مشخص روی ens160 و فقط default route طراحی‌شده؛ generate بدون خطا. بازگشت timeout را روی محیط هدف آزمایش کنید و به آن به‌تنهایی برای بازیابی تکیه نکنید.

```bash
ip -br address show ens160
ip route get 10.20.30.53
resolvectl query corp.example.com
ping -c 3 10.20.30.1
```''',
'chmod 600 مانع خواندن اطلاعات حساس YAML توسط کاربران معمولی می‌شود. IP ثابت جای firewall نیست. DNS داخلی و route مدیریت را نگه دارید؛ پاسخ ping به معنی بازبودن همه سرویس‌ها نیست. IPv6 را با policy سازمان پیکربندی کنید و مسیرهای آن را نیز ارزیابی کنید.',
'Zabbix availability، packet loss و تغییر IP را پایش کند؛ node_exporter خطاهای interface را به Prometheus می‌دهد. تغییر route یا قطع بیش از دو poll متوالی به تیم شبکه alert شود. health check برنامه از subnet مصرف‌کننده اجرا شود.',
'''خطای YAML معمولاً از indentation یا tab است؛ generate محل را نشان می‌دهد. چند default route می‌تواند حاصل merge یا DHCP باقی‌مانده باشد.

```bash
sudo netplan --debug generate
networkctl status ens160
sudo journalctl -u systemd-networkd -n 100 --no-pager
resolvectl status ens160
```

IP درست ولی gateway unreachable: VLAN، ماسک و ARP را بررسی کنید. DNS خراب با route سالم: resolver و ACL/53 را بررسی کنید. بازگشت تنظیم بعد reboot: cloud-init یا automation هنوز مالک فایل است؛ منبع تولید را اصلاح کنید.''',
'''از console فایل جدید را خارج و YAML قبلی را از backup بازگردانید؛ فایل‌های هم‌نام backup را کورکورانه merge نکنید.

```bash
sudo mv /etc/netplan/01-production.yaml /root/01-production.yaml.failed
sudo cp -a /root/netplan-before-change/. /etc/netplan/
sudo netplan generate
sudo netplan apply
```

مدارک IPAM، VLAN و route باید خارج VM نگهداری شوند. بعد rollback از subnet مدیریت و برنامه آزمون کنید.''',
'تغییر NIC، VLAN و IP را در یک مرحله جمع نکنید. کنترل تداخل IP پیش از تغییر، console فعال و canary از شروط پذیرش‌اند.',
'gateway4 در مثال‌های قدیمی با routes جایگزین شود. renderer دسکتاپ ممکن است NetworkManager باشد؛ این نمونه برای networkd است. netplan try در برخی ساختارهای virtual link محدودیت rollback دارد.',
[('Netplan: try',NETPLAN),('Netplan: static IP','https://netplan.readthedocs.io/en/stable/using-static-ip-addresses/')],
[['آیا netplan try تضمین بازیابی است؟','خیر؛ محدودیت‌های rollback را بررسی کنید و console مستقل داشته باشید.'],['چرا تغییر بعد reboot برمی‌گردد؟','معمولاً cloud-init یا ابزار مدیریت پیکربندی هنوز فایل را تولید می‌کند.']])

add('linux-security-account-access-management','مدیریت دسترسی Linux: چرخه حساب، sudo محدود و خروج کارکنان',1,
'موضوع: user و privilege؛ هدف: امنیت دسترسی. سطح قبلی: مقدماتی. کمبود: SSH key در offboarding، نشست فعال، shell escape در sudo و حساب سرویس.',
UBUNTU,LINUXHW,'sudo و OpenSSH؛ مدیر دارای console. opsreader و نگاشت مجوز نمونه است؛ دسترسی directory سازمانی نیازمند بررسی SSSD/LDAP همان سازمان است.',
'قفل‌کردن password به‌تنهایی دسترسی کاربر جداشده را قطع نمی‌کند؛ کلید SSH، token و نشست فعال ممکن است باقی بمانند. کنترل دسترسی باید به هویت و چرخه عمر متصل شود.',
'پشتیبان شیفت باید فقط وضعیت Nginx را بخواند؛ امکان restart یا ویرایش فایل سرویس ندارد. پایان همکاری باید دسترسی bastion و همه VMها را لغو کند.',
'Identity → group → SSH admission → sudo command allowlist → audit. حساب انسانی از service account جداست؛ برای automation از credential قابل چرخش استفاده می‌شود.',
'''حساب بدون عضویت sudo ایجاد کنید؛ ابتدا وجود نام را بررسی کنید.

```bash
getent passwd opsreader
sudo adduser opsreader
id opsreader
command -v systemctl
sudo visudo -f /etc/sudoers.d/opsreader-nginx
```

این خط کامل را در فایل قرار دهید؛ مسیر systemctl باید با خروجی میزبان یکسان باشد. --no-pager مانع shell escape از pager می‌شود.

```sudoers
opsreader ALL=(root) /usr/bin/systemctl --no-pager status nginx.service
```

```bash
sudo chmod 440 /etc/sudoers.d/opsreader-nginx
sudo visudo -cf /etc/sudoers
sudo -l -U opsreader
sudo -u opsreader sudo /usr/bin/systemctl --no-pager status nginx.service
```

انتظار: syntax OK و فقط همین command مجاز. در سرویس inactive، status کد غیرصفر برمی‌گرداند و لزوماً خطای permission نیست. آزمون منفی restart نیز باید در staging نشان دهد این مجوز داده نشده است.''',
'به یک shell، editor، package manager یا wildcard گسترده sudo محدود ندهید؛ بسیاری قابلیت اجرای کد root دارند. حساب سرویس interactive login نداشته باشد. در offboarding کلیدها، عضویت directory، API token، cron، sudo و نشست‌های فعال بازبینی شوند؛ passwd -l فقط password را قفل می‌کند.',
'journalctl رویدادهای sudo و SSH را به سامانه مرکزی بفرستد. تغییر /etc/passwd، /etc/group و /etc/sudoers.d توسط auditd پایش شود. baseline تعداد حساب دارای UID صفر باید یک مورد مجاز باشد.',
'''با sudo -l تفاوت permission و خطای سرویس را جدا کنید. خطای parse با visudo -cf مشخص می‌شود. کاربری که password قفل دارد ولی login می‌کند احتمالاً از key استفاده کرده است.

```bash
sudo -l -U opsreader
sudo chage -l opsreader
loginctl list-sessions
getent group sudo
sudo journalctl _COMM=sudo -n 50 --no-pager
```

Access directory را از گروه local تفکیک کنید؛ cache هویت و نشست قدیمی ممکن است تا logout باقی بماند. نشست را فقط پس از تطبیق شناسه کاربر و ارزیابی jobهای در حال اجرا خاتمه دهید.''',
'از policy، UID/GID و مجوزها نسخه کنترل‌شده بگیرید؛ shadow و private key فقط در مخزن رمزنگاری‌شده. rollback این grant حذف snippet و validate با visudo است. حذف HOME در offboarding تا پایان retention انجام نشود؛ دسترسی را لغو و داده را طبق مالکیت سازمان تحویل دهید.',
'مجوز با command دقیق و آزمون منفی تعریف شود. رمز اضطراری به vault، حساب شخصی به directory و تغییرها به ticket متصل باشند.',
'usermod -aG sudo مجوز گسترده می‌دهد و برای کاربر read-only مناسب نیست. password lock معادل disable همه مسیرهای authentication نیست.',
[('Canonical: user management','https://documentation.ubuntu.com/server/how-to/security/user-management/'),('sudoers manual','https://www.sudo.ws/docs/man/sudoers.man/')],
[['آیا passwd -l کلید SSH را غیرفعال می‌کند؟','خیر؛ مسیر کلید و سایر credentialها باید جدا لغو شوند.'],['چرا --no-pager در sudo لازم است؟','Pager ممکن است امکان اجرای shell با privilege فرمان را فراهم کند.']])

add('nginx-installation-configuration-ubuntu','Nginx در Production: Reverse Proxy، TLS و تشخیص خطای Upstream',2,
'موضوع: نصب Nginx؛ هدف: میزبانی وب. سطح قبلی: مقدماتی. کمبود: upstream، timeout، health check و rollback. مثال TLS قبلی شامل کد ناقص است.',
UBUNTU,'نمونه ۲ vCPU و ۲ GiB RAM؛ worker و file descriptor با load test تعیین شوند. backend آماده روی 127.0.0.1:8080 لازم است.','sudo، nginx، curl و DNS برای app.example.com؛ این نام placeholder است. گواهی و کلید معتبر باید از PKI سازمان در مسیرهای زیر فراهم شوند.',
'Healthy بودن Nginx به معنی سالم‌بودن برنامه نیست. Reverse Proxy باید timeout، شناسه درخواست، TLS معتبر و آزمون مستقل upstream داشته باشد تا خطای 502 قابل پیگیری شود.',
'برنامه داخلی روی loopback اجرا می‌شود و کاربران فقط به TLS proxy دسترسی دارند. ابتدا یک VM canary و سپس load balancer تغییر می‌کند.',
'Client → TCP/443 Nginx → HTTP loopback:8080 → Application. در چند میزبان upstream به subnet خصوصی و firewall محدود منتقل شود؛ forwarding header از proxyهای ناشناس پذیرفته نشود.',
'''ابتدا backend را تست و تنظیم موجود را حفظ کنید. نصب package از repository سیستم انجام می‌شود.

```bash
sudo apt-get update
sudo apt-get install -y nginx curl
nginx -v
curl --fail --max-time 5 http://127.0.0.1:8080/health
sudo cp -a /etc/nginx /root/nginx-before-change
```

در موفقیت health باید کد 200 و payload مورد انتظار برنامه بدهد. پیش از نوشتن config گواهی chain و private key را در مسیرهای زیر نصب و مالکیت root/600 برای key اعمال کنید.

```bash
sudo tee /etc/nginx/sites-available/enterprise-app >/dev/null <<'EOF'
server {
    listen 80;
    server_name app.example.com;
    return 301 https://app.example.com$request_uri;
}
server {
    listen 443 ssl;
    server_name app.example.com;
    ssl_certificate /etc/nginx/tls/fullchain.pem;
    ssl_certificate_key /etc/nginx/tls/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Request-ID $request_id;
        proxy_connect_timeout 5s;
        proxy_read_timeout 60s;
    }
}
EOF
sudo ln -s /etc/nginx/sites-available/enterprise-app /etc/nginx/sites-enabled/enterprise-app
sudo nginx -t
sudo systemctl reload nginx
curl --fail --max-time 10 https://app.example.com/health
```

symlink فقط در نخستین نصب ساخته شود؛ اگر موجود است محتوا و مقصدش را بررسی کنید. انتظار nginx -t: syntax is ok و test is successful. در canary، DNS یا --resolve به IP همان VM اشاره کند. اگر default virtual host یا همان server_name از قبل وجود دارد تعارض را پیش از reload رفع کنید.''',
'backend روی public IP bind نشود. key گواهی خواندنی برای کاربر عمومی نباشد. firewall به مسیر مدیریت و 443 محدود شود؛ 80 فقط برای redirect یا ACME لازم است. HSTS ابتدا با max-age کوتاه و بدون includeSubDomains آزمایش شود. rate limit بر اساس مسیر و ترافیک واقعی تنظیم شود، نه عدد کپی‌شده.',
'Prometheus exporter فقط از شبکه monitoring خوانده شود. نرخ 5xx، latency upstream، handshake failure و عمر گواهی پایش شوند. آستانه نمونه 5xx بالاتر از ۱٪ برای ۵ دقیقه به baseline وابسته است. Grafana درخواست کل و درخواست موفق را کنار هم نشان دهد؛ log شامل request ID باشد.',
'''502: اتصال یا پاسخ نامعتبر upstream؛ 504: timeout؛ 403: permission یا policy. ابتدا upstream مستقیم را امتحان کنید.

```bash
curl --verbose --max-time 5 http://127.0.0.1:8080/health
sudo ss -lntp
sudo tail -n 80 /var/log/nginx/error.log
sudo journalctl -u nginx -n 50 --no-pager
sudo nginx -T
```

connect() failed با Connection refused نشان‌دهنده listener backend است. permission denied ممکن است از socket یا AppArmor باشد؛ chmod 777 راه‌حل نیست. -T ممکن است اطلاعات حساس config را نمایش دهد؛ فقط در محل امن ثبت کنید.''',
'از /etc/nginx، certificate chain و secret با کنترل دسترسی backup بگیرید؛ محتوای برنامه و database خارج از scope Nginx است. در rollback فایل enterprise-app را با نسخه قبلی جایگزین کنید، nginx -t بگیرید و reload کنید. اگر اولین استقرار است symlink جدید را خارج کنید و تنظیم قبلی را validate کنید. قطع سرویس با stop/start روش عادی rollback نیست.',
'Reload فقط پس از syntax test، آزمون health و معیار مشخص پذیرش انجام شود. تغییر global worker را با limit سرویس و benchmark هماهنگ کنید.',
'این نمونه از listen 443 ssl برای سازگاری package Ubuntu استفاده می‌کند؛ syntax HTTP/2 در شاخه‌های Nginx متفاوت است. نمونه قبلی دارای سه‌نقطه، config اجرایی نیست و فقط در بخش تاریخی حفظ شده است.',
[('Nginx: proxy module','https://nginx.org/en/docs/http/ngx_http_proxy_module.html'),('Nginx: HTTPS','https://nginx.org/en/docs/http/configuring_https_servers.html')],
[['آیا nginx -t سلامت برنامه را تأیید می‌کند؟','خیر؛ فقط config را بررسی می‌کند و health check برنامه جدا لازم است.'],['برای 504 باید timeout را زیاد کرد؟','ابتدا latency و وابستگی‌های upstream را بررسی کنید؛ افزایش timeout می‌تواند صف را بزرگ‌تر کند.']])

add('ubuntu-date-time-settings','زمان در Ubuntu Production: Chrony، پایش Offset و رفع اختلال NTP',2,
'موضوع: ساعت و timezone؛ هدف: همگام‌سازی. سطح قبلی: مقدماتی. کمبود: chrony، source selection و اثر time step روی database و auth.',
UBUNTU,LINUXHW,'sudo، chrony و دسترسی UDP/123 به NTP سازمان؛ آدرس‌های 10.20.30.53 و .54 نمونه‌اند.',
'Clock skew می‌تواند Kerberos، TLS، ترتیب log و replication را مختل کند. تنظیم timezone مشکل ساعت واقعی را حل نمی‌کند؛ source، offset و وضعیت sync باید مستقل بررسی شوند.',
'دو NTP داخلی از منابع متفاوت تغذیه می‌شوند؛ سرورهای برنامه و DBA از آن‌ها استفاده می‌کنند و timestamp لاگ مرکزی UTC است.',
'Authoritative time → NTP داخلی → chronyd client → kernel clock → برنامه. timezone فقط نمایش زمان را تغییر می‌دهد؛ تغییر clock واقعی روی workload اثر دارد.',
'''ابتدا وضعیت موجود را ثبت کنید. فقط یک daemon مالک همگام‌سازی باشد؛ نصب chrony معمولاً timesyncd را جایگزین می‌کند اما وضعیت را verify کنید.

```bash
timedatectl status
date -u --iso-8601=seconds
sudo apt-get update
sudo apt-get install -y chrony
systemctl is-active chrony systemd-timesyncd
sudo cp -a /etc/chrony /root/chrony-before-change
```

در فایل /etc/chrony/chrony.conf خطوط pool/server فعلی و sourcedirهای تولیدشده را بازبینی کنید؛ sourceهای متداخل را فقط پس از تعیین مالکیت حذف کنید. دو خط زیر نمونه source سازمان‌اند و باید جایگزین sourceهای public شوند.

```text
server 10.20.30.53 iburst
server 10.20.30.54 iburst
```

```bash
sudo chronyd -p -f /etc/chrony/chrony.conf
sudo systemctl restart chrony
chronyc sources -v
chronyc tracking
sudo timedatectl set-timezone Asia/Tehran
date -u --iso-8601=seconds
```

انتظار: یک source با ^*، Reach پس از چند poll رو به 377 و Leap status: Normal. hostname source ممکن است reverse DNS نمایش داده شود. اگر offset بزرگ است روی Production makestep اجرا نکنید؛ ابتدا اثر آن بر database و scheduling را ارزیابی کنید.''',
'کلاینت NTP نباید بی‌دلیل سرور عمومی شود. sourceهای مجاز را در egress firewall محدود کنید؛ در محیط نیازمند صحت رمزنگاری‌شده NTS را با سرور پشتیبان طراحی کنید. دو سرور هم‌وابسته تنوع منبع ندارند.',
'Offset، stratum، Reach و تغییر source را پایش کنید. نمونه هشدار: offset بالاتر از ۱۰۰ ms برای ۵ دقیقه؛ برای workload حساس آستانه دقیق‌تر تعریف شود. Zabbix/Prometheus exporter نباید صرف active بودن سرویس را sync تلقی کند.',
'''^? یعنی source هنوز قابل استفاده نیست؛ UDP، DNS و clock منبع را بررسی کنید. ^x می‌تواند source ناسازگار نشان دهد.

```bash
chronyc sources -v
chronyc sourcestats -v
chronyc tracking
sudo journalctl -u chrony -n 80 --no-pager
```

Reach صفر با daemon فعال یعنی polling موفق نیست. ساعت VM که مرتب جهش می‌کند نیاز به بررسی time sync hypervisor و یک مالک مشخص دارد. تغییر timezone برای TLS expired راه‌حل نیست.''',
'state داده کاربردی در این مقاله ندارد؛ config chrony، policy timezone و source inventory را version کنید. در rollback source قبلی را بازگردانید و tracking را کنترل کنید. پس از خاموشی طولانی قبل از شروع workload حساس، sync تأیید شود؛ جهش دستی زمان بخشی از recovery database نیست.',
'UTC در log و timezone محلی در نمایش استفاده شود. تغییر دستی ساعت و تغییر NTP یک ticket مستقل با تحلیل workload نیاز دارند.',
'timesyncd برای کلاینت ساده قابل استفاده است؛ این baseline با chrony اجرا می‌شود. دستورهای timedatectl timesync-status به timesyncd مربوط‌اند و سلامت chrony را نشان نمی‌دهند.',
[('Canonical: Chrony','https://documentation.ubuntu.com/server/how-to/networking/chrony-client/'),('Chrony: chronyc','https://chrony-project.org/doc/4.5/chronyc.html')],
[['آیا timezone اشتباه باعث TLS failure است؟','TLS بر زمان واقعی تکیه دارد؛ timezone فقط نمایش را تغییر می‌دهد.'],['آیا active بودن chrony یعنی sync برقرار است؟','خیر؛ source selection، offset و Leap status باید بررسی شوند.']])

add('linux-cli-common-commands','Runbook عیب‌یابی Linux: CPU، حافظه، دیسک، سرویس و شبکه',3,
'موضوع: دستورات CLI؛ هدف: مرجع عملیات. سطح قبلی: مقدماتی. کمبود: ترتیب تشخیص، inode، deleted-open file و ثبت شواهد قبل تغییر.',
UBUNTU,LINUXHW,'coreutils، procps، iproute2، curl؛ دستورات read-only بدون sudo و مشاهده log محافظت‌شده با sudo.',
'در رخداد Production، اجرای تصادفی restart یا حذف log می‌تواند شواهد را از بین ببرد. این runbook تشخیص را از symptom به resource و سپس وابستگی سرویس هدایت می‌کند.',
'Nginx کند شده ولی CPU پایین است. تیم ابتدا memory، disk، inode و upstream را بررسی می‌کند؛ فرضیه پیش از تغییر ثبت می‌شود.',
'Incident → زمان/اثر → CPU/RAM/IO → service log → network/DNS → dependency → تغییر محدود → validation. خروجی‌های timestampدار برای مقایسه قبل و بعد نگهداری شوند.',
'''ابزارها در سیستم موجودند؛ فقط در صورت نبود package از repository همان OS نصب شوند. ابتدا snapshot شواهد read-only بگیرید.

```bash
date -u --iso-8601=seconds
uptime
free -h
vmstat 1 5
df -hT
df -i
ps -eo pid,ppid,comm,%cpu,%mem --sort=-%cpu | head -n 15
systemctl --failed --no-pager
ip -br address
ip route
ss -lnt
```

Load بالا را نسبت به CPU count و IO wait تفسیر کنید؛ RAM کم در free بدون توجه به available نشانه بحران نیست. df -i پر بودن inode را جدا نشان می‌دهد.

```bash
systemctl status nginx --no-pager
sudo journalctl -u nginx --since '15 minutes ago' --no-pager
curl --fail --max-time 5 -I https://example.com
sudo du -xhd1 /var/log
```

example.com مقصد آزمایش عمومی است؛ در رخداد با endpoint واقعی جایگزین کنید. curl فقط status endpoint مشخص را می‌سنجد. du -x از ورود به filesystemهای دیگر جلوگیری می‌کند ولی روی دیسک پربار همچنان هزینه دارد.''',
'sudo فقط برای داده محافظت‌شده باشد. خروجی env، config یا process args ممکن است secret داشته باشد. chmod/chown را به مسیر دقیق و مالک واقعی محدود کنید؛ recursive تغییر permission در رخداد راه‌حل عمومی نیست.',
'Prometheus node_exporter برای CPU، available memory، filesystem و inode؛ Zabbix برای service و reachability. برای ظرفیت، time-to-full از trend مفیدتر از درصد ثابت است. نمودار Grafana باید latency برنامه را کنار resource نشان دهد.',
'''df پر ولی du کوچک: فایل حذف‌شده هنوز open است یا mount داده را پوشانده است. اگر lsof نصب است، خروجی زیر PID و اندازه فایل‌های deleted را مشخص می‌کند.

```bash
sudo lsof +L1
sudo ss -lntp
ip route get 1.1.1.1
resolvectl status
```

پس از شناسایی PID، روش reopen log یا reload مستند سرویس را اجرا کنید؛ kill کورکورانه نکنید. سرویس active با endpoint خراب نشان‌دهنده وابستگی یا config برنامه است. permission denied را با namei -l روی مسیر واقعی و policy AppArmor بررسی کنید.''',
'دستورهای تشخیص state جدید ایجاد نمی‌کنند. پیش از تغییر config نسخه مالکیت‌دار آن را حفظ کنید؛ داده برنامه با backup اختصاصی سرویس بازیابی می‌شود. در رخداد disk full، حذف فایل تا بررسی retention و backup انجام نشود.',
'ثبت زمان، exit code و نتیجه فرضیه در ticket؛ اول read-only و بعد تغییر قابل برگشت. reboot باید تصمیم مبتنی بر شواهد باشد.',
'این runbook برای systemd و iproute2 است؛ ifconfig/netstat قدیمی در همه توزیع‌ها نصب نیستند. /proc و ابزارها روی container ممکن است فقط namespace همان container را ببینند.',
[('Canonical: CLI reference','https://documentation.ubuntu.com/server/reference/cli/'),('systemd: journalctl','https://www.freedesktop.org/software/systemd/man/latest/journalctl.html')],
[['چرا df و du متفاوت‌اند؟','فایل حذف‌شده و باز، mount و محدوده پیمایش می‌توانند اختلاف ایجاد کنند.'],['آیا load بالا همیشه CPU bottleneck است؟','خیر؛ taskهای منتظر IO نیز می‌توانند load را افزایش دهند.']])

add('downgrade-mikrotik-routeros-firmware-safely','Downgrade امن RouterOS: کنترل نسخه، Backup و Recovery',1,
'موضوع: downgrade؛ هدف: رفع regression. سطح قبلی: متوسط. کمبود: factory-software، معیار توقف و اثبات recovery.',ROS,ROSHW,ROSPERM,
'بازگرداندن نسخه روتر می‌تواند routing، VPN و syntax پیکربندی را تغییر دهد. تصمیم downgrade باید به regression مشخص، نسخه مقصد مجاز و آزمون سرویس وابسته باشد.',
'روتر شعبه پس از patch جدید tunnel را قطع می‌کند؛ همان مسیر روی CHR آزمایش می‌شود و تغییر با console محلی در پنجره نگهداری اجرا می‌شود.',
'Inventory → backup/export → تطبیق architecture و factory version → package set → reboot → آزمون routing/VPN → تصمیم پذیرش. RouterBOOT مستقل از RouterOS است.',
'''ابتدا نسخه و محدودیت سخت‌افزار را ثبت کنید؛ CHR الزاماً RouterBOARD ندارد.

```routeros
/system resource print
/system package print
/system routerboard print
/export file=before-downgrade
/system backup save name=before-downgrade password="REPLACE_WITH_UNIQUE_BACKUP_SECRET" encryption=aes-sha256
```

placeholder رمز را با secret یکتا جایگزین کنید؛ فایل .backup و .rsc را از Files خارج دستگاه دانلود و امن نگه دارید. factory-software و minimum نسخه قابل نصب را بررسی کنید. از صفحه رسمی، تمام packageهای architecture درست با نسخه یکسان را در root Files بارگذاری کنید؛ فایل قدیمی نامرتبط باقی نگذارید.

```routeros
/file print detail
/system package downgrade
```

این دستور reboot دارد. پس از boot، package version باید دقیقاً نسخه مقصد باشد. تنها بالا آمدن WinBox معیار پذیرش نیست.

```routeros
/system resource print
/system package print
/ip route print detail
/interface print stats
/log print
```

از LAN مسیر DNS، VPN و برنامه شعبه را آزمایش کنید. اگر پس از زمان boot معمول به‌اضافه حاشیه ثبت‌شده management برنگشت، console و recovery plan فعال شود؛ reboot پی‌درپی نکنید.''',
'نسخه مقصد دارای آسیب‌پذیری شناخته‌شده انتخاب نشود. binary backup حاوی secret است؛ encrypted و خارج دستگاه نگهداری شود. SSH/WinBox فقط از management مجاز باشند و package از منبع رسمی دریافت شود.',
'Zabbix/SNMP با credential محدود برای uptime، نسخه، interface و tunnel. تغییر نسخه و boot به ticket متصل شود؛ probe برنامه از شعبه و مرکز هم‌زمان اجرا شود.',
'wrong architecture یا نسخه پایین‌تر از حد دستگاه باعث رد package می‌شود؛ resource، Files و log را تطبیق دهید. feature ناپدیدشده ممکن است به package جدا یا syntax جدید وابسته باشد. configuration export نسخه جدید را روی نسخه قدیمی به‌صورت یکجا import نکنید.',
'نسخه سالم قبلی، package کامل، binary backup، export و ابزار Netinstall مدل دستگاه خارج روتر آماده باشند. برای restore binary همان device و ترجیحاً همان نسخه مبنا را استفاده کنید. در خرابی boot، طبق مستندات Netinstall و دسترسی محلی دستگاه را بازیابی و سپس config را مرحله‌ای validate کنید.',
'RouterBOOT را صرفاً به‌دلیل downgrade سیستم‌عامل تغییر ندهید. معیار توقف: tunnel، default route یا management پس از تغییر پذیرفته نشود.',
'مسیر v7 به v6 ممکن است پشتیبانی نشود یا configuration از دست بدهد. syntax routing و package split را بر اساس مدل و نسخه بررسی کنید؛ export جای certificate/private key را نمی‌گیرد.',
[('MikroTik: Packages','https://help.mikrotik.com/docs/spaces/ROS/pages/40992872/Packages'),('MikroTik: Backup','https://help.mikrotik.com/docs/spaces/ROS/pages/40992852/Backup'),('MikroTik: Netinstall','https://help.mikrotik.com/docs/spaces/ROS/pages/24805390/Netinstall')],
[['آیا RouterBOOT باید هم‌زمان downgrade شود؟','خیر؛ تغییر bootloader نیاز مستقل و مستند می‌خواهد.'],['آیا export تمام secretها را بازیابی می‌کند؟','خیر؛ certificate، private key و برخی stateها باید جدا محافظت شوند.']])

add('mikrotik-block-port-scanners','Firewall میکروتیک: تشخیص Port Scan بدون مسدودسازی اشتباه',1,
'موضوع: psd؛ هدف: کاهش scan روی روتر. سطح قبلی: متوسط. کمبود: whitelist، false positive، ترتیب drop و IPv6.',ROS,ROSHW,ROSPERM,
'تشخیص scan وقتی مفید است که دسترسی مدیریت را قطع نکند. psd یک heuristic برای TCP است؛ default deny و محدودیت مبدا باید حفاظت اصلی روتر باشند.',
'تیم امنیت از 10.20.30.0/24 scan مجاز انجام می‌دهد. WAN شامل ether1 است؛ input از خود روتر حفاظت می‌کند و سرویس‌های LAN سیاست forward جدا دارند.',
'WAN → established/related → management allowlist → psd → temporary list → drop. این طرح مکمل ruleset موجود است و firewall کامل نیست.',
'''اول ترتیب rule و interface list را بررسی کنید. WAN و mgmt-approved باید از قبل وجود داشته باشند؛ مثال whitelist فقط subnet آزمایشگاه است.

```routeros
/interface list member print
/ip firewall filter print detail
/ip firewall connection tracking print
/ip firewall address-list add list=mgmt-approved address=10.20.30.0/24 comment="Approved management subnet"
```

در Safe Mode این ruleها را اضافه کنید؛ به‌صورت پیش‌فرض انتهای ruleset قرار می‌گیرند. با WinBox آن‌ها را بعد از پذیرش management و قبل از accept عمومی new یا drop نهایی قرار دهید.

```routeros
/ip firewall filter add chain=input in-interface-list=WAN protocol=tcp connection-state=new src-address-list=!mgmt-approved psd=21,3s,3,1 action=add-src-to-address-list address-list=port_scanners address-list-timeout=1h comment="ENT-SCAN detect"
/ip firewall filter add chain=input in-interface-list=WAN src-address-list=port_scanners action=drop comment="ENT-SCAN drop"
/ip firewall filter print stats where comment~"ENT-SCAN"
/ip firewall address-list print where list=port_scanners
```

آزمون فقط از scanner مجاز در staging باشد. انتظار: افزایش counter detection و drop برای TCP scan، بدون blockشدن subnet مدیریت. log نامحدود روی هر packet اضافه نکنید.''',
'whitelist شامل IP قابل جعل از WAN نباشد؛ anti-spoofing در edge مهم است. عدم تشخیص psd مجوز عبور ترافیک نیست. IPv6 chain مستقل دارد؛ حفاظت IPv4 به IPv6 منتقل نمی‌شود. SNMP و مدیریت عمومی اینترنتی بسته باشند.',
'CPU، connection count، drop counter و اندازه address list پایش شود. Alert افزایش ناگهانی scan همراه با load لازم است؛ یک scan منفرد نباید pager ایجاد کند. log rate محدود و به syslog امن شبکه مدیریت منتقل شود.',
'counter صفر: ترتیب rules، WAN membership یا ترافیک forward را بررسی کنید. false positive: رفتار scanner/NAT اشتراکی و timeout را تحلیل کنید؛ آستانه را بی‌دلیل پایین نیاورید. slow scan یا UDP ممکن است psd را دور بزند؛ این محدودیت طراحی است.',
'''Backup تنظیمات firewall و export خارج دستگاه. برای rollback فقط ruleهای این تغییر را disable کنید، نه همه firewall را.

```routeros
/ip firewall filter disable [find where comment~"ENT-SCAN"]
/ip firewall address-list print where list=port_scanners
```

حذف یک entry باید پس از تطبیق IP با رخداد انجام شود؛ پاک‌کردن کل list بدون تحلیل، شواهد را از بین می‌برد.''',
'Input و forward و IPv6 را جدا تست کنید؛ established قبل از heuristic باشد. آستانه و timeout بر اساس false positive مستند شوند.',
'psd تشخیص همه حمله‌ها یا IDS نیست. fasttrack عمدتاً ترافیک forward established را تحت تأثیر قرار می‌دهد؛ از نتیجه input برای سلامت forward نتیجه نگیرید.',
[('MikroTik: Filter',FILTER),('MikroTik: Matchers','https://help.mikrotik.com/docs/spaces/ROS/pages/250708064/Common+Firewall+Matchers+and+Actions')],
[['آیا input از سرورهای LAN حفاظت می‌کند؟','خیر؛ ترافیک عبوری به سیاست forward نیاز دارد.'],['آیا psd همه scanها را کشف می‌کند؟','خیر؛ heuristic محدود است و جای default deny و IDS را نمی‌گیرد.']])

add('mikrotik-block-website','کنترل دسترسی وب در MikroTik: DNS، TLS SNI و محدودیت HTTPS',1,
'موضوع: website blocking؛ هدف: policy خروجی. سطح قبلی: متناقض. کمبود: ECH/QUIC/DoH، root domain، FastTrack و scope DNS. wildcard address-list و Layer7 عمومی معتبر نیستند.',ROS+' برای type=NXDOMAIN و match-subdomain، پشتیبانی patch هدف را با CLI help تأیید کنید.',ROSHW,ROSPERM,
'مسدودسازی وب با IP یا جست‌وجوی رشته، روی CDN و TLS مدرن نتیجه قابل اتکا نمی‌دهد. کنترل باید محدودیت visibility و رفتار کلاینت مدیریت‌شده را روشن کند.',
'کلاینت‌های VLAN کاربران از resolver داخلی استفاده می‌کنند؛ policy آزمایش برای example.com است. روتر فقط بخشی از کنترل را انجام می‌دهد و endpoint policy مسئول DoH/VPN است.',
'Managed client → resolver سازمانی → DNS policy؛ TCP TLS قابل مشاهده → tls-host. QUIC، ECH و VPN می‌توانند نام مقصد را از matcher روتر پنهان کنند؛ نیاز به secure web gateway ممکن است باقی بماند.',
'''ابتدا DNS و ترتیب firewall را ثبت کنید؛ LAN و WAN interface list باید درست باشند. resolver روتر فقط در صورتی فعال شود که input UDP/TCP 53 از LAN مجاز و از WAN بسته باشد؛ این قواعد باید قبل از drop نهایی قرار گیرند.

```routeros
/ip dns print
/ip firewall filter print detail
/ip dns static add name=example.com match-subdomain=yes type=NXDOMAIN comment="ENT-WEB DNS policy"
/ip firewall filter add chain=forward in-interface-list=LAN out-interface-list=WAN protocol=tcp dst-port=443 tls-host=example.com action=drop comment="ENT-WEB root SNI"
/ip firewall filter add chain=forward in-interface-list=LAN out-interface-list=WAN protocol=tcp dst-port=443 tls-host=*.example.com action=drop comment="ENT-WEB subdomain SNI"
```

resolver فعال موجود را استفاده کنید؛ allow-remote-requests را بدون firewall درست فعال نکنید. SNI rules باید پیش از FastTrack و accept established مربوط قرار گیرند؛ اتصال‌های FastTrack موجود نیاز به آزمون با اتصال تازه دارند.

```routeros
/ip firewall filter print stats where comment~"ENT-WEB"
/ip dns static print detail where comment~"ENT-WEB"
```

روی کلاینت Linux مدیریت‌شده، DNS و TCP TLS را جدا آزمایش کنید.

```bash
dig @10.20.30.1 example.com
curl --http1.1 --connect-timeout 5 https://example.com
```

10.20.30.1 placeholder IP resolver روتر است. انتظار DNS: NXDOMAIN. در تست SNI از مسیر DNS مجاز برای resolve استفاده شود تا رد TCP قابل اندازه‌گیری باشد؛ failure DNS به‌تنهایی اثبات matcher نیست.''',
'DNS recursion روی WAN باز نشود. برای اجبار DNS، فقط subnet کاربران و resolver سازمانی را target کنید؛ سرویس‌های دیتاسنتر و VPN استثنای مستند دارند. IP اشتراکی CDN را کورکورانه block نکنید. HTTPS content رمزنگاری شده و generic Layer7 قابل اتکا نیست.',
'counter policy، حجم UDP/443، CPU و complaint کاربران ثبت شوند. مسدودسازی QUIC بدون تحلیل fallback برنامه اجرا نشود. نمودار قبل/بعد، bypass مجاز و false positive را نشان دهد.',
'سایت باز می‌شود: root و subdomain، DNS cache، DoH، ECH، QUIC، proxy و مسیر IPv6 را جدا بررسی کنید. tls-host با ClientHello تکه‌شده ممکن است match نکند. IP rule می‌تواند سرویس بی‌ارتباط CDN را قطع کند؛ ownership و DNS تغییرپذیر را بررسی کنید.',
'''export policy را نگه دارید. rollback فقط rules و DNS entry همین تغییر را غیرفعال کند.

```routeros
/ip firewall filter disable [find where comment~"ENT-WEB"]
/ip dns static disable [find where comment~"ENT-WEB"]
```

cache کلاینت و resolver را طبق سیاست پاک و دسترسی برنامه را دوباره آزمایش کنید؛ تنظیم کلی firewall تغییر نکند.''',
'برای الزام سراسری از endpoint management و secure web gateway استفاده کنید. policy همراه exception، مالک کسب‌وکار و تاریخ بازبینی باشد.',
'address-list با *.example.com یک wildcard DNS عمومی نیست. نسخه تاریخی content/Layer7 برای HTTPS جدید کاربرد اجرایی ندارد. NXDOMAIN و matcherها را برای patch دقیق تأیید کنید.',
[('MikroTik: DNS','https://help.mikrotik.com/docs/spaces/ROS/pages/37748767/DNS'),('MikroTik: Matchers','https://help.mikrotik.com/docs/spaces/ROS/pages/250708064/Common+Firewall+Matchers+and+Actions')],
[['آیا tls-host همه HTTPS را مسدود می‌کند؟','خیر؛ ECH، QUIC، fragmentation و VPN محدودیت ایجاد می‌کنند.'],['آیا یک IP مقصد برابر یک سایت است؟','خیر؛ CDN می‌تواند یک IP را بین چندین سرویس مشترک کند.']])

add('mikrotik-openvpn-setup-v7','OpenVPN در RouterOS v7: PKI، دسترسی محدود و تشخیص TLS',1,
'موضوع: VPN؛ هدف: اتصال سازمانی. سطح قبلی: متوسط با خطا. کمبود: mismatch cipher، password ضعیف، route نادرست و مجوز forward. syntax server از 7.17 تغییر کرده است.',
'RouterOS 7.17 یا جدیدتر با ساختار named server؛ OpenVPN Community 2.6 روی Linux/Windows. patch انتخاب‌شده در staging تثبیت شود.',ROSHW,
ROSPERM+' PKI سازمانی با CA، گواهی vpn.example.com دارای SAN و client اختصاصی؛ ساعت sync و TCP/1194. این runbook PKI موجود را استفاده می‌کند.',
'بالا آمدن tunnel بدون کنترل forward، شبکه دیتاسنتر را بیش از نیاز در اختیار کلاینت قرار می‌دهد. VPN باید احراز هویت، مسیر برگشت و مجوز سرویس را یکجا validate کند.',
'پشتیبان فقط به SSH سرور 10.20.30.10 دسترسی دارد؛ pool مجزای 10.250.0.0/24، split tunnel و گواهی اختصاصی استفاده می‌شود.',
'Client certificate + PPP user → TCP/1194 → ovpn server → pool → forward ACL → server. LAN باید route برگشت 10.250.0.0/24 به روتر داشته باشد؛ NAT جای route درست نیست.',
'''گواهی‌ها را با نام‌های ca.crt و server.p12 از PKI به Files بارگذاری کنید. private key از طریق فایل passwordدار وارد شود؛ CA private key روی edge نگهداری نمی‌شود.

```routeros
/system clock print
/certificate import file-name=ca.crt passphrase=""
/certificate import file-name=server.p12 passphrase="REPLACE_WITH_P12_SECRET"
/certificate print detail
```

نام certificate واردشده را با CLI بررسی کنید و در دستور بعد REPLACE_SERVER_CERT را با نام واقعی جایگزین کنید. certificate باید private key و chain معتبر داشته باشد.

```routeros
/ip pool add name=ent-ovpn-pool ranges=10.250.0.10-10.250.0.100
/ppp profile add name=ent-ovpn local-address=10.250.0.1 remote-address=ent-ovpn-pool
/ppp secret add name=opsvpn service=ovpn password="REPLACE_WITH_UNIQUE_USER_SECRET" profile=ent-ovpn
/interface ovpn-server server add name=ent-ovpn-server disabled=no protocol=tcp port=1194 mode=ip certificate=REPLACE_SERVER_CERT require-client-certificate=yes auth=sha256 cipher=aes256-cbc tls-version=only-1.2 default-profile=ent-ovpn
/ip firewall filter add chain=input in-interface-list=WAN protocol=tcp dst-port=1194 action=accept comment="ENT-OVPN listener"
/ip firewall filter add chain=forward src-address=10.250.0.0/24 dst-address=10.20.30.10 protocol=tcp dst-port=22 action=accept comment="ENT-OVPN SSH grant"
/ip firewall filter add chain=forward src-address=10.250.0.0/24 action=drop comment="ENT-OVPN deny other"
```

ruleها قبل از drop نهایی و پیش از accept عمومی LAN قرار گیرند؛ established/related موجود برای پاسخ باید درست باشد. نمونه روی firewall آماده اجرا می‌شود، نه روتر خالی بدون default deny.

فایل client.ovpn کامل زیر کنار ca.crt، client.crt و client.key قرار گیرد. vpn.example.com placeholder است؛ گواهی باید همین نام واقعی را پوشش دهد. کلید client passphraseدار باشد.

```openvpn
client
dev tun
proto tcp-client
remote vpn.example.com 1194
nobind
persist-key
persist-tun
ca ca.crt
cert client.crt
key client.key
auth-user-pass
remote-cert-tls server
verify-x509-name vpn.example.com name
tls-version-min 1.2
auth SHA256
cipher AES-256-CBC
data-ciphers AES-256-CBC
route 10.20.30.10 255.255.255.255
verb 3
```

اجرای sudo openvpn --config client.ovpn روی Linux باید Initialization Sequence Completed نشان دهد. login و key password به‌صورت تعاملی وارد شوند؛ در فایل ذخیره نشوند. SSH مجاز و دسترسی به مقصد غیرمجاز هر دو تست شوند.''',
'compression خاموش باشد. client مشترک استفاده نشود؛ certificate و PPP credential هر دو در offboarding لغو شوند. require-client-certificate هویت فرد را به username به‌تنهایی bind نمی‌کند؛ نگاشت credential و certificate را در policy و آزمون منفی بررسی کنید. MFA نیازمند طرح سازگار RADIUS/identity است و در این نمونه پیاده‌سازی نشده است.',
'PPP active، pool utilization، certificate expiry، failed auth و log tunnel پایش شوند. Alert قطع اتصال کاربر عادی با قطع کل سرویس متفاوت باشد؛ certificate زیر ۳۰ روز هشدار نمونه است.',
'TLS verify failed: زمان، CA، SAN و key usage. AUTH_FAILED: PPP service، password یا policy. tunnel بالا ولی SSH timeout: route برگشت و forward counter. cipher mismatch: دقیقاً CBC/SHA256 این baseline را در هر دو طرف تطبیق دهید؛ GCM را بدون طراحی سازگار اضافه نکنید.',
'export بدون secrets، backup رمزنگاری‌شده و نسخه PKI در vault نگهداری شوند. گواهی client قابل revoke باشد و روش اعمال CRL روی patch هدف تست شود. در rollback named server و ruleهای ENT-OVPN را disable و routeهای اختصاصی کلاینت را حذف کنید؛ restore کل روتر برای یک rule لازم نیست.',
'اول route، سپس auth، سپس ACL مثبت و منفی را تأیید کنید. هر client یک گواهی و حساب دارد؛ cipher انتخابی compatibility baseline است و ارزیابی cryptographic policy سازمان لازم است.',
'قبل از 7.17 ساختار server singleton متفاوت است. UDP و cipherهای قابل پشتیبانی به نسخه وابسته‌اند؛ مثال قدیمی SHA1 همراه AES-GCM ناسازگار بود. gateway برابر IP خود روتر نباید default route باشد.',
[('MikroTik: OpenVPN','https://help.mikrotik.com/docs/spaces/ROS/pages/2031655/OpenVPN'),('OpenVPN 2.6 manual','https://openvpn.net/community-resources/reference-manual-for-openvpn-2-6/')],
[['چرا tunnel برقرار است ولی سرور باز نمی‌شود؟','مسیر برگشت و forward ACL مستقل از authentication هستند و باید بررسی شوند.'],['آیا تنظیم v7 روی همه patchها یکسان است؟','خیر؛ از 7.17 ساختار named server تغییر کرده و باید patch هدف تثبیت شود.']])

add('mikrotik-unequal-dual-wan-load-balancing-ecmp','Dual-WAN وزنی در RouterOS v7: PCC، مسیر برگشت و Failover',1,
'موضوع: WAN نابرابر؛ هدف: تقسیم بار ۲ به ۱. سطح قبلی: متوسط با syntax v6. کمبود: table v7، تضمین نادرست وزن ECMP، FastTrack و failover session.',ROS,ROSHW,
ROSPERM+' نمونه روی lab بدون routing policy و DNAT موجود؛ ether1=WAN1، ether2=WAN2، ether3=LAN با IP و gatewayهای زیر از قبل تنظیم شوند.',
'دو لینک ۱۰۰ و ۵۰ مگابیتی با یک default route تکرارشده، توزیع ظرفیت قابل تضمین ندارند. PCC اتصال‌ها را وزن‌دهی می‌کند؛ failover باید در هر routing table و مسیر پاسخ آزموده شود.',
'LAN برابر 10.10.10.0/24 است. WAN1:192.168.30.2/30 با GW .1 و WAN2:172.30.30.2/30 با GW .1. این آدرس‌ها فقط lab هستند؛ روی Production با نقشه IPAM جایگزین شوند.',
'LAN new connection → PCC buckets 0/1→WAN1 و 2→WAN2 → connection mark → routing table → srcnat. پاسخ ورودی از هر WAN باید همان WAN را انتخاب کند. وزن تعداد اتصال است، نه تضمین نسبت بایت یا جمع سرعت یک دانلود.',
'''پیش از تغییر export، route و ruleهای FastTrack را بررسی کنید. در canary FastTrack را برای اتصال‌های marked مستثنا کنید؛ در lab می‌توان rule fasttrack موجود را موقت disable کرد.

```routeros
/ip address print
/ip route print detail
/ip firewall mangle print detail
/ip firewall filter print where action=fasttrack-connection
/routing table add name=ent-wan1 fib
/routing table add name=ent-wan2 fib
/ip firewall address-list add list=ent-local address=10.10.10.0/24
/ip firewall address-list add list=ent-local address=192.168.30.0/30
/ip firewall address-list add list=ent-local address=172.30.30.0/30
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-list=ent-local action=accept comment="ENT-PCC local bypass"
/ip firewall mangle add chain=prerouting in-interface=ether1 connection-state=new connection-mark=no-mark action=mark-connection new-connection-mark=ent-c1 comment="ENT-PCC incoming WAN1"
/ip firewall mangle add chain=prerouting in-interface=ether2 connection-state=new connection-mark=no-mark action=mark-connection new-connection-mark=ent-c2 comment="ENT-PCC incoming WAN2"
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-type=!local connection-state=new connection-mark=no-mark per-connection-classifier=both-addresses-and-ports:3/0 action=mark-connection new-connection-mark=ent-c1 comment="ENT-PCC bucket0"
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-type=!local connection-state=new connection-mark=no-mark per-connection-classifier=both-addresses-and-ports:3/1 action=mark-connection new-connection-mark=ent-c1 comment="ENT-PCC bucket1"
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-type=!local connection-state=new connection-mark=no-mark per-connection-classifier=both-addresses-and-ports:3/2 action=mark-connection new-connection-mark=ent-c2 comment="ENT-PCC bucket2"
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-type=!local connection-mark=ent-c1 action=mark-routing new-routing-mark=ent-wan1 passthrough=no comment="ENT-PCC route1"
/ip firewall mangle add chain=prerouting in-interface=ether3 dst-address-type=!local connection-mark=ent-c2 action=mark-routing new-routing-mark=ent-wan2 passthrough=no comment="ENT-PCC route2"
/ip firewall mangle add chain=output connection-mark=ent-c1 action=mark-routing new-routing-mark=ent-wan1 passthrough=no comment="ENT-PCC reply1"
/ip firewall mangle add chain=output connection-mark=ent-c2 action=mark-routing new-routing-mark=ent-wan2 passthrough=no comment="ENT-PCC reply2"
/ip route add dst-address=0.0.0.0/0 gateway=192.168.30.1 routing-table=main distance=1 check-gateway=ping comment="ENT-PCC main1"
/ip route add dst-address=0.0.0.0/0 gateway=172.30.30.1 routing-table=main distance=2 check-gateway=ping comment="ENT-PCC main2"
/ip route add dst-address=0.0.0.0/0 gateway=192.168.30.1@main routing-table=ent-wan1 distance=1 check-gateway=ping comment="ENT-PCC table1 primary"
/ip route add dst-address=0.0.0.0/0 gateway=172.30.30.1@main routing-table=ent-wan1 distance=2 check-gateway=ping comment="ENT-PCC table1 backup"
/ip route add dst-address=0.0.0.0/0 gateway=172.30.30.1@main routing-table=ent-wan2 distance=1 check-gateway=ping comment="ENT-PCC table2 primary"
/ip route add dst-address=0.0.0.0/0 gateway=192.168.30.1@main routing-table=ent-wan2 distance=2 check-gateway=ping comment="ENT-PCC table2 backup"
/ip firewall nat add chain=srcnat src-address=10.10.10.0/24 out-interface=ether1 action=masquerade comment="ENT-PCC nat1"
/ip firewall nat add chain=srcnat src-address=10.10.10.0/24 out-interface=ether2 action=masquerade comment="ENT-PCC nat2"
```

rules بالا روی lab پاک با firewall از قبل امن اجرا شوند؛ در سایت موجود با ruleهای قبلی merge و duplicate route/NAT حذف شود. subnetهای VPN/DC به ent-local افزوده شوند. ابتدا چند صد اتصال تازه بسازید؛ counters bucket0+1 باید تقریباً دو برابر bucket2 باشد، نه الزاماً bytes. WAN1 را در lab قطع و route فعال هر table و برقراری اتصال تازه را بررسی کنید.''',
'NAT جای firewall نیست. input WAN default deny و forward فقط LAN مجاز باشد. مسیر داخلی نباید به policy WAN فرستاده شود. مدیریت روتر از LAN/console برقرار بماند؛ DNAT موجود نیازمند آزمون symmetry مستقل است.',
'برای هر WAN availability، packet loss، latency و utilization مستقل ثبت شود. check-gateway=ping فقط gateway را می‌سنجد؛ قطع بالادست ISP با gateway زنده را کشف نمی‌کند. recursive probe یا کنترل چند مقصد مستقل نیاز طراحی و آزمون جدا دارد.',
'توزیع دیده نمی‌شود: اتصال‌های قدیمی و FastTrack را بررسی کنید. پاسخ از WAN غلط: connection-mark و output/prerouting counters. route inactive: next-hop در main و gateway scope. banking session حساس به public IP: مقصد را pin یا classifier both-addresses انتخاب کنید و وزن را دوباره بسنجید.',
'export قبل تغییر حفظ شود. rollback به ترتیب disable mangleهای ENT-PCC، restore route/NAT قبلی و disable route/NAT جدید است؛ tableها فقط پس از نبود reference حذف شوند. connection state را کورکورانه flush نکنید. با تغییر public IP، نشست TCP ممکن است قطع شود و reconnect لازم است؛ failover حفظ همه sessionها را تضمین نمی‌کند.',
'ECMP equal-cost و PCC weighted را تفکیک کنید؛ یک flow سرعت دو لینک را جمع نمی‌کند. gateway failure و upstream failure دو آزمون جدا باشند.',
'routing-mark در دستور /ip route نسخه v6 با routing-table v7 یکسان نیست. تکرار gateway برای وزن‌دهی در نسخه جدید مبنای قابل اتکای طراحی نیست؛ مقاله با URL تاریخی حفظ و طراحی به PCC ارتقا یافته است.',
[('MikroTik: PCC','https://help.mikrotik.com/docs/spaces/ROS/pages/152600617/Per+connection+classifier'),('MikroTik: Policy routing','https://help.mikrotik.com/docs/spaces/ROS/pages/59965508/Policy+Routing')],
[['آیا وزن ۲ به ۱ یعنی پهنای‌باند دقیق ۲ به ۱؟','خیر؛ وزن بر اتصال‌ها اعمال می‌شود و اندازه flowها متفاوت است.'],['آیا failover نشست TCP را حفظ می‌کند؟','با تغییر public IP ممکن است نشست قطع شود و اتصال دوباره لازم باشد.']])
add('windows-cmd-common-network-commands','عیب‌یابی شبکه Windows: DNS، TCP، SMB و ثبت شواهد رخداد',3,
'موضوع: CMD network؛ هدف: diagnosis. سطح قبلی: مقدماتی. کمبود: خروجی قبل flush، PID معتبر، محدودیت ICMP و روش امن credential. نمونه key=clear اطلاعات حساس افشا می‌کند.',WIN,WINHW,
'CMD و PowerShell داخلی؛ خواندن وضعیت معمولاً بدون Administrator. route/firewall mutation نیازمند elevation و change مجاز است.',
'ping موفق، DNS درست و TCP قابل اتصال سه نتیجه متفاوت‌اند. برای رخداد شبکه باید لایه خراب را مشخص کرد؛ release/renew یا reset stack نباید اولین اقدام روی سرور باشد.',
'سرور برنامه به files01.corp.example.com روی SMB وصل نمی‌شود؛ DNS خصوصی و TCP/445 از subnet برنامه به file server بررسی می‌شود.',
'Client → DNS → selected route/interface → firewall → TCP listener → SMB authentication → share/NTFS. مرحله‌ای که fail می‌شود تعیین می‌کند تغییر متعلق به کدام تیم است.',
'''ابتدا در CMD شواهد بگیرید؛ دامنه زیر placeholder داخلی است.

```cmd
ipconfig /all
route print
arp -a
nslookup files01.corp.example.com
tracert -d 10.20.30.20
netstat -ano
```

در PowerShell آزمون TCP و route انجام دهید. انتظار TcpTestSucceeded: True برای ارتباط TCP؛ این خروجی permission SMB را تأیید نمی‌کند.

```powershell
$Target = 'files01.corp.example.com'
Resolve-DnsName $Target -Type A
Test-NetConnection $Target -Port 445 -InformationLevel Detailed
Get-NetIPConfiguration
Get-SmbConnection
```

برای نگهداری خروجی پوشه یکتا بسازید؛ commandهای خارجی exit code مستقل دارند.

```powershell
$ReportPath = Join-Path $env:TEMP ('Network-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $ReportPath -ErrorAction Stop | Out-Null
ipconfig /all | Out-File (Join-Path $ReportPath 'ipconfig.txt') -Encoding utf8
route print | Out-File (Join-Path $ReportPath 'routes.txt') -Encoding utf8
Test-NetConnection $Target -Port 445 | Export-Clixml (Join-Path $ReportPath 'tcp445.xml')
```

Flush DNS فقط وقتی stale cache با پاسخ authoritative مقایسه شده باشد انجام شود. سپس همان query و برنامه دوباره آزمایش شود.''',
'Wi-Fi export با key=clear secret را آشکار می‌کند و در بسته support استفاده نشود. net use از credential plaintext در command line استفاده نکند؛ Kerberos با hostname و حساب جاری ترجیح دارد. firewall را برای رفع مشکل خاموش نکنید.',
'Zabbix availability و TCP service را جدا بسنجد. برای SMB latency، reconnect و error log ثبت شود. رویداد DNS و network change با زمان incident هم‌راستا شود؛ خروجی interface حساس فقط در ticket محدود قرار گیرد.',
'''DNS timeout ولی IP قابل دسترسی: resolver/53 یا suffix. TCP false با ping true: listener یا firewall/ACL. SMB access denied با TCP true: identity و share/NTFS، نه routing.

```powershell
Get-NetTCPConnection -State Listen | Select-Object LocalAddress,LocalPort,OwningProcess
$Listener = Get-NetTCPConnection -LocalPort 445 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if ($null -ne $Listener) {
    Get-Process -Id $Listener.OwningProcess
} else {
    Write-Warning 'No TCP/445 listener found on this host'
}
Get-WinEvent -LogName 'Microsoft-Windows-SMBClient/Connectivity' -MaxEvents 20 -ErrorAction SilentlyContinue
```

بررسی listener روی file server اجرا می‌شود؛ اگر listener نیست query خالی است و اسکریپت هشدار می‌دهد. tracert/pathping با hop بی‌پاسخ به‌تنهایی packet loss برنامه را اثبات نمی‌کنند؛ برخی روترها ICMP را rate limit می‌کنند.''',
'تشخیص state ندارد؛ route/firewall snapshot قبل تغییر و rollback اختصاصی rule لازم است. ipconfig /release و netsh int ip reset می‌توانند نشست مدیریت را قطع کنند؛ فقط با console و برنامه برگشت اجرا شوند. فایل شواهد مطابق retention امن نگهداری شود.',
'Hostname و IP را جدا تست کنید؛ برای Kerberos، SMB با IP ممکن است رفتار authentication را تغییر دهد. netstat و PID فقط یک snapshot‌اند و تشخیص قطعی بدافزار نیستند.',
'cmdletهای NetTCPIP روی Windows موجودند؛ PowerShell Core در سایر OS همان moduleها را ندارد. دستورات قدیمی CMD برای تشخیص حفظ شده‌اند، اما عملیات mutation بدون preflight از flow جدید حذف شده است.',
[('Microsoft: Test-NetConnection','https://learn.microsoft.com/en-us/powershell/module/nettcpip/test-netconnection'),('Microsoft: ipconfig','https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/ipconfig')],
[['آیا ping موفق یعنی SMB سالم است؟','خیر؛ TCP/445، authentication و permission باید جدا بررسی شوند.'],['آیا باید ابتدا DNS cache را پاک کرد؟','خیر؛ ابتدا cache و پاسخ resolver را ثبت و مقایسه کنید.']])

add('windows-hardware-info-cmd-vs-dxdiag','Inventory سازمانی Windows: CIM، ظرفیت و محدودیت DxDiag',3,
'موضوع: hardware inventory؛ هدف: اطلاعات سیستم. سطح قبلی: متوسط. کمبود: خروجی ساختاریافته، null در VM، driver و محدودیت GPU RAM.',WIN,WINHW,
'PowerShell x64، CimCmdlets و Storage؛ خواندن local با دسترسی عادی، remote با حساب delegated و WinRM امن. DxDiag برای Desktop Experience است.',
'خروجی screenshot برای تصمیم ظرفیت کافی نیست. Inventory باید قابل مقایسه، timestampدار و با تمایز سخت‌افزار فیزیکی و VM جمع‌آوری شود.',
'قبل از ارتقای fleet، مدل BIOS، core و RAM سرورها با inventory سازمان مقایسه می‌شود. serial موجود در VM الزاماً serial chassis نیست.',
'CIM/Storage providers → snapshot JSON → CMDB → مقایسه baseline. DxDiag فقط برای مشکل گرافیک و DirectX client مناسب است؛ ظرفیت GPU حرفه‌ای از ابزار vendor تأیید می‌شود.',
'''اسکریپت کامل read-only را در Windows PowerShell 5.1 x64 اجرا کنید. محل خروجی در TEMP کاربر است؛ اگر policy سازمان اجازه ندهد با مسیر مجاز جایگزین کنید.

```powershell
$ErrorActionPreference = 'Stop'
$InventoryPath = Join-Path $env:TEMP ('Inventory-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.json')
$Inventory = [ordered]@{
    CollectedUtc = [DateTime]::UtcNow.ToString('o')
    Computer = Get-CimInstance Win32_ComputerSystem | Select-Object Name,Manufacturer,Model,TotalPhysicalMemory
    OS = Get-CimInstance Win32_OperatingSystem | Select-Object Caption,Version,BuildNumber,LastBootUpTime
    CPU = @(Get-CimInstance Win32_Processor | Select-Object Name,NumberOfCores,NumberOfLogicalProcessors)
    Memory = @(Get-CimInstance Win32_PhysicalMemory | Select-Object Manufacturer,PartNumber,Capacity,Speed)
    BIOS = Get-CimInstance Win32_BIOS | Select-Object Manufacturer,SMBIOSBIOSVersion,SerialNumber
    Disks = @(Get-PhysicalDisk | Select-Object FriendlyName,MediaType,Size,HealthStatus)
    Video = @(Get-CimInstance Win32_VideoController | Select-Object Name,DriverVersion)
}
$Inventory | ConvertTo-Json -Depth 6 | Set-Content -LiteralPath $InventoryPath -Encoding UTF8
Get-Content -LiteralPath $InventoryPath -Raw | ConvertFrom-Json | Select-Object CollectedUtc,Computer
Get-FileHash -LiteralPath $InventoryPath -Algorithm SHA256
```

خروجی JSON شامل timestamp UTC و objectهای مشخص است؛ Capacity و Size برحسب byte هستند. برای GiB بر 1GB در PowerShell تقسیم کنید. serial و model را با CMDB و کنسول vendor تأیید کنید. DxDiag فقط در سیستم دارای GUI و binary مربوط اجرا شود؛ مسیر گزارش باید موجود و نوشتنی باشد.''',
'Serial، hostname و inventory توپولوژی سازمان را افشا می‌کنند؛ فایل به share عمومی نرود. remote CIM از session مجاز با least privilege و WinRM TLS/Kerberos استفاده کند؛ Basic بدون TLS فعال نشود.',
'به‌جای alert روی هر inventory، تغییر غیرمجاز RAM/BIOS و health دیسک بررسی شود. Zabbix برای سلامت و CMDB برای مالکیت دارایی استفاده شوند. HealthStatus سالم به معنی SMART کامل یا سلامت RAID controller نیست.',
'CIM null در VM می‌تواند محدودیت provider باشد؛ مقدار Unknown را با صفر واقعی یکی نکنید. Get-PhysicalDisk ممکن است دیسک پشت RAID را abstract ببیند؛ خروجی vendor controller معیار مکمل است. DxDiag روی Server Core ممکن است موجود نباشد؛ این خطای اجرای inventory سرور نیست. access denied در remote یعنی session/permission بررسی شود، نه غیرفعال‌کردن firewall.',
'Inventory state سرویس را تغییر نمی‌دهد؛ JSON و hash با retention نگهداری شوند. برای recovery سخت‌افزار علاوه بر inventory، firmware، driver، config RAID و backup کاربردی لازم است. serial به‌تنهایی restore procedure نیست.',
'از CIM برای automation و tool vendor برای telemetry اختصاصی استفاده کنید. API کم‌دقت AdapterRAM را برای GPU جدید معیار ظرفیت نگذارید.',
'WMIC deprecated است و موجودبودن آن در یک میزبان تضمین fleet نیست. این script از CIM استفاده می‌کند. Server Core و workstation گرافیکی scopeهای متفاوت دارند.',
[('Microsoft: Get-CimInstance','https://learn.microsoft.com/en-us/powershell/module/cimcmdlets/get-ciminstance'),('Microsoft: removed features','https://learn.microsoft.com/en-us/windows/whats-new/removed-features')],
[['آیا DxDiag ابزار مناسب همه سرورهاست؟','خیر؛ برای گرافیک و DirectX است و روی Server Core ممکن است موجود نباشد.'],['آیا HealthStatus سالم سلامت RAID را تضمین می‌کند؟','خیر؛ telemetry کنترلر و ابزار vendor نیز باید بررسی شوند.']])

add('windows-password-reset-secure-access-recovery','بازیابی دسترسی Windows: AD، حساب محلی، LAPS و Audit',1,
'موضوع: reset password؛ هدف: recovery مجاز. سطح قبلی: مقدماتی. کمبود: delegation، DPAPI/EFS، audit event و service account وابسته.',WIN,WINHW,
'برای local: Microsoft.PowerShell.LocalAccounts روی x64؛ برای AD: RSAT ActiveDirectory و حق Reset Password روی OU هدف. درخواست و هویت کاربر پیش از اجرا تأیید شود.',
'Reset حساب اشتباه می‌تواند سرویس، task زمان‌بندی‌شده یا داده EFS را از دسترس خارج کند. بازیابی باید نوع هویت، وابستگی‌ها و audit را قبل از تغییر مشخص کند.',
'کاربر انسانی account خود را فراموش کرده و Helpdesk فقط روی OU کارکنان delegation دارد. حساب‌های سرویس و Domain Admin خارج scope این عملیات‌اند.',
'Identity verification → account type → delegation → dependency check → reset → login validation → audit. Microsoft Account، Entra، AD و local مسیرهای بازیابی متفاوت دارند؛ BitLocker کلید مستقل دارد.',
'''ابتدا حساب انسانی AD را روی workstation مجاز با RSAT بررسی کنید. ops.user placeholder است؛ قبل از reset نام و OU باید با درخواست تطبیق داشته باشد.

```powershell
Import-Module ActiveDirectory -ErrorAction Stop
$AccountName = 'ops.user'
Get-ADUser -Identity $AccountName -Properties Enabled,LockedOut,PasswordLastSet,DistinguishedName |
    Select-Object SamAccountName,Enabled,LockedOut,PasswordLastSet,DistinguishedName
$NewPassword = Read-Host 'Enter approved temporary password' -AsSecureString
Set-ADAccountPassword -Identity $AccountName -Reset -NewPassword $NewPassword -ErrorAction Stop
Set-ADUser -Identity $AccountName -ChangePasswordAtLogon $true -ErrorAction Stop
Get-ADUser -Identity $AccountName -Properties PasswordLastSet | Select-Object SamAccountName,PasswordLastSet
```

موفقیت cmdlet معمولاً خروجی ندارد؛ PasswordLastSet با forced change ممکن است صفر/زمان خاص نشان داده شود. کاربر از workstation عضو دامنه به DC قابل دسترس login و password را تغییر دهد. unlock فقط اگر lockout تأیید و علت credential قدیمی رفع شده باشد.

برای حساب local انسانی، در PowerShell elevated روی همان دستگاه، مسیر جدا زیر اجرا شود.

```powershell
$LocalAccountName = 'local.ops'
Get-LocalUser -Name $LocalAccountName -ErrorAction Stop
$LocalPassword = Read-Host 'New local password' -AsSecureString
Set-LocalUser -Name $LocalAccountName -Password $LocalPassword -ErrorAction Stop
```

local.ops placeholder است. حساب local تحت Windows LAPS از workflow LAPS سازمان بازیابی/rotate شود؛ reset دستی با lifecycle آن تعارض ایجاد نکند.''',
'راز در transcript، command history یا ticket نوشته نشود. قبل reset local، احتمال از دست رفتن دسترسی EFS، certificate private key و credentialهای DPAPI ارزیابی شود. برای حساب سرویس، gMSA یا workflow rotation اختصاصی لازم است. BitLocker recovery key فقط پس از تطبیق device/key ID تحویل داده شود.',
'با Advanced Audit Policy مناسب، eventهای Security مانند 4724 برای reset و 4740 برای lockout به SIEM منتقل شوند. عدم وجود event می‌تواند به audit policy مربوط باشد؛ خودبه‌خود اثبات عدم تغییر نیست. alert reset حساب حساس به تیم IAM برود.',
'Access denied: delegation و elevation؛ password policy error: complexity/history و fine-grained policy؛ تکرار lockout: سرویس یا task با credential قدیمی. DC unreachable: DNS و secure channel بررسی شود؛ resetهای پیاپی راه‌حل نیست. PIN Windows Hello password حساب نیست و مسیر رسمی خود را دارد.',
'password قدیمی قابل rollback نیست؛ secret جدید از مسیر امن تحویل و در صورت شکست rotation دوباره انجام شود. EFS recovery certificate، BitLocker key و حساب break-glass از قبل backup شده باشند. AD system-state restore جای reset یک کاربر نیست.',
'برای هر reset شناسه درخواست، operator، account و زمان ثبت شود؛ password ثبت نشود. identity verification و permission delegated در runbook Helpdesk واضح باشند.',
'Microsoft Account و Entra از recovery رسمی همان provider استفاده کنند؛ Set-ADAccountPassword فقط AD DS است. Password reset disk فقط local است و باید پیش از حادثه ساخته شود.',
[('Microsoft: Set-ADAccountPassword','https://learn.microsoft.com/en-us/powershell/module/activedirectory/set-adaccountpassword'),('Microsoft: Windows LAPS','https://learn.microsoft.com/en-us/windows-server/identity/laps/laps-overview'),('Microsoft: BitLocker FAQ','https://learn.microsoft.com/en-us/windows/security/operating-system-security/data-protection/bitlocker/faq')],
[['آیا reset password کلید BitLocker را جایگزین می‌کند؟','خیر؛ BitLocker recovery key مستقل است.'],['آیا password قبلی قابل rollback است؟','خیر؛ در صورت نیاز باید credential جدید از workflow مجاز تنظیم شود.']])
add('install-mikrotik-chr-vmware-workstation','آزمایشگاه CHR در VMware Workstation: جداسازی شبکه و آزمون RouterOS',3,
'موضوع: CHR VM؛ هدف: lab RouterOS. سطح قبلی: مقدماتی. کمبود: جداسازی WAN/LAN، اثر license و عدم تعمیم Workstation به Production.',
'RouterOS CHR v7، VMware Workstation نسخه دارای مجوز و پشتیبانی میزبان؛ patch روتر و build Workstation در manifest ثبت شوند.',
'پیشنهاد lab: ۲ vCPU، ۱ GiB RAM برای CHR، حداقل ۱ GiB دیسک و دو vNIC؛ ظرفیت throughput به میزبان و license وابسته است.',
'VMDK رسمی MikroTik، Virtual Network Editor و Administrator برای ساخت VMnet. console اولیه؛ password و license از سیاست سازمان.',
'اتصال bridge اشتباه در lab می‌تواند DHCP یا routing آزمایشی را وارد شبکه شرکت کند. CHR باید با VMnetهای مشخص و دسترسی مدیریت محدود راه‌اندازی شود.',
'یک VMnet NAT نقش WAN و یک Host-only مجزا نقش LAN دارد. تیم قبل ارتقای روتر شعبه export و firewall را روی CHR آزمایش می‌کند.',
'VMnet NAT → CHR ether1؛ Host-only بدون DHCP داخلی VMware → CHR ether2 → VM کلاینت. شبکه مدیریت و شبکه تست به نام و MAC در inventory نگاشت شوند.',
'''۱. VMDK رسمی CHR را دانلود و checksum منتشرشده را بررسی کنید؛ نام و hash فایل را ثبت کنید. VM جدید با Existing virtual disk بسازید و در نخستین boot، mapping NIC را از console تأیید کنید.

۲. WAN آزمایشگاه 192.168.100.10/24 با gateway 192.168.100.2 است؛ این مقادیر باید با VMnet NAT واقعی تطبیق داده شوند. LAN برابر 10.99.0.1/24 است.

```routeros
/interface ethernet print detail
/user set [find name=admin] password="REPLACE_WITH_UNIQUE_ADMIN_SECRET"
/system identity set name=CHR-STAGING
/ip address add address=192.168.100.10/24 interface=ether1 comment="LAB WAN"
/ip address add address=10.99.0.1/24 interface=ether2 comment="LAB LAN"
/ip route add dst-address=0.0.0.0/0 gateway=192.168.100.2
/ip dns set servers=192.168.100.2 allow-remote-requests=no
/ip firewall filter add chain=input connection-state=established,related action=accept
/ip firewall filter add chain=input in-interface=ether2 src-address=10.99.0.0/24 action=accept
/ip firewall filter add chain=input action=drop
/ip firewall filter add chain=forward connection-state=established,related action=accept
/ip firewall filter add chain=forward in-interface=ether2 out-interface=ether1 action=accept
/ip firewall filter add chain=forward action=drop
/ip firewall nat add chain=srcnat src-address=10.99.0.0/24 out-interface=ether1 action=masquerade
/ping 192.168.100.2 count=3
/system license print
```

این ruleset فقط برای IPv4 lab پاک است؛ IPv6 در VMnet نیز جدا محدود شود. انتظار: gateway پاسخ دهد و کلاینت LAN با gateway 10.99.0.1 بتواند مسیر مجاز را طی کند. از WAN جدید نباید WinBox/SSH روتر قابل دسترسی باشد.''',
'VMnet Bridged به NIC شرکت وصل نشود. حساب admin secret یکتا و serviceهای اضافی خاموش باشند. کنسول میزبان با MFA/کنترل دسترسی محافظت شود؛ snapshot شامل credential است.',
'CPU میزبان و VM، dropped packets و interface counter ثبت شوند. throughput زیر license Free محدود است؛ نتایج benchmark را با سطح license همراه کنید.',
'IP ندارد: mapping vNIC، connected at power on و VMnet subnet. gateway timeout: MAC/VMnet و firewall host. سرعت محدود: license و load میزبان. default route به IP خود CHR اشاره نکند.',
'قبل آزمون snapshot با label و export خارج VM تهیه شود؛ snapshot جای backup مستقل نیست. بازگرداندن clone و license/system ID باید مطابق دستورالعمل CHR بررسی شود؛ VM clone با IP یکسان هم‌زمان روشن نشود.',
'Lab برای validation syntax و رفتار است؛ برای dataplane Production از hypervisor پشتیبانی‌شده و sizing واقعی استفاده کنید.',
'Free license محدودیت 1 Mbps upload per interface دارد؛ P1/P10/P-Unlimited شرایط متفاوت دارند. Workstation desktop baseline طراحی HA نیست.',
[('MikroTik: CHR','https://help.mikrotik.com/docs/spaces/ROS/pages/18350234/Cloud+Hosted+Router+CHR'),('MikroTik: Downloads','https://mikrotik.com/download')],
[['آیا lab Workstation جای روتر Production است؟','خیر؛ هدف آزمون configuration است و HA، پشتیبانی و ظرفیت جدا باید طراحی شوند.'],['چرا throughput پایین است؟','سطح license CHR و resource میزبان را هم‌زمان بررسی کنید.']])

add('install-vmware-esxi-vmware-workstation-vmcisr','Nested ESXi در Workstation: آزمایشگاه کنترل‌شده و تشخیص VMCI',3,
'موضوع: nested ESXi؛ هدف: lab. سطح قبلی: مقدماتی. کمبود: مرز support، اثر VBS/Hyper-V و روش تشخیص VMCI بدون hack عمومی.',
'ESXi 8.x با ISO مجاز و Workstation build سازگار با OS میزبان؛ version دقیق و KB مربوط به خطا پیش از تغییر ثبت شوند.',
'پیشنهاد lab میزبان ۳۲ GiB RAM، CPU با VT-x/EPT یا AMD-V/RVI و SSD؛ nested ESXi با ۴ vCPU، ۸ GiB RAM، boot disk مناسب و datastore جدا. این اعداد تضمین performance نیستند.',
'Firmware virtualization فعال، دسترسی admin به Workstation و console؛ network Host-only/NAT جدا. license نرم‌افزار باید معتبر باشد.',
'Nested ESXi برای تمرین migration و API مفید است، اما نتایج آن پشتیبانی و عملکرد bare metal را ثابت نمی‌کند. خطای VMCI باید از log و compatibility تحلیل شود.',
'تیم پیش از تغییر vSwitch، یک ESXi nested و دو VM test در lab می‌سازد؛ شبکه management از LAN شرکت جداست.',
'Physical CPU → Workstation virtualization engine → nested ESXi → nested VM. اگر hypervisor میزبان قابلیت مورد نیاز را به guest ارائه نکند nested VM boot نمی‌شود.',
'''۱. نسخه Workstation و OS را ثبت کنید. در Firmware میزبان virtualization را فعال و در VM Settings گزینه Virtualize Intel VT-x/EPT or AMD-V/RVI را انتخاب کنید.

۲. VM با guest type مناسب VMware ESXi و adapter مورد قبول همان build بسازید. ISO رسمی را attach و ESXi را روی disk اختصاصی نصب کنید؛ انتخاب disk مقصد تأیید شود.

۳. از DCUI، Management Network، IP، DNS و hostname را تنظیم کنید. نمونه 10.99.0.11/24 روی Host-only است و باید با VMnet هم‌خوان باشد. F2 → Configure Management Network → Test Management Network.

۴. روی workstation، دسترسی TCP و سپس Host Client را بررسی کنید.

```powershell
Test-NetConnection -ComputerName 10.99.0.11 -Port 443
```

انتظار TcpTestSucceeded: True؛ مرورگر به https://10.99.0.11/ باز شود. گواهی lab را با fingerprint console بررسی کنید. nested VM کوچک بسازید و boot، شبکه و storage آن را validate کنید؛ بالا آمدن Host Client به‌تنهایی کافی نیست.''',
'management از اینترنت قابل دسترسی نباشد. برای lab credential جدا استفاده شود. VBS/Hyper-V/Secure Boot میزبان را برای workaround بدون تحلیل امنیتی خاموش نکنید؛ اثر و پشتیبانی build مربوط لازم است.',
'resource میزبان، ballooning/swapping guest، datastore latency و log Workstation ثبت شوند. alertهای lab از Production جدا شوند. oversubscription آزمایشگاه نباید خطای طراحی Production تلقی شود.',
'Module VMCISr power on failed: vmware.log همان VM، تنظیم device و KB همان build را بررسی کنید؛ حذف عمومی vmci0 یا تغییر VMX راه‌حل نسخه‌مستقل نیست. VT-x unavailable: firmware، ارائه nested virtualization و موتور فعال میزبان. NIC missing: نوع adapter و image compatibility؛ driver تصادفی وارد ISO نکنید.',
'VM خاموش‌شده و config/VMX به‌همراه export config host backup شوند. snapshot پیش از آزمایش مفید است ولی روی nested workload stateful جای backup داخل guest را نمی‌گیرد. rollback workaround VMX فقط پس از خاموشی و از نسخه ثبت‌شده باشد.',
'Lab و Production scope جدا در سند ذکر شوند. خطای دقیق و buildها در ticket پشتیبانی ثبت شوند؛ workaround فقط به نسخه‌ای که KB پوشش می‌دهد اعمال شود.',
'Broadcom محدودیت پشتیبانی nested ESXi را اعلام می‌کند؛ نصب موفق در Workstation به معنی supported Production نیست. VMCISr یک علامت است و علت واحد ندارد.',
[('Broadcom: nested ESXi support','https://knowledge.broadcom.com/external/article/313547/support-for-running-esxi-as-a-nested-vir.html'),('Broadcom: Workstation virtualization','https://knowledge.broadcom.com/external/article/389469')],
[['آیا nested ESXi روی Workstation پشتیبانی Production دارد؟','باید ماتریس support سازنده بررسی شود؛ این مقاله فقط آزمایشگاه است.'],['آیا خطای VMCI همیشه با حذف device حل می‌شود؟','خیر؛ log و KB مربوط به build باید علت را مشخص کنند.']])

add('vmware-esxi-8-installation-basic-configuration','استقرار ESXi 8 در دیتاسنتر: HCL، Management و Recovery',2,
'موضوع: نصب ESXi؛ هدف: راه‌اندازی host. سطح قبلی: مقدماتی. کمبود: HCL، OEM image، lifecycle، config backup و network redundancy.',
'ESXi 8.x با patch دارای پشتیبانی و OEM image مورد تأیید سازنده؛ vCenter سازگار، firmware/driver در Compatibility Guide بررسی شود.',
'CPU، NIC، controller و boot device باید در HCL همان build باشند. پیشنهاد نمونه ۲ uplink مدیریت و storage/datastore مستقل؛ sizing workload با capacity plan انجام شود.',
'console/iLO/iDRAC مجاز، credential نصب، license و DNS forward/reverse، NTP و VLAN آماده. نصب روی disk مقصد داده آن را بازنویسی می‌کند.',
'نصب موفق hypervisor بدون تطبیق HCL و شبکه مدیریت، آماده سرویس نیست. میزبان باید پیش از ورود workload از نظر firmware، DNS، NTP، uplink و مسیر recovery تأیید شود.',
'یک host جدید به cluster اضافه می‌شود؛ هنوز workload روی آن نیست. management VLAN30 با دو uplink و console خارج شبکه فعال است.',
'OOB console → ESXi boot device؛ vmk management → vSS/vDS uplinks → switch VLAN؛ datastore جدا → VM. storage و management failure domain در طراحی ثبت شوند.',
'''۱. مدل CPU/NIC/controller و firmware را با build هدف و image OEM تطبیق دهید. hash ISO را ثبت و boot device را با serial دقیق تأیید کنید.

۲. از installer نصب را روی boot device تعیین‌شده انجام دهید. قبل انتخاب، datastore موجود و disk حاوی داده را شناسایی کنید. اولین boot از DCUI انجام شود.

۳. در F2 → Configure Management Network، NICهای مدیریت، VLAN ID مطابق switch port، static IP و DNS را تنظیم کنید. Test Management Network باید gateway و DNS را بررسی کند. IP مثال 10.20.30.11/24 و GW 10.20.30.1 است.

۴. Host Client را با hostname ثبت‌شده و گواهی معتبر باز کنید؛ SSH فقط موقتاً در پنجره تغییر فعال شود. از ESXi Shell دستورهای read-only زیر را اجرا کنید.

```sh
vmware -v
esxcli system version get
esxcli network nic list
esxcli network ip interface ipv4 get
esxcli network ip route ipv4 list
esxcli system ntp get
```

انتظار: build هدف، vmk management با IP صحیح و هر دو uplink طبق طراحی. DNS و NTP را در Host Client تنظیم و start policy بررسی کنید. سپس host را به vCenter اضافه، یک VM canary ایجاد و شبکه، datastore و restart آزمایشی workload را تأیید کنید.''',
'management subnet محدود، حساب admin نام‌دار در vCenter و least privilege؛ shell/SSH پس از تغییر خاموش شود. Lockdown Mode فقط پس از آزمون exception و OOB recovery فعال شود. certificate از PKI سازمان و syslog مقصد امن باشد.',
'vCenter alarm برای uplink، datastore، hardware sensor و host disconnect؛ Zabbix vCenter API با حساب محدود. NTP، latency storage و مصرف boot device ثبت شود؛ CPU پایین به‌تنهایی سلامت نیست.',
'''management قطع: OOB/DCUI، VLAN و vmnic mapping. NIC missing: OEM image و driver/firmware HCL. datastore ناپدید: path و controller، نه format مجدد.

```sh
esxcli storage core path list
esxcli network vswitch standard list
tail -n 80 /var/log/vmkernel.log
tail -n 80 /var/log/hostd.log
```

در VLAN trunk، tag سمت VMkernel و physical switch باید هم‌خوان باشد؛ double tagging دسترسی را قطع می‌کند. خطای certificate را با نام DNS و chain حل کنید، نه غیرفعال‌کردن validation عمومی.''',
'''در ESXi Shell موقت config bundle بسازید؛ URL خروجی را از workstation مجاز دانلود و سپس shell را ببندید.

```sh
vim-cmd hostsvc/firmware/sync_config
vim-cmd hostsvc/firmware/backup_config
```

Bundle حاوی config است؛ backup VM/datastore نیست. restore به build و UUID الزامات دارد؛ روی host جایگزین طبق KB سازنده و در maintenance انجام شود. package/image، license، VLAN و credential recovery خارج host نگهداری شوند.''',
'Canary قبل workload، patch staged و firmware/driver هماهنگ. USB/SD تاریخی را بدون بررسی الزامات storage build جدید انتخاب نکنید.',
'این baseline ESXi 8 است؛ lifecycle/support و entitlement ممکن است تغییر کند و پیش از deploy بررسی شود. Workstation nested برای اعتبارسنجی HCL کافی نیست.',
[('Broadcom: ESXi configuration backup','https://knowledge.broadcom.com/external/article/313510/how-to-back-up-and-restore-the-esxi-host.html'),('Broadcom: Compatibility Guide','https://compatibilityguide.broadcom.com/')],
[['آیا backup config شامل VMهاست؟','خیر؛ ماشین‌ها و datastore به backup مستقل نیاز دارند.'],['آیا NIC شناسایی‌شده الزاماً supported است؟','خیر؛ model، driver، firmware و build باید در ماتریس سازگاری تطبیق داشته باشند.']])

add('vsphere-standard-switch-vs-distributed-switch','طراحی vSS و vDS: مهاجرت VMkernel، VLAN و بازیابی Management',2,
'موضوع: switch انتخابی؛ هدف: طراحی شبکه vSphere. سطح قبلی: متوسط. کمبود: rollback دقیق migration، licensing و failure vCenter.',
'vSphere/ESXi 8.x؛ build سازگار vCenter و entitlement لازم برای vDS/featureها پیش از طراحی تأیید شود.',
'دو uplink مستقل برای نمونه migration؛ switch فیزیکی trunk و MTU یکسان. یک uplink مدیریت تا validation روی vSS باقی بماند.',
'مجوز Network configuration در vCenter و OOB console هر host. LACP فقط با support و طراحی switch فیزیکی و vDS مناسب.',
'مهاجرت هم‌زمان vmk0 و همه uplinkها می‌تواند host و vCenter را از management خارج کند. انتخاب vDS باید همراه با migration مرحله‌ای و مسیر بازگشت باشد.',
'cluster شش host دارد؛ سیاست port group بین hostها drift کرده است. تیم به vDS مهاجرت می‌کند ولی روی host canary یک uplink vSS نگه می‌دارد.',
'vSS: config محلی هر host؛ vDS: control plane مدیریت‌شده در vCenter و dataplane روی host. نبود vCenter لزوماً forwarding موجود را قطع نمی‌کند، اما تغییر و recovery محدود می‌شود.',
'''۱. vSS، port group، VLAN، MTU، vmk و uplink هر host را inventory کنید. در ESXi Shell read-only:

```sh
esxcli network vswitch standard list
esxcli network vswitch standard portgroup list
esxcli network ip interface list
esxcli network nic list
```

۲. در vCenter، vDS با نسخه سازگار کمترین host بسازید. distributed port groupهای مدیریت، VM و vMotion با VLAN/MTU دقیق ایجاد شوند؛ security policy و teaming با baseline تطبیق داده شوند.

۳. فقط uplink دوم host canary را به vDS وصل کنید؛ یک VM test روی port group مقصد قرار دهید. VLAN، DNS و دسترسی برنامه را بررسی کنید.

۴. با OOB آماده، vmk مدیریت را از wizard Add and Manage Hosts انتقال دهید؛ uplink vSS اولیه را تا تأیید management نگه دارید. gateway با vmk واقعی آزموده شود.

```sh
vmkping -I vmk0 -c 3 10.20.30.1
```

vmk0 و gateway placeholder طراحی هستند. MTU بزرگ فقط وقتی end-to-end تعریف شده با probe مناسب و DF آزمایش شود. پس از پایداری host و دسترسی vCenter، uplink باقی‌مانده را منتقل کنید. میزبان دوم قبل پذیرش canary تغییر نکند.''',
'Promiscuous mode، MAC changes و forged transmits بدون نیاز workload فعال نشوند. نقش شبکه از Administrator عمومی تفکیک شود؛ backup vDS و credential console امن نگهداری شوند.',
'vCenter برای host disconnect، uplink down، VLAN/MTU drift و packet drop alarm داشته باشد. LACP state و خطای switch فیزیکی از تیم شبکه جمع‌آوری شود؛ LLDP/CDP به policy اطلاعات توپولوژی وابسته است.',
'VM disconnected ولی vmk سالم: VLAN، dvPort و policy VM. host disconnect: mapping uplink و management VLAN از OOB. فقط یک host مشکل دارد: physical trunk و drift baseline. MTU mismatch می‌تواند packet کوچک را عبور دهد و storage/vMotion را خراب کند.',
'پیش از migration، export vDS و config host و نسخه vCenter backup شوند. اگر management قطع شد، از DCUI/OOB مطابق KB سازنده recovery networking انجام و vmk به vSS/port group شناخته‌شده برگردانده شود. نام uplink و VLAN قبلی برای operator محلی در runbook موجود باشد؛ vCenter تنها مسیر recovery نباشد.',
'یک host و یک uplink در هر مرحله؛ rollback پیش از حذف آخرین uplink vSS اثبات شود. LACP به‌تنهایی redundancy application را تضمین نمی‌کند.',
'vSS VLAN و NIC teaming دارد، اما vDS featureهای متمرکز ارائه می‌دهد. availability و licensing featureهایی مانند LACP/NIOC با edition و قرارداد فعلی بررسی شوند.',
[('Broadcom: LACP requirements','https://knowledge.broadcom.com/external/article/324555/host-requirements-for-link-aggregation-e.html'),('Broadcom: vSS/vDS concepts','https://knowledge.broadcom.com/external/article?legacyId=1010555')],
[['آیا خاموشی vCenter همه ترافیک vDS را قطع می‌کند؟','معمولاً dataplane موجود روی host ادامه دارد؛ عملیات مدیریت و recovery محدود می‌شود.'],['چرا همه uplinkها هم‌زمان منتقل نمی‌شوند؟','تا تأیید مسیر جدید، uplink vSS راه مدیریت و rollback را حفظ می‌کند.']])
add('netbox-installation-setup-ubuntu','استقرار NetBox روی Ubuntu: IPAM، امنیت API و بازیابی PostgreSQL',2,
'موضوع: NetBox؛ هدف: source of truth. سطح قبلی: متوسط. کمبود: restore، permission redirection، plugin matrix و آزمون migration. نسخه 4.7.1 قبلی تاریخی است.',
'baseline: Ubuntu Server 24.04 LTS، NetBox 4.7.2، Python 3.12، PostgreSQL 16، Redis 7. patch و pluginها پیش از استقرار در staging تثبیت شوند.',
'پیشنهاد نمونه ۴ vCPU، ۸ GiB RAM، ۶۰ GiB SSD؛ ظرفیت DB/media، تعداد object و job اندازه‌گیری شود. نمونه single node است و HA کامل پیاده نمی‌کند.',
'sudo، git و apt؛ PKI سازمان برای netbox.example.com، DNS و حساب PostgreSQL اختصاصی. netbox.example.com placeholder است؛ SECRET_KEY و pepper تازه تولید می‌شوند.',
'NetBox مرجع طراحی شبکه است؛ از دست رفتن database یا دسترسی API بیش از حد می‌تواند inventory و automation را مختل کند. نصب باید همراه با policy هویت و restore قابل آزمایش باشد.',
'تیم شبکه سه دیتاسنتر، VLANها و prefixها را ثبت می‌کند. خواندن API برای automation مجاز است؛ write فقط به گروه مالک inventory داده می‌شود.',
'HTTPS/Nginx → Gunicorn loopback → Django/NetBox → PostgreSQL. Worker netbox-rq از Redis tasks استفاده می‌کند؛ cache و queue جدا هستند. backup مرجع اصلی شامل DB، media، config و secret است.',
'''نمونه برای VM تازه با DB محلی است. ابتدا dependency نصب و نسخه‌ها ثبت شوند؛ full upgrade خودکار انجام نمی‌شود.

```bash
sudo apt-get update
sudo apt-get install -y postgresql redis-server python3 python3-venv python3-dev build-essential libxml2-dev libxslt1-dev libffi-dev libpq-dev libssl-dev zlib1g-dev git nginx
python3 --version
psql --version
redis-cli ping
sudo -u postgres createuser netbox
sudo -u postgres psql -c '\\password netbox'
sudo -u postgres createdb -O netbox netbox
sudo -u postgres psql -d netbox -c 'GRANT CREATE ON SCHEMA public TO netbox;'
sudo git clone --branch v4.7.2 --depth 1 https://github.com/netbox-community/netbox.git /opt/netbox
sudo adduser --system --group netbox
sudo chown netbox:netbox /opt/netbox/netbox/media
```

\\password رمز را تعاملی می‌گیرد؛ همین رمز در configuration وارد شود. برای host تازه createuser/createdb است؛ روی نصب موجود اجرا نکنید. Redis باید روی loopback بماند. config کامل را با Python بسازید؛ prompt رمز DB را می‌گیرد؛ hostname نمونه را پیش از اجرا با نام واقعی جایگزین کنید.

```bash
sudo python3 - <<'PY'
from pathlib import Path
import getpass, secrets
db_password = getpass.getpass('PostgreSQL netbox password: ')
host = 'netbox.example.com'  # Replace with the real DNS hostname before running.
if not host or '/' in host or '*' in host:
    raise SystemExit('Use one explicit DNS hostname')
config = {
    'ALLOWED_HOSTS': [host],
    'DATABASES': {'default': {'NAME':'netbox','USER':'netbox','PASSWORD':db_password,'HOST':'127.0.0.1','PORT':'5432','CONN_MAX_AGE':300}},
    'REDIS': {'tasks': {'HOST':'127.0.0.1','PORT':6379,'DATABASE':0,'SSL':False}, 'caching': {'HOST':'127.0.0.1','PORT':6379,'DATABASE':1,'SSL':False}},
    'SECRET_KEY': secrets.token_urlsafe(64),
    'API_TOKEN_PEPPERS': {1:secrets.token_urlsafe(64)},
    'CSRF_TRUSTED_ORIGINS': ['https://' + host],
}
path = Path('/opt/netbox/netbox/netbox/configuration.py')
if path.exists():
    raise SystemExit('Configuration already exists; review it')
path.write_text('\\n'.join(f'{k} = {v!r}' for k,v in config.items())+'\\n')
PY
sudo chown root:netbox /opt/netbox/netbox/netbox/configuration.py
sudo chmod 640 /opt/netbox/netbox/netbox/configuration.py
sudo /opt/netbox/upgrade.sh
sudo -u netbox /opt/netbox/venv/bin/python /opt/netbox/netbox/manage.py check
sudo -u netbox /opt/netbox/venv/bin/python /opt/netbox/netbox/manage.py createsuperuser
sudo cp /opt/netbox/contrib/gunicorn.py /opt/netbox/gunicorn.py
sudo cp /opt/netbox/contrib/netbox.service /opt/netbox/contrib/netbox-rq.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now netbox netbox-rq
```

check باید بدون error و serviceها active باشند. Gunicorn contrib معمولاً 127.0.0.1:8001 است؛ bind را از فایل و ss بررسی کنید. Nginx نمونه کامل زیر را با hostname و certificate واقعی بنویسید. static alias به همان checkout اشاره کند.

```nginx
server {
    listen 443 ssl;
    server_name netbox.example.com;
    ssl_certificate /etc/nginx/tls/fullchain.pem;
    ssl_certificate_key /etc/nginx/tls/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    client_max_body_size 25m;
    location /static/ { alias /opt/netbox/netbox/static/; }
    location / {
        proxy_pass http://127.0.0.1:8001;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

فایل را در sites-available/netbox ذخیره، symlink نخستین نصب بسازید، nginx -t و reload انجام دهید؛ گواهی قبل test موجود باشد. curl --fail https://netbox.example.com/login/، login و یک API read با token محدود آزموده شود. سایت و prefix نمونه را با UI بسازید و permission منفی write را برای حساب read-only تست کنید.''',
'PostgreSQL و Redis به WAN bind نشوند؛ فقط 443 عمومی سازمانی و SSH مدیریت مجاز باشد. SECRET_KEY و API_TOKEN_PEPPERS در backup رمزنگاری‌شده ثابت بمانند؛ چرخش بدون طرح می‌تواند credentialها را باطل کند. roleهای object-level و expiration token، SSO/MFA و جداسازی superuser لازم‌اند.',
'health login/API، وضعیت worker، queue age، DB connection، disk و certificate expiry در Zabbix/Prometheus ثبت شوند. endpoint metrics فقط از monitoring قابل دسترسی باشد. active بودن Nginx بدون worker سالم، موفقیت job را ثابت نمی‌کند.',
'''502: Gunicorn bind و service log. DisallowedHost: hostname واقعی در ALLOWED_HOSTS. CSRF 403: trusted origin و forwarded proto. job pending: Redis tasks و netbox-rq.

```bash
systemctl is-active postgresql redis-server netbox netbox-rq nginx
sudo journalctl -u netbox -u netbox-rq -n 100 --no-pager
sudo ss -lntp
redis-cli ping
sudo -u netbox /opt/netbox/venv/bin/python /opt/netbox/netbox/manage.py check
```

خروجی config دارای secret را در ticket عمومی منتشر نکنید. plugin failure پس از upgrade با نسخه plugin و release note بررسی شود؛ پاک‌کردن migration history راه‌حل نیست.''',
'''برای RPO نمونه ۲۴ ساعت، backup روزانه DB و media/config و کپی off-host لازم است. اگر media/config تغییر می‌کنند، export زیر در پنجره maintenance بدون job نویسنده اجرا شود؛ برای RPO کوتاه‌تر PostgreSQL PITR طراحی شود.

```bash
sudo install -d -m 700 /var/backups/netbox
sudo bash -c 'umask 077; sudo -u postgres pg_dump -Fc netbox > /var/backups/netbox/netbox.dump'
sudo tar -czf /var/backups/netbox/application-state.tar.gz -C /opt/netbox netbox/media netbox/netbox/configuration.py
sudo chmod 600 /var/backups/netbox/application-state.tar.gz
```

این redirection داخل shell root انجام می‌شود؛ sudo pg_dump > مسیر محافظت‌شده کافی نیست. backup باید به vault/off-host منتقل و hash ثبت شود. restore روی سرور ایزوله با همان نسخه و plugin، نه روی DB اصلی:

```bash
sudo -u postgres createdb -O netbox netbox_restore
sudo bash -c 'sudo -u postgres pg_restore --no-owner --role=netbox -d netbox_restore < /var/backups/netbox/netbox.dump'
sudo -u postgres psql -d netbox_restore -c 'SELECT count(*) FROM dcim_site;'
```

config سرور restore به netbox_restore اشاره کند؛ SECRET_KEY/pepper و media از backup بازگردانده شوند، automation و webhook خروجی خاموش باشند. login، count object، API و job canary تأیید و RTO اندازه‌گیری شود. downgrade code بعد migration بدون restore DB معتبر نیست.''',
'نسخه و plugin lock، staging migration، backup پیش از upgrade و تست restore از شروط انتشارند. NetBox ابزار discovery یا NMS نیست؛ inventory مالک روشن نیاز دارد.',
'4.7.2 baseline این بازنویسی است، نه وعده سازگاری patchهای آینده. نصب Python package در 4.7 experimental است؛ مسیر Git/archive استفاده شده. API token قدیمی و Bearer جدید را با نسخه token تطبیق دهید.',
[('NetBox: installation','https://netbox.readthedocs.io/en/stable/installation/'),('NetBox: Git installation','https://netbox.readthedocs.io/en/stable/installation/3-netbox/'),('NetBox: 4.7 release notes','https://netbox.readthedocs.io/en/stable/release-notes/version-4.7/'),('NetBox: replicating','https://netbox.readthedocs.io/en/stable/administration/replicating-netbox/')],
[['آیا backup PostgreSQL به‌تنهایی کافی است؟','خیر؛ media، configuration، secret key، pepper و نسخه pluginها نیز باید حفظ شوند.'],['آیا بعد migration می‌توان فقط code را downgrade کرد؟','خیر؛ rollback باید سازگاری schema و بازیابی DB پیش از تغییر را در نظر بگیرد.']])

add('oxidized-network-device-configuration-backup','Oxidized سازمانی: Backup پیکربندی شبکه، Git و Restore کنترل‌شده',2,
'موضوع: backup شبکه؛ هدف: version history. سطح قبلی: متوسط. کمبود: pin dependency، bare Git، host key و تفاوت export/restore.',
UBUNTU+' Oxidized 0.37.0 با Ruby 3.2 روی Ubuntu 24.04 از RubyGems نصب و gem version در Gemfile.lock تثبیت شود؛ مدل هر device با firmware خودش تست شود.',
'پیشنهاد ۲ vCPU، ۴ GiB RAM و دیسک متناسب history؛ تعداد thread با latency تجهیزات و load AAA تعیین شود.',
'Ruby/Bundler و build dependency؛ حساب سرویس بدون login، credential read-only از vault، TCP/22 فقط به management دستگاه‌ها.',
'وجود فایل config به‌تنهایی قابلیت بازیابی شبکه را اثبات نمی‌کند. Oxidized باید تغییر را ثبت، عدم موفقیت جمع‌آوری را هشدار و restore انتخابی را روی مدل واقعی آزمون کند.',
'تیم ۱۲۰ switch و router را هر ساعت poll می‌کند؛ آخرین backup موفق هر node مستقل از سلامت process پایش می‌شود.',
'Device SSH → model parser → normalized config → bare Git → encrypted off-host copy. secret removal ممکن است recovery را ناقص کند؛ credential و binary backup مستقل نیاز دارند.',
'''نمونه lab روی VM تازه است. مسیر executable با command -v بررسی شود؛ بسته gem مصوب را نصب و نسخه واقعی در manifest ثبت کنید.

```bash
sudo apt-get update
sudo apt-get install -y ruby ruby-dev ruby-bundler build-essential libsqlite3-dev libssl-dev pkg-config cmake libssh2-1-dev libicu-dev zlib1g-dev libyaml-dev git
sudo gem install oxidized --version 0.37.0 --no-document
gem list oxidized
command -v oxidized
sudo useradd --system --create-home --home-dir /var/lib/oxidized --shell /usr/sbin/nologin oxidized
sudo install -d -o oxidized -g oxidized -m 700 /var/lib/oxidized/.config/oxidized
```

پیش از تکرار در Production، نسخه خروجی gem را در deployment pin و artifact آن را نگهداری کنید. config کامل در /var/lib/oxidized/.config/oxidized/config با mode 600 قرار گیرد؛ credential placeholder با secret vault جایگزین شود.

```yaml
---
username: oxidized
password: REPLACE_WITH_DEVICE_READ_SECRET
model: ios
interval: 3600
timeout: 20
retries: 2
threads: 5
input:
  default: ssh
  ssh:
    secure: true
output:
  default: git
  git:
    user: Oxidized
    email: oxidized@example.com
    repo: /var/lib/oxidized/configs.git
source:
  default: csv
  csv:
    file: /var/lib/oxidized/.config/oxidized/router.db
    delimiter: !ruby/regexp /:/
    map:
      name: 0
      model: 1
vars:
  remove_secret: true
```

router.db نمونه یک‌خطی: core-sw01.corp.example.com:ios . DNS و SSH host key آن را از مسیر معتبر تأیید کنید؛ flag secure و روش strict verification را با SSH input همان نسخه آزمون منفی کنید. credential read-only باید commands مورد نیاز model را مجاز کند؛ privilege 15 پیش‌فرض ندهید.

```bash
sudo -u oxidized env HOME=/var/lib/oxidized /usr/local/bin/oxidized
```

این foreground test است؛ مسیر binary را با خروجی واقعی جایگزین کنید. پس از یک poll موفق آن را متوقف و unit زیر با همین مسیر واقعی نصب کنید.

```ini
[Unit]
Description=Network configuration collection
After=network-online.target
Wants=network-online.target
[Service]
User=oxidized
Group=oxidized
Environment=HOME=/var/lib/oxidized
ExecStart=/usr/local/bin/oxidized
Restart=on-failure
RestartSec=10
UMask=0077
NoNewPrivileges=true
PrivateTmp=true
[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now oxidized
sudo -u oxidized git --git-dir=/var/lib/oxidized/configs.git log -1 --oneline
sudo -u oxidized git --git-dir=/var/lib/oxidized/configs.git ls-tree --name-only HEAD
```

Git output معمولاً bare است؛ cat مسیر فایل داخل repo روش خواندن config نیست. git show HEAD:نام-node بعد از ls-tree محتوای نسخه را نمایش می‌دهد. log موفق باید با node واقعاً جمع‌آوری‌شده تطبیق داشته باشد.''',
'UI/REST اختیاری روی public IP expose نشود؛ reverse proxy با auth لازم است. Git history می‌تواند secret حذف‌شده قدیمی را نگه دارد؛ encrypted و محدود باشد. read-only model برای RouterOS export یا SwOS باید جدا ارزیابی شود؛ credential گروه‌های مختلف مشترک نباشد.',
'per-node last successful fetch، duration و failure count پایش شوند. تغییر حساس ACL/routing برای review به ticket وارد شود؛ نه اجرای خودکار. stale بیشتر از دو interval آستانه نمونه است؛ systemd active کافی نیست.',
'node failed: DNS/SSH/AAA و model firmware. bare repo فایل working tree ندارد؛ از git show استفاده کنید. Permission denied روی repo: مالک service و UMask. host key changed: fingerprint از console تطبیق داده شود؛ verification خاموش نشود. خروجی ناقص: command authorization و model parser بررسی شود.',
'repo، config، node inventory و credential reference به off-host backup رمزنگاری‌شده منتقل شوند. برای restore، commit شناخته‌شده استخراج و روی دستگاه spare همان مدل/نسخه diff و syntax review شود؛ تغییر interface/IP می‌تواند management را قطع کند. Git export شامل private key، license یا binary state کامل نیست؛ backup بومی vendor جدا لازم است.',
'Restore drill روی مدل واقعی، نگهداری dependency manifest و منع privilege عمومی از شروط بهره‌برداری‌اند. NetBox API خام به قالب source Oxidized تبدیل لازم دارد؛ schema آن‌ها یکسان نیست.',
'SwOS/RouterOS/IOS modelها و transport متفاوت دارند. نسخه 0.37.0 baseline این راهنماست؛ dependencyهای transitively resolved نیز باید در artifact/lockfile سازمان تثبیت شوند.',
[('Oxidized release','https://rubygems.org/gems/oxidized/versions/0.37.0'),('Oxidized project','https://github.com/ytti/oxidized'),('Oxidized configuration','https://github.com/ytti/oxidized/blob/master/docs/Configuration.md'),('Oxidized output','https://github.com/ytti/oxidized/blob/master/docs/Outputs.md')],
[['چرا فایل config با cat در Git repo پیدا نمی‌شود؟','Git output می‌تواند bare باشد؛ git ls-tree و git show برای خواندن object استفاده شوند.'],['آیا export برای بازیابی کامل دستگاه کافی است؟','خیر؛ secret، certificate، license و binary state ممکن است جدا نیاز باشند.']])
add('install-dfs-server-windows-server','DFS در Windows Server: Namespace، Replication و بازیابی فایل',2,
'موضوع: DFS-N/DFS-R؛ هدف: share توزیع‌شده. سطح قبلی: مقدماتی. کمبود: setup کامل، staging، initial sync و backup مستقل.',
'Windows Server 2022/2025 عضو AD DS؛ PowerShell DFSN/DFSR. volume محتوای replicated در این baseline NTFS است؛ DFS-R روی ReFS پشتیبانی نمی‌شود.',
'دو file server با storage جدا، ظرفیت staging بر اساس بزرگ‌ترین فایل‌ها و فضای conflict/deleted؛ حداقل ظرفیت ثابت بدون inventory فایل‌ها اعلام نشود.',
'مجوز نصب role روی هر server، delegation namespace/replication در AD، SMB share و NTFS آماده. نمونه domain corp.example.com، serverهای FS01/FS02 و volume D: موجود باشند.',
'Namespace مسیر ثابت می‌دهد و Replication داده را کپی می‌کند؛ هیچ‌کدام به‌تنهایی backup نیستند. حذف یا overwrite می‌تواند به عضو دیگر هم منتقل شود.',
'دو شعبه از مسیر ثابت Company/Data استفاده می‌کنند. FS01 منبع اولیه و FS02 مقصد sync است؛ کاربران تا پایان initial sync روی FS02 نمی‌نویسند.',
'Client → AD referral/DFS-N → SMB target. DFS-R جدا از مسیر دسترسی، فایل بسته‌شده را بین اعضا replicate می‌کند. replication multi-master به معنی file locking بین سایت‌ها نیست.',
'''روی هر دو file server، role و مسیر را آماده کنید. D: باید volume NTFS مناسب باشد. گروه CORP\\FileUsers placeholder گروه واقعی سازمان است.

```powershell
Install-WindowsFeature FS-DFS-Namespace,FS-DFS-Replication -IncludeManagementTools
New-Item -ItemType Directory -Path 'D:\\DFSRoots\\Company','D:\\Data' -Force | Out-Null
New-SmbShare -Name Company -Path 'D:\\DFSRoots\\Company' -ReadAccess 'CORP\\FileUsers'
New-SmbShare -Name Data -Path 'D:\\Data' -ChangeAccess 'CORP\\FileUsers'
Get-WindowsFeature FS-DFS-Namespace,FS-DFS-Replication
```

NTFS ACL نیز با گروه مالک داده طراحی و اعمال شود؛ Share permission به‌تنهایی اجازه NTFS نمی‌دهد. از یک management host دارای module و delegation:

```powershell
Import-Module DFSN
Import-Module DFSR
New-DfsnRoot -Path '\\\\corp.example.com\\Company' -TargetPath '\\\\FS01\\Company' -Type DomainV2
New-DfsnRootTarget -Path '\\\\corp.example.com\\Company' -TargetPath '\\\\FS02\\Company'
New-DfsnFolder -Path '\\\\corp.example.com\\Company\\Data' -TargetPath '\\\\FS01\\Data'
New-DfsnFolderTarget -Path '\\\\corp.example.com\\Company\\Data' -TargetPath '\\\\FS02\\Data'
New-DfsReplicationGroup -GroupName 'Company-Data'
New-DfsReplicatedFolder -GroupName 'Company-Data' -FolderName 'Data'
Add-DfsrMember -GroupName 'Company-Data' -ComputerName 'FS01','FS02'
Add-DfsrConnection -GroupName 'Company-Data' -SourceComputerName 'FS01' -DestinationComputerName 'FS02'
Set-DfsrMembership -GroupName 'Company-Data' -FolderName 'Data' -ComputerName 'FS01' -ContentPath 'D:\\Data' -PrimaryMember $true -Force
Set-DfsrMembership -GroupName 'Company-Data' -FolderName 'Data' -ComputerName 'FS02' -ContentPath 'D:\\Data' -Force
```

Add-DfsrConnection به‌صورت پیش‌فرض reverse connection نیز می‌سازد؛ topology را با Get-DfsrConnection بررسی کنید. روی نصب موجود createها را تکرار نکنید. propagation AD و initial sync زمان می‌برد. PrimaryMember فقط انتخاب منبع initial sync است. قبل پذیرش، فایل canary بسته‌شده از FS01 به FS02 با hash یکسان برسد و backlog صفر شود.''',
'SMB و RPC/DFSR فقط بین اعضا و subnetهای مصرف‌کننده مطابق firewall رسمی Windows مجاز باشد؛ range پورت dynamic RPC را با policy سازمان هماهنگ کنید. ACL share و NTFS، SMB signing و audit access لازم‌اند؛ کاربران end-user روی namespace root write نداشته باشند.',
'backlog هر جهت، DFS Replication event log، staging pressure، disk space و conflict/deleted پایش شود. backlog لحظه‌ای طبیعی است؛ backlog رو به رشد چند interval مهم است. Zabbix checkها context حساب مانیتورینگ مشخص داشته باشند.',
'''namespace سالم ولی فایل قدیمی: DFS-R و backlog جدا بررسی شوند. دستورهای تشخیص روی میزبان دارای module:

```powershell
Get-DfsrConnection -GroupName 'Company-Data'
Get-DfsrBacklog -GroupName 'Company-Data' -FolderName 'Data' -SourceComputerName 'FS01' -DestinationComputerName 'FS02' -Verbose
Get-DfsrBacklog -GroupName 'Company-Data' -FolderName 'Data' -SourceComputerName 'FS02' -DestinationComputerName 'FS01' -Verbose
Get-WinEvent -LogName 'DFS Replication' -MaxEvents 30
Test-NetConnection FS02 -Port 445
```

backlog cmdlet فهرست محدود نشان می‌دهد؛ count کامل در verbose بررسی شود. DB یا فایل open ممکن است replicate نشود؛ staging insufficient یا content freshness نیاز بررسی event دارد. database DFS-R را برای رفع خطا حذف نکنید.''',
'VSS-aware backup مستقل از share و AD/namespace تهیه و نسخه off-site/immutable نگهداری شود. در restore فایل، propagation overwrite را مدیریت کنید؛ restore اولیه در مسیر ایزوله و مقایسه ACL/hash انجام شود، سپس فایل منتخب با Change Record برگردد. خرابی عضو: rebuild و non-authoritative sync طبق مستندات Microsoft؛ روی دو عضو primary هم‌زمان ندهید. RPO تابع backup واقعی است، نه فقط سرعت DFS-R.',
'DFS-R برای فایل‌های بسته و workflow سازگار است؛ database live، VM disk فعال و فایل مشترک دارای locking بین شعبه‌ها workload مناسب این طرح نیستند. initial sync پیش از referral کاربران تأیید شود.',
'DFS-N و DFS-R نقش‌های جدا دارند. این نمونه domain-based است؛ standalone namespace رفتار HA متفاوت دارد. volume replicated در baseline NTFS باشد.',
[('Microsoft: DFS-R overview','https://learn.microsoft.com/en-us/windows-server/storage/dfs-replication/dfs-replication-overview'),('Microsoft: DFS namespace deployment','https://learn.microsoft.com/en-us/windows-server/storage/dfs-namespaces/checklist-deploy-dfs-namespaces'),('Microsoft: DFSR PowerShell','https://learn.microsoft.com/en-us/powershell/module/dfsr/')],
[['آیا DFS-R جای backup است؟','خیر؛ حذف و overwrite می‌توانند replicate شوند و backup مستقل لازم است.'],['آیا PrimaryMember رهبر دائمی است؟','خیر؛ برای initial sync استفاده می‌شود و replication معمول multi-master است.']])

add('sql-server-automatic-backup-job','Backup سازمانی SQL Server: Agent، زنجیره Log و Restore آزمایشی',1,
'موضوع: backup job؛ هدف: recovery database. سطح قبلی: Enterprise با بسته مستقل موجود. کمبود: gate پذیرش RPO/RTO و نمونه restore ایزوله تکمیلی؛ اسکریپت‌های قبلی حفظ می‌شوند.',
'SQL Server 2022/2025 با edition پشتیبان SQL Server Agent؛ Windows Server و PowerShell 5.1. compression/encryption با edition/build بررسی شود.',
'storage backup جدا از data/log، ظرفیت full+diff+log و کپی off-host؛ throughput restore اندازه‌گیری شود و sizing بر اساس حجم واقعی باشد.',
'DBA برای backup/restore و Agent proxy محدود برای file operation؛ SQLBackupFiles و whitelist در بخش اجرایی موجود همین مقاله پیاده شده‌اند.',
'Job سبز به معنی قابل بازیابی‌بودن تمام databaseها نیست. برای پذیرش backup باید تازگی هر database، سلامت chain و restore واقعی روی instance ایزوله تأیید شود.',
'DB-Jira نمونه database سازمان است؛ RPO هدف ۱۵ دقیقه و RTO هدف ۶۰ دقیقه صرفاً اهداف آموزشی‌اند و باید از اندازه‌گیری restore تأیید شوند.',
'Whitelist → FULL/DIFF/LOG jobs → checksum/audit → encrypted off-host → restore drill. VERIFYONLY خوانایی backup را می‌سنجد؛ صحت کامل داده کاربردی و CHECKDB را جایگزین نمی‌کند.',
'''پیاده‌سازی کامل procedure، PowerShell تهیه پوشه، سه Agent Job، cleanup و audit در بخش‌های اجرایی موجود همین مقاله قرار دارد و حفظ شده است. ابتدا scripts را با نام فایل‌های مشخص ذخیره، whitelist و proxy را provision و jobهای disabled را دستی validate کنید. برای Express، SQL Server Agent موجود نیست و scheduler خارجی نیاز workflow مستقل دارد.

در instance منبع recovery model و وضعیت log reuse را بخوانید؛ این query هیچ model را تغییر نمی‌دهد.

```sql
SELECT name,state_desc,recovery_model_desc,log_reuse_wait_desc
FROM sys.databases
WHERE database_id > 4;
SELECT TOP (20) database_name,type,backup_finish_date,first_lsn,last_lsn,database_backup_lsn
FROM msdb.dbo.backupset
ORDER BY backup_finish_date DESC;
```

انتظار FULL/DIFF/LOG مطابق برنامه هر DB؛ NULL یا backup قدیمی alert است. پس از تغییر SIMPLE به FULL برای شروع log chain، full یا differential مناسب لازم است؛ log backup تا بررسی chain فعال نشود. نمونه restore زیر یک FULL مستقل را فقط روی instance تست بازیابی می‌کند.''',
'backup encryption certificate و private key با password قوی خارج instance backup شوند. service account فقط روی مسیر backup لازم permission داشته باشد. xp_cmdshell برای عملیات فایل فعال نشود. restore سرور تست از outbound integration و کاربران Production جدا باشد.',
'آخرین FULL/DIFF/LOG هر database، audit failure، فضای دیسک و duration پایش شوند. Queryهای موجود این مقاله missing/stale را مشخص می‌کنند؛ success سطح Agent به‌تنهایی کافی نیست. Alert از مسیر Database Mail/operator یا مانیتورینگ مستقل delivery test داشته باشد.',
'OS error 5: ACL از دید SQL service account، نه کاربر SSMS. 3201: مسیر فایل روی server SQL یا share مجاز؛ مسیر کلاینت معیار نیست. log backup unavailable: recovery model و chain. restore log gap: FirstLSN/LastLSN و FULL پایه بررسی شود؛ chain مفقود با تکرار VERIFYONLY ترمیم نمی‌شود.',
'''نمونه کامل restore یک FULL روی instance ایزوله است؛ این نام‌ها placeholder فایل موجود و logical name واقعی‌اند. ابتدا FILELISTONLY را اجرا و logical names را در MOVE جایگزین کنید؛ D:\\RestoreLab باید از قبل وجود داشته و SQL service account permission داشته باشد.

```sql
RESTORE FILELISTONLY FROM DISK=N'D:\\RestoreLab\\DB-Jira_FULL.bak';
```

پس از بررسی خروجی و نبود database هم‌نام، این batch اجرا شود. اگر backup بیش از دو data/log file دارد برای هر فایل MOVE اضافه کنید؛ تعداد فایل از خروجی قبلی تعیین می‌شود.

```sql
IF DB_ID(N'DB_Jira_RestoreTest') IS NOT NULL
    THROW 52000, 'Restore target already exists; choose a fresh isolated name.', 1;
RESTORE DATABASE DB_Jira_RestoreTest
FROM DISK=N'D:\\RestoreLab\\DB-Jira_FULL.bak'
WITH MOVE N'DB-Jira' TO N'D:\\RestoreLab\\DB_Jira_RestoreTest.mdf',
     MOVE N'DB-Jira_log' TO N'D:\\RestoreLab\\DB_Jira_RestoreTest_log.ldf',
     CHECKSUM, RECOVERY, STATS=10;
DBCC CHECKDB (N'DB_Jira_RestoreTest') WITH NO_INFOMSGS, ALL_ERRORMSGS;
```

این نمونه FULL-only است و PITR نیست. برای PITR از FULL و DIFF سازگار با NORECOVERY، سپس تمام LOGهای پیوسته تا STOPAT هدف و RECOVERY استفاده شود؛ نسخه و chain در بخش اصلی مقاله توضیح داده شده‌اند. CHECKDB بدون error، query کاربردی مجاز و مدت واقعی restore معیار پذیرش‌اند. encryption/TDE certificate پیش از restore روی مقصد با key امن import شود.''',
'Cleanup فقط پس از اثبات backup پایه محافظت‌شده و تمام chain retention انجام شود. برای مورد کوچک این مقاله script اختصاصی whitelist حفظ شده؛ در fleet بزرگ راهکار رسمی سازمانی با coverage همان edition بررسی شود.',
'فایل backup از SQL جدیدتر به engine قدیمی‌تر restore نمی‌شود. Agent در Express وجود ندارد. scriptهای موجود و metadata/FAQ هشت‌گانه مقاله حفظ شده‌اند.',
[('Microsoft: restore overview','https://learn.microsoft.com/en-us/sql/relational-databases/backup-restore/restore-and-recovery-overview-sql-server'),('Microsoft: VERIFYONLY','https://learn.microsoft.com/en-us/sql/t-sql/statements/restore-statements-verifyonly-transact-sql'),('Microsoft: backup encryption','https://learn.microsoft.com/en-us/sql/relational-databases/backup-restore/backup-encryption')],
[['آیا VERIFYONLY جای restore واقعی است؟','خیر؛ restore روی instance ایزوله، CHECKDB و آزمون کاربردی لازم‌اند.'],['آیا سبز بودن Agent Job کافی است؟','خیر؛ تازگی و وضعیت backup هر database باید مستقل بررسی شود.']])

add('creating-a-bootable-usb','رسانه نصب سازمانی: ISO معتبر، UEFI و آزمون بازیابی سرور',3,
'موضوع: USB boot؛ هدف: نصب/recovery. سطح قبلی: مقدماتی. کمبود: زنجیره اعتماد ISO، کنترل انتخاب device و معیار restore.',
'Ubuntu 24.04 LTS یا Windows Server 2022/2025 با ISO رسمی؛ Rufus نسخه stable مصوب روی Windows 11. Media Creation Tool عمومی Windows client برای ISO Server جایگزین نیست.',
'USB با ظرفیت کافی نسبت به ISO و سازگار firmware؛ نمونه ۱۶ GiB، workstation آماده و سرور spare. مدل controller/NIC مقصد پیش از نصب تأیید شود.',
'Admin workstation برای write، دسترسی مجاز boot/OOB سرور، hash منتشرشده سازنده و backup داده USB. انتخاب device مقصد عملیات پاک‌کننده است.',
'یک USB خراب یا ISO نامعتبر در پنجره recovery می‌تواند RTO را از بین ببرد. رسانه سازمانی باید منشأ، hash، firmware mode و آزمون boot ثبت‌شده داشته باشد.',
'تیم دیتاسنتر رسانه نصب سرور spare را قبل از خرابی آماده می‌کند؛ روی label نام OS، build، hash و تاریخ آزمون boot درج می‌شود.',
'Vendor ISO → signature/checksum trust → media writer → firmware boot → installer/recovery. USB نصب، backup داده کاربردی نیست.',
'''۱. ISO از vendor و checksum از کانال رسمی معتبر دریافت شود. محاسبه hash روی workstation read-only است؛ مسیر placeholder به فایل موجود اشاره کند.

```powershell
$IsoPath = 'C:\\InstallMedia\\ubuntu-24.04-live-server-amd64.iso'
Get-Item -LiteralPath $IsoPath -ErrorAction Stop | Select-Object Name,Length
Get-FileHash -LiteralPath $IsoPath -Algorithm SHA256
Get-Disk | Select-Object Number,FriendlyName,SerialNumber,BusType,Size
```

hash باید دقیقاً با ناشر تطبیق کند؛ hash خودساخته فقط تغییر را می‌سنجد و اعتماد منبع را ثابت نمی‌کند.

۲. در Rufus، USB با serial/size واقعی انتخاب، ISO معرفی و firmware mode مقصد بررسی شود. برای UEFI معمولاً GPT مناسب است؛ انتخاب ISO/DD mode طبق راهنمای distribution انجام شود. Windows Server از ISO و فرآیند رسمی Server استفاده کند.

۳. پیش از Start، operator دوم یا کنترل معتبر change نام device را تأیید کند؛ تمام داده USB پاک می‌شود. پس از پایان و eject، روی spare از boot menu موقت USB را انتخاب کنید.

۴. Secure Boot اگر image و chain آن را پشتیبانی می‌کنند فعال بماند. رسیدن به installer، شناسایی storage/NIC و دسترسی recovery به backup test معیار پذیرش‌اند؛ نصب روی دیسک Production در این آزمون انجام نشود.''',
'ISO/USB فقط در زنجیره تحویل مجاز نگهداری شود. password و unattended secret روی USB عمومی قرار نگیرد. boot از USB برای سرورهای حساس با کنترل OOB و audit باشد؛ خاموش‌کردن Secure Boot تصمیم پیش‌فرض نیست.',
'سرویس دائمی ندارد؛ media inventory، تاریخ آخرین boot test و recall نسخه معیوب در CMDB ثبت شود. مانیتورینگ Zabbix برای USB نصب ضرورت فنی ندارد؛ alert انقضای image یا review policy در مدیریت دارایی انجام شود.',
'USB در boot menu نیست: port، firmware mode و رسانه. storage دیده نمی‌شود: OEM driver و controller mode/HCL؛ دیسک را format نکنید. Secure Boot violation: signature/image compatibility؛ hash و vendor documentation. Windows فایل بزرگ روی FAT32: writer و workflow رسمی image را بررسی کنید؛ تبدیل دلخواه partition راه‌حل عمومی نیست.',
'رسانه بازسازی‌پذیر است؛ ISO مصوب، checksum، writer version و تنظیمات در artifact store نگهداری شوند. recovery واقعی به backup workload، key رمزنگاری و driver معتبر نیاز دارد. دسترسی backup از محیط recovery پیش از حادثه تست شود.',
'از boot test روی spare و روی model مشابه استفاده کنید. USB recovery با رسانه نصب و backup داده سه artifact جدا هستند.',
'Legacy BIOS/MBR فقط برای نیاز واقعی سیستم قدیمی است. Media Creation Tool نسخه client معادل دریافت Windows Server نیست؛ روش ISO vendor حفظ شود.',
[('Canonical: bootable USB','https://documentation.ubuntu.com/desktop/en/24.04/how-to/create-a-bootable-usb-stick/'),('Rufus official','https://rufus.ie/'),('Microsoft: Windows Server installation','https://learn.microsoft.com/en-us/windows-server/get-started/install-windows-server')],
[['آیا USB نصب همان backup سرور است؟','خیر؛ فقط ابزار نصب یا recovery است و داده کاربردی جدا backup می‌شود.'],['آیا باید Secure Boot را خاموش کرد؟','فقط برای ناسازگاری مستند و تصمیم مجاز؛ در حالت سازگار فعال بماند.']])

add('http-vs-https-ssl-certificate-impact','HTTPS در Production: TLS، Certificate Chain و Rollout امن HSTS',2,
'موضوع: HTTP/TLS؛ هدف: امنیت انتقال. سطح قبلی: مقدماتی. کمبود: chain validation، renewal probe و rollback HSTS.',
UBUNTU+' OpenSSL 3.x، curl و Nginx از repository؛ حداقل TLS 1.2 و پشتیبانی TLS 1.3 مطابق client policy.',LINUXHW,
'DNS app.example.com، CA داخلی یا عمومی معتبر، certificate شامل SAN، fullchain و key با permission محدود. sudo فقط برای config proxy.',
'گواهی منقضی یا chain ناقص می‌تواند API و automation را هم‌زمان قطع کند. HTTPS علاوه بر رمزنگاری، تأیید هویت مقصد و سلامت chain را به lifecycle گواهی وابسته می‌کند.',
'API سازمان پشت TLS proxy است؛ browser و jobهای Linux از trust store متفاوت استفاده می‌کنند. rollout با کلاینت canary و سپس HSTS کوتاه آغاز می‌شود.',
'DNS/SNI → TLS handshake → chain/hostname verification → HTTP request → backend. TLS termination باید مرز اعتماد proxy تا backend را نیز مشخص کند.',
'''app.example.com placeholder مقصد واقعی است. ابتدا certificate و chain بدون bypass تست شوند.

```bash
HOST='app.example.com'
openssl s_client -connect "$HOST:443" -servername "$HOST" -verify_hostname "$HOST" -verify_return_error </dev/null
curl --fail --show-error --head "https://$HOST/"
```

انتظار Verify return code: 0 (ok)، hostname صحیح و status مورد انتظار endpoint. برای CA داخلی به‌جای -k، CA مصوب را به trust store یا --cacert فایل معتبر بدهید.

config کامل TLS و redirect در مقاله Nginx این مجموعه آمده است. پس از استقرار آن، فقط در server block HTTPS این directive کامل افزوده شود؛ این snippet مستقل config کامل Nginx نیست.

```nginx
add_header Strict-Transport-Security "max-age=300" always;
```

```bash
sudo nginx -t
sudo systemctl reload nginx
curl --fail --head https://app.example.com
```

header باید فقط روی HTTPS و max-age کوتاه دیده شود. پس از اطمینان از تمام hostnameها و renewal، مدت افزایش یابد. includeSubDomains و preload فقط پس از inventory کامل و برنامه recovery انتخاب شوند.''',
'SSL نام رایج قدیمی است؛ SSLv2/v3 نباید baseline باشد. private key root-only و chain معتبر. از 0-RTT برای درخواست state-changing بدون تحلیل replay استفاده نشود. redirect با hostname ثابت مانع اعتماد بی‌جا به Host ورودی می‌شود.',
'پایش certificate از مسیر واقعی کاربر شامل SAN، chain و expiry باشد؛ فایل روی disk شاید با certificate سرو شده متفاوت باشد. probe TLS و آزمون API هر دو؛ هشدار expiry در ۳۰/۱۴/۷ روز نمونه است.',
'Expired: زمان واقعی client/server و notAfter. hostname mismatch: DNS/SAN/SNI. issuer unknown: intermediate و CA trust. curl موفق ولی browser خطا: mixed content، cache/HSTS یا trust store متفاوت. Error در upstream HTTP الزاماً TLS client نیست. openssl بدون -verify_return_error ممکن است handshake را با هشدار ادامه دهد؛ نتیجه را درست تفسیر کنید.',
'key/cert و config به vault امن backup شوند؛ chain قدیمی معتبر برای rollback deploy قابل نگهداری است. بعد renewal reload و remote probe لازم است. HSTS در client cache می‌شود؛ max-age=0 فقط روی HTTPS معتبر policy را حذف می‌کند و recovery فوری همه کلاینت‌ها را تضمین نمی‌کند. preload حذف فرآیند و زمان مستقل دارد.',
'Automation renewal، canary trust test و مالک گواهی تعریف شود. -k برای acceptance test استفاده نشود. HTTPS به‌تنهایی امنیت application یا endpoint آلوده را حل نمی‌کند.',
'TLS 1.3 با TLS 1.2 رفتار cipher و handshake متفاوت دارد؛ policy clientهای قدیمی با منبع رسمی تطبیق شود. HSTS نیاز HTTPS فعال معتبر دارد؛ redirect جای HSTS نیست.',
[('IETF: TLS 1.3','https://www.rfc-editor.org/rfc/rfc8446.html'),('IETF: HSTS','https://www.rfc-editor.org/rfc/rfc6797.html'),('Nginx: HTTPS','https://nginx.org/en/docs/http/configuring_https_servers.html')],
[['آیا redirect جای HSTS است؟','خیر؛ HSTS policy مرورگر را پس از دریافت از HTTPS معتبر تعیین می‌کند.'],['چرا گواهی معتبر روی disk کافی نیست؟','سرویس ممکن است certificate قبلی را serve کند؛ probe از مسیر واقعی client لازم است.']])

add('imap-vs-pop3-email-protocol-comparison','انتخاب IMAP و POP3 سازمانی: TLS، OAuth و جلوگیری از فقدان ایمیل',3,
'موضوع: email protocol؛ هدف: انتخاب client. سطح قبلی: مقدماتی. کمبود: OAuth، mailbox retention، اثر delete و تست امن TLS.',
'Client دارای IMAP4/POP3 و OAuth سازگار با provider؛ OpenSSL 3.x برای تشخیص. Exchange Online با سیاست جاری سازمان؛ رفتار Exchange Server on-prem جدا بررسی شود.',
'workstation موجود؛ mailbox quota، local cache و storage archive بر اساس retention تعیین شود. این مقاله نصب mail server جدید نیست.',
'اجازه فعال‌سازی protocol توسط mail admin و test mailbox؛ certificate معتبر و egress TCP/993 یا 995. SMTP submission برای ارسال مستقل است.',
'POP3 با سیاست حذف محلی می‌تواند نامه را از دید دستگاه‌های دیگر خارج کند؛ IMAP نیز با حذف همگام‌شده backup نیست. انتخاب باید با retention و authentication سازمان هم‌خوان باشد.',
'کاربر موبایل و دسکتاپ باید folder و read state مشترک داشته باشد؛ IMAP انتخاب می‌شود. یک workflow قدیمی POP3 قبل migration با mailbox آزمایشی و retention بررسی می‌شود.',
'Client ↔ IMAP mailbox state؛ POP3 → download/optional delete؛ ارسال از SMTP submission مستقل. OAuth مربوط به authentication provider است و TLS صرفاً انتقال را حفاظت می‌کند.',
'''۱. protocol در سطح tenant و mailbox با policy مجاز بررسی شود؛ فعال‌سازی POP/IMAP برای همه کاربران پیش‌فرض نیست. provider ممکن است Basic Auth را پشتیبانی نکند.

۲. hostnameهای زیر placeholder server سازمان‌اند. قبل واردکردن credential، chain و hostname را validate کنید.

```bash
MAILHOST='mail.example.com'
openssl s_client -connect "$MAILHOST:993" -servername "$MAILHOST" -verify_hostname "$MAILHOST" -verify_return_error -crlf
```

پس از banner از commandهای بدون credential استفاده کنید؛ مثال transcript زیر فقط IMAP است.

```text
a001 CAPABILITY
a002 LOGOUT
```

انتظار response tagged OK و capabilityهای اعلام‌شده server. برای POP3S تست TLS مستقل اجرا شود:

```bash
openssl s_client -connect "$MAILHOST:995" -servername "$MAILHOST" -verify_hostname "$MAILHOST" -verify_return_error -crlf
```

پس از banner، CAPA و سپس QUIT وارد شود. در client مصوب، OAuth sign-in و mailbox تست را تنظیم کنید؛ ایمیل آزمایشی دریافت، read state و folder sync بین دو device و ارسال از submission مجاز آزموده شود. موفقیت TLS login موفق را اثبات نمی‌کند.''',
'Basic Auth و app password را بدون پشتیبانی و policy مجاز توصیه نکنید؛ OAuth/MFA provider را رعایت کنید. plaintext 110/143 بدون STARTTLS اجباری استفاده نشود. TLS/993 و 995 implicit TLS هستند؛ 587 STARTTLS submission با policy provider.',
'auth failure، mailbox quota، delivery delay و client sync error پایش شوند. تست synthetic با mailbox اختصاصی و کم‌اختیار باشد؛ credential در dashboard قرار نگیرد. monitoring MTA و IMAP/POP scope متفاوت دارد.',
'TLS سالم ولی login رد: protocol disabled، OAuth scope یا policy account. نامه روی device نیست: folder subscription، POP delete policy و cache. ارسال رد ولی دریافت سالم: SMTP submission و auth ارسال جدا. قبل پاک‌کردن profile، local-only mailbox/PST را backup کنید.',
'IMAP synchronization یا POP leave-on-server جای backup و retention نیستند. mailbox backup/retention/legal hold باید با provider و الزام سازمان تعریف شود؛ local-only email قبل migration export امن شود. restore آزمایشی پیام به mailbox ایزوله با header/date/attachment بررسی شود.',
'برای چند device، IMAP یا client رسمی provider معمولاً sync مناسب‌تری دارد؛ محدودیت provider و OAuth معیار انتخاب‌اند. retention باید server-side و مستقل از رفتار client تعریف شود.',
'POP3 همیشه پیام را حذف نمی‌کند؛ تنظیم client و server تعیین‌کننده است. SMTP مسئول ارسال است، نه IMAP. سیاست Exchange Online را به Exchange on-prem تعمیم ندهید.',
[('Microsoft: POP/IMAP Exchange Online','https://learn.microsoft.com/en-us/exchange/clients-and-mobile-in-exchange-online/pop3-and-imap4/pop3-and-imap4'),('Microsoft: OAuth POP/IMAP/SMTP','https://learn.microsoft.com/en-us/exchange/client-developer/legacy-protocols/how-to-authenticate-an-imap-pop-smtp-application-by-using-oauth')],
[['آیا IMAP backup ایمیل است؟','خیر؛ حذف همگام می‌شود و retention/backup مستقل لازم است.'],['آیا دریافت سالم یعنی ارسال سالم است؟','خیر؛ SMTP submission مسیر و authentication جدا دارد.']])

if __name__ == '__main__':
    descriptions = {
        'enable-ssh-linux-complete-guide': 'پیکربندی SSH با کلید، دسترسی از bastion، Security Hardening، بررسی تنظیم مؤثر، آزمون ورود مستقل و بازیابی اتصال در محیط Production.',
        'set-static-ip-ubuntu-server-netplan': 'تنظیم IP ثابت با Netplan در Ubuntu؛ کنترل renderer و مالک تنظیمات، اعمال با rollback زمان‌دار و اعتبارسنجی route، DNS و اتصال مدیریت.',
        'linux-security-account-access-management': 'مدیریت حساب‌های Linux با sudo محدود، چرخه ورود و خروج کارکنان، حفظ مالکیت فایل، ثبت رویداد و بازیابی دسترسی مجاز.',
        'nginx-installation-configuration-ubuntu': 'استقرار Nginx به‌عنوان Reverse Proxy با TLS، بررسی syntax پیش از reload، پایش upstream و عیب‌یابی خطاهای اتصال در Production.',
        'ubuntu-date-time-settings': 'همگام‌سازی زمان Ubuntu با Chrony؛ انتخاب منابع NTP، تحلیل offset و Reach، پایش سلامت و کنترل اثر تغییر ساعت بر سرویس‌ها.',
        'linux-cli-common-commands': 'عیب‌یابی رخداد Linux با شواهد زمان‌دار؛ بررسی CPU، حافظه، دیسک، inode، سرویس و شبکه پیش از تغییر وضعیت Production.',
        'downgrade-mikrotik-routeros-firmware-safely': 'Downgrade امن RouterOS با کنترل architecture و نسخه مجاز، Backup رمزنگاری‌شده، دسترسی console و آزمون routing و VPN پس از reboot.',
        'mikrotik-block-port-scanners': 'تشخیص Port Scan در MikroTik با whitelist مدیریت، مسدودسازی موقت، بررسی ترتیب ruleها و کنترل false positive در Firewall.',
        'mikrotik-block-website': 'کنترل دسترسی وب در MikroTik با DNS و TLS SNI؛ آزمون محدودیت‌های ECH، QUIC، DoH، FastTrack و مسیر IPv6.',
        'mikrotik-openvpn-setup-v7': 'راه‌اندازی OpenVPN در RouterOS v7 با PKI، گواهی اختصاصی، تنظیم سازگار کلاینت، مسیر برگشت و آزمون دسترسی مجاز و غیرمجاز.',
        'mikrotik-unequal-dual-wan-load-balancing-ecmp': 'طراحی Dual-WAN وزنی با PCC در RouterOS v7؛ routing table، مسیر پاسخ، FastTrack، پایش هر لینک و آزمون failover اتصال‌های تازه.',
        'windows-cmd-common-network-commands': 'عیب‌یابی شبکه Windows از DNS و route تا TCP و SMB؛ ثبت شواهد پیش از پاک‌کردن cache و تفکیک اتصال از مجوز دسترسی.',
        'windows-hardware-info-cmd-vs-dxdiag': 'جمع‌آوری Inventory سخت‌افزار Windows با CIM و Storage، خروجی JSON زمان‌دار، تطبیق CMDB و تشخیص محدودیت DxDiag و RAID.',
        'windows-password-reset-secure-access-recovery': 'بازیابی مجاز حساب Windows با تأیید هویت، delegation محدود، کنترل وابستگی‌ها، تحویل امن credential و ثبت رویدادهای reset.',
        'install-mikrotik-chr-vmware-workstation': 'ساخت آزمایشگاه CHR روی VMware Workstation با VMnet مجزا، Firewall مدیریت، آزمون اتصال و اندازه‌گیری ظرفیت با توجه به license.',
        'install-vmware-esxi-vmware-workstation-vmcisr': 'استقرار Nested ESXi در آزمایشگاه جدا؛ بررسی قابلیت CPU، شبکه و datastore و تشخیص خطای VMCI با log و KB همان build.',
        'vmware-esxi-8-installation-basic-configuration': 'استقرار ESXi 8 با بررسی HCL، firmware و driver، شبکه مدیریت، DNS و NTP، آزمون VM canary و Backup تنظیمات میزبان.',
        'vsphere-standard-switch-vs-distributed-switch': 'مقایسه vSS و vDS و مهاجرت مرحله‌ای شبکه vSphere؛ حفظ uplink مدیریت، آزمون VLAN و MTU و بازیابی از console مستقل.',
        'netbox-installation-setup-ubuntu': 'استقرار NetBox 4.7.2 روی Ubuntu با PostgreSQL، Redis و TLS؛ مجوز API محدود، پایش worker و بازیابی DB، media و secretها.',
        'oxidized-network-device-configuration-backup': 'Backup تنظیمات شبکه با Oxidized؛ حساب محدود، تاریخچه Git، پایش آخرین جمع‌آوری موفق هر node و آزمون restore روی دستگاه spare.',
        'install-dfs-server-windows-server': 'راه‌اندازی DFS-N و DFS-R در Windows Server با مجوزهای Share و NTFS، آزمون initial sync، پایش backlog و Backup مستقل فایل‌ها.',
        'sql-server-automatic-backup-job': 'عملیات Backup در SQL Server با Agent، کنترل تازگی FULL و DIFF و LOG، حفظ زنجیره Retention و آزمون Restore مستقل.',
        'creating-a-bootable-usb': 'آماده‌سازی رسانه boot سازمانی با ISO رسمی و hash معتبر، تنظیم firmware، تأیید USB مقصد و آزمون نصب و recovery روی سرور spare.',
        'http-vs-https-ssl-certificate-impact': 'مدیریت HTTPS در Production؛ بررسی SAN و chain گواهی، renewal و پایش expiry، rollout تدریجی HSTS و تحلیل خطا از مسیر واقعی کلاینت.',
        'imap-vs-pop3-email-protocol-comparison': 'انتخاب IMAP و POP3 بر اساس sync، OAuth و retention؛ اعتبارسنجی TLS، آزمون چند دستگاه و بازیابی ایمیل مستقل از رفتار کلاینت.',
    }
    for slug, description in descriptions.items():
        DATA[slug]['description'] = description
        DATA[slug]['keywords'] = [DATA[slug]['title'], 'مدیریت زیرساخت', 'عیب‌یابی', 'امنیت سرویس', 'بازیابی', slug.replace('-', ' ')]
    assert len(DATA) == 25, len(DATA)
    target = Path(__file__).resolve().parents[1] / 'docs/enterprise-articles/runbooks.json'
    target.write_text(json.dumps(DATA,ensure_ascii=False,indent=2),encoding='utf-8')
    print('Prepared',len(DATA),'curated runbooks')
