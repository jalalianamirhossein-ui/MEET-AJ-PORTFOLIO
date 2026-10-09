<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_entity_encoded_article_markup_cannot_bypass_output_sanitization(): void
    {
        $article = new Article([
            'title' => 'Encoded content', 'slug' => 'encoded-content',
            'content' => '<p>&#60;section&#62;&#60;img src="/missing" onerror="alert(1)"&#62;&#60;script&#62;alert(2)&#60;/script&#62;&#60;/section&#62;</p>',
        ]);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$article->displayContent(), LIBXML_NONET);
        $this->assertSame(0, (new \DOMXPath($dom))->query('//script | //*[@onerror]')->length);
    }

    public function test_production_transport_enforcement_uses_configured_origin(): void
    {
        config(['security.enforce_https' => true, 'app.url' => 'https://meetaj.ir']);
        $this->get('http://attacker.example/articles?q=network')->assertStatus(308)
            ->assertRedirect('https://meetaj.ir/articles?q=network');
        $this->post('http://attacker.example/forms/contact.php')->assertStatus(400);
        $this->get('https://meetaj.ir/admin/login')->assertOk();

        config(['security.trusted_proxies' => ['192.0.2.0/24']]);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://meetaj.ir/admin/login')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.10'])
            ->get('http://meetaj.ir/admin/login')->assertStatus(308)
            ->assertRedirect('https://meetaj.ir/admin/login');
    }

    public function test_security_headers_cover_errors_admin_and_signed_responses(): void
    {
        config(['security.enforce_https' => true]);
        foreach (['/', '/admin/login', '/missing-page'] as $path) {
            $response = $this->get('https://localhost'.$path);
            $response->assertHeader('Strict-Transport-Security', 'max-age=31536000')
                ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'")
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }
        config(['security.enforce_https' => false]);
        $this->get('http://localhost/')->assertOk()->assertHeaderMissing('Strict-Transport-Security');
        foreach (['/articles?signature=test', '/filament/exports/999/download', '/livewire-anything/preview-file/missing'] as $path) {
            $this->assertStringContainsString('no-store', $this->get($path)->headers->get('Cache-Control'));
        }
    }

    public function test_http_hosting_allows_login_and_clears_https_upgrade_policy(): void
    {
        config(['security.enforce_https' => false, 'app.url' => 'http://meetaj.ir', 'session.secure' => false]);
        $this->get('http://meetaj.ir/admin/login')->assertOk()
            ->assertHeaderMissing('Location')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://meetaj.ir/admin/login')->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=0');
    }

    public function test_mail_failures_log_only_safe_context_and_keep_the_request(): void
    {
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('sensitive SMTP detail'));
        Log::shouldReceive('error')->once()->withArgs(fn ($message, $context) =>
            array_keys($context) === ['request_id', 'exception_class']
            && $context['exception_class'] === \RuntimeException::class);
        $this->withSession(['_token' => 'mail-test'])->post('/forms/contact.php', [
            'csrf_token' => 'mail-test', 'name' => 'Test User', 'email' => 'test@example.test',
            'subject' => 'Test notification', 'message' => 'A valid contact message for testing.',
        ])->assertOk();
        $this->assertDatabaseHas('requests', ['email' => 'test@example.test']);
    }

    public function test_related_article_limit_and_lightweight_selection_are_respected(): void
    {
        $source = Article::published()->firstOrFail();
        $this->assertCount(0, $source->relatedArticles(0));
        $this->assertCount(1, $source->relatedArticles(1));
        $this->assertLessThanOrEqual(12, $source->relatedArticles(100)->count());
        $this->assertArrayNotHasKey('content', $source->relatedArticles(1)->first()->getAttributes());
    }

    public function test_service_upload_cannot_assign_an_unvalidated_existing_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('other-record/note.txt', 'Private content');
        $admin = User::create(['name' => 'Admin', 'email' => 'service-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(\App\Filament\Resources\ServiceResource\Pages\CreateService::class)
            ->fillForm([
                'title' => 'Test service', 'slug' => 'test-upload-service', 'language' => 'en',
                'price_type' => 'custom_quote', 'price_currency' => 'AED', 'status' => 'draft',
                'sort_order' => 0, 'featured_image' => ['other-record/note.txt'],
            ])->call('create')->assertHasFormErrors(['featured_image']);
        $this->assertDatabaseMissing('services', ['slug' => 'test-upload-service']);
    }

    public function test_search_matches_literal_sql_wildcards_on_supported_databases(): void
    {
        $article = Article::create([
            'title' => 'Literal 100% value_name! with a \\ path', 'slug' => 'literal-search',
            'content' => '<p>Literal search example.</p>', 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        foreach (['100%', 'value_name!', '\\ path'] as $term) {
            $this->assertTrue(Article::published()->search($term)->whereKey($article->id)->exists(), $term);
        }
        $this->assertFalse(Article::published()->search('100_')->whereKey($article->id)->exists());
    }
}
