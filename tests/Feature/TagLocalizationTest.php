<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TagLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_filters_are_localized_and_tags_stay_english_without_extra_queries(): void
    {
        Category::where('slug', 'cisco')->where('language', 'fa')->update(['name' => 'نام فارسی ویرایش‌شده']);
        Category::where('slug', 'cisco')->where('language', 'en')->update(['name' => 'Edited English category']);
        DB::enableQueryLog();
        $tags = Tag::whereIn('slug', ['cisco', 'microsoft', 'mikrotik', 'linux', 'vmware', 'other'])->get();
        $queryCount = count(DB::getQueryLog());
        foreach ($tags as $tag) {
            $this->assertSame($tag->displayName('en'), $tag->displayName('fa'), $tag->slug);
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $tag->displayName('fa'));
        }
        $this->assertSame($queryCount, count(DB::getQueryLog()), 'Displaying labels must not query once per tag.');
        $this->assertSame(2, $queryCount);
        DB::disableQueryLog();

        $dom = $this->dom(view('articles.partials.category-filters')->render());
        foreach ($tags as $tag) {
            foreach (['en', 'fa'] as $locale) {
                $expected = $tag->categoryTranslations->firstWhere('language', $locale)->name;
                $this->assertSame($expected, $dom->evaluate('string(//button[@data-filter=".filter-'.$tag->slug.'"]/span/@data-'.$locale.')'));
            }
        }
    }

    public function test_english_tags_and_localized_categories_reach_home_library_search_and_article_pages(): void
    {
        $tag = Tag::where('slug', 'cisco')->firstOrFail();
        $category = Category::where('slug', 'cisco')->where('language', 'en')->firstOrFail();
        Category::where('slug', 'cisco')->where('language', 'fa')->update(['name' => 'برچسب فارسی سفارشی']);
        $article = Article::create([
            'title' => 'Localized tag example', 'slug' => 'localized-tag-example',
            'content' => '<p>Example article.</p>', 'category_id' => $category->id,
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $article->tags()->attach($tag);

        foreach (['en', 'fa'] as $locale) {
            foreach (['/', '/articles', '/articles?tag=cisco', $article->path()] as $path) {
                $html = $this->withCookie('lang', $locale)->get($path)->assertOk()->getContent();
                $dom = $this->dom($html);
                $labels = $dom->query('//a[contains(@href,"tag=cisco")]/@data-fa | //a[contains(@href,"tag=cisco")]/span/@data-fa | //a[contains(@href,"tag=cisco")]/@data-en | //a[contains(@href,"tag=cisco")]/span/@data-en');
                $this->assertGreaterThan(0, $labels->length, $path);
                foreach ($labels as $label) {
                    $this->assertSame('Cisco', $label->nodeValue, $path);
                }
                $categories = $dom->query('//*[contains(concat(" ", normalize-space(@class), " "), " article-teaser-category ") or contains(concat(" ", normalize-space(@class), " "), " article-category ")]');
                $ciscoCategories = 0;
                foreach ($categories as $categoryLabel) {
                    if ($categoryLabel->getAttribute('data-en') === 'Cisco') {
                        $this->assertSame('سیسکو', $categoryLabel->getAttribute('data-fa'), $path);
                        $ciscoCategories++;
                    }
                }
                $this->assertGreaterThan(0, $ciscoCategories, $path);
                if ($path === '/articles?tag=cisco') {
                    $this->assertSame('مقالات با برچسب Cisco', $dom->evaluate('string(//*[contains(@class,"article-search-summary")]/span/@data-fa)'));
                }
            }
        }
    }

    public function test_missing_category_translations_keep_persian_filters_and_english_tags(): void
    {
        Category::where('language', 'fa')->delete();
        $this->assertSame('Storage', Tag::where('slug', 'qnap')->firstOrFail()->displayName('fa'));
        $tag = Tag::create(['slug' => 'custom-tool', 'name' => 'Custom Tool']);
        $this->assertSame('Custom Tool', $tag->displayName('fa'));
        $this->assertSame('MikroTik', Tag::where('slug', 'mikrotik')->firstOrFail()->displayName('fa'));
        $dom = $this->dom(view('articles.partials.category-filters')->render());
        foreach (Tag::BRAND_FILTERS as $slug) {
            $labelFa = $dom->evaluate('string(//button[@data-filter=".filter-'.$slug.'"]/span/@data-fa)');
            $labelEn = $dom->evaluate('string(//button[@data-filter=".filter-'.$slug.'"]/span/@data-en)');
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $labelFa, $slug);
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $labelEn, $slug);
        }
    }

    private function dom(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);

        return new \DOMXPath($dom);
    }
}
