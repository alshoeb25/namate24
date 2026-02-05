<?php

namespace App\Filament\Resources;

use App\Models\StudentRequirement;
use App\Filament\Traits\RoleBasedAccess;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\StudentRequirementResource\Pages;

class StudentRequirementResource extends Resource
{
    use RoleBasedAccess;

    protected static ?string $model = StudentRequirement::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Enquiries';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Student Information
                TextInput::make('student_name')
                    ->label('Student Name')
                    ->default(fn($record) => $record?->student?->name ?? $record?->student_name)
                    ->disabled(),
                TextInput::make('phone')
                    ->label('Phone')
                    ->disabled(),
                TextInput::make('alternate_phone')
                    ->label('Alternate Phone')
                    ->disabled(),
                
                // Subject & Details
                TextInput::make('other_subject')
                    ->label('Subject'),
                Select::make('service_type')
                    ->label('Service Type')
                    ->options([
                        'tutoring' => 'Tutoring',
                        'assignment' => 'Assignment Help',
                        'exam_prep' => 'Exam Preparation',
                    ]),
                Textarea::make('details')
                    ->label('Details')
                    ->columnSpanFull(),
                
                // Location
                TextInput::make('location')
                    ->label('Location'),
                TextInput::make('city')
                    ->label('City'),
                TextInput::make('area')
                    ->label('Area'),
                TextInput::make('pincode')
                    ->label('Pincode'),
                
