<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionOrderResource\Pages;
use App\Models\SubscriptionOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\RoleBasedAccess;

class SubscriptionOrderResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = SubscriptionOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Subscribed Users';

    protected static ?string $navigationGroup = 'Subscription Management';

    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Details')
                    ->description('View subscription order details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('subscription_plan_id')
                            ->relationship('subscriptionPlan', 'name')
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('razorpay_order_id')
                            ->disabled()
                            ->placeholder('Not available'),

                        Forms\Components\TextInput::make('razorpay_payment_id')
                            ->disabled()
                            ->placeholder('Not available'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->description('Payment and order status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'initiated' => 'Initiated',
                                'paid' => 'Paid',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('amount')
                            ->disabled()
                            ->prefix('₹'),

                        Forms\Components\TextInput::make('currency')
                            ->disabled()
                            ->default('INR'),

                        Forms\Components\TextInput::make('payment_method')
                            ->disabled()
                            ->placeholder('Not available'),

                        Forms\Components\DateTimeField::make('paid_at')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimeField::make('created_at')
                            ->disabled(),

                        Forms\Components\DateTimeField::make('updated_at')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'paid',
                        'success' => 'completed',
                        'warning' => 'pending',
                        'warning' => 'initiated',
                        'danger' => 'failed',
                        'danger' => 'cancelled',
                        'secondary' => 'refunded',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (₹)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.validity_days')
                    ->label('Validity (Days)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.views_allowed')
                    ->label('Views Allowed')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Subscribed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'initiated' => 'Initiated',
                        'paid' => 'Paid',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('Plan')
                    ->relationship('subscriptionPlan', 'name'),

                Tables\Filters\Filter::make('paid_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('paid_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('active_subscriptions')
                    ->label('Active Subscriptions Only')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['paid', 'completed'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Add bulk actions as needed
            ])
            ->defaultSort('paid_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionOrders::route('/'),
            'view' => Pages\ViewSubscriptionOrder::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['paid', 'completed'])->count();
    }
}
