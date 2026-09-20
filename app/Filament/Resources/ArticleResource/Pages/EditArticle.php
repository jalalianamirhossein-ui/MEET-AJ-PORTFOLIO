<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditArticle extends EditRecord
{
    protected static string $resource = \App\Filament\Resources\ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('View public page')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => $this->getRecord()->path())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->status === 'published' && $this->getRecord()->published_at?->lte(now())),
            DeleteAction::make()->requiresConfirmation(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Article saved';
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['content'] = Article::normalizeContentMarkup((string) ($data['content'] ?? ''));

        return $data;
    }
}
