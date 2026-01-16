<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRoleResource\Pages;
use App\Filament\Traits\RoleBasedAccess;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Spatie\Permission\Models\Role;

class UserRoleResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Access Control';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Assign Admin Roles';
    protected static ?string $pluralLabel = 'Admin Users';
    protected static ?string $modelLabel = 'Admin User';

    protected static function getResourcePermissionName(): string
    {
        return 'access-control';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $adminRoles = [
            'coin_wallet_admin',
            'student_admin',
            'tutor_admin',
            'enquiries_admin',
            'reviews_admin',
            'service_admin',
        ];

        return parent::getEloquentQuery()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $adminRoles));
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Only super admin can see this
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Users are created through registration
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return false; // Don't allow deleting users here
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->disabled(),
                        Forms\Components\TextInput::make('role')
                            ->label('Base Role')
                            ->disabled()
                            ->helperText('User type: student, tutor, or admin'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Admin Roles')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name', fn ($query) => $query->where('guard_name', 'web')->whereNotIn('name', ['super_admin','tutor','student']))
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Assign admin roles to this user (multiple selections allowed). Super admin is protected and only available via system administration.'),
                    ]),

                Forms\Components\Section::make('Direct Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Assign specific permissions (optional, usually roles are sufficient)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => $record->email),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('role')
                    ->label('User Type')
                    ->badge()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Admin Roles')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Role Count')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\BadgeColumn::make('is_disabled')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Disabled' : 'Active')
                    ->colors([
                        'danger' => true,
                        'success' => false,
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filter by Admin Role')
                    ->multiple()
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Manage Roles'),
                Tables\Actions\ViewAction::make(),
                Action::make('disable')
                    ->label('Disable User')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record) => !$record->is_disabled)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
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
                    }),
                Action::make('enable')
                    ->label('Enable User')
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
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('assign_role')
                    ->label('Assign Role to Selected')
                    ->icon('heroicon-o-shield-check')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Select Role to Assign')
                            ->options(Role::where('guard_name', 'web')->whereNotIn('name', ['super_admin'])->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data, $records) {
                        $role = Role::find($data['role']);
                        foreach ($records as $user) {
                            $user->assignRole($role);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('remove_role')
                    ->label('Remove Role from Selected')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Select Role to Remove')
                            ->options(Role::where('guard_name', 'web')->whereNotIn('name', ['super_admin'])->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data, $records) {
                        $role = Role::find($data['role']);
                        foreach ($records as $user) {
                            $user->removeRole($role);
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserRoles::route('/'),
            'edit' => Pages\EditUserRole::route('/{record}/edit'),
            'view' => Pages\ViewUserRole::route('/{record}'),
        ];
    }
}
