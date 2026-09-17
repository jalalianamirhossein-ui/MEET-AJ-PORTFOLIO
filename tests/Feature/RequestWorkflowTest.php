<?php

namespace Tests\Feature;

use App\Models\Request as ContactRequest;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }

    public function test_public_contact_cannot_set_status_or_internal_notes(): void
    {
        $token = $this->get('/forms/get-csrf-token.php')->json('token');
        $this->post('/forms/contact.php', [
            'csrf_token' => $token,
            'name' => 'Workflow User',
            'email' => 'workflow@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
            'status' => 'completed',
            'internal_notes' => 'customer must not write this',
        ], ['HTTP_ACCEPT' => 'text/plain'])->assertOk()->assertSee('OK', false);

        $request = ContactRequest::query()->where('email', 'workflow@example.com')->first();
        $this->assertNotNull($request);
        $this->assertSame('new', $request->status);
        $this->assertNull($request->internal_notes);
        $this->assertArrayNotHasKey('internal_notes', $request->toArray());
    }

    public function test_request_status_workflow_rejects_legacy_labels(): void
    {
        $request = ContactRequest::create([
            'name' => 'Pipeline',
            'email' => 'pipeline@example.com',
            'subject' => 'Need help with network',
            'message' => 'This is a valid test message.',
            'status' => 'new',
        ]);
        $request->internal_notes = 'Call after 18:00';
        $request->status = 'contacted';
        $request->save();
        $this->assertSame('Contacted', $request->fresh()->statusLabel());
        $this->assertSame('Call after 18:00', $request->fresh()->internal_notes);
        $this->assertArrayNotHasKey('internal_notes', $request->fresh()->toArray());

        $this->expectException(ValidationException::class);
        $request->status = 'spam';
        $request->save();
    }

    public function test_editors_cannot_open_requests_and_admins_can(): void
    {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor-req@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();
        $admin = User::create(['name' => 'Admin', 'email' => 'admin-req@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->assertFalse($editor->fresh()->can('viewAny', ContactRequest::class));
        $this->assertTrue($admin->fresh()->can('viewAny', ContactRequest::class));

        \Livewire\Livewire::actingAs($editor)
            ->test(\App\Filament\Resources\RequestResource\Pages\ManageRequests::class)
            ->assertForbidden();
        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\RequestResource\Pages\ManageRequests::class)
            ->assertOk();
    }
}
