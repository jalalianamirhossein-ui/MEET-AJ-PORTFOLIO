<?php

use App\Models\Article;
use App\Services\ArticleStructure;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Repair the stored content in every publication state, including CMS-only rows.
        // Do not import source files over editorial changes or alter metadata/timestamps.
        Article::query()->chunkById(50, function ($articles): void {
            foreach ($articles as $article) {
                $content = app(ArticleStructure::class)->repair((string) $article->content, $article->slug);
                if ($content !== $article->content) {
                    Article::withoutTimestamps(fn () => $article->forceFill(['content' => $content])->saveQuietly());
                }
            }
        });
    }

    public function down(): void
    {
        // Reintroducing obsolete references would overwrite later editorial work.
    }
};
