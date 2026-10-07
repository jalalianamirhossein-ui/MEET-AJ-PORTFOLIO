<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedisProductionArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'redis-installation-configuration-replication';

    public function test_article_renders_both_languages_with_intact_runbook_seo_and_assets(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertTrue($article->tags()->where('slug', 'redis')->exists());
        $codes = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xpath = new \DOMXPath($dom);
            $metadata = $article->presentation['localizations'][$locale];
            $this->assertSame($locale, $xpath->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xpath->evaluate('string(/html/@dir)'));
            $this->assertSame($metadata['meta_title'], $xpath->evaluate('string(//head/title)'));
            $this->assertSame($metadata['description'], $xpath->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertSame($article->publicUrl(), $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
            foreach ($xpath->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $id = substr($anchor->getAttribute('href'), 1);
                $this->assertSame(1, $xpath->query('//*[@id="'.$id.'"]')->length, $id);
            }
            foreach (['authentication-acl', 'persistence', 'replica-configuration', 'replication-is-not-ha', 'sentinel', 'cluster-comparison', 'backup-restore', 'troubleshooting', 'best-practices'] as $id) {
                $this->assertSame(1, $xpath->query('//article//section[@id="'.$id.'"]')->length, $id);
            }
            $codes[$locale] = [];
            foreach ($xpath->query('//article//pre/code') as $block) $codes[$locale][] = $block->textContent;
            $joined = implode("\n", $codes[$locale]);
            foreach (['replicaof 10.10.20.10 6379', 'masteruser repl', 'ACL DRYRUN app FLUSHALL', 'SET company "MEET AJ"', 'WAIT 1 5000', 'role:slave', 'appenddirname "appendonlydir"'] as $command) {
                $this->assertStringContainsString($command, $joined);
            }
            $schema = json_decode($xpath->evaluate('string(//script[@id="article-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $schema['inLanguage']);
            $this->assertSame($metadata['title'], $schema['headline']);
            $faq = json_decode($xpath->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xpath->evaluate('string(//article)'));
            }
            foreach (['redis-production-architecture.png', 'redis-replication-architecture.png', 'redis-sentinel-high-availability.png', 'redis-monitoring-architecture.png'] as $image) {
                $path = public_path('assets/img/articles/content/'.$image);
                $this->assertSame([1920, 1080], array_slice(getimagesize($path), 0, 2));
                $this->assertSame(1, $xpath->query('//article//img[contains(@src,"'.$image.'") and string-length(@alt)>20]')->length);
            }
            $this->assertSame([1000, 1000], array_slice(getimagesize(public_path('assets/img/articles/banners/redis-production-banner.png')), 0, 2));
        }
        $this->assertSame($codes['en'], $codes['fa'], 'Localization must preserve executable runbook code.');
        foreach (['primary-baseline.conf', 'replica-baseline.conf', 'bootstrap-acl.sh', 'tls-overlay.conf', 'cache-memory-overlay.conf', 'sentinel-monitor-template.conf', 'prometheus-scrape.yml'] as $file) {
            $this->assertFileExists(public_path('downloads/'.self::SLUG.'/'.$file));
            $this->assertSame(file_get_contents(resource_path('content/articles/'.self::SLUG.'/'.$file)), file_get_contents(public_path('downloads/'.self::SLUG.'/'.$file)));
        }
        $this->get('/articles?q=Redis')->assertOk()->assertSee($article->path(), false);
        $this->get('/')->assertOk()->assertSee($article->path(), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(), false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }

    public function test_migration_rerun_preserves_editorial_changes(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Reviewed editorial revision</p>']);
        $migration = require database_path('migrations/2026_10_07_000047_add_redis_production_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed editorial revision</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
