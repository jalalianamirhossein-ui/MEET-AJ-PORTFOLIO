<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $category = Category::firstOrCreate(
            ['language' => 'en', 'slug' => 'cisco'],
            ['name' => 'Cisco', 'translation_key' => (string) Str::uuid()]
        );
        Category::firstOrCreate(
            ['language' => 'fa', 'slug' => 'cisco'],
            ['name' => 'سیسکو', 'translation_key' => $category->translation_key]
        );
        $article = Article::where('slug', 'cisco-catalyst-layer-2-layer-3-switch-hardening')->where('language', 'en')->first();
        if ($article) {
            $article->update([
                'category_id' => $category->id,
                'presentation' => array_replace($article->presentation ?? [], [
                    'category_label_en' => 'Cisco',
                    'category_label_fa' => 'سیسکو',
                    'filter_class' => 'filter-cisco',
                    'body_class' => 'article-page theme-cisco',
                ]),
            ]);
            app(\App\Services\ArticleTagAssigner::class)->syncArticle($article);
        }
    }

    public function down(): void
    {
        // Preserve editorial classification during rollback.
    }
};
