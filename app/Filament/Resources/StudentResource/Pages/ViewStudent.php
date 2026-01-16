<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('disable')
                ->label('Disable Student')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn () => !$this->record?->is_disabled)
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Disable Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $student = $this->record;
                    if (!$student) {
                        return;
                    }

                    $student->update([
                        'is_disabled' => true,
                        'disabled_reason' => $data['reason'],
                        'disabled_by' => auth()->id(),
                        'disabled_at' => now(),
                    ]);

                    $this->dispatch('notify', message: 'Student disabled successfully.');
                }),

            Actions\Action::make('enable')
                ->label('Enable Student')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record?->is_disabled)
                ->requiresConfirmation()
                ->action(function () {
                    $student = $this->record;
                    if (!$student) {
                        return;
                    }

                    $student->update([
                        'is_disabled' => false,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    $this->dispatch('notify', message: 'Student enabled successfully.');
                }),
        ];
    }
}
