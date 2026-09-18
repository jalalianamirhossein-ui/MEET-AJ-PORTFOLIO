<?php

namespace App\Services;

use App\Models\Service;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

class LegacyServiceImporter
{
    /** @var list<array{slug:string,icon:string,sort:int}> */
    public const CATALOG = [
        ['slug' => 'network-design', 'icon' => 'bi bi-diagram-3', 'sort' => 1],
        ['slug' => 'system-administration', 'icon' => 'bi bi-hdd-network', 'sort' => 2],
        ['slug' => 'devops-automation', 'icon' => 'bi bi-gear-wide-connected', 'sort' => 3],
        ['slug' => 'monitoring-security', 'icon' => 'bi bi-shield-check', 'sort' => 4],
        ['slug' => 'virtualization-solutions', 'icon' => 'bi bi-boxes', 'sort' => 5],
        ['slug' => 'technical-consulting', 'icon' => 'bi bi-briefcase', 'sort' => 6],
    ];

    /**
     * @return array{imported:int,skipped:int,report:list<array{slug:string,status:string}>}
     */
    public function import(bool $dryRun = false): array
    {
        $cards = $this->homepageCards();
        $imported = 0;
        $skipped = 0;
        $report = [];

        foreach (self::CATALOG as $meta) {
            $path = base_path('services/'.$meta['slug'].'.html');
            if (! is_file($path)) {
                $report[] = ['slug' => $meta['slug'], 'status' => 'missing source'];
                $skipped++;

                continue;
            }

            $existing = Service::query()->where('language', 'en')->where('slug', $meta['slug'])->first();
            if ($existing && ! $dryRun) {
                $report[] = ['slug' => $meta['slug'], 'status' => 'skipped existing'];
                $skipped++;

                continue;
            }
            if ($existing && $dryRun) {
                $report[] = ['slug' => $meta['slug'], 'status' => 'would skip existing'];
                $skipped++;

                continue;
            }

            $payload = $this->parseFile($path, $meta, $cards[$meta['slug']] ?? []);
            if ($dryRun) {
                $report[] = ['slug' => $meta['slug'], 'status' => 'would import'];
                $imported++;

                continue;
            }

            Service::create($payload);
            $imported++;
            $report[] = ['slug' => $meta['slug'], 'status' => 'imported'];
        }

        if (! $dryRun) {
            app(HomepageServiceCatalog::class)->sync();
        }

        return compact('imported', 'skipped', 'report');
    }

