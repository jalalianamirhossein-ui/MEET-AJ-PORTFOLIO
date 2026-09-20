<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\RequestResource;
use App\Models\Category;
use App\Models\User;
use App\Services\LegacyArticleImporter;
use App\Services\LegacySitePublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(LegacySitePublisher::class)->buildViews();
        app(LegacyArticleImporter::class)->import(false);
    }

    public function test_admin_panel_uses_white_red_palette(): void
    {
        $css = (string) file_get_contents(resource_path('css/filament-admin.css'));
        $this->assertStringContainsString('--color-primary: #be123c', $css);
        $this->assertStringContainsString('--primary-400: #be123c !important', $css);
        $this->assertStringContainsString('html.dark .fi-fo-field-label-content', $css);
        $this->assertStringContainsString('html.fi .fi-sidebar-item-label', $css);
        $this->assertStringContainsString('html.fi .fi-checkbox-label', $css);
        $this->assertStringContainsString('color: #1e293b !important', $css);
        $this->assertStringContainsString('.fi-btn.fi-color-primary.fi-bg-color-400', $css);
        $this->assertStringContainsString('--color-danger: #7f1d1d', $css);
        $this->assertStringContainsString('background: #ffffff', $css);
        $this->assertStringContainsString('--color-primary-soft: #fff1f2', $css);
        $this->assertStringNotContainsString('--color-primary: #2563eb', $css);
        $this->assertStringNotContainsString('--color-info: #0ea5e9', $css);

        $provider = (string) file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));
        $this->assertStringContainsString("Color::hex('#be123c')", $provider);
        $this->assertStringContainsString('->darkMode(false)', $provider);
        $this->assertStringContainsString('->themeSwitcher(false)', $provider);
        $this->assertStringContainsString("Color::hex('#7f1d1d')", $provider);
        $this->assertStringNotContainsString("Color::hex('#2563eb')", $provider);
        $this->assertStringNotContainsString("Color::hex('#0ea5e9')", $provider);
    }

    public function test_category_accents_reuse_public_topic_palette(): void
    {
        $this->assertSame('#2563eb', Category::query()->where('slug', 'microsoft')->first()?->accentColor());
        $this->assertSame('#15803d', Category::query()->where('slug', 'linux')->first()?->accentColor());
        $this->assertSame('#c2410c', Category::query()->where('slug', 'mikrotik')->first()?->accentColor());
        $this->assertSame('#6d28d9', Category::query()->where('slug', 'vmware')->first()?->accentColor());
        $this->assertSame('#a16207', Category::query()->where('slug', 'others')->first()?->accentColor());
        $this->assertStringContainsString('meetaj-category-chip', Category::query()->where('slug', 'linux')->first()?->accentChipHtml());
        $this->assertStringContainsString('#15803d', Category::query()->where('slug', 'linux')->first()?->accentChipHtml());
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('categories', 'accent_color'));
        $this->assertNull(Category::query()->where('slug', 'linux')->first()?->accent_color);
    }

    public function test_stored_category_accent_overrides_slug_fallback_and_rejects_invalid_hex(): void
    {
        $linux = Category::query()->where('slug', 'linux')->where('language', 'en')->first();
        $this->assertNotNull($linux);
        $linux->forceFill(['accent_color' => '#0EA5E9'])->save();
        $linux->refresh();
        $this->assertSame('#0ea5e9', $linux->accent_color);
        $this->assertSame('#0ea5e9', $linux->accentColor());

        $linux->forceFill(['accent_color' => null])->save();
        $linux->refresh();
        $this->assertNull($linux->accent_color);
        $this->assertSame('#15803d', $linux->accentColor());

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $linux->forceFill(['accent_color' => 'burgundy'])->save();
    }

    public function test_public_article_surfaces_use_category_accent_color(): void
    {
        $linux = Category::query()->where('slug', 'linux')->where('language', 'en')->first();
        $this->assertNotNull($linux);
        $linux->forceFill(['accent_color' => '#0ea5e9'])->save();

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('--topic: #0ea5e9', $home);
        $this->assertStringContainsString("style=\"--topic: #0ea5e9;\"", $this->get('/articles')->assertOk()->getContent());

        $source = (string) file_get_contents(app_path('Filament/Resources/CategoryResource.php'));
        $this->assertStringContainsString("ColorPicker::make('accent_color')", $source);
        $this->assertStringContainsString("->label('Accent color')", $source);
        $this->assertStringContainsString('clearAccentColor', $source);
    }

    public function test_admin_sees_category_color_chips_editor_is_denied_requests(): void
    {
        $admin = User::create(['name' => 'Theme Admin', 'email' => 'theme-admin@example.test', 'password' => 'password12chars']);
        $admin->forceFill(['role' => 'admin'])->save();
        $editor = User::create(['name' => 'Theme Editor', 'email' => 'theme-editor@example.test', 'password' => 'password12chars']);
        $editor->forceFill(['role' => 'editor'])->save();

        $articles = $this->actingAs($admin)->get(ArticleResource::getUrl())->assertOk()->getContent();
        $this->assertStringContainsString('meetaj-category-chip', $articles);
        $this->assertStringContainsString('--meetaj-topic: #15803d', $articles);
        $this->assertStringContainsString('--meetaj-topic: #2563eb', $articles);

        $categories = $this->actingAs($admin)->get(CategoryResource::getUrl())->assertOk()->getContent();
        $this->assertStringContainsString('meetaj-category-chip', $categories);
        $this->assertStringContainsString('#c2410c', $categories);

        $this->actingAs($admin)->get(RequestResource::getUrl())->assertOk();
        $this->assertNotSame(200, $this->actingAs($editor)->get(RequestResource::getUrl())->getStatusCode());
        $this->actingAs($editor)->get(ArticleResource::getUrl())->assertOk();
        $this->actingAs($editor)->get('/admin')->assertOk();
    }

    public function test_login_page_loads_single_admin_stylesheet(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        $this->assertStringContainsString('Meet AJ CMS', $html);
        $this->assertStringContainsString('/css/app/meet-aj-admin.css', $html);
        $this->assertStringContainsString('data-meetaj="admin-contrast-late"', $html);
        $this->assertStringContainsString('--primary-500:oklch(0.68270588235294 0.17009090909091 16.935)', $html);

        $published = public_path('css/app/meet-aj-admin.css');
        $this->assertFileExists($published);
        $served = (string) file_get_contents($published);
        $this->assertStringContainsString('--color-primary: #be123c', $served);
        $this->assertStringContainsString('--primary-400: #be123c !important', $served);
        $this->assertStringContainsString('html.dark .fi-fo-field-label-content', $served);
        $this->assertStringContainsString('html.fi .fi-checkbox-label', $served);
        $this->assertStringContainsString('.meetaj-category-chip', $served);
    }
}
