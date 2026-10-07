<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Tag::where('slug', 'qnap')->update(['name' => 'Storage']);
        Category::where('slug', 'qnap')->where('language', 'en')->update(['name' => 'Storage']);
        Category::where('slug', 'qnap')->where('language', 'fa')->update(['name' => 'استوریج']);
        Article::with(['category', 'tags'])->chunkById(50, function ($articles): void {
            foreach ($articles as $article) {
                if ($article->primaryFilterSlug() !== 'qnap') { continue; }
                $article->presentation = array_replace($article->presentation ?? [], [
                    'category_label_en' => 'Storage', 'category_label_fa' => 'استوریج',
                ]);
                $article->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Preserve display labels and subsequent editorial changes on rollback.
    }
};
