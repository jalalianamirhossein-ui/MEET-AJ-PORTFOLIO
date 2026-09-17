<?php

declare(strict_types=1);

$path = __DIR__.'/index.html';
$html = file_get_contents($path);
if ($html === false) {
    fwrite(STDERR, "Unable to read index.html\n");
    exit(1);
}

$skills = <<<'HTML'
                <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="skills-board skills-content skills-animation">
            <article class="skill-group">
              <h3 data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</h3>
              <div class="progress">
                <span class="skill"><span>Linux</span> <i class="val">100%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Windows Server</span> <i class="val">92%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Active Directory</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>DNS / DFS / WSUS</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Backup (Veeam)" data-fa="پشتیبان‌گیری (Veeam)">Backup (Veeam)</span> <i class="val">77%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="77" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Networking" data-fa="شبکه">Networking</h3>
              <div class="progress">
                <span class="skill"><span>Cisco</span> <i class="val">92%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>MikroTik</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Firewall" data-fa="فایروال">Firewall</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>VPN</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>VoIP</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Virtualization & Cloud" data-fa="مجازی‌سازی و ابر">Virtualization & Cloud</h3>
              <div class="progress">
                <span class="skill"><span>VMware vSphere</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>KVM</span> <i class="val">82%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>AWS</span> <i class="val">78%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Azure</span> <i class="val">76%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="76" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>SQL Server HA</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="DevOps & Operations" data-fa="DevOps و عملیات">DevOps & Operations</h3>
              <div class="progress">
                <span class="skill"><span>Docker</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>CI/CD</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>GitLab / Jenkins</span> <i class="val">82%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Ansible</span> <i class="val">80%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Terraform</span> <i class="val">75%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span>Zabbix / Grafana</span> <i class="val">86%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="86" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
            <article class="skill-group">
              <h3 data-en="Professional" data-fa="مهارت‌های حرفه‌ای">Professional</h3>
              <div class="progress">
                <span class="skill"><span data-en="Documentation" data-fa="مستندسازی">Documentation</span> <i class="val">90%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Troubleshooting" data-fa="عیب‌یابی">Troubleshooting</span> <i class="val">95%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Planning" data-fa="برنامه‌ریزی">Planning</span> <i class="val">85%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
              <div class="progress">
                <span class="skill"><span data-en="Communication" data-fa="ارتباط با مشتری">Communication</span> <i class="val">88%</i></span>
                <div class="progress-bar-wrap"><div class="progress-bar" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div></div>
              </div>
            </article>
          </div>
        </div>
        <!-- End Skills Container -->
HTML;

$start = strpos($html, '<div class="skills-board">');
$end = strpos($html, '        <!-- End Skills Container -->');
if ($start === false || $end === false || $end < $start) {
    fwrite(STDERR, "Unable to locate skills container\n");
    exit(1);
}

$open = strrpos(substr($html, 0, $start), '<div class="container"');
if ($open === false) {
    fwrite(STDERR, "Unable to locate skills container open tag\n");
    exit(1);
}

$html = substr($html, 0, $open).$skills.substr($html, $end + strlen('        <!-- End Skills Container -->'));

$replacements = [
    'assets/css/site-modules.css?v=1806' => 'assets/css/site-modules.css?v=1807',
    'assets/css/lang-toggle.css?v=1300' => 'assets/css/lang-toggle.css?v=1301',
    'assets/js/main.js?v=1402' => 'assets/js/main.js?v=1403',
    'assets/js/i18n.js?v=1300' => 'assets/js/i18n.js?v=1301',
];
$html = strtr($html, $replacements);

if (! str_contains($html, 'assets/js/contact-form.js')) {
    $html = str_replace(
        '    <script src="assets/js/main.js?v=1403" defer></script>',
        "    <script src=\"assets/js/contact-form.js?v=1403\" defer></script>\n    <script src=\"assets/js/main.js?v=1403\" defer></script>",
        $html
    );
}

if (file_put_contents($path, $html) === false) {
    fwrite(STDERR, "Unable to write index.html\n");
    exit(1);
}

echo "patched index.html skills + assets\n";
