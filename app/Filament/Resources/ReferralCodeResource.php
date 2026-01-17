<?php

namespace App\Filament\Resources;

use App\Models\ReferralCode;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePickerComponent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\ReferralCodeResource\Pages;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class ReferralCodeResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = ReferralCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Referrals';
    protected static ?int $navigationSort = 19;
    protected static ?string $recordTitleAttribute = 'referral_code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('referral_code')
                    ->required()
                    ->unique(ReferralCode::class, 'referral_code', ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null)
                    ->label('Referral Code')
                    ->helperText('Unique code for referral campaign'),

                Select::make('type')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->required()
                    ->label('Code Type'),

                Select::make('referral_type')
                    ->options([
                        'welcome' => 'Welcome',
                        'promotion' => 'Promotion',
                        'fest' => 'Fest',
                    ])
                    ->required()
                    ->label('Campaign Type')
                    ->helperText('Category of this referral campaign'),

                TextInput::make('coins')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->label('Coins per Referral')
                    ->helperText('Coins awarded for each successful referral'),

                TextInput::make('max_count')
                    ->numeric()
                    ->minValue(1)
                    ->label('Max Redemptions')
                    ->helperText('Optional: Limit how many times this code can be used'),

                DateTimePickerComponent::make('expiry')
                    ->label('Expiry Date')
                    ->helperText('Optional: When this code expires'),

                Toggle::make('used')
                    ->disabled()
                    ->label('Marked as Used'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referral_code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                BadgeColumn::make('type')
                    ->colors([
                        'warning' => 'admin',
                        'info' => 'user',
                    ])
                    ->sortable(),

                BadgeColumn::make('referral_type')
                    ->colors([
                        'success' => 'welcome',
                        'primary' => 'promotion',
                        'danger' => 'fest',
                    ])
                    ->label('Campaign')
                    ->sortable(),

                TextColumn::make('coins')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('max_count')
                    ->numeric()
                    ->placeholder('—')
                    ->label('Max Uses'),

                TextColumn::make('expiry')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No expiry'),

                BadgeColumn::make('used')
                    ->getStateUsing(fn ($record) => $record->used ? 'Used' : 'Available')
                    ->colors([
                        'danger' => 'Used',
                        'success' => 'Available',
                    ])
                    ->icons([
                        'heroicon-o-check' => 'Used',
                        'heroicon-o-check-circle' => 'Available',
                    ])
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'admin' => 'Admin',
                        'user' => 'User',
                    ]),

                SelectFilter::make('referral_type')
                    ->label('Campaign Type')
                    ->options([
                        'welcome' => 'Welcome',
                        'promotion' => 'Promotion',
                        'fest' => 'Fest',
                    ]),

                TernaryFilter::make('used')
                    ->label('Status')
                    ->queries(
                        true: fn (Builder $query) => $query->where('used', true),
                        false: fn (Builder $query) => $query->where('used', false),
                    ),
            ])
            ->actions([
                EditAction::make()
                    ->disabled(fn ($record) => $record->used),

                DeleteAction::make()
                    ->disabled(fn ($record) => $record->used),
            ])
            ->bulkActions([
                // Bulk actions disabled for used codes
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'referral_codes';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralCodes::route('/'),
            'create' => Pages\CreateReferralCode::route('/create'),
            'edit' => Pages\EditReferralCode::route('/{record}/edit'),
        ];
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return !$record->used;
    }
}
