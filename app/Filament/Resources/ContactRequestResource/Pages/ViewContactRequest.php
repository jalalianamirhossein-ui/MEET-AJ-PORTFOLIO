<?php

namespace App\Filament\Resources\ContactRequestResource\Pages;

use App\Filament\Resources\ContactRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactRequest extends ViewRecord
{
    protected static string $resource = ContactRequestResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name ?: 'Contact request';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Update status'),
        ];
    }
}
