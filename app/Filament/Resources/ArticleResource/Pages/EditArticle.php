<?php
namespace App\Filament\Resources\ArticleResource\Pages;
class EditArticle extends \Filament\Resources\Pages\EditRecord {
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; }
}
