<?php

namespace App\Filament\Resources;

use App\Models\Referral;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\ReferralResource\Pages;

class ReferralResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Referral::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('referrer.name')
                    ->label('Referrer')
                    ->disabled(),
                TextInput::make('referred.name')
                    ->label('Referred User')
                    ->disabled(),
                TextInput::make('referrer_coins')
                    ->numeric()
                    ->disabled(),
                TextInput::make('referred_coins')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referrer.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referred.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referrer_coins')
                    ->numeric(decimalPlaces: 0),
                TextColumn::make('referred_coins')
                    ->numeric(decimalPlaces: 0),
                TextColumn::make('reward_given')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getResourcePermissionName(): string
    {
        return 'wallet';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-wallet') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Referrals are created automatically
    }

    public static function canEdit($record = null): bool
    {
        return false; // Referrals should not be edited
    }

    public static function canDelete($record = null): bool
    {
        return false; // Referrals should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
            'manual' => Pages\ManualReferral::route('/manual'),
        ];
    }
}
