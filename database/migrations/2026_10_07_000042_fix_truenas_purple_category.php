<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $articles = Article::where('slug', 'truenas-zfs-enterprise')->get();
            if ($articles->isEmpty()) { return; }

            $tag = Tag::firstOrCreate(['slug' => 'qnap'], ['name' => 'QNAP']);
            foreach ($articles as $article) {
                // Older installs may never have imported the QNAP category.
                // Repair taxonomy and theme without replacing authored content.
                $category = Category::firstOrCreate(
                    ['slug' => 'qnap', 'language' => $article->language],
                    ['name' => 'QNAP', 'accent_color' => Tag::BRAND_COLORS['qnap']]
                );
                $category->update(['accent_color' => Tag::BRAND_COLORS['qnap']]);
                $presentation = $article->presentation ?? [];
                $presentation['filter_class'] = 'filter-qnap';
                $presentation['body_class'] = 'article-page theme-qnap';
                $article->category_id = $category->id;
                $article->presentation = $presentation;
                $article->saveQuietly();
                $article->tags()->syncWithoutDetaching([$tag->id]);
            }
        });
    }

    public function down(): void
    {
        // Preserve the corrected category and subsequent editorial changes.
    }
};