    /**
     * @param  array{slug:string,icon:string,sort:int}  $meta
     * @param  array<string,string>  $card
     * @return array<string,mixed>
     */
    public function parseFile(string $path, array $meta, array $card = []): array
    {
        $html = file_get_contents($path);
        if ($html === false) {
            throw new \RuntimeException('Unable to read '.$path);
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        $xp = new DOMXPath($dom);

        $h1 = $this->first($xp, '//h1[contains(@class,"service-title")]');
        $subtitle = $this->first($xp, '//p[contains(@class,"service-subtitle")]');
        $title = $this->bilingual($h1);
        $shortFromPage = $this->bilingual($subtitle);

        $schema = $this->jsonLd($html);
        $price = isset($schema['offers']['price']) ? (float) $schema['offers']['price'] : null;
        $currency = (string) ($schema['offers']['priceCurrency'] ?? 'AED');
        $amountNode = $this->first($xp, '//*[contains(@class,"price")]//*[contains(@class,"amount")]');
        $unitNode = $this->first($xp, '//*[contains(@class,"price")]//*[contains(@class,"unit")]');
        $badge = $this->first($xp, '//*[contains(@class,"badge")]');

        $lists = $xp->query('//ul[contains(@class,"list")]');
        $features = $this->listItems($lists->item(0) instanceof DOMElement ? $lists->item(0) : null);
        $exclusions = $this->listItems($lists->item(1) instanceof DOMElement ? $lists->item(1) : null);
        $deliverables = $this->listItems($lists->item(2) instanceof DOMElement ? $lists->item(2) : null);
        $sla = $this->listItems($lists->item(3) instanceof DOMElement ? $lists->item(3) : null);
        $addons = $this->listItems($lists->item(4) instanceof DOMElement ? $lists->item(4) : null);

        $process = [];
        foreach ($xp->query('//*[contains(@class,"timeline-item")]') as $item) {
            if (! $item instanceof DOMElement) {
                continue;
            }
            $phase = $this->firstWithin($xp, $item, './/*[contains(@class,"timeline-phase")]');
            $duration = $this->firstWithin($xp, $item, './/*[contains(@class,"timeline-duration")]');
            $process[] = [
                'en' => $this->bilingual($phase)['en'],
                'fa' => $this->bilingual($phase)['fa'],
                'duration_en' => $this->bilingual($duration)['en'],
                'duration_fa' => $this->bilingual($duration)['fa'],
            ];
        }

        $faq = [];
        foreach ($xp->query('//*[contains(@class,"faq-item")]') as $item) {
            if (! $item instanceof DOMElement) {
                continue;
            }
            $question = $this->firstWithin($xp, $item, './/*[contains(@class,"faq-question")]//span');
            $answer = $this->firstWithin($xp, $item, './/*[contains(@class,"faq-answer")]//p');
            $faq[] = [
                'question_en' => $this->bilingual($question)['en'],
                'question_fa' => $this->bilingual($question)['fa'],
                'answer_en' => $this->bilingual($answer)['en'],
                'answer_fa' => $this->bilingual($answer)['fa'],
            ];
        }

        $irr = $this->first($xp, '//p[contains(@class,"muted")]');
        $cta = $this->first($xp, '//*[contains(@class,"card")]//p[@data-en and contains(@data-en,"Ready")]');
        if (! $cta) {
            foreach ($xp->query('//p[@data-en]') as $p) {
                if ($p instanceof DOMElement && str_contains($p->getAttribute('data-en'), 'Ready')) {
                    $cta = $p;
                    break;
                }
            }
        }

        $subject = '';
        if (preg_match('/name="subject"\s+value="([^"]+)"/', $html, $m)) {
            $subject = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $metaDesc = $this->attr($html, 'name="description"', 'content')
            ?: (string) ($schema['description'] ?? $shortFromPage['en']);
        $seoTitle = $this->bilingual($this->first($xp, '//title'))['en'] ?: $title['en'].' — Pricing & Scope';
        $seoTitleFa = $this->bilingual($this->first($xp, '//title'))['fa'];
        $ogTitle = $this->attr($html, 'property="og:title"', 'content') ?: $seoTitle;
        $ogDesc = $this->attr($html, 'property="og:description"', 'content') ?: $metaDesc;

        $shortEn = $card['en'] ?? $shortFromPage['en'];
        $shortFa = $card['fa'] ?? $shortFromPage['fa'];

        $body = trim($shortFromPage['en']."\n\n".$metaDesc);

        return [
            'translation_key' => (string) Str::uuid(),
            'title' => $title['en'],
            'slug' => $meta['slug'],
            'language' => 'en',
            'short_description' => $shortEn,
            'description' => $shortFromPage['en'],
            'content' => $body,
            'features' => $features,
            'process' => $process,
            'faq' => $faq,
            'price' => $price,
            'price_currency' => $currency ?: 'AED',
            'price_label' => $this->bilingual($badge)['en'] ?: 'Fixed Price',
            'price_type' => 'fixed',
            'featured_image' => null,
            'seo_title' => $seoTitle,
            'seo_description' => $metaDesc,
            'og_title' => $ogTitle,
            'og_description' => $ogDesc,
            'sort_order' => $meta['sort'],
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'presentation' => [
                'icon' => $meta['icon'],
                'title_fa' => $title['fa'],
                'short_description_fa' => $shortFa,
                'description_fa' => $shortFromPage['fa'],
                'seo_title_fa' => $seoTitleFa,
                'price_label_fa' => $this->bilingual($badge)['fa'] ?: 'قیمت ثابت',
                'price_display_en' => $this->bilingual($amountNode)['en'],
                'price_display_fa' => $this->bilingual($amountNode)['fa'],
                'imported_price' => $price !== null ? (string) $price : null,
                'unit_en' => $this->bilingual($unitNode)['en'],
                'unit_fa' => $this->bilingual($unitNode)['fa'],
                'irr_note_en' => $this->bilingual($irr)['en'],
                'irr_note_fa' => $this->bilingual($irr)['fa'],
                'cta_en' => $this->bilingual($cta)['en'],
                'cta_fa' => $this->bilingual($cta)['fa'],
                'form_subject' => $subject ?: $title['en'].' Quote Request',
                'exclusions' => $exclusions,
                'deliverables' => $deliverables,
                'sla' => $sla,
                'addons' => $addons,
                'source_file' => 'services/'.$meta['slug'].'.html',
            ],
        ];
    }

    /**
     * @return array<string, array{en:string,fa:string}>
     */
    public function homepageCards(): array
    {
        $html = file_get_contents(base_path('index.html'));
        if ($html === false) {
            return [];
        }
        $cards = [];
        $map = [
            'Network Design & Implementation' => 'network-design',
            'System Administration' => 'system-administration',
            'DevOps & Automation' => 'devops-automation',
            'Monitoring & Security' => 'monitoring-security',
            'Virtualization Solutions' => 'virtualization-solutions',
            'Technical Consulting' => 'technical-consulting',
        ];
        if (preg_match_all('/<div class="col-lg-4 col-md-6 service-item d-flex".*?<\/div>\s*<\/div>/s', $html, $blocks)) {
            foreach ($blocks[0] as $block) {
                if (! preg_match('/<h4 class="title">.*?<span([^>]*)>(.*?)<\/span>/s', $block, $title)) {
                    continue;
                }
                $titleEn = $this->attrFromTag($title[1], 'data-en') ?: trim(strip_tags($title[2]));
                $slug = $map[$titleEn] ?? null;
                if (! $slug) {
                    continue;
                }
                $descEn = '';
                $descFa = '';
                if (preg_match('/<p\s+class="description"([^>]*)>/s', $block, $p)) {
                    $descEn = $this->attrFromTag($p[1], 'data-en');
                    $descFa = $this->attrFromTag($p[1], 'data-fa');
                }
                $cards[$slug] = ['en' => $descEn, 'fa' => $descFa];
            }
        }

        return $cards;
    }

    /**
     * @return array{en:string,fa:string}
     */
    private function bilingual(?DOMElement $el): array
    {
        if (! $el) {
            return ['en' => '', 'fa' => ''];
        }

        return [
            'en' => trim($el->getAttribute('data-en') ?: preg_replace('/\s+/', ' ', $el->textContent) ?? ''),
            'fa' => trim($el->getAttribute('data-fa')),
        ];
    }

    /**
     * @return list<array{en:string,fa:string}>
     */
    private function listItems(?DOMElement $ul): array
    {
        if (! $ul) {
            return [];
        }
        $items = [];
        foreach ($ul->getElementsByTagName('li') as $li) {
            $items[] = $this->bilingual($li);
        }

        return $items;
    }

    private function first(DOMXPath $xp, string $query): ?DOMElement
    {
        $node = $xp->query($query)?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function firstWithin(DOMXPath $xp, DOMElement $ctx, string $query): ?DOMElement
    {
        $node = $xp->query($query, $ctx)?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    /**
     * @return array<string,mixed>
     */
    private function jsonLd(string $html): array
    {
        if (! preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m)) {
            return [];
        }
        $decoded = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function attr(string $html, string $marker, string $name): string
    {
        $pos = strpos($html, $marker);
        if ($pos === false) {
            return '';
        }
        $chunk = substr($html, max(0, $pos - 80), 400);
        if (preg_match('/'.$name.'="([^"]*)"/', $chunk, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }

    private function attrFromTag(string $attrs, string $name): string
    {
        if (preg_match('/'.$name.'="([^"]*)"/', $attrs, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }
}
