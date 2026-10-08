<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\ImagePaths;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImageOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_source_article_has_one_named_banner_and_old_duplicate_folders_are_gone(): void
    {
        app(\App\Services\LegacyArticleImporter::class)->import(false);
        $sources = Article::all()->map(fn ($article) => basename(rawurldecode($article->thumbnailUrl())))->all();
        $banners = array_map('basename', glob(resource_path('assets/img/articles/banners/*.png')));
        sort($sources);
        sort($banners);
        $this->assertSame($sources, $banners);
        $this->assertDirectoryDoesNotExist(resource_path('assets/img/banners/articles'));
        $this->assertDirectoryDoesNotExist(resource_path('assets/img/articles/banners/optimized'));
        $this->assertFileExists(resource_path('assets/img/articles/content/.gitkeep'));
    }

    public function test_legacy_images_resolve_to_organized_banners_and_keep_uploads(): void
    {
        $article = new Article([
            'featured_image' => '/assets/img/portfolio/other-1.png',
            'presentation' => ['thumbnail' => '/assets/img/portfolio/other-1.png'],
        ]);
        $this->assertSame('/assets/img/articles/banners/creating-a-bootable-usb.png', $article->thumbnailUrl());
        $this->assertSame('/assets/img/articles/banners/creating-a-bootable-usb.png', $article->galleryUrl());
        $article->featured_image = 'images/articles/banners/custom.webp';
        $this->assertSame('/storage/images/articles/banners/custom.webp', $article->thumbnailUrl());
        $this->assertSame('/storage/images/articles/banners/custom.webp', $article->galleryUrl());
    }

    public function test_existing_inline_banner_reuses_one_file_and_content_folder_is_reserved(): void
    {
        $html = '<img src="/assets/img/portfolio/linux-8.png"><img class="article-hero-thumbnail" src="/assets/img/portfolio/linux-8.png">';
        $body = ImagePaths::body($html, 'linux-security-auditor-bash');
        $this->assertStringContainsString('src="/assets/img/articles/banners/linux-security-auditor-bash.png"', $body);
        $this->assertStringContainsString('class="article-hero-thumbnail" src="/assets/img/articles/banners/linux-security-auditor-bash.png"', $body);
        $this->assertFileExists(resource_path('assets/img/articles/banners/linux-security-auditor-bash.png'));
        $this->assertFileExists(resource_path('assets/img/articles/content/.gitkeep'));
    }

    public function test_only_known_existing_legacy_images_redirect(): void
    {
        $this->get('/assets/img/portfolio/other-1.png')->assertStatus(301)->assertRedirect('/assets/img/articles/banners/creating-a-bootable-usb.png');
        $this->get('/assets/img/logo.png')->assertStatus(301)->assertRedirect('/assets/img/brand/logo.png');
        $this->get('/assets/img/unknown.png')->assertNotFound();
    }

    public function test_retired_fortigate_copies_are_replaced_by_redirects_to_maintained_images(): void
    {
        app(\App\Services\LegacySitePublisher::class)->publishAssets();
        foreach (config('image-paths.legacy') as $old => $new) {
            if (! str_contains($old, 'fortigate-sd-wan-load-balancing-failover')) {
                continue;
            }
            $this->assertFileDoesNotExist(public_path('assets/img/'.$old));
            $this->assertFileExists(resource_path('assets/img/'.$new));
            $this->get('/assets/img/'.$old)->assertStatus(301)->assertRedirect('/assets/img/'.$new);
        }
    }

    public function test_migration_changes_paths_without_replacing_editorial_data_and_is_repeatable(): void
    {
        $article = DB::table('articles')->where('slug', 'mikrotik-pbr-client')->first();
        $this->assertNotNull($article);
        $content = '<p>Custom CMS prose</p><img src="/assets/img/portfolio/mikrotik-8.png">';
        DB::table('articles')->where('id', $article->id)->update([
            'content' => $content,
            'featured_image' => '/assets/img/portfolio/mikrotik-8.png',
            'presentation' => json_encode(['thumbnail' => '/assets/img/portfolio/mikrotik-8.png', 'source_hash' => 'editorial-drift', 'custom' => ['keep' => true]]),
        ]);
        $before = (array) DB::table('articles')->where('id', $article->id)->first();
        $migration = require database_path('migrations/2026_10_06_000023_organize_image_paths.php');
        $migration->up();
        $after = (array) DB::table('articles')->where('id', $article->id)->first();
        foreach (array_diff(array_keys($before), ['content', 'featured_image', 'presentation', 'seo_data']) as $column) {
            $this->assertSame($before[$column], $after[$column], $column);
        }
        $this->assertSame(str_replace('/assets/img/portfolio/mikrotik-8.png', '/assets/img/articles/banners/mikrotik-pbr-client.png', $content), $after['content']);
        $presentation = json_decode($after['presentation'], true);
        $this->assertSame('editorial-drift', $presentation['source_hash']);
        $this->assertTrue($presentation['custom']['keep']);
        $migration->up();
        $this->assertSame($after, (array) DB::table('articles')->where('id', $article->id)->first());
    }
}
