<?php

namespace App\Services;

use App\Models\HomepageContent;

class HomepageContentCatalog
{
    /**
     * The first release is seeded from the current homepage copy. Existing
     * admin edits are preserved when the homepage is rendered again.
     *
     * @return list<array{key:string,label:string,sort:int,content:array<string,mixed>}>
     */
    public function definitions(): array
    {
        return [
            [
                'key' => 'hero', 'label' => 'Hero', 'sort' => 10,
                'content' => [
                    'badge_en' => 'Available for Work', 'badge_fa' => 'آماده برای همکاری',
                    'title_en' => 'AmirHossein Jalalian', 'title_fa' => 'امیرحسین جلالیان',
                    'role_en' => 'Infrastructure & DevOps Engineer', 'role_fa' => 'مهندس زیرساخت و DevOps',
                    'subtitle_en' => 'Infrastructure & DevOps Engineer', 'subtitle_fa' => 'مهندس زیرساخت و DevOps',
                    'image' => '/assets/img/hero-bg.jpg',
                    'primary_label_en' => 'Get In Touch', 'primary_label_fa' => 'تماس با من', 'primary_href' => '#contact',
                    'secondary_label_en' => 'Articles', 'secondary_label_fa' => 'مقالات', 'secondary_href' => '#portfolio',
                ],
            ],
            [
                'key' => 'site', 'label' => 'Site identity & social links', 'sort' => 5,
                'content' => [
                    'site_name' => 'Meet AJ',
                    'profile_image' => '/assets/img/my-profile-img.jpg',
                    'logo_image' => '/assets/img/logo.png',
                    'navigation' => [
                        ['key' => 'hero', 'href' => '#hero', 'icon' => 'bi bi-house', 'label_en' => 'Home', 'label_fa' => 'صفحه اصلی'],
                        ['key' => 'about', 'href' => '#about', 'icon' => 'bi bi-person', 'label_en' => 'About', 'label_fa' => 'درباره من'],
                        ['key' => 'resume', 'href' => '#resume', 'icon' => 'bi bi-file-earmark-text', 'label_en' => 'Resume', 'label_fa' => 'رزومه'],
                        ['key' => 'services', 'href' => '#services', 'icon' => 'bi bi-hdd-stack', 'label_en' => 'Services', 'label_fa' => 'خدمات'],
                        ['key' => 'articles', 'href' => '#portfolio', 'icon' => 'bi bi-images', 'label_en' => 'Articles', 'label_fa' => 'مقالات'],
                        ['key' => 'testimonials', 'href' => '#testimonials', 'icon' => 'bi bi-menu-button', 'label_en' => 'Testimonials', 'label_fa' => 'نظرات'],
                        ['key' => 'contact', 'href' => '#contact', 'icon' => 'bi bi-envelope', 'label_en' => 'Contact', 'label_fa' => 'تماس با من'],
                    ],
                    'socials' => [
                        ['key' => 'linkedin', 'icon' => 'bi bi-linkedin', 'label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/amirhussein-jalalian-050702188/'],
                        ['key' => 'github', 'icon' => 'bi bi-github', 'label' => 'GitHub', 'url' => 'https://github.com/jalalianamirhossein-ui'],
                        ['key' => 'whatsapp', 'icon' => 'bi bi-whatsapp', 'label' => 'WhatsApp', 'url' => 'https://wa.me/989197276219'],
                        ['key' => 'telegram', 'icon' => 'bi bi-telegram', 'label' => 'Telegram', 'url' => 'https://t.me/Aj_mercury'],
                        ['key' => 'twitter', 'icon' => 'bi bi-twitter-x', 'label' => 'X (Twitter)', 'url' => 'https://twitter.com/RealAjMercury'],
                        ['key' => 'stackoverflow', 'icon' => 'bi bi-stack-overflow', 'label' => 'Stack Overflow', 'url' => 'https://stackoverflow.com/users/24522280/amir-jalalian'],
                        ['key' => 'facebook', 'icon' => 'bi bi-facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/amir.jalalian.37'],
                        ['key' => 'instagram', 'icon' => 'bi bi-instagram', 'label' => 'Instagram', 'url' => 'https://instagram.com/aj.mercury'],
                    ],
                ],
            ],
            [
                'key' => 'about', 'label' => 'About', 'sort' => 20,
                'content' => [
                    'kicker_en' => 'Infrastructure & DevOps Engineer', 'kicker_fa' => 'مهندس زیرساخت و DevOps',
                    'title_en' => 'Get to Know Me', 'title_fa' => 'با من آشنا شوید',
                    'headline_en' => 'Designing, managing, and optimizing enterprise systems.', 'headline_fa' => 'طراحی، مدیریت و بهینه‌سازی سیستم‌های سازمانی.',
                    'name_en' => 'Amirhossein Jalalian', 'name_fa' => 'امیرحسین جلالیان',
                    'role_en' => 'Infrastructure & DevOps Engineer', 'role_fa' => 'مهندس زیرساخت و DevOps',
                    'location_en' => 'Tehran, Iran', 'location_fa' => 'تهران، ایران',
                    'image' => '/assets/img/my-profile-img-2.jpg',
                    'lead_1_en' => 'I am Amirhossein Jalalian, an Infrastructure & DevOps Engineer with extensive experience in designing, managing, and optimizing enterprise systems. My goal is to provide reliable, secure, and scalable solutions that help organizations operate more efficiently while reducing risks.',
                    'lead_1_fa' => 'من امیرحسین جلالیان هستم، مهندس زیرساخت و DevOps با تجربه گسترده در طراحی، مدیریت و بهینه‌سازی سیستم‌های سازمانی. هدف من ارائه راهکارهای پایدار، امن و مقیاس‌پذیر است که به سازمان‌ها کمک می‌کند کارآیی بیشتری داشته باشند و در عین حال ریسک‌ها را کاهش دهند.',
                    'lead_2_en' => 'I have extensive experience in Windows Server, Cisco and MikroTik technologies, virtualization, and other IT infrastructure solutions. My approach is always results-driven, carrying out each project with accountability, precision, and strong teamwork.',
                    'lead_2_fa' => 'من تجربه‌ای گسترده در Windows Server، تجهیزات سیسکو و میکروتیک، مجازی‌سازی و سایر فناوری‌های زیرساختی دارم. رویکرد من در کار، همواره نتیجه‌محور است و هر پروژه را با مسئولیت‌پذیری بالا، دقت فنی و روحیه کار تیمی به سرانجام می‌رسانم.',
                    'facts' => [
                        ['label_en' => 'Birthday', 'label_fa' => 'تاریخ تولد', 'value_en' => '19 July 1999', 'value_fa' => '۲۸ تیر ۱۳۷۸'],
                        ['label_en' => 'Degree', 'label_fa' => 'مدرک تحصیلی', 'value_en' => 'Bachelor of IT Engineering', 'value_fa' => 'کارشناسی مهندسی فناوری اطلاعات'],
                        ['label_en' => 'Experience', 'label_fa' => 'تجربه', 'value_en' => '5+ Years', 'value_fa' => 'بیش از ۵ سال'],
                    ],
                    'values' => [
                        ['title_en' => 'Integrity', 'title_fa' => 'صداقت', 'body_en' => 'Honest and transparent in all professional interactions', 'body_fa' => 'صادق و شفاف در تمام تعاملات حرفه‌ای'],
                        ['title_en' => 'Excellence', 'title_fa' => 'تعالی', 'body_en' => 'Committed to delivering the highest quality solutions', 'body_fa' => 'متعهد به ارائه راهکارهای با بالاترین کیفیت'],
                        ['title_en' => 'Collaboration', 'title_fa' => 'همکاری', 'body_en' => 'Strong believer in teamwork and collective success', 'body_fa' => 'باور قوی به کار تیمی و موفقیت جمعی'],
                        ['title_en' => 'Innovation', 'title_fa' => 'نوآوری', 'body_en' => 'Continuously learning and adapting to new technologies', 'body_fa' => 'یادگیری مداوم و سازگاری با فناوری‌های جدید'],
                    ],
                    'domains' => [
                        ['key' => 'infrastructure', 'icon' => 'bi bi-hdd-stack', 'title_en' => 'Infrastructure', 'title_fa' => 'زیرساخت', 'items_en' => ['Linux', 'Windows Server', 'VMware', 'KVM'], 'items_fa' => ['Linux', 'Windows Server', 'VMware', 'KVM']],
                        ['key' => 'networking', 'icon' => 'bi bi-diagram-3', 'title_en' => 'Networking', 'title_fa' => 'شبکه', 'items_en' => ['Cisco', 'MikroTik', 'VPN', 'VoIP'], 'items_fa' => ['Cisco', 'MikroTik', 'VPN', 'VoIP']],
                        ['key' => 'devops', 'icon' => 'bi bi-gear', 'title_en' => 'DevOps', 'title_fa' => 'دواپس', 'items_en' => ['Docker', 'CI/CD', 'Jenkins', 'GitLab', 'Ansible', 'Terraform'], 'items_fa' => ['Docker', 'CI/CD', 'Jenkins', 'GitLab', 'Ansible', 'Terraform']],
                        ['key' => 'monitoring', 'icon' => 'bi bi-graph-up', 'title_en' => 'Monitoring', 'title_fa' => 'مانیتورینگ', 'items_en' => ['Zabbix', 'Grafana', 'Cacti', 'Redgate'], 'items_fa' => ['Zabbix', 'Grafana', 'Cacti', 'Redgate']],
                        ['key' => 'security', 'icon' => 'bi bi-shield-check', 'title_en' => 'Security', 'title_fa' => 'امنیت', 'items_en' => ['Firewall & security rules', 'Active Directory', 'VPN', 'Backup (Veeam)'], 'items_fa' => ['قوانین فایروال و امنیت', 'Active Directory', 'VPN', 'پشتیبان‌گیری (Veeam)']],
                    ],
                    'philosophy_title_en' => 'My Philosophy', 'philosophy_title_fa' => 'فلسفه من',
                    'philosophy_en' => 'I value honesty, patience, and accountability as core principles. I believe that respect for the profession and adherence to fundamental values form the foundation for achieving long-term and sustainable success in the field of Information Technology.',
                    'philosophy_fa' => 'برای من، صداقت، صبر و مسئولیت‌پذیری ارزش‌های اساسی هستند. باور دارم که احترام به حرفه و پایبندی به اصول بنیادین، زیربنای دستیابی به موفقیت پایدار و بلندمدت در حوزه فناوری اطلاعات است.',
                    'motto_en' => 'Success is born of sustained effort, continuous learning, and faith in the journey.', 'motto_fa' => 'موفقیت زاده تلاش مستمر، یادگیری مداوم و ایمان به مسیر است.',
                ],
            ],
            [
                'key' => 'stats', 'label' => 'Stats', 'sort' => 30,
                'content' => [
                    'items' => [
                        ['icon' => 'bi bi-people', 'value' => 49, 'label_en' => 'Satisfied customers', 'label_fa' => 'مشتریان راضی'],
                        ['icon' => 'bi bi-journal-richtext', 'value' => 31, 'label_en' => 'Successful projects', 'label_fa' => 'پروژه‌های موفق'],
                        ['icon' => 'bi bi-headset', 'value' => 4160, 'label_en' => 'Support hours provided', 'label_fa' => 'ساعت پشتیبانی ارائه شده'],
                        ['icon' => 'bi bi-people', 'value' => 12, 'label_en' => 'Expert team', 'label_fa' => 'تیم متخصص'],
                    ],
                ],
            ],
            [
                'key' => 'skills', 'label' => 'Skills', 'sort' => 40,
                'content' => [
                    'title_en' => 'Skills', 'title_fa' => 'مهارت‌ها',
                    'intro_en' => 'My core technical and professional skills in computer networks and IT infrastructure.', 'intro_fa' => 'مهارت‌های اصلی فنی و حرفه‌ای من در شبکه‌های کامپیوتری و زیرساخت فناوری اطلاعات.',
                    'groups' => [
                        ['key' => 'infrastructure', 'title_en' => 'Infrastructure', 'title_fa' => 'زیرساخت', 'items' => [['name_en' => 'Linux', 'name_fa' => 'Linux', 'value' => 100], ['name_en' => 'Windows Server', 'name_fa' => 'Windows Server', 'value' => 92], ['name_en' => 'Active Directory', 'name_fa' => 'Active Directory', 'value' => 90], ['name_en' => 'DNS / DFS / WSUS', 'name_fa' => 'DNS / DFS / WSUS', 'value' => 88], ['name_en' => 'Backup (Veeam)', 'name_fa' => 'پشتیبان‌گیری (Veeam)', 'value' => 77]]],
                        ['key' => 'networking', 'title_en' => 'Networking', 'title_fa' => 'شبکه', 'items' => [['name_en' => 'Cisco', 'name_fa' => 'Cisco', 'value' => 92], ['name_en' => 'MikroTik', 'name_fa' => 'MikroTik', 'value' => 90], ['name_en' => 'Firewall', 'name_fa' => 'فایروال', 'value' => 88], ['name_en' => 'VPN', 'name_fa' => 'VPN', 'value' => 85], ['name_en' => 'VoIP', 'name_fa' => 'VoIP', 'value' => 80]]],
                        ['key' => 'virtualization', 'title_en' => 'Virtualization & Cloud', 'title_fa' => 'مجازی‌سازی و ابر', 'items' => [['name_en' => 'VMware vSphere', 'name_fa' => 'VMware vSphere', 'value' => 90], ['name_en' => 'KVM', 'name_fa' => 'KVM', 'value' => 82], ['name_en' => 'AWS', 'name_fa' => 'AWS', 'value' => 78], ['name_en' => 'Azure', 'name_fa' => 'Azure', 'value' => 76], ['name_en' => 'SQL Server HA', 'name_fa' => 'SQL Server HA', 'value' => 80]]],
                        ['key' => 'devops', 'title_en' => 'DevOps & Operations', 'title_fa' => 'DevOps و عملیات', 'items' => [['name_en' => 'Docker', 'name_fa' => 'Docker', 'value' => 88], ['name_en' => 'CI/CD', 'name_fa' => 'CI/CD', 'value' => 85], ['name_en' => 'GitLab / Jenkins', 'name_fa' => 'GitLab / Jenkins', 'value' => 82], ['name_en' => 'Ansible', 'name_fa' => 'Ansible', 'value' => 80], ['name_en' => 'Terraform', 'name_fa' => 'Terraform', 'value' => 75], ['name_en' => 'Zabbix / Grafana', 'name_fa' => 'Zabbix / Grafana', 'value' => 86]]],
                        ['key' => 'professional', 'title_en' => 'Professional', 'title_fa' => 'مهارت‌های حرفه‌ای', 'items' => [['name_en' => 'Documentation', 'name_fa' => 'مستندسازی', 'value' => 90], ['name_en' => 'Troubleshooting', 'name_fa' => 'عیب‌یابی', 'value' => 95], ['name_en' => 'Planning', 'name_fa' => 'برنامه‌ریزی', 'value' => 85], ['name_en' => 'Communication', 'name_fa' => 'ارتباط با مشتری', 'value' => 88]]],
                    ],
                ],
            ],
            [
                'key' => 'resume', 'label' => 'Resume', 'sort' => 50,
                'content' => [
                    'title_en' => 'Resume', 'title_fa' => 'رزومه',
                    'intro_en' => 'My academic background and professional work experience in computer networks and IT infrastructure.', 'intro_fa' => 'پیشینه تحصیلی و تجربه کاری حرفه‌ای من در شبکه‌های کامپیوتری و زیرساخت IT.',
                    'education' => [
                        ['title_en' => 'DevOps Engineering Program', 'title_fa' => 'برنامه مهندسی DevOps', 'period' => '2024', 'org_en' => 'Sanat Training Center', 'org_fa' => 'مرکز آموزش سانانت', 'url' => 'https://sananetco.com/en/home/'],
                        ['title_en' => 'LPIC-2', 'title_fa' => 'LPIC-2', 'period' => '2023', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                        ['title_en' => 'LPIC-1', 'title_fa' => 'LPIC-1', 'period' => '2023', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                        ['title_en' => 'Comprehensive VMware vSphere', 'title_fa' => 'VMware vSphere جامع', 'period' => '2021', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                        ['title_en' => "Bachelor's in Computer Networks & Internet", 'title_fa' => 'کارشناسی شبکه‌های کامپیوتری و اینترنت', 'period' => '2020 - 2022', 'org_en' => 'University of Applied Science and Technology', 'org_fa' => 'دانشگاه علمی و کاربردی', 'url' => 'https://iii.ac.ir/'],
                        ['title_en' => 'MCSA & Network Plus', 'title_fa' => 'MCSA و Network Plus', 'period' => '2018', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                        ['title_en' => 'CCNA 200-125', 'title_fa' => 'CCNA 200-125', 'period' => '2018', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                        ['title_en' => 'Associate Degree in Software Engineering', 'title_fa' => 'کاردانی مهندسی نرم‌افزار', 'period' => '2016 - 2018', 'org_en' => 'Shahid Shamsipour Technical and Vocational University', 'org_fa' => 'دانشگاه فنی و حرفه‌ای شهید شمس‌پور', 'url' => 'https://shamsipour.nus.ac.ir/'],
                        ['title_en' => 'Network Plus', 'title_fa' => 'Network Plus', 'period' => '2013', 'org_en' => 'Arzhang Higher Education Institute', 'org_fa' => 'مؤسسه آموزش عالی ارژنگ', 'url' => 'https://arjang.ac.ir/'],
                    ],
                    'experience' => [
                        ['title_en' => 'Infrastructure & DevOps Engineer', 'title_fa' => 'مهندس زیرساخت و DevOps', 'period' => 'Sep 2018 – Present', 'org_en' => 'Newsha Drinks Co.', 'org_fa' => 'شرکت کشت و صنعت نیوشاداریان', 'url' => 'https://newshadrinks.com', 'body_en' => 'Designing, securing, and operating enterprise infrastructure, automation, monitoring, and deployment workflows.', 'body_fa' => 'طراحی، ایمن‌سازی و مدیریت زیرساخت سازمانی، اتوماسیون، مانیتورینگ و فرایندهای استقرار.'],
                        ['title_en' => 'Network Administrator', 'title_fa' => 'ادمین شبکه', 'period' => 'Jul 2017 – Aug 2018', 'org_en' => 'University of Applied Science & Technology', 'org_fa' => 'دانشگاه علمی و کاربردی', 'url' => 'https://uast48ac.ir/fa/', 'body_en' => 'Installed and maintained network equipment, connectivity, CCTV systems, and security policies.', 'body_fa' => 'نصب و نگهداری تجهیزات شبکه، ارتباطات، سامانه‌های نظارتی و سیاست‌های امنیتی.'],
                    ],
                ],
            ],
            [
                'key' => 'contact', 'label' => 'Contact', 'sort' => 60,
                'content' => [
                    'title_en' => 'Contact', 'title_fa' => 'تماس با من',
                    'intro_en' => 'Get in touch for professional IT services, network solutions, or technical consulting.', 'intro_fa' => 'برای خدمات حرفه‌ای IT، راهکارهای شبکه یا مشاوره فنی با من تماس بگیرید.',
                    'heading_en' => "Let's Work Together", 'heading_fa' => 'بیایید با هم کار کنیم',
                    'body_en' => "Ready to discuss your IT infrastructure needs? I'm here to help you achieve your goals with professional solutions.", 'body_fa' => 'آماده بحث در مورد نیازهای زیرساخت IT شما هستم؟ من اینجا هستم تا با راهکارهای حرفه‌ای به شما کمک کنم.',
                    'location_en' => 'Tehran, Iran', 'location_fa' => 'تهران، ایران',
                    'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1145.5099796149461!2d51.40510627035861!3d35.701826831163324',
                    'items' => [
                        ['icon' => 'bi bi-telephone', 'key' => 'phone', 'title_en' => 'Call Me', 'title_fa' => 'تماس تلفنی', 'body_en' => 'Direct phone consultation', 'body_fa' => 'مشاوره تلفنی مستقیم', 'value' => '+989197276219', 'href' => 'tel:+989197276219', 'cta_en' => '+98 9197276219', 'cta_fa' => '09197276219'],
                        ['icon' => 'bi bi-whatsapp', 'key' => 'whatsapp', 'title_en' => 'WhatsApp', 'title_fa' => 'واتساپ', 'body_en' => 'Quick messaging & support', 'body_fa' => 'پیام‌رسانی سریع و پشتیبانی', 'value' => 'https://wa.me/989197276219', 'href' => 'https://wa.me/989197276219', 'cta_en' => 'Start Chat', 'cta_fa' => 'شروع گفتگو'],
                        ['icon' => 'bi bi-envelope', 'key' => 'email', 'title_en' => 'Email Me', 'title_fa' => 'ایمیل', 'body_en' => 'Detailed project discussions', 'body_fa' => 'بحث‌های تفصیلی پروژه', 'value' => 'jalalian.amirhossein@gmail.com', 'href' => 'mailto:jalalian.amirhossein@gmail.com', 'cta_en' => 'jalalian.amirhossein@gmail.com', 'cta_fa' => 'jalalian.amirhossein@gmail.com'],
                        ['icon' => 'bi bi-telegram', 'key' => 'telegram', 'title_en' => 'Telegram', 'title_fa' => 'تلگرام', 'body_en' => 'Instant communication', 'body_fa' => 'ارتباط فوری', 'value' => 'https://t.me/Aj_mercury', 'href' => 'https://t.me/Aj_mercury', 'cta_en' => '@Aj_mercury', 'cta_fa' => '@Aj_mercury'],
                    ],
                ],
            ],
        ];
    }

    /** @return array{created:int,existing:int} */
    public function sync(): array
    {
        $created = 0;
        $existing = 0;
        foreach ($this->definitions() as $definition) {
            $section = HomepageContent::query()->firstOrCreate(
                ['key' => $definition['key']],
                ['label' => $definition['label'], 'content' => $definition['content'], 'sort_order' => $definition['sort']]
            );
            if ($section->wasRecentlyCreated) {
                $created++;
            } else {
                $existing++;
            }
        }

        return compact('created', 'existing');
    }

    /** @return array<string,HomepageContent> */
    public function forView(): array
    {
        $this->sync();

        return HomepageContent::query()->published()->orderBy('sort_order')->get()->keyBy('key')->all();
    }
}
