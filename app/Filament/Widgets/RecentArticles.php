<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class RecentArticles extends TableWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent articles';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Article::query()->with('category')->latest('updated_at')->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->limit(40)->wrap(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—')
                    ->html()
                    ->formatStateUsing(function (?string $state, Article $record): HtmlString {
                        if (! $record->category) {
                            return new HtmlString('—');
                        }

                        return new HtmlString($record->category->accentChipHtml($state));
                    }),
                TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->recordUrl(fn (Article $record): string => ArticleResource::getUrl('edit', ['record' => $record]))
            ->emptyStateHeading('No articles yet')
            ->emptyStateDescription('Import the original HTML set or create a draft.');
    }
}
