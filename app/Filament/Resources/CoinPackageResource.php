<?php

namespace App\Filament\Resources;

use App\Models\CoinPackage;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use App\Filament\Resources\CoinPackageResource\Pages;

class CoinPackageResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = CoinPackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Wallet Management';
    protected static ?int $navigationSort = 16;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $pluralModelLabel = 'Coin Packages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Package Information')
                    ->description('Basic details about the coin package')
                    ->schema([
                        TextInput::make('name')
                            ->label('Package Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., 50 Coins, 100 Coins Pro'),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Brief description about this package'),
                    ]),

                Section::make('Coins Configuration')
                    ->description('Set coin amounts and bonuses')
                    ->schema([
                        TextInput::make('coins')
                            ->label('Base Coins')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('50')
                            ->helperText('Actual coins user will receive'),
                        
                        TextInput::make('bonus_coins')
                            ->label('Bonus Coins')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('10')
                            ->helperText('Extra coins as bonus (optional)'),
                    ]),

                Section::make('Pricing')
                    ->description('Set the price for this package')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price (INR)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₹')
                            ->placeholder('49.99'),
                    ]),

                Section::make('Display Options')
                    ->description('Control how this package appears to users')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('0')
                            ->helperText('Lower numbers appear first'),
                        
                        Toggle::make('is_popular')
                            ->label('Mark as Popular')
                            ->default(false)
                            ->helperText('Show badge on package card'),
                        
                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->helperText('Only active packages will be visible to users'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Package Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('coins')
                    ->label('Base Coins')
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('bonus_coins')
                    ->label('Bonus Coins')
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('total_coins')
                    ->label('Total Coins')
                    ->getStateUsing(fn ($record) => $record->coins + $record->bonus_coins)
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('price')
                    ->label('Price')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('₹')
                    ->sortable(),
                
                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable(),
                
                IconColumn::make('is_popular')
                    ->label('Popular')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->sortable(),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'coin-packages';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-coin-packages') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-coin-packages') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('edit-coin-packages') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete-coin-packages') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoinPackages::route('/'),
            'create' => Pages\CreateCoinPackage::route('/create'),
            'edit' => Pages\EditCoinPackage::route('/{record}/edit'),
        ];
    }
}
