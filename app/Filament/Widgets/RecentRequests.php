<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\RequestResource;
use App\Models\Request as ContactRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentRequests extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent requests';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ContactRequest::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->limit(24),
                TextColumn::make('subject')->limit(32)->wrap(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'new' => 'danger',
                    'contacted' => 'info',
                    'in_discussion' => 'warning',
                    'quoted' => 'primary',
                    'approved', 'completed' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('created_at')->since()->label('Received'),
            ])
            ->recordUrl(fn (): string => RequestResource::getUrl())
            ->emptyStateHeading('No contact requests')
            ->emptyStateDescription('Inbound homepage and service quote forms appear here.');
    }
}
