<?php

namespace App\Filament\Resources\TutorReviewResource\Pages;

use App\Filament\Resources\TutorReviewResource;
use App\Models\TutorReview;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTutorReview extends ViewRecord
{
    protected static string $resource = TutorReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (TutorReview $record) {
                    $record->update(['status' => 'approved']);
                    TutorReview::updateTutorRating($record->tutor_id);
                })
                ->visible(fn (TutorReview $record) => $record->status !== 'approved' && (auth()->user()?->can('approve-reviews') ?? false)),
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (TutorReview $record) {
                    $record->update(['status' => 'rejected']);
                    TutorReview::updateTutorRating($record->tutor_id);
                })
                ->visible(fn (TutorReview $record) => $record->status !== 'rejected' && (auth()->user()?->can('reject-reviews') ?? false)),
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Tutor Review')
                    ->schema([
                        Infolists\Components\TextEntry::make('tutor_name')
                            ->label('Tutor')
                            ->state(fn (TutorReview $record) => $record->tutor?->name ?? '—')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('student_name')
                            ->label('Student')
                            ->state(fn (TutorReview $record) => $record->student?->user?->name ?? '—')
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('rating')
                            ->label('Rating')
                            ->badge()
                            ->color(fn (?int $state) => match (true) {
                                $state >= 4 => 'success',
                                $state >= 3 => 'info',
                                default => 'warning',
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('comment')
                            ->label('Comment')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
