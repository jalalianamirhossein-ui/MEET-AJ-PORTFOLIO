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
        $content = is_array($content) ? $content : [];
        foreach (($data['content'] ?? []) as $key => $value) {
            if ($value !== null && $value !== '') {
                $content[$key] = $value;
            }
        }
        $data['content'] = $content;
        unset($data['content_json']);

        return $data;
    }
}
