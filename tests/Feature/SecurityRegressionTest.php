<?php

namespace Tests\Feature;

use App\Filament\Resources\HomepageContentResource\Pages\EditHomepageContent;
use App\Filament\Widgets\RecentRequests;
use App\Models\Article;
use App\Models\HomepageContent;
use App\Models\User;
use App\Services\HomepageContentCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Middleware\TrustProxies;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_untrusted_clients_cannot_spoof_proxy_headers(): void
    {
        $request = Request::create('http://localhost/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '198.51.100.10', 'HTTP_X_FORWARDED_FOR' => '203.0.113.20',
            'HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_X_FORWARDED_HOST' => 'attacker.example',
        ]);
        app(TrustProxies::class)->handle($request, function ($request) {
            $this->assertSame('198.51.100.10', $request->ip());
            $this->assertFalse($request->secure());
            $this->assertSame('localhost', $request->getHost());

            return response('OK');
        });
    }

    public function test_admin_login_and_guest_redirect_have_security_and_private_cache_headers(): void
    {
        foreach (['/admin/login', '/admin'] as $path) {
            $response = $this->get($path);
            $response->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->assertStringContainsString('no-store', $this->get('/')->assertOk()->headers->get('Cache-Control'));
    }

    public function test_configured_proxy_preserves_real_client_ip_without_trusting_forwarded_host(): void
    {
        config(['security.trusted_proxies' => ['192.0.2.0/24']]);
        $request = Request::create('http://localhost/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.10', 'HTTP_X_FORWARDED_FOR' => '203.0.113.20',
            'HTTP_X_FORWARDED_PROTO' => 'https', 'HTTP_X_FORWARDED_HOST' => 'attacker.example',
        ]);
        app(TrustProxies::class)->handle($request, function ($request) {
            $this->assertSame('203.0.113.20', $request->ip());
            $this->assertTrue($request->secure());
            $this->assertSame('localhost', $request->getHost());

            return response('OK');
        });
    }

    public function test_stored_article_html_cannot_execute_scripts_even_inside_code_blocks(): void
    {
        $article = Article::create([
            'title' => 'Safe article', 'slug' => 'safe-article',
            'content' => '<section id="intro"><h2 data-en="Title" data-fa="عنوان">Title</h2>'
                .'<script>alert("stored-xss")</script><img src="/image.png" onerror="alert(1)">'
                .'<a href="javascript:alert(1)">Unsafe link</a><iframe srcdoc="bad"></iframe>'
                .'<pre><code><img src="x" onerror="alert(2)"></code></pre></section>',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$article->displayContent(), LIBXML_NONET);
        $xp = new \DOMXPath($dom);
        $this->assertSame(0, $xp->query('//script | //iframe | //*[@onerror] | //a[starts-with(@href,"javascript:")]')->length);
        $this->assertSame('عنوان', $xp->evaluate('string(//h2[@data-en="Title"]/@data-fa)'));
        $safeCode = "<pre class=\"sample\"><code class=\"language-bash\">echo &lt;safe&gt;\r\n  # exact whitespace</code></pre>";
        $article->content = '<section id="intro">'.$safeCode.'</section>';
        $this->assertStringContainsString($safeCode, $article->displayContent());
    }

    public function test_code_like_markup_inside_attributes_cannot_break_out_after_sanitizing(): void
    {
        $input = '<a title=\'<pre class="x"><code>sample</code></pre>\'>Link</a>';
        $safe = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($input);
        $this->assertStringNotContainsString('title="<pre', $safe);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$safe, LIBXML_NONET);
        $this->assertSame(0, $dom->getElementsByTagName('pre')->length);
    }

    public function test_unpublishing_homepage_sections_does_not_crash_the_page(): void
    {
        app(HomepageContentCatalog::class)->sync();
        HomepageContent::whereIn('key', ['hero', 'site', 'about', 'contact'])->update(['is_published' => false]);
        $this->get('/')->assertOk();
    }

    public function test_homepage_links_reject_script_schemes_without_changing_database_content(): void
    {
        app(HomepageContentCatalog::class)->sync();
        $hero = HomepageContent::where('key', 'hero')->firstOrFail();
        $content = array_replace($hero->content, ['primary_href' => 'javascript:alert(1)']);
        $hero->update(['content' => $content]);
        $this->get('/')->assertOk()->assertDontSee('href="javascript:alert(1)"', false);
        $this->assertSame('javascript:alert(1)', $hero->fresh()->content['primary_href']);
    }

    public function test_invalid_search_input_returns_a_client_error(): void
    {
        foreach (['/articles?q[]=bad', '/articles?tag[]=bad', '/articles?q='.str_repeat('x', 201)] as $path) {
            $this->get($path)->assertStatus(400);
        }
    }

    public function test_renamed_article_redirects_both_old_urls_and_never_exposes_a_draft(): void
    {
        $article = Article::create([
            'title' => 'Renamed article', 'slug' => 'old-url', 'content' => '<p>Example.</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        $article->update(['slug' => 'new-url']);
        foreach (['/articles/old-url', '/articles/old-url.html'] as $path) {
            $this->get($path.'?lang=fa&ref=nav')->assertStatus(301)->assertRedirect('/articles/new-url?ref=nav');
        }
        $article->update(['status' => 'draft']);
        $this->get('/articles/old-url')->assertNotFound();
        $this->get('/articles/old-url.html')->assertNotFound();
    }

    public function test_editor_cannot_mount_the_private_requests_widget(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'security-editor@example.test', 'password' => 'password12chars', 'role' => 'editor']);
        Livewire::actingAs($editor)->test(RecentRequests::class)->assertForbidden();
    }

    public function test_last_admin_cannot_be_demoted(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'last-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        $this->expectException(ValidationException::class);
        $admin->update(['role' => 'editor']);
    }

    public function test_invalid_homepage_json_is_rejected_before_overwriting_content(): void
    {
        app(HomepageContentCatalog::class)->sync();
        $hero = HomepageContent::where('key', 'hero')->firstOrFail();
        $original = $hero->content;
        $admin = User::create(['name' => 'Admin', 'email' => 'json-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(EditHomepageContent::class, ['record' => $hero->id])
            ->fillForm(['content_json' => '{invalid'])->call('save')->assertHasFormErrors(['content_json']);
        $this->assertSame($original, $hero->fresh()->content);
    }

    public function test_image_upload_always_uses_an_image_extension(): void
    {
        Storage::fake('public');
        $admin = User::create(['name' => 'Admin', 'email' => 'upload-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(\App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial::class)
            ->fillForm([
                'author_name' => 'Safe Avatar', 'quote_en' => 'Image upload test.',
                'quote_fa' => 'آزمایش آپلود تصویر', 'sort_order' => 99,
            ])->set('data.avatar', [UploadedFile::fake()->image('payload.html')->mimeType('image/jpeg')])
            ->call('create')->assertHasNoFormErrors();
        $avatar = \App\Models\Testimonial::where('author_name', 'Safe Avatar')->firstOrFail()->avatar;
        $this->assertMatchesRegularExpression('/\.(jpg|png|webp)$/', $avatar);
        Storage::disk('public')->assertExists($avatar);
    }

    public function test_image_upload_cannot_assign_an_arbitrary_existing_public_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('other-record/private-note.txt', 'Not an image.');
        $admin = User::create(['name' => 'Admin', 'email' => 'path-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(\App\Filament\Resources\TestimonialResource\Pages\CreateTestimonial::class)
            ->fillForm([
                'author_name' => 'Tampered Avatar', 'quote_en' => 'Path tampering test.',
                'quote_fa' => 'آزمایش مسیر فایل', 'sort_order' => 99,
                'avatar' => ['other-record/private-note.txt'],
            ])->call('create')->assertHasFormErrors(['avatar']);
        $this->assertDatabaseMissing('testimonials', ['author_name' => 'Tampered Avatar']);
    }
}
