<?php

namespace App\Services;

use App\Models\Article;

class ArticleSeo
{
    public function forArticle(Article $article): array
    {
        $seo = $article->seo_data ?? [];
        $canonical = $article->canonicalUrl();
        $image = $article->imageUrl();
        $title = $this->englishHeadline($article, $article->meta_title ?: $article->englishTitle());
        $description = $article->meta_description ?: $article->englishExcerpt();

        $schema = $seo['schema'] ?? null;
        if (! is_array($schema) || ($schema['@type'] ?? '') !== 'Article') {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $title,
                'description' => $description,
                'image' => $image,
                'author' => [
                    '@type' => 'Person',
                    'name' => 'AmirHossein Jalalian',
                    'url' => rtrim((string) config('app.url'), '/').'/',
                ],
                'mainEntityOfPage' => $canonical,
            ];
        } else {
            $schema = $this->absolutize($schema, $canonical, $image);
            if (isset($schema['headline'])) {
                $schema['headline'] = $this->englishHeadline($article, is_string($schema['headline']) ? $schema['headline'] : $title);
            }
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_title' => $this->englishHeadline($article, $seo['og_title'] ?? $title),
            'og_description' => $seo['og_description'] ?? $description,
            'og_url' => $canonical,
            'og_type' => $seo['og_type'] ?? 'article',
            'og_image' => $this->absolute($seo['og_image'] ?? $image),
            'twitter_card' => $seo['twitter_card'] ?? 'summary',
            'twitter_title' => $this->englishHeadline($article, $seo['twitter_title'] ?? $title),
            'twitter_description' => $seo['twitter_description'] ?? $description,
            'twitter_image' => isset($seo['twitter_image']) ? $this->absolute($seo['twitter_image']) : null,
            'schema' => $schema,
            'breadcrumb' => $this->breadcrumb($article, $canonical),
            'robots' => $seo['robots'] ?? 'index, follow',
        ];
    }

    private function absolutize(array $schema, string $canonical, string $image): array
    {
        $schema['@context'] ??= 'https://schema.org';
        if (isset($schema['image'])) {
            if (is_string($schema['image'])) {
                $schema['image'] = $this->absolute($schema['image']);
            } elseif (is_array($schema['image']) && isset($schema['image']['url'])) {
                $schema['image']['url'] = $this->absolute((string) $schema['image']['url']);
            }
        } else {
            $schema['image'] = $image;
        }
        $schema['mainEntityOfPage'] = $canonical;
        $schema['url'] = $canonical;

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function breadcrumb(Article $article, string $canonical): array
    {
        $origin = rtrim((string) config('app.url'), '/');
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $origin.'/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Articles', 'item' => $origin.'/articles'],
        ];
        $position = 3;
        if ($article->category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $article->category->name,
                'item' => $origin.'/articles',
            ];
            $position++;
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $this->englishHeadline($article, $article->title),
            'item' => $canonical,
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private function englishHeadline(Article $article, ?string $preferred): string
    {
        $candidates = [
            $preferred,
            $article->englishTitle(),
            data_get($article->presentation, 'hero_title_en'),
            data_get($article->presentation, 'card_title_en'),
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && ! preg_match('/\p{Arabic}/u', $candidate)) {
                return $candidate;
            }
        }

        return $article->englishTitle();
    }

    private function absolute(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        $path = '/'.ltrim(str_replace('../', '', $url), '/');

        return rtrim((string) config('app.url'), '/').$path;
    }
}
