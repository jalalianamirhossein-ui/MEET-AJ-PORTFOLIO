<?php

namespace App\Filament\Resources\ServiceRequestResource\Pages;

use App\Filament\Resources\ServiceRequestResource;
use Filament\Resources\Pages\ListRecords;

class ManageServiceRequests extends ListRecords
{
    protected static string $resource = ServiceRequestResource::class;

    public function getTitle(): string
    {
        return 'Service requests';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Service quote submissions. Search, filter, view details, and update status.';
    }
}
