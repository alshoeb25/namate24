<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        
        // Ensure all relationships are loaded
        $this->record->load(['user', 'disabledBy']);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure nested relationships are available in form data
        if ($this->record && $this->record->user) {
            $data['user'] = [
                'name' => $this->record->user->name,
                'email' => $this->record->user->email,
                'phone' => $this->record->user->phone,
                'country' => $this->record->user->country,
                'city' => $this->record->user->city,
                'coins' => $this->record->user->coins,
            ];
        }
        
        if ($this->record && $this->record->disabledBy) {
            $data['disabledBy'] = [
                'name' => $this->record->disabledBy->name,
            ];
        }
        
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Placeholder::make('user.name')
                            ->label('Name')
                            ->content(fn () => $this->record?->user?->name ?? '-'),
                        Forms\Components\Placeholder::make('user.email')
                            ->label('Email')
                            ->content(fn () => $this->record?->user?->email ?? '-'),
                        Forms\Components\Placeholder::make('user.phone')
                            ->label('Phone')
                            ->content(fn () => $this->record?->user?->phone ?? '-'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Student Profile Information')
                    ->schema([
                        Forms\Components\TextInput::make('grade_level')
                            ->label('Grade/Level')
                            ->disabled(),
                        Forms\Components\Textarea::make('learning_goals')
                            ->label('Learning Goals')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\TextInput::make('preferred_subjects')
                            ->label('Preferred Subjects')
                            ->disabled(),
                        Forms\Components\TextInput::make('budget_range')
                            ->label('Budget Range')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Status')
                    ->schema([
                        Forms\Components\Placeholder::make('is_disabled')
                            ->label('Status')
                            ->content(fn () => $this->record?->is_disabled ? 'Disabled' : 'Active'),
                        Forms\Components\TextInput::make('disabled_reason')
                            ->label('Disable Reason')
                            ->disabled(),
                        Forms\Components\Placeholder::make('disabledBy.name')
                            ->label('Disabled By')
                            ->content(fn () => $this->record?->disabledBy?->name ?? '-'),
                        Forms\Components\TextInput::make('disabled_at')
                            ->label('Disabled At')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Placeholder::make('user.country')
                            ->label('Country')
                            ->content(fn () => $this->record?->user?->country ?? '-'),
                        Forms\Components\Placeholder::make('user.city')
                            ->label('City')
                            ->content(fn () => $this->record?->user?->city ?? '-'),
                        Forms\Components\Placeholder::make('user.coins')
                            ->label('Coins')
                            ->content(fn () => $this->record?->user?->coins ?? '-'),
                    ])
                    ->columns(3),
            ]);
    }

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
