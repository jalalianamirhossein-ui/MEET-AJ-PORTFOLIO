<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Article;
use App\Services\LegacyArticleImporter;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryFilterColorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_color_follows_database_category_changes(): void
    {
        $category = Category::where('slug', 'cisco')->where('language', 'en')->firstOrFail();
        foreach (['#123456', '#abcdef'] as $color) {
            $category->update(['accent_color' => $color]);
            $html = view('articles.partials.category-filters')->render();
            $dom = new \DOMDocument;
            @$dom->loadHTML($html, LIBXML_NONET);
            $xp = new \DOMXPath($dom);
            $this->assertSame('--topic: '.$color.';', $xp->evaluate('string(//button[@data-filter=".filter-cisco"]/@style)'));
        }
    }

    public function test_brand_without_category_keeps_its_palette_fallback(): void
    {
        Category::where('slug', 'fortinet')->delete();
        $html = view('articles.partials.category-filters')->render();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html, LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $this->assertSame('--topic: '.strtolower(Tag::BRAND_COLORS['fortinet']).';', $xp->evaluate('string(//button[@data-filter=".filter-fortinet"]/@style)'));
    }

    public function test_qnap_falls_back_to_purple_and_respects_a_saved_color(): void
    {
        Category::where('slug', 'qnap')->delete();
        $this->assertSame('#6f2da8', Category::accentColorForSlug('qnap'));
        $category = new Category(['slug' => 'qnap', 'accent_color' => 'invalid']);
        $this->assertSame('#6f2da8', $category->accentColor());
        $category->accent_color = '#123456';
        $this->assertSame('#123456', $category->accentColor());
    }

    public function test_all_article_cards_and_pages_use_their_primary_filter_color(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $filterDom = new \DOMDocument;
        @$filterDom->loadHTML(view('articles.partials.category-filters')->render(), LIBXML_NONET);
        $filters = new \DOMXPath($filterDom);
        foreach (Article::with(['category', 'tags'])->where('status', 'published')->get() as $article) {
            $filter = '.filter-'.$article->primaryFilterSlug();
            $filterStyle = $filters->evaluate('string(//button[@data-filter="'.$filter.'"]/@style)');
            $this->assertNotEmpty($filterStyle, $article->slug);
            preg_match('/--topic:\s*(#[a-f0-9]{6})/i', $filterStyle, $match);
            $color = strtolower($match[1]);
            foreach (['library', 'related'] as $variant) {
                $card = view('components.article-card', compact('article', 'variant'))->render();
                $this->assertStringContainsString('--topic: '.$color.';', $card, $article->slug);
            }
            $page = $this->get($article->path())->assertOk()->getContent();
            $this->assertStringContainsString('--article-primary: '.$color.';', $page, $article->slug);
            $this->assertStringContainsString('--article-secondary: color-mix(in srgb, '.$color.' 85%, #fff);', $page, $article->slug);
            $this->assertStringContainsString('--article-accent: color-mix(in srgb, '.$color.' 65%, #fff);', $page, $article->slug);
        }
    }

    public function test_generic_category_and_missing_brand_category_use_the_assigned_filter_palette(): void
    {
        Category::where('slug', 'fortinet')->delete();
        $others = Category::where('slug', 'others')->where('language', 'en')->firstOrFail();
        $article = Article::create([
            'title' => 'FortiGate example', 'slug' => 'fortigate-example',
            'content' => '<p>Example.</p>', 'category_id' => $others->id,
        ]);
        $tag = Tag::firstOrCreate(['slug' => 'fortinet'], ['name' => 'Fortinet']);
        $article->tags()->attach($tag);
        $this->assertSame('#ee3124', $article->accentColor());
        $this->assertSame($tag->accentColor(), $article->accentColor());
        $this->assertStringContainsString('filter-fortinet', view('components.article-card', ['article' => $article])->render());
        $article->tags()->detach();
        $article->unsetRelation('tags');
        $article->presentation = ['filter_class' => 'filter-qnap'];
        $this->assertSame('#6f2da8', $article->accentColor());
        $this->assertStringContainsString('filter-qnap', view('components.article-card', ['article' => $article])->render());
    }

    public function test_cms_filter_color_changes_reach_translated_articles_and_keep_the_primary_brand(): void
    {
        $english = Category::where('slug', 'mikrotik')->where('language', 'en')->firstOrFail();
        $persian = Category::where('slug', 'mikrotik')->where('language', 'fa')->firstOrFail();
        $persian->update(['accent_color' => '#987654']);
        $article = Article::create([
            'title' => 'آزمایش', 'slug' => 'translated-mikrotik-example', 'language' => 'fa',
            'content' => '<p>متن.</p>', 'category_id' => $persian->id,
        ]);
        $microsoft = Tag::firstOrCreate(['slug' => 'microsoft'], ['name' => 'Microsoft']);
        $article->tags()->attach($microsoft);
        foreach (['#123456', '#abcdef'] as $color) {
            $english->update(['accent_color' => $color]);
            $this->assertSame('mikrotik', $article->primaryFilterSlug());
            $this->assertSame($color, $article->accentColor());
            $this->assertStringContainsString('--topic: '.$color.';', view('components.article-card', ['article' => $article])->render());
            $this->assertStringContainsString('--topic: '.$color.';', view('articles.partials.category-filters')->render());
        }
    }

    public function test_truenas_color_repair_handles_missing_or_gold_categories_without_replacing_content(): void
    {
        Category::where('slug', 'qnap')->delete();
        $others = Category::where('slug', 'others')->where('language', 'en')->firstOrFail();
        $article = Article::create([
            'title' => 'TrueNAS', 'slug' => 'truenas-zfs-enterprise',
            'category_id' => $others->id, 'content' => '<p>Edited in the CMS.</p>',
            'status' => 'draft', 'sort_order' => 9,
            'presentation' => ['filter_class' => 'filter-others', 'body_class' => 'article-page theme-other', 'card_title_fa' => 'عنوان ویرایش‌شده'],
        ]);
        $existingTag = Tag::firstOrCreate(['slug' => 'custom-editorial-tag'], ['name' => 'Custom editorial tag']);
        $article->tags()->attach($existingTag);
        $migration = require database_path('migrations/2026_10_07_000042_fix_truenas_purple_category.php');

        foreach (['missing', 'gold'] as $state) {
            if ($state === 'gold') {
                $article->fresh()->category->update(['accent_color' => '#A16207']);
            }
            $migration->up();
            $migration->up();
            $repaired = $article->fresh(['category', 'tags']);
            $this->assertSame('qnap', $repaired->category->slug);
            $this->assertSame('#6f2da8', $repaired->accentColor());
            $this->assertSame('filter-qnap', $repaired->filterClass());
            $this->assertSame('article-page theme-qnap', $repaired->presentation['body_class']);
            $this->assertSame('عنوان ویرایش‌شده', $repaired->presentation['card_title_fa']);
            $this->assertSame('<p>Edited in the CMS.</p>', $repaired->content);
            $this->assertSame('draft', $repaired->status);
            $this->assertNull($repaired->published_at);
            $this->assertSame(9, $repaired->sort_order);
            $this->assertEqualsCanonicalizing(['custom-editorial-tag', 'qnap'], $repaired->tags->pluck('slug')->all());
            $this->assertStringContainsString('--topic: #6f2da8', view('components.article-card', ['article' => $repaired])->render());
        }
    }
}
