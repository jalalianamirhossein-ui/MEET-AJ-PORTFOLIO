<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAssetDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_https_origin_generates_secure_assets_even_when_origin_request_is_http(): void
    {
        config(['app.url' => 'https://meetaj.ir', 'security.enforce_https' => false]);
        (new AppServiceProvider(app()))->boot();

        $html = $this->get('http://meetaj.ir/admin/login')->assertOk()->getContent();
        $this->assertStringContainsString('https://meetaj.ir/css/filament/filament/app.css', $html);
        $this->assertStringContainsString('https://meetaj.ir/css/app/meet-aj-admin.css', $html);
        $this->assertStringContainsString('https://meetaj.ir/js/filament/filament/app.js', $html);
        $this->assertMatchesRegularExpression('~src="https://meetaj\.ir/livewire[^\"]+/livewire(?:\.min)?\.js~', $html);
        $this->assertDoesNotMatchRegularExpression('~(?:href|src)="http://~', $html);

        $admin = User::create(['name' => 'Asset Admin', 'email' => 'asset-admin@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        foreach (['/admin', '/admin/articles', '/admin/articles/create'] as $path) {
            $this->actingAs($admin)->get('http://meetaj.ir'.$path)->assertOk()
                ->assertSee('https://meetaj.ir/css/filament/filament/app.css', false)
                ->assertDontSee('src="http://meetaj.ir/', false);
        }
    }

    public function test_http_local_development_keeps_http_asset_urls(): void
    {
        config(['app.url' => 'http://localhost']);
        (new AppServiceProvider(app()))->boot();
        $this->get('http://localhost/admin/login')->assertOk()
            ->assertSee('http://localhost/css/filament/filament/app.css', false)
            ->assertSee('http://localhost/js/filament/filament/app.js', false);
    }

    public function test_native_login_still_validates_credentials_and_supports_remember_me(): void
    {
        Livewire::test(Login::class)->fillForm(['email' => '', 'password' => ''])
            ->call('authenticate')->assertHasFormErrors(['email' => 'required', 'password' => 'required']);
        $this->assertGuest();

        $admin = User::create(['name' => 'Login Admin', 'email' => 'login-asset@example.test', 'password' => 'password12chars', 'role' => 'admin']);
        Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'incorrect-password'])
            ->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest();

        Livewire::test(Login::class)->fillForm(['email' => $admin->email, 'password' => 'password12chars', 'remember' => true])
            ->call('authenticate')->assertHasNoFormErrors()->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->assertNotEmpty($admin->fresh()->getRememberToken());
    }

    public function test_livewire_login_transport_still_requires_a_csrf_token(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $payload = ['components' => [[
            'snapshot' => html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'updates' => [],
            'calls' => [],
        ]]];
        $uri = Livewire::getUpdateUri();
        $this->postJson($uri, $payload, ['X-Livewire' => 'true'])->assertStatus(419);
        $this->withSession(['_token' => 'asset-csrf-test'])
            ->postJson($uri, $payload + ['_token' => 'asset-csrf-test'], ['X-Livewire' => 'true'])
            ->assertOk();
        $this->assertGuest();
    }

    public function test_asset_check_fails_for_production_mixed_content_without_fetching_insecure_assets(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://meetaj.ir/admin/login' => Http::response('<link rel="stylesheet" href="http://meetaj.ir/css/app.css"><script src="http://meetaj.ir/livewire/app.js"></script>', 200, ['Content-Type' => 'text/html'])]);
        $this->artisan('site:check-assets', ['--url' => 'https://meetaj.ir', '--path' => ['/admin/login']])
            ->expectsOutputToContain('Insecure or unsupported asset URL')->assertFailed();
        Http::assertSentCount(1);
    }

    public function test_asset_check_accepts_relative_assets(): void
    {
        Http::preventStrayRequests();
        $page = '<link rel="stylesheet" href="/css/app.css"><script src="/js/app.js"></script>';
        Http::fake([
            'https://meetaj.ir/admin/login' => Http::response($page, 200, ['Content-Type' => 'text/html']),
            'https://meetaj.ir/css/app.css' => Http::response('body{color:red}', 200, ['Content-Type' => 'text/css']),
            'https://meetaj.ir/js/app.js' => Http::response('window.example = true;', 200, ['Content-Type' => 'application/javascript; charset=utf-8']),
        ]);
        $this->artisan('site:check-assets', ['--url' => 'https://meetaj.ir', '--path' => ['/admin/login']])->assertSuccessful();
    }

    public function test_asset_check_rejects_html_fallbacks_and_missing_files(): void
    {
        Http::preventStrayRequests();
        $page = '<link rel="stylesheet" href="/css/app.css"><script src="/js/app.js"></script>';
        Http::fake([
            'https://meetaj.ir/admin/login' => Http::response($page, 200, ['Content-Type' => 'text/html']),
            'https://meetaj.ir/css/app.css' => Http::response('<html>Fallback</html>', 200, ['Content-Type' => 'text/html']),
            'https://meetaj.ir/js/app.js' => Http::response('Not found', 404, ['Content-Type' => 'text/plain']),
        ]);
        $this->artisan('site:check-assets', ['--url' => 'https://meetaj.ir', '--path' => ['/admin/login']])
            ->expectsOutputToContain('got 200 text/html')->expectsOutputToContain('got 404 text/plain')->assertFailed();
    }
}
