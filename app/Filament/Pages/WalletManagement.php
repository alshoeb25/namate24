<?php

namespace App\Filament\Pages;

use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Traits\HasTabs;
use Filament\Widgets\TabsWidget;
use Illuminate\Support\Facades\DB;

class WalletManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Wallet Admin Dashboard';
    protected static string $view = 'filament.pages.wallet-management';

    public function getStats(): array
    {
        $totalCoinsInWallets = User::sum('coins');
        $totalCoinPackages = CoinPackage::count();
        $activeCoinPackages = CoinPackage::active()->count();
        $totalTransactions = CoinTransaction::count();
        
        // Total coins spent (negative transactions)
        $totalCoinsSpent = CoinTransaction::where('amount', '<', 0)->sum(DB::raw('ABS(amount)'));
        
        // Total coins credited (positive transactions)
        $totalCoinsAwarded = CoinTransaction::where('amount', '>', 0)->sum('amount');

        // Payment history stats
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->orWhere('status', 'paid')->count();
        $totalPaymentAmount = Order::where('status', 'completed')->orWhere('status', 'paid')->sum('amount');
        $pendingOrders = Order::where('status', 'pending')->orWhere('status', 'initiated')->count();
        $failedOrders = Order::where('status', 'failed')->count();

        return [
            'totalCoinsInWallets' => $totalCoinsInWallets,
            'totalCoinPackages' => $totalCoinPackages,
            'activeCoinPackages' => $activeCoinPackages,
            'totalTransactions' => $totalTransactions,
            'totalCoinsAwarded' => $totalCoinsAwarded,
            'totalCoinsSpent' => $totalCoinsSpent,
            'totalOrders' => $totalOrders,
            'completedOrders' => $completedOrders,
            'totalPaymentAmount' => $totalPaymentAmount,
            'pendingOrders' => $pendingOrders,
            'failedOrders' => $failedOrders,
        ];
    }

    public function getRecentPayments()
    {
        return Order::latest()
            ->limit(10)
            ->get();
    }

    public function getRecentPaymentTransactions()
    {
        return \App\Models\PaymentTransaction::with(['user', 'order'])
            ->latest()
            ->limit(15)
            ->get();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin can access everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Check wallet management permission
        return $user->can('view-wallet-management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
