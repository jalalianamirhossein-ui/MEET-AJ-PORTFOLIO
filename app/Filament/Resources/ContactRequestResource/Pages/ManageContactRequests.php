<?php

namespace App\Filament\Resources\ContactRequestResource\Pages;

use App\Filament\Resources\ContactRequestResource;
use Filament\Resources\Pages\ListRecords;

class ManageContactRequests extends ListRecords
{
    protected static string $resource = ContactRequestResource::class;

    public function getTitle(): string
    {
        return 'Contact requests';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Homepage contact form submissions. Search, filter, view the message, and update status.';
    }
}
