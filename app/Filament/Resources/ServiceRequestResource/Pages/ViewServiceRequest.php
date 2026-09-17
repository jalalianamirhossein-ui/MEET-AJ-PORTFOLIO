<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Filament\Resources\ServiceRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceRequest extends ViewRecord
{
    protected static string $resource = ServiceRequestResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name ?: 'Service request';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Update status'),
        ];
    }
}
