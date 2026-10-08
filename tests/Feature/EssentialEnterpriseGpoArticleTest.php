<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EssentialEnterpriseGpoArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_renders_both_languages_with_all_assets_and_shared_commands(): void
    {
        $article = Article::where('slug', '10-essential-group-policies-windows-domain')->firstOrFail();
        $commands = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($article->presentation['localizations'][$locale]['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertGreaterThanOrEqual(19, $xp->query('//article//section')->length);
            $this->assertSame(8, $xp->query('//article//section[@id="faq"]//details[contains(@class,"article-faq-item")]')->length);
            $this->assertSame(0, $xp->query('//article//section[not(@id="references") and not(@id="official-references")]//a[starts-with(@href,"https://learn.microsoft.com")]')->length);
            $this->assertSame('official-references', $xp->evaluate('string((//article//section)[last()]/@id)'));
            foreach ($xp->query('//script[@type="application/ld+json"]') as $script) {
                $schema = json_decode($script->textContent,true);
                if (($schema['@type'] ?? '') === 'FAQPage') {
                    $this->assertCount(8,$schema['mainEntity']);
                    $this->assertSame($article->presentation['localizations'][$locale]['faq'][0][0],$schema['mainEntity'][0]['name']);
                }
            }
            $this->assertSame(4, $xp->query('//article//figure')->length);
            $this->assertSame(10, $xp->query('//article//section[@id="summary" or .//*[@id="summary"]]//tbody/tr')->length);
            $this->assertSame(12, $xp->query('//article//section[@id="enterprise-checklist"]//ul[1]/li')->length);
            $this->assertSame(11, $xp->query('//article//section[@id="enterprise-checklist"]//ul[2]/li')->length);
            $this->assertSame(0, $xp->query('//article//pre[not(@dir="ltr")]')->length);
            foreach ($xp->query('//article//pre/code') as $node) $commands[$locale][] = trim($node->textContent);
            if ($locale === 'en') $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            foreach ($xp->query('//article//a[starts-with(@href,"#")]') as $anchor) {
                $id = substr($anchor->getAttribute('href'),1);
                $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length);
            }
        }
        $this->assertSame($commands['en'], $commands['fa']);
        $this->assertStringContainsString('Get-ADUserResultantPasswordPolicy', implode("\n",$commands['en']));
        $this->assertStringContainsString("-Path 'C:\\Temp\\PC-PILOT-01-RSoP.html'", implode("\n",$commands['en']));
        $this->assertStringContainsString('→ System'."\n".'→ LAPS', str_replace("\r\n", "\n", implode("\n",$commands['en'])));
        $this->assertSame('microsoft', $article->category->slug);
        $this->assertSame('published', $article->status);
        $banner = public_path('assets/img/articles/banners/10-Essential-Group-Policies-for-Enterprise-Windows.png');
        $this->assertSame([1000,1000], array_slice(getimagesize($banner),0,2));
        foreach (['Active-Directory-GPO-Architecture.png','Windows-Group-Policy-Security-Baseline.png','GPO-Deployment-Workflow-Test-to-Production.png','Group-Policy-Troubleshooting-gpresult-RSoP.png'] as $name) {
            $this->assertFileExists(public_path('assets/img/articles/content/'.$name));
            $this->assertStringContainsString($name,$article->content);
        }
        $this->get('/articles?q=Group+Policies')->assertOk()->assertSee($article->path(),false);
        $this->get('/sitemap.xml')->assertOk()->assertSee($article->publicUrl(),false);
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }
}
