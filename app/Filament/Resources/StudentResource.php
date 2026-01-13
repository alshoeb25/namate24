<?php

namespace App\Filament\Resources;

use App\Models\Student;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\StudentResource\Pages;

class StudentResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user.name')
                    ->label('Name')
                    ->disabled(),
                TextInput::make('user.email')
                    ->label('Email')
                    ->disabled(),
                TextInput::make('user.phone')
                    ->label('Phone')
                    ->disabled(),
                Select::make('grade_level')
                    ->options([
                        '6' => '6th Grade',
                        '7' => '7th Grade',
                        '8' => '8th Grade',
                        '9' => '9th Grade',
                        '10' => '10th Grade',
                        '11' => '11th Grade',
                        '12' => '12th Grade',
                        'college' => 'College',
                    ]),
                TextInput::make('learning_goals')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('user.phone')
                    ->searchable(),
                TextColumn::make('grade_level'),
                TextColumn::make('user.coins')
                    ->label('Coins'),
                TextColumn::make('user.created_at')
                    ->label('Joined')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('grade_level')
                    ->options([
                        '6' => '6th Grade',
                        '7' => '7th Grade',
                        '8' => '8th Grade',
                        '9' => '9th Grade',
                        '10' => '10th Grade',
                        '11' => '11th Grade',
                        '12' => '12th Grade',
                        'college' => 'College',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'students';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-students') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-students') ?? false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-students') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-students') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
