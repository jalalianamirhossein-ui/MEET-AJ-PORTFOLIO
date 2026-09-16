<?php
namespace App\Filament\Widgets;
use App\Models\Request;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class NewRequests extends StatsOverviewWidget
{
    public static function canView(): bool { return auth()->user()?->isAdmin() ?? false; }
    protected function getStats(): array {
        return [Stat::make('New contact requests', Request::where('status', 'new')->count())->url(\App\Filament\Resources\RequestResource::getUrl())];
    }
}
