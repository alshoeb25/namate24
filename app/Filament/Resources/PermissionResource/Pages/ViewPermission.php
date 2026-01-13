<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Permission Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Permission Name')
                            ->badge()
                            ->color('primary')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('guard_name')
                            ->label('Guard')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Usage')
                    ->schema([
                        Infolists\Components\TextEntry::make('roles_count')
                            ->label('Used in Roles')
                            ->state(fn ($record) => $record->roles()->count())
                            ->badge()
                            ->color('success'),
                    ]),

                Infolists\Components\Section::make('Roles with this Permission')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('roles')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Role Name')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('users_count')
                                    ->label('Users')
                                    ->state(fn ($record) => $record->users()->count())
                                    ->badge()
                                    ->color('warning'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->roles()->count() > 0),
            ]);
    }
}
