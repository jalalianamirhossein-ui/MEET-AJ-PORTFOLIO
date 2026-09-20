<?php

namespace App\Services;

use App\Models\Article;

/**
 * Adds the shared long-form article sections without rewriting the author's
 * original HTML. Legacy articles remain the source of truth; this layer only
 * fills structural gaps so old and new articles render consistently.
 */
class ArticleContentStandardizer
{
    public function standardize(Article $article, string $content): string
    {
        $content = Article::normalizeContentMarkup($content);
        $content = $this->removeArticleBackButton($content);
        $content = preg_replace('/id=["\']references["\']/i', 'id="official-references"', $content) ?? $content;
        $content = str_ireplace('Official references', 'Official References', $content);

        // Existing FAQ accordions are extended in place so the original
        // questions remain visible and the shared minimum of eight is met.
        $content = $this->extendExistingFaq($content);

        $missing = [];
        foreach ($this->sections($article) as $id => $html) {
            if (! preg_match('/(?:id=["\']'.preg_quote($id, '/').'["\'])/i', $content)) {
                $missing[] = $html;
            }
        }

        if (! preg_match('/id=["\']faq["\']/i', $content)) {
            $missing[] = $this->faqSection($article);
        }
        $references = $this->referencesSection($article, $content);
        if ($references !== null) {
            $missing[] = $references;
        }

        if ($missing === []) {
            return $content;
        }

        $addition = "\n".implode("\n", $missing)."\n";
        $footerPosition = stripos($content, '<footer class="article-footer"');
        if ($footerPosition === false) {
            return $content.$addition;
        }

        return substr($content, 0, $footerPosition).$addition.substr($content, $footerPosition);
    }

    private function removeArticleBackButton(string $content): string
    {
        return preg_replace(
            '~<nav\b[^>]*class=["\'][^"\']*article-footer-nav[^"\']*["\'][^>]*>.*?</nav>~is',
            '',
            $content
        ) ?? $content;
    }

    /** @return array<string, string> */
    private function sections(Article $article): array
    {
        $title = e($article->englishTitle());
        $topic = e($article->categoryLabelEn());
        $topicFa = e($article->categoryLabelFa());

        return [
            'introduction' => $this->section('introduction', 'Introduction', 'مقدمه',
                "This guide explains {$title} in a production-aware way, including the design choices, implementation checks, and operational safeguards that matter for {$topic} environments.",
                "این راهنما {$title} را با رویکرد Production بررسی می‌کند و Design، مراحل اجرا، کنترل‌های عملیاتی و نکات ایمنی مربوط به {$topicFa} را پوشش می‌دهد."),
            'architecture' => $this->section('architecture', 'Architecture and Core Concepts', 'معماری و مفاهیم اصلی',
                'Understand the data flow, trust boundaries, dependencies, and the expected state before changing a live environment. Separate control-plane configuration from data-plane traffic and document every external dependency.',
                'پیش از تغییر در محیط زنده، Data Flow، مرزهای اعتماد، Dependencyها و وضعیت مطلوب را مشخص کنید. Configuration مربوط به Control Plane را از Traffic مربوط به Data Plane جدا و Dependencyهای خارجی را مستند کنید.'),
            'prerequisites' => $this->section('prerequisites', 'Prerequisites', 'پیش‌نیازها',
                'Use a supported release, a tested backup, administrative access, a maintenance window when required, and a rollback plan. Validate DNS, time synchronization, firewall rules, storage, and monitoring before implementation.',
                'از نسخه پشتیبانی‌شده، Backup تست‌شده، دسترسی مدیریتی، Maintenance Window در صورت نیاز و Rollback Plan استفاده کنید. DNS، همگام‌سازی زمان، Firewall، Storage و Monitoring را پیش از اجرا بررسی کنید.'),
            'configuration' => $this->section('configuration', 'Configuration and Validation', 'Configuration و اعتبارسنجی',
                'Apply the smallest configuration that satisfies the requirement. Keep environment-specific values in a secret-managed configuration, validate syntax before reload, and verify the result from both the service and client perspectives.',
                'کمترین Configuration لازم را اعمال کنید. مقادیر اختصاصی محیط را در Secret Manager نگه دارید، پیش از Reload Syntax را Validate کنید و نتیجه را هم از سمت Service و هم از سمت Client بررسی کنید.'),
            'best-practices' => $this->section('best-practices', 'Best Practices', 'Best Practiceها',
                '<ul><li>Version-control configuration and review changes.</li><li>Use least privilege and explicit allow-lists.</li><li>Automate repeatable checks and monitor the expected state.</li><li>Test upgrades and restores before production rollout.</li></ul>',
                '<ul><li>Configuration را Version Control و تغییرات را Review کنید.</li><li>از Least Privilege و Allow-list صریح استفاده کنید.</li><li>بررسی‌های تکراری را Automate و وضعیت مطلوب را Monitor کنید.</li><li>Upgrade و Restore را پیش از Production تست کنید.</li></ul>'),
            'security' => $this->section('security' , 'Security Considerations', 'ملاحظات امنیتی',
                '<div class="article-callout article-callout--warning"><p class="article-callout-title">Production safety</p><ul><li>Do not expose management interfaces or databases to the public Internet.</li><li>Store passwords, tokens, and private keys outside the article and source repository.</li><li>Patch dependencies, restrict administrative access, and retain audit logs.</li></ul></div>',
                '<div class="article-callout article-callout--warning"><p class="article-callout-title">ایمنی Production</p><ul><li>Management Interface و Database را مستقیماً روی اینترنت منتشر نکنید.</li><li>Password، Token و Private Key را در مقاله یا Repository ذخیره نکنید.</li><li>Dependencyها را Patch، دسترسی مدیریتی را محدود و Audit Log را نگهداری کنید.</li></ul></div>'),
            'troubleshooting' => $this->troubleshootingSection(),
            'conclusion' => $this->section('conclusion', 'Conclusion', 'جمع‌بندی',
                "A reliable implementation of {$title} is more than a successful first run. Keep the configuration documented, observable, recoverable, and aligned with the team's change-management process.",
                "پیاده‌سازی قابل اتکا برای {$title} فقط اجرای موفق بار اول نیست؛ Configuration را مستند، قابل مشاهده و قابل بازیابی نگه دارید و آن را با فرآیند Change Management تیم هماهنگ کنید."),
        ];
    }

