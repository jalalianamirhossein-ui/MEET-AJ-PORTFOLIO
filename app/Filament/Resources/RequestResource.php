<?php
namespace App\Filament\Resources;
use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{TextInput, Textarea, Select};
use Filament\Tables\{Table, Columns\TextColumn, Filters\SelectFilter};
use Filament\Actions\EditAction;
class RequestResource extends Resource
{
    protected static ?string $model = Request::class;
    protected static ?string $modelLabel = 'Contact request';
    public static function form(Schema $schema): Schema {
        return $schema->components([
            TextInput::make('name')->disabled()->dehydrated(false), TextInput::make('email')->disabled()->dehydrated(false),
            TextInput::make('phone')->disabled()->dehydrated(false), TextInput::make('subject')->disabled()->dehydrated(false),
            Textarea::make('message')->rows(10)->disabled()->dehydrated(false),
            Select::make('status')->options(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'spam' => 'Spam'])->required(),
        ]);
    }
    public static function table(Table $table): Table {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('email')->searchable(), TextColumn::make('phone')->searchable(), TextColumn::make('subject')->searchable()->wrap(), TextColumn::make('message')->searchable()->limit(50), TextColumn::make('status')->badge(), TextColumn::make('created_at')->dateTime()->sortable()])
            ->filters([SelectFilter::make('status')->options(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'spam' => 'Spam'])])
            ->recordActions([EditAction::make()->label('View / update status')])->defaultSort('created_at', 'desc');
    }
    public static function getPages(): array { return ['index' => RequestResource\Pages\ManageRequests::route('/')]; }
}
