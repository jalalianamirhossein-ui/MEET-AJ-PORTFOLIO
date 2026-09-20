<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
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
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('CMS access')
                ->description('Admins can manage users and contact requests. Editors can manage articles and categories only.')
                ->icon(Heroicon::OutlinedLockClosed)
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                    Select::make('role')->options(['admin' => 'Admin', 'editor' => 'Editor'])->required()->default('editor'),
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->rule(Password::min(12)->max(72))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText('Minimum 12 characters. Leave blank when editing to keep the current password.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->badge()->color(fn (string $state): string => $state === 'admin' ? 'primary' : 'gray')->sortable(),
                TextColumn::make('created_at')->since()->label('Created')->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')->options(['admin' => 'Admin', 'editor' => 'Editor']),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->hidden(fn (User $record): bool => auth()->id() === $record->id),
            ])
            ->emptyStateHeading('No CMS users')
            ->emptyStateDescription('Create an admin with php artisan cms:create-user on the host, or add one here.')
            ->emptyStateIcon(Heroicon::OutlinedUsers)
            ->emptyStateActions([CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
