<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = \App\Filament\Resources\ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->requiresConfirmation(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Service saved';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existing = $this->getRecord()->presentation ?? [];
        $incoming = $data['presentation'] ?? [];
        $data['presentation'] = array_merge(
            is_array($existing) ? $existing : [],
            is_array($incoming) ? $incoming : []
        );
        if (! filled($data['featured_image'] ?? null)) {
            unset($data['featured_image']);
        }

        return $data;
    }
}
