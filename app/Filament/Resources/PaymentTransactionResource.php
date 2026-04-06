<?php

namespace App\Filament\Resources;

use App\Models\PaymentTransaction;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ColorColumn;
use App\Filament\Resources\PaymentTransactionResource\Pages;

class PaymentTransactionResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = PaymentTransaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 6;
    protected static ?string $label = 'Payment Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user.name')
                    ->label('User')
                    ->disabled(),
                TextInput::make('razorpay_order_id')
                    ->label('Razorpay Order ID')
                    ->disabled(),
                TextInput::make('razorpay_payment_id')
                    ->label('Razorpay Payment ID')
                    ->disabled(),
                TextInput::make('amount')
                    ->numeric()
                    ->disabled(),
                TextInput::make('currency')
                    ->disabled(),
                TextInput::make('coins')
                    ->numeric()
                    ->disabled(),
                TextInput::make('bonus_coins')
                    ->numeric()
                    ->disabled(),
                Select::make('status')
                    ->options([
                        'AUTHORIZED' => 'Authorized',
                        'CAPTURED' => 'Captured',
                        'SETTLED' => 'Settled',
                        'FAILED' => 'Failed',
                        'REFUNDED' => 'Refunded',
                        'PARTIAL_REFUND' => 'Partial Refund',
                        'SUCCESS' => 'Success',
                        'PENDING' => 'Pending',
                    ])
                    ->disabled(),
                TextInput::make('type')
                    ->disabled(),
                TextInput::make('description')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('razorpay_order_id')
                    ->label('Order ID')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('razorpay_payment_id')
                    ->label('Payment ID')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('₹')
                    ->sortable(),
                TextColumn::make('coins')
                    ->sortable(),
                TextColumn::make('bonus_coins')
                    ->label('Bonus')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'AUTHORIZED' => 'info',
                        'CAPTURED' => 'info',
                        'SETTLED' => 'success',
                        'SUCCESS' => 'success',
                        'PENDING' => 'warning',
                        'FAILED' => 'danger',
                        'REFUNDED' => 'gray',
                        'PARTIAL_REFUND' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'AUTHORIZED' => 'Authorized',
                        'CAPTURED' => 'Captured',
                        'SETTLED' => 'Settled',
                        'FAILED' => 'Failed',
                        'REFUNDED' => 'Refunded',
                        'PARTIAL_REFUND' => 'Partial Refund',
                        'SUCCESS' => 'Success',
                        'PENDING' => 'Pending',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'coin_purchase' => 'Coin Purchase',
                        'refund' => 'Refund',
                        'others' => 'Others',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function canAccess(): bool
    {
        // Allow resource to be registered even if user isn't logged in
        $user = auth()->user();

        if (!$user) {
            // During route registration, no user is logged in
            return true;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can('view-orders');
    }

    public static function canCreate(): bool
    {
        return false; // Payment transactions are auto-created, not manually created
    }

    public static function canEdit($record): bool
    {
        return false; // Payment transactions should not be edited
    }

    public static function canDelete($record): bool
    {
        return false; // Payment transactions should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
        ];
    }
}
