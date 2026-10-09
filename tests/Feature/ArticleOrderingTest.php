<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Models\Article;
use App\Models\User;
use App\Services\ArticleOrdering;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
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
        $initialCount = Article::count();
        $old = $this->newArticle('new-guide-older', 3);
        $new = $this->newArticle('new-guide-newer');
        $translated = $this->newArticle('new-guide-newer', 0, 'fa');
        $before = DB::table('articles')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        $ordering = app(ArticleOrdering::class);
        $result = $ordering->synchronize();
        $this->assertCount($initialCount + 3, $result);
        $ordered = Article::where('language', 'en')->inDisplayOrder()->pluck('slug')->all();
        $this->assertLessThan(array_search($old->slug, $ordered, true), array_search($new->slug, $ordered, true));
        $dates = Article::where('language', 'en')->inDisplayOrder()->get()->pluck('published_at')->map->timestamp->all();
        $expectedDates = $dates;
        rsort($expectedDates);
        $this->assertSame($expectedDates, $dates);
        $this->assertSame(0, $translated->fresh()->sort_order);

        $after = DB::table('articles')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        foreach ($before as $index => $row) {
            unset($row['sort_order'], $after[$index]['sort_order']);
            $this->assertSame($row, $after[$index]);
        }
        $this->assertSame($result, $ordering->synchronize());

        // A repeat import must not reject or reset CMS-only additions.
        app(LegacyArticleImporter::class)->import(false, true, array_merge(config('article-order.enterprise'), config('article-order.guides')));
        $this->assertSame($ordered, Article::where('language', 'en')->inDisplayOrder()->pluck('slug')->all());
        foreach (['/', '/articles', '/articles?q=Reviewed%20new%20article'] as $path) {
            $response = $this->get($path)->assertOk();
            $items = $response->viewData(str_contains($path, '?') ? 'results' : 'articles');
            $matching = collect($items->all())->filter(fn ($article) => $article->language === 'en' && str_starts_with($article->slug, 'new-guide-'))->pluck('slug')->values()->all();
            $this->assertSame([$new->slug, $old->slug], $matching, $path);
        }
    }

    public function test_historical_priority_lists_cannot_override_publication_time(): void
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

    public function test_same_day_publications_use_time_then_insertion_and_hide_drafts_and_future_articles(): void
    {
        Article::query()->delete();
        $this->travelTo(now()->startOfDay()->addHours(12));
        $older = $this->newArticle('older');
        $older->update(['published_at' => now()->startOfDay()->addHours(8), 'sort_order' => 0]);
        $sameTimeFirst = $this->newArticle('same-time-first');
        $sameTimeLast = $this->newArticle('same-time-last');
        foreach ([$sameTimeFirst, $sameTimeLast] as $article) {
            $article->update(['published_at' => now()->startOfDay()->addHours(9), 'sort_order' => 999]);
        }
        // Editing an older article must not promote it as a newly published article.
        $older->update(['content' => '<p>Editorial update</p>', 'updated_at' => now()]);
        $this->newArticle('draft')->update(['status' => 'draft']);
        $this->newArticle('future')->update(['published_at' => now()->addDay()]);
        $this->newArticle('unpublished')->update(['status' => 'draft', 'published_at' => null]);
        $expected = [$sameTimeLast->slug, $sameTimeFirst->slug, $older->slug];
        $this->assertSame($expected, Article::published()->inDisplayOrder()->pluck('slug')->all());
        foreach (['en', 'fa'] as $locale) {
            foreach (['/', '/articles', '/articles?q=networking'] as $path) {
                $response = $this->withUnencryptedCookie('lang', $locale)->get($path)->assertOk();
                $items = $response->viewData(str_contains($path, '?') ? 'results' : 'articles');
                $this->assertSame($expected, collect($items->all())->pluck('slug')->all(), $locale.' '.$path);
            }
        }
    }

    public function test_date_repair_is_guarded_idempotent_and_preserves_cms_changes(): void
    {
        $article = Article::where('slug', 'grafana-installation-zabbix-integration')->firstOrFail();
        $seo = $article->seo_data;
        data_set($seo, 'schema.datePublished', '2026-10-09T00:00:00+03:30');
        data_set($seo, 'schema.dateModified', '2026-10-09');
        $article->update(['published_at' => '2026-10-09 00:00:00', 'seo_data' => $seo,
            'content' => '<p>Protected CMS edit</p>', 'status' => 'draft']);
        $before = $article->getRawOriginal();
        $migration = require database_path('migrations/2026_10_10_000054_use_chronological_article_order.php');
        $migration->up();
        $article->refresh();
        $this->assertSame('2026-10-10', $article->published_at->toDateString());
        $this->assertSame('2026-10-10T00:00:00+03:30', data_get($article->seo_data, 'schema.datePublished'));
        $after = $article->getRawOriginal();
        unset($before['published_at'], $before['seo_data'], $before['sort_order'], $after['published_at'], $after['seo_data'], $after['sort_order']);
        $this->assertSame($before, $after);
        $snapshot = DB::table('articles')->orderBy('id')->get()->toJson();
        $migration->up();
        $this->assertSame($snapshot, DB::table('articles')->orderBy('id')->get()->toJson());

        // A later editorial date choice must survive even with the same import metadata.
        $article->update(['published_at' => '2026-10-08 15:00:00', 'seo_data' => $seo]);
        $manualBefore = $article->getRawOriginal();
        $migration->up();
        $manualAfter = $article->fresh()->getRawOriginal();
        unset($manualBefore['sort_order'], $manualAfter['sort_order']);
        $this->assertSame($manualBefore, $manualAfter);
    }

    public function test_admin_table_defaults_to_publication_order(): void
    {
        Article::query()->delete();
        $old = $this->newArticle('admin-old', 3);
        $new = $this->newArticle('admin-new');
        $old->update(['sort_order' => 0]);
        $new->update(['sort_order' => 99]);
        $admin = User::create(['name' => 'Ordering admin', 'email' => 'ordering-admin@example.test',
            'password' => 'test-password', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(ListArticles::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$new, $old], inOrder: true);
    }
}
