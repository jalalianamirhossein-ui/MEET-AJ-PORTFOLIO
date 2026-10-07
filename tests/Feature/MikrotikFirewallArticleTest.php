<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use App\Services\MikrotikFirewallArticleRepair;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MikrotikFirewallArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_repair_preserves_content_and_publication_details(): void
    {
        $article = Article::where('slug', 'mikrotik-firewall-hardening-input-forward-chain')->firstOrFail();
        $banner = '/assets/img/articles/banners/MikroTik Firewall Hardening Network Blueprint.png';
        $presentation = $article->presentation;
        $presentation['thumbnail'] = '/assets/img/banners/site/hero-bg.jpg';
        $presentation['gallery'] = '/assets/img/banners/site/hero-bg.jpg';
        $article->update([
            'featured_image' => '/assets/img/banners/site/hero-bg.jpg',
            'presentation' => $presentation,
        ]);
        $content = $article->content;
        $publishedAt = $article->published_at;
        $status = $article->status;
        $migration = require database_path('migrations/2026_10_07_000048_restore_mikrotik_firewall_blueprint_banner.php');
        $migration->up();
        $article->refresh();

        $this->assertSame($banner, $article->featured_image);
        $this->assertSame($banner, $article->thumbnailUrl());
        $this->assertSame($banner, $article->galleryUrl());
        $this->assertSame($banner, $article->seo_data['og_image']);
        $this->assertSame($banner, $article->seo_data['twitter_image']);
        $this->assertSame($content, $article->content);
        $this->assertEquals($publishedAt, $article->published_at);
        $this->assertSame($status, $article->status);
        $updatedAt = $article->updated_at;
        $migration->up();
        $this->assertEquals($updatedAt, $article->fresh()->updated_at);
    }

    public function test_reimport_keeps_the_network_blueprint_on_the_homepage_and_article(): void
    {
        $slug = 'mikrotik-firewall-hardening-input-forward-chain';
        $banner = '/assets/img/articles/banners/MikroTik Firewall Hardening Network Blueprint.png';
        $article = Article::where('slug', $slug)->firstOrFail();
        $presentation = $article->presentation;
        $presentation['source_hash'] = 'outdated-source';
        $article->update(['presentation' => $presentation]);

        $result = app(LegacyArticleImporter::class)->import(false, true, [$slug]);
        $this->assertSame(1, $result['updated']);
        $article->refresh();
        $this->assertSame($banner, $article->featured_image);
        $this->assertSame($banner, $article->thumbnailUrl());
        $this->assertSame($banner, $article->galleryUrl());

        $html = $this->get('/')->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $images = $xp->query('//article[.//a[@href="/articles/'.$slug.'"]]//img');
        $this->assertSame(1, $images->length);
        $this->assertSame($banner, $images->item(0)->getAttribute('src'));
        $this->get($article->path())->assertOk()->assertSee('src="'.$banner.'"', false);
    }

    public function test_both_languages_show_faq_and_translated_table_without_changing_commands(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, ['mikrotik-firewall-hardening-input-forward-chain']);
        $article = Article::where('slug', 'mikrotik-firewall-hardening-input-forward-chain')->firstOrFail();
        $codes = [];
        foreach (['en', 'fa'] as $locale) {
            $html = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame(2, $xp->query('//section[@id="faq"]//details')->length);
            foreach ($article->presentation['localizations'][$locale]['faq'] as $pair) {
                $this->assertStringContainsString($pair[0], $xp->evaluate('string(//section[@id="faq"])'));
                $this->assertStringContainsString($pair[1], $xp->evaluate('string(//section[@id="faq"])'));
            }
            $this->assertStringContainsString($locale === 'fa' ? 'رایانه مدیر' : 'Admin PC', $xp->evaluate('string(//section[@id="testing"])'));
            $this->assertStringContainsString($locale === 'fa' ? 'هشدار Production' : 'Production warning', $xp->evaluate('string(//article)'));
            foreach ($xp->query('//article//pre/code') as $code) $codes[$locale][] = $code->textContent;
        }
        $this->assertSame($codes['en'], $codes['fa']);
        $service = app(MikrotikFirewallArticleRepair::class);
        $this->assertSame($article->content, $service->repair($article->content, $article->presentation['localizations']));
    }
}
