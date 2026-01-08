<?php

namespace App\Filament\Resources\TutorRefundRequestResource\Pages;

use App\Filament\Resources\TutorRefundRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutorRefundRequests extends ListRecords
{
    protected static string $resource = TutorRefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
