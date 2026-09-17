<?php

namespace Tests\Feature;

use App\Models\Request as ContactRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormCsrfAndAdminRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_session_cookie_flags_match_http_app_url(): void
    {
        $this->assertSame('lax', config('session.same_site'));
        $this->assertSame('meetaj_session', config('session.cookie'));
        $this->assertTrue((bool) config('session.http_only'));
        $this->assertFalse((bool) config('session.secure'));
    }

    public function test_homepage_embeds_live_csrf_token(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('name="csrf-token"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="csrf_token"\s+value=""/', $html);
        $this->assertStringContainsString('value="'.csrf_token().'"', $html);
        $this->assertStringContainsString('progress-bar', $html);
        $this->assertStringContainsString('aria-valuenow="100"', $html);
        $this->assertStringContainsString('contact-form.js', $html);
    }

    public function test_missing_csrf_token_returns_plain_419(): void
    {
        $this->get('/');
        $this->post('/forms/contact.php', [
            'name' => 'No Token',
            'email' => 'notoken@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ], ['HTTP_ACCEPT' => 'text/plain'])
            ->assertStatus(419)
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('Security token expired', false);
        $this->assertDatabaseMissing('requests', ['email' => 'notoken@example.com']);
    }

    public function test_header_csrf_token_is_accepted(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->assertOk()->json('token');
        $this->post('/forms/contact.php', [
            'name' => 'Header Token',
            'email' => 'header-token@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ], [
            'HTTP_ACCEPT' => 'text/plain',
            'HTTP_X_CSRF_TOKEN' => $token,
        ])->assertOk()->assertSee('OK', false);
        $this->assertDatabaseHas('requests', ['email' => 'header-token@example.com', 'status' => 'new']);
    }

    public function test_page_token_field_submits_contact_and_service_requests(): void
    {
        $home = $this->get('/');
        $home->assertOk();
        preg_match('/name="csrf-token" content="([^"]+)"/', $home->getContent(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $token = $matches[1];

        $this->post('/forms/contact.php', [
            '_token' => $token,
            'csrf_token' => $token,
            'name' => 'Page Token User',
            'email' => 'page-token@example.com',
            'phone' => '09120000001',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
        ])->assertOk()->assertSee('OK', false);

        $serviceToken = $this->get('/services/network-design')->assertOk();
        preg_match('/name="csrf-token" content="([^"]+)"/', $serviceToken->getContent(), $serviceMatches);
        $this->assertNotEmpty($serviceMatches[1] ?? null);

        $this->post('/forms/contact.php', [
            'csrf_token' => $serviceMatches[1],
            'name' => 'Service Page User',
            'email' => 'service-page@example.com',
            'phone' => '0501234567',
            'service' => 'network-design',
            'subject' => 'Network Design Quote Request',
            'message' => 'Please quote a campus network refresh.',
        ])->assertOk()->assertSee('OK', false);

        $serviceId = Service::query()->where('slug', 'network-design')->value('id');
        $this->assertDatabaseHas('requests', [
            'email' => 'page-token@example.com',
            'service_id' => null,
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('requests', [
            'email' => 'service-page@example.com',
            'service_id' => $serviceId,
            'status' => 'new',
        ]);
    }

    public function test_admin_contact_and_service_request_pages_use_database_counts(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-forms@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();
        $editor = User::create(['name' => 'Editor', 'email' => 'editor-forms@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();

        $serviceId = Service::query()->where('slug', 'network-design')->value('id');
        ContactRequest::create([
            'name' => 'Contact Person',
            'email' => 'split-contact@example.com',
            'phone' => '09120000002',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
            'status' => 'new',
        ]);
        ContactRequest::create([
            'name' => 'Service Person',
            'email' => 'split-service@example.com',
            'phone' => '0509990000',
            'subject' => 'Network Design Quote Request',
            'message' => 'Please quote a campus network refresh.',
            'status' => 'new',
            'service_id' => $serviceId,
        ]);

        $contact = ContactRequest::query()->where('email', 'split-contact@example.com')->firstOrFail();
        $service = ContactRequest::query()->where('email', 'split-service@example.com')->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('Contact Requests', false)
            ->assertSee('Service Requests', false)
            ->assertSee('/admin/contact-requests', false)
            ->assertSee('/admin/service-requests', false);
        $this->actingAs($admin)->get('/admin/contact-requests')->assertOk()->assertSee('split-contact@example.com', false)->assertDontSee('split-service@example.com', false);
        $this->actingAs($admin)->get('/admin/service-requests')->assertOk()->assertSee('split-service@example.com', false)->assertDontSee('split-contact@example.com', false);
        $this->actingAs($admin)->get('/admin/contact-requests/'.$contact->id)->assertOk()->assertSee('Need help with network', false);
        $this->actingAs($admin)->get('/admin/service-requests/'.$service->id)->assertOk()->assertSee('Network Design Quote Request', false);

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ContactRequestResource\Pages\ManageContactRequests::class)
            ->assertOk()
            ->assertSee('split-contact@example.com');
        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ServiceRequestResource\Pages\ManageServiceRequests::class)
            ->assertOk()
            ->assertSee('split-service@example.com');
        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\ContactRequestResource\Pages\ManageContactRequests::class)
            ->assertForbidden();
        $this->assertNotSame(200, $this->actingAs($editor)->get('/admin/contact-requests')->getStatusCode());
    }
}
