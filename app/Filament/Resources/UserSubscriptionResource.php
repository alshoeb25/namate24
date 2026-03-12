<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSubscriptionResource\Pages;
use App\Models\UserSubscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Traits\RoleBasedAccess;

class UserSubscriptionResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = UserSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Active Subscriptions';

    protected static ?string $navigationGroup = 'Subscription Management';

    protected static ?int $navigationSort = 17;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Subscriber details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Subscription Details')
                    ->description('Plan and subscription information')
                    ->schema([
                        Forms\Components\Select::make('subscription_plan_id')
                            ->relationship('subscriptionPlan', 'name')
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('subscriptionPlan.price')
                            ->label('Price (₹)')
                            ->disabled(),

                        Forms\Components\TextInput::make('subscriptionPlan.validity_days')
                            ->label('Validity (Days)')
                            ->disabled(),

                        Forms\Components\TextInput::make('subscriptionPlan.views_allowed')
                            ->label('Views Allowed')
                            ->disabled(),

                        Forms\Components\TextInput::make('views_used')
                            ->label('Views Used')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Subscription Timeline')
                    ->description('Active period and expiration')
                    ->schema([
                        Forms\Components\DateTimeField::make('activated_at')
                            ->label('Subscription Started')
                            ->disabled(),

                        Forms\Components\DateTimeField::make('expires_at')
                            ->label('Expires At')
                            ->disabled(),

                        Forms\Components\TextInput::make('remaining_days')
                            ->label('Remaining Days')
                            ->disabled()
                            ->formatStateUsing(function ($state, $record) {
                                if ($record) {
                                    return $record->getRemainingDays();
                                }
                                return 0;
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                                'suspended' => 'Suspended',
                            ])
                            ->disabled(),

                        Forms\Components\DateTimeField::make('cancelled_at')
                            ->label('Cancelled At')
                            ->disabled(),

                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->disabled(),
                    ])
                    ->columns(1),

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
                        'success' => 'active',
                        'danger' => 'expired',
                        'danger' => 'cancelled',
                        'warning' => 'suspended',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.price')
                    ->label('Plan Price (₹)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.validity_days')
                    ->label('Validity (Days)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('views_used')
                    ->label('Views Used')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptionPlan.views_allowed')
                    ->label('Max Views')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('activated_at')
                    ->label('Started')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('Plan')
                    ->relationship('subscriptionPlan', 'name'),

                Tables\Filters\Filter::make('activated_at')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('active_only')
                    ->label('Currently Active')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')
                        ->where('expires_at', '>', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Read-only, no bulk actions
            ])
            ->defaultSort('activated_at', 'desc')
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
            'index' => Pages\ListUserSubscriptions::route('/'),
            'view' => Pages\ViewUserSubscription::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();
    }
}
