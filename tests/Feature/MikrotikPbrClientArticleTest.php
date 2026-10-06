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
        app(LegacyArticleImporter::class)->import(false, false, ['mikrotik-pbr-client']);
        $article = Article::where('slug', 'mikrotik-pbr-client')->firstOrFail();
        $this->assertSame('mikrotik', $article->category->slug);
        $this->assertSame('fa', data_get($article->presentation, 'content_language'));
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
}
