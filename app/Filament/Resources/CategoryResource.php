<?php
namespace App\Filament\Resources;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{TextInput, Select};
use Filament\Tables\{Table, Columns\TextColumn};
use Filament\Actions\{EditAction, DeleteAction};
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    public static function form(Schema $schema): Schema {
        return $schema->components([TextInput::make('name')->required()->maxLength(255), TextInput::make('slug')->required()->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'), Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch'])->default('en')->required()]);
    }
    public static function table(Table $table): Table { return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('slug'), TextColumn::make('language')])->recordActions([EditAction::make(), DeleteAction::make()]); }
    public static function getPages(): array { return ['index' => CategoryResource\Pages\ManageCategories::route('/')]; }
}
