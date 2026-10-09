<?php

use App\Models\Article;
use App\Services\ArticleOrdering;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            foreach (Article::where('slug', 'grafana-installation-zabbix-integration')->lockForUpdate()->get() as $article) {
                // Repair only the original imported review-date/publication-date mix-up.
                // Preserve dates chosen by editors and all unrelated content and timestamps.
                if ($article->getRawOriginal('published_at') !== '2026-10-09 00:00:00'
                    || data_get($article->presentation, 'source_file') !== 'articles/grafana-installation-zabbix-integration.html'
                    || data_get($article->seo_data, 'date_provenance') !== 'schema:datePublished'
                    || data_get($article->seo_data, 'schema.datePublished') !== '2026-10-09T00:00:00+03:30') {
                    continue;
                }

                $seo = $article->seo_data;
                data_set($seo, 'schema.datePublished', '2026-10-10T00:00:00+03:30');
                if (data_get($seo, 'schema.dateModified') === '2026-10-09') {
                    data_set($seo, 'schema.dateModified', '2026-10-10');
                }
                DB::table('articles')->where('id', $article->id)->update([
                    'published_at' => '2026-10-10 00:00:00',
                    'seo_data' => json_encode($seo, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            app(ArticleOrdering::class)->synchronize();
        });
    }

    public function down(): void
    {
        // Do not reinstate a known erroneous date or undo editorial publication dates.
    }
};
