<?php

namespace App\Filament\Resources;

use App\Models\Tag;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHashtag;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tag')
                ->description('Used for public filters, related articles, and search. Slugs must stay unique.')
                ->schema([
                    TextInput::make('name')->required()->maxLength(80),
                    TextInput::make('slug')->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->helperText('Leave blank to generate from the name.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('articles_count')->counts('articles')->label('Articles'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->requiresConfirmation(),
            ])
            ->emptyStateHeading('No tags yet')
            ->emptyStateDescription('Create a tag, or run php artisan articles:sync-tags after importing articles.')
            ->emptyStateIcon(Heroicon::OutlinedHashtag)
            ->emptyStateActions([CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => TagResource\Pages\ManageTags::route('/')];
    }
}
