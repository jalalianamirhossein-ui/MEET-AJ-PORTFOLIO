<?php

namespace Tests\Feature;

use App\Models\Category;
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
        $this->assertSame('--topic: '.Tag::BRAND_COLORS['fortinet'].';', $xp->evaluate('string(//button[@data-filter=".filter-fortinet"]/@style)'));
    }
}
