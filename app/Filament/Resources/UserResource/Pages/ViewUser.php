<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->record;

        return [
            Actions\Action::make('disableAccount')
                ->label('Disable Account')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn () => !$record->is_disabled)
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Disable Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record) {
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

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'disable_user_account',
                        'user',
                        $record->id,
                        $data['reason'],
                        [
                            'disabled_tutor' => (bool) $record->tutor,
                            'disabled_student' => (bool) $record->student,
                        ]
                    ));
                }),
            Actions\Action::make('enableAccount')
                ->label('Enable Account')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $record->is_disabled)
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->update([
                        'is_disabled' => null,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'enable_user_account',
                        'user',
                        $record->id
                    ));
                }),
            Actions\Action::make('disableTutor')
                ->label('Disable Tutor')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->visible(fn () => $record->tutor && !$record->tutor->is_disabled)
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Disable Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record) {
                    $record->tutor?->update([
                        'is_disabled' => true,
                        'disabled_reason' => $data['reason'],
                        'disabled_by' => auth()->id(),
                        'disabled_at' => now(),
                    ]);

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'disable_tutor_profile',
                        'tutor',
                        $record->tutor->id,
                        $data['reason'],
                        [
                            'user_id' => $record->id,
                        ]
                    ));
                }),
            Actions\Action::make('enableTutor')
                ->label('Enable Tutor')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->visible(fn () => !$record->is_disabled && $record->tutor && $record->tutor->is_disabled)
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $tutor = $record->tutor;
                    if (!$tutor) {
                        return;
                    }

                    $tutor->update([
                        'is_disabled' => null,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    // Reindex if approved
                    if ($tutor->moderation_status === 'approved') {
                        try {
                            $elasticService = app(\App\Services\ElasticService::class);
                            $client = $elasticService->client();
                            $tutor->load('user', 'subjects');
                            $client->index([
                                'index' => 'tutors',
                                'id' => $tutor->id,
                                'body' => $tutor->toElasticArray(),
                                'refresh' => true
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to reindex tutor in Elasticsearch: ' . $e->getMessage());
                        }
                    }

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'enable_tutor_profile',
                        'tutor',
                        $tutor->id,
                        'Enabled via Users module',
                        [
                            'user_id' => $record->id,
                        ]
                    ));
                }),
            Actions\Action::make('disableStudent')
                ->label('Disable Student')
                ->icon('heroicon-o-user-minus')
                ->color('danger')
                ->visible(fn () => $record->student && !$record->student->is_disabled)
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('Disable Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record) {
                    $record->student?->update([
                        'is_disabled' => true,
                        'disabled_reason' => $data['reason'],
                        'disabled_by' => auth()->id(),
                        'disabled_at' => now(),
                    ]);

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'disable_student_profile',
                        'student',
                        $record->student->id,
                        $data['reason'],
                        [
                            'user_id' => $record->id,
                        ]
                    ));
                }),
            Actions\Action::make('enableStudent')
                ->label('Enable Student')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->visible(fn () => !$record->is_disabled && $record->student && $record->student->is_disabled)
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->student?->update([
                        'is_disabled' => null,
                        'disabled_reason' => null,
                        'disabled_by' => null,
                        'disabled_at' => null,
                    ]);

                    dispatch(new \App\Jobs\AdminLogActivityJob(
                        auth()->id(),
                        'enable_student_profile',
                        'student',
                        $record->student->id,
                        null,
                        [
                            'user_id' => $record->id,
                        ]
                    ));
                }),
        ];
    }
}
