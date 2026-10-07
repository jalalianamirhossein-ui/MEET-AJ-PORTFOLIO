<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $slug = 'fortigate-sd-wan-load-balancing-failover';
        $result = app(LegacyArticleImporter::class)->import(false, false, [$slug]);

        // A rerun must preserve subsequent CMS classification and publication edits.
        if ($result['imported'] === 0) {
            return;
        }

        $category = Category::firstOrCreate(
            ['language' => 'en', 'slug' => 'fortinet'],
            ['name' => 'Fortinet', 'translation_key' => (string) Str::uuid(), 'accent_color' => Tag::BRAND_COLORS['fortinet']]
        );
        Category::firstOrCreate(
            ['language' => 'fa', 'slug' => 'fortinet'],
            ['name' => 'فورتی‌نت', 'translation_key' => $category->translation_key, 'accent_color' => Tag::BRAND_COLORS['fortinet']]
        );

        $article = Article::where('language', 'en')->where('slug', $slug)->firstOrFail();
        $article->update([
            'category_id' => $category->id,
            'presentation' => array_replace($article->presentation ?? [], [
                'category_label_en' => 'Fortinet',
                'category_label_fa' => 'فورتی‌نت',
                'filter_class' => 'filter-fortinet',
            ]),
        ]);
    }

    public function down(): void
    {
        // Preserve the article, redirects and editorial changes on rollback.
    }
};
