<?php

namespace App\Filament\Resources;

use App\Models\Review;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\ReviewResource\Pages;

class ReviewResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('tutor.user.name')
                    ->label('Tutor')
                    ->disabled(),
                TextInput::make('student.name')
                    ->label('Student/Reviewer')
                    ->disabled(),
                TextInput::make('rating')
                    ->numeric()
                    ->disabled(),
                RichEditor::make('comment')
                    ->columnSpanFull()
                    ->disabled(),
                Select::make('moderation_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->native(false),
                Toggle::make('is_hidden')
                    ->label('Hide Review'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tutor.user.name')
                    ->label('Tutor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('moderation_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_hidden')
                    ->label('Hidden'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('moderation_status')
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
                SelectFilter::make('is_hidden')
                    ->options([
                        true => 'Hidden',
                        false => 'Visible',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['moderation_status' => 'approved']))
                    ->visible(fn (Review $record) => $record->moderation_status !== 'approved'),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['moderation_status' => 'rejected']))
                    ->visible(fn (Review $record) => $record->moderation_status !== 'rejected'),
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
        return false; // Reviews cannot be created in admin panel
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-reviews') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-reviews') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
