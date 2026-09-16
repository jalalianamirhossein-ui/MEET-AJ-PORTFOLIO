<?php

namespace App\Filament\Resources;

use App\Models\Request;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $modelLabel = 'Contact request';

    protected static ?string $pluralModelLabel = 'Requests';

    protected static string | \UnitEnum | null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 1;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedInbox;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Request::query()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inbound message')
                ->description('Submitted through the public contact or service quote form. Fields are read-only.')
                ->icon(Heroicon::OutlinedEnvelope)
                ->schema([
                    TextInput::make('name')->disabled()->dehydrated(false),
                    TextInput::make('email')->disabled()->dehydrated(false),
                    TextInput::make('phone')->disabled()->dehydrated(false),
                    TextInput::make('linked_service')
                        ->label('Service')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function (?Request $record): string {
                            $record?->loadMissing('service');

                            return $record?->service?->title ?: 'Homepage contact (no service)';
                        }),
                    TextInput::make('subject')->disabled()->dehydrated(false),
                    Textarea::make('message')->rows(10)->disabled()->dehydrated(false)->columnSpanFull(),
                ]),
            Section::make('Handling')
                ->schema([
                    Select::make('status')
                        ->options(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'spam' => 'Spam'])
                        ->required()
                        ->helperText('Mark spam instead of deleting so the original submission stays auditable.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('service.title')->label('Service')->placeholder('—')->searchable()->sortable(),
                TextColumn::make('phone')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subject')->searchable()->wrap()->limit(40),
                TextColumn::make('message')->searchable()->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'in_progress' => 'warning',
                    'resolved' => 'success',
                    default => 'gray',
                })->sortable(),
                TextColumn::make('created_at')->label('Received')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved', 'spam' => 'Spam']),
                SelectFilter::make('service_id')->label('Service')->relationship('service', 'title'),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('service'))
            ->recordClasses(fn (Request $record): ?string => $record->status === 'new' ? 'meetaj-request-new' : null)
            ->recordActions([
                EditAction::make()
                    ->label('Review')
                    ->after(function (Request $record): void {
                        Notification::make()->title('Request updated')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('resolve')
                        ->label('Mark resolved')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Request $request): void {
                                $request->status = 'resolved';
                                $request->save();
                            });
                            Notification::make()->title('Selected requests marked resolved.')->success()->send();
                        }),
                    BulkAction::make('spam')
                        ->label('Mark as spam')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Request $request): void {
                                $request->status = 'spam';
                                $request->save();
                            });
                            Notification::make()->title('Selected requests marked as spam.')->success()->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No requests yet')
            ->emptyStateDescription('Homepage contact and service quote submissions appear here. Passwords and CSRF tokens are never stored.')
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => RequestResource\Pages\ManageRequests::route('/')];
    }
}
