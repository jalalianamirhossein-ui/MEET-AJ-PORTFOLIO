<?php

namespace App\Filament\Resources;

use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactRequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $modelLabel = 'Contact request';

    protected static ?string $pluralModelLabel = 'Contact requests';

    protected static bool $shouldRegisterNavigation = false;

    protected static string | \UnitEnum | null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 1;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedEnvelope;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->contacts();
    }

    public static function canViewAny(): bool
    {
        return RequestResource::canViewAny();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Request::query()->contacts()->unread()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return RequestResource::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RequestResource::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestResource::table($table)
            ->emptyStateHeading('No contact requests yet')
            ->emptyStateDescription('Messages from the homepage contact form appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ContactRequestResource\Pages\ManageContactRequests::route('/'),
            'view' => ContactRequestResource\Pages\ViewContactRequest::route('/{record}'),
            'edit' => ContactRequestResource\Pages\EditContactRequest::route('/{record}/edit'),
        ];
    }
}
