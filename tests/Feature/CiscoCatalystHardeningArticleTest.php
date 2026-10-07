<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CiscoCatalystHardeningArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_imported_article_localizes_prose_and_preserves_independent_configurations(): void
    {
        $article = Article::where('slug', 'cisco-catalyst-layer-2-layer-3-switch-hardening')->firstOrFail();
        $localizedCode = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($article->presentation['localizations'][$locale]['title'], $xp->evaluate('string(//meta[@property="og:title"]/@content)'));
            $this->assertSame(26, $xp->query('//article//section')->length);
            $this->assertSame(12, $xp->query('//article//*[@role="note"]')->length);
            foreach ($xp->query('//article//pre/code') as $block) $localizedCode[$locale][] = trim($block->textContent);
            if ($locale === 'en') $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            $this->assertStringContainsString('Verify command availability for your Catalyst platform and IOS/IOS XE release before deployment.', $xp->evaluate('string(//article)'));
        }
        $this->assertSame($localizedCode['en'], $localizedCode['fa']);
        $directory = public_path('downloads/'.$article->slug);
        $l2 = file_get_contents($directory.'/l2-access.cfg');
        $l3 = file_get_contents($directory.'/l3-switch.cfg');
        foreach ([$l2, $l3] as $config) {
            $this->assertContains(trim($config), $localizedCode['en']);
            $this->assertStringContainsString('v3 priv read NMS-READ access SNMP-ACL', $config);
            $this->assertStringContainsString('transport input ssh', $config);
            $this->assertStringNotContainsString('snmp-server community', $config);
        }
        $this->assertStringContainsString("\nno ip routing\n", $l2);
        $this->assertStringContainsString('ip default-gateway <GATEWAY>', $l2);
        $this->assertStringNotContainsString('ip route 0.0.0.0', $l2);
        $this->assertStringContainsString("\nip routing\n", $l3);
        $this->assertStringNotContainsString('no ip routing', $l3);
        $this->assertStringNotContainsString('ip default-gateway', $l3);
        $this->assertStringContainsString('ip access-group USERS-TO-MGMT in', $l3);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(), false);
        $this->get('/articles?q=Cisco')->assertOk()->assertSee($article->path(), false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }
}
