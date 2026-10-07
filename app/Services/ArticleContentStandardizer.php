<?php

namespace App\Services;

use App\Models\Article;

/**
 * Recognizes authored sections, repairs duplicate structure, and fills gaps
 * while preserving the author's prose, executable blocks and citations.
 */
class ArticleContentStandardizer
{
    public function standardize(Article $article, string $content): string
    {
        $content = Article::normalizeContentMarkup($content);
        $content = $this->removeArticleBackButton($content);
        $content = $this->wrapFlatSections($content);
        $content = $this->removeSeoSections($content);
        // An empty legacy anchor is a navigation target, not an authored section.
        foreach (array_merge(array_keys($this->sections($article)), ['faq', 'official-references']) as $id) {
            if (! preg_match('~<section\b[^>]*\bid=["\']'.preg_quote($id, '~').'["\']~i', $content)) {
                $content = preg_replace('~<span\b[^>]*\bid=["\']'.preg_quote($id, '~').'["\'][^>]*>\s*</span>~is', '', $content) ?? $content;
            }
        }
        $content = $this->reuseAuthoredSections($article, $content);
        $content = $this->mergeRepeatedSections($article, $content);
        $content = $this->deduplicateReferenceLinks($content);
        $content = $this->addKnownSectionIds($content);
        $content = preg_replace('~<h2\b([^>]*)>\s*FAQ\s*</h2>~i', '<h2$1 data-en="Frequently Asked Questions" data-fa="پرسش‌های متداول">Frequently Asked Questions</h2>', $content) ?? $content;
        if (preg_match('/href=["\']#references["\']/', $content.(string) data_get($article->presentation, 'toc_html'))
            && ! preg_match('/\bid=["\']references["\']/', $content)) {
            $content = preg_replace('~(<section\b[^>]*\bid=["\']official-references["\'][^>]*>)~i', '$1<span id="references" class="article-section-anchor" aria-hidden="true"></span>', $content, 1) ?? $content;
        }
        $content = str_ireplace('Official references', 'Official References', $content);
        // Existing FAQ accordions are extended in place so the original
        // questions remain visible and the shared minimum of eight is met.
        $content = $this->extendExistingFaq($content, ! data_get($article->presentation, 'localizations'));

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
            return $this->orderEndingSections($content);
        }

        $addition = "\n".implode("\n", $missing)."\n";
        $footerPosition = stripos($content, '<footer class="article-footer"');
        if ($footerPosition === false) {
            return $this->orderEndingSections($content.$addition);
        }

