<?php

namespace Tests\Feature;

use App\Filament\Widgets\WorkspaceActions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_shortcuts_follow_user_permissions(): void
    {
        $admin = User::create(['name' => 'Preview Admin', 'email' => 'preview-admin@example.test', 'password' => 'preview-password', 'role' => 'admin']);
        Livewire::actingAs($admin)->test(WorkspaceActions::class)
            ->assertSee('Write an article')
            ->assertSee('Manage homepage')
            ->assertSee('Review requests');

        $editor = User::create(['name' => 'Preview Editor', 'email' => 'preview-editor@example.test', 'password' => 'preview-password', 'role' => 'editor']);
        Livewire::actingAs($editor)->test(WorkspaceActions::class)
            ->assertSee('Write an article')
            ->assertSee('Manage homepage')
            ->assertDontSee('Review requests');
    }

    public function test_authenticated_dashboard_renders_workspace_and_login_renders_intro(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('A clear space for your next idea.');
        $admin = User::create(['name' => 'Preview Admin', 'email' => 'preview-admin@example.test', 'password' => 'preview-password', 'role' => 'admin']);
        Livewire::withoutLazyLoading();
        $response = $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('Your next great update starts here.')
            ->assertSee('Workspace shortcuts')
            ->assertSee('glass-1', false);

        // Optional static visual fixture uses isolated test data, never live users.
        if (getenv('MEETAJ_ADMIN_PREVIEW') === '1') {
            $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $response->getContent());
            $html = str_replace('<head>', '<head><base href="http://127.0.0.1:8088/">', $html);
            $html = str_replace('http://localhost', 'http://127.0.0.1:8088', $html);
            $html = str_replace('class="fi-sidebar fi-main-sidebar"', 'class="fi-sidebar fi-main-sidebar fi-sidebar-open"', $html);
            $html = preg_replace_callback('/<aside\b[^>]*>/i', fn ($match) => preg_replace('/\s+x-cloak="[^"]*"/', '', $match[0]), $html);
            $html = str_replace('</head>', '<style>.fi-main-ctn{opacity:1!important}.fi-sidebar{display:flex!important;transform:none!important}@media(max-width:1023px){.fi-sidebar{display:none!important}}</style></head>', $html);
            file_put_contents(base_path('.runtime/admin-preview.html'), $html);
        }
    }
}