                // Academic Details
                TextInput::make('class')
                    ->label('Class/Grade'),
                Select::make('level')
                    ->label('Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                        'Expert' => 'Expert',
                    ]),
                
                // Budget
                TextInput::make('budget')
                    ->label('Budget')
                    ->numeric(),
                Select::make('budget_type')
                    ->label('Budget Type')
                    ->options([
                        'per_hour' => 'Per Hour',
                        'per_month' => 'Per Month',
                        'fixed' => 'Fixed',
                    ]),
                
                // Preferences
                Select::make('mode')
                    ->label('Mode')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'both' => 'Both',
                    ]),
                Select::make('gender_preference')
                    ->label('Gender Preference')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'no_preference' => 'No Preference',
                    ]),
                Select::make('availability')
                    ->label('Availability')
                    ->options([
                        'morning' => 'Morning',
                        'afternoon' => 'Afternoon',
                        'evening' => 'Evening',
                        'flexible' => 'Flexible',
                        'full_time' => 'Full Time',
                    ]),
                
                // Status & Lead Management
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'completed' => 'Completed',
                    ]),
                Select::make('lead_status')
                    ->label('Lead Status')
                    ->options([
                        'open' => 'Open',
                        'full' => 'Full',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),
                TextInput::make('current_leads')
                    ->label('Current Leads')
                    ->numeric()
                    ->disabled(),
                TextInput::make('max_leads')
                    ->label('Max Leads')
                    ->numeric(),
                
                // Pricing
                TextInput::make('post_fee')
                    ->label('Post Fee')
                    ->numeric()
                    ->disabled(),
                TextInput::make('unlock_price')
                    ->label('Unlock Price')
                    ->numeric()
                    ->disabled(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Student Information')
                    ->schema([
                        TextEntry::make('student.user.name')
                            ->label('Student Name'),
                        TextEntry::make('phone')
                            ->label('Phone'),
                        TextEntry::make('alternate_phone')
                            ->label('Alternate Phone'),
                    ])
                    ->columns(3),
                
                Section::make('Subject & Details')
                    ->schema([
                        TextEntry::make('subjects.name')
                            ->label('Subjects')
                            ->badge()
                            ->separator(','),
                        TextEntry::make('service_type')
                            ->label('Service Type'),
                        TextEntry::make('details')
                            ->label('Details')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Location')
                    ->schema([
                        TextEntry::make('location')
                            ->label('Location'),
                        TextEntry::make('city')
                            ->label('City'),
                        TextEntry::make('area')
                            ->label('Area'),
                        TextEntry::make('pincode')
                            ->label('Pincode'),
                    ])
                    ->columns(4),
                
                Section::make('Academic Details')
                    ->schema([
                        TextEntry::make('class')
                            ->label('Class/Grade'),
                        TextEntry::make('level')
                            ->label('Level'),
                    ])
                    ->columns(2),
                
                Section::make('Budget & Preferences')
                    ->schema([
                        TextEntry::make('budget')
                            ->label('Budget')
                            ->money('INR'),
                        TextEntry::make('budget_type')
                            ->label('Budget Type'),
                        TextEntry::make('mode')
                            ->label('Mode'),
                        TextEntry::make('gender_preference')
                            ->label('Gender Preference'),
                        TextEntry::make('availability')
                            ->label('Availability'),
                    ])
                    ->columns(3),

                Section::make('Requirement History')
                    ->schema([
                        RepeatableEntry::make('history_items')
                            ->label('')
                            ->state(function (StudentRequirement $record) {
                                $record->loadMissing([
                                    'unlocks.tutor.user',
                                    'unlocks.tutor.subjects',
                                    'approachedTutors.user',
                                    'approachedTutors.subjects',
                                ]);

                                $history = [];
                                $history[] = [
                                    'label' => 'Requirement Created',
                                    'date' => $record->created_at,
                                    'tutor_name' => null,
                                    'tutor_email' => null,
                                    'type' => 'created',
                                ];

                                foreach ($record->unlocks ?? [] as $unlock) {
                                    $tutor = $unlock->tutor;
                                    $tutorUser = $tutor?->user;
                                    $history[] = [
                                        'label' => 'Tutor Unlocked',
                                        'date' => $unlock->created_at,
                                        'tutor_name' => $tutorUser?->name,
                                        'tutor_email' => $tutorUser?->email,
                                        'type' => 'unlock',
                                        'unlock_price' => $unlock->unlock_price,
                                    ];
                                }

                                // Get all approached tutors from the dedicated table
                                $approachedRecords = \DB::table('student_requirement_approached_tutors')
                                    ->where('student_requirement_id', $record->id)
                                    ->orderBy('created_at', 'asc')
                                    ->get();

                                foreach ($approachedRecords as $approached) {
                                    $approachedTutor = \App\Models\Tutor::with('user')->find($approached->tutor_id);
                                    if ($approachedTutor && $approachedTutor->user) {
                                        $approachedUser = $approachedTutor->user;
                                        $history[] = [
                                            'label' => 'Tutor Approached',
                                            'date' => $approached->created_at,
                                            'tutor_name' => $approachedUser?->name,
                                            'tutor_email' => $approachedUser?->email,
                                            'type' => 'approached',
                                            'coins_spent' => $approached->coins_spent,
                                        ];
                                    }
                                }

                                usort($history, function ($a, $b) {
                                    return strtotime($b['date'] ?? 0) <=> strtotime($a['date'] ?? 0);
                                });

                                return $history;
                            })
                            ->schema([
                                TextEntry::make('label')
                                    ->label('Event')
                                    ->badge()
                                    ->color(function ($state): string {
                                        $state = is_array($state) ? $state : [];
                                        return match ($state['type'] ?? null) {
                                        'approached' => 'success',
                                        'unlock' => 'info',
                                        default => 'gray',
                                        };
                                    }),
                                TextEntry::make('date')
                                    ->label('Date')
                                    ->dateTime(),
                                TextEntry::make('tutor_name')
                                    ->label('Tutor')
                                    ->placeholder('—'),
                                TextEntry::make('tutor_email')
                                    ->label('Tutor Email')
                                    ->placeholder('—'),

                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),

                Section::make('Approached Tutors Details')
                    ->schema([
                        RepeatableEntry::make('approachedTutors')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Tutor Name'),
                                TextEntry::make('user.email')
                                    ->label('Tutor Email'),
                                TextEntry::make('user.phone')
                                    ->label('Tutor Phone'),
                                TextEntry::make('rating_avg')
                                    ->label('Rating'),
                                TextEntry::make('pivot.created_at')
                                    ->label('Approached At')
                                    ->dateTime(),
                                TextEntry::make('pivot.coins_spent')
                                    ->label('Coins Spent'),
                            ])
                            ->columns(6)
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible()
                    ->visible(fn (StudentRequirement $record): bool => $record->approachedTutors()->exists()),
                
                Section::make('Status & Lead Management')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('lead_status')
                            ->label('Lead Status')
                            ->badge(),
                        TextEntry::make('current_leads')
                            ->label('Current Leads'),
                        TextEntry::make('max_leads')
                            ->label('Max Leads'),
                        TextEntry::make('post_fee')
                            ->label('Post Fee')
                            ->money('INR'),
                        TextEntry::make('unlock_price')
                            ->label('Unlock Price')
                            ->money('INR'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('current_leads')
                    ->label('Leads'),
                TextColumn::make('max_leads')
                    ->label('Max'),
                TextColumn::make('lead_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'open' => 'info',
                        'full' => 'warning',
                        'closed' => 'danger',
                        'cancelled' => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('lead_status')
                    ->options([
                        'open' => 'Open',
                        'full' => 'Full',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye'),
            ]);
    }

    protected static function getResourcePermissionName(): string
    {
        return 'enquiries';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-enquiries') ?? false;
    }

    public static function canView($record = null): bool
    {
        return auth()->user()?->can('view-enquiries') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Enquiries are created by students
    }

    public static function canEdit($record = null): bool
    {
        return false; // Requirements are read-only
    }

    public static function canDelete($record = null): bool
    {
        return false; // Enquiries should not be deleted
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentRequirements::route('/'),
            'view' => Pages\ViewStudentRequirement::route('/{record}'),
            'edit' => Pages\EditStudentRequirement::route('/{record}/edit'),
        ];
    }
}
