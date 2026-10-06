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
        $start = CarbonImmutable::create(2025, 1, 1, 0, 0, 0, config('app.timezone'));
        $articles = app(ArticleOrdering::class)->apply(
            Article::query()->where('language', 'en')->where('status', 'published')
        )->get(['id']);

        if ($articles->isEmpty()) {
            return;
        }

        $range = max(1, $start->diffInDays($today));
        $used = [];
        foreach ($articles->values() as $index => $article) {
            if ($index === 0) {
                $published = $today->setTime(10, 30, 0);
            } else {
                // Deterministic pseudo-random dates keep deployments repeatable.
                $dayOffset = (($index * 7919) % $range);
                $minutes = (($index * 1237) % 720) + 8 * 60;
                $published = $today->subDays($dayOffset)->setTime(intdiv($minutes, 60), $minutes % 60, ($index * 37) % 60);
                while (isset($used[$published->format('Y-m-d H:i:s')]) || $published->lt($start)) {
                    $published = $published->subMinutes(17);
                }
            }
            $used[$published->format('Y-m-d H:i:s')] = true;
            DB::table('articles')->where('id', $article->id)->update(['published_at' => $published]);
        }
    }

    public function down(): void
    {
        // Publication dates are editorial data and are intentionally preserved.
    }
};
