<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Str;

class HomepageServiceCatalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            [
                'slug' => 'network-design',
                'icon' => 'bi bi-diagram-3',
                'sort' => 1,
                'title_en' => 'Enterprise Network Design & Implementation',
                'title_fa' => 'طراحی و پیاده‌سازی شبکه سازمانی',
                'card_title_en' => 'Enterprise Network Design',
                'card_title_fa' => 'طراحی شبکه سازمانی',
                'short_en' => 'Network design, Cisco/MikroTik, VLAN, STP, EtherChannel, routing, wireless, and branch connectivity.',
                'short_fa' => 'طراحی شبکه، Cisco/MikroTik، VLAN، STP، EtherChannel، Routing، Wireless و ارتباط شعب.',
                'features' => [
                    ['en' => 'Network design', 'fa' => 'طراحی شبکه'],
                    ['en' => 'Cisco / MikroTik', 'fa' => 'Cisco/MikroTik'],
                    ['en' => 'VLAN', 'fa' => 'VLAN'],
                    ['en' => 'STP', 'fa' => 'STP'],
                    ['en' => 'EtherChannel', 'fa' => 'EtherChannel'],
                    ['en' => 'Routing', 'fa' => 'Routing'],
                    ['en' => 'Wireless', 'fa' => 'Wireless'],
                    ['en' => 'Branch connectivity', 'fa' => 'ارتباط شعب'],
                ],
            ],
            [
                'slug' => 'mikrotik-routing-multi-wan',
                'icon' => 'bi bi-signpost-split',
                'sort' => 2,
                'title_en' => 'MikroTik Routing & Multi-WAN',
                'title_fa' => 'روتینگ MikroTik و Multi-WAN',
                'card_title_en' => 'MikroTik Multi-WAN / Routing',
                'card_title_fa' => 'روتینگ و Multi-WAN MikroTik',
                'short_en' => 'Load balancing, failover, ECMP, policy routing, VRF, NAT, and multiple ISPs.',
                'short_fa' => 'Load Balancing، Failover، ECMP، Policy Routing، VRF، NAT و چند ISP.',
                'features' => [
                    ['en' => 'Load balancing', 'fa' => 'Load Balancing'],
                    ['en' => 'Failover', 'fa' => 'Failover'],
                    ['en' => 'ECMP', 'fa' => 'ECMP'],
                    ['en' => 'Policy routing', 'fa' => 'Policy Routing'],
                    ['en' => 'VRF', 'fa' => 'VRF'],
                    ['en' => 'NAT', 'fa' => 'NAT'],
                    ['en' => 'Multiple ISPs', 'fa' => 'چند ISP'],
                ],
            ],
            [
                'slug' => 'system-administration',
                'icon' => 'bi bi-hdd-stack',
                'sort' => 3,
                'title_en' => 'Server Infrastructure & Administration',
                'title_fa' => 'زیرساخت و مدیریت سرور',
                'card_title_en' => 'Server Infrastructure',
                'card_title_fa' => 'زیرساخت سرور',
                'short_en' => 'Windows Server, Ubuntu/Linux, DNS, DHCP, Nginx, hardening, and troubleshooting.',
                'short_fa' => 'Windows Server، Ubuntu/Linux، DNS، DHCP، Nginx، Hardening و Troubleshooting.',
                'features' => [
                    ['en' => 'Windows Server', 'fa' => 'Windows Server'],
                    ['en' => 'Ubuntu / Linux', 'fa' => 'Ubuntu/Linux'],
                    ['en' => 'DNS', 'fa' => 'DNS'],
                    ['en' => 'DHCP', 'fa' => 'DHCP'],
                    ['en' => 'Nginx', 'fa' => 'Nginx'],
                    ['en' => 'Hardening', 'fa' => 'Hardening'],
                    ['en' => 'Troubleshooting', 'fa' => 'Troubleshooting'],
                ],
            ],
            [
                'slug' => 'virtualization-solutions',
                'icon' => 'bi bi-boxes',
                'sort' => 4,
                'title_en' => 'VMware & Virtualization',
                'title_fa' => 'VMware و مجازی‌سازی',
                'card_title_en' => 'VMware Virtualization',
                'card_title_fa' => 'مجازی‌سازی VMware',
                'short_en' => 'ESXi, VM migration, storage, resource optimization, snapshots, and virtual infrastructure design.',
                'short_fa' => 'ESXi، VM Migration، Storage، Resource Optimization، Snapshot و طراحی زیرساخت مجازی.',
                'features' => [
                    ['en' => 'ESXi', 'fa' => 'ESXi'],
                    ['en' => 'VM migration', 'fa' => 'VM Migration'],
                    ['en' => 'Storage', 'fa' => 'Storage'],
                    ['en' => 'Resource optimization', 'fa' => 'Resource Optimization'],
                    ['en' => 'Snapshots', 'fa' => 'Snapshot'],
                    ['en' => 'Virtual infrastructure design', 'fa' => 'طراحی زیرساخت مجازی'],
                ],
            ],
            [
                'slug' => 'hp-enterprise-server',
                'icon' => 'bi bi-hdd',
                'sort' => 5,
                'title_en' => 'HP Enterprise Server Infrastructure',
                'title_fa' => 'زیرساخت سرورهای سازمانی HP',
                'card_title_en' => 'HPE Server Infrastructure',
                'card_title_fa' => 'زیرساخت سرور HPE',
                'short_en' => 'Deploy and maintain HPE ProLiant servers, RAID, storage, and performance.',
                'short_fa' => 'راه‌اندازی و نگهداری HPE ProLiant، RAID، Storage و Performance.',
                'features' => [
                    ['en' => 'HPE ProLiant deployment', 'fa' => 'راه‌اندازی HPE ProLiant'],
                    ['en' => 'HPE ProLiant maintenance', 'fa' => 'نگهداری HPE ProLiant'],
                    ['en' => 'RAID', 'fa' => 'RAID'],
                    ['en' => 'Storage', 'fa' => 'Storage'],
                    ['en' => 'Performance', 'fa' => 'Performance'],
                ],
            ],
            [
                'slug' => 'sql-server-high-availability',
                'icon' => 'bi bi-database',
                'sort' => 6,
                'title_en' => 'SQL Server Infrastructure & High Availability',
                'title_fa' => 'زیرساخت SQL Server و دسترس‌پذیری بالا',
                'card_title_en' => 'SQL Server / AlwaysOn',
                'card_title_fa' => 'SQL Server / AlwaysOn',
                'short_en' => 'SQL Server, Always On Availability Groups, backup/restore, performance, maintenance, and disaster recovery.',
                'short_fa' => 'SQL Server، AlwaysOn AG، Backup/Restore، Performance، Maintenance و Disaster Recovery.',
                'features' => [
                    ['en' => 'SQL Server', 'fa' => 'SQL Server'],
                    ['en' => 'Always On Availability Groups', 'fa' => 'AlwaysOn AG'],
                    ['en' => 'Backup / Restore', 'fa' => 'Backup/Restore'],
                    ['en' => 'Performance', 'fa' => 'Performance'],
                    ['en' => 'Maintenance', 'fa' => 'Maintenance'],
                    ['en' => 'Disaster recovery', 'fa' => 'Disaster Recovery'],
                ],
            ],
            [
                'slug' => 'jira-implementation',
                'icon' => 'bi bi-kanban',
                'sort' => 7,
                'title_en' => 'Jira & Confluence Enterprise Solutions',
                'title_fa' => 'راهکارهای سازمانی Jira و Confluence',
                'card_title_en' => 'Jira & Confluence Enterprise',
                'card_title_fa' => 'Jira و Confluence سازمانی',
                'short_en' => 'Enterprise Jira installation, migration, workflow, permissions, upgrade, backup, and troubleshooting.',
                'short_fa' => 'نصب Jira سازمانی، Migration، Workflow، Permission، Upgrade، Backup و Troubleshooting.',
                'features' => [
                    ['en' => 'Enterprise Jira installation', 'fa' => 'نصب Jira سازمانی'],
                    ['en' => 'Migration', 'fa' => 'Migration'],
                    ['en' => 'Workflow', 'fa' => 'Workflow'],
                    ['en' => 'Permissions', 'fa' => 'Permission'],
                    ['en' => 'Upgrade', 'fa' => 'Upgrade'],
                    ['en' => 'Backup', 'fa' => 'Backup'],
                    ['en' => 'Troubleshooting', 'fa' => 'Troubleshooting'],
                ],
            ],
            [
                'slug' => 'monitoring-security',
                'icon' => 'bi bi-graph-up-arrow',
                'sort' => 8,
                'title_en' => 'Monitoring & Observability',
                'title_fa' => 'مانیتورینگ و مشاهده‌پذیری',
                'card_title_en' => 'Monitoring / Zabbix / Grafana',
                'card_title_fa' => 'مانیتورینگ / Zabbix / Grafana',
                'short_en' => 'Zabbix, Grafana, SNMP, JMX, and monitoring for servers, network, SQL, Docker, Redis, and MongoDB.',
                'short_fa' => 'Zabbix، Grafana، SNMP، JMX و مانیتورینگ Server، Network، SQL، Docker، Redis و MongoDB.',
                'features' => [
                    ['en' => 'Zabbix', 'fa' => 'Zabbix'],
                    ['en' => 'Grafana', 'fa' => 'Grafana'],
                    ['en' => 'SNMP', 'fa' => 'SNMP'],
                    ['en' => 'JMX', 'fa' => 'JMX'],
                    ['en' => 'Server monitoring', 'fa' => 'مانیتورینگ Server'],
                    ['en' => 'Network monitoring', 'fa' => 'مانیتورینگ Network'],
                    ['en' => 'SQL monitoring', 'fa' => 'مانیتورینگ SQL'],
                    ['en' => 'Docker monitoring', 'fa' => 'مانیتورینگ Docker'],
                    ['en' => 'Redis monitoring', 'fa' => 'مانیتورینگ Redis'],
                    ['en' => 'MongoDB monitoring', 'fa' => 'مانیتورینگ MongoDB'],
                ],
            ],
            [
                'slug' => 'devops-automation',
                'icon' => 'bi bi-gear-wide-connected',
                'sort' => 9,
                'title_en' => 'DevOps & Deployment Automation',
                'title_fa' => 'دواپس و اتوماسیون استقرار',
                'card_title_en' => 'DevOps / Docker / Jenkins / GitLab / Ansible',
                'card_title_fa' => 'DevOps / Docker / Jenkins / GitLab / Ansible',
                'short_en' => 'Docker, Jenkins, GitLab CI/CD, Ansible, Nginx, and automated deployment.',
                'short_fa' => 'Docker، Jenkins، GitLab CI/CD، Ansible، Nginx و Automated Deployment.',
                'features' => [
                    ['en' => 'Docker', 'fa' => 'Docker'],
                    ['en' => 'Jenkins', 'fa' => 'Jenkins'],
                    ['en' => 'GitLab CI/CD', 'fa' => 'GitLab CI/CD'],
                    ['en' => 'Ansible', 'fa' => 'Ansible'],
                    ['en' => 'Nginx', 'fa' => 'Nginx'],
                    ['en' => 'Automated deployment', 'fa' => 'Automated Deployment'],
                ],
            ],
            [
                'slug' => 'voip-infrastructure',
                'icon' => 'bi bi-headset',
                'sort' => 10,
                'title_en' => 'VoIP Infrastructure',
                'title_fa' => 'زیرساخت VoIP',
                'card_title_en' => 'VoIP',
                'card_title_fa' => 'VoIP',
                'short_en' => 'SIP, IP phones, call routing, branch connectivity, and VoIP troubleshooting.',
                'short_fa' => 'SIP، IP Phone، Call Routing، ارتباط شعب و عیب‌یابی VoIP.',
                'features' => [
                    ['en' => 'SIP', 'fa' => 'SIP'],
                    ['en' => 'IP Phone', 'fa' => 'IP Phone'],
                    ['en' => 'Call routing', 'fa' => 'Call Routing'],
                    ['en' => 'Branch connectivity', 'fa' => 'ارتباط شعب'],
                    ['en' => 'VoIP troubleshooting', 'fa' => 'عیب‌یابی VoIP'],
                ],
            ],
            [
                'slug' => 'cctv-surveillance',
                'icon' => 'bi bi-camera-video',
                'sort' => 11,
                'title_en' => 'CCTV & Surveillance Infrastructure',
                'title_fa' => 'زیرساخت دوربین مداربسته و نظارت',
                'card_title_en' => 'CCTV / Hikvision / ONVIF',
                'card_title_fa' => 'CCTV / Hikvision / ONVIF',
                'short_en' => 'Hikvision, NVR, ONVIF, VMS, PoE networking, and camera infrastructure design.',
                'short_fa' => 'Hikvision، NVR، ONVIF، VMS، PoE Network و طراحی زیرساخت دوربین.',
                'features' => [
                    ['en' => 'Hikvision', 'fa' => 'Hikvision'],
                    ['en' => 'NVR', 'fa' => 'NVR'],
                    ['en' => 'ONVIF', 'fa' => 'ONVIF'],
                    ['en' => 'VMS', 'fa' => 'VMS'],
                    ['en' => 'PoE network', 'fa' => 'PoE Network'],
                    ['en' => 'Camera infrastructure design', 'fa' => 'طراحی زیرساخت دوربین'],
                ],
            ],
            [
                'slug' => 'network-security',
                'icon' => 'bi bi-shield-lock',
                'sort' => 12,
                'title_en' => 'Network Security & Remote Access',
                'title_fa' => 'امنیت شبکه و دسترسی از راه دور',
                'card_title_en' => 'Network Security',
                'card_title_fa' => 'امنیت شبکه',
                'short_en' => 'Firewall, VPN, segmentation, ACL, secure remote access, and hardening.',
                'short_fa' => 'Firewall، VPN، Segmentation، ACL، Secure Remote Access و Hardening.',
                'features' => [
                    ['en' => 'Firewall', 'fa' => 'Firewall'],
                    ['en' => 'VPN', 'fa' => 'VPN'],
                    ['en' => 'Segmentation', 'fa' => 'Segmentation'],
                    ['en' => 'ACL', 'fa' => 'ACL'],
                    ['en' => 'Secure remote access', 'fa' => 'Secure Remote Access'],
                    ['en' => 'Hardening', 'fa' => 'Hardening'],
                ],
            ],
            [
                'slug' => 'technical-consulting',
                'icon' => 'bi bi-briefcase',
                'sort' => 90,
                'show_in_catalog' => false,
                'preserve_existing_copy' => true,
            ],
        ];
    }

    /**
     * @return array{updated:int,created:int,preserved:int}
     */
    public function sync(): array
    {
        $updated = 0;
        $created = 0;
        $preserved = 0;

        foreach ($this->definitions() as $definition) {
            $service = Service::query()
                ->where('language', 'en')
                ->where('slug', $definition['slug'])
                ->first();

            if ($service && ($definition['preserve_existing_copy'] ?? false)) {
                $presentation = $service->presentation ?? [];
                $presentation['show_in_catalog'] = false;
                $presentation['catalog_migrated'] = 'homepage-2026-09';
                $service->show_in_catalog = false;
                $service->sort_order = (int) $definition['sort'];
                $service->presentation = $presentation;
                $service->save();
                $preserved++;

                continue;
            }

            $payload = $this->payload($definition, $service);
            if ($service) {
                $service->fill($payload);
                $service->save();
                $updated++;
            } else {
                Service::create($payload);
                $created++;
            }
        }

        return compact('updated', 'created', 'preserved');
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function payload(array $definition, ?Service $existing): array
    {
        $presentation = $existing?->presentation ?? [];
        if ($existing && is_array($existing->features) && $existing->features !== [] && ! isset($presentation['legacy_features'])) {
            $presentation['legacy_features'] = $existing->features;
        }

        $detailEn = $definition['short_en'];
        $detailFa = $definition['short_fa'];

        $presentation['icon'] = $definition['icon'];
        $presentation['title_fa'] = $definition['title_fa'];
        $presentation['card_title_en'] = $definition['card_title_en'] ?? $definition['title_en'];
        $presentation['card_title_fa'] = $definition['card_title_fa'] ?? $definition['title_fa'];
        $presentation['short_description_fa'] = $detailFa;
        $presentation['description_fa'] = $detailFa;
        $presentation['show_in_catalog'] = (bool) ($definition['show_in_catalog'] ?? true);
        $presentation['form_subject'] = $definition['title_en'].' inquiry';
        $presentation['cta_en'] = $presentation['cta_en'] ?? 'Ready to implement this in your environment? Tell me what you need.';
        $presentation['cta_fa'] = $presentation['cta_fa'] ?? 'برای پیاده‌سازی این خدمت در محیط شما، نیازتان را بگویید.';

        $isNew = $existing === null;

        return [
            'translation_key' => $existing?->translation_key ?: (string) Str::uuid(),
            'title' => $definition['title_en'],
            'slug' => $definition['slug'],
            'language' => 'en',
            'short_description' => $detailEn,
            'description' => $detailEn,
            'content' => $detailEn."\n\n".$detailFa,
            'features' => $definition['features'],
            'process' => $existing?->process ?: [],
            'faq' => $existing?->faq ?: [],
            'price' => $isNew ? null : $existing->price,
            'price_currency' => $existing?->price_currency ?: 'AED',
            'price_label' => $isNew ? 'Custom quote' : ($existing->price_label ?: 'Fixed Price'),
            'price_type' => $isNew ? 'custom_quote' : ($existing->price_type ?: 'fixed'),
            'featured_image' => $existing?->featured_image,
            'seo_title' => $definition['title_en'].' — Meet AJ',
            'seo_description' => $detailEn,
            'og_title' => $definition['title_en'].' — Meet AJ',
            'og_description' => $detailEn,
            'sort_order' => (int) $definition['sort'],
            'status' => 'published',
            'published_at' => $existing?->published_at ?: now()->subMinute(),
            'show_in_catalog' => (bool) ($definition['show_in_catalog'] ?? true),
            'presentation' => $presentation,
        ];
    }
}
