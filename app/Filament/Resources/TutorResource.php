<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorResource\Pages;
use App\Filament\Traits\RoleBasedAccess;
use App\Models\Tutor;
use App\Models\TutorModerationAction;
use App\Notifications\TutorApprovalNotification;
use App\Notifications\TutorRejectionNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class TutorResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Tutor::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Tutors';
    protected static ?string $pluralLabel = 'Tutors';
    protected static ?string $modelLabel = 'Tutor';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->required(),
            Forms\Components\TextInput::make('headline')->required(),
            Forms\Components\Textarea::make('about'),
            Forms\Components\TextInput::make('price_per_hour')->numeric(),
            Forms\Components\Select::make('moderation_status')
                ->options([
                    'pending' => 'pending',
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('user.phone')->label('Phone')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('headline')->limit(50)->wrap()->searchable(),
                Tables\Columns\TextColumn::make('price_per_hour')->label('Price/hr')->money('INR', true)->sortable(),
                Tables\Columns\BadgeColumn::make('moderation_status')->colors([
                    'secondary' => 'pending',
                    'success' => 'approved',
                    'danger'  => 'rejected',
                ])->sortable(),
                Tables\Columns\TextColumn::make('rating_avg')->label('Rating')->sortable(),
                Tables\Columns\BadgeColumn::make('is_disabled')
                    ->label('Account')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Disabled' : 'Active')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime('M d, Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->filters([
                Tables\Filters\SelectFilter::make('moderation_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('user:id,name,email,phone');
    }

    public static function getRelations(): array
    {
        return [
            TutorResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    protected static function getResourcePermissionName(): string
    {
        return 'tutors';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-tutors') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-tutors') ?? false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-tutors') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-tutors') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutors::route('/'),
            'create' => Pages\CreateTutor::route('/create'),
            'view' => Pages\ViewTutor::route('/{record}'),
            'edit' => Pages\EditTutor::route('/{record}/edit'),
        ];
    }
}