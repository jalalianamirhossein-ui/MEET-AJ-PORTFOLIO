<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrafanaZabbixArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'grafana-installation-zabbix-integration';

    public function test_complete_bilingual_article_preserves_commands_sections_seo_and_downloads(): void
    {
        if (getenv('ARTICLE_PREVIEW_EXPORT')) {
            config(['app.url' => 'http://127.0.0.1:18081']);
            if (! is_dir(storage_path('app/bilingual-preview'))) { mkdir(storage_path('app/bilingual-preview'), 0755, true); }
        }
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $metadata = json_decode(file_get_contents(resource_path('content/articles/'.self::SLUG.'/metadata.json')), true, flags: JSON_THROW_ON_ERROR);
        $commands = [];
        foreach (['en', 'fa'] as $locale) {
            $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
            if (getenv('ARTICLE_PREVIEW_EXPORT')) {
                file_put_contents(storage_path('app/bilingual-preview/'.self::SLUG.'.'.$locale.'.html'), $html);
            }
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame(1, $xp->query('//h1')->length);
            $this->assertSame(18, $xp->query('//article//h2')->length, 'Authored chapters must not gain generic filler');
            $this->assertSame(1, $xp->query('//article//figure[@id="architecture"]')->length);
            $this->assertLessThan(strpos($html, 'id="configuration"'), strpos($html, 'id="architecture"'));
            $this->assertSame($metadata['localizations'][$locale]['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            foreach ($metadata['sections'] as $id) {
                $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length, $id);
            }
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $this->assertSame(1, $xp->query('//*[@id="'.substr($anchor->getAttribute('href'), 1).'"]')->length);
            }
            foreach ($xp->query('//article//pre/code') as $block) {
                $this->assertSame('ltr', $block->parentNode->getAttribute('dir'));
                $commands[$locale][] = $block->textContent;
            }
            $this->assertSame(6, $xp->query('//article//figure/img')->length);
            foreach ($xp->query('//article//figure/img') as $img) {
                $this->assertSame('1920', $img->getAttribute('width'));
                $this->assertSame('1080', $img->getAttribute('height'));
                if ($locale === 'fa') { $this->assertMatchesRegularExpression('/\p{Arabic}/u', $img->getAttribute('alt')); }
            }
            $faq = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true, flags: JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            $this->assertSame($metadata['localizations'][$locale]['faq'][0][0], $faq['mainEntity'][0]['name']);
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            }
        }
        $this->assertSame($commands['en'], $commands['fa']);
        $this->assertGreaterThan(30, count($commands['en']));
        foreach ($metadata['downloads'] as $name) {
            $source = resource_path('content/articles/'.self::SLUG.'/'.$name);
            $public = public_path('downloads/'.self::SLUG.'/'.$name);
            $this->assertFileExists($public);
            $this->assertSame(file_get_contents($source), file_get_contents($public));
            $this->assertStringContainsString('/downloads/'.self::SLUG.'/'.$name, $article->content);
        }
    }

    public function test_cms_library_tags_ordering_sitemap_and_related_article_link(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertSame(['devops', 'grafana', 'infrastructure', 'linux', 'monitoring', 'observability', 'zabbix'], $article->tags()->orderBy('slug')->pluck('slug')->all());
        foreach (['/articles', '/articles?q=Grafana', '/articles?tag=grafana', '/articles?tag=observability'] as $path) {
            $this->get($path)->assertOk()->assertSee($article->path());
        }
        $this->get($article->path().'.html')->assertRedirect($article->path());
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl());
        $this->assertStringContainsString('/articles/zabbix-server-linux-windows-agents-backup', $article->content);
        $this->assertNotEmpty($article->relatedArticles(3));
        $priority = config('article-order.enterprise');
        $this->assertSame(array_search('zabbix-server-linux-windows-agents-backup', $priority, true) + 1, array_search(self::SLUG, $priority, true));
        $zabbix = Article::where('slug', 'zabbix-server-linux-windows-agents-backup')->firstOrFail();
        $this->assertStringContainsString($article->path(), $zabbix->content);
    }

    public function test_registration_is_idempotent_and_preserves_cms_edits(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Approved CMS edit</p>', 'status' => 'draft']);
        $zabbix = Article::where('slug', 'zabbix-server-linux-windows-agents-backup')->firstOrFail();
        $zabbix->update(['content' => '<p>Preserve Zabbix editorial work</p>', 'status' => 'draft']);
        $before = $zabbix->fresh()->getAttributes();
        $migration = require database_path('migrations/2026_10_10_000053_add_grafana_zabbix_article.php');
        $migration->up();
        $migration->up();
        app(LegacyArticleImporter::class)->import(false, false, [self::SLUG]);
        $this->assertSame('<p>Approved CMS edit</p>', $article->fresh()->content);
        $this->assertSame('draft', $article->fresh()->status);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
        $saved = $zabbix->fresh();
        $this->assertStringContainsString('Preserve Zabbix editorial work', $saved->content);
        $this->assertSame(1, substr_count($saved->content, $article->path()));
        foreach ($before as $key => $value) {
            if ($key !== 'content') { $this->assertSame($value, $saved->getAttributes()[$key], $key); }
        }
    }
}
