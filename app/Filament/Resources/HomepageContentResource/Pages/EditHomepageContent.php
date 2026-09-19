<?php

namespace App\Filament\Resources\HomepageContentResource\Pages;

use App\Filament\Resources\HomepageContentResource;
use Filament\Resources\Pages\EditRecord;

class EditHomepageContent extends EditRecord
{
    protected static string $resource = HomepageContentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $content = json_decode((string) ($data['content_json'] ?? ''), true);
        $data['content'] = is_array($content) ? $content : ($data['content'] ?? []);
        unset($data['content_json']);

        return $data;
    }
}
