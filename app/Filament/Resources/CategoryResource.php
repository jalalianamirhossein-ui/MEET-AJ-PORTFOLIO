<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')
                ->description('Used to group articles. Language cannot change after articles are assigned.')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(180)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->helperText('Public topic color is derived from this slug: microsoft #2563eb, linux #15803d, mikrotik #c2410c, vmware #6d28d9, others #a16207. It is not stored as a separate column.'),
                    Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch'])->default('en')->required(),
                    TextInput::make('translation_key')->disabled()->dehydrated(false)->helperText('Assigned automatically. Unique per language.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(fn (string $state, Category $record): HtmlString => new HtmlString($record->accentChipHtml($state))),
                TextColumn::make('accent')
                    ->label('Accent')
                    ->state(fn (Category $record): string => $record->accentColor())
                    ->badge()
                    ->extraAttributes(fn (Category $record): array => [
                        'class' => 'meetaj-category-hex',
                        'style' => '--meetaj-topic: '.$record->accentColor(),
                    ]),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('language')->badge()->sortable(),
                TextColumn::make('articles_count')->counts('articles')->label('Articles'),
            ])
            ->filters([
                SelectFilter::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch']),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->requiresConfirmation(),
            ])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create a category before assigning it to an article.')
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2)
            ->emptyStateActions([CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => CategoryResource\Pages\ManageCategories::route('/')];
    }
}
