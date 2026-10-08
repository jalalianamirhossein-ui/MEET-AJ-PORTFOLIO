<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NginxSniReverseProxyArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'nginx-reverse-proxy-multiple-domains-single-ip-443';

    public function test_bilingual_article_preserves_layout_metadata_code_and_images(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertTrue($article->tags()->where('slug', 'nginx')->exists());
        $codes = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $metadata = $article->presentation['localizations'][$locale];
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($metadata['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($metadata['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertSame($metadata['title'], $xp->evaluate('string(//meta[@property="og:title"]/@content)'));
            $this->assertSame(implode(', ', $metadata['keywords']), $xp->evaluate('string(//meta[@name="keywords"]/@content)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            $this->assertGreaterThanOrEqual(27, $xp->query('//article//section')->length);
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $this->assertSame(1, $xp->query('//*[@id="'.substr($anchor->getAttribute('href'), 1).'"]')->length);
            }
            foreach (['Nginx Reverse Proxy Infrastructure Diagram.png', 'Nginx SNI Routing Infographic.png', 'Nginx Reverse Proxy Troubleshooting Flowchart.png', 'NAT vs Nginx Reverse Proxy.png'] as $image) {
                $response->assertSee('/assets/img/articles/content/'.str_replace(' ', '%20', $image), false);
                $this->assertFileExists(public_path('assets/img/articles/content/'.$image));
            }
            $response->assertSee('Nginx Reverse Proxy Routing Infographic.png', false);
            $codes[$locale] = [];
            foreach ($xp->query('//article//pre/code') as $block) $codes[$locale][] = $block->textContent;
            $schema = json_decode($xp->evaluate('string(//script[@id="article-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $schema['inLanguage']);
            $this->assertSame($metadata['title'], $schema['headline']);
            $faq = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            foreach ($faq['mainEntity'] as $question) {
                $this->assertStringContainsString($question['name'], $xp->evaluate('string(//section[@id="faq"])'));
            }
            if ($locale === 'en') $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
        }
        $this->assertSame($codes['en'], $codes['fa']);
        $config = file_get_contents(public_path('downloads/'.self::SLUG.'/reverse-proxy.conf'));
        $this->assertContains(trim($config), array_map('trim', $codes['en']));
        $this->get('/articles?q=Nginx')->assertOk()->assertSee($article->path(), false);
        $this->get('/')->assertOk()->assertSee($article->path(), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(), false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }

    public function test_migration_rerun_preserves_cms_edits(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Reviewed CMS revision</p>']);
        $migration = require database_path('migrations/2026_10_07_000030_add_nginx_sni_reverse_proxy_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed CMS revision</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
