<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZabbixEnterpriseArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'zabbix-server-linux-windows-agents-backup';

    public function test_both_languages_render_complete_chapters_seo_faq_toc_and_identical_commands(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertSame(['linux', 'nginx', 'ubuntu', 'windows', 'zabbix'], $article->tags()->orderBy('slug')->pluck('slug')->all());
        $commands = [];
        foreach (['en', 'fa'] as $locale) {
            $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $text = $article->presentation['localizations'][$locale];
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($text['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($text['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertSame($text['image_alt'], $xp->evaluate('string(//img[@class="article-hero-thumbnail"]/@alt)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            $this->assertSame(1, $xp->query('//h1')->length);
            $this->assertSame(17, $xp->query('//article//section')->length);
            foreach (['introduction', 'architecture', 'prerequisites', 'configuration', 'linux-agent', 'windows-agent', 'monitoring', 'notifications', 'security', 'database-backup', 'automated-backup', 'recovery', 'troubleshooting', 'best-practices', 'conclusion', 'faq', 'official-references'] as $id) {
                $this->assertSame(1, $xp->query('//article//section[@id="'.$id.'"]')->length, $id);
            }
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $this->assertSame(1, $xp->query('//*[@id="'.substr($anchor->getAttribute('href'), 1).'"]')->length);
            }
            foreach ($xp->query('//article//pre/code') as $block) {
                $commands[$locale][] = $block->textContent;
            }
            $schema = json_decode($xp->evaluate('string(//script[@id="article-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $schema['inLanguage']);
            $this->assertSame($text['title'], $schema['headline']);
            $faq = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            $this->assertSame($text['faq'][0][0], $faq['mainEntity'][0]['name']);
            foreach ($xp->query('//article//figure/img') as $img) {
                $this->assertSame('1920', $img->getAttribute('width'));
                $this->assertSame('1080', $img->getAttribute('height'));
                $alt = $img->getAttribute('alt');
                $locale === 'fa' ? $this->assertMatchesRegularExpression('/\p{Arabic}/u', $alt) : $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $alt);
            }
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            }
        }
        $this->assertSame($commands['en'], $commands['fa']);
        $this->assertStringContainsString('1:7.0.31-1+ubuntu24.04', $article->content);
    }

    public function test_listing_tag_search_sitemap_related_redirects_and_downloads_are_integrated(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        foreach (['/articles', '/articles?q=Zabbix', '/articles?tag=zabbix', '/articles?tag=linux', '/articles?tag=windows'] as $path) {
            $this->get($path)->assertOk()->assertSee($article->path());
        }
        $this->get($article->path().'.html')->assertRedirect($article->path());
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl());
        $this->assertNotEmpty($article->relatedArticles(3));
        $dom = new \DOMDocument;
        @$dom->loadHTML($article->content, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $this->assertSame(13, $xp->query('//a[starts-with(@href,"/downloads/")]')->length);
        foreach ($xp->query('//a[starts-with(@href,"/downloads/")]') as $link) {
            $name = basename($link->getAttribute('href'));
            $source = resource_path('content/articles/'.self::SLUG.'/'.$name);
            $public = public_path(ltrim($link->getAttribute('href'), '/'));
            $this->assertFileExists($source);
            $this->assertSame(file_get_contents($source), file_get_contents($public));
        }
        foreach ($xp->query('//a[starts-with(@href,"/articles/")]') as $link) {
            $this->get($link->getAttribute('href'))->assertOk();
        }
        $images = json_decode(file_get_contents(resource_path('content/articles/'.self::SLUG.'/images.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(6, $images);
        foreach ($images as $image) {
            $folder = str_contains($image['filename'], '-banner') ? 'banners' : 'content';
            $path = 'assets/img/articles/'.$folder.'/'.$image['filename'];
            $source = resource_path($path);
            $this->assertSame($image['dimensions'], array_slice(getimagesize($source), 0, 2));
            $this->assertSame($image['sha256'], hash_file('sha256', $source));
            $this->assertSame(hash_file('sha256', $source), hash_file('sha256', public_path($path)));
        }
    }

    public function test_rerunning_migration_preserves_cms_editorial_changes(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->content = '<p>Reviewed CMS edit</p>';
        $article->save();
        $migration = require database_path('migrations/2026_10_09_000049_add_zabbix_enterprise_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed CMS edit</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
