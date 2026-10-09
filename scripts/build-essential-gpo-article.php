<?php
// Maintained bilingual editorial source; uses the existing article importer and UI.
$root = dirname(__DIR__);
$slug = '10-essential-group-policies-windows-domain';
$title = ['en' => '10 Essential Group Policies Every Organization Needs', 'fa' => '۱۰ Group Policy ضروری برای امنیت Windows Domain'];
$description = ['en' => 'Production guidance for ten enterprise Windows GPOs: exact paths, settings, Windows LAPS, Defender, WSUS, staged deployment, verification and troubleshooting.', 'fa' => 'راهنمای عملی ۱۰ GPO ضروری Windows Domain؛ مسیر دقیق تنظیمات، Windows LAPS، Defender، Firewall، WSUS، Audit، تست، عیب‌یابی و استقرار مرحله‌ای.'];
$keywords = ['Group Policy', 'GPO Hardening', 'Active Directory Security', 'Windows Server 2022', 'Windows Server 2025', 'Windows LAPS', 'Defender Antivirus', 'WSUS', 'RDP Hardening', 'Advanced Audit Policy'];
$parts = []; $toc = [];
function esc(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function dual(string $tag, string $en, string $fa, string $attrs = ''): string {
    if (in_array($tag, ['a', 'figcaption'], true)) return '<'.$tag.' '.$attrs.'>'.dual('span', $en, $fa).'</'.$tag.'>';
    return '<'.$tag.' '.$attrs.' data-en="'.esc($en).'" data-fa="'.esc($fa).'">'.esc($en).'</'.$tag.'>';
}
function paragraph(string $en, string $fa): void { global $parts; $parts[] = dual('p', $en, $fa); }
function heading(string $en, string $fa): void { global $parts; $parts[] = dual('h3', $en, $fa); }
function section(string $id, string $en, string $fa): void {
    global $parts, $toc; if ($toc) $parts[] = '</section>';
    $toc[] = [$id, $en, $fa]; $parts[] = '<section id="'.$id.'">'.dual('h2', $en, $fa);
}
function code(string $text, string $language = 'text'): void { global $parts; $parts[] = '<pre dir="ltr"><code class="language-'.$language.'">'.esc(trim($text)).'</code></pre>'; }
function note(string $en, string $fa): void { global $parts; $parts[] = '<div class="article-callout article-callout--warning" role="note">'.dual('p', $en, $fa).'</div>'; }
function table(array $headers, array $rows): void {
    global $parts; $out = '<table><thead><tr>';
    foreach ($headers as $cell) $out .= dual('th', $cell[0], $cell[1]);
    $out .= '</tr></thead><tbody>';
    foreach ($rows as $row) { $out .= '<tr>'; foreach ($row as $cell) { if (is_string($cell)) $cell = [$cell, $cell]; $out .= dual('td', $cell[0], $cell[1]); } $out .= '</tr>'; }
    $parts[] = $out.'</tbody></table>';
}
function checklist(array $items): void { global $parts; $out = '<ul>'; foreach ($items as [$en,$fa]) $out .= dual('li', '☐ '.$en, '☐ '.$fa); $parts[] = $out.'</ul>'; }
function source(string $url, string $label): void { global $sources; $sources[$url] = $label; }
function figure(string $name, string $en, string $fa): void {
    global $parts, $root; $path = '/assets/img/articles/content/'.$name; $src = $root.'/resources'.$path;
    if (!is_file($src)) throw new RuntimeException('Missing image: '.$src);
    $dest = $root.'/public'.$path; if (!is_dir(dirname($dest))) mkdir(dirname($dest),0775,true); copy($src,$dest);
    [$w,$h] = getimagesize($src);
    $parts[] = '<figure><img src="'.esc($path).'" alt="'.esc($en).'" width="'.$w.'" height="'.$h.'" loading="lazy" decoding="async">'.dual('figcaption',$en,$fa).'</figure>';
}
$computer = "Computer Configuration\n→ Policies\n→ Administrative Templates\n";
$security = "Computer Configuration\n→ Policies\n→ Windows Settings\n→ Security Settings\n";

section('production-scope', 'Production scope and prerequisites', 'دامنه کاربرد و پیش‌نیازهای Production');
paragraph('A successful gpupdate is not proof of an effective security control. This runbook combines exact policy paths, starting values, deployment scope and functional checks for System Administrators, Windows Server Administrators, Network Administrators and Infrastructure Engineers.', 'پیام موفق gpupdate اثبات امنیت دستگاه نیست. این Runbook برای System Administrator، Windows Server Administrator، Network Administrator و Infrastructure Engineer، مسیر واقعی Policy، مقدار اولیه، Scope استقرار و آزمون عملکرد ارائه می‌کند.');
paragraph('Scope: Microsoft AD DS, Windows Server 2022/2025, domain-joined Windows 10/11, GPMC and separate Workstations, Servers, Domain Controllers and Users OUs. All suggested values require alignment with organizational security policy, application dependencies and availability requirements.', 'سناریو شامل Microsoft AD DS، Windows Server 2022/2025، کلاینت Domain-Joined با Windows 10/11، GPMC و OUهای جداگانه Workstations، Servers، Domain Controllers و Users است. مقادیر پیشنهادی باید با Security Policy سازمان، وابستگی نرم‌افزار و نیاز دسترس‌پذیری هماهنگ شوند.');
note('Windows 10 22H2 reached general end of support on October 14, 2025. Production use requires an applicable ESU entitlement or migration; LTSC editions have separate lifecycle dates. GPO hardening does not replace security updates.', 'پشتیبانی عمومی Windows 10 نسخه 22H2 در ۱۴ اکتبر ۲۰۲۵ پایان یافته است. استفاده Production به پوشش ESU معتبر یا مهاجرت نیاز دارد؛ نسخه‌های LTSC چرخه مستقل دارند. GPO جایگزین Security Update نیست.');
source('https://learn.microsoft.com/en-us/lifecycle/announcements/windows-10-end-of-support', 'Windows 10 lifecycle');
paragraph('Record OS edition, build and patch level. Maintain versioned Windows, Office and Edge ADMX/ADML files in the Central Store. Check Supported on and the policy help against the target OS; a newer template does not add a feature to an older system. Paths below are in Group Policy Management Editor, reached by editing a GPO in GPMC.', 'Edition، Build و Patch Level را ثبت کنید. ADMX/ADMLهای Windows، Office و Edge را در Central Store نسخه‌بندی کنید. Supported on و Help تنظیم باید با OS مقصد سازگار باشد؛ ADMX جدید قابلیت جدیدی به OS قدیمی اضافه نمی‌کند. مسیرهای زیر داخل Group Policy Management Editor هستند که از Edit کردن GPO در GPMC باز می‌شود.');
code('\\\\<domain-fqdn>\\SYSVOL\\<domain-fqdn>\\Policies\\PolicyDefinitions');
paragraph('Assign one management authority to each setting: GPO, Intune, Configuration Manager or Microsoft Defender for Endpoint security settings management. Inventory conflicts before rollout.', 'برای هر Setting یک مرجع مدیریت تعیین کنید: GPO، Intune، Configuration Manager یا مدیریت تنظیمات امنیتی Microsoft Defender for Endpoint. تداخل‌ها را پیش از Rollout شناسایی کنید.');
figure('Windows-Group-Policy-Security-Baseline.png', 'Illustrative overview of the ten controls; use the exact paths and scope in this runbook, not abbreviated labels in the artwork.', 'نمای مفهومی ده کنترل؛ مسیرهای خلاصه تصویر مرجع اجرایی نیستند. مسیر دقیق Firewall در Security Settings و Office Macro در User Configuration، مطابق متن مقاله است.');

section('password-lockout', '1. Password & Account Lockout Policy', '۱. Password و Account Lockout Policy');
paragraph('Purpose: reduce password reuse and online guessing. Scope: the domain default policy for domain accounts; local account policy on member computers is a separate scope.', 'هدف: کاهش استفاده مجدد از رمز و حدس آنلاین. Scope اصلی، Policy پیش‌فرض حساب‌های Domain است؛ Policy حساب محلی روی Member Computer دامنه اعمال جداگانه دارد.');
code($security."→ Account Policies\n→ Password Policy\n\n".$security."→ Account Policies\n→ Account Lockout Policy");
table([['Policy','Policy'],['Starting value','مقدار اولیه پیشنهادی'],['Operational consideration','ملاحظه عملیاتی']], [
    ['Enforce password history','24 passwords', ['Discourage reuse; combine with minimum age.','جلوگیری از استفاده مجدد؛ همراه Minimum Age.']],
    ['Minimum password length','14 characters', ['Prefer longer passphrases; validate legacy applications.','Passphrase طولانی‌تر مناسب است؛ نرم‌افزار Legacy تست شود.']],
    ['Password must meet complexity requirements','Enabled', ['Does not itself block all common passwords.','همه رمزهای رایج را به‌تنهایی مسدود نمی‌کند.']],
    ['Minimum password age','1 day', ['Prevents rapid cycling through history.','جلوگیری از تغییر پیاپی برای دورزدن History.']],
    ['Maximum password age', ['0 with compensating controls; e.g. 90 days only if mandated','۰ با کنترل جبرانی؛ مثلاً ۹۰ روز فقط در صورت الزام'], ['Choose through organizational security policy.','با Security Policy سازمان تعیین شود.']],
    ['Account lockout threshold','10 invalid attempts', ['Monitor spraying and accidental lockouts.','Password Spraying و Lockout ناخواسته پایش شود.']],
    ['Account lockout duration','15 minutes', ['Avoid permanent lockout without a recovery process.','قفل دائمی بدون فرآیند Recovery ایجاد نشود.']],
    ['Reset account lockout counter after','15 minutes', ['Must not exceed lockout duration.','از Lockout Duration بیشتر نباشد.']],
    ['Store passwords using reversible encryption','Disabled', ['Enable only for a documented exceptional dependency.','فقط وابستگی استثنایی مستند می‌تواند دلیل فعال‌سازی باشد.']],
]);
paragraph('Periodic expiration is not a universal modern recommendation. Maximum age 0 requires compromise detection and prompt reset of exposed credentials, stronger password controls and MFA on services that support it. A very low lockout threshold can cause denial of service; 10 is an initial baseline, not protection against every spraying attack.', 'انقضای دوره‌ای توصیه عمومی مدرن نیست. Maximum Age برابر ۰ باید با تشخیص افشای Credential، Reset فوری رمز افشاشده، کنترل رمز ضعیف و MFA در سرویس پشتیبان همراه باشد. Threshold بسیار پایین می‌تواند DoS ایجاد کند؛ ۱۰ نقطه شروع است و همه حملات Spraying را متوقف نمی‌کند.');
source('https://learn.microsoft.com/en-in/previous-versions/windows/it-pro/windows-10/security/threat-protection/security-policy-settings/maximum-password-age','Maximum password age');
source('https://learn.microsoft.com/en-us/previous-versions/windows/it-pro/windows-10/security/threat-protection/security-policy-settings/account-lockout-threshold','Account lockout threshold');
heading('Domain policy and Fine-Grained Password Policy', 'Domain Policy و Fine-Grained Password Policy');
paragraph('Link the authoritative domain Password/Account Policy at the domain root, usually retaining these settings in Default Domain Policy. A password GPO linked to the Users OU does not create a distinct password policy for those domain users. To apply different values to privileged users, use a Fine-Grained Password Policy (PSO) on users or global security groups, managed with ADAC or ActiveDirectory PowerShell; FGPP is not linked to an OU.', 'Policy اصلی Password/Account دامنه در Domain Root لینک می‌شود و معمولاً این تنظیمات در Default Domain Policy حفظ می‌شوند. لینک Password GPO به Users OU، رمز متفاوتی برای کاربران Domain آن OU ایجاد نمی‌کند. برای حساب مدیریتی از FGPP یا PSO روی User یا Global Security Group با ADAC یا PowerShell استفاده کنید؛ FGPP به OU لینک نمی‌شود.');
paragraph('Deploy: validate domain account behavior in a test domain, or pilot a PSO on test users. A Test OU on member computers validates local accounts, not the domain default policy. Review service credentials before changing expiration or lockout behavior.', 'Deploy: رفتار حساب Domain را در Test Domain یا با PSO روی کاربران آزمایشی بررسی کنید. Test OU روی Member Computer حساب محلی را تست می‌کند، نه Policy پیش‌فرض Domain. پیش از تغییر Expiration و Lockout، Credential سرویس‌ها بررسی شود.');
code("Get-ADDefaultDomainPasswordPolicy\nGet-ADUserResultantPasswordPolicy -Identity 'pilot.user'\nSearch-ADAccount -LockedOut -UsersOnly", 'powershell');
paragraph('Verify: no resultant PSO output means the default domain policy applies. Use a disposable user to test short/reused password rejection and controlled lockout; never deliberately lock a production service account.', 'Verification: نبود خروجی Resultant PSO یعنی Policy پیش‌فرض Domain ملاک است. با User موقت رد رمز کوتاه/تکراری و Lockout کنترل‌شده را تست کنید؛ حساب سرویس Production را برای آزمون قفل نکنید.');
source('https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/get-started/adac/fine-grained-password-policies', 'Fine-grained password policies');

section('defender', '2. Microsoft Defender Antivirus Hardening', '۲. Microsoft Defender Antivirus Hardening');
code($computer."→ Windows Components\n→ Microsoft Defender Antivirus");
table([['Policy below the base path','Policy زیر مسیر اصلی'],['Value','مقدار']], [
    ['Turn off Microsoft Defender Antivirus','Disabled'],
    ['Real-time Protection → Turn off real-time protection','Disabled'],
    ['Real-time Protection → Turn on behavior monitoring','Enabled'],
    ['Real-time Protection → Monitor file and program activity on your computer','Enabled'],
    ['Real-time Protection → Turn on script scanning','Enabled'],
    ['MAPS → Join Microsoft MAPS','Enabled: Advanced MAPS'],
    ['MAPS → Send file samples when further analysis is required','Enabled: Send safe samples'],
    ['MAPS → Configure the “Block at First Sight” feature','Enabled'],
    ['Configure detection for potentially unwanted applications','Enabled: Block'],
    ['Scan → Specify the scan type to use for a scheduled scan','Enabled: Quick scan'],
    ['Scan → Specify the day of the week to run a scheduled scan','Enabled: Every day'],
    ['Scan → Specify the time of day to run a scheduled scan','120 (02:00)'],
    ['Scan → Check for the latest virus and spyware definitions before running a scheduled scan','Enabled'],
]);
paragraph('Purpose: protect endpoints against malware, suspicious behavior and unwanted software. Time is minutes after midnight. Daily quick scans are a starting point; test catch-up behavior for powered-off laptops and workload impact on servers. Cloud protection needs approved network access, and sample submission needs agreement with data-handling policy.', 'هدف: مقابله با Malware، رفتار مشکوک و نرم‌افزار ناخواسته. زمان اسکن برحسب دقیقه پس از نیمه‌شب است. Quick Scan روزانه نقطه شروع است؛ Catch-up لپ‌تاپ خاموش و اثر روی Server تست شود. Cloud Protection به ارتباط مجاز و Sample Submission به هماهنگی با سیاست محرمانگی نیاز دارد.');
source('https://learn.microsoft.com/en-us/defender-endpoint/schedule-antivirus-scans-group-policy','Scheduled scans');
source('https://learn.microsoft.com/en-ie/defender-endpoint/enable-cloud-protection-microsoft-defender-antivirus','Cloud protection and sample submission');
note('Tamper Protection cannot be enabled or disabled through GPO. Changes to protected settings may be blocked even when policy processing appears successful. Check effective state and use the supported central management workflow.', 'Tamper Protection با GPO فعال یا غیرفعال نمی‌شود. تغییر Setting محافظت‌شده ممکن است باوجود موفقیت ظاهری پردازش Policy مسدود شود. وضعیت مؤثر و فرآیند مدیریت مرکزی پشتیبانی‌شده را بررسی کنید.');
paragraph('Deploy: pilot clients and representative server roles independently. On Windows 10/11, third-party antivirus, onboarding and configuration can change Active/Passive behavior. Microsoft Defender for Endpoint adds EDR and central security management; installed Defender Antivirus alone is not MDE onboarding. Choose one settings owner and keep exclusions narrow, justified and reviewed.', 'Deploy: Client و Server نماینده نقش‌های مختلف را جدا تست کنید. در Windows 10/11، آنتی‌ویروس ثالث، Onboarding و تنظیمات بر Active/Passive اثر دارند. MDE قابلیت EDR و مدیریت مرکزی دارد؛ نصب Defender Antivirus به‌معنای Onboarding در MDE نیست. مالک تنظیمات مشخص و Exclusion محدود، مستند و بازبینی‌شده باشد.');
code("Get-MpComputerStatus | Select-Object AMRunningMode, AntivirusEnabled,\n    RealTimeProtectionEnabled, BehaviorMonitorEnabled, IsTamperProtected,\n    AntivirusSignatureLastUpdated\nGet-MpPreference | Select-Object DisableRealtimeMonitoring,\n    DisableBehaviorMonitoring, DisableScriptScanning, MAPSReporting,\n    SubmitSamplesConsent, PUAProtection, ScanParameters, ScanScheduleTime\nStart-MpScan -ScanType QuickScan", 'powershell');
paragraph('Verify effective state, signature freshness, quick-scan completion and cloud connectivity. False on a Disable-prefixed preference normally means that feature is enabled; pay attention to inverted policy names.', 'Verification: وضعیت مؤثر، تازگی Signature، پایان Quick Scan و ارتباط Cloud را بررسی کنید. False در Preference با پیشوند Disable معمولاً به‌معنای فعال‌بودن قابلیت است؛ نام وارونه Policy را دقیق بخوانید.');
source('https://learn.microsoft.com/en-us/defender-endpoint/use-group-policy-microsoft-defender-antivirus','Defender Group Policy and tamper limitations');

section('firewall', '3. Windows Defender Firewall', '۳. Windows Defender Firewall');
code($security."→ Windows Defender Firewall with Advanced Security\n→ Windows Defender Firewall with Advanced Security\n→ Properties");
table([['Profile','Profile'],['Firewall State','Firewall State'],['Inbound','Inbound'],['Outbound','Outbound']], [['Domain','On','Block','Allow'],['Private','On','Block','Allow'],['Public','On','Block','Allow']]);
paragraph('Purpose: control host exposure and lateral movement. Inbound Block still permits matching Allow rules; Block all connections is a different option. An internal attacker may never cross the perimeter firewall, so disabling the host firewall removes an important enterprise boundary. Some console versions omit Defender in the displayed node name.', 'هدف: محدودکردن Exposure و حرکت جانبی. Inbound Block اجازه Rule مجاز را حفظ می‌کند؛ Block all connections گزینه متفاوتی است. مهاجم داخلی ممکن است از Firewall مرزی عبور نکند؛ خاموش‌کردن Host Firewall مرز مهم امنیت را حذف می‌کند. در برخی Consoleها نام گره بدون Defender است.');
code('Windows Defender Firewall with Advanced Security → Inbound Rules → New Rule → Custom');
table([['Service','سرویس'],['Example port','Port نمونه'],['Approved remote source','مبدأ مجاز']], [
    ['RDP','TCP 3389; UDP 3389 if needed',['Jump server / management VPN','Jump Server / VPN مدیریتی']],
    ['WinRM','TCP 5985 or HTTPS 5986',['Management servers','سرورهای مدیریت']],
    ['Monitoring',['Agent-specific','مطابق Agent'],['Named collectors only','فقط Collectorهای مشخص']],
    ['SQL Server','TCP 1433 if configured',['Application servers only','فقط Application Serverها']],
]);
paragraph('Specify protocol, local port, program/service, remote IP and profiles. SQL named instances can use dynamic ports; configure a fixed port where practical, and allow UDP 1434 only when SQL Browser is needed. WinRM 5985 with Kerberos can use message encryption; HTTPS requires a working listener and trusted certificate. A firewall rule does not start a service or create a listener.', 'Protocol، Local Port، Program/Service، Remote IP و Profile را تعیین کنید. SQL Named Instance ممکن است Dynamic Port داشته باشد؛ در صورت امکان Port ثابت و UDP 1434 فقط برای نیاز SQL Browser مجاز شود. WinRM روی 5985 با Kerberos می‌تواند Message Encryption داشته باشد؛ HTTPS به Listener و Certificate معتبر نیاز دارد. Rule سرویس را روشن یا Listener ایجاد نمی‌کند.');
paragraph('Deploy: prepare management allow rules before enforcement, retain console/OOB access and test server role traffic. Review broad existing allow rules and local rule merging. Enable dropped-packet logging with a suitable size and collection plan.', 'Deploy: پیش از Enforcement، Rule مدیریت آماده و Console/OOB حفظ شود؛ ترافیک نقش Server تست شود. Allow Rule گسترده قبلی و Local Rule Merging بررسی شود. ثبت Packetهای Drop با اندازه مناسب و برنامه جمع‌آوری فعال شود.');
code("Get-NetFirewallProfile -PolicyStore ActiveStore |\n    Select-Object Name, Enabled, DefaultInboundAction, DefaultOutboundAction\nGet-NetFirewallRule -PolicyStore ActiveStore |\n    Where-Object DisplayName -Like 'MeetAJ-*'\nTest-NetConnection -ComputerName 'srv01.corp.example' -Port 3389", 'powershell');
paragraph('Verify from both allowed and denied source networks. Success from an allowed source alone does not prove access restrictions. Outbound Allow is an initial compatibility setting, not an outbound least-privilege allowlist.', 'Verification: از شبکه مجاز و غیرمجاز تست کنید. اتصال موفق از مبدأ مجاز به‌تنهایی اثبات محدودیت نیست. Outbound Allow نقطه شروع سازگار است، نه Allowlist خروجی مبتنی بر Least Privilege.');
source('https://learn.microsoft.com/en-us/windows/security/operating-system-security/network-security/windows-firewall/configure','Firewall rules with Group Policy');

section('windows-update', '4. Windows Update / WSUS Policy', '۴. Windows Update / WSUS Policy');
paragraph('Purpose: patch reliably without unplanned service interruption. Use separate client and server policies and patch rings; do not apply a workstation restart schedule to an enterprise server OU.', 'هدف: وصله‌گذاری قابل اتکا بدون قطع ناخواسته سرویس. Policy و Ring کلاینت/سرور جدا باشد؛ زمان Restart کلاینت را روی OU سرور Enterprise اعمال نکنید.');
code("GPO-OPS-WindowsUpdate-Clients\nGPO-OPS-WindowsUpdate-Servers\n\n".$computer."→ Windows Components\n→ Windows Update\n→ Manage end user experience\n→ Configure Automatic Updates\n\nWindows Update\n→ Manage updates offered from Windows Server Update Service\n→ Specify intranet Microsoft update service location");
paragraph('Older ADMX versions show these settings directly beneath Windows Update. Configure the update service and statistics service URLs to match the deployed WSUS topology; the common ports are 8530 for HTTP and 8531 for HTTPS. Use HTTPS only after TLS is correctly configured.', 'در ADMX قدیمی تنظیمات ممکن است مستقیماً زیر Windows Update باشند. URL سرویس Update و Statistics را با توپولوژی واقعی WSUS هماهنگ کنید؛ Port رایج HTTP برابر 8530 و HTTPS برابر 8531 است. HTTPS تنها پس از تنظیم صحیح TLS استفاده شود.');
code('https://wsus.corp.example:8531');
table([['Policy','Policy'],['Clients','Clients'],['Servers','Servers']], [
    ['Configure Automatic Updates',['Enabled: 4 — scheduled installation','Enabled: 4 — نصب زمان‌بندی‌شده'],['3 with orchestrated installation; or 4 in an approved standalone window','۳ همراه نصب توسط Orchestrator؛ یا ۴ در پنجره تأییدشده سرور مستقل']],
    ['Specify intranet Microsoft update service location',['Configured WSUS URLs','URLهای WSUS'],['Configured WSUS URLs','URLهای WSUS']],
    ['Active Hours','08:00–18:00',['Service-specific; check OS support','متناسب سرویس؛ پشتیبانی OS بررسی شود']],
    ['Restart deadlines',['According to patch SLA','طبق SLA وصله‌گذاری'],['According to service dependencies and change window','طبق وابستگی سرویس و Change Window']],
]);
code("Windows Update → Manage end user experience\n→ Turn off auto-restart for updates during active hours\n\nWindows Update → Legacy Policies (newer ADMX)\n→ No auto-restart with logged on users for scheduled automatic updates installations");
paragraph('The logged-on-users setting has a specific scheduled-installation scope; it is not a universal no-reboot guarantee. Review deadlines and legacy policy interactions. Active Hours controls restarts, not a complete installation or failover maintenance window.', 'تنظیم Logged-on Users Scope مشخص نصب زمان‌بندی‌شده دارد و تضمین عمومی عدم Restart نیست. تعامل Deadline و Legacy Policy را بررسی کنید. Active Hours کنترل Restart است، نه پنجره کامل نصب یا Failover.');
note('WSUS approval and GPO alone do not orchestrate SQL, Hyper-V or cluster maintenance. Option 3 can leave downloads uninstalled if no installation workflow exists. Implement pre-check, drain/failover, install, reboot, health check and return-to-service through a central tool or runbook.', 'WSUS Approval و GPO به‌تنهایی Maintenance مربوط به SQL، Hyper-V و Cluster را هماهنگ نمی‌کنند. Option 3 بدون فرآیند نصب می‌تواند Update را دانلودشده اما نصب‌نشده باقی بگذارد. Pre-check، Drain/Failover، Install، Reboot، Health Check و Return to Service با ابزار مرکزی یا Runbook اجرا شود.');
paragraph('Deploy: approve updates to a pilot ring first, then broad clients and role-based server rings. Verify scan source, last contact, compliance, actual installed update and application health after reboot.', 'Deploy: ابتدا Ring آزمایشی، سپس کلاینت عمومی و Ring سرور براساس نقش. Scan Source، Last Contact، Compliance، Update نصب‌شده و سلامت برنامه پس از Reboot بررسی شود.');
code("Get-ItemProperty 'HKLM:\\SOFTWARE\\Policies\\Microsoft\\Windows\\WindowsUpdate'\nGet-ItemProperty 'HKLM:\\SOFTWARE\\Policies\\Microsoft\\Windows\\WindowsUpdate\\AU'\nGet-WinEvent -LogName 'Microsoft-Windows-WindowsUpdateClient/Operational' -MaxEvents 30", 'powershell');
paragraph('Registry values prove configuration presence, not patch installation. If WSUS and cloud update management coexist, explicitly assign scan sources and avoid contradictory policies.', 'وجود Registry اثبات نصب Patch نیست. در هم‌زیستی WSUS و مدیریت Cloud، Scan Sourceها مشخص و Policy متعارض حذف شود.');
source('https://learn.microsoft.com/en-us/windows-server/administration/windows-server-update-services/deploy/4-configure-group-policy-settings-for-automatic-updates','WSUS automatic updates');
source('https://learn.microsoft.com/en-us/windows/deployment/update/waas-wufb-group-policy','Update policy behavior');

section('usb-control', '5. USB / Removable Storage Control', '۵. کنترل USB و Removable Storage');
code($computer."→ System\n→ Removable Storage Access");
paragraph('Purpose: reduce removable-media malware and data exfiltration. The table applies to Removable Disks: Deny read access, Deny write access and Deny execute access; it is not a blanket USB bus block.', 'هدف: کاهش بدافزار رسانه قابل حمل و خروج داده. جدول مربوط به Removable Disks: Deny read access، Deny write access و Deny execute access است؛ کل USB Bus را مسدود نمی‌کند.');
table([['Mode','حالت'],['Deny read','Deny read'],['Deny write','Deny write'],['Deny execute','Deny execute']], [
    ['Allow','Disabled','Disabled',['Enabled recommended; Disabled for full allow','Enabled پیشنهادی؛ برای Allow کامل Disabled']],
    ['Read Only','Disabled','Enabled','Enabled'],['Block','Enabled','Enabled','Enabled'],
]);
paragraph('All Removable Storage classes: Deny all access blocks more storage classes and overrides individual access settings. Leave it disabled when implementing Read Only. Phones using WPD/MTP may need separate policies. Keyboard and mouse are not removable disks.', 'All Removable Storage classes: Deny all access کلاس‌های بیشتری را مسدود و تنظیم جزئی را Override می‌کند؛ در Read Only فعال نباشد. تلفن WPD/MTP ممکن است Policy جدا بخواهد. Keyboard و Mouse، Removable Disk نیستند.');
code($computer."→ System\n→ Device Installation\n→ Device Installation Restrictions");
paragraph('Device Installation Restrictions controls device installation by IDs/classes, not just file access. Broad USB installation blocks may disable keyboards, docks, smart-card readers or industrial equipment. Use narrow allowlists where necessary.', 'Device Installation Restrictions نصب Device با ID/Class را کنترل می‌کند، نه صرفاً دسترسی فایل. Block گسترده USB ممکن است Keyboard، Dock، Smart Card Reader یا تجهیز صنعتی را از کار بیندازد. در صورت نیاز Allowlist دقیق تعریف کنید.');
paragraph('Deploy: pilot on ordinary and specialist workstations, with a documented exception group and review date. Verify read, write and execution separately using a disposable USB. Test keyboard, mouse and dock; reconnect or reboot if the policy requires it.', 'Deploy: روی Workstation معمولی و تخصصی Pilot و گروه استثنای مستند با تاریخ بازبینی تعریف کنید. Verification: Read، Write و Execute با USB آزمایشی جدا تست شوند؛ Keyboard، Mouse و Dock بررسی و در صورت نیاز Device مجدداً متصل یا Reboot شود.');
source('https://learn.microsoft.com/en-us/windows/client-management/mdm/policy-csp-admx-removablestorage','Removable storage policies');

section('screen-lock', '6. Automatic Screen Lock', '۶. قفل خودکار Workstation');
code($security."→ Local Policies\n→ Security Options\n→ Interactive logon: Machine inactivity limit\n\nRecommended: 900 seconds (15 minutes)");
paragraph('Purpose: protect abandoned interactive sessions. Use 300–600 seconds for public or sensitive workstations when justified; 0 disables the inactivity limit. Validate display power settings that may cause an earlier lock.', 'هدف: حفاظت نشست رهاشده. برای Workstation عمومی یا حساس در صورت توجیه ۳۰۰ تا ۶۰۰ ثانیه مناسب است؛ ۰ محدودیت را غیرفعال می‌کند. Power Setting نمایشگر ممکن است Lock زودتر ایجاد کند و باید تست شود.');
code("User Configuration\n→ Policies\n→ Administrative Templates\n→ Control Panel\n→ Personalization\n\nEnable screen saver = Enabled\nPassword protect the screen saver = Enabled\nScreen saver timeout = Enabled: 900 seconds\nForce specific screen saver = valid approved .scr, if required (e.g. scrnsave.scr)");
paragraph('Deploy: link the computer policy to Workstations; link screen-saver user policy to the user OU. Linking user settings only to a computer OU is insufficient without deliberately configured loopback. Activate a valid screen saver and test the target environment.', 'Deploy: Policy کامپیوتری به Workstations و Policy کاربری Screen Saver به OU کاربران لینک شود. User Setting در Computer OU بدون Loopback طراحی‌شده کافی نیست. Screen Saver معتبر فعال و محیط مقصد تست شود.');
code("Get-ItemProperty 'HKLM:\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Policies\\System' -Name InactivityTimeoutSecs",'powershell');
paragraph('Verify by leaving a pilot session idle and checking that reauthentication is required. Exemptions for kiosks and control-room systems need a separate reviewed design; do not impose workstation behavior blindly on these systems.', 'Verification: نشست Pilot را بدون ورودی رها کنید و نیاز به احراز هویت مجدد را بررسی کنید. Kiosk و اتاق کنترل به طراحی استثنای تأییدشده نیاز دارند؛ رفتار عمومی Workstation را کورکورانه روی آن‌ها اعمال نکنید.');
source('https://learn.microsoft.com/en-us/previous-versions/windows/it-pro/windows-10/security/threat-protection/security-policy-settings/interactive-logon-machine-inactivity-limit','Machine inactivity limit');

section('local-admin-laps', '7. Local Administrator Hardening & Windows LAPS', '۷. Local Administrator Hardening و Windows LAPS');
paragraph('Two independent controls are required: group membership determines who is an administrator; LAPS manages a local account password. Remove Domain Users, Authenticated Users and broad support groups from Local Administrators; inspect nested group access, not just direct members.', 'دو کنترل مستقل لازم است: عضویت گروه تعیین می‌کند چه کسی Administrator است؛ LAPS رمز حساب محلی را مدیریت می‌کند. Domain Users، Authenticated Users و گروه عمومی پشتیبانی از Local Administrators حذف شوند؛ Nested Membership نیز بررسی شود.');
code("GG-Workstation-LocalAdmins\nGG-Server-LocalAdmins\n\n".$security."→ Restricted Groups\n\nComputer Configuration\n→ Preferences\n→ Control Panel Settings\n→ Local Users and Groups");
table([['Method','روش'],['Behavior','رفتار'],['Risk / use','ریسک / کاربرد']], [
    ['Restricted Groups: Members of this group',['Enforces members of the target group; removes unspecified members where permitted.','اعضای گروه هدف را اعمال می‌کند و اعضای خارج فهرست را در موارد مجاز حذف می‌کند.'],['Complete reviewed allowlist; preserve recovery access.','Allowlist کامل و بررسی‌شده؛ Recovery حفظ شود.']],
    ['Restricted Groups: This group is a member of',['Adds the selected group to the destination group.','گروه منتخب را عضو گروه مقصد می‌کند.'],['Does not cleanse all other destination members.','همه اعضای دیگر مقصد را پاک‌سازی نمی‌کند.']],
    ['GPP Local Users and Groups: Update',['Add/remove specified members.','افزودن/حذف اعضای مشخص.'],['Review Delete all member users/groups carefully.','Delete all member users/groups دقیق بررسی شود.']],
]);
note('A wrong membership allowlist can remove legitimate support access. Avoid competing Restricted Groups/GPP definitions and unreviewed Replace actions. Apply local-group designs to workstations and member servers; a DC does not have the same local SAM group model.', 'Allowlist اشتباه می‌تواند دسترسی پشتیبانی را حذف کند. Restricted Groups و GPP متعارض و Replace بدون بررسی تعریف نکنید. این طراحی برای Workstation و Member Server است؛ DC مدل Local SAM یکسانی ندارد.');
paragraph('A shared local administrator password or hash can enable compromise across many computers after one endpoint is breached. Do not distribute passwords using GPP. Audit legacy SYSVOL files containing cpassword and rotate affected credentials.', 'رمز یا Hash مشترک Local Administrator می‌تواند پس از نفوذ به یک Endpoint، دسترسی به چندین دستگاه ایجاد کند. رمز را با GPP توزیع نکنید؛ فایل Legacy دارای cpassword در SYSVOL بررسی و Credential مربوط Rotate شود.');
heading('Modern Windows LAPS configuration', 'پیکربندی Windows LAPS مدرن');
code($computer."→ System\n→ LAPS");
table([['Policy','Policy'],['Recommended starting configuration','پیکربندی اولیه پیشنهادی']], [
    ['Configure password backup directory','Enabled: Active Directory'],
    ['Password Settings','Length 20; age 30 days; upper/lowercase + digits + special characters'],
    ['Enable password encryption','Enabled, when prerequisites are met'],
    ['Configure authorized password decryptors',['Dedicated restricted recovery group','گروه محدود و اختصاصی Recovery']],
    ['Do not allow password expiration time longer than required by policy','Enabled'],
    ['Post-authentication actions',['Reset password; pilot any logoff/reboot action','Reset رمز؛ Logoff/Reboot فقط پس از Pilot']],
]);
paragraph('Windows LAPS creates unique rotating passwords and backs them up to AD or Entra ID; this runbook uses AD. AD password encryption requires at least Windows Server 2016 domain functional level. Schema permissions, read permissions and authorized decryption are distinct. Newer automatic account management/passphrase settings require compatible OS builds and templates; a Server 2025 installation does not prove the domain functional level.', 'Windows LAPS رمز مستقل و دوره‌ای را در AD یا Entra ID پشتیبان می‌گیرد؛ این مقاله از AD استفاده می‌کند. Encryption در AD حداقل DFL برابر Windows Server 2016 می‌خواهد. مجوز Schema، Read و Decrypt متفاوت‌اند. Automatic Account Management و Passphrase جدید به OS/ADMX سازگار نیاز دارند؛ نصب Server 2025 به‌تنهایی DFL را اثبات نمی‌کند.');
source('https://learn.microsoft.com/en-ie/windows-server/identity/laps/laps-management-policy-settings','Windows LAPS policy settings');
paragraph('Deploy: confirm Windows LAPS servicing updates, schema change approval and AD replication, then delegate narrowly and link to a pilot OU. For a custom local account on versions without automatic account management, create the account separately; LAPS does not create it. The built-in Administrator can be identified by RID even after renaming.', 'Deploy: Update لازم Windows LAPS، تأیید Schema Change و AD Replication بررسی، سپس Delegation محدود و Link به Pilot OU انجام شود. حساب سفارشی روی نسخه فاقد Automatic Account Management باید جدا ساخته شود؛ LAPS آن را ایجاد نمی‌کند. Built-in Administrator حتی بعد از Rename با RID قابل شناسایی است.');
code("# Approved schema extension; run with appropriate rights\nUpdate-LapsADSchema\nSet-LapsADComputerSelfPermission -Identity 'OU=Workstations,DC=corp,DC=example'\nFind-LapsADExtendedRights -Identity 'OU=Workstations,DC=corp,DC=example'\n\n# On the pilot endpoint\nGet-LocalGroupMember -SID 'S-1-5-32-544'\nInvoke-LapsPolicyProcessing\n\n# On an authorized administration host\nGet-LapsADPassword -Identity 'PC-PILOT-01'", 'powershell');
paragraph('Delegate retrieval/reset rights with Set-LapsADReadPasswordPermission and Set-LapsADResetPasswordPermission to approved groups; ensure Configure authorized password decryptors matches the recovery design. Verify Microsoft-Windows-LAPS/Operational, authorized recovery and post-use rotation. Do not save plaintext passwords in tickets or screenshots.', 'با Set-LapsADReadPasswordPermission و Set-LapsADResetPasswordPermission، بازیابی/Reset به گروه مجاز Delegate شود؛ Configure authorized password decryptors با طراحی Recovery هماهنگ باشد. Verification: لاگ Microsoft-Windows-LAPS/Operational، بازیابی مجاز و Rotate پس از استفاده تست شود. رمز Plaintext در Ticket یا Screenshot ذخیره نشود.');
source('https://learn.microsoft.com/en-us/windows-server/identity/laps/laps-scenarios-windows-server-active-directory','Deploy Windows LAPS with AD');
source('https://learn.microsoft.com/en-us/windows/client-management/mdm/policy-csp-restrictedgroups','Restricted group membership semantics');

section('rdp-hardening', '8. RDP Hardening', '۸. RDP Hardening');
code($computer."→ Windows Components\n→ Remote Desktop Services\n→ Remote Desktop Session Host\n→ Security");
table([['Policy','Policy'],['Value','مقدار']], [
    ['Require user authentication for remote connections by using Network Level Authentication','Enabled'],
    ['Require use of specific security layer for remote (RDP) connections','Enabled: SSL'],
    ['Set client connection encryption level','Enabled: High Level'],
    ['Always prompt for password upon connection',['Enabled after SSO review','Enabled پس از بررسی SSO']],
]);
paragraph('Purpose: reduce unauthorized remote access. SSL is the displayed policy option for the TLS security layer; provision a trusted certificate with the correct name and validate negotiation. High Level does not replace certificate validation.', 'هدف: کاهش دسترسی Remote غیرمجاز. SSL نام گزینه نمایشی لایه TLS است؛ Certificate مورد اعتماد با نام صحیح فراهم و Negotiation بررسی شود. High Level جایگزین اعتبارسنجی Certificate نیست.');
code("Remote Desktop Session Host\n→ Session Time Limits\n\nSet time limit for active but idle Remote Desktop Services sessions = 15 minutes\nSet time limit for disconnected sessions = 30 minutes\nEnd session when time limits are reached = workload-specific, pilot before enabling\n\n".$security."→ Local Policies\n→ User Rights Assignment\n→ Allow log on through Remote Desktop Services\n→ Deny log on through Remote Desktop Services");
paragraph('Restrict Remote Desktop Users and user rights to approved groups. Deny takes precedence over Allow, including administrators who belong to a denied group. Disconnect differs from logoff; session termination can destroy unsaved work. Combine domain/local account lockout with source-restricted firewall rules.', 'Remote Desktop Users و User Rights به گروه مجاز محدود شوند. Deny بر Allow اولویت دارد و Administrator عضو گروه Deny نیز رد می‌شود. Disconnect با Logoff متفاوت است؛ پایان نشست کار ذخیره‌نشده را از بین می‌برد. Lockout حساب Domain/Local و Rule محدود مبدأ هم‌زمان اجرا شود.');
note('Do not expose RDP directly to the Internet. Use an MFA-protected VPN, RD Gateway or a controlled jump server/bastion. These access systems also need patching, trusted TLS and narrow authorization.', 'RDP مستقیماً از Internet در دسترس نباشد. VPN با MFA، RD Gateway یا Jump Server/Bastion کنترل‌شده استفاده شود. این سامانه‌ها نیز به Patch، TLS معتبر و Authorization محدود نیاز دارند.');
paragraph('Deploy to a pilot with console access. Verify a new connection by an authorized user, rejection of an unauthorized user/source, NLA, certificate trust and actual idle/disconnected behavior.', 'Deploy: روی Pilot با Console Access اعمال شود. Verification: اتصال جدید کاربر مجاز، رد User/Source غیرمجاز، NLA، اعتماد Certificate و رفتار واقعی Idle/Disconnected بررسی شود.');
code("Get-CimInstance -Namespace 'root\\cimv2\\terminalservices' `\n    -ClassName Win32_TSGeneralSetting -Filter \"TerminalName='RDP-tcp'\" |\n    Select-Object UserAuthenticationRequired, SecurityLayer, MinEncryptionLevel\nquser", 'powershell');
source('https://learn.microsoft.com/windows/client-management/mdm/policy-csp-admx-terminalserver','Remote Desktop policy reference');

section('advanced-audit', '9. Advanced Audit Policy', '۹. Advanced Audit Policy');
code($security."→ Advanced Audit Policy Configuration\n→ Audit Policies");
table([['Category → Subcategory','Category → Subcategory'],['Starting audit flags','Audit اولیه'],['Target / note','Target / توضیح']], [
    ['Logon/Logoff → Audit Logon','Success + Failure','All endpoints'],
    ['Logon/Logoff → Audit Logoff','Success','All endpoints'],
    ['Logon/Logoff → Audit Account Lockout','Failure','All endpoints'],
    ['Logon/Logoff → Audit Special Logon','Success','All endpoints'],
    ['Account Logon → Audit Credential Validation','Success + Failure','DCs and local account validation'],
    ['Account Logon → Audit Kerberos Authentication Service','Success + Failure','DCs'],
    ['Account Logon → Audit Kerberos Service Ticket Operations','Success + Failure',['DCs; assess volume','DC؛ حجم بررسی شود']],
    ['Account Management → Audit User Account Management','Success + Failure','DCs and local accounts'],
    ['Account Management → Audit Computer Account Management','Success + Failure','Especially DCs'],
    ['Account Management → Audit Security Group Management','Success + Failure','DCs and local groups'],
    ['Policy Change → Audit Audit Policy Change','Success + Failure','All endpoints'],
    ['Policy Change → Audit Authentication Policy Change','Success','All endpoints'],
    ['Privilege Use → Audit Sensitive Privilege Use','Success + Failure',['Assess workload volume','حجم Workload بررسی شود']],
    ['Detailed Tracking → Audit Process Creation','Success','All endpoints'],
]);
paragraph('Purpose: produce usable investigation evidence. Account Logon tracks credential validation; Logon tracks sessions on the destination host. Select subcategories by role and SIEM capacity rather than enabling everything indiscriminately.', 'هدف: شواهد قابل استفاده برای بررسی Incident. Account Logon اعتبارسنجی Credential و Logon نشست روی Host مقصد را پوشش می‌دهد. Subcategory براساس نقش و ظرفیت SIEM انتخاب شود، نه فعال‌سازی همه گزینه‌ها.');
source('https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/plan/security-best-practices/advanced-audit-policy-configuration','Advanced Audit Policy');
code($security."→ Local Policies\n→ Security Options\n→ Audit: Force audit policy subcategory settings (Windows Vista or later)\n   to override audit policy category settings = Enabled\n\n".$computer."→ System\n→ Audit Process Creation\n→ Include command line in process creation events = Enabled");
paragraph('Command-line capture requires Audit Process Creation. Event 4688 can then contain arguments in plaintext, including secrets. Limit log access and avoid putting credentials in command-line arguments.', 'ثبت Command Line به Audit Process Creation نیاز دارد. Event 4688 می‌تواند آرگومان و Secret را Plaintext ثبت کند؛ دسترسی Log محدود و Credential در Command Line وارد نشود.');
source('https://learn.microsoft.com/en-us/windows-server/identity/ad-ds/manage/component-updates/command-line-process-auditing','Command-line process auditing');
table([['Event ID','Event ID'],['Meaning / investigation value','معنا / کاربرد']], [
    ['4624',['Successful logon; inspect logon type and source.','Logon موفق؛ Logon Type و مبدأ بررسی شود.']],
    ['4625',['Failed logon; inspect status/substatus.','Logon ناموفق؛ Status/SubStatus بررسی شود.']],
    ['4720',['User account created.','ایجاد User Account.']],['4726',['User account deleted.','حذف User Account.']],
    ['4728',['Member added to a security-enabled global group.','افزودن عضو به Security-enabled Global Group.']],
    ['4732',['Member added to a security-enabled local group; local SAM or domain-local context.','افزودن عضو به Security-enabled Local Group؛ در Context محلی یا Domain Local.']],
    ['4688',['New process; command line when separately enabled.','Process جدید؛ Command Line با Policy مکمل.']],
    ['4740',['Account locked out.','قفل‌شدن حساب.']],['4719',['System audit policy changed.','تغییر System Audit Policy.']],['1102',['Security log cleared.','پاک‌شدن Security Log.']],
]);
paragraph('Deploy separate role-aware audit settings and log sizes. Forward via WEF or a supported agent to SIEM, Microsoft Sentinel, Splunk, Graylog or Wazuh. Audit GPO alone does not configure forwarding. Set retention, collection health monitoring and alert ownership.', 'Deploy: تنظیم Audit و اندازه Log براساس نقش جدا باشد. با WEF یا Agent پشتیبانی‌شده به SIEM، Microsoft Sentinel، Splunk، Graylog یا Wazuh ارسال کنید. Audit GPO به‌تنهایی Forwarding ایجاد نمی‌کند. Retention، پایش سلامت جمع‌آوری و مالک Alert مشخص باشد.');
code("auditpol /get /category:*\n\nGet-WinEvent -FilterHashtable @{\n    LogName = 'Security'\n    Id = 4624,4625,4720,4726,4728,4732,4688\n    StartTime = (Get-Date).AddHours(-1)\n}", 'powershell');
paragraph('Verify by generating a controlled failed logon and harmless process, then confirm local events, central ingestion and alert delivery. Audit volume and confidential arguments are production design constraints.', 'Verification: Logon ناموفق کنترل‌شده و Process بی‌خطر ایجاد، سپس Event محلی، Ingestion مرکزی و Alert تست شود. حجم Audit و محرمانگی آرگومان‌ها محدودیت طراحی Production هستند.');

section('application-hardening', '10. Office / Browser / PowerShell Hardening', '۱۰. هاردنینگ Office / Browser / PowerShell');
heading('Office macros and ActiveX', 'Office Macro و ActiveX');
code("User Configuration\n→ Policies\n→ Administrative Templates\n→ Microsoft Word 2016\n→ Word Options\n→ Security\n→ Trust Center\n→ Block macros from running in Office files from the Internet = Enabled\n\nMicrosoft Excel 2016 → Excel Options → Security → Trust Center\n→ Block macros from running in Office files from the Internet = Enabled\n\nMicrosoft Office 2016 → Security Settings\n→ Disable All ActiveX = Enabled (where present in the installed Office ADMX)");
paragraph('Purpose: block common malicious document execution paths. Office templates may retain the 2016 product label for newer releases; verify support for the installed Office build. Internet macro blocking relies on origin signals such as Mark of the Web. Broad trusted locations and removing MOTW weaken the control. Distribute approved macros through narrow, signed, owned workflows and test legacy ActiveX dependencies.', 'هدف: مسدودکردن مسیرهای رایج اجرای سند آلوده. Office ADMX ممکن است برای نسخه جدید هم نام 2016 داشته باشد؛ پشتیبانی Build بررسی شود. Block Macro اینترنت به نشانه منشأ مانند Mark of the Web وابسته است. Trusted Location گسترده و حذف MOTW کنترل را تضعیف می‌کند. Macro مجاز با مسیر محدود، امضاشده و مالک مشخص و وابستگی Legacy ActiveX با Pilot مدیریت شود.');
source('https://learn.microsoft.com/en-us/DeployOffice/security/internet-macros-blocked','Office Internet macro blocking');
heading('Microsoft Edge security policies', 'Microsoft Edge Security Policies');
code($computer."→ Microsoft Edge\n→ SmartScreen settings\n\nConfigure Microsoft Defender SmartScreen = Enabled\nPrevent bypassing Microsoft Defender SmartScreen prompts for sites = Enabled\nPrevent bypassing of Microsoft Defender SmartScreen warnings about downloads = Enabled");
paragraph('Install MSEdge ADMX/ADML. Use mandatory policies rather than Microsoft Edge - Default Settings (users can override). Review extension permissions and maintain an approved allowlist without breaking required applications.', 'MSEdge ADMX/ADML نصب شود. از Mandatory Policy استفاده کنید؛ Microsoft Edge - Default Settings (users can override) الزام ایجاد نمی‌کند. مجوز Extension و Allowlist مجاز با حفظ برنامه لازم بررسی شود.');
source('https://learn.microsoft.com/en-us/deployedge/microsoft-edge-policies','Microsoft Edge policy reference');
heading('Windows PowerShell 5.1 and PowerShell 7', 'Windows PowerShell 5.1 و PowerShell 7');
code($computer."→ Windows Components\n→ Windows PowerShell\n\nTurn on PowerShell Script Block Logging = Enabled\nTurn on Module Logging = Enabled; Module Names: selected modules or * after volume review\nTurn on PowerShell Transcription = Enabled; protected output directory\nTurn on Script Execution = Allow only signed scripts, if required and pilot-tested\n\nPowerShell 7 (separate installed templates):\nComputer Configuration → Policies → Administrative Templates → PowerShell Core");
paragraph('PowerShell is essential for administration and should not be disabled wholesale. Combine logging with least privilege, script signing, JEA and application control through App Control for Business or AppLocker. Execution Policy is not a security boundary and cannot replace application control. Validate edition/build support for the selected control.', 'PowerShell ابزار ضروری مدیریت است و نباید به‌طور کامل Disable شود. Logging با Least Privilege، Script Signing، JEA و Application Control با App Control for Business یا AppLocker ترکیب شود. Execution Policy مرز امنیتی و جایگزین Application Control نیست. پشتیبانی Edition/Build ابزار انتخابی بررسی شود.');
paragraph('Transcription captures input/output in text files. Protect ACLs, retention and transfer; users must not read others’ transcripts. Script logging can capture secrets: assess Protected Event Logging and authorized decryption. Windows PowerShell policies do not automatically prove PowerShell 7 coverage.', 'Transcription ورودی/خروجی را در فایل متنی ثبت می‌کند؛ ACL، Retention و انتقال حفاظت شوند و کاربر Transcript دیگران را نخواند. Script Logging ممکن است Secret ثبت کند؛ Protected Event Logging و Decryption مجاز بررسی شود. Policy Windows PowerShell اثبات پوشش PowerShell 7 نیست.');
source('https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.core/about/about_group_policy_settings?view=powershell-7.6','PowerShell Group Policy');
source('https://learn.microsoft.com/powershell/module/microsoft.powershell.core/about/about_logging_windows?view=powershell-7.6','PowerShell logging on Windows');
paragraph('Deploy Office settings to pilot users and Edge/PowerShell computer settings to pilot devices. Verify an Internet-origin test macro, edge://policy status, a new PowerShell session and the transcript ACL. Inspect 4104 (script blocks) and 4103 (modules); PowerShell 7 uses PowerShellCore/Operational when its provider is registered.', 'Deploy: تنظیم Office به Pilot User و Edge/PowerShell کامپیوتری به Pilot Device. Verification: Macro آزمایشی با منشأ Internet، وضعیت edge://policy، PowerShell Session جدید و ACL Transcript بررسی شوند. Event 4104 مربوط به Script Block و 4103 مربوط به Module است؛ PowerShell 7 با Provider ثبت‌شده از PowerShellCore/Operational استفاده می‌کند.');
code("Get-WinEvent -FilterHashtable @{\n    LogName = 'Microsoft-Windows-PowerShell/Operational'\n    Id = 4103,4104\n    StartTime = (Get-Date).AddMinutes(-15)\n}", 'powershell');

section('architecture', 'Modular OU and GPO architecture', 'معماری Modular برای OU و GPO');
figure('Active-Directory-GPO-Architecture.png', 'Illustrative OU separation; domain-account password policy is linked at the domain root, and each setting has one owner.', 'تفکیک مفهومی OUها؛ Password Policy حساب Domain در Domain Root اعمال می‌شود. جایگاه Account Policies در تصویر جایگزین این قاعده نیست؛ هر Setting یک مالک دارد.');
paragraph('Separate workstations, member servers, DCs and users. A large monolithic GPO couples unrelated changes and makes rollout and rollback harder. Define one authoritative owner per setting; do not repeatedly configure the same value in competing baselines.', 'Workstation، Member Server، DC و User جدا باشند. GPO بزرگ تغییرات نامرتبط را به هم وابسته و Rollout/Rollback را دشوار می‌کند. برای هر Setting مالک معتبر واحد تعیین و مقدار یکسان در Baselineهای متعارض تکرار نشود.');
code("GPO-SEC-Password-Policy\nGPO-SEC-Defender\nGPO-SEC-Windows-Firewall\nGPO-OPS-WindowsUpdate-Clients\nGPO-OPS-WindowsUpdate-Servers\nGPO-SEC-USB-Control\nGPO-SEC-Workstation-Lock\nGPO-SEC-Local-Admin\nGPO-SEC-RDP-Hardening\nGPO-SEC-Audit-Logging\nGPO-SEC-Application-Hardening\n\nDomain\n│   └── Domain Password / Account Policy\n├── Domain Controllers\n│   └── DC Security GPO\n├── Servers\n│   ├── Server Security Baseline\n│   ├── Server Update Policy\n│   └── RDP Hardening\n├── Workstations\n│   ├── Workstation Security Baseline\n│   ├── Defender\n│   ├── Firewall\n│   ├── USB Control\n│   ├── Workstation Lock\n│   └── Windows Update\n└── Users\n    ├── Office Hardening\n    └── User Screen Saver Policy");
paragraph('Split GPO-SEC-Application-Hardening into user/computer or product policies if ownership differs. Add Clients, Servers or DC suffixes when values differ. A member-server local-group policy must not inherit blindly onto DCs.', 'اگر مالک متفاوت است، GPO-SEC-Application-Hardening به Policy User/Computer یا محصول تفکیک شود. در تفاوت مقدار، پسوند Clients، Servers یا DC اضافه کنید. Policy Local Group سرور Member نباید کورکورانه به DC برسد.');

section('deployment', 'Safe deployment: Test OU to Production', 'Deploy صحیح از Test OU تا Production');
figure('GPO-Deployment-Workflow-Test-to-Production.png', 'Staged rollout with functional validation and monitoring; a processing event alone does not prove control effectiveness.', 'Rollout مرحله‌ای با آزمون عملکرد و پایش؛ Event پردازش به‌تنهایی اثربخشی کنترل را ثابت نمی‌کند.');
code('Create GPO → Link to Test OU → Add Pilot Computers → gpupdate → Validation → Production Rollout');
paragraph('Record existing effective policy and application health; create and document the GPO, link it to a pilot scope representing builds and workloads, apply the update and any required restart/logoff, validate effective state and functional behavior, then expand in waves with a stop criterion.', 'وضعیت Policy مؤثر و سلامت برنامه ثبت، GPO ساخته و مستند، به Pilot شامل Build/Workload نماینده لینک، Update و Restart/Logoff لازم اجرا و وضعیت مؤثر و عملکرد تست شود؛ سپس با معیار توقف در موج‌های کنترل‌شده توسعه یابد.');
note('Never deploy an untested security GPO across the whole domain. Domain-account password policy needs a test domain or a user-targeted FGPP pilot; a workstation Test OU does not reproduce that scope.', 'هیچ GPO امنیتی تست‌نشده روی کل Domain Deploy نشود. Password Policy حساب Domain به Test Domain یا FGPP روی کاربران Pilot نیاز دارد؛ Workstation Test OU این Scope را بازتولید نمی‌کند.');
paragraph('Moving an object to a Test OU also changes inherited policies. Prefer a pilot child OU with preserved baseline or carefully scoped filtering. Prepare an explicit reverse change: unlinking alone does not restore GPP membership changes or every persistent setting.', 'جابه‌جایی Object به Test OU، Policy موروثی را هم تغییر می‌دهد. Pilot Child OU با Baseline حفظ‌شده یا Filtering دقیق مناسب است. تغییر معکوس صریح آماده کنید؛ Unlink به‌تنهایی عضویت تغییرکرده GPP یا همه Settingهای باقی‌مانده را برنمی‌گرداند.');

section('troubleshooting', 'Troubleshooting and resultant-policy validation', 'Troubleshooting و اعتبارسنجی Policy مؤثر');
figure('Group-Policy-Troubleshooting-gpresult-RSoP.png', 'Illustrative troubleshooting tools; use the complete commands below, including the required report path.', 'نمای مفهومی ابزارهای Troubleshooting؛ فرمان اجرایی را از متن زیر بردارید. Get-GPResultantSetOfPolicy با ReportType و Path فایل گزارش تولید می‌کند و نمونه تصویری بازگشت Object مرجع اجرا نیست.');
code("gpupdate /force\ngpresult /r\ngpresult /r /scope computer\ngpresult /r /scope user\nrsop.msc",'batch');
paragraph('gpupdate /force reprocesses policy, but cannot fix DNS or replication and may require logoff/reboot. gpresult /r lists applied policies and scopes; elevate for computer results. Run user results in the intended user context, not an unrelated administrator account. rsop.msc is useful but may omit newer settings or preferences.', 'gpupdate /force Policy را دوباره پردازش می‌کند اما DNS یا Replication را اصلاح نمی‌کند و ممکن است Logoff/Reboot لازم باشد. gpresult /r فهرست GPO و Scope را می‌دهد؛ Computer Result را Elevated اجرا کنید. User Result برای کاربر موردنظر باشد، نه Administrator دیگر. rsop.msc مفید است اما همه تنظیمات جدید یا Preferences را تضمین نمی‌کند.');
code("New-Item -Path 'C:\\Temp' -ItemType Directory -Force\ngpresult /h C:\\Temp\\gpresult.html /f\n\nImport-Module GroupPolicy\nGet-GPResultantSetOfPolicy `\n    -Computer 'PC-PILOT-01' `\n    -User 'CORP\\pilot.user' `\n    -ReportType Html `\n    -Path 'C:\\Temp\\PC-PILOT-01-RSoP.html'",'powershell');
paragraph('The HTML report identifies winning settings, applied/denied GPOs and filtering. Get-GPResultantSetOfPolicy requires the GroupPolicy RSAT module, report type/path and suitable remote connectivity/permissions. Reported configuration must still be compared with live control behavior.', 'گزارش HTML، Winning Setting، GPO اعمال/ردشده و Filtering را مشخص می‌کند. Get-GPResultantSetOfPolicy به ماژول RSAT GroupPolicy، ReportType/Path و ارتباط و مجوز Remote مناسب نیاز دارد. نتیجه گزارش با رفتار مؤثر کنترل مقایسه شود.');
table([['Issue','مشکل'],['Investigation','روش بررسی']], [
    [['GPO Not Applied','GPO Not Applied'],['Check enabled link, correct scope and enabled user/computer half.','Link فعال، Scope صحیح و بخش User/Computer فعال باشد.']],
    ['Security Filtering',['Target needs Read and Apply Group Policy; inspect Deny permissions.','Target به Read و Apply Group Policy نیاز دارد؛ Deny بررسی شود.']],
    ['WMI Filtering',['Test query, OS match, response time and errors on the target.','Query، OS Match، زمان پاسخ و خطا روی Target تست شود.']],
    ['Block Inheritance',['Inspect the target OU and Group Policy Inheritance tab.','OU و تب Group Policy Inheritance بررسی شود.']],
    ['Enforced GPO',['An enforced parent link can override lower-level settings.','Link بالادستی Enforced می‌تواند تنظیم پایین‌تر را Override کند.']],
    ['OU Placement',['Locate actual user/computer objects; Computers container is not an OU.','محل واقعی Object بررسی شود؛ Computers Container یک OU نیست.']],
    ['Computer vs User Configuration',['Check object scope and deliberate loopback Merge/Replace design.','Scope Object و طراحی Loopback Merge/Replace بررسی شود.']],
    ['Replication Delay',['Identify the selected DC and compare GPO versions across DCs.','DC منتخب و نسخه GPO در DCها مقایسه شود.']],
    ['SYSVOL Replication',['Check DFSR health, AD-side and SYSVOL-side versions separately.','سلامت DFSR و نسخه بخش AD و SYSVOL جدا بررسی شود.']],
    ['DNS Problems',['Use internal resolvers able to resolve AD SRV records.','Resolver داخلی قادر به Resolve رکورد AD SRV استفاده شود.']],
    [['New group membership','عضویت جدید گروه'],['Refresh user/computer security tokens; logon or restart may be required.','Token امنیتی تازه شود؛ Logon یا Restart ممکن است لازم باشد.']],
]);
paragraph('For narrowly filtered user GPOs, retain computer read access, typically via Domain Computers or the scoped computer group. Removing Authenticated Users from Apply filtering does not remove the computer-read requirement. Read is distinct from applying user settings to a computer.', 'برای User GPO با Filtering محدود، Read کامپیوتر را با Domain Computers یا گروه کامپیوتری هدف حفظ کنید. حذف Authenticated Users از Apply Filtering الزام Read کامپیوتر را حذف نمی‌کند. Read با Apply شدن User Setting به Computer متفاوت است.');
code("nltest /dsgetdc:corp.example\nrepadmin /replsummary\ndcdiag /test:DNS\n\nResolve-DnsName -Type SRV '_ldap._tcp.dc._msdcs.corp.example'\nTest-Path '\\\\corp.example\\SYSVOL\\corp.example\\Policies'\nTest-ComputerSecureChannel -Verbose\n\nGet-WinEvent -LogName 'Microsoft-Windows-GroupPolicy/Operational' -MaxEvents 50",'powershell');
paragraph('Run replication/DC diagnostics on an authorized host with the required tools. Test-ComputerSecureChannel is for member computers, not DC validation. SYSVOL accessibility does not prove all DC replicas are healthy. Check Microsoft-Windows-GroupPolicy/Operational on endpoints and DFS Replication on DCs; identify the failing DC/extension before repair.', 'Diagnostic مربوط به DC/Replication روی Host مجاز با ابزار لازم اجرا شود. Test-ComputerSecureChannel برای Member Computer است، نه اعتبارسنجی DC. دسترسی SYSVOL سلامت همه Replicaها را ثابت نمی‌کند. Microsoft-Windows-GroupPolicy/Operational در Endpoint و DFS Replication در DC بررسی و DC/Extension خطادار پیش از Repair مشخص شود.');

section('best-practices', 'Enterprise GPO best practices', 'Best Practices عملیاتی GPO');
checklist([
    ['Keep Default Domain Policy mainly for domain password/account policies, not all security settings.','Default Domain Policy عمدتاً برای Domain Password/Account Policy حفظ شود، نه همه تنظیمات امنیتی.'],
    ['If a separate root password GPO is used, document precedence and avoid conflicting account settings.','در GPO مستقل Password در Domain Root، Precedence مستند و Account Setting متعارض حذف شود.'],
    ['Avoid extensive unexplained changes to Default Domain Controllers Policy; use separate DC hardening GPOs.','Default Domain Controllers Policy بدون دلیل گسترده تغییر نکند؛ DC Hardening در GPO جدا باشد.'],
    ['Use modular GPOs, standard names, owners and documented settings.','GPO Modular، نام استاندارد، مالک و تنظیم مستند داشته باشد.'],
    ['Use change management, representative pilot OUs and tested rollback.','Change Management، Pilot OU نماینده و Rollback تست‌شده وجود داشته باشد.'],
    ['Delegate edit, link and LAPS recovery rights using least privilege.','Edit، Link و LAPS Recovery با Least Privilege Delegate شوند.'],
    ['Review Microsoft security baselines for the exact OS version; document deviations instead of blindly importing them.','Security Baseline مایکروسافت برای نسخه دقیق OS بررسی و اختلاف مستند شود؛ Import کورکورانه انجام نشود.'],
    ['Back up GPOs and separately document links, filters and external dependencies.','Backup GPO و مستندسازی جداگانه Link، Filter و وابستگی خارجی انجام شود.'],
    ['Disable unused user/computer halves; use Enforced and expensive WMI filters only for a justified requirement.','بخش User/Computer استفاده‌نشده غیرفعال؛ Enforced و WMI پرهزینه فقط با نیاز مشخص استفاده شوند.'],
]);
table([['Naming convention','Naming Convention'],['Purpose','کاربرد']], [['GPO-SEC-xxxxx',['Security controls','کنترل امنیتی']],['GPO-OPS-xxxxx',['Operations and maintenance','عملیات و نگهداری']],['GPO-USR-xxxxx',['User configuration','تنظیم کاربری']],['GPO-SRV-xxxxx',['Server role configuration','تنظیم نقش Server']]]);
code("Import-Module GroupPolicy\nNew-Item -Path 'D:\\GPO-Backup' -ItemType Directory -Force\nBackup-GPO -All -Path 'D:\\GPO-Backup' -Comment 'Before security policy rollout'",'powershell');
paragraph('GPO backup is not a complete AD backup and does not substitute for capturing OU links, filtering, delegation and recovery dependencies. Retain a change record with before/after values and verification evidence.', 'Backup GPO، Backup کامل AD نیست و جای ثبت Link OU، Filtering، Delegation و وابستگی Recovery را نمی‌گیرد. Change Record با مقدار قبل/بعد و شواهد Verification حفظ شود.');
source('https://learn.microsoft.com/en-us/windows/security/operating-system-security/device-management/windows-security-configuration-framework/security-compliance-toolkit-10','Microsoft Security Compliance Toolkit');

section('summary', 'Summary: purpose, target and priority', 'جدول Summary: هدف، Target و Priority');
table([['GPO','GPO'],['Purpose','Purpose'],['Target','Target'],['Recommended Setting','Recommended Setting'],['Risk if Disabled','Risk if Disabled'],['Priority','Priority']], [
    ['Password & Lockout',['Credential protection','حفاظت Credential'],'Domain accounts',['History 24; length ≥14; lockout 10/15/15','History 24؛ طول ≥14؛ Lockout 10/15/15'],['Guessing and credential abuse','حدس رمز و سوءاستفاده Credential'],'Critical'],
    ['Defender',['Malware protection','حفاظت Malware'],'Clients / supported servers',['Real-time, behavior, cloud, PUA enabled','Real-time، Behavior، Cloud و PUA فعال'],['Reduced detection and prevention','کاهش تشخیص و پیشگیری'],'Critical'],
    ['Firewall',['Host network boundary','مرز شبکه Host'],'All hosts; role-specific rules','On; Inbound Block; Outbound Allow',['Lateral movement and service exposure','حرکت جانبی و Exposure سرویس'],'Critical'],
    ['Windows Update',['Patch compliance','Compliance وصله‌گذاری'],'Clients / servers separately',['WSUS/source, rings, coordinated restart','منبع، Ring و Restart هماهنگ'],['Unpatched vulnerabilities','آسیب‌پذیری Patch نشده'],'Critical'],
    ['USB Control',['Removable-media control','کنترل رسانه قابل حمل'],'Workstations','Read Only / Block',['Exfiltration and removable-media malware','خروج داده و بدافزار USB'],'High'],
    ['Screen Lock',['Session protection','حفاظت نشست'],'Workstations + users','900 seconds; reauthentication',['Abandoned session misuse','سوءاستفاده نشست رهاشده'],'Medium'],
    ['Local Admin & LAPS',['Privilege and password control','کنترل Privilege و رمز'],'Workstations / member servers',['Narrow membership; unique rotating password','عضویت محدود؛ رمز مستقل و Rotate'],['Shared-credential lateral compromise','نفوذ جانبی با Credential مشترک'],'Critical'],
    ['RDP Hardening',['Remote access protection','حفاظت Remote'],'RDP-enabled hosts','NLA; TLS; scoped users/firewall',['Unauthorized remote access','دسترسی Remote غیرمجاز'],'Critical'],
    ['Audit Logging',['Detection and evidence','تشخیص و شواهد'],'DCs / servers / clients','Advanced Audit; 4688; central collection',['Missing evidence and late detection','نبود شواهد و تشخیص دیرهنگام'],'High'],
    ['Application Hardening',['Reduce malicious execution','کاهش اجرای مخرب'],'Users / relevant endpoints','Macro block; ActiveX control; SmartScreen; PS logs',['Malicious content and reduced visibility','محتوای مخرب و کاهش Visibility'],'High'],
]);
paragraph('Priority expresses importance, not rollout order. Sequence changes by service dependency, lockout risk and recovery readiness. This runbook is a tested-deployment design, not an assurance that the commands were executed against an organization’s domain.', 'Priority اهمیت کنترل را نشان می‌دهد، نه ترتیب Rollout. ترتیب با وابستگی سرویس، خطر Lockout و آمادگی Recovery تعیین شود. این Runbook طراحی استقرار قابل تست است؛ اجرای فرمان‌ها روی Domain واقعی سازمان از آن نتیجه نمی‌شود.');

$faq = [
    ['Where should the domain password policy be linked?', 'Link the authoritative domain account policy at the domain root. A password GPO linked only to a Users OU does not establish a separate password policy for those domain users. Use FGPP for selected users or global security groups.', 'Password Policy اصلی Domain کجا لینک شود؟', 'Policy معتبر حساب Domain در Domain Root لینک شود. Password GPO که فقط به Users OU لینک شده باشد، Policy رمز جداگانه برای کاربران Domain ایجاد نمی‌کند. برای User یا Global Security Group منتخب از FGPP استفاده کنید.'],
    ['Does Windows LAPS remove local administrator membership?', 'No. Windows LAPS rotates and backs up a managed local account password. Control administrator membership separately through a reviewed Restricted Groups or Local Users and Groups policy.', 'آیا Windows LAPS عضویت Local Administrator را حذف می‌کند؟', 'خیر. Windows LAPS رمز حساب محلی مدیریت‌شده را Rotate و Backup می‌کند. عضویت Administrator باید جداگانه با Restricted Groups یا Local Users and Groups بررسی‌شده کنترل شود.'],
    ['Can GPO manage Defender Tamper Protection?', 'GPO cannot enable or disable Tamper Protection. Protected settings may reject GPO changes even when processing succeeds; verify the effective Defender state and use supported central management.', 'آیا Tamper Protection با GPO مدیریت می‌شود؟', 'GPO نمی‌تواند Tamper Protection را فعال یا غیرفعال کند. Setting محافظت‌شده ممکن است تغییر GPO را باوجود پردازش موفق رد کند؛ وضعیت مؤثر Defender و ابزار مرکزی پشتیبانی‌شده را بررسی کنید.'],
    ['Does blocking removable disks disable every USB device?', 'No. Removable Storage Access controls storage classes. Keyboard, mouse and other USB devices are separate; broad Device Installation Restrictions need an explicit compatibility pilot.', 'آیا Block کردن USB Storage همه دستگاه‌های USB را قطع می‌کند؟', 'خیر. Removable Storage Access کلاس‌های Storage را کنترل می‌کند. Keyboard، Mouse و سایر Deviceها جدا هستند؛ Device Installation Restrictions گسترده به Pilot سازگاری نیاز دارد.'],
    ['Do Active Hours create a server maintenance window?', 'No. Active Hours affects restart behavior. Cluster drain, failover, installation sequencing and health checks require an orchestrator or an approved operational runbook.', 'آیا Active Hours یک Maintenance Window برای Server ایجاد می‌کند؟', 'خیر. Active Hours رفتار Restart را کنترل می‌کند. Drain، Failover، ترتیب نصب و Health Check مربوط به Cluster به Orchestrator یا Runbook عملیاتی تأییدشده نیاز دارند.'],
    ['Is a successful gpupdate sufficient verification?', 'No. Inspect gpresult and the winning settings, then check effective endpoint state and test allowed and denied behavior. Replication, filtering, competing managers and tamper protection can change the outcome.', 'آیا موفقیت gpupdate برای Verification کافی است؟', 'خیر. gpresult و Winning Setting را بررسی، سپس وضعیت مؤثر Endpoint و رفتار مجاز و غیرمجاز را تست کنید. Replication، Filtering، ابزار مدیریت متعارض و Tamper Protection می‌توانند بر نتیجه اثر بگذارند.'],
    ['Should PowerShell be disabled across the organization?', 'Keep required administrative functionality and apply least privilege, logging, signed-script workflows and application control. Execution Policy alone is not a security boundary; validate Windows PowerShell and PowerShell 7 separately.', 'آیا PowerShell باید در کل سازمان Disable شود؟', 'قابلیت مدیریتی لازم را حفظ و Least Privilege، Logging، Script Signing و Application Control را اعمال کنید. Execution Policy به‌تنهایی مرز امنیتی نیست؛ Windows PowerShell و PowerShell 7 جدا اعتبارسنجی شوند.'],
    ['Does unlinking a GPO restore the previous configuration?', 'Not reliably for every setting. Preferences, group membership and persistent changes can remain. Record the previous state and test an explicit rollback before production rollout.', 'آیا Unlink کردن GPO تنظیمات قبلی را برمی‌گرداند؟', 'برای همه Settingها قابل اتکا نیست. Preferences، عضویت گروه و تغییرات باقی‌مانده ممکن است حفظ شوند. وضعیت قبلی را ثبت و Rollback صریح را پیش از Production تست کنید.'],
];
section('faq', 'Frequently Asked Questions', 'سؤالات متداول');
$parts[] = '<div class="article-faq">';
foreach ($faq as [$questionEn,$answerEn,$questionFa,$answerFa]) {
    $parts[] = '<div class="article-faq-item">'.dual('h3',$questionEn,$questionFa).dual('p',$answerEn,$answerFa).'</div>';
}
$parts[] = '</div>';

section('enterprise-checklist', 'Enterprise Deployment Checklist', 'Enterprise Deployment Checklist');
heading('Before deployment', 'قبل از Deploy');
checklist([
    ['Inventory OS edition/build, lifecycle and update support.','Edition/Build، Lifecycle و پوشش Update ثبت شده است.'],
    ['Version and validate Windows, Edge, Office and LAPS templates.','Template مربوط به Windows، Edge، Office و LAPS نسخه‌بندی و بررسی شده است.'],
    ['Approve values through security policy and change management.','مقادیر با Security Policy و Change Management تأیید شده‌اند.'],
    ['Separate DC, server, workstation and user scope.','Scope مربوط به DC، Server، Workstation و User جدا شده است.'],
    ['Identify management ownership and GPO/MDM/MDE conflicts.','مالک مدیریت و تداخل GPO/MDM/MDE مشخص است.'],
    ['Back up GPOs and record links, filtering and permissions.','Backup GPO و Link، Filtering و Permission ثبت شده است.'],
    ['Check DNS, AD replication and SYSVOL/DFSR health.','سلامت DNS، AD Replication و SYSVOL/DFSR بررسی شده است.'],
    ['Select representative pilot devices/users and a rollout stop criterion.','Pilot نماینده و معیار توقف Rollout تعیین شده است.'],
    ['Prepare firewall management rules and console/OOB access.','Rule مدیریت Firewall و Console/OOB آماده است.'],
    ['Record local administrators and confirm LAPS schema, ACLs and decryption.','Local Administrator ثبت و Schema، ACL و Decryption مربوط به LAPS بررسی شده است.'],
    ['Plan maintenance, restart, log capacity, retention and SIEM ingestion.','Maintenance، Restart، ظرفیت Log، Retention و SIEM برنامه‌ریزی شده است.'],
    ['Test the explicit rollback and recovery procedure.','Rollback صریح و فرآیند Recovery تست شده است.'],
]);
heading('After deployment', 'پس از Deploy');
checklist([
    ['Run gpupdate and any required restart/logoff.','gpupdate و Restart/Logoff لازم انجام شده است.'],
    ['Review gpresult winning settings and effective endpoint state.','Winning Setting در gpresult و وضعیت مؤثر Endpoint بررسی شده است.'],
    ['Test both allowed and denied RDP/firewall scenarios.','سناریوی مجاز و غیرمجاز RDP/Firewall تست شده است.'],
    ['Confirm legitimate administration and recovery still work.','دسترسی مدیریت مجاز و Recovery برقرار است.'],
    ['Verify LAPS retrieval and post-use password rotation.','بازیابی LAPS و Rotate پس از استفاده تست شده است.'],
    ['Test USB read/write/execute and workstation lock timing.','Read/Write/Execute روی USB و زمان Lock تست شده است.'],
    ['Verify patch compliance and post-restart application health.','Compliance وصله و سلامت برنامه پس از Restart بررسی شده است.'],
    ['Confirm audit/PowerShell events and alerts reach the central platform.','Event و Alert مربوط به Audit/PowerShell به سامانه مرکزی می‌رسند.'],
    ['Validate Office macro, ActiveX and Edge policy on actual builds.','Macro، ActiveX و Edge Policy روی Build واقعی تست شده‌اند.'],
    ['Record incidents, exceptions and baseline deviations.','Incident، استثنا و اختلاف Baseline ثبت شده‌اند.'],
    ['Expand scope only after pilot approval and assign periodic review ownership.','Scope پس از تأیید Pilot توسعه و مالک بازبینی دوره‌ای تعیین شده است.'],
]);
section('references', 'Official Microsoft References', 'منابع و مراجع رسمی مایکروسافت');
paragraph('Consult the documentation that matches the deployed OS, product build and administrative templates before changing production policy.', 'پیش از تغییر Policy در Production، مستندات منطبق با OS، Build محصول و Administrative Template نصب‌شده را بررسی کنید.');
$parts[] = '<ul>';
foreach ($sources as $url => $label) $parts[] = '<li>'.dual('a',$label,'مرجع رسمی: '.$label,'href="'.esc($url).'" rel="noopener"').'</li>';
$parts[] = '</ul></section>';
$localizations = []; foreach (['en','fa'] as $lang) $localizations[$lang] = ['title'=>$title[$lang],'meta_title'=>$lang==='fa'?'۱۰ Group Policy ضروری برای امنیت Windows Domain | MeetAJ':'10 Essential Enterprise Group Policies | MeetAJ','description'=>$description[$lang],'keywords'=>$keywords,'faq'=>array_map(fn($item)=>$lang==='fa'?[$item[2],$item[3]]:[$item[0],$item[1]],$faq)];
$nav = ''; foreach ($toc as [$id,$en,$fa]) $nav .= '<li class="article-nav-item">'.dual('a',$en,$fa,'href="#'.$id.'"').'</li>';
$banner = '/assets/img/articles/banners/10-Essential-Group-Policies-for-Enterprise-Windows.png';
$bannerSource = $root.'/resources'.$banner; if (!is_file($bannerSource)) throw new RuntimeException('Missing banner');
// Export the supplied square banner to the requested 1000×1000 dimensions.
// Preserve the original in the editorial package; never resize an already exported source.
$package = $root.'/resources/content/articles/'.$slug; if (!is_dir($package)) mkdir($package,0775,true);
$original = $package.'/banner-original.png'; if (!is_file($original)) copy($bannerSource,$original);
$bannerDestination = $root.'/public'.$banner; if (!is_dir(dirname($bannerDestination))) mkdir(dirname($bannerDestination),0775,true);
$image = imagecreatefrompng($original); $export = imagecreatetruecolor(1000,1000);
imagecopyresampled($export,$image,0,0,0,0,1000,1000,imagesx($image),imagesy($image)); imagepng($export,$bannerSource); copy($bannerSource,$bannerDestination); imagedestroy($image); imagedestroy($export);
$url = 'https://meetaj.ir/articles/'.$slug;
$schema = ['@context'=>'https://schema.org','@type'=>'Article','headline'=>$title['en'],'description'=>$description['en'],'inLanguage'=>'en','datePublished'=>'2026-10-07T00:00:00+03:30','dateModified'=>'2026-10-07','image'=>'https://meetaj.ir'.$banner,'author'=>['@type'=>'Person','name'=>'AmirHossein Jalalian']];
$html = '<!doctype html><html lang="en" dir="ltr" data-article-language="en"><head><meta charset="UTF-8"><title>'.esc($localizations['en']['meta_title']).'</title><meta name="article:content-language" content="en"><meta name="description" content="'.esc($description['en']).'"><meta name="keywords" content="'.esc(implode(', ',$keywords)).'"><meta property="og:title" content="'.esc($title['en']).'"><meta property="og:description" content="'.esc($description['en']).'"><meta property="og:type" content="article"><meta property="og:image" content="'.esc($banner).'"><link rel="canonical" href="'.$url.'"><script id="article-localizations" type="application/json">'.json_encode($localizations,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script><script type="application/ld+json">'.json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script></head><body class="article-page theme-microsoft"><header><nav><ul>'.$nav.'</ul></nav></header><main><section class="article-hero article-header hero">'.dual('span','Microsoft','مایکروسافت','class="article-category"').dual('h1',$title['en'],$title['fa'],'class="article-title hero-title"').dual('p',$description['en'],$description['fa'],'class="article-excerpt hero-subtitle"').'<img class="article-hero-thumbnail" src="'.esc($banner).'" alt="Ten essential Group Policies for enterprise Windows" width="1000" height="1000"></section><article class="article-body" lang="en" dir="ltr">'.implode("\n",$parts).'</article></main></body></html>';
require_once dirname(__DIR__).'/app/Services/ArticleStructure.php';
$html = (new \App\Services\ArticleStructure)->repair($html, $slug);
file_put_contents($root.'/resources/legacy/articles/'.$slug.'.html',$html);
echo 'Built '.$slug.': '.count($toc).' bilingual sections, five supplied images and localized SEO.'.PHP_EOL;