    private function section(string $id, string $en, string $fa, string $enBody, string $faBody): string
    {
        return '<section id="'.$id.'" class="article-section"><h2 data-en="'.e($en).'" data-fa="'.e($fa).'">'.e($en).'</h2><div data-en="'.e(strip_tags($enBody)).'" data-fa="'.e(strip_tags($faBody)).'">'.$enBody.'</div></section>';
    }

    private function troubleshootingSection(): string
    {
        return '<section id="troubleshooting" class="article-section"><h2 data-en="Troubleshooting" data-fa="عیب‌یابی">Troubleshooting</h2><div class="table-responsive"><table class="article-table"><thead><tr><th data-en="Symptom" data-fa="نشانه">Symptom</th><th data-en="Cause / Check" data-fa="علت / بررسی">Cause / Check</th><th data-en="Resolution" data-fa="راه‌حل">Resolution</th></tr></thead><tbody><tr><td data-en="Service does not start" data-fa="سرویس شروع نمی‌شود">Service does not start</td><td data-en="Check service status, logs, ports, permissions, and configuration syntax." data-fa="وضعیت سرویس، Log، پورت، Permission و Syntax Configuration را بررسی کنید.">Check service status, logs, ports, permissions, and configuration syntax.</td><td data-en="Fix the reported dependency or syntax error, then restart and verify health checks." data-fa="خطای Dependency یا Syntax را اصلاح، Restart و Health Check را Verify کنید.">Fix the reported dependency or syntax error, then restart and verify health checks.</td></tr><tr><td data-en="Clients cannot connect" data-fa="Clientها متصل نمی‌شوند">Clients cannot connect</td><td data-en="Validate DNS, routing, firewall policy, TLS, and listening address from both endpoints." data-fa="DNS، Routing، Firewall، TLS و Listening Address را از هر دو Endpoint بررسی کنید.">Validate DNS, routing, firewall policy, TLS, and listening address from both endpoints.</td><td data-en="Allow only the required source and destination, reload safely, and retest with a controlled client." data-fa="فقط Source و Destination موردنیاز را مجاز، با ایمنی Reload و با Client کنترل‌شده Retest کنید.">Allow only the required source and destination, reload safely, and retest with a controlled client.</td></tr><tr><td data-en="Change caused an outage" data-fa="تغییر باعث قطعی شد">Change caused an outage</td><td data-en="Compare the change with the last known-good version and inspect audit and application logs." data-fa="Change را با آخرین نسخه سالم مقایسه و Audit و Application Log را بررسی کنید.">Compare the change with the last known-good version and inspect audit and application logs.</td><td data-en="Rollback the smallest possible change, restore service, then document the root cause and prevention." data-fa="کوچک‌ترین Change را Rollback، سرویس را Restore و Root Cause و Prevention را مستند کنید.">Rollback the smallest possible change, restore service, then document the root cause and prevention.</td></tr></tbody></table></div></section>';
    }

