<?php

namespace App\Filament\Resources;

use App\Models\StudentRequirement;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\StudentRequirementResource\Pages;

class StudentRequirementResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = StudentRequirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Enquiries';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('student.user.name')
                    ->label('Student Name')
                    ->disabled(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('post_fee')
                    ->numeric()
                    ->disabled(),
                TextInput::make('unlock_price')
                    ->numeric()
                    ->disabled(),
                Select::make('lead_status')
                    ->options([
                        'open' => 'Open',
                        'full' => 'Full',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),
                TextInput::make('current_leads')
                    ->numeric()
                    ->disabled(),
                TextInput::make('max_leads')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50),
                TextColumn::make('current_leads')
                    ->label('Leads'),
                TextColumn::make('max_leads')
                    ->label('Max'),
                TextColumn::make('lead_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'open' => 'info',
                        'full' => 'warning',
                        'closed' => 'danger',
                        'cancelled' => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('lead_status')
                    ->options([
                        'open' => 'Open',
                        'full' => 'Full',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'enquiries';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-enquiries') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Enquiries are created by students
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('manage-enquiries') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return false; // Enquiries should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentRequirements::route('/'),
            'view' => Pages\ViewStudentRequirement::route('/{record}'),
            'edit' => Pages\EditStudentRequirement::route('/{record}/edit'),
        ];
    }
}
