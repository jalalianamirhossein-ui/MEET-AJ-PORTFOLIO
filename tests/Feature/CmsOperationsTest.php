<?php

namespace Tests\Feature;

use App\Mail\ContactReceivedMail;
use App\Models\Article;
use App\Models\Request as ContactRequest;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CmsOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_contact_is_kept_when_mail_transport_fails(): void
    {
        config([
            'cms.contact_email' => 'notify@example.test',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 9,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Mail Failure User',
            'email' => 'mailfail@example.com',
            'phone' => '+989000000000',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ], ['HTTP_ACCEPT' => 'text/plain'])->assertOk()->assertSee('OK', false);

        $this->assertDatabaseHas('requests', [
            'email' => 'mailfail@example.com',
            'phone' => '+989000000000',
            'subject' => 'Need help with network',
        ]);
    }

    public function test_contact_notification_is_attempted_after_persist(): void
    {
        Mail::fake();
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Notify User',
            'email' => 'notify-user@example.com',
            'phone' => '09120000000',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ])->assertOk()->assertSee('OK', false);

        Mail::assertSent(ContactReceivedMail::class);
        $this->assertSame('09120000000', ContactRequest::query()->where('email', 'notify-user@example.com')->value('phone'));
    }

    public function test_contact_rate_limit_returns_429(): void
    {
        $payload = [
            'name' => 'Rate User',
            'email' => 'rate@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ];
        for ($i = 0; $i < 5; $i++) {
            $token = $this->get('/forms/get-csrf-token.php')->json('token');
            $this->post('/forms/contact.php', $payload + ['csrf_token' => $token, 'email' => 'rate'.$i.'@example.com'])->assertOk();
        }
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', $payload + ['csrf_token' => $token, 'email' => 'rate-last@example.com'])->assertStatus(429);
    }

    public function test_editor_cannot_open_contact_requests(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-crud@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->assertSame('editor', $editor->fresh()->role);
        $this->assertFalse($editor->fresh()->can('viewAny', ContactRequest::class));
        $this->assertTrue($admin->fresh()->can('viewAny', ContactRequest::class));
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->assertNotSame(200, $this->actingAs($editor)->get('/admin/requests')->getStatusCode());
        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ArticleResource\Pages\ListArticles::class)
            ->assertOk();
        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\RequestResource\Pages\ManageRequests::class)
            ->assertForbidden();
    }

    public function test_article_crud_publish_draft_and_delete(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-articles@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);

        $draft = Article::create([
            'title' => 'Draft QA article',
            'slug' => 'draft-qa-article',
            'language' => 'en',
            'content' => '<p>Draft body</p>',
            'status' => 'draft',
        ]);
        $this->get('/articles/draft-qa-article')->assertNotFound();
        $this->get('/admin/articles/'.$draft->id.'/edit')->assertOk();

        $draft->update([
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'meta_title' => 'Draft QA article',
            'meta_description' => 'Published from draft for QA.',
        ]);
        $this->get('/articles/draft-qa-article')->assertOk()->assertSee('Draft QA article', false);

        $draft->delete();
        $this->get('/articles/draft-qa-article')->assertNotFound();
    }

    public function test_new_article_form_generates_slug_and_seo_defaults(): void
    {
        $admin = User::create(['name' => 'Article Creator', 'email' => 'article-creator@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ArticleResource\Pages\CreateArticle::class)
            ->fillForm([
                'title' => 'Quick CMS Article',
                'language' => 'en',
                'content' => '<h2>Article body</h2><p>Useful content for readers.</p>',
                'status' => 'draft',
                'sort_order' => 999,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::query()->where('title', 'Quick CMS Article')->firstOrFail();
        $this->assertSame('quick-cms-article', $article->slug);
        $this->assertSame('Article body Useful content for readers.', $article->excerpt);
        $this->assertSame('Quick CMS Article', $article->meta_title);
        $this->assertSame($article->excerpt, $article->meta_description);
    }

    public function test_escaped_pasted_article_markup_is_rendered_as_html(): void
    {
        Article::create([
            'title' => 'Escaped Markup QA',
            'slug' => 'escaped-markup-qa',
            'language' => 'en',
            'content' => '&lt;!-- Introduction --&gt;&lt;section id="introduction"&gt;&lt;h2&gt;Introduction&lt;/h2&gt;&lt;p&gt;Readable article text.&lt;/p&gt;&lt;/section&gt;',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/articles/escaped-markup-qa')
            ->assertOk()
            ->assertSee('<section id="introduction">', false)
            ->assertSee('Introduction', false)
            ->assertDontSee('&lt;section', false);
    }

    public function test_testimonial_can_be_added_from_the_admin_and_reaches_homepage(): void
    {
        $admin = User::create(['name' => 'Testimonial Admin', 'email' => 'testimonial-admin@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->get('/admin/testimonials')
            ->assertOk()
            ->assertSee('/admin/testimonials/create', false);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial::class)
            ->fillForm([
                'author_name' => 'CMS Client',
                'quote_en' => 'The new testimonial is visible immediately from the CMS.',
                'quote_fa' => 'این نظر جدید مستقیماً از CMS نمایش داده می‌شود.',
                'role_en' => 'Technical Lead',
                'role_fa' => 'مدیر فنی',
                'company_en' => 'CMS Client Co.',
                'company_fa' => 'شرکت مشتری CMS',
                'sort_order' => 999,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('testimonials', ['author_name' => 'CMS Client']);
        $this->get('/')->assertOk()->assertSee('The new testimonial is visible immediately from the CMS.', false);
        $this->assertSame(6, Testimonial::query()->count());
    }

    public function test_article_pages_have_complete_seo_and_clean_canonicals(): void
    {
        foreach (Article::query()->orderBy('slug')->get() as $article) {
            $html = $this->get('/articles/'.$article->slug)->assertOk()->getContent();
            $this->assertStringContainsString('rel="canonical"', $html);
            $this->assertStringContainsString('/articles/'.$article->slug, $html);
            $this->assertStringNotContainsString('rel="canonical" href="'.$article->publicUrl().'.html"', $html);
            $this->assertStringContainsString('property="og:title"', $html);
            $this->assertStringContainsString('name="twitter:card"', $html);
            $this->assertStringContainsString('application/ld+json', $html);
            $this->assertStringNotContainsString('hreflang="de"', $html);
        }
    }

    public function test_sitemap_xml_is_well_formed_and_canonical_only(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $document = new \DOMDocument;
        $this->assertTrue($document->loadXML($xml));
        $this->assertStringNotContainsString('/index.html', $xml);
        $this->assertStringNotContainsString('/articles/creating-a-bootable-usb.html', $xml);
        $this->assertStringNotContainsString('/services/network-design.html', $xml);
        $this->assertStringNotContainsString('/services/network-design', $xml);
        $this->assertStringNotContainsString('/admin', $xml);
        $this->assertStringNotContainsString('/de/', $xml);
        $this->assertSame(24, Article::query()->count());
        foreach (Article::query()->pluck('slug') as $slug) {
            $this->assertStringContainsString('/articles/'.$slug, $xml);
        }
    }

    public function test_public_pages_do_not_reference_missing_local_assets(): void
    {
        $pages = ['/', '/articles', '/articles/'.Article::query()->value('slug')];
        foreach ($pages as $page) {
            $html = $this->get($page)->assertOk()->getContent();
            preg_match_all('/(?:href|src)="(\/assets\/[^"]+)"/', $html, $matches);
            foreach (array_unique($matches[1]) as $asset) {
                $path = public_path(ltrim(parse_url($asset, PHP_URL_PATH), '/'));
                if (! is_file($path)) {
                    $this->get($asset)->assertOk();
                }
            }
        }
    }
}
