<?php

// Run from the project root: php scripts/update-article-order.php
// Updates only sort_order; aborts if the expected article set is incomplete.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$slugs = [
    'netbox-installation-setup-ubuntu',
    'oxidized-network-device-configuration-backup',
    'nginx-installation-configuration-ubuntu',
    'linux-security-account-access-management',
    'mikrotik-unequal-dual-wan-load-balancing-ecmp',
    'sql-server-automatic-backup-job',
    'vsphere-standard-switch-vs-distributed-switch',
    'mikrotik-openvpn-setup-v7',
    'enable-ssh-linux-complete-guide',
    'set-static-ip-ubuntu-server-netplan',
    'linux-cli-common-commands',
    'install-mikrotik-chr-vmware-workstation',
    'install-vmware-esxi-vmware-workstation-vmcisr',
    'install-dfs-server-windows-server',
    'http-vs-https-ssl-certificate-impact',
    'mikrotik-block-port-scanners',
    'mikrotik-block-website',
    'downgrade-mikrotik-routeros-firmware-safely',
    'windows-cmd-common-network-commands',
    'windows-password-reset-secure-access-recovery',
    'ubuntu-date-time-settings',
    'imap-vs-pop3-email-protocol-comparison',
    'windows-hardware-info-cmd-vs-dxdiag',
    'vmware-esxi-8-installation-basic-configuration',
    'creating-a-bootable-usb',
];

DB::transaction(function () use ($slugs): void {
    $before = DB::table('articles')->orderBy('id')->lockForUpdate()->get();
    $actual = $before->pluck('slug')->all();
    $missing = array_diff($slugs, $actual);
    $unexpected = array_diff($actual, $slugs);

    if ($missing || $unexpected || count($actual) !== count($slugs)) {
        throw new RuntimeException('Article set mismatch. Missing: '.implode(', ', $missing)
            .'; unexpected: '.implode(', ', $unexpected).'. No ordering changes applied.');
    }

    $start = strtotime('2024-01-01 00:00:00');
    $end = time();
    $timestamps = [];
    mt_srand(20240920);
    for ($i = 0; $i < count($slugs) - 1; $i++) {
        $timestamps[] = mt_rand($start, $end - 86400);
    }
    sort($timestamps);
    $timestamps[] = mt_rand(strtotime('today 00:00:00'), $end);

    foreach ($slugs as $order => $slug) {
        DB::table('articles')->where('slug', $slug)->update([
            'sort_order' => $order,
            'published_at' => date('Y-m-d H:i:s', $timestamps[$order]),
        ]);
    }

    $after = DB::table('articles')->orderBy('id')->get();
    foreach ($before as $index => $original) {
        $updated = $after[$index];
        $oldFields = (array) $original;
        $newFields = (array) $updated;
        unset($oldFields['sort_order'], $oldFields['published_at'], $newFields['sort_order'], $newFields['published_at']);
        if ($oldFields !== $newFields) {
            throw new RuntimeException('Non-ordering article fields changed; rolling back.');
        }
    }

    $ordered = $after->sortBy('sort_order')->values();
    if ($ordered->pluck('slug')->all() !== $slugs
        || $ordered->pluck('sort_order')->map(fn ($value) => (int) $value)->all() !== range(0, 24)) {
        throw new RuntimeException('Order verification failed; rolling back.');
    }
});

foreach ($slugs as $order => $slug) {
    echo $order.' '.$slug.PHP_EOL;
}
echo 'Verified: NetBox first, unique sort_order values 0-24, all other article fields unchanged.'.PHP_EOL;
