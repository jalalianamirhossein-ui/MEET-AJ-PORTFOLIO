<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ArticleOrdering;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticleOrderingTest extends TestCase
{
    use RefreshDatabase;

    private function newArticle(string $slug, int $daysAgo = 0, string $language = 'en'): Article
    {
        return Article::create([
            'title' => 'New enterprise networking guide', 'slug' => $slug, 'language' => $language,
            'content' => '<p>Reviewed new article.</p>', 'status' => 'published',
            'published_at' => now()->subDays($daysAgo)->subMinute(),
        ]);
    }

    public function test_new_articles_are_accepted_sorted_by_recency_and_keep_dates_and_content(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $old = $this->newArticle('new-guide-older', 3);
        $new = $this->newArticle('new-guide-newer');
        $translated = $this->newArticle('new-guide-newer', 0, 'fa');
        $before = DB::table('articles')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $ordering = app(ArticleOrdering::class);
        $result = $ordering->synchronize();
        $this->assertCount(count(app(LegacyArticleImporter::class)->articleFiles()) + 3, $result);
        $ordered = Article::where('language', 'en')->inDisplayOrder()->pluck('slug')->all();
        $priorityCount = count(config('article-order.enterprise'));
        $this->assertSame([$new->slug, $old->slug], array_slice($ordered, 0, 2));
        $this->assertSame(config('article-order.enterprise'), array_slice($ordered, 2, $priorityCount));
        $this->assertSame(config('article-order.guides'), array_slice($ordered, $priorityCount + 2));
        $this->assertSame(0, $translated->fresh()->sort_order);

        $after = DB::table('articles')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        foreach ($before as $index => $row) {
            unset($row['sort_order'], $after[$index]['sort_order']);
            $this->assertSame($row, $after[$index]);
        }
        $this->assertSame($result, $ordering->synchronize());

        // A repeat import must not reject or reset CMS-only additions.
        app(LegacyArticleImporter::class)->import(false, true);
        $this->assertSame($ordered, Article::where('language', 'en')->inDisplayOrder()->pluck('slug')->all());
        foreach (['/', '/articles', '/articles?q=networking'] as $path) {
            $response = $this->get($path)->assertOk();
            $items = $response->viewData(str_contains($path, '?') ? 'results' : 'articles');
            $matching = collect($items->all())->filter(fn ($article) => $article->language === 'en' && str_starts_with($article->slug, 'new-guide-'))->pluck('slug')->values()->all();
            $this->assertSame([$new->slug, $old->slug], $matching, $path);
        }
    }

    public function test_missing_curated_articles_are_harmless_and_new_enterprise_articles_can_be_promoted(): void
    {
        // Content migrations may seed articles; this scenario needs an empty library.
        Article::query()->delete();
        $old = $this->newArticle('sparse-old', 4);
        $new = $this->newArticle('sparse-new');
        $ordering = app(ArticleOrdering::class);
        $this->assertCount(2, $ordering->synchronize());
        $this->assertSame([$new->slug, $old->slug], Article::inDisplayOrder()->pluck('slug')->all());
        config(['article-order.enterprise' => [$old->slug]]);
        $ordering->synchronize();
        $this->assertSame([$new->slug, $old->slug], Article::inDisplayOrder()->pluck('slug')->all());
        $this->assertSame(1, $old->fresh()->sort_order);
        $this->assertSame(0, $new->fresh()->sort_order);
    }
}
