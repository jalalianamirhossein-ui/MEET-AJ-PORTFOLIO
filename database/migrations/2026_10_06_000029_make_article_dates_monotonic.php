<?php

use App\Models\Article;
use App\Services\ArticleOrdering;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $published = app(ArticleOrdering::class)->apply(
            Article::query()->where('language', 'en')->where('status', 'published')
        )->get(['id']);
        $date = CarbonImmutable::today()->setTime(10, 30, 0);

        foreach ($published->values() as $index => $article) {
            if ($index > 0) {
                // Natural editorial spacing: 7 to 23 days between articles.
                $gap = 7 + (($index * 11) % 17);
                $date = $date->subDays($gap)->setTime(8 + (($index * 3) % 9), ($index * 17) % 60, ($index * 31) % 60);
            }
            DB::table('articles')->where('id', $article->id)->update(['published_at' => $date]);
        }
    }

    public function down(): void
    {
        // Publication dates are editorial data and are intentionally preserved.
    }
};
