<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleFaqTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_imported_faq_has_persian_questions_and_answers(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        foreach (Article::all() as $article) {
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$article->displayContent(), LIBXML_NONET);
            $nodes = (new \DOMXPath($dom))->query('//section[@id="faq"]//*[@data-en]');
            $this->assertGreaterThan(0, $nodes->length, $article->slug);
            foreach ($nodes as $node) {
                $this->assertMatchesRegularExpression('/\p{Arabic}/u', $node->getAttribute('data-fa'),
                    $article->slug.': '.$node->getAttribute('data-en'));
                $this->assertNotEmpty($node->getAttribute('data-en'));
            }
        }
    }

    public function test_stored_eight_item_faq_is_repaired_without_changing_english_or_custom_persian(): void
    {
        $item = '<div class="article-faq-item"><h3 data-en="Is this safe for production?" data-fa="Is this safe for production?">Is this safe for production?</h3>'
            .'<p data-en="How do I verify the result?">How do I verify the result?</p></div>';
        $custom = '<div class="article-faq-item"><h3 data-en="Is this safe for production?" data-fa="ترجمهٔ اختصاصی نویسنده">Is this safe for production?</h3></div>';
        $outside = '<p data-en="Is this safe for production?" data-fa="Is this safe for production?">Outside FAQ</p>';
        $article = new Article(['title' => 'Test', 'slug' => 'test',
            'content' => $outside.'<section id="faq"><div class="article-faq">'.str_repeat($item, 7).$custom.'</div></section>']);
        $rendered = $article->displayContent();
        $this->assertStringContainsString($outside, $rendered);
        $this->assertStringContainsString('data-en="Is this safe for production?"', $rendered);
        $this->assertStringContainsString('data-fa="ترجمهٔ اختصاصی نویسنده"', $rendered);
        $this->assertStringContainsString('data-fa="آیا اجرای این روش در محیط عملیاتی ایمن است؟"', $rendered);
        $this->assertStringContainsString('data-fa="درستی نتیجه را چگونه تأیید کنیم؟"', $rendered);
        $this->assertSame(8, substr_count($rendered, 'article-faq-item'));
    }
}
