<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BilingualEnterpriseArticleTest extends TestCase
{
    use RefreshDatabase;

    private function dom(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);

        return new \DOMXPath($dom);
    }

    public function test_all_articles_render_equivalent_runbooks_and_locale_specific_seo_without_javascript(): void
    {
        if (getenv('ARTICLE_PREVIEW_EXPORT')) {
            config(['app.url' => 'http://127.0.0.1:18081']);
            if (! is_dir(storage_path('app/bilingual-preview'))) mkdir(storage_path('app/bilingual-preview'), 0775, true);
        }
        app(LegacyArticleImporter::class)->import(false);
        $articles = Article::published()->get();
        $this->assertCount(25, $articles);
        foreach ($articles as $article) {
            $localeCodes = [];
            $localeIds = [];
            $original = $article->content;
            foreach (['fa', 'en'] as $lang) {
                $url = $article->path().($lang === 'fa' ? '?lang=fa' : '');
                $html = $this->get($url)->assertOk()->getContent();
                if (getenv('ARTICLE_PREVIEW_EXPORT')) {
                    file_put_contents(storage_path('app/bilingual-preview/'.$article->slug.'.'.$lang.'.html'), $html);
                }
                $xp = $this->dom($html);
                $meta = $article->presentation['localizations'][$lang];
                $this->assertSame($lang, $xp->evaluate('string(/html/@lang)'), $article->slug);
                $this->assertSame($lang, $xp->evaluate('string(/html/@data-article-language)'));
                $this->assertSame($meta['meta_title'], $xp->evaluate('string(//head/title)'));
                $this->assertSame($meta['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
                $this->assertSame(implode(', ', $meta['keywords']), $xp->evaluate('string(//meta[@name="keywords"]/@content)'));
                $this->assertSame($meta['title'], $xp->evaluate('string(//h1[@class="article-title hero-title"])'));
                $switchSeo = json_decode($xp->evaluate('string(//script[@id="article-language-seo"])'), true, 512, JSON_THROW_ON_ERROR);
                foreach (['fa', 'en'] as $switchLocale) {
                    $this->assertSame($article->presentation['localizations'][$switchLocale]['meta_title'], $switchSeo[$switchLocale]['title']);
                    $this->assertSame($switchLocale, $switchSeo[$switchLocale]['faq_schema']['inLanguage']);
                    $this->assertArrayNotHasKey('content', $switchSeo[$switchLocale], 'Switch payload must not duplicate article bodies');
                }
                $this->assertSame($article->publicUrl().($lang === 'fa' ? '?lang=fa' : ''), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
                foreach (['fa', 'en'] as $alternate) {
                    $target = $article->publicUrl().($alternate === 'fa' ? '?lang=fa' : '');
                    $this->assertSame($target, $xp->evaluate('string(//link[@hreflang="'.$alternate.'"]/@href)'));
                    $this->assertSame($target, $xp->evaluate('string(//nav[@class="article-translations"]/a[@hreflang="'.$alternate.'"]/@href)'));
                }
                $this->assertSame($lang, $xp->evaluate('string(//article[@class="article-body"]/@lang)'));
                $this->assertSame($lang === 'fa' ? 'rtl' : 'ltr', $xp->evaluate('string(//article[@class="article-body"]/@dir)'));
                foreach ($xp->query('//a[@class="article-teaser-link"]') as $peer) {
                    $this->assertSame($lang === 'fa', str_ends_with($peer->getAttribute('href'), '?lang=fa'));
                }
                $localeCodes[$lang] = [];
                foreach ($xp->query('//article[@class="article-body"]//pre/code') as $code) {
                    $localeCodes[$lang][] = $code->textContent;
                }
                $localeIds[$lang] = [];
                foreach ($xp->query('//article[@class="article-body"]//section[not(ancestor::details)]') as $section) {
                    $localeIds[$lang][] = $section->getAttribute('id');
                }
                $this->assertGreaterThanOrEqual(13, count($localeIds[$lang]), $article->slug);
                $faqSchema = null;
                $articleSchema = null;
                foreach ($xp->query('//head/script[@type="application/ld+json"]') as $script) {
                    $schema = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);
                    if ($schema['@type'] === 'FAQPage') $faqSchema = $schema;
                    if ($schema['@type'] === 'Article') $articleSchema = $schema;
                }
                $this->assertSame($lang, $articleSchema['inLanguage']);
                $this->assertSame($meta['title'], $articleSchema['headline']);
                $this->assertSame($lang, $faqSchema['inLanguage']);
                $faqText = $xp->evaluate('string(//article[@class="article-body"]//section[@id="faq"])');
                $this->assertCount(count($meta['faq']), $faqSchema['mainEntity']);
                foreach ($faqSchema['mainEntity'] as $question) {
                    $this->assertStringContainsString($question['name'], $faqText);
                    $this->assertStringContainsString($question['acceptedAnswer']['text'], $faqText);
                }
                if ($lang === 'en') {
                    foreach ($xp->query('//article[@class="article-body"]//*[self::h2 or self::h3 or self::p or self::td or self::th or self::li][not(ancestor::details) and not(ancestor::pre)]') as $prose) {
                        $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $prose->textContent, $article->slug.': '.$prose->textContent);
                    }
                }
                foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                    $id = substr($anchor->getAttribute('href'), 1);
                    $this->assertSame(1, $xp->query('//*[@id="'.$id.'"]')->length, $article->slug.': '.$id);
                }
            }
            $this->assertSame($localeCodes['fa'], $localeCodes['en'], $article->slug.' code drift');
            $this->assertSame($localeIds['fa'], $localeIds['en'], $article->slug.' section drift');
            $this->assertSame($original, $article->fresh()->content, 'GET must not persist locale');
        }
        $this->assertSame(25, Article::count());
    }

    public function test_query_language_survives_legacy_redirect_and_is_independent_of_cookie_preferences(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $path = '/articles/enable-ssh-linux-complete-guide';
        $this->get($path.'.html?lang=en')->assertRedirect($path.'?lang=en');
        $this->withCookie('lang', 'en')->get($path.'?lang=fa')->assertOk()->assertSee('data-article-language="fa"', false);
        $this->withCookie('lang', 'fa')->get($path)->assertOk()->assertSee('data-article-language="en"', false);
        $this->get($path.'?lang=invalid')->assertOk()->assertSee('data-article-language="en"', false);
        $alias = $this->get($path.'?lang=en')->assertOk()->getContent();
        $this->assertSame(url($path), $this->dom($alias)->evaluate('string(//link[@rel="canonical"]/@href)'));
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $dom = new \DOMDocument;
        $this->assertTrue($dom->loadXML($xml));
        $xp = new \DOMXPath($dom);
        $xp->registerNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xp->registerNamespace('x', 'http://www.w3.org/1999/xhtml');
        $this->assertSame(50, $xp->query('//s:url[contains(s:loc,"/articles/")]')->length);
        $this->assertSame(100, $xp->query('//s:url/x:link')->length);
    }

    public function test_sql_editorial_package_supports_the_same_english_route(): void
    {
        $package = require resource_path('content/articles/sql-server-automatic-backup-job/build.php');
        Article::create(array_merge($package, ['slug' => 'sql-server-automatic-backup-job', 'status' => 'published', 'published_at' => now()->subDay()]));
        $this->get('/articles/sql-server-automatic-backup-job?lang=en')->assertOk()
            ->assertSee($package['presentation']['localizations']['en']['title'])
            ->assertSee('"inLanguage":"en"', false)
            ->assertSee('Why is log backup unavailable in SIMPLE?');
    }

    public function test_updating_existing_imported_articles_enables_english_after_deployment(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::where('slug', 'enable-ssh-linux-complete-guide')->firstOrFail();
        $id = $article->id;
        $translationKey = $article->translation_key;
        $presentation = $article->presentation;
        unset($presentation['localizations']);
        $presentation['source_hash'] = 'pre-bilingual-source';
        $article->update([
            'presentation' => $presentation,
            'content' => '<section id="enterprise-intro"><h2>مقدمه</h2><p>متن نسخه قدیمی فارسی</p></section>',
        ]);

        // A routine import skips existing rows even after source files change.
        $this->artisan('articles:import-legacy')->assertSuccessful();
        $this->assertNull(data_get($article->fresh()->presentation, 'localizations'));
        $this->get($article->path().'?lang=en')->assertOk()->assertSee('متن نسخه قدیمی فارسی');

        // Preview is read-only; the explicit update preserves the shared identity.
        $this->artisan('articles:import-legacy', ['--update-existing' => true, '--dry-run' => true])->assertSuccessful();
        $this->assertNull(data_get($article->fresh()->presentation, 'localizations'));
        $this->artisan('articles:import-legacy', ['--update-existing' => true])->assertSuccessful();
        $article->refresh();
        $this->assertSame($id, $article->id);
        $this->assertSame($translationKey, $article->translation_key);
        $this->assertCount(25, Article::all());
        $this->get($article->path().'?lang=en')->assertOk()
            ->assertSee('data-article-language="en"', false)
            ->assertSee($article->presentation['localizations']['en']['title'])
            ->assertDontSee('متن نسخه قدیمی فارسی');
        $this->get($article->path().'?lang=fa')->assertOk()
            ->assertSee('data-article-language="fa"', false)
            ->assertSee($article->presentation['localizations']['fa']['title']);
    }

    public function test_comparison_articles_keep_their_original_decision_topic(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach ([
            'vsphere-standard-switch-vs-distributed-switch' => 'switch-comparison',
            'http-vs-https-ssl-certificate-impact' => 'http-https-comparison',
            'imap-vs-pop3-email-protocol-comparison' => 'mail-protocol-comparison',
            'windows-hardware-info-cmd-vs-dxdiag' => 'inventory-tool-comparison',
            'mikrotik-unequal-dual-wan-load-balancing-ecmp' => 'ecmp-pcc-comparison',
        ] as $slug => $section) {
            foreach (['en', 'fa'] as $locale) {
                $html = $this->get('/articles/'.$slug.($locale === 'fa' ? '?lang=fa' : ''))->assertOk()->getContent();
                $xp = $this->dom($html);
                $this->assertGreaterThanOrEqual(3, $xp->query('//section[@id="'.$section.'"]//tbody/tr')->length, $slug);
                $this->assertSame(1, $xp->query('//ul[@class="article-toc-list"]//a[@href="#'.$section.'"]')->length);
                $this->assertLessThan(strpos($html, '<section id="enterprise-installation"'), strpos($html, '<section id="'.$section.'"'));
            }
        }
    }
}
