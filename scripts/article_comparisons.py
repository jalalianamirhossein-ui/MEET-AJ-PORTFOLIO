"""Editorial corrections that retain the comparison topics of the original articles."""

def pair(fa, en):
    return {'fa': fa, 'en': en}

ARTICLES = {
    'vsphere-standard-switch-vs-distributed-switch': {
        'title': pair('مقایسه vSphere Standard Switch و Distributed Switch؛ قابلیت‌ها و معیار انتخاب', 'vSphere Standard Switch vs Distributed Switch: Features and Design Choices'),
        'description': pair('مقایسه vSS و vDS در مدیریت، VLAN، Traffic Shaping، LLDP و Port Group؛ معیار انتخاب برای ESXi مستقل و Cluster، همراه با ملاحظات مهاجرت.', 'Compare vSS and vDS management, VLANs, traffic shaping, LLDP and port groups, then choose a design and validate migration without losing management access.'),
        'intro': pair('هر دو Switch، VM و VMkernel را به شبکه متصل می‌کنند؛ تفاوت اصلی در محل مدیریت Configuration و قابلیت‌های شبکه است. در Cluster، یکسان نبودن VLAN یا Policy بین Hostها هنگام جابه‌جایی VM مشکل ایجاد می‌کند. این مقاله ابتدا vSS و vDS را مقایسه می‌کند و سپس روش انتخاب، بررسی Configuration و ملاحظات مهاجرت را توضیح می‌دهد.', 'Both switch types connect VMs and VMkernel adapters to the network. Their management model and feature set differ. In a cluster, inconsistent VLANs or policies can break connectivity after a VM moves. This comparison explains those differences, selection criteria and configuration checks before covering migration.'),
        'scenario': pair('در یک محیط نمونه با شش ESXi Host، VLAN برنامه‌ها مشترک است اما Policy بعضی Port Groupها بین Hostها تفاوت دارد. برای این Cluster مدیریت متمرکز vDS بررسی می‌شود. در مقابل، یک ESXi مستقل برای سایت کوچک با VLAN و NIC Teaming معمولی می‌تواند روی vSS بماند؛ تعداد Host به‌تنهایی دلیل مهاجرت نیست.', 'Consider six ESXi hosts sharing application VLANs but carrying inconsistent port-group policies. Central management through vDS is a candidate for this cluster. A standalone host with ordinary VLAN and NIC teaming requirements may remain on vSS. Host count alone does not determine the choice.'),
        'source': ['Broadcom: vSS/vDS concepts and feature comparison', 'https://knowledge.broadcom.com/external/article/324515/overview-of-vnetwork-distributed-switch.html'],
        'faq': [
            pair(['آیا vSS از VLAN و NIC Teaming پشتیبانی می‌کند؟', 'بله؛ این دو قابلیت در هر دو Switch وجود دارند. تفاوت‌های مهم vDS شامل مدیریت متمرکز، LLDP، Private VLAN و Traffic Shaping ورودی است.'], ['Does vSS support VLANs and NIC teaming?', 'Yes. Both switch types support these capabilities. Key vDS differences include centralized management, LLDP, private VLANs and inbound traffic shaping.']),
            pair(['آیا برای هر Cluster باید vDS انتخاب شود؟', 'خیر؛ نیاز به قابلیت، کنترل Consistency، Entitlement و مسیر Recovery تصمیم را تعیین می‌کنند. vSS با Configuration هماهنگ نیز در Cluster قابل استفاده است.'], ['Does every cluster require vDS?', 'No. Feature requirements, configuration consistency, entitlement and recovery access determine the choice. A cluster can also use consistently configured standard switches.']),
            pair(['آیا قطع vCenter ترافیک موجود vDS را متوقف می‌کند؟', 'معمولاً Forwarding موجود روی Host ادامه دارد؛ تغییر Configuration و بعضی عملیات بازیابی محدود می‌شوند. دسترسی OOB و مسیر بازیابی مستقل باید آماده باشد.'], ['Does a vCenter outage stop existing vDS traffic?', 'Existing host forwarding generally continues, while configuration changes and some recovery operations are restricted. Prepare OOB access and an independent recovery path.']),
        ],
        'sections': [
            {'id': 'switch-types', 'title': pair('vSS و vDS چگونه کار می‌کنند؟', 'How vSS and vDS Work'), 'paragraphs': [
                pair('vSS روی هر ESXi Host Configuration مستقل دارد. Port Group، VLAN و Policy باید روی Hostهای مقصد یکسان تعریف شوند؛ یکسان بودن نام Port Group به‌تنهایی یکسان بودن تنظیمات را ثابت نمی‌کند.', 'A vSS has an independent configuration on each ESXi host. Destination hosts need matching port groups, VLANs and policies. Matching port-group names alone do not prove matching settings.'),
                pair('vDS در vCenter مدیریت می‌شود و Hostهای عضو، بخش اجرایی شبکه را نگه می‌دارند. Distributed Port Group سیاست مشترک پورت‌ها را تعریف می‌کند. قطع vCenter معمولاً Forwarding موجود را متوقف نمی‌کند، اما تغییر Configuration و بعضی عملیات بازیابی را محدود می‌کند.', 'A vDS is managed through vCenter while member hosts maintain the forwarding components. Distributed port groups define shared port policies. A vCenter outage generally leaves existing forwarding running, while restricting configuration changes and some recovery operations.')
            ]},
            {'id': 'switch-comparison', 'title': pair('جدول مقایسه vSS و vDS', 'vSS vs vDS Comparison'), 'headers': [pair('معیار', 'Criterion'), pair('Standard Switch — vSS', 'Standard Switch — vSS'), pair('Distributed Switch — vDS', 'Distributed Switch — vDS')], 'rows': [
                [pair('مدیریت Configuration', 'Configuration management'), pair('مستقل روی هر Host', 'Independent on each host'), pair('متمرکز در vCenter', 'Centralized in vCenter')],
                [pair('VLAN و 802.1Q', 'VLANs and 802.1Q'), pair('پشتیبانی می‌شود', 'Supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('چند Uplink و NIC Teaming', 'Multiple uplinks and NIC teaming'), pair('پشتیبانی می‌شود', 'Supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('Traffic Shaping خروجی', 'Outbound traffic shaping'), pair('پشتیبانی می‌شود', 'Supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('Traffic Shaping ورودی', 'Inbound traffic shaping'), pair('پشتیبانی نمی‌شود', 'Not supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('Private VLAN', 'Private VLAN'), pair('پشتیبانی نمی‌شود', 'Not supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('LLDP', 'LLDP'), pair('پشتیبانی نمی‌شود', 'Not supported'), pair('پشتیبانی می‌شود', 'Supported')],
                [pair('Port Group', 'Port group'), pair('Port Group محلی هر Host', 'Host-local port group'), pair('Distributed Port Group', 'Distributed port group')],
                [pair('یکسان‌سازی Policy', 'Policy consistency'), pair('به Automation یا کنترل هر Host نیاز دارد', 'Requires automation or per-host checks'), pair('Policy مشترک برای Hostهای عضو', 'Shared policy across member hosts')],
                [pair('وابستگی مدیریتی', 'Management dependency'), pair('مدیریت مستقیم از Host ممکن است', 'Direct host management is available'), pair('برای مدیریت مرکزی به vCenter نیاز دارد', 'Central management requires vCenter')],
            ], 'paragraphs': [pair('این جدول قابلیت فنی را مقایسه می‌کند؛ دسترسی به Featureها، نسخه vDS و Entitlement باید برای Build و قرارداد واقعی بررسی شود. وجود NIC Teaming به معنی پشتیبانی LACP در هر دو Switch نیست؛ طراحی LACP باید جداگانه بررسی شود.', 'This table compares technical capabilities. Check feature entitlement and vDS compatibility against the actual build and contract. NIC teaming does not imply LACP support on both switch types; validate that design separately.')]},
            {'id': 'port-groups-and-uplinks', 'title': pair('Port Group، Uplink و VMkernel', 'Port Groups, Uplinks and VMkernel'), 'paragraphs': [pair('Port Group سیاست اتصال VMها یا VMkernel را مشخص می‌کند؛ Uplink اتصال Switch مجازی به NIC فیزیکی است. vmnic نام NIC فیزیکی و vmk نام VMkernel Adapter است. نام vmk0 به‌تنهایی نوع سرویس را تعیین نمی‌کند؛ برچسب Management، vMotion یا Storage را در Configuration همان Host بررسی کنید.', 'Port groups define connection policies for VMs or VMkernel adapters. Uplinks connect virtual switches to physical NICs. ESXi names physical NICs vmnic and VMkernel adapters vmk. The name vmk0 alone does not identify a service; inspect the actual Management, vMotion or storage configuration.')]},
            {'id': 'switch-selection', 'title': pair('چه زمانی vSS یا vDS انتخاب کنیم؟', 'When to Choose vSS or vDS'), 'paragraphs': [pair('vSS برای Host مستقل، محیط کوچک با Policy ساده یا طراحی بدون vCenter مناسب است؛ مشروط به اینکه تیم بتواند Consistency را کنترل کند. vDS زمانی ارزش دارد که مدیریت متمرکز، Policy مشترک یا قابلیت‌هایی مانند LLDP، Private VLAN و Shaping ورودی نیاز واقعی باشند. پیچیدگی عملیات، Entitlement و مسیر Recovery نیز بخشی از تصمیم‌اند.', 'Choose vSS for independent hosts, simple policies or designs without vCenter, provided the team can maintain consistency. Choose vDS when centralized management, shared policies or features such as LLDP, private VLANs and inbound shaping meet a real requirement. Include operational complexity, entitlement and recovery access in the decision.'), pair('اگر نتیجه مقایسه به نفع vDS بود، مراحل Configuration و مهاجرت پایین را روی Host آزمایشی اجرا کنید. مهاجرت، پیامد انتخاب معماری است؛ شرط لازم برای استفاده از VLAN یا vMotion نیست.', 'If the comparison favors vDS, apply the configuration and migration procedure below to a canary host. Migration follows the architecture decision; it is not a prerequisite for using VLANs or vMotion.')]
            }
        ]
    },
    'http-vs-https-ssl-certificate-impact': {
        'title': pair('مقایسه HTTP و HTTPS؛ نقش TLS، گواهی و اثر عملی بر امنیت', 'HTTP vs HTTPS: TLS Certificates, Security and Operational Impact'),
        'description': pair('تفاوت HTTP و HTTPS در محرمانگی، اصالت سرور و یکپارچگی ارتباط؛ نقش گواهی TLS، محدودیت‌های HTTPS و روش اعتبارسنجی Chain و HSTS.', 'Compare HTTP and HTTPS confidentiality, integrity and server authentication; understand certificate limits, trust-chain validation and safe HSTS rollout.'),
        'sections': [{'id': 'http-https-comparison', 'title': pair('تفاوت HTTP و HTTPS در عمل', 'HTTP vs HTTPS in Practice'), 'headers': [pair('معیار', 'Criterion'), pair('HTTP', 'HTTP'), pair('HTTPS', 'HTTPS')], 'rows': [
            [pair('محرمانگی انتقال', 'Transport confidentiality'), pair('داده در مسیر رمزنگاری نمی‌شود', 'Traffic is not encrypted'), pair('انتقال با TLS محافظت می‌شود', 'TLS protects the connection')],
            [pair('اصالت سرور', 'Server authentication'), pair('خود پروتکل هویت سرور را اثبات نمی‌کند', 'The protocol does not authenticate the server'), pair('نام دامنه و زنجیره اعتماد گواهی بررسی می‌شود', 'Certificate names and trust chains are validated')],
            [pair('یکپارچگی انتقال', 'Transport integrity'), pair('TLS ندارد', 'No TLS protection'), pair('TLS دست‌کاری داده در مسیر را تشخیص می‌دهد', 'TLS detects modification in transit')],
            [pair('پورت متداول', 'Conventional port'), pair('80؛ الزام پروتکل نیست', '80; other ports are possible'), pair('443؛ الزام پروتکل نیست', '443; other ports are possible')],
            [pair('نیاز عملیاتی', 'Operational requirements'), pair('بدون مدیریت گواهی TLS', 'No TLS certificate lifecycle'), pair('Renewal، Trust Store و پایش Expiry', 'Renewal, trust stores and expiry monitoring')],
        ], 'paragraphs': [pair('HTTPS امنیت انتقال را فراهم می‌کند؛ آسیب‌پذیری برنامه، مجوز اشتباه یا سرقت حساب را برطرف نمی‌کند. گواهی معتبر نیز کیفیت یا قابل اعتماد بودن کسب‌وکار را اثبات نمی‌کند. اصطلاح SSL در نام قدیمی مقاله حفظ شده، اما پیاده‌سازی جاری باید TLS باشد.', 'HTTPS protects transport; it does not fix application vulnerabilities, incorrect permissions or account theft. A valid certificate does not establish the quality or legitimacy of a business. The original article uses the familiar term SSL, while current deployments use TLS.')] }],
        'source': ['IETF: HTTP and HTTPS semantics', 'https://www.rfc-editor.org/rfc/rfc9110.html']
    },
    'imap-vs-pop3-email-protocol-comparison': {
        'title': pair('مقایسه IMAP و POP3؛ همگام‌سازی، نگهداری پیام و انتخاب پروتکل', 'IMAP vs POP3: Synchronization, Retention and Protocol Selection'),
        'sections': [{'id': 'mail-protocol-comparison', 'title': pair('جدول مقایسه IMAP و POP3', 'IMAP vs POP3 Comparison'), 'headers': [pair('معیار', 'Criterion'), pair('IMAP', 'IMAP'), pair('POP3', 'POP3')], 'rows': [
            [pair('مدل دسترسی', 'Access model'), pair('کار با Mailbox و Folder روی سرور', 'Works with server mailboxes and folders'), pair('دریافت پیام از Maildrop', 'Retrieves messages from a maildrop')],
            [pair('چند دستگاه', 'Multiple devices'), pair('Folder و وضعیت پیام همگام می‌شود', 'Synchronizes folders and message state'), pair('همگام‌سازی Folder و وضعیت خواندن ندارد', 'No folder or read-state synchronization')],
            [pair('حذف و نگهداری', 'Deletion and retention'), pair('حذف می‌تواند روی همه Clientها دیده شود', 'Deletion can propagate to other clients'), pair('حفظ نسخه سرور به تنظیم Client و Policy وابسته است', 'Server-copy retention depends on client settings and policy')],
            [pair('TLS ضمنی', 'Implicit TLS'), pair('معمولاً TCP/993', 'Typically TCP/993'), pair('معمولاً TCP/995', 'Typically TCP/995')],
            [pair('ارسال ایمیل', 'Sending mail'), pair('SMTP سرویس جداگانه است', 'SMTP is a separate service'), pair('SMTP سرویس جداگانه است', 'SMTP is a separate service')],
        ], 'paragraphs': [pair('IMAP معمولاً برای کاربر چند دستگاه مناسب‌تر است؛ POP3 ممکن است برای Workflow قدیمی دریافت پیام لازم باشد. هیچ‌کدام به‌تنهایی Backup نیستند. پشتیبانی OAuth و محدودیت Authentication را برای Provider واقعی بررسی کنید؛ انتخاب پروتکل به معنی فعال بودن Basic Authentication نیست.', 'IMAP generally fits multi-device users; a legacy retrieval workflow may still require POP3. Neither protocol is a backup strategy. Check OAuth support and authentication restrictions for the actual provider; selecting a protocol does not imply that Basic Authentication is available.')] }],
        'source': ['Microsoft: POP3 and IMAP4 behavior', 'https://learn.microsoft.com/en-us/exchange/clients-and-mobile-in-exchange-online/pop3-and-imap4/pop3-and-imap4']
    },
    'windows-hardware-info-cmd-vs-dxdiag': {
        'title': pair('مقایسه CMD، PowerShell و DxDiag برای شناسایی سخت‌افزار Windows', 'Windows Hardware Information: CMD vs PowerShell vs DxDiag'),
        'source': ['Microsoft: WMIC deprecation and PowerShell replacement', 'https://learn.microsoft.com/en-us/windows/win32/wmisdk/wmic'],
        'sections': [{'id': 'inventory-tool-comparison', 'title': pair('کدام ابزار برای کدام بررسی؟', 'Which Tool Fits the Investigation?'), 'headers': [pair('ابزار', 'Tool'), pair('کاربرد مناسب', 'Best fit'), pair('محدودیت', 'Limit')], 'rows': [
            [pair('CMD / systeminfo', 'CMD / systeminfo'), pair('نمای کلی OS، CPU و RAM در جلسه پشتیبانی', 'OS, CPU and RAM overview during support'), pair('متن خروجی برای Inventory ساختاریافته مناسب نیست', 'Text output is awkward for structured inventory')],
            [pair('PowerShell / CIM', 'PowerShell / CIM'), pair('Inventory قابل Automation و خروجی JSON', 'Automated inventory and JSON exports'), pair('دقت اطلاعات به Provider و مجوز وابسته است', 'Accuracy depends on providers and permissions')],
            [pair('DxDiag', 'DxDiag'), pair('بررسی DirectX، گرافیک، صدا و Driver', 'DirectX, graphics, audio and driver investigation'), pair('جایگزین CMDB یا وضعیت RAID نیست', 'Does not replace CMDB or RAID health checks')],
        ], 'paragraphs': [pair('CMD یک Shell است، نه API سخت‌افزار؛ ابزار اجراشده در آن منبع اطلاعات را تعیین می‌کند. WMIC در نسخه‌های جدید Windows ممکن است حذف یا غیرفعال باشد؛ برای Automation جدید از CIM استفاده کنید. روی Server Core وجود DxDiag را فرض نکنید.', 'CMD is a shell, not a hardware API; the utility it runs determines the information source. WMIC may be absent or disabled on newer Windows versions, so use CIM for new automation. Do not assume DxDiag is present on Server Core.')] }]
    },
    'mikrotik-unequal-dual-wan-load-balancing-ecmp': {
        'title': pair('Dual-WAN نامتقارن در MikroTik؛ تفاوت ECMP و PCC و طراحی Failover', 'MikroTik Unequal Dual-WAN: ECMP vs PCC and Failover Design'),
        'sections': [{'id': 'ecmp-pcc-comparison', 'title': pair('چرا مثال اجرایی از PCC استفاده می‌کند؟', 'Why the Configuration Uses PCC'), 'headers': [pair('معیار', 'Criterion'), pair('ECMP', 'ECMP'), pair('PCC', 'PCC')], 'rows': [
            [pair('مکانیزم', 'Mechanism'), pair('انتخاب مسیر بین Next Hopهای هم‌هزینه', 'Route selection among equal-cost next hops'), pair('تقسیم Connectionها با Hash و Connection Mark', 'Connection classification using hashes and marks')],
            [pair('کنترل توزیع', 'Distribution control'), pair('وابسته به Routing و Hash پیاده‌سازی', 'Depends on routing and implementation hashing'), pair('قابل طراحی با Bucketهای Classifier', 'Designed through classifier buckets')],
            [pair('مسیر پاسخ', 'Return paths'), pair('برای Sessionهای NAT نیازمند بررسی مستقل است', 'NAT session return paths need separate validation'), pair('با Connection Mark و Routing Mark کنترل می‌شود', 'Controlled through connection and routing marks')],
        ], 'paragraphs': [pair('موضوع نسخه قدیمی، Load Balancing نامتقارن با Failover بود و ECMP را راهکار معرفی می‌کرد. این هدف حفظ شده است؛ مثال RouterOS v7 برای تقسیم ۲ به ۱ از PCC استفاده می‌کند تا نسبت Bucket و مسیر پاسخ صریح باشد. نسبت Connectionها تضمین نسبت پهنای باند نیست؛ رفتار Hash، NAT و Failover باید روی همان نسخه آزموده شود.', 'The original topic was unequal load balancing with failover using ECMP. That goal is retained. The RouterOS v7 configuration uses PCC for an explicit 2:1 bucket allocation and return-path policy. A connection ratio does not guarantee a bandwidth ratio; test hashing, NAT and failover on the target version.')] }],
        'source': ['MikroTik: Per Connection Classifier', 'https://help.mikrotik.com/docs/spaces/ROS/pages/152600617/Per+connection+classifier']
    }
}

def apply_editorial(data, locale):
    for slug, item in ARTICLES.items():
        for field in ['title', 'description', 'intro', 'scenario']:
            if field in item:
                data[slug][field] = item[field][locale]
        if locale == 'fa' and 'source' in item and item['source'] not in data[slug]['sources']:
            data[slug]['sources'].append(item['source'])
        data[slug]['keywords'][0] = data[slug]['title']
        if 'faq' in item:
            data[slug]['faq'] = [entry[locale] for entry in item['faq']]

def render_sections(slug):
    from html import escape
    def element(tag, value):
        return '<'+tag+' data-fa="'+escape(value['fa'], quote=True)+'" data-en="'+escape(value['en'], quote=True)+'">'+escape(value['fa'])+'</'+tag+'>'
    sections = []
    for section in ARTICLES.get(slug, {}).get('sections', []):
        content = element('h2', section['title'])
        if 'rows' in section:
            content += '<div class="table-responsive"><table class="table"><thead><tr>'+''.join(element('th', v) for v in section['headers'])+'</tr></thead><tbody>'
            content += ''.join('<tr>'+''.join(element('td', v) for v in row)+'</tr>' for row in section['rows'])+'</tbody></table></div>'
        content += ''.join(element('p', v) for v in section.get('paragraphs', []))
        sections.append('<section id="'+section['id']+'" class="article-section">'+content+'</section>')
    return sections
