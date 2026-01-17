<?php

namespace App\Filament\Resources;

use App\Models\ReferralInvite;
use App\Models\ReferralCode;
use App\Jobs\SendReferralInviteEmail;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\ReferralInviteResource\Pages;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ReferralInviteResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = ReferralInvite::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Referrals';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ReferralInvite::class, 'email', ignoreRecord: true)
                    ->disabled(fn ($record) => $record !== null)
                    ->label('Email Address'),

                Select::make('referral_code_id')
                    ->label('Referral Code')
                    ->required()
                    ->helperText('Select a referral code - coins will be automatically assigned')
                    ->allowHtml()
                    ->options(
                        ReferralCode::where('used', false)
                            ->get()
                            ->pluck('referral_code', 'id')
                            ->mapWithKeys(function ($code, $id) {
                                $codeModel = ReferralCode::find($id);
                                return [
                                    $id => "{$code} ({$codeModel->referral_type} - {$codeModel->coins} coins)"
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $code = ReferralCode::find($state);
                            if ($code) {
                                $set('referred_coins', $code->coins);
                            }
                        }
                    }),

                TextInput::make('referred_coins')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->label('Coins to Award')
                    ->helperText('Automatically set from selected referral code'),

                Toggle::make('is_used')
                    ->disabled()
                    ->label('Used / Redeemed'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Email Address')
                    ->icon('heroicon-o-envelope')
                    ->description(fn ($record) => $record->is_used ? 'Joined & Used' : 'Invited'),

                TextColumn::make('referralCode.referral_code')
                    ->label('Voucher Code')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->description(fn ($record) => $record->referralCode ? $record->referralCode->referral_type : ''),

                TextColumn::make('referred_coins')
                    ->numeric()
                    ->sortable()
                    ->label('Coins')
                    ->badge()
                    ->color('success'),

                BadgeColumn::make('email_status')
                    ->label('Email Status')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'queued',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-paper-airplane' => 'queued',
                        'heroicon-o-check-circle' => 'sent',
                        'heroicon-o-x-circle' => 'failed',
                    ]),

                BadgeColumn::make('is_used')
                    ->getStateUsing(fn ($record) => $record->is_used ? 'Joined' : 'Invited')
                    ->colors([
                        'success' => 'Joined',
                        'warning' => 'Invited',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Joined',
                        'heroicon-o-clock' => 'Invited',
                    ])
                    ->label('Status'),

                TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Joined At')
                    ->placeholder('Not yet')
                    ->description('When user registered'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Invited At')
                    ->description('Invite created'),
            ])
            ->filters([
                TernaryFilter::make('is_used')
                    ->label('Redeem Status')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_used', true),
                        false: fn (Builder $query) => $query->where('is_used', false),
                    ),
                SelectFilter::make('email_status')
                    ->label('Email Status')
                    ->options([
                        'pending' => 'Pending',
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading('Referral Invite Details')
                    ->form([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->disabled(),
                        TextInput::make('referralCode.referral_code')
                            ->label('Voucher Code')
                            ->disabled(),
                        TextInput::make('referred_coins')
                            ->label('Coins')
                            ->disabled(),
                        TextInput::make('email_status')
                            ->label('Email Status')
                            ->disabled(),
                        TextInput::make('email_error')
                            ->label('Email Error')
                            ->disabled()
                            ->visible(fn ($record) => !empty($record->email_error))
                            ->columnSpanFull(),
                        Toggle::make('is_used')
                            ->label('User Joined')
                            ->disabled(),
                        TextInput::make('used_at')
                            ->label('Joined At')
                            ->disabled()
                            ->visible(fn ($record) => $record->is_used),
                    ]),
                Action::make('sendEmail')
                    ->label(fn ($record) => $record->email_status === 'sent' ? 'Resend Email' : 'Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color(fn ($record) => $record->email_status === 'sent' ? 'warning' : 'info')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->email_status === 'sent' ? 'Resend Invitation Email' : 'Send Invitation Email')
                    ->modalDescription(fn ($record) => $record->email_status === 'sent' ? "Resend invitation email to {$record->email}?" : "Send invitation email to {$record->email}?")
                    ->modalSubmitActionLabel(fn ($record) => $record->email_status === 'sent' ? 'Resend Email' : 'Send Email')
                    ->visible(fn ($record) => !$record->is_used && in_array($record->email_status, ['pending', 'sent', 'failed', 'queued', null]))
                    ->action(function (ReferralInvite $record) {
                        try {
                            $wasResend = $record->email_status === 'sent';
                            
                            // Update status to queued FIRST
                            $record->update(['email_status' => 'queued', 'email_error' => null]);
                            
                            // Dispatch the job
                            SendReferralInviteEmail::dispatch($record);
                            
                            Notification::make()
                                ->success()
                                ->title($wasResend ? 'Email Resent' : 'Email Queued')
                                ->body(($wasResend ? 'Invitation email for ' : 'Invitation email for ') . "{$record->email} has been queued for sending.")
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Queue Email')
                                ->body("Error: {$e->getMessage()}")
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('sendEmails')
                        ->label('Send Emails')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Send Invitation Emails')
                        ->modalDescription(fn (Collection $records) => "Send invitation emails to {$records->count()} selected recipients?")
                        ->modalSubmitActionLabel('Send Emails')
                        ->action(function (Collection $records) {
                            $sent = 0;
                            $skipped = 0;
                            $failed = 0;
                            
                            foreach ($records as $record) {
                                // Skip if already sent or user already joined
                                if ($record->is_used || in_array($record->email_status, ['sent', 'queued'])) {
                                    $skipped++;
                                    continue;
                                }
                                
                                try {
                                    $record->update(['email_status' => 'queued', 'email_error' => null]);
                                    SendReferralInviteEmail::dispatch($record);
                                    $sent++;
                                } catch (\Throwable $e) {
                                    $failed++;
                                }
                            }
                            
                            $message = [];
                            if ($sent > 0) $message[] = "{$sent} email(s) queued";
                            if ($skipped > 0) $message[] = "{$skipped} skipped (already sent/joined)";
                            if ($failed > 0) $message[] = "{$failed} failed";
                            
                            if ($sent > 0) {
                                Notification::make()
                                    ->success()
                                    ->title('Emails Processing')
                                    ->body(implode(', ', $message))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->warning()
                                    ->title('No Emails Sent')
                                    ->body(implode(', ', $message))
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'referral_invites';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-referral-invites') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create-referral-invites') ?? false;
    }

    public static function canEdit($record = null): bool
    {
        return auth()->user()?->can('edit-referral-invites') ?? false;
    }

    public static function canDelete($record = null): bool
    {
        return auth()->user()?->can('delete-referral-invites') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReferralInvites::route('/'),
            'create' => Pages\CreateReferralInvite::route('/create'),
            'edit' => Pages\EditReferralInvite::route('/{record}/edit'),
            'bulk-upload' => Pages\BulkUploadReferralInvites::route('/bulk-upload'),
            'statistics' => Pages\StatisticsReferralInvites::route('/statistics'),
        ];
    }
}
