<?php

namespace App\Filament\Resources;

use App\Models\CoinTransaction;
use App\Models\User;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Filament\Resources\CoinTransactionResource\Pages;

class CoinTransactionResource extends Resource
{
    use RoleBasedAccess;

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
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'addedByAdmin']))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->label('User')
                    ->description(fn($record) => $record->user?->email),
                TextColumn::make('type')
                    ->badge()
                    ->label('Type')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'enquiry_post' => 'Enquiry Post',
                        'enquiry_unlock' => 'Enquiry Unlock',
                        'coin_purchase' => 'Coin Purchase',
                        'referral_reward' => 'Referral Reward',
                        'referral_bonus' => 'Referral Bonus',
                        'admin_credit' => 'Admin Credit',
                        'admin_debit' => 'Admin Debit',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'enquiry_post' => 'danger',
                        'enquiry_unlock' => 'danger',
                        'coin_purchase' => 'success',
                        'referral_reward' => 'info',
                        'referral_bonus' => 'info',
                        'admin_credit' => 'success',
                        'admin_debit' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 0)
                    ->alignEnd()
                    ->label('Amount')
                    ->formatStateUsing(fn($state) => ($state > 0 ? '+' : '') . $state)
                    ->color(fn($state) => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('balance_after')
                    ->numeric(decimalPlaces: 0)
                    ->alignEnd()
                    ->label('Balance After')
                    ->description(function ($record) {
                        // Calculate balance: total credits - total debits up to this transaction
                        $totalCredits = \App\Models\CoinTransaction::where('user_id', $record->user_id)
                            ->where('amount', '>', 0)
                            ->where('created_at', '<=', $record->created_at)
                            ->sum('amount');
                        
                        $totalDebits = abs(\App\Models\CoinTransaction::where('user_id', $record->user_id)
                            ->where('amount', '<', 0)
                            ->where('created_at', '<=', $record->created_at)
                            ->sum('amount'));
                        
                        $calculatedBalance = $totalCredits - $totalDebits;
                        
                        return "Calculated: {$calculatedBalance} (Added: {$totalCredits} - Spent: {$totalDebits})";
                    })
                    ->sortable(),
                TextColumn::make('addedByAdmin.name')
                    ->label('Added By')
                    ->searchable(['addedByAdmin.name', 'addedByAdmin.email'])
                    ->sortable()
                    ->description(fn($record) => $record->addedByAdmin?->email)
                    ->default('System')
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'enquiry_post' => 'Enquiry Post',
                        'enquiry_unlock' => 'Enquiry Unlock',
                        'coin_purchase' => 'Coin Purchase',
                        'referral_reward' => 'Referral Reward',
                        'referral_bonus' => 'Referral Bonus',
                        'admin_credit' => 'Admin Credit',
                        'admin_debit' => 'Admin Debit',
                    ])
                    ->multiple(),
                SelectFilter::make('added_by_admin_id')
                    ->label('Added By Admin')
                    ->relationship('addedByAdmin', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Action::make('addCoins')
                    ->label('Add Coins to User')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Select::make('user_id')
                            ->label('Select User')
                            ->searchable()
                            ->required()
                            ->options(function () {
                                return User::query()
                                    ->select('id', 'name', 'email')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn($user) => [$user->id => "{$user->name} ({$user->email})"]);
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // Fetch fresh user data from database
                                    $user = User::find($state);
                                    // Get coins directly from users table (same as wallet logic)
                                    $balance = $user ? (int)$user->coins : 0;
                                    $set('current_balance', $balance);
                                    
                                    // Set helper text based on balance
                                    if ($balance === 0) {
                                        $set('balance_status', '⚠️ User has ZERO coins in wallet');
                                        $set('amount', 100); // Suggest 100 coins
                                    } elseif ($balance < 10) {
                                        $set('balance_status', '⚠️ Low balance - User needs more coins');
                                        $set('amount', 50); // Suggest 50 coins
                                    } elseif ($balance < 50) {
                                        $set('balance_status', '⚡ Moderate balance');
                                        $set('amount', 20); // Suggest 20 coins
                                    } else {
                                        $set('balance_status', '✅ Good balance');
                                        $set('amount', 10); // Default 10 coins
                                    }
                                }
                            }),
                        TextInput::make('current_balance')
                            ->label('Current Wallet Balance')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('coins')
                            ->reactive(),
                        TextInput::make('balance_status')
                            ->label(' ')
                            ->disabled()
                            ->dehydrated(false)
                            ->hiddenLabel()
                            ->reactive(),
                        TextInput::make('amount')
                            ->label('Coins to Add')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('coins')
                            ->helperText('Maximum 100 coins per transaction')
                            ->live(),
                        Textarea::make('description')
                            ->label('Reason/Description')
                            ->required()
                            ->rows(3)
                            ->placeholder('Enter the reason for adding these coins...')
                            ->helperText('This will be visible in transaction history'),
                    ])
                    ->action(function (array $data) {
                        // Get fresh user data from database
                        $user = User::findOrFail($data['user_id']);
                        
                        // Always add positive amount
                        $amount = abs($data['amount']);
                        
                        // Update user's coins (same logic as WalletService)
                        $currentBalance = (int)($user->coins ?? 0);
                        $newBalance = $currentBalance + $amount;
                        $user->coins = $newBalance;
                        $user->save();
                        
                        // Create transaction record
                        CoinTransaction::create([
                            'user_id' => $user->id,
                            'added_by_admin_id' => auth()->id(),
                            'type' => 'admin_credit',
                            'amount' => $amount,
                            'balance_after' => $newBalance,
                            'description' => $data['description'],
                            'meta' => [
                                'admin_name' => auth()->user()->name,
                                'admin_email' => auth()->user()->email,
                                'admin_id' => auth()->id(),
                                'timestamp' => now()->toDateTimeString(),
                            ],
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Coins Added Successfully')
                            ->body("{$amount} coins added to {$user->name}'s wallet. New balance: {$newBalance} coins")
                            ->send();
                    })
                    ->modalWidth('md')
                    ->visible(fn() => auth()->check() && (
                        auth()->user()->hasRole('super_admin') || 
                        auth()->user()->can('manage-coins')
                    )),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function getResourcePermissionName(): string
    {
        return 'coins';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-transactions') ?? false;
    }

    public static function canCreate(): bool
    {
        // Allow creation through the "Add Coins" action
        return auth()->check() && (
            auth()->user()->hasRole('super_admin') || 
            auth()->user()->can('manage-coins')
        );
    }

    public static function canEdit($record = null): bool
    {
        return false; // Transactions should not be edited
    }

    public static function canDelete($record = null): bool
    {
        return false; // Transactions should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoinTransactions::route('/'),
        ];
    }
}
