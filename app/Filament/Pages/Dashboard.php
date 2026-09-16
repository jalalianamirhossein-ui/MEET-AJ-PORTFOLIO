<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function getTitle(): string | Htmlable
    {
        return 'Operations';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Published technical content, inbound contact requests, and CMS access.';
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
