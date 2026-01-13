<?php

namespace App\Filament\Resources\UserRoleResource\Pages;

use App\Filament\Resources\UserRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUserRole extends EditRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Clear permission cache after updating user roles
        app()['cache']->forget('spatie.permission.cache');

        Notification::make()
            ->title('Roles updated successfully')
            ->success()
            ->body('The user\'s roles and permissions have been updated.')
            ->send();
    }
}
