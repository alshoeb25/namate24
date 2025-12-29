<?php

namespace App\Filament\Resources;

use App\Models\CoinTransaction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\CoinTransactionResource\Pages;

class CoinTransactionResource extends Resource
{
    protected static ?string $model = CoinTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user.name')
                    ->label('User')
                    ->disabled(),
                TextInput::make('type')
                    ->disabled(),
                TextInput::make('amount')
                    ->numeric()
                    ->disabled(),
                TextInput::make('balance_after')
                    ->numeric()
                    ->disabled(),
                TextInput::make('description')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'enquiry_post' => 'danger',
                        'enquiry_unlock' => 'danger',
                        'coin_purchase' => 'success',
                        'referral_reward' => 'info',
                        'referral_bonus' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 0)
                    ->alignEnd(),
                TextColumn::make('balance_after')
                    ->numeric(decimalPlaces: 0)
                    ->alignEnd(),
                TextColumn::make('description')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'enquiry_post' => 'Enquiry Post',
                        'enquiry_unlock' => 'Enquiry Unlock',
                        'coin_purchase' => 'Coin Purchase',
                        'referral_reward' => 'Referral Reward',
                        'referral_bonus' => 'Referral Bonus',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoinTransactions::route('/'),
        ];
    }
}
