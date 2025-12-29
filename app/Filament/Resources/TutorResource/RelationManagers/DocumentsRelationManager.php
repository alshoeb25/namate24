<?php

namespace App\Filament\Resources\TutorResource\RelationManagers;

use App\Models\TutorDocument;
use App\Models\AdminActionLog;
use App\Notifications\DocumentReviewNotification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Redis;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('document_type')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')->label('Type')->searchable(),
                Tables\Columns\TextColumn::make('file_name')->label('File Name')->toggleable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('File URL')
                    ->url(fn (TutorDocument $record) => url('/storage/' . ltrim($record->file_path, '/')))
                    ->openUrlInNewTab(),
                Tables\Columns\BadgeColumn::make('verification_status')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('rejection_reason')->limit(80)->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('verifiedBy.name')->label('Verified By')->toggleable(),
                Tables\Columns\TextColumn::make('verified_at')->dateTime()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(),
            ])
            ->filters([
                // Add status filters if needed
            ])
            ->headerActions([
                // no create from admin
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (TutorDocument $record) => $record->verification_status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (TutorDocument $record) {
                        $admin = auth()->user();
                        $record->update([
                            'verification_status' => 'approved',
                            'rejection_reason' => null,
                            'verified_by' => $admin?->id,
                            'verified_at' => now(),
                        ]);
                        if ($record->tutor && $record->tutor->user) {
                            $record->tutor->user->notify(new DocumentReviewNotification($record, 'approved'));
                            $userId = $record->tutor->user->id;
                            Redis::hmset("user:{$userId}:verification", 'docs', 'verified', 'updated_at', now()->toISOString());
                        }
                        AdminActionLog::create([
                            'admin_id' => $admin?->id,
                            'action_type' => 'document_approve',
                            'subject_type' => TutorDocument::class,
                            'subject_id' => $record->id,
                            'metadata' => [
                                'tutor_id' => $record->tutor_id,
                                'document_type' => $record->document_type,
                            ],
                        ]);
                        Redis::rpush('admin:actions', json_encode([
                            'ts' => now()->toISOString(),
                            'admin_id' => $admin?->id,
                            'type' => 'document_approve',
                            'subject' => ['type' => 'TutorDocument', 'id' => $record->id],
                        ]));
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (TutorDocument $record) => $record->verification_status !== 'rejected')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Reason')->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (TutorDocument $record, array $data) {
                        $admin = auth()->user();
                        $record->update([
                            'verification_status' => 'rejected',
                            'rejection_reason' => $data['reason'] ?? null,
                            'verified_by' => $admin?->id,
                            'verified_at' => now(),
                        ]);
                        if ($record->tutor && $record->tutor->user) {
                            $record->tutor->user->notify(new DocumentReviewNotification($record, 'rejected', $data['reason'] ?? ''));
                            $userId = $record->tutor->user->id;
                            Redis::hmset("user:{$userId}:verification", 'docs', 'unverified', 'updated_at', now()->toISOString());
                        }
                        AdminActionLog::create([
                            'admin_id' => $admin?->id,
                            'action_type' => 'document_reject',
                            'subject_type' => TutorDocument::class,
                            'subject_id' => $record->id,
                            'metadata' => [
                                'tutor_id' => $record->tutor_id,
                                'document_type' => $record->document_type,
                                'reason' => $data['reason'] ?? null,
                            ],
                        ]);
                        Redis::rpush('admin:actions', json_encode([
                            'ts' => now()->toISOString(),
                            'admin_id' => $admin?->id,
                            'type' => 'document_reject',
                            'subject' => ['type' => 'TutorDocument', 'id' => $record->id],
                        ]));
                    }),
            ])
            ->bulkActions([]);
    }
}
