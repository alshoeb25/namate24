<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            AdminStatsOverview::class,
        ];
    }
}
