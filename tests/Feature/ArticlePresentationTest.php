<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleLocalization;
use App\Services\ArticlePresentation;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_article_has_native_faq_controls_and_preserves_code_in_both_languages(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach (Article::all() as $article) {
            $stored = $article->content;
            foreach (['en', 'fa'] as $locale) {
                $localized = app(ArticleLocalization::class)->apply(clone $article, $locale);
                $prepared = $localized->displayContent();
                preg_match_all('~<pre\b[^>]*>.*?</pre>~is', $localized->content, $original);
                preg_match_all('~<pre\b[^>]*>.*?</pre>~is', $prepared, $displayed);
                $this->assertSame($original[0], $displayed[0], $article->slug.' '.$locale);

                $dom = new \DOMDocument;
                @$dom->loadHTML('<?xml encoding="UTF-8">'.$prepared, LIBXML_NONET);
                $xp = new \DOMXPath($dom);
                $faq = $xp->query('//section[@id="faq"]//details[contains(@class,"article-faq-disclosure")]');
                $this->assertCount(count($article->presentation['localizations'][$locale]['faq']), $faq, $article->slug);
                foreach ($faq as $item) {
                    $this->assertFalse($item->hasAttribute('open'));
                    $this->assertSame(1, $xp->query('./summary/h3', $item)->length);
                    $this->assertNotSame('', trim($xp->evaluate('string(./div)', $item)));
                }
                $this->assertSame(0, $xp->query('//section[not(contains(concat(" ",normalize-space(@class)," ")," article-section "))]')->length);
                $this->assertSame(count($displayed[0]), $xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," article-code ")]//pre')->length);
            }
            $this->assertSame($stored, $article->fresh()->content);
        }
    }

    public function test_presentation_is_repeatable_and_removes_only_empty_prose(): void
    {
        $source = '<section id="architecture"><h2>Architecture</h2><p>&nbsp;<br></p><p>Keep this explanation.</p>'
            .'<pre><code>Client -&gt; Router\n  # keep whitespace</code></pre></section>'
            .'<section id="faq"><h2>FAQ</h2><h3 data-en="Question" data-fa="پرسش">Question</h3><p>Answer</p></section>';
        $service = app(ArticlePresentation::class);
        $prepared = $service->prepare($source);
        $this->assertStringNotContainsString('<p>&nbsp;<br></p>', $prepared);
        $this->assertStringContainsString('<p>Keep this explanation.</p>', $prepared);
        $this->assertStringContainsString('article-architecture', $prepared);
        $this->assertStringContainsString('article-flow', $prepared);
        $this->assertStringContainsString('<pre><code>Client -&gt; Router\n  # keep whitespace</code></pre>', $prepared);
        $this->assertSame($prepared, $service->prepare($prepared));
    }
}
