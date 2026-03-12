<?php

namespace App\Filament\Resources;

use App\Models\SubscriptionPlan;
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
use App\Filament\Resources\SubscriptionPlanResource\Pages;

class SubscriptionPlanResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Subscription Management';
    protected static ?int $navigationSort = 15;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Plan Information')
                    ->description('Basic details about the subscription plan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Plan Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Premium, Pro, Enterprise'),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Describe what this plan includes'),
                    ]),

                Section::make('Pricing')
                    ->description('Set the price for this plan')
                    ->schema([
                        TextInput::make('price')
                            ->label('Price (INR)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₹')
                            ->placeholder('299.99'),
                        
                        TextInput::make('currency')
                            ->label('Currency')
                            ->required()
                            ->default('INR')
                            ->maxLength(3)
                            ->disabled(),
                    ]),

                Section::make('Plan Features')
                    ->description('Configure what users get with this plan')
                    ->schema([
                        TextInput::make('validity_days')
                            ->label('Validity (Days)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('30'),
                        
                        TextInput::make('views_allowed')
                            ->label('Views/Queries Allowed')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('100'),
                    ]),

                Section::make('Status')
                    ->description('Control whether this plan is available')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->helperText('Only active plans will be visible to users'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('price')
                    ->label('Price')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('₹')
                    ->sortable(),
                
                TextColumn::make('validity_days')
                    ->label('Validity')
                    ->suffix(' days')
                    ->sortable(),
                
                TextColumn::make('views_allowed')
                    ->label('Views Allowed')
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
        return 'subscription-plans';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-subscription-plans') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-subscription-plans') ?? false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-subscription-plans') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-subscription-plans') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
