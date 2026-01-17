<?php

namespace App\Filament\Resources\ReferralInviteResource\Pages;

use App\Filament\Resources\ReferralInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReferralInvites extends ListRecords
{
    protected static string $resource = ReferralInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('bulkUpload')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(fn () => ReferralInviteResource::getUrl('bulk-upload'))
                ->button(),
            Actions\Action::make('statistics')
                ->label('Statistics')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => ReferralInviteResource::getUrl('statistics'))
                ->button(),
        ];
    }
}
