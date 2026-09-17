<?php

namespace App\Filament\Resources\RequestResource\Pages;

use Filament\Resources\Pages\ManageRecords;

class ManageRequests extends ManageRecords
{
    protected static string $resource = \App\Filament\Resources\RequestResource::class;

    public function getTitle(): string
    {
        return 'All requests';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'All inbound contact and service quote submissions.';
    }
}
