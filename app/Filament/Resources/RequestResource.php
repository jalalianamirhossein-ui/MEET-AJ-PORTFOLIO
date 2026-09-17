<?php

namespace App\Filament\Resources;

use App\Models\Request;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $modelLabel = 'Request';

    protected static ?string $pluralModelLabel = 'All requests';

    protected static bool $shouldRegisterNavigation = false;

    protected static string | \UnitEnum | null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 3;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedInbox;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
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
                    TextInput::make('created_at')
                        ->label('Received')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Request $record): string => $record?->created_at?->toDayDateTimeString() ?: '—'),
                ]),
            Section::make('Handling')
                ->schema([
                    Select::make('status')
                        ->options(Request::STATUSES)
                        ->required()
                        ->helperText('Customer fields stay read-only. Internal notes never appear on the public site.'),
                    Textarea::make('internal_notes')
                        ->label('Internal notes')
                        ->rows(6)
                        ->columnSpanFull()
                        ->helperText('Visible only to CMS admins.'),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')
                ->icon(Heroicon::OutlinedUser)
                ->columns(2)
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('email')->copyable(),
                    TextEntry::make('phone')->placeholder('—'),
                    TextEntry::make('type')
                        ->label('Type')
                        ->state(fn (Request $record): string => $record->typeLabel()),
                    TextEntry::make('service.title')
                        ->label('Service')
                        ->placeholder('Homepage contact (no service)'),
                    TextEntry::make('created_at')->label('Received')->dateTime(),
                ]),
            Section::make('Message')
                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                ->schema([
                    TextEntry::make('subject'),
                    TextEntry::make('message')->columnSpanFull(),
                ]),
            Section::make('Status')
                ->schema([
                    TextEntry::make('inbox')
                        ->label('Inbox')
                        ->badge()
                        ->state(fn (Request $record): string => $record->inboxLabel())
                        ->color(fn (Request $record): string => $record->status === 'new' ? 'danger' : 'success'),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => Request::STATUSES[$state] ?? $state)
                        ->color(fn (string $state): string => match ($state) {
                            'new' => 'danger',
                            'contacted' => 'info',
                            'in_discussion' => 'warning',
                            'quoted' => 'primary',
                            'approved', 'completed' => 'success',
                            default => 'gray',
                        }),
                    TextEntry::make('internal_notes')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('updated_at')->since()->label('Last updated'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('inbox')
                    ->label('Inbox')
                    ->badge()
                    ->state(fn (Request $record): string => $record->inboxLabel())
                    ->color(fn (Request $record): string => $record->status === 'new' ? 'danger' : 'gray'),
                TextColumn::make('type')
                    ->label('Type')
                    ->state(fn (Request $record): string => $record->typeLabel())
                    ->badge()
                    ->color(fn (Request $record): string => $record->isServiceRequest() ? 'primary' : 'gray')
                    ->toggleable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('service.title')->label('Service')->placeholder('—')->searchable()->sortable(),
                TextColumn::make('subject')->searchable()->wrap()->limit(40),
                TextColumn::make('message')->searchable()->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'contacted' => 'info',
                    'in_discussion' => 'warning',
                    'quoted' => 'primary',
                    'approved', 'completed' => 'success',
                    default => 'gray',
                })->formatStateUsing(fn (string $state): string => Request::STATUSES[$state] ?? $state)->sortable(),
                TextColumn::make('created_at')->label('Received')->dateTime()->sortable(),
                TextColumn::make('updated_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('inbox')
                    ->label('Inbox')
                    ->options([
                        'new' => 'New / unread',
                        'processed' => 'Processed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'new' => $query->unread(),
                            'processed' => $query->processed(),
                            default => $query,
                        };
                    }),
                SelectFilter::make('status')->options(Request::STATUSES),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'contact' => 'Contact request',
                        'service' => 'Service request',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'contact' => $query->contacts(),
                            'service' => $query->serviceRequests(),
                            default => $query,
                        };
                    }),
                SelectFilter::make('service_id')->label('Service')->relationship('service', 'title'),
                Filter::make('created_at')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('service'))
            ->recordClasses(fn (Request $record): ?string => $record->status === 'new' ? 'meetaj-request-new' : null)
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->slideOver(),
                EditAction::make()
                    ->label('Update status')
                    ->after(function (Request $record): void {
                        Notification::make()->title('Request updated')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_processed')
                        ->label('Mark contacted')
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Request $request): void {
                                if ($request->status === 'new') {
                                    $request->status = 'contacted';
                                    $request->save();
                                }
                            });
                            Notification::make()->title('Selected new requests marked contacted.')->success()->send();
                        }),
                    BulkAction::make('complete')
                        ->label('Mark completed')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Request $request): void {
                                $request->status = 'completed';
                                $request->save();
                            });
                            Notification::make()->title('Selected requests marked completed.')->success()->send();
                        }),
                    BulkAction::make('cancel')
                        ->label('Mark cancelled')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Request $request): void {
                                $request->status = 'cancelled';
                                $request->save();
                            });
                            Notification::make()->title('Selected requests marked cancelled.')->success()->send();
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
