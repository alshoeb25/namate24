<?php

namespace App\Filament\Resources\TutorResource\Pages;

use App\Filament\Resources\TutorResource;
use App\Models\Tutor;
use App\Models\TutorModerationAction;
use App\Notifications\TutorApprovalNotification;
use App\Notifications\TutorRejectionNotification;
use App\Jobs\SendTutorApprovalReminderJob;
use App\Services\ElasticService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ViewTutor extends ViewRecord
{
    protected static string $resource = TutorResource::class;

    public function getHeading(): string
    {
        return 'View ' . ($this->record->user?->name ?? 'Tutor');
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        
        // Ensure all relationships are loaded
        $this->record->load([
            'user',
            'reviewedBy',
            'moderationActions.admin',
            'documents',
            'documents.verifiedBy',
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
                Forms\Components\Section::make('Required Fields for Approval')
                    ->schema([
                        Forms\Components\Actions::make([
                                    FormAction::make('sendReminder')
                                ->label('Send reminder email')
                                ->icon('heroicon-o-envelope')
                                ->color('primary')
                                ->visible(fn () => $this->record?->user?->email && !$this->areApprovalRequirementsComplete($this->record))
                                ->action(function () {
                                    $record = $this->record;
                                    if (!$record?->user?->email) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('No email found')
                                            ->body('Tutor does not have an email address to send the reminder to.')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $missing = $this->getApprovalRequirementErrors($record);

                                    if (empty($missing)) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('All requirements complete')
                                            ->body('This tutor already meets all approval requirements.')
                                            ->success()
                                            ->send();
                                        return;
                                    }

                                    $link = url('/tutor/profile/personal-details');
                                    SendTutorApprovalReminderJob::dispatch(
                                        $record->user->email,
                                        $record->user->name ?? 'Tutor',
                                        $missing,
                                        $link
                                    );

                                    \Filament\Notifications\Notification::make()
                                        ->title('Reminder queued')
                                        ->body('Email reminder has been queued to send the missing requirements list.')
                                        ->success()
                                        ->send();
                                }),
                        ])->columnSpanFull(),
                        Forms\Components\Placeholder::make('approval_requirements')
                            ->label('')
                            ->content(fn () => new HtmlString($this->getApprovalRequirementsMessage())),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record && $record->moderation_status !== 'approved'),

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
                        Forms\Components\Placeholder::make('gender')
                            ->label('Gender')
                            ->content(fn () => $this->record?->gender ?? '-'),
                    ])
                    ->columns(2)
                    ->extraAttributes(fn () => [
                        'style' => ($this->record?->user?->name && $this->record?->user?->email) 
                            ? 'border-left: 4px solid #22c55e;' 
                            : 'border-left: 4px solid #9ca3af;'
                    ]),

                Forms\Components\Section::make('Profile Information')
                    ->schema([
                        Forms\Components\Placeholder::make('profile_info')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatProfileInformation())),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(fn () => [
                        'style' => ($this->record?->headline || $this->record?->description || $this->record?->speciality) 
                            ? 'border-left: 4px solid #22c55e;' 
                            : 'border-left: 4px solid #9ca3af;'
                    ]),

                Forms\Components\Section::make('Languages & Opportunities')
                    ->schema([
                        Forms\Components\Placeholder::make('languages')
                            ->label('Languages')
                            ->content(fn () => $this->formatLanguages()),
                        Forms\Components\Placeholder::make('opportunities')
                            ->label('Opportunities')
                            ->content(fn () => $this->formatOpportunities()),
                    ])
                    ->columns(1)
                    ->visible(fn () => true)
                    ->extraAttributes(fn () => [
                        'style' => (!empty($this->record->languages)) 
                            ? 'border-left: 4px solid #22c55e;' 
                            : 'border-left: 4px solid #9ca3af;'
                    ]),

                Forms\Components\Section::make('Teaching Subjects')
                    ->schema([
                        Forms\Components\Placeholder::make('subjects')
                            ->label('Subject Details')
                            ->content(fn () => new HtmlString($this->formatSubjects())),
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => true)
                    ->extraAttributes(fn () => [
                        'style' => ($this->record && $this->record->subjects()->exists()) 
                            ? 'border-left: 4px solid #22c55e;' 
                            : 'border-left: 4px solid #9ca3af;'
                    ]),

                Forms\Components\Section::make('Uploaded Documents')
                    ->schema([
                        Forms\Components\Placeholder::make('documents')
                            ->label('Document Details')
                            ->content(fn () => new HtmlString($this->formatDocuments())),
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => true)
                    ->extraAttributes(fn () => [
                        'style' => ($this->record && $this->record->documents()->exists()) 
                            ? 'border-left: 4px solid #22c55e;' 
                            : 'border-left: 4px solid #9ca3af;'
                    ]),

                Forms\Components\Section::make('Address Information')
                    ->schema([
                        Forms\Components\Placeholder::make('address_info')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatAddressInformation())),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Education')
                    ->schema([
                        Forms\Components\Placeholder::make('educations')
                            ->label('Education Entries')
                            ->content(fn () => new HtmlString($this->formatEducations())),
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => $this->record && is_array($this->record->educations) && count($this->record->educations) > 0),

                Forms\Components\Section::make('Experience')
                    ->schema([
                        Forms\Components\Placeholder::make('experiences')
                            ->label('Experience Entries')
                            ->content(fn () => new HtmlString($this->formatExperiences())),
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => $this->record && is_array($this->record->experiences) && count($this->record->experiences) > 0),

                Forms\Components\Section::make('Teaching Details')
                    ->schema([
                        Forms\Components\Placeholder::make('charges')
                            ->label('Charges')
                            ->content(fn () => $this->formatCharges()),
                        Forms\Components\Placeholder::make('fee_notes')
                            ->label('Fee Notes')
                            ->content(fn () => $this->record?->fee_notes ?? '-'),
                        Forms\Components\Placeholder::make('session_duration')
                            ->label('Session Duration (minutes)')
                            ->content(fn () => $this->record?->session_duration ?? '-'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Professional Details')
                    ->schema([
                        Forms\Components\Placeholder::make('professional_details')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatProfessionalDetails())),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Teaching Preferences')
                    ->schema([
                        Forms\Components\Placeholder::make('teaching_preferences')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatTeachingPreferences())),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Profile Photo & Introduction Video')
                    ->schema([
                        Forms\Components\Placeholder::make('media')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatPhotoAndVideo())),
                        Forms\Components\Actions::make([
                            FormAction::make('approveVideo')
                                ->label('✓ Approve Video')
                                ->icon('heroicon-o-check-circle')
                                ->color('success')
                                ->visible(fn () => ($this->record?->video_approval_status === 'pending' || $this->record?->video_approval_status === null) && ($this->record?->introductory_video || $this->record?->youtube_intro_url))
                                ->action(function () {
                                    $this->record->update([
                                        'video_approval_status' => 'approved',
                                        'video_rejection_reason' => null,
                                    ]);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Video Approved')
                                        ->body('Tutor\'s introduction video has been approved.')
                                        ->success()
                                        ->send();
                                }),
                            FormAction::make('rejectVideo')
                                ->label('✗ Reject Video')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->visible(fn () => $this->record?->video_approval_status !== 'rejected' && ($this->record?->introductory_video || $this->record?->youtube_intro_url))
                                ->form([
                                    Forms\Components\Textarea::make('rejection_reason')
                                        ->label('Rejection Reason')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder('Explain why the video is being rejected...'),
                                ])
                                ->action(function (array $data) {
                                    $this->record->update([
                                        'video_approval_status' => 'rejected',
                                        'video_rejection_reason' => $data['rejection_reason'],
                                    ]);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Video Rejected')
                                        ->body('Tutor has been notified about the rejection.')
                                        ->warning()
                                        ->send();
                                }),
                        ])->columnSpanFull()
                            ->visible(fn () => $this->record?->introductory_video || $this->record?->youtube_intro_url),
                    ])
                    ->columnSpanFull()
                    ->visible(fn () => $this->record?->photo || $this->record?->introductory_video || $this->record?->youtube_intro_url),

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
                        Forms\Components\Placeholder::make('moderation_history')
                            ->label('')
                            ->content(fn () => new HtmlString($this->formatModerationHistory())),
                    ])
                    ->visible(fn ($record) => $record && $record->moderationActions()->exists()),
            ]);
    }

    private function formatCharges(): string
    {
        $minFee = $this->record?->min_fee;
        $maxFee = $this->record?->max_fee;
        $chargeType = $this->record?->charge_type ?? 'Per Hour';
        
        if (!$minFee && !$maxFee) {
            return '-';
        }
        
        if ($minFee && $maxFee) {
            return sprintf('₹%s - ₹%s (%s)', number_format($minFee, 2), number_format($maxFee, 2), $chargeType);
        } elseif ($minFee) {
            return sprintf('₹%s (%s)', number_format($minFee, 2), $chargeType);
        } else {
            return sprintf('₹%s (%s)', number_format($maxFee, 2), $chargeType);
        }
    }
    private function formatProfileInformation(): string
    {
        $html = '<ul style="margin: 0; padding-left: 20px;">';
        
        if ($this->record?->headline) {
            $html .= sprintf('<li><strong>Headline:</strong> %s</li>', htmlspecialchars($this->record->headline));
        }
        if ($this->record?->description) {
            $html .= sprintf('<li><strong>Profile Description:</strong> %s</li>', htmlspecialchars($this->record->description));
        }
        if ($this->record?->speciality) {
            $html .= sprintf('<li><strong>Speciality:</strong> %s</li>', htmlspecialchars($this->record->speciality));
        }
        if ($this->record?->strength) {
            $html .= sprintf('<li><strong>My Strengths:</strong> %s</li>', htmlspecialchars($this->record->strength));
        }
        if ($this->record?->current_role) {
            $html .= sprintf('<li><strong>Current Role:</strong> %s</li>', htmlspecialchars($this->record->current_role));
        }
        
        $html .= '</ul>';
        return $html;
    }
    private function getMonthName($monthNumber): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$monthNumber] ?? $monthNumber;
    }

    private function formatEducations(): string
    {
        $educations = $this->record->educations ?? [];
        if (empty($educations)) {
            return '-';
        }
        
        $html = '';
        foreach ($educations as $index => $edu) {
            $degree = $edu['degree'] ?? '';
            $degreeType = $edu['degree_type'] ?? '';
            $institution = $edu['institution'] ?? '';
            $city = $edu['city'] ?? '';
            $field = $edu['field_of_study'] ?? '';
            $mode = $edu['study_mode'] ?? '';
            $startMonth = $edu['start_month'] ?? '';
            $startYear = $edu['start_year'] ?? '';
            $endMonth = $edu['end_month'] ?? '';
            $endYear = $edu['end_year'] ?? '';
            $score = $edu['score'] ?? '';
            
            $title = '';
            if ($degree && $degreeType) {
                $title = "$degree ($degreeType)";
            } elseif ($degree) {
                $title = $degree;
            }
            
            $institutionInfo = '';
            if ($institution || $city) {
                $institutionInfo = trim(($institution ?? '') . (($institution && $city) ? ', ' : '') . ($city ?? ''));
            }
            
            $fieldInfo = '';
            if ($field || $mode) {
                $fieldInfo = ($field ? "Field: $field" : '') . (($field && $mode) ? ' | ' : '') . ($mode ? "Mode: $mode" : '');
            }
            
            $duration = '';
            if ($startMonth || $startYear || $endMonth || $endYear) {
                $start = '';
                if ($startMonth) {
                    $start = $this->getMonthName((int)$startMonth);
                }
                if ($startYear) {
                    $start = trim($start . ' ' . $startYear);
                }
                
                $end = '';
                if ($endMonth || $endYear) {
                    if ($endMonth) {
                        $end = $this->getMonthName((int)$endMonth);
                    }
                    if ($endYear) {
                        $end = trim($end . ' ' . $endYear);
                    }
                } else {
                    $end = 'Current';
                }
                
                $duration = "Duration: " . ($start ?: 'Start date pending') . " - " . $end;
            }
            
            $scoreInfo = $score ? "<br>Score: $score" : '';
            
            $html .= "<div style='margin-bottom: 15px; padding: 10px; border-left: 3px solid #007bff; background: #f8f9fa;'>";
            $html .= "<strong>#" . ($index + 1) . ("$title" ? " - $title" : '') . "</strong><br>";
            if ($institutionInfo) {
                $html .= "Institution: $institutionInfo<br>";
            }
            if ($fieldInfo) {
                $html .= "$fieldInfo<br>";
            }
            if ($duration) {
                $html .= "$duration";
            }
            $html .= "$scoreInfo</div>";
        }
        return $html;
    }

    private function formatExperiences(): string
    {
        $experiences = $this->record->experiences ?? [];
        if (empty($experiences)) {
            return '-';
        }
        
        $html = '';
        foreach ($experiences as $index => $exp) {
            $title = $exp['title'] ?? '';
            $company = $exp['company'] ?? '';
            $designation = $exp['designation'] ?? '';
            $association = $exp['association'] ?? '';
            $startMonth = $exp['start_month'] ?? '';
            $startYear = $exp['start_year'] ?? '';
            $endMonth = $exp['end_month'] ?? '';
            $endYear = $exp['end_year'] ?? '';
            $roles = $exp['roles'] ?? '';
            
            $titleInfo = '';
            if ($title && $company) {
                $titleInfo = "$title at $company";
            } elseif ($title) {
                $titleInfo = $title;
            }
            
            $detailsInfo = '';
            if ($designation || $association) {
                $detailsInfo = ($designation ? "Designation: $designation" : '') . (($designation && $association) ? ' | ' : '') . ($association ? "Type: $association" : '');
            }
            
            $duration = '';
            if ($startMonth || $startYear || $endMonth || $endYear) {
                $start = '';
                if ($startMonth) {
                    $start = $this->getMonthName((int)$startMonth);
                }
                if ($startYear) {
                    $start = trim($start . ' ' . $startYear);
                }
                
                $end = '';
                if ($endMonth || $endYear) {
                    if ($endMonth) {
                        $end = $this->getMonthName((int)$endMonth);
                    }
                    if ($endYear) {
                        $end = trim($end . ' ' . $endYear);
                    }
                } else {
                    $end = 'Current';
                }
                
                $duration = "Duration: " . ($start ?: 'Start date pending') . " - " . $end;
            }
            
            $rolesInfo = $roles ? "<br>Roles: $roles" : '';
            
            $html .= "<div style='margin-bottom: 15px; padding: 10px; border-left: 3px solid #28a745; background: #f8f9fa;'>";
            $html .= "<strong>#" . ($index + 1) . ("$titleInfo" ? " - $titleInfo" : '') . "</strong><br>";
            if ($detailsInfo) {
                $html .= "$detailsInfo<br>";
            }
            if ($duration) {
                $html .= "$duration";
            }
            $html .= "$rolesInfo</div>";
        }
        return $html;
    }
    private function formatAddressInformation(): string
    {
        $parts = [];
        
        if ($this->record?->address) {
            $parts[] = htmlspecialchars($this->record->address);
        }
        if ($this->record?->area) {
            $parts[] = htmlspecialchars($this->record->area);
        }
        if ($this->record?->city) {
            $parts[] = htmlspecialchars($this->record->city);
        }
        if ($this->record?->state) {
            $parts[] = htmlspecialchars($this->record->state);
        }
        if ($this->record?->postal_code) {
            $parts[] = htmlspecialchars($this->record->postal_code);
        }
        if ($this->record?->country) {
            $parts[] = htmlspecialchars($this->record->country);
        }
        
        if (empty($parts)) {
            return '-';
        }
        
        return implode('<br>', $parts);
    }
    private function formatCourses(): string
    {
        $courses = $this->record->courses ?? [];
        if (empty($courses)) {
            return '-';
        }
        
        $html = '';
        foreach ($courses as $index => $course) {
            $languages = is_array($course['languages'] ?? null) ? implode(', ', $course['languages']) : '-';
            $html .= sprintf(
                "<div style='margin-bottom: 15px; padding: 10px; border-left: 3px solid #fd7e14; background: #f8f9fa;'>" .
                "<strong>#%d - %s</strong><br>" .
                "Price: %s | Mode: %s<br>" .
                "Duration: %s | Languages: %s<br>" .
                "Description: %s%s" .
                "</div>",
                $index + 1,
                $course['title'] ?? 'N/A',
                isset($course['price']) ? '₹' . number_format($course['price'], 2) : 'N/A',
                $course['mode_of_delivery'] ?? 'N/A',
                isset($course['duration']) ? $course['duration'] . ' ' . ($course['duration_unit'] ?? '') : 'N/A',
                $languages,
                substr($course['description'] ?? 'N/A', 0, 100),
                isset($course['certificate']) ? "<br>Certificate: {$course['certificate']}" : ''
            );
        }
        return $html;
    }

    private function formatTeachingMode(): string
    {
        $mode = $this->record->teaching_mode;
        if (is_array($mode)) {
            return implode(', ', array_map('ucfirst', $mode));
        }
        return $mode ? ucfirst($mode) : '-';
    }

    private function formatLanguages(): string
    {
        $languages = $this->record->languages ?? [];
        if (empty($languages)) {
            return '-';
        }
        return implode(', ', $languages);
    }

    private function formatOpportunities(): string
    {
        $opportunities = $this->record->opportunities ?? [];
        if (empty($opportunities)) {
            return '-';
        }
        
        if (count($opportunities) === 1) {
            return $opportunities[0];
        }
        
        $last = array_pop($opportunities);
        return implode(', ', $opportunities) . ' and ' . $last;
    }

    private function formatDocuments(): string
    {
        $documents = $this->record->documents ?? [];
        if (empty($documents)) {
            return '-';
        }
        
        $html = '';
        $statusColors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];
        
        foreach ($documents as $index => $doc) {
            $statusColor = $statusColors[$doc->verification_status] ?? 'secondary';
            $statusBadge = sprintf(
                "<span style='display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; background-color: %s;'>%s</span>",
                match($statusColor) {
                    'warning' => '#ffc107',
                    'success' => '#28a745',
                    'danger' => '#dc3545',
                    default => '#6c757d'
                },
                ucfirst($doc->verification_status)
            );

            // Resolve a downloadable URL for the document file
            $path = $doc->file_path ?? '';
            $fileUrl = '#';
            if ($path) {
                // If already an absolute URL, use as-is; otherwise generate public disk URL
                if (preg_match('#^https?://#i', $path)) {
                    $fileUrl = $path;
                } else {
                    $fileUrl = Storage::disk('public')->url($path);
                }
            }
            
            $html .= sprintf(
                "<div style='margin-bottom: 15px; padding: 10px; border-left: 3px solid %s; background: #f8f9fa;'>" .
                "<strong>#%d - %s</strong> %s<br>" .
                "Type: %s<br>" .
                "File: <a href='%s' target='_blank' rel='noopener noreferrer'>%s</a><br>" .
                "%s%s" .
                "</div>",
                match($statusColor) {
                    'warning' => '#ffc107',
                    'success' => '#28a745',
                    'danger' => '#dc3545',
                    default => '#6c757d'
                },
                $index + 1,
                $doc->document_type ?? 'N/A',
                $statusBadge,
                ucfirst(str_replace('_', ' ', $doc->document_type ?? 'N/A')),
                $fileUrl,
                $doc->file_name ?? 'Download',
                $doc->verifiedBy?->name ? "Verified By: {$doc->verifiedBy->name}<br>" : '',
                $doc->verified_at ? "Verified At: " . $doc->verified_at->format('d M Y H:i') : ''
            );
        }
        return $html;
    }

    private function formatProfessionalDetails(): string
    {
        $html = '<ul style="margin: 0; padding-left: 20px;">';
        
        if ($this->record?->experience_years) {
            $html .= sprintf('<li><strong>Years of Experience:</strong> %s</li>', $this->record->experience_years);
        }
        if ($this->record?->experience_total_years) {
            $html .= sprintf('<li><strong>Total Years of Experience:</strong> %s</li>', $this->record->experience_total_years);
        }
        if ($this->record?->experience_teaching_years) {
            $html .= sprintf('<li><strong>Teaching Years:</strong> %s</li>', $this->record->experience_teaching_years);
        }
        if ($this->record?->experience_online_years) {
            $html .= sprintf('<li><strong>Online Teaching Years:</strong> %s</li>', $this->record->experience_online_years);
        }
        if ($this->record?->teaching_mode) {
            $html .= sprintf('<li><strong>Teaching Mode:</strong> %s</li>', $this->formatTeachingMode());
        }
        if ($this->record?->rating_avg) {
            $html .= sprintf('<li><strong>Average Rating:</strong> %s</li>', number_format($this->record->rating_avg, 2));
        }
        if ($this->record?->rating_count) {
            $html .= sprintf('<li><strong>Number of Ratings:</strong> %s</li>', $this->record->rating_count);
        }
        
        $html .= '</ul>';
        return $html;
    }

    private function formatTeachingPreferences(): string
    {
        $html = '<ul style="margin: 0; padding-left: 20px;">';
        
        if ($this->record?->teaching_style) {
            $html .= sprintf('<li><strong>Teaching Style:</strong> %s</li>', $this->record->teaching_style);
        }
        $html .= sprintf('<li><strong>Willing to Travel:</strong> %s</li>', $this->record?->travel_willing ? '✅ Yes' : '❌ No');
        
        if ($this->record?->travel_distance_km) {
            $html .= sprintf('<li><strong>Travel Distance:</strong> %s km</li>', $this->record->travel_distance_km);
        }
        $html .= sprintf('<li><strong>Online Available:</strong> %s</li>', $this->record?->online_available ? '✅ Yes' : '❌ No');
        $html .= sprintf('<li><strong>Has Digital Pen:</strong> %s</li>', $this->record?->has_digital_pen ? '✅ Yes' : '❌ No');
        $html .= sprintf('<li><strong>Helps with Homework:</strong> %s</li>', $this->record?->helps_homework ? '✅ Yes' : '❌ No');
        $html .= sprintf('<li><strong>Employed Full Time:</strong> %s</li>', $this->record?->employed_full_time ? '✅ Yes' : '❌ No');
        
        $html .= '</ul>';
        return $html;
    }

    private function formatPhotoAndVideo(): string
    {
        $html = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">';
        
        // Profile Photo
        $html .= '<div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #007bff;">';
        $html .= '<h4 style="margin-top: 0; color: #007bff;">Profile Photo</h4>';
        
        if ($this->record?->photo) {
            $photoUrl = asset('storage/' . $this->record->photo);
            
            // Show thumbnail
            $html .= sprintf(
                '<img src="%s" alt="Profile Photo" style="max-width: 150px; max-height: 150px; border-radius: 6px; object-fit: cover; display: block; margin: 10px 0;">',
                htmlspecialchars($photoUrl)
            );
            
            // Show link to open in new tab
            $html .= sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 500;">
                    <span style="margin-right: 6px;">🔗</span> Open Photo in New Tab
                </a>',
                htmlspecialchars($photoUrl)
            );
            
            $html .= sprintf('<p style="font-size: 12px; color: #666; margin: 10px 0 0 0;">📁 File: %s</p>', htmlspecialchars($this->record->photo));
        } else {
            $html .= '<p style="color: #999; font-style: italic;">No profile photo uploaded</p>';
        }
        
        $html .= '</div>';
        
        // Introduction Video
        $html .= '<div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #9333ea;">';
        $html .= '<h4 style="margin-top: 0; color: #9333ea;">Introduction Video</h4>';
        
        // Approval Status Badge
        if ($this->record?->video_approval_status || $this->record?->introductory_video || $this->record?->youtube_intro_url) {
            $status = $this->record->video_approval_status ?? 'pending';
            $statusColor = match($status) {
                'approved' => '#22c55e',
                'rejected' => '#ef4444',
                'pending' => '#f59e0b',
                default => '#f59e0b'
            };
            $statusText = ucfirst($status);
            $html .= sprintf(
                '<div style="display: inline-block; padding: 6px 12px; background: %s; color: white; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 10px;">%s</div>',
                $statusColor,
                $statusText
            );
        }
        
        if ($this->record?->introductory_video) {
            $videoUrl = asset('storage/' . $this->record->introductory_video);
            
            // Show link to open video in new tab
            $html .= sprintf(
                '<div style="margin: 15px 0;">
                    <a href="%s" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 10px 20px; background: #9333ea; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                        <span style="margin-right: 8px;">🎥</span> Open Video in New Tab
                    </a>
                </div>',
                htmlspecialchars($videoUrl)
            );
            
            $html .= sprintf('<p style="font-size: 12px; color: #666; margin: 10px 0 0 0;">📁 File: %s</p>', htmlspecialchars($this->record->introductory_video));
        } elseif ($this->record?->youtube_intro_url) {
            // Show link to YouTube video
            $html .= sprintf(
                '<div style="margin: 15px 0;">
                    <a href="%s" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 10px 20px; background: #ff0000; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
                        <span style="margin-right: 8px;">▶️</span> Open YouTube Video
                    </a>
                </div>',
                htmlspecialchars($this->record->youtube_intro_url)
            );
            
            $html .= sprintf(
                '<p style="font-size: 12px; color: #666; margin: 10px 0 0 0;">🔗 YouTube: <a href="%s" target="_blank" style="color: #9333ea; text-decoration: none;">%s</a></p>',
                htmlspecialchars($this->record->youtube_intro_url),
                htmlspecialchars($this->record->youtube_intro_url)
            );
        } else {
            $html .= '<p style="color: #999; font-style: italic;">No introduction video uploaded</p>';
        }
        
        if ($this->record?->video_title) {
            $html .= sprintf('<p style="font-size: 13px; margin: 8px 0 0 0;"><strong>Title:</strong> %s</p>', htmlspecialchars($this->record->video_title));
        }
        
        // Show rejection reason if rejected
        if ($this->record?->video_approval_status === 'rejected' && $this->record?->video_rejection_reason) {
            $html .= sprintf(
                '<div style="margin-top: 15px; padding: 10px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; color: #991b1b;"><strong>Rejection Reason:</strong><br>%s</div>',
                htmlspecialchars($this->record->video_rejection_reason)
            );
        }
        
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }

    private function formatSubjects(): string
    {
        $subjects = $this->record->subjects ?? [];
        if (empty($subjects)) {
            return '-';
        }
        
        $html = '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($subjects as $subject) {
            $fromLevel = $subject->pivot->from_level ?? '-';
            $toLevel = $subject->pivot->to_level ?? '-';
            $html .= sprintf(
                '<li><strong>%s</strong> (Level: %s to %s)</li>',
                $subject->name ?? 'N/A',
                $fromLevel,
                $toLevel
            );
        }
        $html .= '</ul>';
        return $html;
    }

    private function formatModerationHistory(): string
    {
        $actions = $this->record->moderationActions()->orderBy('created_at', 'desc')->get();
        if ($actions->isEmpty()) {
            return '-';
        }
        
        $html = '<div style="font-family: monospace;">';
        foreach ($actions as $action) {
            $time = $action->created_at->format('d M Y H:i');
            $adminName = $action->admin?->name ?? 'System';
            $actionType = ucfirst($action->action);
            $reason = $action->reason ? ' - ' . $action->reason : '';
            
            $html .= sprintf(
                '<div style="margin-bottom: 10px; padding: 8px; background: #f8f9fa; border-left: 3px solid #6c757d;">%s: <strong>%s</strong> %s%s</div>',
                $time,
                $adminName,
                $actionType,
                $reason
            );
        }
        $html .= '</div>';
        
        return $html;
    }

    private function getApprovalRequirementsMessage(): string
    {
        $requirements = [
            [
                'label' => 'Name',
                'status' => (bool) $this->record?->user?->name,
                'value' => $this->record?->user?->name ?? 'Missing'
            ],
            [
                'label' => 'Email',
                'status' => (bool) $this->record?->user?->email,
                'value' => $this->record?->user?->email ?? 'Missing'
            ],
            [
                'label' => 'Subjects',
                'status' => $this->record && $this->record->subjects()->exists(),
                'value' => $this->record && $this->record->subjects()->exists() ? $this->record->subjects()->count() . ' subject(s)' : 'No subjects added'
            ],
            [
                'label' => 'Languages',
                'status' => !empty($this->record->languages),
                'value' => !empty($this->record->languages) ? implode(', ', $this->record->languages) : 'No languages added'
            ],
            [
                'label' => 'Uploaded Documents',
                'status' => $this->record && $this->record->documents()->exists(),
                'value' => $this->record && $this->record->documents()->exists() ? $this->record->documents()->count() . ' document(s)' : 'No documents uploaded'
            ],
            [
                'label' => 'Profile Information',
                'status' => (bool) ($this->record?->headline || $this->record?->description || $this->record?->speciality),
                'value' => ($this->record?->headline || $this->record?->description || $this->record?->speciality) ? 'Completed' : 'Headline, Description, or Speciality required'
            ],
        ];

        $html = '<div style="padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">';
        $html .= '<p style="margin-bottom: 12px; font-weight: bold; color: #495057; font-size: 14px;">✓ Required fields for approval:</p>';
        $html .= '<ul style="margin: 0; padding-left: 20px; list-style: none;">';
        
        foreach ($requirements as $req) {
            $icon = $req['status'] ? '✅' : '❌';
            $color = $req['status'] ? '#28a745' : '#dc3545';
            $html .= sprintf(
                '<li style="margin-bottom: 8px; color: %s;"><strong>%s %s:</strong> <span style="color: #6c757d;">%s</span></li>',
                $color,
                $icon,
                $req['label'],
                htmlspecialchars($req['value'])
            );
        }
        
        $html .= '</ul>';
        
        $allComplete = array_reduce($requirements, fn($carry, $req) => $carry && $req['status'], true);
        
        if ($allComplete) {
            $html .= '<p style="margin-top: 12px; padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; border: 1px solid #c3e6cb; font-weight: bold;">✓ All required fields are complete. This tutor can be approved.</p>';
        } else {
            $html .= '<p style="margin-top: 12px; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; border: 1px solid #f5c6cb; font-weight: bold;">⚠ Some required fields are missing. </p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private function getApprovalRequirementErrors(Tutor $record): array
    {
        $errors = [];
        
        if (!$record->user?->name) {
            $errors[] = 'Name is required';
        }
        if (!$record->user?->email) {
            $errors[] = 'Email is required';
        }
        if (!$record->subjects()->exists()) {
            $errors[] = 'At least one subject is required';
        }
        if (empty($record->languages)) {
            $errors[] = 'At least one language is required';
        }
        if (!$record->documents()->exists()) {
            $errors[] = 'At least one document must be uploaded';
        }
        if (!$record->headline && !$record->description && !$record->speciality) {
            $errors[] = 'Profile information (headline, description, or speciality) is required';
        }

        return $errors;
    }

    private function areApprovalRequirementsComplete(Tutor $record): bool
    {
        return empty($this->getApprovalRequirementErrors($record));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn () => $this->record->moderation_status !== 'approved')
                ->form([
                    Forms\Components\Textarea::make('approval_reason')
                        ->label('Approval Message (Optional)')
                        ->placeholder('Add a personalized message to the tutor')
                        ->rows(3),
                ])
                ->modalHeading('Approve Tutor Profile')
                ->modalDescription('Are you sure you want to approve this tutor profile?')
                ->action(function (Tutor $record, array $data) {
                    // Validate required fields before approval
                    $errors = $this->getApprovalRequirementErrors($record);
                    
                    if (!empty($errors)) {
                        // If tutor has an email, queue a reminder with missing items and profile link
                        if ($record->user?->email) {
                            $link = url('/tutor/profile/personal-details');
                            SendTutorApprovalReminderJob::dispatch(
                                $record->user->email,
                                $record->user->name ?? 'Tutor',
                                $errors,
                                $link
                            );
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Cannot Approve Tutor')
                            ->body('The following fields are missing:<br>' . implode('<br>', array_map(fn($e) => '• ' . $e, $errors)))
                            ->danger()
                            ->persistent()
                            ->send();
                        return;
                    }
                    
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

                    // Notify tutor with approval reason
                    $record->user->notify(new TutorApprovalNotification($record, $data['approval_reason'] ?? null));
                    
                    // Index tutor in Elasticsearch
                    try {
                        $elasticService = app(ElasticService::class);
                        $client = $elasticService->client();
                        $record->load('user', 'subjects');
                        $client->index([
                            'index' => 'tutors',
                            'id' => $record->id,
                            'body' => $record->toElasticArray(),
                            'refresh' => true
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to index tutor in Elasticsearch: ' . $e->getMessage());
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Tutor Approved')
                        ->body($record->user->name . ' has been approved successfully!')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('disable')
                ->label('Disable')
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
                ->label('Enable')
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
                ->label('Reject')
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

                    // Remove tutor from Elasticsearch
                    try {
                        $elasticService = app(ElasticService::class);
                        $client = $elasticService->client();
                        $client->delete([
                            'index' => 'tutors',
                            'id' => $record->id,
                            'refresh' => true
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to remove tutor from Elasticsearch: ' . $e->getMessage());
                    }
                    
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
