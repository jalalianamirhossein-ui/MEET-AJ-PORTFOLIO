<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PingTriggeredPolicyRoutingArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_router_guides_follow_publication_time_on_home_library_search_and_tag(): void
    {
        app(LegacyArticleImporter::class)->import(false);
        Article::whereNotIn('slug', ['mikrotik-pbr-client', 'mikrotik-ping-triggered-policy-routing', 'mikrotik-openvpn-setup-v7'])->update(['status' => 'draft']);
        $pbr = Article::where('slug', 'mikrotik-ping-triggered-policy-routing')->firstOrFail();
        $pbr->update(['published_at' => now()->subYears(2)]);
        Article::where('slug', 'mikrotik-pbr-client')->update(['published_at' => now()->subDay()]);
        Article::where('slug', 'mikrotik-openvpn-setup-v7')->update(['published_at' => now()->subHour()]);

        foreach (['/', '/articles', '/articles?q=mikrotik', '/articles?tag=mikrotik'] as $path) {
            $response = $this->get($path)->assertOk();
            $items = $response->viewData(str_contains($path, '?') ? 'results' : 'articles');
            $this->assertSame(['mikrotik-openvpn-setup-v7', 'mikrotik-pbr-client', $pbr->slug], collect($items->all())->pluck('slug')->all(), $path);
        }

        $this->assertSame('mikrotik', $pbr->category->slug);
        $this->assertTrue($pbr->tags()->where('slug', 'mikrotik')->exists());
        $this->get('/articles?q=nginx')->assertOk()->assertDontSee($pbr->title);
    }
}
