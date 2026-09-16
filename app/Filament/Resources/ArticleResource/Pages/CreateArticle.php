<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Article created as a draft until you publish it';
    }
}
