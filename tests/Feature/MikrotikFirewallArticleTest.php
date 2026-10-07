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
