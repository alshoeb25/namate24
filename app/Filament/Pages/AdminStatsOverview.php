<?php

namespace App\Filament\Pages;

use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\StudentRequirement;

class AdminStatsOverview extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $metrics = Cache::remember('admin:stats-overview', 60, function () {
            return [
                'tutors' => User::role('tutor')->count(),
                'students' => User::role('student')->count(),
                'active_enquiries' => StudentRequirement::where('lead_status', '!=', 'closed')->count(),
                'coins_distributed' => CoinTransaction::whereIn('type', ['coin_purchase', 'referral_reward', 'referral_bonus'])
                    ->where('amount', '>', 0)
                    ->sum('amount'),
                'paid_revenue' => Order::where('status', 'paid')->sum('amount'),
            ];
        });

        return [
            BaseStatsOverviewWidget\Stat::make('Total Tutors', $metrics['tutors'])
                ->description('Active tutors on platform')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            BaseStatsOverviewWidget\Stat::make('Total Students', $metrics['students'])
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            BaseStatsOverviewWidget\Stat::make('Active Enquiries', $metrics['active_enquiries'])
                ->description('Open or full enquiries')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            BaseStatsOverviewWidget\Stat::make('Coins Distributed', $metrics['coins_distributed'])
                ->description('Total coins given out')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            BaseStatsOverviewWidget\Stat::make('Payment Revenue', '₹' . number_format($metrics['paid_revenue'], 2))
                ->description('From successful orders')
                ->descriptionIcon('heroicon-m-currency-rupee')
                ->color('success'),
        ];
    }
}
