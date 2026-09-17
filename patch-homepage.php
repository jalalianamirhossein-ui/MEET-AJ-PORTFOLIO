<?php

declare(strict_types=1);

$path = __DIR__.'/index.html';
$html = file_get_contents($path);
if ($html === false) {
    throw new RuntimeException('Unable to read index.html');
}

$skills = <<<'HTML'
        <div class="container" data-aos="fade-up" data-aos-delay="100">
          <div class="skills-board">
            <section class="skill-group">
              <h3 data-en="Infrastructure" data-fa="زیرساخت">Infrastructure</h3>
              <ul class="skill-chip-row">
                <li>Linux</li>
                <li>Windows Server</li>
                <li>Active Directory</li>
                <li>DNS / DFS / WSUS</li>
                <li data-en="Backup (Veeam)" data-fa="پشتیبان‌گیری (Veeam)">Backup (Veeam)</li>
              </ul>
            </section>
            <section class="skill-group">
              <h3 data-en="Networking" data-fa="شبکه">Networking</h3>
              <ul class="skill-chip-row">
                <li>Cisco</li>
                <li>MikroTik</li>
                <li data-en="Firewall" data-fa="فایروال">Firewall</li>
                <li>VPN</li>
                <li>VoIP</li>
              </ul>
            </section>
            <section class="skill-group">
              <h3 data-en="Virtualization & Cloud" data-fa="مجازی‌سازی و ابر">Virtualization & Cloud</h3>
              <ul class="skill-chip-row">
                <li>VMware vSphere</li>
                <li>KVM</li>
                <li>AWS</li>
                <li>Azure</li>
                <li>SQL Server HA</li>
              </ul>
            </section>
            <section class="skill-group">
              <h3 data-en="DevOps & Operations" data-fa="DevOps و عملیات">DevOps & Operations</h3>
              <ul class="skill-chip-row">
                <li>Docker</li>
                <li>CI/CD</li>
                <li>GitLab / Jenkins</li>
                <li>Ansible</li>
                <li>Terraform</li>
                <li>Zabbix / Grafana</li>
              </ul>
            </section>
            <section class="skill-group">
              <h3 data-en="Professional" data-fa="مهارت‌های حرفه‌ای">Professional</h3>
              <ul class="skill-chip-row">
                <li data-en="Documentation" data-fa="مستندسازی">Documentation</li>
                <li data-en="Troubleshooting" data-fa="عیب‌یابی">Troubleshooting</li>
                <li data-en="Planning" data-fa="برنامه‌ریزی">Planning</li>
                <li data-en="Communication" data-fa="ارتباط با مشتری">Communication</li>
              </ul>
            </section>
          </div>
        </div>
HTML;

$start = strpos($html, '==================== SKILLS CONTENT ==================');
$end = strpos($html, '<!-- End Skills Container -->');
if ($start === false || $end === false) {
    throw new RuntimeException('Unable to locate skills content');
}
$commentOpen = strrpos(substr($html, 0, $start), '<!--');
if ($commentOpen === false) {
    throw new RuntimeException('Unable to locate skills comment');
}

$html = substr($html, 0, $commentOpen).$skills."\n        ".substr($html, $end);

$html = str_replace('site-modules.css?v=1802', 'site-modules.css?v=1803', $html);
$html = str_replace('site-modules.css?v=1801', 'site-modules.css?v=1803', $html);

file_put_contents($path, $html);
echo "skills patched\n";
