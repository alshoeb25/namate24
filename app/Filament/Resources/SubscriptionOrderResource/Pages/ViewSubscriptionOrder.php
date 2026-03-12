<?php

namespace App\Filament\Resources\SubscriptionOrderResource\Pages;

use App\Filament\Resources\SubscriptionOrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSubscriptionOrder extends ViewRecord
{
    protected static string $resource = SubscriptionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // View-only page, no edit or delete actions
        ];
    }
}
