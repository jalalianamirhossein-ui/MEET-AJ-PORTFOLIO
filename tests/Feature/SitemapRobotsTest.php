<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapRobotsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Content migrations seed real articles; these cases exercise their own fixtures.
        Article::query()->delete();
    }

    private function article(string $slug, array $attributes = []): Article
    {
        return Article::create(array_merge([
            'title' => 'Sitemap article',
            'slug' => $slug,
            'content' => '<p>Public article content.</p>',
            'language' => 'en',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $attributes));
    }

    private function sitemap(): \DOMXPath
    {
        $xml = $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->getContent();
        $document = new \DOMDocument;
        $this->assertTrue($document->loadXML($xml, LIBXML_NONET));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        return $xpath;
    }

    public function test_sitemap_lists_canonical_public_pages_with_stored_article_dates(): void
    {
        config(['app.url' => 'https://meetaj.ir/']);
        $article = $this->article('published');
        $article->forceFill(['updated_at' => now()->subHours(2)])->save();
        $xpath = $this->sitemap();
        $locations = [];
        foreach ($xpath->query('/s:urlset/s:url/s:loc') as $location) {
            $locations[] = $location->textContent;
        }
        $this->assertSame([
            'https://meetaj.ir/',
            'https://meetaj.ir/articles',
            $article->canonicalUrl(),
        ], $locations);
        $this->assertSame($article->updated_at->toDateString(),
            $xpath->evaluate('string(/s:urlset/s:url[s:loc="'.$article->canonicalUrl().'"]/s:lastmod)'));
        $this->assertSame(0, $xpath->query('/s:urlset/s:url[s:loc="https://meetaj.ir/"]/s:lastmod')->length);
    }

    public function test_sitemap_excludes_unpublished_and_non_public_language_rows(): void
    {
        $this->article('published');
        $this->article('draft', ['status' => 'draft']);
        $this->article('future', ['published_at' => now()->addDay()]);
        $this->article('persian-row', ['language' => 'fa']);
        $this->article('german-draft', ['language' => 'de', 'status' => 'draft']);
        $this->assertSame(3, $this->sitemap()->query('/s:urlset/s:url')->length);
    }

    public function test_sitemap_respects_noindex_and_none_but_allows_nofollow(): void
    {
        $allowed = $this->article('indexable', ['seo_data' => ['robots' => 'index, nofollow']]);
        foreach (['noindex, follow', 'NOINDEX', ' none ', 'follow, noindex'] as $index => $robots) {
            $this->article('excluded-'.$index, ['seo_data' => ['robots' => $robots]]);
        }
        $xpath = $this->sitemap();
        $this->assertSame(3, $xpath->query('/s:urlset/s:url')->length);
        $this->assertSame($allowed->canonicalUrl(), $xpath->evaluate('string(/s:urlset/s:url[3]/s:loc)'));
    }

    public function test_robots_points_to_configured_sitemap_and_keeps_public_assets_crawlable(): void
    {
        config(['app.url' => 'http://meetaj.ir/']);
        $response = $this->get('/robots.txt')->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $lines = explode("\n", $response->getContent());
        foreach (['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /forms/', 'Sitemap: http://meetaj.ir/sitemap.xml'] as $line) {
            $this->assertContains($line, $lines);
        }
        $this->assertNotContains('Disallow: /', $lines);
        $this->assertNotContains('Disallow: /assets/', $lines);
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }
}
