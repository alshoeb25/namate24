<?php

namespace App\Filament\Resources\ReferralInviteResource\Pages;

use App\Models\ReferralInvite;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;

class StatisticsReferralInvites extends Page
{
    protected static string $resource = \App\Filament\Resources\ReferralInviteResource::class;
    protected static string $view = 'filament.pages.statistics-referral-invites';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static bool $shouldRegisterNavigation = false;

    public array $stats = [];

    public function mount(): void
    {
        $this->loadStatistics();
    }

    public function loadStatistics(): void
    {
        $this->stats = [
            'total' => ReferralInvite::count(),
            'used' => ReferralInvite::where('is_used', true)->count(),
            'unused' => ReferralInvite::where('is_used', false)->count(),
            'total_coins_offered' => ReferralInvite::sum('referred_coins') ?? 0,
            'total_coins_redeemed' => ReferralInvite::where('is_used', true)->sum('referred_coins') ?? 0,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->color('gray')
                ->url(\App\Filament\Resources\ReferralInviteResource::getUrl('index')),
        ];
    }
}
