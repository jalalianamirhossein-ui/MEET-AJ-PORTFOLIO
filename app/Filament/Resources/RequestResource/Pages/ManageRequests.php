<?php

namespace App\Filament\Resources\RequestResource\Pages;

use Filament\Resources\Pages\ManageRecords;

class ManageRequests extends ManageRecords
{
    protected static string $resource = \App\Filament\Resources\RequestResource::class;

    public function getTitle(): string
    {
        return 'Contact requests';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Inbound messages from the public site. Status is the only editable field.';
    }
}
