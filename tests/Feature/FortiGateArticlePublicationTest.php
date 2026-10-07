<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FortiGateArticlePublicationTest extends TestCase
{
    use RefreshDatabase;

    private const SLUG = 'fortigate-sd-wan-load-balancing-failover';

    public function test_migration_makes_article_discoverable_with_supplied_and_generated_images(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $this->assertSame('published', $article->status);
        $this->assertTrue($article->published_at->lessThanOrEqualTo(now()));
        $this->assertSame('fortinet', $article->category->slug);
        $this->assertTrue($article->tags()->where('slug', 'fortinet')->exists());
        $this->get('/articles?q=FortiGate')->assertOk()->assertSee($article->path());
        $this->withUnencryptedCookie('lang', 'fa')->get($article->path())
            ->assertOk()->assertSee('Performance SLA')
            ->assertSee('fortigate-generated-dual-wan-topology.png', false)
            ->assertSee('Dual%20WAN%20Network%20Topology.png', false);
        $this->get($article->path().'.html')->assertRedirect($article->path());

        $source = file_get_contents(resource_path('legacy/articles/'.self::SLUG.'.html'));
        preg_match_all('/<img\b[^>]*src="([^"]+)"/s', $source, $images);
        $this->assertCount(10, $images[1]);
        foreach ($images[1] as $url) {
            $this->assertFileExists(public_path(ltrim(rawurldecode($url), '/')));
        }
    }

    public function test_migration_rerun_preserves_editorial_content_and_draft_status(): void
    {
        $article = Article::where('slug', self::SLUG)->firstOrFail();
        $article->update(['content' => '<p>Edited in CMS</p>', 'status' => 'draft']);
        $migration = require database_path('migrations/2026_10_07_000037_add_fortigate_sdwan_article.php');
        $migration->up();
        $this->assertSame('<p>Edited in CMS</p>', $article->fresh()->content);
        $this->assertSame('draft', $article->fresh()->status);
        $this->assertSame(1, Article::where('slug', self::SLUG)->count());
    }
}
