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

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/requests')->assertOk()
            ->assertSee('audit-sender@example.com', false)
            ->assertSee('Audit Sender', false)
            ->assertSee('Need help with network', false)
            ->assertSee('Communications', false)
            ->assertSee('Requests', false);
        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\RequestResource\Pages\ManageRequests::class)
            ->assertOk()
            ->assertSee('audit-sender@example.com');

        $this->assertNotSame(200, $this->actingAs($editor)->get('/admin/requests')->getStatusCode());
        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\RequestResource\Pages\ManageRequests::class)
            ->assertForbidden();
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
        $home = $this->get('/');
        $this->assertStringContainsString('charset=utf-8', strtolower((string) $home->headers->get('content-type')));
    }

    public function test_persian_homepage_attributes_are_not_question_marks(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-fa="صفحه اصلی"', $html);
        $this->assertStringContainsString('data-fa="تماس با من"', $html);
        $this->assertStringContainsString('data-fa="نظرات"', $html);
        $this->assertStringContainsString('راه‌حل‌های قابل اعتماد', $html);
        $this->assertStringContainsString('مانیتورینگ Zabbix', $html);
        $this->assertStringContainsString('متخصص شبکه', $html);
        $this->assertDoesNotMatchRegularExpression('/data-fa="[?؟]{3,}"/u', $html);
        $this->assertDoesNotMatchRegularExpression('/data-typed-items-fa="[?؟,\s]+"/u', $html);
        $this->get('/articles')->assertOk()
            ->assertSee('data-fa="صفحه اصلی"', false)
            ->assertSee('href="/#contact"', false);
    }

    public function test_article_titles_remain_english(): void
    {
        $this->assertSame(23, Article::query()->count());
        foreach (Article::query()->get() as $article) {
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $article->title, $article->slug);
            $this->get('/articles/'.$article->slug)
                ->assertOk()
                ->assertSee('data-i18n-lock', false)
                ->assertSee($article->title)
                ->assertSee('href="/#contact"', false);
        }
        $listing = $this->get('/articles')->assertOk()->getContent();
        $this->assertStringContainsString('data-i18n-lock', $listing);
        $first = Article::query()->orderBy('slug')->first();
        $this->assertStringContainsString($first->title, $listing);

        $article = Article::query()->orderBy('slug')->first();
        $english = $article->title;
        $article->forceFill([
            'title' => 'آموزش تست',
            'meta_title' => 'آموزش تست',
        ])->save();
        app(LegacyArticleImporter::class)->import(false);
        $article->refresh();
        $this->assertSame($english, $article->title);
        $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $article->title);
        $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', (string) $article->meta_title);
    }

    public function test_persian_testimonials_use_shared_rtl_safe_slider(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertSame(1, substr_count($home, 'id="testimonials"'));
        $this->assertSame(1, substr_count($home, 'id="testimonials-carousel"'));
        $this->assertSame(5, substr_count($home, 'class="testimonial-card"'));
        $this->assertStringNotContainsString('testimonials-slider-fa', $home);
        $this->assertStringNotContainsString('id="testimonials-fa"', $home);
        $this->assertStringContainsString('data-fa="نظرات"', $home);
        $this->assertStringContainsString('site-modules.css?v=1821', $home);
        $this->assertStringContainsString('main.js?v=1407', $home);
        $this->assertStringContainsString('i18n.js?v=1403', $home);
        $this->assertStringContainsString('rtl.css?v=1403', $home);

        $main = (string) file_get_contents(base_path('assets/js/main.js'));
        $this->assertStringContainsString('function syncTestimonialsSwipers', $main);
        $this->assertStringContainsString('meetaj:languagechange', $main);
        $this->assertStringContainsString('config.rtl = isRtl', $main);
        $this->assertStringContainsString('config.autoHeight = true', $main);
        $this->assertStringContainsString('navigation.nextEl = ".testimonials-next"', $main);
        $this->assertStringNotContainsString('nextEl: ".testimonials-prev"', $main);

        $i18n = (string) file_get_contents(base_path('assets/js/i18n.js'));
        $this->assertStringContainsString('return english', $i18n);
        $this->assertStringNotContainsString('setAttribute("data-fa"', $i18n);

        $rtl = (string) file_get_contents(base_path('assets/css/rtl.css'));
        $this->assertStringContainsString('.testimonials-slider:not(.swiper-rtl) .swiper-wrapper', $rtl);
        $this->assertStringContainsString('direction: ltr !important', $rtl);

        $modules = (string) file_get_contents(base_path('assets/css/site-modules.css'));
        $this->assertStringContainsString('.testimonials-slider:not(.swiper-rtl) .swiper-wrapper', $modules);
        $this->assertStringContainsString('direction: ltr !important', $modules);
        $this->assertStringContainsString('.testimonials.is-empty', $modules);
    }
}
