<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
