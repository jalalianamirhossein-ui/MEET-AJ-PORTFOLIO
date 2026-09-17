<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ArticleLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_articles_index_exposes_accessible_search_and_keeps_the_library_grid(): void
    {
        $this->get('/articles')
            ->assertOk()
            ->assertSee('role="search"', false)
            ->assertSee('id="article-q"', false)
            ->assertSee('isotope-container', false)
            ->assertSee('articles-load-more', false)
            ->assertSee('Linux', false)
            ->assertSee('data-fa="رفتن به محتوای اصلی"', false)
            ->assertSee('data-fa="نمایش مقالات بیشتر"', false)
            ->assertSee('article-chip-label', false);
    }

    public function test_article_search_filters_published_rows_without_loading_the_isotope_grid(): void
    {
        $draft = Article::create([
            'title' => 'HiddenDraftNeedleXYZ',
            'slug' => 'hidden-draft-needle-xyz',
            'language' => 'en',
            'content' => '<p>Draft only body that must stay private.</p>',
            'excerpt' => 'HiddenDraftNeedleXYZ',
            'status' => 'draft',
        ]);
        $this->assertNull($draft->published_at);

        $response = $this->get('/articles?q=linux');
        $response->assertOk()
            ->assertSee('article-result-list', false)
            ->assertSee('article-teaser', false)
            ->assertSee('article-teaser-media', false)
            ->assertDontSee('isotope-container', false)
            ->assertDontSee('HiddenDraftNeedleXYZ', false)
            ->assertSee('linux', false);

        $this->get('/articles/hidden-draft-needle-xyz')->assertNotFound();

        $published = Article::published()->search('linux')->first();
        $this->assertNotNull($published);
        $this->get('/articles?q=linux')
            ->assertSee($published->thumbnailUrl(), false)
            ->assertSee('Read article', false);
    }

    public function test_tag_filter_and_related_articles_use_published_tagged_peers(): void
    {
        $this->assertGreaterThan(0, Tag::query()->count());
        $linux = Tag::query()->where('slug', 'linux')->first();
        $this->assertNotNull($linux);

        $this->get('/articles?tag=linux')
            ->assertOk()
            ->assertSee('article-result-list', false)
            ->assertSee('aria-current="page"', false);

        $article = Article::published()->whereHas('tags', fn ($query) => $query->where('slug', 'linux'))->first();
        $this->assertNotNull($article);

        $draftPeer = Article::create([
            'title' => 'Unpublished Linux Peer',
            'slug' => 'unpublished-linux-peer',
            'language' => 'en',
            'content' => '<p>Should never be recommended.</p>',
            'status' => 'draft',
            'category_id' => $article->category_id,
        ]);
        $draftPeer->tags()->sync([$linux->id]);

        $html = $this->get($article->path())->assertOk()->getContent();
        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertStringContainsString('Related Articles', $html);
        $this->assertStringContainsString('article-related-grid', $html);
        $this->assertStringContainsString('article-share-btn', $html);
        $this->assertStringContainsString('article-hero-layout', $html);
        $this->assertStringContainsString('article-hero-media', $html);
        $this->assertStringContainsString('article-shell', $html);
        $this->assertStringContainsString('article-toc', $html);
        $this->assertStringNotContainsString('figure class="article-cover"', $html);
        $this->assertStringContainsString('linkedin.com/sharing', $html);
        $this->assertStringContainsString('twitter.com/intent/tweet', $html);
        $this->assertStringContainsString('wa.me/', $html);
        $this->assertStringContainsString('t.me/share', $html);
        $this->assertStringContainsString('data-copy-link', $html);
        $this->assertStringContainsString('min read', $html);
        $this->assertStringNotContainsString('unpublished-linux-peer', $html);
        $related = $article->relatedArticles(3);
        $this->assertLessThanOrEqual(3, $related->count());
        $this->assertFalse($related->contains(fn (Article $row) => $row->id === $article->id));
        $this->assertFalse($related->contains(fn (Article $row) => $row->id === $draftPeer->id));
        $related->each(function (Article $row): void {
            $this->assertSame('published', $row->status);
            $this->assertContains($row->language, ['en', 'fa']);
        });
    }

    public function test_duplicate_tags_are_rejected_and_editors_can_manage_tags(): void
    {
        $existing = Tag::query()->first();
        $this->expectException(ValidationException::class);
        Tag::create(['name' => $existing->name, 'slug' => 'duplicate-tag-name']);
    }

    public function test_tag_admin_is_available_to_content_editors(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor-tags@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();
        $this->assertTrue($editor->fresh()->can('viewAny', Tag::class));

        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\TagResource\Pages\ManageTags::class)
            ->assertOk();
    }
}
