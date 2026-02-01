<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorReviewResource\Pages;
use App\Filament\Traits\RoleBasedAccess;
use App\Models\TutorReview;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TutorReviewResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = TutorReview::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Tutor Reviews';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tutor.name')
                    ->label('Tutor')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('student.user.name')
                    ->label('Student')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('rating')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('comment')
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tutor.name')
                    ->label('Tutor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (TutorReview $record) {
                        $record->update(['status' => 'approved']);
                        TutorReview::updateTutorRating($record->tutor_id);
                    })
                    ->visible(fn (TutorReview $record) => $record->status !== 'approved' && (auth()->user()?->can('approve-reviews') ?? false)),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (TutorReview $record) {
                        $record->update(['status' => 'rejected']);
                        TutorReview::updateTutorRating($record->tutor_id);
                    })
                    ->visible(fn (TutorReview $record) => $record->status !== 'rejected' && (auth()->user()?->can('reject-reviews') ?? false)),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'reviews';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-reviews') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-reviews') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-reviews') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'tutor:id,name',
            'student:id,user_id',
            'student.user:id,name',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutorReviews::route('/'),
            'view' => Pages\ViewTutorReview::route('/{record}'),
            'edit' => Pages\EditTutorReview::route('/{record}/edit'),
        ];
    }
}
