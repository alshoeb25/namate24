<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record = null): bool
    {
        return false;
    }

    public static function canDelete($record = null): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('User Details')
                ->schema([
                    Forms\Components\TextInput::make('id')->label('User ID')->disabled(),
                    Forms\Components\TextInput::make('name')->disabled(),
                    Forms\Components\TextInput::make('email')->disabled(),
                    Forms\Components\TextInput::make('phone')->disabled(),
                    Forms\Components\TextInput::make('role')->label('Base Role')->disabled(),
                    Forms\Components\TextInput::make('coins')->disabled(),
                    Forms\Components\Toggle::make('is_disabled')->label('Account Disabled')->disabled(),
                    Forms\Components\Textarea::make('disabled_reason')->label('Disabled Reason')->disabled()->columnSpanFull(),
                    Forms\Components\TextInput::make('disabled_at')->label('Disabled At')->disabled(),
                    Forms\Components\TextInput::make('created_at')->label('Registered At')->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Roles')
                ->schema([
                    Forms\Components\Placeholder::make('roles')
                        ->label('Assigned Roles')
                        ->content(fn (User $record) => $record->roles->pluck('name')->implode(', ') ?: 'None'),
                    Forms\Components\Placeholder::make('permissions')
                        ->label('Permissions')
                        ->content(fn (User $record) => $record->getAllPermissions()->pluck('name')->implode(', ') ?: 'None'),
                ]),

            Forms\Components\Section::make('Tutor Profile')
                ->schema([
                    Forms\Components\Placeholder::make('tutor_id')
                        ->label('Tutor ID')
                        ->content(fn (User $record) => $record->tutor?->id ?? 'N/A'),
                    Forms\Components\Toggle::make('tutor_is_disabled')
                        ->label('Tutor Disabled')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn (User $record) => (bool) ($record->tutor?->is_disabled ?? false)),
                    Forms\Components\Textarea::make('tutor_disabled_reason')
                        ->label('Tutor Disabled Reason')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn (User $record) => $record->tutor?->disabled_reason),
                ])->columns(3),

            Forms\Components\Section::make('Student Profile')
                ->schema([
                    Forms\Components\Placeholder::make('student_id')
                        ->label('Student ID')
                        ->content(fn (User $record) => $record->student?->id ?? 'N/A'),
                    Forms\Components\Toggle::make('student_is_disabled')
                        ->label('Student Disabled')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn (User $record) => (bool) ($record->student?->is_disabled ?? false)),
                    Forms\Components\Textarea::make('student_disabled_reason')
                        ->label('Student Disabled Reason')
                        ->disabled()
                        ->dehydrated(false)
                        ->default(fn (User $record) => $record->student?->disabled_reason),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                BadgeColumn::make('role')
                    ->label('Base Role')
                    ->colors([
                        'danger' => 'admin',
                        'info' => 'tutor',
                        'success' => 'student',
                        'gray' => fn ($state) => !in_array($state, ['admin', 'tutor', 'student'], true),
                    ]),
                TextColumn::make('roles.name')
                    ->label('Admin Roles')
                    ->badge()
                    ->separator(',')
                    ->wrap()
                    ->default('None')
                    ->toggleable(),
                BadgeColumn::make('is_disabled')
                    ->label('Account')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Disabled' : 'Active')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),
                BadgeColumn::make('tutor_status')
                    ->label('Tutor')
                    ->getStateUsing(fn (User $record) => $record->tutor ? ($record->tutor->is_disabled ? 'Disabled' : 'Active') : 'N/A')
                    ->colors([
                        'danger' => 'Disabled',
                        'success' => 'Active',
                        'gray' => 'N/A',
                    ]),
                BadgeColumn::make('student_status')
                    ->label('Student')
                    ->getStateUsing(fn (User $record) => $record->student ? ($record->student->is_disabled ? 'Disabled' : 'Active') : 'N/A')
                    ->colors([
                        'danger' => 'Disabled',
                        'success' => 'Active',
                        'gray' => 'N/A',
                    ]),
                IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),
                TextColumn::make('created_at')->label('Registered')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Base Role')
                    ->options([
                        'student' => 'Student',
                        'tutor' => 'Tutor',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\TernaryFilter::make('is_disabled')
                    ->label('Account Status')
                    ->trueLabel('Disabled')
                    ->falseLabel('Active')
                    ->queries(
                        true: fn ($query) => $query->where('is_disabled', true),
                        false: fn ($query) => $query->where('is_disabled', false),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('disableAccount')
                    ->label('Disable Account')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record) => !$record->is_disabled)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Disable Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update([
                            'is_disabled' => true,
                            'disabled_reason' => $data['reason'],
                            'disabled_by' => auth()->id(),
                            'disabled_at' => now(),
                        ]);

                        if ($record->tutor && !$record->tutor->is_disabled) {
                            $record->tutor->update([
                                'is_disabled' => true,
                                'disabled_reason' => $data['reason'],
                                'disabled_by' => auth()->id(),
                                'disabled_at' => now(),
                            ]);
                        }

                        if ($record->student && !$record->student->is_disabled) {
                            $record->student->update([
                                'is_disabled' => true,
                                'disabled_reason' => $data['reason'],
                                'disabled_by' => auth()->id(),
                                'disabled_at' => now(),
                            ]);
                        }

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'disable_user_account',
                            'target_type' => 'user',
                            'target_id' => $record->id,
                            'notes' => $data['reason'],
                            'metadata' => [
                                'disabled_tutor' => (bool) $record->tutor,
                                'disabled_student' => (bool) $record->student,
                            ],
                        ]);
                    }),
                Action::make('enableAccount')
                    ->label('Enable Account')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (User $record) => $record->is_disabled)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->update([
                            'is_disabled' => false,
                            'disabled_reason' => null,
                            'disabled_by' => null,
                            'disabled_at' => null,
                        ]);

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'enable_user_account',
                            'target_type' => 'user',
                            'target_id' => $record->id,
                            'notes' => null,
                            'metadata' => null,
                        ]);
                    }),
                Action::make('disableTutor')
                    ->label('Disable Tutor')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->tutor && !$record->tutor->is_disabled)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Disable Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->tutor?->update([
                            'is_disabled' => true,
                            'disabled_reason' => $data['reason'],
                            'disabled_by' => auth()->id(),
                            'disabled_at' => now(),
                        ]);

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'disable_tutor_profile',
                            'target_type' => 'tutor',
                            'target_id' => $record->tutor->id,
                            'notes' => $data['reason'],
                            'metadata' => [
                                'user_id' => $record->id,
                            ],
                        ]);
                    }),
                Action::make('enableTutor')
                    ->label('Enable Tutor')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (User $record) => !$record->is_disabled && $record->tutor && $record->tutor->is_disabled)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $tutor = $record->tutor;
                        if (!$tutor) {
                            return;
                        }

                        $oldStatus = $tutor->moderation_status;

                        $tutor->update([
                            'moderation_status' => 'approved',
                            'is_disabled' => false,
                            'disabled_reason' => null,
                            'disabled_by' => null,
                            'disabled_at' => null,
                        ]);

                        \App\Models\TutorModerationAction::create([
                            'tutor_id' => $tutor->id,
                            'admin_id' => auth()->id(),
                            'action' => 'approve',
                            'reason' => null,
                            'notes' => 'Enabled via Users module',
                            'old_status' => $oldStatus,
                            'new_status' => 'approved',
                        ]);

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'enable_tutor_profile',
                            'target_type' => 'tutor',
                            'target_id' => $tutor->id,
                            'notes' => 'Enabled via Users module',
                            'metadata' => [
                                'user_id' => $record->id,
                                'old_status' => $oldStatus,
                                'new_status' => 'approved',
                            ],
                        ]);
                    }),
                Action::make('disableStudent')
                    ->label('Disable Student')
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->student && !$record->student->is_disabled)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Disable Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->student?->update([
                            'is_disabled' => true,
                            'disabled_reason' => $data['reason'],
                            'disabled_by' => auth()->id(),
                            'disabled_at' => now(),
                        ]);

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'disable_student_profile',
                            'target_type' => 'student',
                            'target_id' => $record->student->id,
                            'notes' => $data['reason'],
                            'metadata' => [
                                'user_id' => $record->id,
                            ],
                        ]);
                    }),
                Action::make('enableStudent')
                    ->label('Enable Student')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (User $record) => !$record->is_disabled && $record->student && $record->student->is_disabled)
                    ->requiresConfirmation()
                    ->action(function (User $record) {
                        $record->student?->update([
                            'is_disabled' => false,
                            'disabled_reason' => null,
                            'disabled_by' => null,
                            'disabled_at' => null,
                        ]);

                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'enable_student_profile',
                            'target_type' => 'student',
                            'target_id' => $record->student->id,
                            'notes' => null,
                            'metadata' => [
                                'user_id' => $record->id,
                            ],
                        ]);
                    }),
                Action::make('addCoins')
                    ->label('Add Coins')
                    ->icon('heroicon-o-plus-circle')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('coins')
                            ->label('Coins to Add')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(500)
                            ->helperText('Maximum 500 coins per transaction'),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->rows(3)
                            ->helperText('Why are you adding coins to this user?'),
                    ])
                    ->action(function (User $record, array $data) {
                        $coinsToAdd = (int)$data['coins'];
                        
                        // Ensure max 500 coins
                        if ($coinsToAdd > 500) {
                            $coinsToAdd = 500;
                        }
                        
                        // Add coins to user
                        $record->increment('coins', $coinsToAdd);
                        
                        // Log the activity
                        \App\Models\AdminActivityLog::create([
                            'admin_id' => auth()->id(),
                            'action' => 'add_coins_to_user',
                            'target_type' => 'user',
                            'target_id' => $record->id,
                            'notes' => $data['reason'],
                            'metadata' => [
                                'coins_added' => $coinsToAdd,
                                'user_coins_before' => $record->coins - $coinsToAdd,
                                'user_coins_after' => $record->coins,
                            ],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Success')
                            ->body("Added {$coinsToAdd} coins to {$record->name}")
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'roles',
            'tutor',
            'student',
            'disabledBy:id,name',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
