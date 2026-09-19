<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_homepage_catalog_migrates_existing_services_without_deleting_them(): void
    {
        $this->assertSame(13, Service::query()->count());
        $this->assertSame(12, Service::query()->publicCatalog()->count());
        $this->assertSame([
            'network-design',
            'mikrotik-routing-multi-wan',
            'system-administration',
            'virtualization-solutions',
            'hp-enterprise-server',
            'sql-server-high-availability',
            'jira-implementation',
            'monitoring-security',
            'devops-automation',
            'voip-infrastructure',
            'cctv-surveillance',
            'network-security',
        ], Service::query()->publicCatalog()->pluck('slug')->all());

        $consulting = Service::query()->where('slug', 'technical-consulting')->firstOrFail();
        $this->assertFalse($consulting->show_in_catalog);
        $this->assertSame('published', $consulting->status);
        $this->assertNotSame('', (string) $consulting->title);
        $this->assertNotEmpty(data_get($consulting->presentation, 'legacy_features') ?? $consulting->features);
    }

    public function test_homepage_lists_implementation_cards_and_hides_unpublished_rows(): void
    {
        $home = $this->get('/')->assertOk();
        $home->assertSee('id="service-catalog"', false);
        $home->assertSee('id="service-drawer"', false);
        $home->assertSee('Enterprise Network Design &amp; Implementation', false);
        $home->assertSee('MikroTik Routing &amp; Multi-WAN', false);
        $home->assertSee('SQL Server Infrastructure &amp; High Availability', false);
        $home->assertSee('CCTV &amp; Surveillance Infrastructure', false);
        $home->assertSee('VoIP Infrastructure', false);
        $home->assertSee('View Details', false);
        $home->assertSee('Contact Me', false);
        $home->assertSee('data-fa="مشاهده جزئیات"', false);
        $home->assertSee('data-fa="تماس با من"', false);
        $home->assertDontSee('AED 4,900', false);
        $home->assertDontSee('Request Service', false);
        $home->assertDontSee('Technical Consulting', false);

        $draft = Service::query()->where('slug', 'technical-consulting')->firstOrFail();
        $draft->status = 'draft';
        $draft->save();

        $this->get('/')->assertOk()->assertDontSee('/services/technical-consulting"', false);
        $this->get('/services/technical-consulting')->assertNotFound();
        $this->get('/services/technical-consulting.html')->assertNotFound();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/services/technical-consulting', false);
    }

    public function test_service_request_stores_service_id(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Service Buyer',
            'email' => 'service-buyer@example.com',
            'phone' => '0501234567',
            'service' => 'network-design',
            'subject' => 'Network Design Quote Request',
            'message' => 'Please quote a campus network refresh.',
        ])->assertOk()->assertSee('OK', false);

        $serviceId = Service::query()->where('slug', 'network-design')->value('id');
        $this->assertDatabaseHas('requests', [
            'email' => 'service-buyer@example.com',
            'service_id' => $serviceId,
            'subject' => 'Network Design Quote Request',
        ]);
    }

    public function test_unpublished_slug_does_not_attach_and_homepage_contact_still_works(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Homepage User',
            'email' => 'homepage-user@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
            'service' => 'not-a-real-service',
        ])->assertOk();
        $this->assertDatabaseHas('requests', [
            'email' => 'homepage-user@example.com',
            'service_id' => null,
        ]);
    }

    public function test_admin_can_manage_services_and_editors_cannot(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'svc-editor@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();
        $admin = User::create(['name' => 'Admin', 'email' => 'svc-admin@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->assertFalse($editor->fresh()->can('viewAny', Service::class));
        $this->assertTrue($admin->fresh()->can('viewAny', Service::class));
        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\ServiceResource\Pages\ListServices::class)
            ->assertForbidden();
        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ServiceResource\Pages\ListServices::class)
            ->assertOk();
        $this->actingAs($admin)->get('/admin/services')->assertOk();
    }

    public function test_price_changes_are_admin_controlled_and_german_stays_draft(): void
    {
        $service = Service::query()->where('slug', 'network-design')->firstOrFail();
        $service->price = 500;
        $service->price_type = 'starting_from';
        $service->price_currency = 'AED';
        $service->save();

        $this->get('/')->assertOk()->assertDontSee('Starting from 500 AED', false);

        $this->expectException(ValidationException::class);
        Service::create([
            'title' => 'Netzwerk',
            'slug' => 'netzwerk',
            'language' => 'de',
            'short_description' => 'Entwurf',
            'status' => 'published',
            'published_at' => now(),
            'price_type' => 'custom_quote',
        ]);
    }

    public function test_service_detail_urls_are_not_public(): void
    {
        $this->get('/services/network-design.html?ref=nav')->assertNotFound();
        $this->get('/services/does-not-exist')->assertNotFound();
    }
}