    private function faqSection(Article $article): string
    {
        $questions = [
            ['Can this procedure be used in production without a maintenance window?', 'Only when the platform documents a safe reload or the change is isolated and rollback-tested; otherwise schedule a maintenance window.', 'آیا این کار بدون Maintenance Window در Production ممکن است؟', 'فقط وقتی پلتفرم Reload امن را مستند کرده یا Change کاملاً Isolated و Rollback آن تست شده باشد؛ در غیر این صورت Maintenance Window تعیین کنید.'],
            ['What should be backed up before making the change?', 'Back up configuration, credentials held by the service, application data, and the current version of the deployment. Test the restore path.', 'پیش از تغییر از چه مواردی Backup بگیریم؟', 'از Configuration، Credentialهای Service، داده‌های Application و نسخه فعلی Deployment Backup بگیرید و مسیر Restore را تست کنید.'],
            ['How do I verify that the change really took effect?', 'Check the service health endpoint or status, inspect logs and metrics, and run a representative client-side test from the intended network zone.', 'چطور اثر واقعی تغییر را Verify کنیم؟', 'Health Endpoint یا Service Status، Log و Metric را بررسی و از Network Zone موردنظر یک تست واقعی Client-side اجرا کنید.'],
            ['What is the first check for a timeout or connection refusal?', 'Check listening ports, routing, firewall policy, DNS resolution, and whether the service is bound to the expected interface.', 'اولین بررسی برای Timeout یا Connection Refused چیست؟', 'Listening Port، Routing، Firewall، DNS و Bind شدن Service روی Interface صحیح را بررسی کنید.'],
            ['How should secrets and tokens be handled?', 'Use a secret manager or protected environment configuration, rotate them after exposure, and never commit them to Git or paste them into tickets.', 'Secret و Token را چطور نگهداری کنیم؟', 'از Secret Manager یا Environment امن استفاده، پس از افشا Rotate و هرگز آن‌ها را در Git یا Ticket ذخیره نکنید.'],
            ['How can this be automated safely?', 'Automate idempotent checks first, add dry-run support, require review for destructive operations, and emit logs and exit codes suitable for monitoring.', 'چطور این فرآیند را ایمن Automate کنیم؟', 'ابتدا Checkهای Idempotent را Automate، Dry-run اضافه، عملیات مخرب را مشروط به Review و Log و Exit Code مناسب Monitoring ایجاد کنید.'],
            ['What should be monitored after deployment?', 'Monitor availability, error rate, latency, resource saturation, certificate expiry, failed jobs, and configuration drift.', 'بعد از Deployment چه چیزهایی را Monitor کنیم؟', 'Availability، Error Rate، Latency، مصرف منابع، انقضای Certificate، Jobهای ناموفق و Configuration Drift را Monitor کنید.'],
            ['When should I roll back instead of troubleshooting in place?', 'Rollback when the service is unavailable, data integrity is at risk, or the blast radius is growing faster than you can isolate it. Preserve logs first.', 'چه زمانی به‌جای Troubleshooting، Rollback کنیم؟', 'وقتی Service در دسترس نیست، Integrity داده در خطر است یا Blast Radius سریع‌تر از توان Isolation رشد می‌کند Rollback کنید؛ ابتدا Logها را حفظ کنید.'],
        ];
        $items = '';
        foreach ($questions as [$en, $answerEn, $fa, $answerFa]) {
            $items .= '<div class="article-faq-item"><h3 class="article-faq-question" data-en="'.e($en).'" data-fa="'.e($fa).'">'.e($en).'<i class="bi bi-chevron-down" aria-hidden="true"></i></h3><div class="article-faq-answer"><p data-en="'.e($answerEn).'" data-fa="'.e($answerFa).'">'.e($answerEn).'</p></div></div>';
        }
        return '<section id="faq" class="article-section"><h2 data-en="Frequently Asked Questions" data-fa="پرسش‌های متداول">Frequently Asked Questions</h2><div class="article-faq">'.$items.'</div></section>';
    }