        return $this->orderEndingSections(substr($content, 0, $footerPosition).$addition.substr($content, $footerPosition));
    }

    /** Reuse detailed authored sections and remove only matching automatic filler. */
    private function reuseAuthoredSections(Article $article, string $content): string
    {
        $patterns = [
            'introduction' => '/^(?:introduction|overview|مقدمه|معرفی)\b/iu',
            'architecture' => '/architecture|معماری|مفاهیم\s+(?:اصلی|کلیدی)|برنامه چگونه کار/iu',
            'prerequisites' => '/prerequisites|pre[- ]?(?:checks|hardening checks)|hardware|پیش[‌ -]?نیاز|سخت[‌ -]?افزار/iu',
            'configuration' => '/^(?:installation|configuration|(?:safe\s+)?deployment|نصب|راه[‌ -]?اندازی|تنظیم|پیاده[‌ -]?سازی)/iu',
            'best-practices' => '/best\s+practices|checklist|recommendations|اشتباهات رایج|توصیه|چک[‌ -]?لیست/iu',
            'security' => '/security|hardening|امنیت|ایمن[‌ -]?سازی/iu',
            'troubleshooting' => '/troubleshoot|عیب[‌ -]?یابی/iu',
            'conclusion' => '/^(?:conclusion|summary\b|جمع[‌ -]?بندی|نتیجه[‌ -]?گیری|چه زمانی TrueNAS)/iu',
            'faq' => '/^(?:FAQ\b|frequently\s+asked\s+questions|(?:پرسش|سؤال|سوال)[‌\s]*(?:های|ات)?\s*متداول)/iu',
            'official-references' => '/^(?:official\s+(?:references|sources)|references|sources|منابع|مراجع)/iu',
        ];
        $templates = $this->sections($article);
        $templates['faq'] = $this->faqSection($article);
        $references = $this->referencesSection($article, '');
        if ($references !== null) { $templates['official-references'] = $references; }
        $sectionPattern = '~<section\b([^>]*)>(.*?)</section>~is';

        foreach ($patterns as $id => $pattern) {
            // Attribute values keep this comparison stable after FA/EN localization.
            $signature = function (string $html): string {
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                foreach (iterator_to_array($xp->query('//*[@data-en]')) as $node) {
                    while ($node->firstChild) { $node->removeChild($node->firstChild); }
                    $node->appendChild($dom->createTextNode($node->getAttribute('data-en')));
                }
                return trim(preg_replace('/\s+/u', ' ', $dom->textContent) ?? '');
            };
            $generatedSignature = isset($templates[$id]) ? $signature($templates[$id]) : null;
            preg_match_all($sectionPattern, $content, $sections, PREG_SET_ORDER);
            $authored = null;
            $generated = null;
            foreach ($sections as $section) {
                preg_match('/\bid=["\']([^"\']+)["\']/', $section[1], $sectionId);
                $currentId = $sectionId[1] ?? '';
                if ($currentId !== $id && array_key_exists($currentId, $patterns)) { continue; }
                if ($currentId === $id && $generatedSignature !== null && $signature($section[0]) === $generatedSignature) {
                    $generated = $section[0];
                    continue;
                }
                if (! preg_match('~<h2\b([^>]*)>(.*?)</h2>~is', $section[2], $heading)) { continue; }
                preg_match('/\bdata-en="([^"]*)"/i', $heading[1], $english);
                $label = html_entity_decode($english[1] ?? strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $label = preg_replace('/^\s*[0-9۰-۹]+[.،):\s-]+/u', '', $label) ?? $label;
                if ($currentId === $id || preg_match($pattern, $label)) {
                    $authored ??= [$section, $currentId];
                }
            }
            if ($authored === null) { continue; }
            if ($generated !== null) { $content = str_replace($generated, '', $content); }
            [$section, $oldId] = $authored;
            if ($oldId === $id || preg_match('/\bid=["\']'.preg_quote($id, '/').'["\']/', $content)) { continue; }
            $attributes = $oldId !== ''
                ? preg_replace('/\bid=["\'][^"\']*["\']/', 'id="'.$id.'"', $section[1], 1)
                : ' id="'.$id.'"'.$section[1];
            // Preserve old TOC and external fragment links when assigning a canonical ID.
            $anchor = $oldId !== '' ? '<span id="'.e($oldId).'" class="article-section-anchor" aria-hidden="true"></span>' : '';
            $content = str_replace($section[0], '<section'.$attributes.'>'.$anchor.$section[2].'</section>', $content);
        }

        return $content;
    }

    /** Older exports use sibling h2 blocks instead of section containers. */
    private function wrapFlatSections(string $content): string
    {
        $blocks = [];
        $content = preg_replace_callback('~<pre\b[^>]*>.*?</pre>~is', function (array $match) use (&$blocks): string {
            $key = '__ARTICLE_SECTION_CODE_'.count($blocks).'__';
            $blocks[$key] = $match[0];
            return $key;
        }, $content) ?? $content;
        preg_match_all('~</?section\b[^>]*>|</?footer\b[^>]*>|<h2\b[^>]*>.*?</h2>~is', $content, $matches, PREG_OFFSET_CAPTURE);
        $sectionDepth = 0;
        $footerDepth = 0;
        $start = null;
        $ranges = [];
        foreach ($matches[0] as [$tag, $offset]) {
            if ($start !== null && preg_match('~^<(?:h2|section|footer)\b~i', $tag)) {
                $ranges[] = [$start, $offset - $start];
                $start = null;
            }
            if (preg_match('~^</section\b~i', $tag)) { $sectionDepth = max(0, $sectionDepth - 1); }
            elseif (preg_match('~^<section\b~i', $tag)) { $sectionDepth++; }
            elseif (preg_match('~^</footer\b~i', $tag)) { $footerDepth = max(0, $footerDepth - 1); }
            elseif (preg_match('~^<footer\b~i', $tag)) { $footerDepth++; }
            elseif ($sectionDepth === 0 && $footerDepth === 0) { $start = $offset; }
        }
        if ($start !== null) { $ranges[] = [$start, strlen($content) - $start]; }
        foreach (array_reverse($ranges) as [$offset, $length]) {
            $body = substr($content, $offset, $length);
            $id = '';
            if (preg_match('~^<h2\b[^>]*\bid=["\']([^"\']+)["\']~i', $body, $heading)) {
                $id = ' id="'.e($heading[1]).'"';
                $body = preg_replace('~^(<h2\b[^>]*?)\s+id=["\'][^"\']+["\']~i', '$1', $body, 1) ?? $body;
            }
            $content = substr_replace($content, '<section'.$id.' class="article-section">'.$body.'</section>', $offset, $length);
        }
        return strtr($content, $blocks);
    }

    /** Fold older parallel runbook sections into their matching current sections. */
    private function mergeRepeatedSections(Article $article, string $content): string
    {
        $aliases = [
            'enterprise-intro' => ['introduction', 'Introduction'],
            'enterprise-architecture' => ['architecture', 'Architecture / Design'],
            'enterprise-prerequisites' => ['prerequisites', 'Prerequisites'],
            'enterprise-installation' => ['configuration', 'Installation / Configuration'],
            'enterprise-security' => ['security', 'Security Hardening'],
            'enterprise-troubleshooting' => ['troubleshooting', 'Troubleshooting'],
            'enterprise-practices' => ['best-practices', 'Best Practices'],
            'enterprise-sources' => ['official-references', 'Official Sources'],
        ];
        $templates = $this->sections($article);
        foreach ($aliases as $alias => [$id, $label]) {
            $pattern = '~<section\b[^>]*\bid=["\']%s["\'][^>]*>(.*?)</section>~is';
            if (! preg_match(sprintf($pattern, $alias), $content, $old)
                || ! preg_match(sprintf($pattern, $id), $content, $current)) { continue; }
            if (isset($templates[$id]) && $current[0] === $templates[$id]) { continue; }
            if (! preg_match('~<h2\b([^>]*)>(.*?)</h2>~is', $old[1], $heading)) { continue; }
            preg_match('/\bdata-en="([^"]*)"/i', $heading[1], $english);
            $oldLabel = trim(html_entity_decode($english[1] ?? strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($oldLabel !== $label) { continue; }
            $body = preg_replace('~<h2\b[^>]*>.*?</h2>~is', '', $old[1], 1) ?? $old[1];
            $anchor = '<span id="'.$alias.'" class="article-section-anchor" aria-hidden="true"></span>';
            // Preserve all unique authored prose, examples, citations and code.
            $merged = substr($current[0], 0, -strlen('</section>')).$anchor.$body.'</section>';
            $content = str_replace($old[0], '', $content);
            $content = str_replace($current[0], $merged, $content);
        }
        return $content;
    }

    /** Remove repeated bibliography entries while retaining explanatory citations. */
    private function deduplicateReferenceLinks(string $content): string
    {
        return preg_replace_callback('~<section\b[^>]*\bid=["\']official-references["\'][^>]*>.*?</section>~is', function (array $section): string {
            $seen = [];
            return preg_replace_callback('~<li\b[^>]*>.*?</li>~is', function (array $item) use (&$seen): string {
                if (! preg_match('~^<li\b[^>]*>\s*<a\b[^>]*\bhref=["\']([^"\']+)["\'][^>]*>[^<]*</a\s*>\s*</li>~is', $item[0], $link)) {
                    return $item[0];
                }
                $url = html_entity_decode($link[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (isset($seen[$url])) { return ''; }
                $seen[$url] = true;
                return $item[0];
            }, $section[0]) ?? $section[0];
        }, $content) ?? $content;
    }

    /**
     * Keep the article footer sections predictable for readers and crawlers.
     * Existing authored sections used to remain wherever they were found,
     * while generated sections were appended, producing mixed orderings.
     */
    private function orderEndingSections(string $content): string
    {
        $orderedIds = ['conclusion', 'faq', 'official-references'];
        $sections = [];
        foreach ($orderedIds as $id) {
            $pattern = "~<section\\b(?=[^>]*\\bid=[\"']".preg_quote($id, '~')."[\"'])[^>]*>.*?</section>~is";
            if (preg_match($pattern, $content, $match)) {
                $sections[$id] = $match[0];
                $content = str_replace($match[0], '', $content, $count);
            }
        }

        if ($sections === []) {
            return $content;
        }

        $addition = "\n".implode("\n", array_map(fn ($id) => $sections[$id], array_keys($sections)))."\n";
        $footerPosition = stripos($content, '<footer class="article-footer"');
        if ($footerPosition === false) {
            return rtrim($content).$addition;
        }

        return rtrim(substr($content, 0, $footerPosition)).$addition.substr($content, $footerPosition);
    }

    private function removeArticleBackButton(string $content): string
    {
        return preg_replace(
            '~<nav\b[^>]*class=["\'][^"\']*article-footer-nav[^"\']*["\'][^>]*>.*?</nav>~is',
            '',
            $content
        ) ?? $content;
    }

    /** Remove editorial SEO fields that were accidentally imported as article sections. */
    private function removeSeoSections(string $content): string
    {
        $content = preg_replace_callback('~<section\b[^>]*>.*?</section>~is', function (array $match): string {
            if (preg_match('~<h[12]\b([^>]*)>(.*?)</h[12]>~is', $match[0], $heading)) {
                $label = html_entity_decode(strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (preg_match('/\b(?:SEO\s+Title|Meta\s+Description|Keywords)\b|عنوان\s+SEO|توضیحات\s+متا|کلمات\s+کلیدی/iu', $label)) {
                    return '';
                }
            }

            return $match[0];
        }, $content) ?? $content;

        // Some imported articles stored the SEO block as a bare h2 followed
        // by a list, rather than wrapping it in a section.
        return preg_replace(
            '~<h[12]\b[^>]*>\s*(?:SEO|SEO\s+Title|عنوان\s+SEO)\s*</h[12]>.*?(?=<h[12]\b|$)~isu',
            '',
            $content
        ) ?? $content;
    }

    /** Reuse authored sections instead of adding a second generic introduction or references section. */
    private function addKnownSectionIds(string $content): string
    {
        $known = [
            'introduction' => '/(?:introduction|overview|مقدمه|معرفی)/iu',
            'conclusion' => '/(?:conclusion|summary|جمع[‌ ]?بندی|نتیجه)/iu',
            'official-references' => '/(?:official\s+(?:references|sources)|references?|sources?|منابع|مراجع)/iu',
        ];

        foreach ($known as $id => $pattern) {
            if (preg_match('/\bid=["\']'.preg_quote($id, '/').'["\']/i', $content)) {
                continue;
            }

            $content = preg_replace_callback('~<section\b([^>]*)>(.*?)</section>~is', function (array $match) use ($id, $pattern): string {
                if (preg_match('/\bid=["\'][^"\']+["\']/i', $match[1])) {
                    return $match[0];
                }
                if (! preg_match('~<h[12]\b([^>]*)>(.*?)</h[12]>~is', $match[2], $heading)) {
                    return $match[0];
                }
                $label = html_entity_decode(strip_tags($heading[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! preg_match($pattern, $label)) {
                    return $match[0];
                }

                return '<section id="'.$id.'"'.$match[1].'>'.$match[2].'</section>';
            }, $content, 1) ?? $content;
        }

        return $content;
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

    private function extendExistingFaq(string $content, bool $pad = true): string
    {
        if (! preg_match('/<section\b[^>]*id=["\']faq["\'][^>]*>(.*?)<\/section>/is', $content, $match, PREG_OFFSET_CAPTURE)) {
            return $content;
        }
        $section = $match[0][0];
        $fallback = $this->faqFallbacks();
        $translations = [];
        foreach ($fallback as [$question, $answer, $questionFa, $answerFa]) {
            $translations[$question] = $questionFa;
            $translations[$answer] = $answerFa;
        }
        // Repair known generated English fallbacks even when eight FAQ items
        // are already stored. Preserve custom Persian copy and English labels.
        $section = preg_replace_callback('/<[a-z][a-z0-9]*\b[^>]*\bdata-en="([^"]*)"[^>]*>/i', function (array $tag) use ($translations): string {
            $english = html_entity_decode($tag[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (! isset($translations[$english])) {
                return $tag[0];
            }
            if (preg_match('/\bdata-fa="([^"]*)"/i', $tag[0], $fa)) {
                if (preg_match('/\p{Arabic}/u', html_entity_decode($fa[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'))) {
                    return $tag[0];
                }

                return preg_replace('/\bdata-fa="[^"]*"/i', 'data-fa="'.e($translations[$english]).'"', $tag[0], 1);
            }

            return substr($tag[0], 0, -1).' data-fa="'.e($translations[$english]).'">';
        }, $section) ?? $section;
        preg_match_all('~<h[34]\b|<summary\b(?![^>]*>\s*<h[34]\b)~i', $section, $questions);
        $count = max(substr_count($section, 'article-faq-item'), count($questions[0]));
        if ($count >= 8 || ! $pad) {
            return substr_replace($content, $section, $match[0][1], strlen($match[0][0]));
        }

        $extras = '';
        foreach (array_slice($fallback, 0, 8 - $count) as [$question, $answer, $questionFa, $answerFa]) {
            $extras .= '<div class="article-faq-item"><h3 class="article-faq-question" data-en="'.e($question).'" data-fa="'.e($questionFa).'">'.e($question).'<i class="bi bi-chevron-down" aria-hidden="true"></i></h3><div class="article-faq-answer"><p data-en="'.e($answer).'" data-fa="'.e($answerFa).'">'.e($answer).'</p></div></div>';
        }
        $updated = preg_replace('~</div>\s*</section>\s*$~i', $extras.'</div></section>', $section, 1) ?? $section;
        return substr_replace($content, $updated, $match[0][1], strlen($match[0][0]));
    }

    private function faqFallbacks(): array
    {
        return [
            ['Is this safe for production?', 'Use a tested backup, a rollback plan, least privilege, and a controlled validation window.', 'آیا اجرای این روش در محیط عملیاتی ایمن است؟', 'پیش از اجرا، نسخهٔ پشتیبان و روش بازگردانی را آزمایش کنید، دسترسی‌ها را محدود نگه دارید و تغییر را در بازهٔ کنترل‌شده انجام دهید.'],
            ['How do I troubleshoot a timeout?', 'Check DNS, routing, firewall rules, listening ports, TLS, and service logs from both endpoints.', 'خطای پایان مهلت اتصال را چگونه بررسی کنیم؟', 'نام‌گشایی، مسیریابی، قوانین فایروال، پورت‌های فعال، ارتباط امن و گزارش‌های سرویس را در هر دو سمت اتصال بررسی کنید.'],
            ['How should secrets be stored?', 'Use protected environment configuration or a secret manager; never commit tokens or passwords to Git.', 'رمزها و اطلاعات محرمانه را چگونه نگهداری کنیم؟', 'از سامانهٔ مدیریت اسرار یا تنظیمات محافظت‌شدهٔ محیط استفاده کنید؛ رمز عبور و توکن را در مخزن کد ذخیره نکنید.'],
            ['What should be monitored afterward?', 'Monitor availability, errors, latency, resource saturation, certificates, and configuration drift.', 'پس از اجرا چه مواردی باید پایش شوند؟', 'دسترس‌پذیری سرویس، خطاها، تأخیر، مصرف منابع، اعتبار گواهی‌ها و تغییرات ناخواستهٔ تنظیمات را پایش کنید.'],
            ['When should I roll back?', 'Roll back when availability or data integrity is at risk and the blast radius is increasing.', 'چه زمانی باید به وضعیت قبلی بازگردیم؟', 'اگر دسترس‌پذیری سرویس یا صحت داده‌ها در خطر است و دامنهٔ مشکل گسترش می‌یابد، تغییر را بازگردانید.'],
            ['How do I verify the result?', 'Check service health, logs, metrics, and a representative client-side transaction.', 'درستی نتیجه را چگونه تأیید کنیم؟', 'وضعیت سلامت سرویس، گزارش‌ها و شاخص‌ها را بررسی کنید و یک عملیات واقعی از سمت کاربر انجام دهید.'],
            ['Can the procedure be automated?', 'Yes; prefer idempotent checks, dry-run support, review gates, clear logs, and useful exit codes.', 'آیا می‌توان این فرایند را خودکار کرد؟', 'بله؛ بررسی‌ها را طوری طراحی کنید که تکرارشان اثر اضافی نداشته باشد. اجرای آزمایشی، بازبینی، گزارش روشن و کد خروج قابل استفاده برای پایش را در نظر بگیرید.'],
            ['What is the enterprise change-control requirement?', 'Record owner, impact, maintenance window, validation evidence, and rollback steps in the change record.', 'برای مدیریت تغییر در سازمان چه اطلاعاتی لازم است؟', 'مسئول تغییر، اثر آن، بازهٔ نگهداری، شواهد تأیید نتیجه و مراحل بازگردانی را در سابقهٔ تغییر ثبت کنید.'],
        ];
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
