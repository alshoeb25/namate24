<?php

namespace App\Filament\Resources\SubscriptionOrderResource\Pages;

use App\Filament\Resources\SubscriptionOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionOrders extends ListRecords
{
    protected static string $resource = SubscriptionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only list, no create action needed
        ];
    }
}
