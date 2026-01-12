<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        TextInput::make('user_type')->label('User Type')->disabled(),
                    ]),
                Forms\Components\Section::make('Tutor/Student Information')
                    ->schema([
                        TextInput::make('first_name')->disabled(),
                        TextInput::make('last_name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('mobile')->label('Phone')->disabled(),
                    ])
                    ->visible(fn ($record) => $record && in_array($record->user_type, ['tutor', 'student'])),
                Forms\Components\Section::make('Organization Information')
                    ->schema([
                        TextInput::make('organization_name')->disabled(),
                        TextInput::make('contact_person')->disabled(),
                        TextInput::make('email')->disabled(),
                    ])
                    ->visible(fn ($record) => $record && $record->user_type === 'organisation'),
                Forms\Components\Section::make('Message')
                    ->schema([
                        Textarea::make('message')->disabled()->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Technical Info')
                    ->schema([
                        TextInput::make('ip_address')->label('IP')->disabled(),
                        TextInput::make('user_agent')->label('User Agent')->disabled(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_type')->label('User Type')->badge()->colors([
                    'primary',
                ]),
                TextColumn::make('first_name')->label('First Name')->searchable()->toggleable(),
                TextColumn::make('last_name')->label('Last Name')->searchable()->toggleable(),
                TextColumn::make('organization_name')->label('Organization')->searchable()->toggleable(),
                TextColumn::make('contact_person')->label('Contact Person')->searchable()->toggleable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('mobile')->label('Phone')->toggleable(),
                TextColumn::make('created_at')->label('Submitted')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label('User Type')
                    ->options([
                        'tutor' => 'Tutor',
                        'student' => 'Student',
                        'organisation' => 'Organisation',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'view' => Pages\ViewContactSubmission::route('/{record}'),
        ];
    }
}
