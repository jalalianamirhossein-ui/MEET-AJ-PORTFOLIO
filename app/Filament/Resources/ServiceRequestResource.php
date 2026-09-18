<?php

namespace App\Filament\Resources;

use App\Models\Request;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $modelLabel = 'Service request';

    protected static ?string $pluralModelLabel = 'Service requests';

    protected static bool $shouldRegisterNavigation = false;

    protected static string | \UnitEnum | null $navigationGroup = 'Communications';

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->serviceRequests();
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
        $count = Request::query()->serviceRequests()->unread()->count();

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
            ->emptyStateHeading('No service requests yet')
            ->emptyStateDescription('Quote requests from service pages appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ServiceRequestResource\Pages\ManageServiceRequests::route('/'),
            'view' => ServiceRequestResource\Pages\ViewServiceRequest::route('/{record}'),
            'edit' => ServiceRequestResource\Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
