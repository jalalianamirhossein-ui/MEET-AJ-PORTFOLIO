<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditService extends EditRecord
{
    protected static string $resource = \App\Filament\Resources\ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('View public page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $this->getRecord()->path())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'published' && $this->getRecord()->published_at?->lte(now()) && $this->getRecord()->language !== 'de'),
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
