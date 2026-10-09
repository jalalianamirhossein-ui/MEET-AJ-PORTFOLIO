<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleContentStandardizer;
use App\Services\ArticleLocalization;
use App\Services\ArticlePresentation;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleLayoutAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_articles_have_styled_tables_unique_anchors_and_nonempty_cards(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach (Article::with(['category', 'tags'])->get() as $article) {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$article->displayContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $ids = [];
            foreach ($xp->query('//*[@id]') as $node) { $ids[] = $node->getAttribute('id'); }
            $this->assertSame($ids, array_values(array_unique($ids)), $article->slug);
            foreach ($xp->query('//table') as $table) {
                $this->assertStringContainsString('article-table', $table->getAttribute('class'), $article->slug);
                $this->assertGreaterThan(0, $xp->query('ancestor::*[contains(@class,"table-responsive") or contains(@class,"article-table-wrap")]', $table)->length, $article->slug);
            }
            $card = view('components.article-card', ['article' => $article])->render();
            $this->assertDoesNotMatchRegularExpression('/data-en=""/', $card, $article->slug);
            $this->assertStringContainsString('--topic: '.$article->accentColor(), $card);
        }
    }

    public function test_structural_repairs_remove_only_unchanged_automatic_duplicates(): void
    {
        $service = app(ArticleContentStandardizer::class);
        $article = new Article(['title' => 'Example', 'slug' => 'example']);
        $generated = $service->standardize($article, '<p>Original opening.</p>');
        $source = '<section id="enterprise-intro"><h2>Introduction</h2><p>Authored introduction.</p></section>'.$generated;
        $result = $service->standardize($article, $source);
        $this->assertSame(1, substr_count($result, 'id="introduction"'));
        $this->assertStringContainsString('id="enterprise-intro"', $result);
        $this->assertStringContainsString('Authored introduction.', $result);
        $this->assertStringNotContainsString('This guide explains', $result);
        $this->assertSame($result, $service->standardize($article, $result));
        $custom = str_replace('This guide explains', 'An editor changed this: this guide explains', $source);
        $this->assertStringContainsString('An editor changed this:', $service->standardize($article, $custom));
    }

    public function test_markdown_table_repair_preserves_executable_blocks_and_is_repeatable(): void
    {
        $code = '<pre><code>| Do not | change |\n|---|---|\n| code | bytes |</code></pre>';
        $source = '<p>| Name | Value |'."\n".'|---|---|'."\n".'| &lt;unsafe&gt; | A &amp; B |</p>'.$code;
        $service = app(ArticlePresentation::class);
        $result = $service->prepare($source);
        $this->assertStringContainsString('<th scope="col">Name</th>', $result);
        $this->assertStringContainsString('<td>&lt;unsafe&gt;</td>', $result);
        $this->assertStringContainsString($code, $result);
        $this->assertSame($result, $service->prepare($result));
    }

    public function test_generated_sections_use_the_requested_language_without_flattening_lists(): void
    {
        $service = app(ArticleContentStandardizer::class);
        foreach (['en', 'fa'] as $locale) {
            $article = new Article(['title' => 'Example & recovery', 'slug' => 'example',
                'presentation' => ['content_language' => $locale, 'localizations' => ['en' => [], 'fa' => []]],
            ]);
            $result = $service->standardize($article, '<pre><code>echo "unchanged"</code></pre>');
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$result, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame(4, $xp->query('//*[@id="best-practices"]//li')->length);
            $this->assertSame(3, $xp->query('//*[@id="security"]//li')->length);
            $this->assertSame(0, $xp->query('//div[@data-en or @data-fa]')->length);
            foreach ($xp->query('//*[@data-'.$locale.']') as $node) {
                // FAQ chevrons have no text. Leaf prose must already be localized
                // in server HTML, before any client-side language switching.
                $this->assertSame($node->getAttribute('data-'.$locale), trim($node->textContent), $locale.' '.$node->nodeName);
            }
            $this->assertStringContainsString('<pre><code>echo "unchanged"</code></pre>', $result);
            $this->assertSame($result, $service->standardize($article, $result));
        }
    }

    public function test_all_article_prose_matches_its_locale_before_javascript_runs(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach (Article::with(['category', 'tags'])->get() as $source) {
            foreach (['en', 'fa'] as $locale) {
                $article = app(ArticleLocalization::class)->apply(clone $source, $locale);
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$article->displayContent(), LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                foreach ($xp->query('//*[@data-'.$locale.'][not(ancestor::pre)]') as $node) {
                    if (! in_array($node->nodeName, ['h2', 'h3', 'h4', 'p', 'span', 'a', 'li', 'th', 'td', 'figcaption', 'summary'])) { continue; }
                    $normalize = fn ($value) => trim(preg_replace('/\s+/u', ' ', $value));
                    $expected = $normalize($node->getAttribute('data-'.$locale));
                    $actual = $normalize($node->textContent);
                    $message = $source->slug.' '.$locale.' '.$node->nodeName;
                    if ($expected === '') { $this->assertSame('', $actual, $message); }
                    else { $this->assertStringStartsWith($expected, $actual, $message); }
                }
            }
        }
    }

    public function test_legacy_warnings_appear_before_the_operations_they_guard(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach ([
            ['oxidized-network-device-configuration-backup', 'Before the foreground test, provision known hosts', 'sudo -u oxidized env HOME=/var/lib/oxidized /usr/local/bin/oxidized'],
            ['ubuntu-date-time-settings', 'Run the following commands one at a time.', 'sudo chronyd -p -f /etc/chrony/chrony.conf'],
        ] as [$slug, $warning, $command]) {
            $source = Article::where('slug', $slug)->firstOrFail();
            foreach (['en', 'fa'] as $locale) {
                $html = app(ArticleLocalization::class)->apply(clone $source, $locale)->displayContent();
                $this->assertStringContainsString($warning, $html);
                $this->assertStringContainsString($command, $html);
                $this->assertLessThan(strpos($html, $command), strpos($html, $warning), $slug.' '.$locale);
            }
        }
    }

    public function test_truenas_keeps_qnap_theme_and_complete_persian_content_after_import(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::with(['category', 'tags'])->where('slug', 'truenas-zfs-enterprise')->firstOrFail();
        $this->assertSame('qnap', $article->category->slug);
        $this->assertNotEmpty($article->englishCardTitle());
        $this->withUnencryptedCookie('lang', 'fa')->get($article->path())->assertOk()->assertSee('lang="fa" dir="rtl"', false)
            ->assertSee('پرسش‌های متداول TrueNAS')->assertSee('NAS فایل‌ها را')
            ->assertDontSee('|---|')->assertDontSee('This guide explains');
        $article->update(['content' => '<p>Custom CMS content.</p>']);
        $migration = require database_path('migrations/2026_10_07_000041_repair_truenas_card_metadata.php');
        $migration->up();
        $this->assertSame('<p>Custom CMS content.</p>', $article->fresh()->content);
    }
}
