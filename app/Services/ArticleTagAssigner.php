<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Str;

class ArticleTagAssigner
{
    /**
     * Tags derived from live titles, categories, and article bodies.
     * Docker is omitted: no article in this library covers it.
     *
     * @var array<string, string>
     */
    public const CATALOG = [
        'microsoft' => 'Microsoft',
        'windows' => 'Windows',
        'cisco' => 'Cisco',
        'fortinet' => 'Fortinet',
        'supermicro' => 'Supermicro',
        'hpe' => 'HPE',
        'ubiquiti' => 'Ubiquiti',
        'juniper' => 'Juniper',
        'avaya' => 'AVAYA',
        'qnap' => 'QNAP',
        'dell' => 'DELL',
        'linux' => 'Linux',
        'ubuntu' => 'Ubuntu',
        'mikrotik' => 'MikroTik',
        'vmware' => 'VMware',
        'esxi' => 'VMware ESXi',
        'vsphere' => 'VMware vSphere',
        'nginx' => 'NGINX',
        'netbox' => 'NetBox',
        'oxidized' => 'Oxidized',
        'sql-server' => 'Microsoft SQL Server',
        'openvpn' => 'OpenVPN',
        'ssh' => 'OpenSSH',
    ];

    public function syncCatalog(): void
    {
        Tag::query()->whereNotIn('slug', array_keys(self::CATALOG))->get()->each(function (Tag $tag): void {
            $tag->articles()->detach();
            $tag->delete();
        });
        foreach (self::CATALOG as $slug => $name) {
            Tag::query()->firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }

    public function syncArticle(Article $article): void
    {
        $this->syncCatalog();
        $slugs = $this->slugsFor($article);
        $ids = Tag::query()->whereIn('slug', $slugs)->pluck('id');
        $article->tags()->sync($ids);
    }

    public function syncPublishedLibrary(): int
    {
        $this->syncCatalog();
        $count = 0;
        Article::query()->with('category')->each(function (Article $article) use (&$count): void {
            $this->syncArticle($article);
            $count++;
        });

        return $count;
    }

    /**
     * @return list<string>
     */
    public function slugsFor(Article $article): array
    {
        $specific = [
            'netbox-installation-setup-ubuntu' => ['netbox', 'ubuntu', 'linux'],
            'oxidized-network-device-configuration-backup' => ['oxidized', 'ubuntu', 'linux'],
            'nginx-installation-configuration-ubuntu' => ['nginx', 'ubuntu', 'linux'],
            'linux-security-account-access-management' => ['linux'],
            'mikrotik-unequal-dual-wan-load-balancing-ecmp' => ['mikrotik'],
            'sql-server-automatic-backup-job' => ['sql-server', 'microsoft'],
            'vsphere-standard-switch-vs-distributed-switch' => ['vsphere', 'vmware'],
            'mikrotik-openvpn-setup-v7' => ['mikrotik', 'openvpn'],
            'enable-ssh-linux-complete-guide' => ['ssh', 'linux'],
            'set-static-ip-ubuntu-server-netplan' => ['ubuntu', 'linux'],
            'linux-cli-common-commands' => ['linux'],
            'install-mikrotik-chr-vmware-workstation' => ['mikrotik', 'vmware'],
            'install-vmware-esxi-vmware-workstation-vmcisr' => ['esxi', 'vmware'],
            'install-dfs-server-windows-server' => ['windows', 'microsoft'],
            'http-vs-https-ssl-certificate-impact' => ['microsoft'],
            'mikrotik-block-port-scanners' => ['mikrotik'],
            'mikrotik-block-website' => ['mikrotik'],
            'downgrade-mikrotik-routeros-firmware-safely' => ['mikrotik'],
            'windows-cmd-common-network-commands' => ['windows', 'microsoft'],
            'windows-password-reset-secure-access-recovery' => ['windows', 'microsoft'],
            'ubuntu-date-time-settings' => ['ubuntu', 'linux'],
            'imap-vs-pop3-email-protocol-comparison' => ['microsoft'],
            'windows-hardware-info-cmd-vs-dxdiag' => ['windows', 'microsoft'],
            'vmware-esxi-8-installation-basic-configuration' => ['esxi', 'vmware'],
            'creating-a-bootable-usb' => ['windows', 'linux'],
        ];
        if (isset($specific[$article->slug])) {
            return $specific[$article->slug];
        }

        $haystack = Str::lower(implode(' ', array_filter([
            $article->title,
            $article->slug,
            $article->excerpt,
            $article->category?->name,
            $article->filterClass(),
        ])));

        $slugs = [];

        if (str_contains($haystack, 'linux') || str_contains($haystack, 'ubuntu') || str_contains($haystack, 'nginx') || str_contains($haystack, 'filter-linux')) {
            $slugs[] = 'linux';
        }
        if (str_contains($haystack, 'microsoft') || str_contains($haystack, 'windows') || str_contains($haystack, 'sql-server') || str_contains($haystack, 'filter-microsoft')) {
            $slugs[] = 'microsoft';
        }
        if (str_contains($haystack, 'mikrotik') || str_contains($haystack, 'routeros') || str_contains($haystack, 'filter-mikrotik')) {
            $slugs[] = 'mikrotik';
        }
        if (str_contains($haystack, 'vmware') || str_contains($haystack, 'esxi') || str_contains($haystack, 'vsphere') || str_contains($haystack, 'filter-vmware')) {
            $slugs[] = 'vmware';
        }
        if (str_contains($haystack, 'windows-server') || str_contains($haystack, 'windows server') || str_contains($haystack, 'dfs')) {
            $slugs[] = 'windows-server';
        }
        if (
            str_contains($haystack, 'network') || str_contains($haystack, 'vpn') || str_contains($haystack, 'openvpn')
            || str_contains($haystack, 'load-balancing') || str_contains($haystack, 'netplan') || str_contains($haystack, 'switch')
        ) {
            $slugs[] = 'networking';
        }
        if (
            str_contains($haystack, 'security') || str_contains($haystack, 'ssh') || str_contains($haystack, 'https')
            || str_contains($haystack, 'ssl') || str_contains($haystack, 'password') || str_contains($haystack, 'scanner')
            || str_contains($haystack, 'openvpn')
        ) {
            $slugs[] = 'security';
        }
        if (str_contains($haystack, 'devops') || str_contains($haystack, 'nginx')) {
            $slugs[] = 'devops';
        }

        return array_values(array_unique($slugs));
    }
}
