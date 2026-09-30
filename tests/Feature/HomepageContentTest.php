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

    public function test_older_site_records_render_default_navigation_without_overwriting_custom_links(): void
    {
        $this->get('/')->assertOk();
        $site = HomepageContent::query()->where('key', 'site')->firstOrFail();
        $content = $site->content;
        unset($content['navigation']);
        $site->update(['content' => $content]);

        $this->get('/')->assertOk()
            ->assertSee('data-en="Home"', false)
            ->assertSee('data-en="Services"', false)
            ->assertSee('data-en="Contact"', false);

        $site->update(['content' => array_merge($content, ['navigation' => []])]);
        $this->get('/')->assertOk()->assertSee('data-en="Home"', false);

        $site->update(['content' => array_merge($content, ['navigation' => [
            ['href' => '#contact', 'label_en' => 'Talk to AJ', 'label_fa' => 'تماس'],
        ]])]);
        $this->get('/')->assertOk()->assertSee('data-en="Talk to AJ"', false);
        $this->assertSame('Talk to AJ', data_get($site->fresh()->content, 'navigation.0.label_en'));
    }
}
