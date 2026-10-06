<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupPolicyMsiArticleTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'deploy-msi-active-directory-group-policy';

    public function test_migration_integrates_both_languages_metadata_faq_and_discovery(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('microsoft', $article->category->slug);
        $this->assertSame('/assets/img/articles/content/'.self::SLUG.'/'.self::SLUG.'.png', $article->thumbnailUrl());
        $this->assertFileExists(public_path(ltrim($article->thumbnailUrl(), '/')));
        $inline = '/assets/img/articles/content/windows-group-policy-msi-deployment-guide.png';
        $this->assertFileExists(public_path(ltrim($inline, '/')));
        $this->assertSame(hash_file('sha256', resource_path('assets/img/articles/content/'.self::SLUG.'/'.self::SLUG.'.png')),
            hash_file('sha256', public_path(ltrim($article->thumbnailUrl(), '/'))));
        $codes = [];
        foreach (['en', 'fa'] as $locale) {
            $response = $this->withUnencryptedCookie('lang', $locale)->get($article->path())->assertOk();
            $response->assertSee($inline, false);
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $text = $article->presentation['localizations'][$locale];
            $this->assertSame($locale, $xp->evaluate('string(/html/@lang)'));
            $this->assertSame($text['meta_title'], $xp->evaluate('string(//head/title)'));
            $this->assertSame($text['description'], $xp->evaluate('string(//meta[@name="description"]/@content)'));
            $this->assertSame($text['title'], $xp->evaluate('string(//meta[@property="og:title"]/@content)'));
            $this->assertSame(implode(', ', $text['keywords']), $xp->evaluate('string(//meta[@name="keywords"]/@content)'));
            $this->assertSame($article->publicUrl(), $xp->evaluate('string(//link[@rel="canonical"]/@href)'));
            $this->assertSame($text['title'], $xp->evaluate('string(//h1[@class="article-title hero-title"])'));
            $this->assertSame(24, $xp->query('//article//section')->length);
            foreach ($xp->query('//ul[@class="article-toc-list"]//a[starts-with(@href,"#")]') as $anchor) {
                $this->assertSame(1, $xp->query('//*[@id="'.substr($anchor->getAttribute('href'), 1).'"]')->length);
            }
            $codes[$locale] = [];
            foreach ($xp->query('//article//pre/code') as $block) $codes[$locale][] = $block->textContent;
            $schemas = [];
            foreach ($xp->query('//head/script[@type="application/ld+json"]') as $script) {
                $schema = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);
                $schemas[$schema['@type']] = $schema;
            }
            $this->assertSame($locale, $schemas['Article']['inLanguage']);
            $this->assertSame($text['title'], $schemas['Article']['headline']);
            $this->assertCount(7, $schemas['FAQPage']['mainEntity']);
            $faqText = $xp->evaluate('string(//section[@id="faq"])');
            foreach ($schemas['FAQPage']['mainEntity'] as $question) {
                $this->assertStringContainsString($question['name'], $faqText);
                $this->assertStringContainsString($question['acceptedAnswer']['text'], $faqText);
            }
            if ($locale === 'en') {
                $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $xp->evaluate('string(//article)'));
            }
            $this->assertStringNotContainsString('—', $xp->evaluate('string(//article)'));
        }
        $this->assertSame($codes['en'], $codes['fa']);
        $this->get('/')->assertOk()->assertSee($article->path(), false);
        $this->get('/articles?q=MSI')->assertOk()->assertSee($article->path(), false);
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertSame(1, substr_count($xml, '<loc>'.$article->publicUrl().'</loc>'));
        $this->get($article->path().'.html')->assertRedirect($article->path());
    }

    public function test_repeated_migration_preserves_cms_edits_and_shared_identity(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $key = $article->translation_key;
        $article->update(['content' => '<p>CMS revision</p>']);
        $migration = require database_path('migrations/2026_10_06_000025_add_group_policy_msi_article.php');
        $migration->up();
        $this->assertSame('<p>CMS revision</p>', $article->fresh()->content);
        $this->assertSame($key, $article->fresh()->translation_key);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
