<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_detects_stale_imports_even_when_markup_counts_match(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $article = Article::published()->firstOrFail();
        $presentation = $article->presentation;
        $presentation['source_hash'] = str_repeat('0', 64);
        unset($presentation['localizations']);
        $article->update(['presentation' => $presentation]);

        $this->artisan('site:compare-content')
            ->expectsOutputToContain('source changed; run articles:import-legacy --update-existing after reviewing CMS edits; missing fa localization; missing en localization')
            ->expectsOutput('Failures: 1')
            ->assertFailed();
    }

    public function test_comparison_handles_custom_cms_articles_and_ignores_drafts(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        Article::create([
            'title' => 'A new CMS article', 'slug' => 'new-cms-article',
            'content' => '<p>New editorial content.</p>',
            'status' => 'published', 'published_at' => now()->subMinute(),
        ]);
        $draft = Article::published()->firstOrFail();
        $draft->update(['status' => 'draft', 'content' => '<p>Work in progress.</p>']);

        $this->artisan('site:compare-content')
            ->expectsOutputToContain('CMS article without a legacy source')
            ->expectsOutput('Failures: 0')
            ->assertSuccessful();
    }
}
