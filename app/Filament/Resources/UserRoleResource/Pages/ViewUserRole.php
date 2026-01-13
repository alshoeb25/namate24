<?php

namespace App\Filament\Resources\UserRoleResource\Pages;

use App\Filament\Resources\UserRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewUserRole extends ViewRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Manage Roles'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('role')
                            ->label('Base Role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'tutor' => 'success',
                                'student' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\IconEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Admin Roles')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('roles')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Role')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('permissions_count')
                                    ->label('Permissions')
                                    ->state(fn ($record) => $record->permissions()->count())
                                    ->badge()
                                    ->color('success'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->roles()->count() > 0),

                Infolists\Components\Section::make('All Permissions')
                    ->description('Combined permissions from all assigned roles')
                    ->schema([
                        Infolists\Components\TextEntry::make('all_permissions')
                            ->label('')
                            ->state(function ($record) {
                                return $record->getAllPermissions()
                                    ->pluck('name')
                                    ->sort()
                                    ->implode(', ');
                            })
                            ->badge()
                            ->separator(',')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->getAllPermissions()->count() > 0)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