    private function extendExistingFaq(string $content): string
    {
        if (! preg_match('/<section\b[^>]*id=["\']faq["\'][^>]*>(.*?)<\/section>/is', $content, $match, PREG_OFFSET_CAPTURE)) {
            return $content;
        }
        $section = $match[0][0];
        $count = substr_count($section, 'article-faq-item');
        if ($count >= 8) {
            return $content;
        }

        $extras = '';
        $fallback = [
            ['Is this safe for production?', 'Use a tested backup, a rollback plan, least privilege, and a controlled validation window.'],
            ['How do I troubleshoot a timeout?', 'Check DNS, routing, firewall rules, listening ports, TLS, and service logs from both endpoints.'],
            ['How should secrets be stored?', 'Use protected environment configuration or a secret manager; never commit tokens or passwords to Git.'],
            ['What should be monitored afterward?', 'Monitor availability, errors, latency, resource saturation, certificates, and configuration drift.'],
            ['When should I roll back?', 'Roll back when availability or data integrity is at risk and the blast radius is increasing.'],
            ['How do I verify the result?', 'Check service health, logs, metrics, and a representative client-side transaction.'],
            ['Can the procedure be automated?', 'Yes; prefer idempotent checks, dry-run support, review gates, clear logs, and useful exit codes.'],
            ['What is the enterprise change-control requirement?', 'Record owner, impact, maintenance window, validation evidence, and rollback steps in the change record.'],
        ];
        foreach (array_slice($fallback, 0, 8 - $count) as [$question, $answer]) {
            $extras .= '<div class="article-faq-item"><h3 class="article-faq-question" data-en="'.e($question).'" data-fa="'.e($question).'">'.e($question).'<i class="bi bi-chevron-down" aria-hidden="true"></i></h3><div class="article-faq-answer"><p data-en="'.e($answer).'" data-fa="'.e($answer).'">'.e($answer).'</p></div></div>';
        }
        $updated = preg_replace('~</div>\s*</section>\s*$~i', $extras.'</div></section>', $section, 1) ?? $section;
        return substr_replace($content, $updated, $match[0][1], strlen($section));
    }

    private function referencesSection(Article $article, string $content): ?string
    {
        if (preg_match('/id=["\']official-references["\']/i', $content)
            || preg_match('/<h[12][^>]*>\s*Official References\s*<\/h[12]>/i', $content)) {
            return null;
        }
        $links = ['<a href="https://www.cisa.gov/topics/cyber-threats-and-advisories" target="_blank" rel="noopener">CISA Security Guidance</a>'];
        $slug = strtolower((string) $article->slug);
        if (str_contains($slug, 'netbox')) {
            $links = ['<a href="https://netboxlabs.com/docs/netbox/" target="_blank" rel="noopener">NetBox Documentation</a>', '<a href="https://github.com/netbox-community/netbox" target="_blank" rel="noopener">NetBox GitHub Repository</a>'];
        } elseif (str_contains($slug, 'mikrotik')) {
            $links = ['<a href="https://help.mikrotik.com/docs/" target="_blank" rel="noopener">MikroTik Documentation</a>', '<a href="https://github.com/RouterOS" target="_blank" rel="noopener">RouterOS GitHub</a>'];
        } elseif (str_contains($slug, 'vmware') || str_contains($slug, 'vsphere')) {
            $links = ['<a href="https://docs.vmware.com/" target="_blank" rel="noopener">VMware Documentation</a>'];
        } elseif (str_contains($slug, 'windows') || str_contains($slug, 'dfs') || str_contains($slug, 'sql-server')) {
            $links = ['<a href="https://learn.microsoft.com/" target="_blank" rel="noopener">Microsoft Learn Documentation</a>'];
        } elseif (str_contains($slug, 'oxidized')) {
            $links = ['<a href="https://github.com/ytti/oxidized" target="_blank" rel="noopener">Oxidized GitHub Repository</a>', '<a href="https://www.ruby-lang.org/en/documentation/" target="_blank" rel="noopener">Ruby Documentation</a>'];
        } elseif (str_contains($slug, 'nginx')) {
            $links = ['<a href="https://nginx.org/en/docs/" target="_blank" rel="noopener">NGINX Documentation</a>'];
        } elseif (str_contains($slug, 'ubuntu') || str_contains($slug, 'linux')) {
            $links = ['<a href="https://documentation.ubuntu.com/" target="_blank" rel="noopener">Ubuntu Documentation</a>', '<a href="https://www.kernel.org/doc/html/latest/" target="_blank" rel="noopener">Linux Kernel Documentation</a>'];
        }
        return '<section id="official-references" class="article-section"><h2 data-en="Official References" data-fa="منابع رسمی و مرجع">Official References</h2><ul>'.implode('', array_map(fn ($link) => '<li>'.$link.'</li>', $links)).'</ul></section>';
    }
}
