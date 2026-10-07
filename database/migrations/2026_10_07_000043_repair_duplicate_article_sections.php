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
                $content = app(ArticleContentStandardizer::class)->standardize($article, (string) $article->content);
                $presentation = $article->presentation ?? [];
                if (isset($presentation['toc_html'])) {
                    preg_match_all('/\bid=["\']([^"\']+)["\']/', $content, $ids);
                    $presentation['toc_html'] = preg_replace_callback('~<li\b[^>]*>.*?</li>~is', function (array $item) use ($ids): string {
                        if (preg_match('/href=["\']#([^"\']+)["\']/', $item[0], $target)
                            && ! in_array($target[1], $ids[1], true)) {
                            return '';
                        }
                        return $item[0];
                    }, $presentation['toc_html']) ?? $presentation['toc_html'];
                }
                if ($content !== $article->content || $presentation !== $article->presentation) {
                    $article->forceFill(['content' => $content, 'presentation' => $presentation])->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        // Keep authored sections and subsequent CMS edits intact on rollback.
    }
};
