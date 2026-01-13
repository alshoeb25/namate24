<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorRefundRequestResource\Pages;
use App\Filament\Traits\RoleBasedAccess;
use App\Models\TutorRefundRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;

class TutorRefundRequestResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = TutorRefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Coin Management';
    protected static ?string $navigationLabel = 'Tutor Refunds';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Refund Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('tutor.name')
                                    ->label('Tutor')
                                    ->disabled(),
                                Forms\Components\TextInput::make('tutor.email')
                                    ->label('Email')
                                    ->disabled(),
                                Forms\Components\TextInput::make('enquiry.student_name')
                                    ->label('Student Enquiry')
                                    ->disabled(),
                                Forms\Components\TextInput::make('refund_amount')
                                    ->label('Refund Amount (Coins)')
                                    ->numeric()
                                    ->disabled(),
                            ]),

                        Forms\Components\TextInput::make('reason')
                            ->label('Reason')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => TeacherRefundRequest::getReasons()[$state] ?? $state),

                        Forms\Components\Textarea::make('notes')
                            ->label('Tutor\'s Notes')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3)
                            ->required()
                            ->visible(fn ($record) => $record && $record->canApprove()),
                    ]),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(TeacherRefundRequest::getStatuses())
                            ->disabled()
                            ->required(),

                        Forms\Components\TextInput::make('reviewed_by')
                            ->label('Reviewed By')
                            ->disabled(),

                        Forms\Components\DateTimeInput::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled(),

                        Forms\Components\DateTimeInput::make('processed_at')
                            ->label('Processed At')
                            ->disabled(),
                    ])
                    ->columns(2),
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

                TextColumn::make('enquiry.student_name')
                    ->label('Student Enquiry')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->formatStateUsing(fn ($state) => TutorRefundRequest::getReasons()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('refund_amount')
                    ->label('Amount (Coins)')
                    ->sortable()
                    ->alignRight(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'success' => 'processed',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => TutorRefundRequest::getStatuses()[$state] ?? $state)
                    ->sortable(),

                TextColumn::make('requested_at')
                    ->label('Requested')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options(TutorRefundRequest::getStatuses())
                    ->default('pending'),

                SelectFilter::make('reason')
                    ->multiple()
                    ->options(TutorRefundRequest::getReasons()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-s-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->canApprove())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Admin Notes')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->teacher->notify(new \App\Notifications\RefundApprovedNotification($record, null));
                        // The actual credit happens via the API endpoint
                        // This is just for manual review
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-s-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->canReject())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => TeacherRefundRequest::STATUS_REJECTED,
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                            'admin_notes' => $data['notes'],
                        ]);
                        $record->teacher->notify(new \App\Notifications\RefundRejectedNotification($record));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        return auth()->user()?->can('view-coins') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Refund requests are created by tutors
    }

    public static function canEdit($record = null): bool
    {
        return false; // Refund requests should not be edited
    }

    public static function canDelete($record = null): bool
    {
        return false; // Refund requests should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutorRefundRequests::route('/'),
            'view' => Pages\ViewTutorRefundRequest::route('/{record}'),
        ];
    }
}
