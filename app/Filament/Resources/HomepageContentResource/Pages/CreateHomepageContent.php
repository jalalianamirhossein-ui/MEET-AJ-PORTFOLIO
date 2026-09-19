<?php

namespace App\Filament\Resources\HomepageContentResource\Pages;

use App\Filament\Resources\HomepageContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepageContent extends CreateRecord
{
    protected static string $resource = HomepageContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeContent($data);
    }

    /** @param array<string,mixed> $data */
    private function normalizeContent(array $data): array
    {
        $content = json_decode((string) ($data['content_json'] ?? ''), true);
        $data['content'] = is_array($content) ? $content : ($data['content'] ?? []);
        unset($data['content_json']);

        return $data;
    }
}
