<?php
namespace App\Filament\Resources\ArticleResource\Pages;
class ListArticles extends \Filament\Resources\Pages\ListRecords {
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()]; }
}
