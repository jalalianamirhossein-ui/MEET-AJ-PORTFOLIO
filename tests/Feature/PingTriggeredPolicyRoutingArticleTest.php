<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PingTriggeredPolicyRoutingArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_router_guide_follows_client_on_home_library_and_matching_search_regardless_of_date(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        $pbr = Article::where('slug', 'mikrotik-ping-triggered-policy-routing')->firstOrFail();
        $pbr->update(['published_at' => now()->subYears(2)]);
        Article::where('slug', 'linux-security-auditor-bash')->update(['published_at' => now()->subDay()]);
        Article::where('slug', 'mikrotik-openvpn-setup-v7')->update(['published_at' => now()->subHour()]);

        foreach (['/', '/articles', '/articles?q=mikrotik', '/articles?tag=mikrotik'] as $path) {
            $response = $this->get($path)->assertOk();
            $items = $response->viewData(str_contains($path, '?') ? 'results' : 'articles');
            $this->assertSame('mikrotik-pbr-client', $items->first()->slug, $path);
            $this->assertSame($pbr->slug, $items->values()->get(1)->slug, $path);
        }

        $this->assertSame('mikrotik', $pbr->category->slug);
        $this->assertTrue($pbr->tags()->where('slug', 'mikrotik')->exists());
        $this->get('/articles?q=nginx')->assertOk()->assertDontSee($pbr->title);
    }
}
