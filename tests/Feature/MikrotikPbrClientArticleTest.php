<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MikrotikPbrClientArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_article_is_discoverable_and_offers_only_the_installer(): void
    {
        // Normal migrations must install the article without a manual import.
        $article = Article::where('slug', 'mikrotik-pbr-client')->firstOrFail();
        $this->assertSame('mikrotik', $article->category->slug);
        $this->assertSame('fa', data_get($article->presentation, 'content_language'));
        $this->assertSame('mikrotik-pbr-client', Article::published()->inDisplayOrder()->first()->slug);
        $this->assertSame(1, substr_count($article->content, 'href="/downloads/mikrotik-pbr-client/'));
        $this->assertGreaterThan(strpos($article->content, 'id="faq"'), strpos($article->content, 'href="/downloads/mikrotik-pbr-client/'));
        $this->get('/')->assertOk()->assertSee('/articles/mikrotik-pbr-client', false);
        $this->get('/articles/mikrotik-pbr-client')->assertOk()
            ->assertSee('EnableTriggerIp')->assertSee('Send disconnect')
            ->assertSee('/downloads/mikrotik-pbr-client/MikroTikPBRClient-Setup-1.0.2-x64.msi', false)
            ->assertDontSee('MikroTikPBRClient-Source.zip')
            ->assertDontSee('دانلود سورس')->assertDontSee('MikroTikPBRClient.sln');
        $this->get('/articles?q=MikroTik')->assertOk()->assertSee('/articles/mikrotik-pbr-client', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/articles/mikrotik-pbr-client', false);
        $this->assertSame(
            hash_file('sha256', resource_path('content/articles/mikrotik-pbr-client/MikroTikPBRClient-Setup-1.0.2-x64.msi')),
            hash_file('sha256', public_path('downloads/mikrotik-pbr-client/MikroTikPBRClient-Setup-1.0.2-x64.msi')),
        );
        $this->assertFileDoesNotExist(public_path('downloads/mikrotik-pbr-client/MikroTikPBRClient-Source.zip'));
    }

    public function test_installation_preserves_existing_editorial_changes(): void
    {
        $article = Article::where('slug', 'mikrotik-pbr-client')->firstOrFail();
        $article->update(['content' => '<p>Edited in CMS</p>']);
        $migration = require database_path('migrations/2026_10_06_000021_add_mikrotik_pbr_client_article.php');
        $migration->up();
        $this->assertSame('<p>Edited in CMS</p>', $article->fresh()->content);
        $this->assertSame(1, Article::where('slug', 'mikrotik-pbr-client')->count());
    }
}
