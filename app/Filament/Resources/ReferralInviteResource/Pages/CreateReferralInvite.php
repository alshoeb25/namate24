<?php

namespace App\Filament\Resources\ReferralInviteResource\Pages;

use App\Filament\Resources\ReferralInviteResource;
use App\Jobs\SendReferralInviteEmail;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateReferralInvite extends CreateRecord
{
    protected static string $resource = ReferralInviteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default email status
        $data['email_status'] = 'pending';
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Automatically send email after creating referral invite
        try {
            $record = $this->record;
            $record->update(['email_status' => 'queued']);
            
            SendReferralInviteEmail::dispatch($record);
            
            Notification::make()
                ->success()
                ->title('Email Queued')
                ->body("Invitation email to {$record->email} has been queued for sending.")
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->warning()
                ->title('Email Queue Failed')
                ->body("Invite created but email failed to queue: {$e->getMessage()}")
                ->send();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Referral Invite Created')
            ->body('The referral invite has been created and email has been sent.');
    }
}
