<?php

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListReferralCodes extends ListRecords
{
    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Referral Code')
                ->icon('heroicon-o-plus'),
        ];
    }
}
