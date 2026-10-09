<?php

use App\Models\Article;
use App\Services\ArticleContentStandardizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $localizations = json_decode(file_get_contents(resource_path('content/article-localizations.json')), true, flags: JSON_THROW_ON_ERROR);
        // Include drafts and CMS-only records without reimporting or republishing them.
        Article::query()->chunkById(50, function ($articles) use ($localizations): void {
            foreach ($articles as $article) {
                $content = app(ArticleContentStandardizer::class)->standardize($article, (string) $article->content, false);
                $changes = ['content' => $content];
                if (! data_get($article->presentation, 'localizations') && isset($localizations[$article->slug])) {
                    $changes['presentation'] = array_replace($article->presentation ?? [], ['localizations' => $localizations[$article->slug]]);
                }
                if ($content !== $article->content || isset($changes['presentation'])) {
                    // Preserve publication/update timestamps and all SEO/editorial metadata.
                    Article::withoutTimestamps(fn () => $article->forceFill($changes)->saveQuietly());
                }
            }
        });
    }

    public function down(): void
    {
        // Do not restore removed labels or overwrite later editorial changes.
    }
};
