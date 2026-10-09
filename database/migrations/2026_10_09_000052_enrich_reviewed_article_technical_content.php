<?php

use App\Models\Article;
use App\Services\ArticleTechnicalContent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Article::query()->chunkById(50, function ($articles): void {
            foreach ($articles as $article) {
                $content = app(ArticleTechnicalContent::class)->enrich((string) $article->content, $article->slug);
                if ($content !== $article->content) {
                    Article::withoutTimestamps(fn () => $article->forceFill(['content' => $content])->saveQuietly());
                }
            }
        });
    }

    public function down(): void
    {
        // Removing reviewed commands would overwrite subsequent editorial work.
    }
};
