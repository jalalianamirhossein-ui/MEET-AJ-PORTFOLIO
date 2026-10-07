<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleContentStandardizer;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleContentReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_articles_have_single_ending_sections_valid_contents_and_preserved_code(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach (Article::with('category')->get() as $article) {
            $service = app(ArticleContentStandardizer::class);
            $prepared = $service->standardize($article, $article->content);
            $this->assertSame($prepared, $service->standardize($article, $prepared), $article->slug);
            preg_match_all('~<pre\b[^>]*>.*?</pre>~is', $article->content, $original);
            preg_match_all('~<pre\b[^>]*>.*?</pre>~is', $prepared, $repaired);
            $this->assertEqualsCanonicalizing($original[0], $repaired[0], $article->slug);
            foreach (['fa', 'en'] as $locale) {
                $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                foreach (['conclusion', 'faq', 'official-references'] as $id) {
                    $this->assertSame(1, $xp->query('//article[@class="article-body"]//section[@id="'.$id.'"]')->length, $article->slug.' '.$id);
                }
                foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $link) {
                    $id = substr($link->getAttribute('href'), 1);
                    $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length, $article->slug.' '.$id);
                }
                $labels = [];
                foreach ($xp->query('//article[@class="article-body"]//h2') as $heading) {
                    $label = trim(preg_replace('/\s+/u', ' ', $heading->textContent));
                    $this->assertNotContains($label, $labels, $article->slug.' '.$label);
                    $this->assertDoesNotMatchRegularExpression('/^(?:SEO|SEO Title|Meta Description|Keywords)$/i', $label);
                    $labels[] = $label;
                }
            }
        }
    }

    public function test_fortigate_keeps_its_twelve_authored_questions_and_official_citations(): void
    {
        $this->artisan('migrate');
        $article = Article::where('slug', 'fortigate-sd-wan-load-balancing-failover')->firstOrFail();
        $html = $this->get($article->path())->assertOk()->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $this->assertSame(12, $xp->query('//section[@id="faq"]//details')->length);
        $this->assertSame(0, $xp->query('//article[@class="article-body"]/h2')->length);
        $this->assertSame(1, $xp->query('//section[@id="introduction"]')->length);
        $this->assertSame(0, $xp->query('//section[@id="official-references"]//a[contains(@href,"cisa.gov")]')->length);
        $this->assertGreaterThan(10, $xp->query('//section[@id="official-references"]//a[contains(@href,"docs.fortinet.com")]')->length);
        $schema = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true);
        $this->assertCount(12, $schema['mainEntity']);
        foreach ($schema['mainEntity'] as $item) {
            $this->assertStringContainsString($item['name'], $xp->evaluate('string(//section[@id="faq"])'));
        }
    }

    public function test_migration_preserves_editorial_metadata_and_can_be_repeated(): void
    {
        $article = Article::create(['title' => 'Custom title', 'slug' => 'custom-review', 'status' => 'draft',
            'content' => '<h2 id="opening">Introduction</h2><p>Author opening.</p><h2 id="questions">FAQ</h2><h3>Custom question?</h3><p>Custom answer.</p>',
            'presentation' => ['custom' => 'keep', 'toc_html' => '<li><a href="#questions">FAQ</a></li><li><a href="#removed-seo">SEO</a></li>'],
        ]);
        $migration = require database_path('migrations/2026_10_07_000043_repair_duplicate_article_sections.php');
        $migration->up();
        $saved = $article->fresh();
        $this->assertSame('draft', $saved->status);
        $this->assertSame('Custom title', $saved->title);
        $this->assertSame('keep', $saved->presentation['custom']);
        $this->assertStringContainsString('Author opening.', $saved->content);
        $this->assertStringContainsString('Custom answer.', $saved->content);
        $this->assertStringContainsString('#questions', $saved->presentation['toc_html']);
        $this->assertStringNotContainsString('#removed-seo', $saved->presentation['toc_html']);
        $migration->up();
        $this->assertSame($saved->content, $saved->fresh()->content);
    }

    public function test_repeated_bibliography_links_are_removed_without_losing_explanations(): void
    {
        $article = new Article(['title' => 'Reference example', 'slug' => 'reference-example']);
        $source = '<section id="official-references"><h2>References</h2><ul>'
            .'<li><a href="https://example.com/docs">Official documentation</a></li>'
            .'<li><a href="https://example.com/docs">The same documentation</a></li>'
            .'<li>Use the compatibility table in <a href="https://example.com/docs">the documentation</a>.</li>'
            .'</ul></section>';
        $html = app(ArticleContentStandardizer::class)->standardize($article, $source);
        $this->assertStringContainsString('Official documentation', $html);
        $this->assertStringNotContainsString('The same documentation', $html);
        $this->assertStringContainsString('Use the compatibility table', $html);
    }
}
