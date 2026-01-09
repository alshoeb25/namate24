<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use App\Http\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\Http\Middleware\AdminJwtBridge;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use App\Filament\Pages\AdminDashboard;
use App\Filament\Pages\AdminSettings;
use App\Filament\Resources\TutorResource;
use App\Filament\Resources\StudentResource;
use App\Filament\Resources\StudentRequirementResource;
use App\Filament\Resources\CoinTransactionResource;
use App\Filament\Resources\ReferralResource;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\SubjectResource;
use App\Filament\Resources\OrderResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Cyan,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->pages([
                AdminDashboard::class,
                AdminSettings::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class
            ])
            ->plugins([
                // Add plugins here
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Management'),
                NavigationGroup::make()
                    ->label('Wallet Management'),
                NavigationGroup::make()
                    ->label('Content Management'),
                NavigationGroup::make()
                    ->label('Settings'),
            ]);
    }
}
