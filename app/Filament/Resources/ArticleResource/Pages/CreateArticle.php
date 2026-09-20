<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Models\Article;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateArticle extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Article created as a draft until you publish it';
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $slug = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) ($data['title'] ?? 'article'));
        $slug = $slug !== '' ? $slug : 'article';
        if (Article::query()->where('language', $data['language'] ?? 'en')->where('slug', $slug)->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }
        $data['slug'] = $slug;
        $data['content'] = Article::normalizeContentMarkup((string) ($data['content'] ?? ''));

        $plainContent = html_entity_decode(
            (string) preg_replace('/\s+/', ' ', strip_tags((string) preg_replace('/<[^>]+>/', ' ', (string) ($data['content'] ?? '')))),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $plainContent = trim($plainContent);
        $data['excerpt'] = filled($data['excerpt'] ?? null)
            ? (string) $data['excerpt']
            : Str::limit($plainContent, 220);
        $data['meta_title'] = filled($data['meta_title'] ?? null) ? $data['meta_title'] : $data['title'];
        $data['meta_description'] = filled($data['meta_description'] ?? null) ? $data['meta_description'] : $data['excerpt'];

        if (($data['status'] ?? 'draft') === 'published' && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
