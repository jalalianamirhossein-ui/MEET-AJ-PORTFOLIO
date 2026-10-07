<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniFiMeshArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'ubiquiti-unifi-wireless-mesh-network';

    public function test_article_renders_both_locales_with_all_sections_and_localized_images(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertTrue($article->tags()->where('slug', 'ubiquiti')->exists());
        $this->assertSame('ubiquiti', $article->category->slug);
        $this->assertSame(strtolower(\App\Models\Tag::BRAND_COLORS['ubiquiti']), $article->accentColor());
        foreach (['fa', 'en'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($locale === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(/html/@dir)'));
            $this->assertSame($article->presentation['localizations'][$locale]['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame(23, $xp->query('//article//section')->length);
            $this->assertSame(8, $xp->query('//section[@id="faq"]//h3')->length);
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $id = substr($anchor->getAttribute('href'), 1);
                $this->assertSame(1, $xp->query('//article//*[@id="'.$id.'"]')->length);
            }
            $images = $xp->query('//article[@class="article-body"]//img | //figure[@class="article-hero-media"]/img');
            $this->assertSame(5, $images->length);
            foreach ($images as $img) {
                $this->assertSame($img->getAttribute('data-'.$locale.'-alt'), $img->getAttribute('alt'));
                $this->assertSame($img->getAttribute('data-'.$locale.'-title'), $img->getAttribute('title'));
                $this->assertNotSame('', $img->getAttribute('alt'));
                $this->assertFileExists(public_path(ltrim($img->getAttribute('src'), '/')));
                $this->assertSame(1, $xp->query('../figcaption', $img)->length);
            }
            $this->assertSame(10, $xp->query('//section[@id="troubleshooting"]/h3')->length);
            $this->assertGreaterThan(8, $xp->query('//article//a[starts-with(@href,"https://help.ui.com/")]')->length);
            $this->assertStringNotContainsString('set-inform', $response->getContent());
        }
    }

    public function test_import_rerun_preserves_editorial_changes_and_library_discovery(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->get('/articles')->assertOk()->assertSee($article->path());
        $article->update(['content' => '<p>Reviewed by editor</p>']);
        app(LegacyArticleImporter::class)->import(false, false, [self::SLUG]);
        $this->assertSame('<p>Reviewed by editor</p>', $article->fresh()->content);
    }
}
