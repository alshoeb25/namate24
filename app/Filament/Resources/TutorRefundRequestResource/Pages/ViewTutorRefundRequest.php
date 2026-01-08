<?php

namespace App\Filament\Resources\TutorRefundRequestResource\Pages;

use App\Filament\Resources\TutorRefundRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTutorRefundRequest extends ViewRecord
{
    protected static string $resource = TutorRefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
