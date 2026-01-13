<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user.name')
                    ->label('User')
                    ->disabled(),
                TextInput::make('razorpay_order_id')
                    ->disabled(),
                TextInput::make('amount')
                    ->numeric()
                    ->disabled(),
                TextInput::make('status')
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
                TextColumn::make('razorpay_order_id')
                    ->limit(20),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('₹'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'info',
                        'attempted' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'attempted' => 'Attempted',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getResourcePermissionName(): string
    {
        return 'orders';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-orders') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Orders are created programmatically
    }

    public static function canEdit($record = null): bool
    {
        return false; // Orders should not be edited
    }

    public static function canDelete($record = null): bool
    {
        return false; // Orders should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
        ];
    }
}
