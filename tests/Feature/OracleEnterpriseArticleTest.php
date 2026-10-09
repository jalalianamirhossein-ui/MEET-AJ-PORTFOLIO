<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OracleEnterpriseArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'oracle-database-26ai-installation-oracle-linux';

    public function test_bilingual_publication_preserves_runbook_navigation_seo_and_assets(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertTrue($article->tags()->where('slug', 'oracle')->exists());
        $this->assertTrue($article->tags()->where('slug', 'linux')->exists());
        $blocks = [];
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
            foreach (['introduction', 'architecture', 'prerequisites', 'configuration', 'system-service', 'security', 'rman-backup', 'automated-backup', 'restore-recovery', 'troubleshooting', 'best-practices', 'conclusion', 'faq', 'official-references'] as $id) {
                $this->assertSame(1, $xpath->query('//article//section[@id="'.$id.'"]')->length, $id);
            }
            foreach ($xpath->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $this->assertSame(1, $xpath->query('//*[@id="'.substr($anchor->getAttribute('href'), 1).'"]')->length);
            }
            $blocks[$locale] = [];
            foreach ($xpath->query('//article//pre/code') as $block) {
                $blocks[$locale][] = $block->textContent;
            }
            $commands = implode("\n", $blocks[$locale]);
            foreach (['oracle-ai-database-preinstall-26ai', 'oracle-ai-database-ee-26ai-1.0-1.el9.x86_64.rpm', '/etc/init.d/oracledb_ORCLCDB-26ai configure', 'ALTER PLUGGABLE DATABASE ORCLPDB1 SAVE STATE', 'STARTUP MOUNT', 'INCREMENTAL LEVEL 0', 'INCREMENTAL LEVEL 1', 'OPEN RESETLOGS', 'ALLOW_CLEANUP', 'TLS_SERVER_DN_MATCH'] as $required) {
                $this->assertStringContainsString($required, $commands);
            }
            $this->assertGreaterThanOrEqual(3, $xpath->query('//article//div[contains(@class,"table-responsive")]/table')->length);
            foreach (['oracle-database-architecture.png', 'oracle-database-installation-workflow.png', 'oracle-database-systemd-service.png', 'oracle-database-security-hardening.png', 'oracle-database-rman-backup-recovery.png'] as $image) {
                $this->assertSame([1920, 1080], array_slice(getimagesize(public_path('assets/img/articles/content/'.$image)), 0, 2));
                $this->assertSame(1, $xpath->query('//article//img[contains(@src,"'.$image.'") and string-length(@alt)>20]')->length);
            }
            $schema = json_decode($xpath->evaluate('string(//script[@id="article-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $schema['inLanguage']);
            $this->assertSame($metadata['title'], $schema['headline']);
            $faq = json_decode($xpath->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xpath->evaluate('string(//article)'));
            }
        }
        $this->assertSame($blocks['en'], $blocks['fa']);
        $this->assertSame(array_slice(getimagesize(resource_path('assets/img/articles/banners/oracle-database-26ai-banner.png')), 0, 2), array_slice(getimagesize(public_path('assets/img/articles/banners/oracle-database-26ai-banner.png')), 0, 2));
        foreach (glob(resource_path('content/articles/'.self::SLUG.'/*')) as $source) {
            $published = public_path('downloads/'.self::SLUG.'/'.basename($source));
            if (is_file($published)) {
                $this->assertSame(file_get_contents($source), file_get_contents($published));
            }
        }
        foreach (['oracle-rman-backup.sh', 'oracle-rman@.service', 'oracle-rman-daily.timer', 'oracle-rman-weekly.timer', 'oracle-rman-archivelog.timer', 'oracle-rman.env', 'oracle-rman.logrotate'] as $template) {
            $this->assertFileExists(public_path('downloads/'.self::SLUG.'/'.$template));
        }
        $this->get('/articles?q=Oracle')->assertOk()->assertSee($article->path(), false);
        $this->get('/articles?tag=oracle')->assertOk()->assertSee($article->path(), false);
        $this->get('/')->assertOk()->assertSee($article->path(), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(), false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
        $this->assertNotEmpty($article->relatedArticles(3));
    }

    public function test_migration_preserves_editorial_revisions_on_rerun(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Reviewed Oracle editorial revision</p>']);
        $migration = require database_path('migrations/2026_10_09_000048_add_oracle_enterprise_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed Oracle editorial revision</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
