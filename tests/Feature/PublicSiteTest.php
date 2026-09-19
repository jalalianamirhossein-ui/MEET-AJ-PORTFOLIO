<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_homepage_and_index_redirect(): void
    {
        $this->get('/')->assertOk()->assertSee('Meet AJ', false)->assertSee('id="portfolio"', false)->assertSee('id="service-catalog"', false)->assertSee('testimonials-slider', false)->assertSee('mailto:jalalian.amirhossein@gmail.com', false)->assertSee('data-fa=', false)->assertSee('csrf_token', false)->assertDontSee('@@gmail', false);
        $this->get('/index.html')->assertRedirect('/');
        $this->assertSame(301, $this->get('/index.html')->baseResponse->getStatusCode());
    }

    public function test_homepage_renders_each_primary_section_once(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['hero', 'about', 'skills', 'resume', 'services', 'portfolio', 'testimonials', 'contact'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'), $id.' should appear once');
        }
        $this->assertSame(1, substr_count($html, 'id="articles-load-more"'));
    }

    public function test_service_pages_redirect_once_and_render(): void
    {
        foreach ([
            'cctv-surveillance',
            'devops-automation',
            'hp-enterprise-server',
            'jira-implementation',
            'mikrotik-routing-multi-wan',
            'monitoring-security',
            'network-design',
            'network-security',
            'sql-server-high-availability',
            'system-administration',
            'technical-consulting',
            'virtualization-solutions',
            'voip-infrastructure',
        ] as $slug) {
            $legacy = $this->get('/services/'.$slug.'.html');
            $legacy->assertRedirect('/services/'.$slug);
            $this->assertSame(301, $legacy->baseResponse->getStatusCode());
            $this->get('/services/'.$slug)
                ->assertOk()
                ->assertSee('csrf_token', false)
                ->assertSee('rel="canonical"', false)
                ->assertSee('application/ld+json', false)
                ->assertSee('name="service"', false);
        }
    }

    public function test_all_articles_redirect_once_and_render(): void
    {
        $this->assertSame(24, Article::query()->count());
        foreach (Article::query()->orderBy('slug')->get() as $article) {
            $legacy = $this->get('/articles/'.$article->slug.'.html');
            $legacy->assertRedirect('/articles/'.$article->slug);
            $this->assertSame(301, $legacy->baseResponse->getStatusCode());
            $this->get('/articles/'.$article->slug)
                ->assertOk()
                ->assertSee($article->slug, false)
                ->assertSee($article->thumbnailUrl(), false)
                ->assertSee('article-hero-thumbnail', false)
                ->assertSee('rel="canonical"', false)
                ->assertSee('application/ld+json', false)
                ->assertSee('data-fa=', false)
                ->assertSee('id="article-content"', false);
        }
    }

    public function test_unknown_article_is_404_and_query_string_survives_redirect(): void
    {
        $this->get('/articles/does-not-exist')->assertNotFound();
        $first = Article::query()->first();
        $this->get('/articles/'.$first->slug.'.html?ref=nav')->assertRedirect('/articles/'.$first->slug.'?ref=nav');
    }

    public function test_sitemap_and_robots_exclude_legacy_and_admin(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8');
        $sitemap->assertDontSee('/index.html', false);
        $sitemap->assertDontSee('/articles/creating-a-bootable-usb.html', false);
        $sitemap->assertDontSee('/services/network-design.html', false);
        $sitemap->assertDontSee('/admin', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin', false)->assertSee('Sitemap:', false);
        $this->get('/manifest.json')->assertOk();
    }

    public function test_contact_token_and_persistence(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->assertOk()->json('token');
        $this->assertNotEmpty($token);
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Test User',
            'email' => 'tester@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ], ['HTTP_ACCEPT' => 'text/plain'])->assertOk()->assertSee('OK', false);
        $this->assertDatabaseHas('requests', ['email' => 'tester@example.com', 'subject' => 'Need help with network']);
    }

    public function test_contact_honeypot_and_validation(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
            'website' => 'https://spam.test',
        ])->assertOk()->assertSee('OK', false);
        $this->assertDatabaseMissing('requests', ['email' => 'bot@example.com']);

        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'A',
            'email' => 'bad',
            'subject' => 'x',
            'message' => 'short',
        ])->assertStatus(400);
    }

    public function test_admin_login_page_is_public_and_panel_is_protected(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect();
        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password12chars']);
        $user->forceFill(['role' => 'admin'])->save();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_german_routes_are_not_public(): void
    {
        $this->get('/de')->assertNotFound();
        $this->get('/de/articles/creating-a-bootable-usb')->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('hreflang="de"', false);
    }
}
