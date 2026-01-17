<?php

namespace App\Filament\Resources\ReferralInviteResource\Pages;

use App\Filament\Resources\ReferralInviteResource;
use App\Jobs\SendReferralInviteEmail;
use App\Models\ReferralCode;
use App\Models\ReferralInvite;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Components\{
    Select,
    FileUpload,
    Textarea,
    Toggle
};
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BulkUploadReferralInvites extends Page
{
    protected static string $resource = ReferralInviteResource::class;
    protected static string $view = 'filament.pages.bulk-upload-referral-invites';
    protected static bool $shouldRegisterNavigation = false;

    protected ?string $heading = 'Bulk Upload Referral Invites';
    protected ?string $subheading = 'Upload a CSV and/or add emails manually, then review before sending';

    public ?array $data = [];

    public bool $showUploadResults = false;
    public bool $isProcessing = false;

    public array $uploadResults = [];

    public int $total = 0;
    public int $processed = 0;
    public function mount(): void
    {
        $this->form->fill();
    }

    /* ============================================================
     | FORM
     |============================================================ */

    public function form(Form $form): Form
    {
        $codes = ReferralCode::where('used', false)
            ->get()
            ->mapWithKeys(fn ($c) => [
                $c->id => "{$c->referral_code} ({$c->coins} coins)"
            ]);

        return $form
            ->schema([
                Select::make('referral_code_id')
                    ->label('Referral Code')
                    ->options($codes)
                    ->required()
                    ->live(),

                FileUpload::make('csvFile')
                    ->label('Upload CSV File (optional)')
                    ->acceptedFileTypes(['text/csv', 'application/csv'])
                    ->multiple(false)
                    ->disk('public')
                    ->directory('promotional')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if (empty($state)) {
                            return;
                        }
                        \Log::debug('CSV uploaded, state:', ['state' => $state]);
                        $this->loadCsvIntoManual($state);
                    }),

                Textarea::make('entries')
                    ->label('Email Addresses')
                    ->rows(10)
                    ->placeholder("user1@example.com\nuser2@example.com")
                    ->required()
                    ->live(),

                Toggle::make('sendEmails')
                    ->label('Send emails after creating invites')
                    ->default(true),
            ])
            ->statePath('data');
    }

    /* ============================================================
     | ACTIONS
     |============================================================ */

    protected function getFormActions(): array
    {
        $hasReferralCode = !empty($this->data['referral_code_id'] ?? null);
        $hasEntries = strlen(trim((string) ($this->data['entries'] ?? ''))) > 0;

        $actions = [
            Action::make('downloadSample')
                ->label('Download Sample CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('downloadSampleCsv'),
        ];

        // Only show Send Invites button if both referral code and entries exist
        if ($hasReferralCode && $hasEntries) {
            $actions[] = Action::make('sendInvites')
                ->label($this->isProcessing ? 'Sending...' : '✉️ Send Invites')
                ->icon($this->isProcessing ? 'heroicon-o-arrow-path' : 'heroicon-o-paper-airplane')
                ->color('primary')
                ->disabled($this->isProcessing)
                ->action('handleSendInvites');
        }

        if ($this->showUploadResults) {
            $actions[] = Action::make('refresh')
                ->label('Refresh Page')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action('refreshPage');
        }

        return $actions;
    }

    public function previewInvites(): void
    {
        // Removed - no longer needed
    }

    public function handleSendInvites(): void
    {
        if ($this->isProcessing) {
            return; // Prevent multiple submissions
        }

        $this->isProcessing = true;
        $this->resetResults();

        $code = $this->getSelectedCode();
        if (!$code) {
            $this->isProcessing = false;
            Notification::make()
                ->danger()
                ->title('Please select a referral code before sending invites.')
                ->send();

            return;
        }

        $emails = $this->collectEmails();

        if (empty($emails)) {
            $this->isProcessing = false;
            Notification::make()
                ->danger()
                ->title('Add email addresses to send invites.')
                ->send();

            return;
        }

        $sendEmails = (bool) ($this->data['sendEmails'] ?? true);

        try {
            DB::transaction(function () use ($emails, $code, $sendEmails) {
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->uploadResults['failed']++;
                        continue;
                    }

                    if (ReferralInvite::where('email', $email)->exists()) {
                        $this->uploadResults['duplicates']++;
                        continue;
                    }

                    $invite = ReferralInvite::create([
                        'email' => $email,
                        'referred_coins' => $code->coins,
                        'referral_code_id' => $code->id,
                        'email_status' => $sendEmails ? 'queued' : 'pending',
                    ]);

                    if ($sendEmails) {
                        // Send immediately (synchronous) to avoid relying on a queue worker
                        SendReferralInviteEmail::dispatchSync($invite);
                        $invite->update(['email_status' => 'sent']);
                    }

                    $this->uploadResults['invites'][] = [
                        'email' => $email,
                        'coins' => $code->coins,
                    ];

                    $this->uploadResults['success']++;
                }
            });

            $this->isProcessing = false;
            $this->showUploadResults = true;

            Notification::make()
                ->success()
                ->title('Invites processed successfully!')
                ->body($this->uploadResults['success'] . ' invites created. Emails are being sent.')
                ->send();

            // Redirect to referral invites list page
            redirect()->route('filament.admin.resources.referral-invites.index');
        } catch (\Exception $e) {
            $this->isProcessing = false;
            \Log::error('Error sending invites:', ['error' => $e->getMessage()]);
            
            Notification::make()
                ->danger()
                ->title('Error processing invites')
                ->body($e->getMessage())
                ->send();
        }
    }

    /* ============================================================
     | HELPERS
     |============================================================ */

    protected function resetResults(): void
    {
        $this->uploadResults = [
            'success' => 0,
            'failed' => 0,
            'duplicates' => 0,
            'invites' => [],
        ];

        $this->showUploadResults = false;
    }

    private function collectEmails(): array
    {
        $emails = [];

        $emails = array_merge($emails, $this->getCsvEmails());

        $manual = (string) ($this->data['entries'] ?? '');
        foreach (preg_split('/[\n;]/', $manual) as $email) {
            $email = trim($email);
            if ($email !== '') {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    private function getSelectedCode(): ?ReferralCode
    {
        $codeId = $this->data['referral_code_id'] ?? null;
        return $codeId ? ReferralCode::find($codeId) : null;
    }

    private function getCsvFilePath($stateOverride = null): ?string
    {
        $csvFile = $stateOverride ?? ($this->data['csvFile'] ?? null);

        \Log::debug('getCsvFilePath - raw state:', ['csvFile' => $csvFile]);

        // Handle serialized TemporaryUploadedFile from Livewire
        if (is_array($csvFile)) {
            // Check if it's the Livewire serialized format
            if (isset($csvFile['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'])) {
                $tempPath = $csvFile['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'];
                \Log::debug('Found temp file path:', ['path' => $tempPath]);
                return is_string($tempPath) && file_exists($tempPath) ? $tempPath : null;
            }

            // If the array is a list, take first item; if associative with 'path', use that.
            if (array_key_exists('path', $csvFile)) {
                $csvFile = $csvFile['path'];
            } else {
                $csvFile = $csvFile[0] ?? null;
                if (is_array($csvFile)) {
                    if (isset($csvFile['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'])) {
                        $tempPath = $csvFile['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'];
                        return is_string($tempPath) && file_exists($tempPath) ? $tempPath : null;
                    }
                    if (array_key_exists('path', $csvFile)) {
                        $csvFile = $csvFile['path'];
                    }
                }
            }
        }

        // Handle Livewire TemporaryUploadedFile object
        if (is_object($csvFile)) {
            if (method_exists($csvFile, 'getRealPath')) {
                return $csvFile->getRealPath();
            }
            if (method_exists($csvFile, 'getPathname')) {
                return $csvFile->getPathname();
            }
            return null;
        }

        return is_string($csvFile) && $csvFile !== '' ? $csvFile : null;
    }

    private function getCsvEmails($stateOverride = null): array
    {
        $emails = [];

        $csvFile = $this->getCsvFilePath($stateOverride);
        if (!$csvFile) {
            \Log::debug('No CSV file path extracted', ['stateOverride' => $stateOverride, 'data' => $this->data['csvFile'] ?? null]);
            return $emails;
        }

        \Log::debug('CSV file resolved to:', ['csvFile' => $csvFile]);

        if (!is_file($csvFile) || !is_readable($csvFile)) {
            \Log::warning('CSV file not readable', ['csvFile' => $csvFile, 'is_file' => is_file($csvFile), 'readable' => is_readable($csvFile)]);
            return $emails;
        }

        $rows = file($csvFile);
        if (empty($rows)) {
            \Log::warning('CSV file is empty', ['csvFile' => $csvFile]);
            return $emails;
        }

        foreach (array_map('str_getcsv', $rows) as $i => $row) {
            if ($i === 0) continue; // Skip header
            $email = trim($row[0] ?? '');
            if ($email !== '') {
                $emails[] = $email;
            }
        }

        \Log::debug('CSV loaded emails:', ['count' => count($emails), 'sample' => array_slice($emails, 0, 5)]);
        return $emails;
    }

    public function loadCsvIntoManual($stateOverride = null): void
    {
        $emails = $this->getCsvEmails($stateOverride);

        if (empty($emails)) {
            Notification::make()
                ->warning()
                ->title('No valid emails found in CSV file.')
                ->send();
            return;
        }

        $manual = (string) ($this->data['entries'] ?? '');
        $manualEmails = [];
        foreach (preg_split('/[\n;]/', $manual) as $email) {
            $email = trim($email);
            if ($email !== '') {
                $manualEmails[] = $email;
            }
        }

        $merged = array_values(array_unique(array_merge($manualEmails, $emails)));

        $this->data['entries'] = implode("\n", $merged);
        $this->form->fill($this->data);

        Notification::make()
            ->success()
            ->title('CSV loaded: ' . count($emails) . ' email(s) added.')
            ->send();
    }

    public function downloadSampleCsv()
    {
        $csv = "Email\n";
        $csv .= "user1@example.com\n";
        $csv .= "user2@example.com\n";
        $csv .= "user3@example.com\n";

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'referral-invites-sample.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function refreshPage(): void
    {
        $this->resetResults();
        $this->data = [];
        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Page refreshed successfully.')
            ->send();
    }
}
