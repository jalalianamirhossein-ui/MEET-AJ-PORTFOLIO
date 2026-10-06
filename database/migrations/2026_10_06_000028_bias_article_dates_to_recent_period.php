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
        $today = CarbonImmutable::today();
        $recentStart = $today->subMonths(9);
        $oldStart = CarbonImmutable::create(2025, 1, 1, 0, 0, 0, config('app.timezone'));
        $articles = app(ArticleOrdering::class)->apply(
            Article::query()->where('language', 'en')->where('status', 'published')
        )->get(['id']);
        $total = $articles->count();

        foreach ($articles->values() as $index => $article) {
            if ($index === 0) {
                $published = $today->setTime(10, 30, 0);
            } elseif ($index < (int) ceil($total * 0.8)) {
                $days = max(1, $recentStart->diffInDays($today));
                $offset = (($index * 7919) % $days);
                $minutes = (($index * 1237) % 720) + 8 * 60;
                $published = $today->subDays($offset)->setTime(intdiv($minutes, 60), $minutes % 60, ($index * 37) % 60);
            } else {
                $days = max(1, $oldStart->diffInDays($recentStart));
                $offset = (($index * 3571) % $days);
                $minutes = (($index * 593) % 720) + 8 * 60;
                $published = $recentStart->subDays($offset)->setTime(intdiv($minutes, 60), $minutes % 60, ($index * 29) % 60);
            }
            DB::table('articles')->where('id', $article->id)->update(['published_at' => $published]);
        }
    }

    public function down(): void
    {
        // Publication dates are editorial data and are intentionally preserved.
    }
};
