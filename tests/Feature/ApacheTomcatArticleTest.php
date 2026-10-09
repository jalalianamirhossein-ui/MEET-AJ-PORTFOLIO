<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApacheTomcatArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'apache-tomcat-linux-installation-security-hardening';

    public function test_bilingual_article_preserves_configuration_and_localizes_seo_and_images(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('linux', $article->category->slug);
        $this->assertSame(['linux', 'nginx', 'tomcat', 'ubuntu'], $article->tags()->orderBy('slug')->pluck('slug')->all());
        $code = [];
        foreach (['en', 'fa'] as $locale) {
            $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $metadata = $article->presentation['localizations'][$locale];
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($metadata['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($metadata['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            $this->assertSame(1, $xp->query('//h1')->length);
            $this->assertSame(17, $xp->query('//article//section')->length, 'All sections should be authored, with no automatic filler.');
            foreach (['introduction', 'architecture', 'prerequisites', 'versions', 'java', 'configuration', 'systemd', 'security', 'nginx-https', 'firewall', 'deployment', 'troubleshooting', 'upgrades', 'best-practices', 'conclusion', 'faq', 'official-references'] as $id) {
                $this->assertSame(1, $xp->query('//article//section[@id="'.$id.'"]')->length, $id);
            }
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $id = substr($anchor->getAttribute('href'), 1);
                $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length, $id);
            }
            $code[$locale] = [];
            foreach ($xp->query('//article//pre/code') as $block) $code[$locale][] = $block->textContent;
            $joined = implode("\n", $code[$locale]);
            foreach (['TOMCAT_VERSION=11.0.26', 'sha512sum --check', 'Type=simple', 'NoNewPrivileges=true', 'ProtectSystem=strict', 'RestrictSUIDSGID=true', '<Server port="-1">', 'address="127.0.0.1"', 'internalProxies="127.0.0.1/32"', 'autoDeploy="false"', 'unpackWARs="false"', 'proxy_set_header X-Forwarded-For $remote_addr;', 'proxy_set_header Forwarded "";', 'sudo certbot renew --dry-run', 'current.rollback'] as $token) {
                $this->assertStringContainsString($token, $joined);
            }
            $this->assertStringNotContainsString('MemoryDenyWriteExecute=true', $joined);
            $this->assertStringNotContainsString('chmod 777', $joined);
            $schema = json_decode($xp->evaluate('string(//script[@id="article-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $schema['inLanguage']);
            $this->assertSame($metadata['title'], $schema['headline']);
            $faq = json_decode($xp->evaluate('string(//script[@id="article-faq-schema"])'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertCount(8, $faq['mainEntity']);
            foreach (['apache-tomcat-production-architecture.png', 'apache-tomcat-systemd-service.png', 'apache-tomcat-security-hardening.png'] as $filename) {
                $this->assertSame([1920, 1080], array_slice(getimagesize(public_path('assets/img/articles/content/'.$filename)), 0, 2));
                $images = $xp->query('//article//img[contains(@src,"'.$filename.'")]');
                $this->assertSame(1, $images->length);
                $alt = $images->item(0)->getAttribute('alt');
                $this->assertGreaterThan(20, strlen($alt));
                $locale === 'fa' ? $this->assertMatchesRegularExpression('/\p{Arabic}/u', $alt) : $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $alt);
            }
            if ($locale === 'en') $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
        }
        $this->assertSame($code['en'], $code['fa']);
        $this->assertSame(array_slice(getimagesize(resource_path('assets/img/articles/banners/apache-tomcat-linux-security-banner.png')), 0, 2), array_slice(getimagesize(public_path('assets/img/articles/banners/apache-tomcat-linux-security-banner.png')), 0, 2));
    }

    public function test_publication_listing_links_redirect_downloads_and_sitemap_use_existing_routes(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        foreach (['/', '/articles', '/articles?q=Tomcat', '/articles?tag=tomcat', '/articles?tag=linux'] as $path) {
            $this->get($path)->assertOk()->assertSee($article->path());
        }
        $this->get($article->path().'.html')->assertRedirect($article->path());
        $this->get($article->path().'?lang=fa')->assertRedirect($article->path());
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl());
        foreach (['nginx-installation-configuration-ubuntu', 'nginx-reverse-proxy-multiple-domains-single-ip-443', 'linux-security-account-access-management', 'linux-security-auditor-bash', 'enable-ssh-linux-complete-guide'] as $slug) {
            $this->get('/articles/'.$slug)->assertOk();
            $this->assertStringContainsString('/articles/'.$slug, $article->content);
        }
        $this->assertNotEmpty($article->relatedArticles(3));
        foreach (['tomcat.service', 'server.xml', 'logging.properties', 'nginx-bootstrap.conf', 'nginx-tomcat.conf'] as $file) {
            $source = resource_path('content/articles/'.self::SLUG.'/'.$file);
            $this->assertFileExists($source);
            $this->assertSame(file_get_contents($source), file_get_contents(public_path('downloads/'.self::SLUG.'/'.$file)));
        }
        $xml = new \DOMDocument;
        $this->assertTrue($xml->load(resource_path('content/articles/'.self::SLUG.'/server.xml'), LIBXML_NONET));
    }

    public function test_repeated_publication_preserves_editorial_changes(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->content = '<p>Reviewed editorial change</p>';
        $article->save();
        $migration = require database_path('migrations/2026_10_09_000048_add_apache_tomcat_article.php');
        $migration->up();
        $this->assertSame('<p>Reviewed editorial change</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
