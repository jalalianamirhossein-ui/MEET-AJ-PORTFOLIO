<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MongoDbProductionArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'mongodb-installation-configuration-production-deployment';

    public function test_article_renders_both_languages_with_identical_copyable_commands_and_resolved_images(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertTrue($article->tags()->where('slug', 'mongodb')->exists());
        $commands = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $metadata = $article->presentation['localizations'][$locale];
            $this->assertSame($metadata['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $link) {
                $this->assertSame(1, $xp->query('//*[@id="'.substr($link->getAttribute('href'), 1).'"]')->length);
            }
            foreach ($xp->query('//img[contains(@src,"/articles/") and contains(@src,"/mongodb-")]') as $img) {
                $relative = rawurldecode($img->getAttribute('src'));
                $this->assertFileExists(public_path(ltrim($relative, '/')));
                $this->assertNotEmpty($img->getAttribute('alt'));
            }
            $this->assertSame(4, $xp->query('//article//figure/img')->length);
            foreach (['introduction', 'architecture', 'prerequisites', 'ubuntu-install', 'debian-install', 'rhel-install', 'security', 'tls', 'replica-set', 'replica-security', 'backup', 'monitoring', 'troubleshooting', 'verification', 'best-practices', 'faq', 'official-references'] as $id) {
                $this->assertSame(1, $xp->query('//article//section[@id="'.$id.'"]')->length);
            }
            foreach ($xp->query('//article//pre/code') as $block) $commands[$locale][] = $block->textContent;
            if ($locale === 'en') $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            $faq = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
        }
        $this->assertSame($commands['en'], $commands['fa']);
        $copy = implode("\n", $commands['en']);
        $this->assertStringContainsString('noble/mongodb-org/9.0', $copy);
        $this->assertStringContainsString('jammy/mongodb-org/9.0', $copy);
        $this->assertStringContainsString('bookworm/mongodb-org/8.0', $copy);
        $this->assertStringNotContainsString('bookworm/mongodb-org/9.0', $copy);
        $this->assertStringContainsString('clusterAuthMode: x509', $copy);
        $this->assertStringContainsString('mode: requireTLS', $copy);
        $this->assertDoesNotMatchRegularExpression('/--uri[^\n]*:[^@\n]+@/', $copy);
        foreach (array_merge(glob(resource_path('assets/img/articles/banners/mongodb-*.png')), glob(resource_path('assets/img/articles/content/mongodb-*.png'))) as $file) {
            $size = getimagesize($file);
            $this->assertSame(str_contains(basename($file), 'banner') ? array_slice(getimagesize(resource_path('assets/img/articles/banners/'.basename($file))), 0, 2) : [1920, 1080], [$size[0], $size[1]]);
        }
        $this->get('/articles?q=MongoDB')->assertOk()->assertSee($article->path(), false);
        $this->get('/articles?tag=mongodb')->assertOk()->assertSee($article->path(), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(), false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }

    public function test_publication_migration_preserves_later_cms_edits(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Reviewed CMS content</p>']);
        $migration = require database_path('migrations/2026_10_07_000045_add_mongodb_production_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed CMS content</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
