<?php

namespace App\Filament\Resources\ReferralInviteResource\Pages;

use App\Filament\Resources\ReferralInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditReferralInvite extends EditRecord
{
    protected static string $resource = ReferralInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => !$record->is_used)
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Referral Invite Updated')
            ->body('The referral invite has been updated successfully.');
    }
}
