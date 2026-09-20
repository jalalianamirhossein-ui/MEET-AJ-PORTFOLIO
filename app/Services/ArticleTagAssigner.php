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
        'linux' => 'Linux',
        'microsoft' => 'Microsoft',
        'mikrotik' => 'MikroTik',
        'vmware' => 'VMware',
        'windows-server' => 'Windows Server',
        'networking' => 'Networking',
        'security' => 'Security',
        'devops' => 'DevOps',
        'ubuntu' => 'Ubuntu',
        'netbox' => 'NetBox',
        'network-backup' => 'Network Backup',
        'nginx' => 'Nginx',
        'sql-server' => 'SQL Server',
        'virtualization' => 'Virtualization',
        'ssh' => 'SSH',
        'netplan' => 'Netplan',
        'windows' => 'Windows',
        'https' => 'HTTPS',
        'backup' => 'Backup',
        'email' => 'Email',
        'hardware' => 'Hardware',
        'usb' => 'USB',
    ];

    public function syncCatalog(): void
    {
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
            'netbox-installation-setup-ubuntu' => ['netbox', 'ubuntu', 'networking'],
            'oxidized-network-device-configuration-backup' => ['network-backup', 'backup', 'ubuntu', 'networking'],
            'nginx-installation-configuration-ubuntu' => ['nginx', 'ubuntu', 'devops', 'linux'],
            'linux-security-account-access-management' => ['linux', 'security'],
            'mikrotik-unequal-dual-wan-load-balancing-ecmp' => ['mikrotik', 'networking'],
            'sql-server-automatic-backup-job' => ['sql-server', 'microsoft', 'backup'],
            'vsphere-standard-switch-vs-distributed-switch' => ['vmware', 'virtualization', 'networking'],
            'mikrotik-openvpn-setup-v7' => ['mikrotik', 'networking', 'security'],
            'enable-ssh-linux-complete-guide' => ['ssh', 'linux', 'security'],
            'set-static-ip-ubuntu-server-netplan' => ['ubuntu', 'netplan', 'linux', 'networking'],
            'linux-cli-common-commands' => ['linux'],
            'install-mikrotik-chr-vmware-workstation' => ['mikrotik', 'vmware', 'virtualization'],
            'install-vmware-esxi-vmware-workstation-vmcisr' => ['vmware', 'virtualization'],
            'install-dfs-server-windows-server' => ['windows-server', 'microsoft', 'backup'],
            'http-vs-https-ssl-certificate-impact' => ['https', 'security'],
            'mikrotik-block-port-scanners' => ['mikrotik', 'security', 'networking'],
            'mikrotik-block-website' => ['mikrotik', 'security', 'networking'],
            'downgrade-mikrotik-routeros-firmware-safely' => ['mikrotik', 'security'],
            'windows-cmd-common-network-commands' => ['windows', 'microsoft', 'networking'],
            'windows-password-reset-secure-access-recovery' => ['windows', 'microsoft', 'security'],
            'ubuntu-date-time-settings' => ['ubuntu', 'linux'],
            'imap-vs-pop3-email-protocol-comparison' => ['email', 'security'],
            'windows-hardware-info-cmd-vs-dxdiag' => ['windows', 'microsoft', 'hardware'],
            'vmware-esxi-8-installation-basic-configuration' => ['vmware', 'virtualization'],
            'creating-a-bootable-usb' => ['usb', 'windows', 'linux'],
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
