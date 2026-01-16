<?php

namespace App\Filament\Resources\TutorResource\Pages;

use App\Filament\Resources\TutorResource;
use App\Models\Tutor;
use App\Models\TutorModerationAction;
use App\Notifications\TutorApprovalNotification;
use App\Notifications\TutorRejectionNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Illuminate\Support\Facades\Notification;

class ViewTutor extends ViewRecord
{
    protected static string $resource = TutorResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        
        // Ensure all relationships are loaded
        $this->record->load([
            'user',
            'reviewedBy',
            'moderationActions.admin',
            'documents',
            'disabledBy'
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure nested relationships are available in form data
        if ($this->record && $this->record->user) {
            $data['user'] = [
                'name' => $this->record->user->name,
                'email' => $this->record->user->email,
                'phone' => $this->record->user->phone,
            ];
        }
        
        if ($this->record && $this->record->reviewedBy) {
            $data['reviewedBy'] = [
                'name' => $this->record->reviewedBy->name,
            ];
        }
        
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
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

                Forms\Components\Section::make('Profile Information')
                    ->schema([
                        Forms\Components\TextInput::make('headline')
                            ->disabled(),
                        Forms\Components\Textarea::make('about')
                            ->disabled()
                            ->rows(4),
                        Forms\Components\TextInput::make('gender')
                            ->disabled(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Teaching Details')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_hour')
                            ->label('Price per Hour (₹)')
                            ->disabled(),
                        Forms\Components\TextInput::make('city')
                            ->disabled(),
                        Forms\Components\TextInput::make('address')
                            ->disabled(),
                        Forms\Components\TextInput::make('state')
                            ->disabled(),
                        Forms\Components\TextInput::make('country')
                            ->disabled(),
                        Forms\Components\TextInput::make('postal_code')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Professional Details')
                    ->schema([
                        Forms\Components\TextInput::make('experience_years')
                            ->label('Years of Experience')
                            ->disabled(),
                        Forms\Components\Select::make('teaching_mode')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'both' => 'Both',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('rating_avg')
                            ->label('Average Rating')
                            ->disabled(),
                        Forms\Components\TextInput::make('rating_count')
                            ->label('Number of Ratings')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Moderation Status')
                    ->schema([
                        Forms\Components\Select::make('moderation_status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(),
                        Forms\Components\Placeholder::make('reviewedBy.name')
                            ->label('Reviewed By')
                            ->content(fn () => $this->record?->reviewedBy?->name ?? '-'),
                        Forms\Components\TextInput::make('reviewed_at')
                            ->label('Reviewed At')
                            ->disabled(),
                        Forms\Components\Placeholder::make('account_status')
                            ->label('Account Status')
                            ->content(fn () => $this->record?->is_disabled ? 'Disabled' : 'Active'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Rejection Details')
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\Textarea::make('rejection_notes')
                            ->label('Admin Notes')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->moderation_status === 'rejected'),

                Forms\Components\Section::make('Moderation History')
                    ->schema([
                        Forms\Components\Repeater::make('moderationActions')
                            ->relationship('moderationActions')
                            ->disabled()
                            ->schema([
                                Forms\Components\TextInput::make('admin.name')
                                    ->label('Admin')
                                    ->disabled(),
                                Forms\Components\TextInput::make('action')
                                    ->disabled(),
                                Forms\Components\Textarea::make('reason')
                                    ->disabled()
                                    ->rows(2),
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Date/Time')
                                    ->disabled(),
                            ])
                            ->columns(2)
                    ])
                    ->visible(fn ($record) => $record && $record->moderationActions()->exists()),

               
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Tutor')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->moderation_status !== 'approved')
                ->requiresConfirmation()
                ->modalHeading('Approve Tutor Profile')
                ->modalDescription('Are you sure you want to approve this tutor profile?')
                ->action(function (Tutor $record) {
                    $admin = auth()->user();
                    
                    $record->update([
                        'moderation_status' => 'approved',
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ]);

                    TutorModerationAction::create([
                        'tutor_id' => $record->id,
                        'admin_id' => $admin->id,
                        'action' => 'approve',
                        'old_status' => 'pending',
                        'new_status' => 'approved',
                    ]);

                    // Notify tutor
                    $record->user->notify(new TutorApprovalNotification($record));
                    
                    $this->dispatch('notify', message: 'Tutor approved successfully!');
                }),

            Actions\Action::make('disable')
                ->label('Disable Tutor')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn () => !$this->record->is_disabled)
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Disable Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $admin = auth()->user();
                    $record = $this->record;

                    $record->update([
                        'is_disabled' => true,
                        'disabled_reason' => $data['reason'],
                        'disabled_by' => $admin?->id,
                        'disabled_at' => now(),
                    ]);

                    $record->user?->update([
                        'is_disabled' => true,
                        'disabled_reason' => $data['reason'],
                        'disabled_by' => $admin?->id,
                        'disabled_at' => now(),
                    ]);

                    $this->dispatch('notify', message: 'Tutor disabled. User will see contact admin notice.');
                }),

            Actions\Action::make('enable')
                ->label('Enable Tutor')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->is_disabled)
                ->requiresConfirmation()
                ->action(function () {
                    $record = $this->record;

                    $record->update([
                        'is_disabled' => false,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    $record->user?->update([
                        'is_disabled' => false,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    $this->dispatch('notify', message: 'Tutor re-enabled.');
                }),

            Actions\Action::make('reject')
                ->label('Reject Tutor')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn () => $this->record->moderation_status !== 'rejected')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(4)
                        ->helperText('Provide a clear reason for rejection to help the tutor improve their profile.'),
                    Forms\Components\Textarea::make('rejection_notes')
                        ->label('Additional Notes (Optional)')
                        ->rows(3)
                        ->helperText('Internal notes for admin reference.'),
                ])
                ->action(function (array $data) {
                    $admin = auth()->user();
                    $record = $this->record;

                    $record->update([
                        'moderation_status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'rejection_notes' => $data['rejection_notes'] ?? null,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ]);

                    TutorModerationAction::create([
                        'tutor_id' => $record->id,
                        'admin_id' => $admin->id,
                        'action' => 'reject',
                        'reason' => $data['rejection_reason'],
                        'notes' => $data['rejection_notes'] ?? null,
                        'old_status' => 'pending',
                        'new_status' => 'rejected',
                    ]);

                    // Notify tutor with rejection reason
                    $record->user->notify(new TutorRejectionNotification(
                        $record,
                        $data['rejection_reason']
                    ));
                    
                    $this->dispatch('notify', message: 'Tutor rejected and notified!');
                }),

            Actions\Action::make('viewPDF')
                ->label('Download PDF')
                ->icon('heroicon-o-document-text')
                ->url(fn () => route('admin.tutors.pdf', ['tutor' => $this->record->id]))
                ->openUrlInNewTab(),
        ];
    }
}
