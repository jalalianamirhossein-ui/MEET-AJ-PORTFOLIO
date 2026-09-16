<?php
namespace App\Filament\Resources\CategoryResource\Pages;
class ManageCategories extends \Filament\Resources\Pages\ManageRecords {
    protected static string $resource = \App\Filament\Resources\CategoryResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()]; }
}
