<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleRedirect;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyArticleImporter
{
    public function __construct(private readonly ArticleHtmlSanitizer $sanitizer) {}

    /**
     * @return array{imported:int,skipped:int,redirects:int,report:array<int,array<string,mixed>>}
     */
    public function import(bool $dryRun = false): array
    {
        $cards = $this->homepageCards();
        $files = $this->articleFiles();
        if (count($files) !== 23) {
            throw new \RuntimeException('Expected 23 article HTML files, found '.count($files));
        }

        $report = [];
        $imported = 0;
        $skipped = 0;
        $redirects = 0;

        $run = function () use ($cards, $files, $dryRun, &$report, &$imported, &$skipped, &$redirects): void {
            $categories = $this->ensureCategories();
            foreach ($files as $index => $path) {
                $relative = 'articles/'.basename($path);
                $html = file_get_contents($path);
                if ($html === false) {
                    throw new \RuntimeException('Unable to read '.$relative);
                }
                $hash = hash('sha256', $html);
                $slug = basename($path, '.html');
                $card = $cards[$slug] ?? null;
                $parsed = $this->parseArticle($html, $slug, $card, $relative, $hash, $index);

                $existing = Article::query()->where('language', 'en')->where('slug', $slug)->first();
                if ($existing) {
                    $skipped++;
                    $report[] = ['slug' => $slug, 'status' => 'skipped', 'reason' => 'already exists'];
                    continue;
                }

                if ($dryRun) {
                    $imported++;
                    $redirects++;
                    $report[] = ['slug' => $slug, 'status' => 'would_import', 'title' => $parsed['title'], 'images' => $parsed['image_count'], 'code_blocks' => $parsed['code_count'], 'data_fa' => $parsed['data_fa_count']];
                    continue;
                }

                $article = new Article;
                $article->forceFill($parsed['attributes'])->save();
                ArticleRedirect::firstOrCreate(
                    ['old_path' => '/articles/'.$slug.'.html'],
                    ['article_id' => $article->id]
                );
                $imported++;
                $redirects++;
                $report[] = ['slug' => $slug, 'status' => 'imported', 'id' => $article->id, 'title' => $article->title];
            }
        };

        if ($dryRun) {
            $run();
        } else {
            DB::transaction($run);
        }

        return compact('imported', 'skipped', 'redirects', 'report');
    }

    /**
     * @return list<string>
     */
    public function articleFiles(): array
    {
        $files = glob(base_path('articles/*.html')) ?: [];
        sort($files);

        return $files;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function homepageCards(): array
    {
        $html = file_get_contents(base_path('index.html'));
        if ($html === false) {
            throw new \RuntimeException('Unable to read index.html');
        }
        $cards = [];
        if (! preg_match_all('/<div\s+class="col-lg-4 col-md-6 portfolio-item isotope-item\s+([^"]+)"\s*>/s', $html, $starts, PREG_OFFSET_CAPTURE)) {
            throw new \RuntimeException('Unable to parse homepage article cards');
        }
        foreach ($starts[0] as $order => $start) {
            $from = $start[1];
            $next = $starts[0][$order + 1][1] ?? strpos($html, '<!-- End Articles Grid -->', $from);
            $block = substr($html, $from, $next - $from);
            if (! preg_match('/href="articles\/([a-z0-9-]+)\.html"/', $block, $slugMatch)) {
                continue;
            }
            $slug = $slugMatch[1];
            preg_match('/src="(assets\/img\/portfolio\/optimized\/[^"]+)"/', $block, $thumb);
            preg_match('/href="(assets\/img\/portfolio\/[^"]+\.png)"/', $block, $gallery);
            preg_match('/alt="([^"]*)"/', $block, $alt);
            preg_match('/data-en="([^"]*)"[^>]*data-fa="([^"]*)"/', $block, $titles);
            preg_match('/<p[^>]*data-en="([^"]*)"[^>]*data-fa="([^"]*)"/s', $block, $excerpts);
            $cards[$slug] = [
                'filter_class' => trim($starts[1][$order][0]),
                'thumbnail' => isset($thumb[1]) ? '/'.$thumb[1] : null,
                'gallery' => isset($gallery[1]) ? '/'.$gallery[1] : null,
                'alt' => $alt[1] ?? null,
                'card_title_en' => $titles[1] ?? null,
                'card_title_fa' => $titles[2] ?? null,
                'card_excerpt_en' => $excerpts[1] ?? null,
                'card_excerpt_fa' => $excerpts[2] ?? null,
                'sort_order' => $order,
            ];
        }
        if (count($cards) !== 23) {
            throw new \RuntimeException('Expected 23 homepage cards, found '.count($cards));
        }

        return $cards;
    }

    /**
     * @param  array<string, mixed>|null  $card
     * @return array{attributes: array<string, mixed>, image_count: int, code_count: int, data_fa_count: int, title: string}
     */
    private function parseArticle(string $html, string $slug, ?array $card, string $relative, string $hash, int $index): array
    {
        $body = $this->sliceArticleBody($html);
        if ($body === null) {
            throw new \RuntimeException('Missing article body in '.$relative);
        }
        $body = $this->rewritePublicPaths($body);
        // Original HTML is a trusted hashed source. The sanitizer is used as a
        // report for CMS-authored HTML, not as a filter that may drop data-fa.

        $tocItems = [];
        if (preg_match_all('/<li class="article-nav-item">.*?<\/li>/s', $html, $toc)) {
            $tocItems = $toc[0];
        }
        $tocHtml = $this->rewritePublicPaths(implode("\n", $tocItems));

        preg_match('/<body class="([^"]+)"/', $html, $bodyClass);
        $theme = $bodyClass[1] ?? 'article-page theme-other';
        $categorySlug = $this->categoryFromTheme($theme, $card['filter_class'] ?? null);
        $category = Category::query()->where('language', 'en')->where('slug', $categorySlug)->first();

        $title = $this->meta($html, 'og:title') ?: $this->tagContent($html, 'title') ?: $slug;
        $description = $this->namedMeta($html, 'description') ?: $this->attr($html, 'class="article-excerpt hero-subtitle"', 'data-en');
        $heroTitle = $this->attr($html, 'class="article-title hero-title"', 'data-en') ?: $title;
        $heroTitleFa = $this->attr($html, 'class="article-title hero-title"', 'data-fa');
        $excerpt = $this->attr($html, 'class="article-excerpt hero-subtitle"', 'data-en') ?: $description;
        $excerptFa = $this->attr($html, 'class="article-excerpt hero-subtitle"', 'data-fa');
        $heroImage = $this->heroImage($html) ?: ($card['gallery'] ?? $card['thumbnail'] ?? '/assets/img/hero-bg.jpg');
        $heroImage = $this->rewritePublicPaths($heroImage);
        $categoryLabelEn = $this->attr($html, 'class="article-category"', 'data-en') ?: Str::headline($categorySlug);
        $categoryLabelFa = $this->attr($html, 'class="article-category"', 'data-fa');

        $schema = $this->jsonLd($html);
        $publishedAt = $this->publishedAt($schema, $relative);
        $seo = [
            'og_title' => $this->meta($html, 'og:title'),
            'og_description' => $this->meta($html, 'og:description'),
            'og_type' => $this->meta($html, 'og:type'),
            'og_image' => $this->meta($html, 'og:image'),
            'og_url' => $this->meta($html, 'og:url'),
            'twitter_card' => $this->namedMeta($html, 'twitter:card'),
            'twitter_title' => $this->namedMeta($html, 'twitter:title'),
            'twitter_description' => $this->namedMeta($html, 'twitter:description'),
            'twitter_image' => $this->namedMeta($html, 'twitter:image'),
            'robots' => $this->namedMeta($html, 'robots'),
            'original_canonical' => $this->canonical($html),
            'schema' => $schema,
            'date_provenance' => $publishedAt['provenance'],
        ];

        $presentation = array_filter([
            'source_file' => $relative,
            'source_hash' => $hash,
            'source_identity' => $relative,
            'body_class' => $theme,
            'toc_html' => $tocHtml,
            'hero_title_en' => $heroTitle,
            'hero_title_fa' => $heroTitleFa,
            'excerpt_translations' => ['fa' => $excerptFa],
            'title_translations' => ['fa' => $heroTitleFa],
            'original_title' => $heroTitle,
            'original_excerpt' => $excerpt,
            'category_label_en' => $categoryLabelEn,
            'category_label_fa' => $categoryLabelFa,
            'filter_class' => $card['filter_class'] ?? $this->filterFromCategory($categorySlug),
            'thumbnail' => $card['thumbnail'] ?? $heroImage,
            'gallery' => $card['gallery'] ?? $heroImage,
            'card_title_en' => $card['card_title_en'] ?? $heroTitle,
            'card_title_fa' => $card['card_title_fa'] ?? $heroTitleFa,
            'card_excerpt_en' => $card['card_excerpt_en'] ?? $excerpt,
            'card_excerpt_fa' => $card['card_excerpt_fa'] ?? $excerptFa,
            'image_alt' => $card['alt'] ?? $heroTitle,
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'title' => $heroTitle,
            'image_count' => substr_count($body, '<img'),
            'code_count' => substr_count($body, '<pre') + substr_count($body, '<code'),
            'data_fa_count' => substr_count($body, 'data-fa='),
            'attributes' => [
                'title' => $heroTitle,
                'slug' => $slug,
                'language' => 'en',
                'translation_key' => (string) Str::uuid(),
                'excerpt' => $excerpt,
                'content' => $body,
                'featured_image' => $card['thumbnail'] ?? $heroImage,
                'category_id' => $category?->id,
                'meta_title' => $title,
                'meta_description' => $description,
                'canonical_url' => null,
                'seo_data' => $seo,
                'presentation' => $presentation,
                'sort_order' => $card['sort_order'] ?? $index,
                'status' => 'published',
                'published_at' => $publishedAt['date'],
            ],
        ];
    }

    /**
     * @return array<string, Category>
     */
    private function ensureCategories(): array
    {
        $names = [
            'microsoft' => ['en' => 'Microsoft', 'fa' => 'مایکروسافت'],
            'linux' => ['en' => 'Linux', 'fa' => 'لینوکس'],
            'mikrotik' => ['en' => 'MikroTik', 'fa' => 'میکروتیک'],
            'vmware' => ['en' => 'VMware', 'fa' => 'مجازی‌سازی'],
            'others' => ['en' => 'Others', 'fa' => 'سایر'],
        ];
        $out = [];
        foreach ($names as $slug => $labels) {
            $key = (string) Str::uuid();
            $en = Category::query()->firstOrCreate(
                ['language' => 'en', 'slug' => $slug],
                ['name' => $labels['en'], 'translation_key' => $key]
            );
            Category::query()->firstOrCreate(
                ['language' => 'fa', 'slug' => $slug],
                ['name' => $labels['fa'], 'translation_key' => $en->translation_key]
            );
            $out[$slug] = $en;
        }

        return $out;
    }

    private function categoryFromTheme(string $bodyClass, ?string $filterClass): string
    {
        if ($filterClass) {
            return match (true) {
                str_contains($filterClass, 'linux') => 'linux',
                str_contains($filterClass, 'microsoft') => 'microsoft',
                str_contains($filterClass, 'mikrotik') => 'mikrotik',
                str_contains($filterClass, 'vmware') => 'vmware',
                default => 'others',
            };
        }

        return match (true) {
            str_contains($bodyClass, 'theme-linux') => 'linux',
            str_contains($bodyClass, 'theme-microsoft') => 'microsoft',
            str_contains($bodyClass, 'theme-mikrotik') => 'mikrotik',
            str_contains($bodyClass, 'theme-vmware') => 'vmware',
            default => 'others',
        };
    }

    private function filterFromCategory(string $slug): string
    {
        return $slug === 'others' ? 'filter-others' : 'filter-'.$slug;
    }

    private function rewritePublicPaths(string $html): string
    {
        $html = str_replace('../assets/', '/assets/', $html);
        $html = str_replace('href="../index.html', 'href="/', $html);
        $html = preg_replace('#href="(?:\.\./)?articles/([a-z0-9-]+)\.html#', 'href="/articles/$1', $html) ?? $html;
        $html = preg_replace('#href="(?:\.\./)?services/([a-z0-9-]+\.html)#', 'href="/services/$1', $html) ?? $html;

        return $html;
    }

    public function articleBody(string $html): string
    {
        return (string) $this->sliceArticleBody($html);
    }

    private function sliceArticleBody(string $html): ?string
    {
        $startTag = '<article class="article-body">';
        $from = strpos($html, $startTag);
        if ($from === false) {
            return null;
        }
        $from += strlen($startTag);
        $pos = $from;
        $depth = 1;
        while ($depth > 0) {
            $nextOpen = strpos($html, '<article', $pos);
            $nextClose = strpos($html, '</article>', $pos);
            if ($nextClose === false) {
                return null;
            }
            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $pos = $nextOpen + 8;
                continue;
            }
            $depth--;
            if ($depth === 0) {
                return substr($html, $from, $nextClose - $from);
            }
            $pos = $nextClose + 10;
        }

        return null;
    }

    private function slice(string $html, string $start, string $end): ?string
    {
        $from = strpos($html, $start);
        if ($from === false) {
            return null;
        }
        $from += strlen($start);
        $to = strpos($html, $end, $from);

        return $to === false ? null : substr($html, $from, $to - $from);
    }

    private function meta(string $html, string $property): ?string
    {
        if (preg_match('/<meta[^>]+property="'.preg_quote($property, '/').'"[^>]+content="([^"]*)"/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private function namedMeta(string $html, string $name): ?string
    {
        if (preg_match('/<meta[^>]+name="'.preg_quote($name, '/').'"[^>]+content="([^"]*)"/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private function canonical(string $html): ?string
    {
        if (preg_match('/<link[^>]+rel="canonical"[^>]+href="([^"]+)"/i', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    private function tagContent(string $html, string $tag): ?string
    {
        if (preg_match('/<'.$tag.'[^>]*>(.*?)<\/'.$tag.'>/is', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return null;
    }

    private function attr(string $html, string $needle, string $attr): ?string
    {
        $pos = strpos($html, $needle);
        if ($pos === false) {
            return null;
        }
        $tagStart = strrpos(substr($html, 0, $pos + strlen($needle)), '<');
        $from = $tagStart === false ? $pos : $tagStart;
        $tagEnd = strpos($html, '>', $pos);
        $chunk = $tagEnd === false
            ? substr($html, $from, 800)
            : substr($html, $from, $tagEnd - $from + 1);
        if (preg_match('/'.$attr.'="([^"]*)"/', $chunk, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private function heroImage(string $html): ?string
    {
        if (preg_match('/class="article-hero-thumbnail"[^>]*src="([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        if (preg_match('/src="([^"]+)"[^>]*class="article-hero-thumbnail"/', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function jsonLd(string $html): ?array
    {
        if (! preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/is', $html, $matches)) {
            return null;
        }
        foreach ($matches[1] as $json) {
            $data = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            if (is_array($data) && ($data['@type'] ?? null) === 'Article') {
                return $data;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $schema
     * @return array{date: \DateTimeInterface, provenance: string}
     */
    private function publishedAt(?array $schema, string $relative): array
    {
        foreach (['datePublished', 'dateCreated'] as $key) {
            if (! empty($schema[$key]) && strtotime((string) $schema[$key]) !== false) {
                return ['date' => \Carbon\Carbon::parse((string) $schema[$key]), 'provenance' => 'schema:'.$key];
            }
        }

        $mtime = filemtime(base_path($relative));
        if ($mtime === false) {
            throw new \RuntimeException('Unable to read source file time for '.$relative);
        }

        return ['date' => \Carbon\Carbon::createFromTimestamp($mtime), 'provenance' => 'source_file_mtime'];
    }
}
