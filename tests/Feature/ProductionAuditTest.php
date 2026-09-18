<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Request as ContactRequest;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_homepage_contact_creates_request_visible_to_admin_not_editor(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'audit-admin@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();
        $editor = User::create(['name' => 'Editor', 'email' => 'audit-editor@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();

        $token = $this->get('/forms/get-csrf-token.php')->assertOk()->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Audit Sender',
            'email' => 'audit-sender@example.com',
            'phone' => '09120000999',
            'subject' => 'Need help with network',
            'message' => 'Please review this complete inbound message.',
        ], ['HTTP_ACCEPT' => 'text/plain'])->assertOk()->assertSee('OK', false);

        $this->assertDatabaseHas('requests', [
            'email' => 'audit-sender@example.com',
            'name' => 'Audit Sender',
            'phone' => '09120000999',
            'subject' => 'Need help with network',
            'message' => 'Please review this complete inbound message.',
            'status' => 'new',
            'service_id' => null,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('/admin/requests', false);
        $inbox = $this->actingAs($admin)->get('/admin/requests')->assertOk();
        $inbox->assertSee('audit-sender@example.com', false)
            ->assertSee('Audit Sender', false)
            ->assertSee('09120000999', false)
            ->assertSee('Need help with network', false);

        $this->assertNotSame(200, $this->actingAs($editor)->get('/admin/requests')->getStatusCode());
        $this->actingAs($editor)->get('/admin')->assertOk()
            ->assertDontSee('/admin/requests', false)
            ->assertDontSee('/admin/users', false);
    }

    public function test_direct_homepage_renders_testimonials_and_contact(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'id="testimonials"'));
        $this->assertSame(1, substr_count($html, 'id="contact"'));
        $this->assertStringContainsString('testimonials-slider', $html);
        $this->assertStringContainsString('php-email-form', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('href="#contact"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="testimonials"[^>]*hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/id="contact"[^>]*hidden/', $html);
        $this->assertStringContainsString('aos.js', $html);
        $this->assertStringContainsString('aos.css', $html);
    }

    public function test_persian_homepage_attributes_are_not_question_marks(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-fa="صفحه اصلی"', $html);
        $this->assertStringContainsString('data-fa="تماس با من"', $html);
        $this->assertStringContainsString('data-fa="نظرات"', $html);
        $this->assertDoesNotMatchRegularExpression('/data-fa="[?؟]{3,}"/u', $html);
        $this->get('/articles')->assertOk()
            ->assertSee('data-fa="صفحه اصلی"', false)
            ->assertSee('href="/#contact"', false);
    }

    public function test_article_titles_remain_english(): void
    {
        $this->assertSame(23, Article::query()->count());
        foreach (Article::query()->get() as $article) {
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $article->title, $article->slug);
            $html = $this->get('/articles/'.$article->slug)->assertOk()->getContent();
            $this->assertStringContainsString('data-i18n-lock', $html);
            $this->assertStringContainsString($article->title, $html);
            $this->assertStringContainsString('href="/#contact"', $html);
        }
        $listing = $this->get('/articles')->assertOk()->getContent();
        $this->assertStringContainsString('data-i18n-lock', $listing);
        $first = Article::query()->orderBy('slug')->first();
        $this->assertStringContainsString($first->title, $listing);
    }
}
