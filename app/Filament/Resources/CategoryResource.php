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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

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
                    TextInput::make('slug')->required()->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                    Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch'])->default('en')->required(),
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
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateActions([CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => CategoryResource\Pages\ManageCategories::route('/')];
    }
}
