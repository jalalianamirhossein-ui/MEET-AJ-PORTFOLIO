<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = \App\Filament\Resources\ServiceResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Service created as a draft until you publish it';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! filled($data['translation_key'] ?? null)) {
            unset($data['translation_key']);
        }

        return $data;
    }
}
