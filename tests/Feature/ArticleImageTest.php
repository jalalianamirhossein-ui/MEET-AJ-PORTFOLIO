<?php

namespace Tests\Feature;

use App\Models\Article;
use Tests\TestCase;

class ArticleImageTest extends TestCase
{
    public function test_old_imports_use_small_thumbnails_and_preserve_full_gallery_images(): void
    {
        $article = new Article([
            'featured_image' => '/assets/img/portfolio/other-1.png',
            'presentation' => [
                'thumbnail' => '/assets/img/portfolio/other-1.png',
                'gallery' => '/assets/img/portfolio/other-1.png',
            ],
        ]);

        $this->assertSame('/assets/img/portfolio/optimized/other-1.jpg', $article->thumbnailUrl());
        $this->assertSame('/assets/img/portfolio/other-1.png', $article->galleryUrl());
        $this->assertSame(rtrim((string) config('app.url'), '/').'/assets/img/portfolio/optimized/other-1.jpg', $article->imageUrl());
        $this->assertLessThan(
            filesize(public_path('assets/img/portfolio/other-1.png')) / 10,
            filesize(public_path(ltrim($article->thumbnailUrl(), '/'))),
        );
    }

    public function test_cms_upload_replaces_imported_images_on_every_surface(): void
    {
        $article = new Article([
            'featured_image' => 'articles/new-cover.webp',
            'presentation' => [
                'thumbnail' => '/assets/img/portfolio/optimized/other-1.jpg',
                'gallery' => '/assets/img/portfolio/other-1.png',
            ],
        ]);

        $this->assertSame('/storage/articles/new-cover.webp', $article->thumbnailUrl());
        $this->assertSame('/storage/articles/new-cover.webp', $article->galleryUrl());
        $this->assertSame(rtrim((string) config('app.url'), '/').'/storage/articles/new-cover.webp', $article->imageUrl());
    }

    public function test_remote_and_public_paths_are_not_rewritten_as_article_routes(): void
    {
        foreach ([
            'https://cdn.example.com/cover.webp' => 'https://cdn.example.com/cover.webp',
            '//cdn.example.com/cover.webp' => '//cdn.example.com/cover.webp',
            'storage/articles/cover.webp' => '/storage/articles/cover.webp',
            '/storage/articles/cover.webp' => '/storage/articles/cover.webp',
            'assets/img/hero-bg.jpg' => '/assets/img/hero-bg.jpg',
        ] as $source => $expected) {
            $article = new Article(['featured_image' => $source]);
            $this->assertSame($expected, $article->thumbnailUrl());
            $this->assertSame($expected, $article->galleryUrl());
        }
    }

    public function test_original_is_kept_when_no_optimized_asset_was_published(): void
    {
        $article = new Article(['featured_image' => '/assets/img/portfolio/windows-3.png']);
        $this->assertSame('/assets/img/portfolio/windows-3.png', $article->thumbnailUrl());
    }
}
