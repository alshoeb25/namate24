<?php

namespace App\Filament\Resources;

use App\Models\Referral;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\ReferralResource\Pages;

class ReferralResource extends Resource
{
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferrals::route('/'),
        ];
    }
}
