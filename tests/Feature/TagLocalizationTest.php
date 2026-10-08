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

    public function test_filters_and_tags_use_saved_persian_category_names_in_one_batch(): void
    {
        Category::where('slug', 'cisco')->where('language', 'fa')->update(['name' => 'نام فارسی ویرایش‌شده']);
        DB::enableQueryLog();
        $tags = Tag::whereIn('slug', ['cisco', 'microsoft', 'mikrotik', 'linux', 'vmware', 'other'])->get();
        $queryCount = count(DB::getQueryLog());
        foreach ($tags as $tag) {
            $slug = $tag->slug === 'other' ? 'others' : $tag->slug;
            $expected = $tag->categoryTranslations->firstWhere('language', 'fa')->name;
            $this->assertSame($expected, $tag->displayName('fa'), $slug);
        }
        $this->assertSame($queryCount, count(DB::getQueryLog()), 'Displaying labels must not query once per tag.');
        $this->assertSame(2, $queryCount);
        DB::disableQueryLog();

        $dom = $this->dom(view('articles.partials.category-filters')->render());
        foreach ($tags as $tag) {
            $this->assertSame($tag->displayName('fa'), $dom->evaluate('string(//button[@data-filter=".filter-'.$tag->slug.'"]/span/@data-fa)'));
            $this->assertSame($tag->displayName('en'), $dom->evaluate('string(//button[@data-filter=".filter-'.$tag->slug.'"]/span/@data-en)'));
        }
    }

    public function test_translated_labels_reach_home_library_search_cards_and_article_pages(): void
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

        foreach (['/', '/articles', '/articles?tag=cisco', $article->path()] as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $dom = $this->dom($html);
            $labels = $dom->query('//a[contains(@href,"tag=cisco")]/@data-fa | //a[contains(@href,"tag=cisco")]/span/@data-fa');
            $this->assertGreaterThan(0, $labels->length, $path);
            foreach ($labels as $label) {
                $this->assertSame('برچسب فارسی سفارشی', $label->nodeValue, $path);
            }
        }
    }

    public function test_missing_translations_keep_technical_names_and_storage_fallback(): void
    {
        Category::where('slug', 'qnap')->where('language', 'fa')->delete();
        $this->assertSame('استوریج', Tag::where('slug', 'qnap')->firstOrFail()->displayName('fa'));
        $tag = Tag::create(['slug' => 'custom-tool', 'name' => 'Custom Tool']);
        $this->assertSame('Custom Tool', $tag->displayName('fa'));
        Category::where('slug', 'mikrotik')->where('language', 'fa')->delete();
        $this->assertSame('MikroTik', Tag::where('slug', 'mikrotik')->firstOrFail()->displayName('fa'));
    }

    private function dom(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);

        return new \DOMXPath($dom);
    }
}
