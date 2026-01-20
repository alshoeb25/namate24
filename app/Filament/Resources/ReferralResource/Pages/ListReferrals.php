<?php

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Resources\ReferralResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListReferrals extends ListRecords
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manual')
                ->label('Manual Referral Entry')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->url(fn () => ReferralResource::getUrl('manual')),
        ];
    }
}
