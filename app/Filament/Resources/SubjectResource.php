<?php

namespace App\Filament\Resources;

use App\Models\Subject;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\SubjectResource\Pages;

class SubjectResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'services';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-services') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-services') ?? false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-services') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-services') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
