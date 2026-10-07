<?php

use App\Models\Article;
use App\Services\ArticleContentStandardizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Article::query()->chunkById(50, function ($articles): void {
            foreach ($articles as $article) {
                $content = (string) $article->content;
                $standardized = app(ArticleContentStandardizer::class)->standardize($article, $content);

                if ($standardized !== $content) {
                    $article->content = $standardized;
                    $article->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        // Content cleanup is intentionally not reversed after deployment.
    }
};
