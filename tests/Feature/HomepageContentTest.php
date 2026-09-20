<?php

namespace Tests\Feature;

use App\Filament\Resources\HomepageContentResource;
use App\Models\HomepageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_sections_are_seeded_and_rendered_from_content_records(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('AmirHossein Jalalian', false)
            ->assertSee('Skills', false)
            ->assertSee('Professional Experience', false)
            ->assertSee('Configured load balancing across 5 Internet connections for stability', false)
            ->assertSee("Let&#039;s Work Together", false);

        $this->assertSame(7, HomepageContent::query()->count());
        $resume = HomepageContent::query()->where('key', 'resume')->firstOrFail();
        $this->assertCount(9, data_get($resume->content, 'education'));
        $this->assertCount(29, data_get($resume->content, 'experience.0.highlights'));
        $this->assertCount(6, data_get($resume->content, 'experience.1.highlights'));

        $hero = HomepageContent::query()->where('key', 'hero')->firstOrFail();
        $hero->update(['content' => array_merge($hero->content, ['title_en' => 'Dynamic Meet AJ Title'])]);
        $this->assertSame('Dynamic Meet AJ Title', data_get($hero->fresh()->content, 'title_en'));

        $this->get('/')->assertOk()->assertSee('Dynamic Meet AJ Title', false);
    }

    public function test_content_sections_are_available_in_the_admin_panel(): void
    {
        $admin = User::create(['name' => 'Homepage Admin', 'email' => 'homepage-admin@example.com', 'password' => 'password12chars', 'role' => 'admin']);
        $this->get('/')->assertOk();

        $this->assertTrue(HomepageContentResource::shouldRegisterNavigation());
        $this->assertTrue($admin->fresh()->can('viewAny', HomepageContent::class));
        \Livewire\Livewire::actingAs($admin)
            ->test(HomepageContentResource\Pages\ListHomepageContents::class)
            ->assertOk();
    }
}
