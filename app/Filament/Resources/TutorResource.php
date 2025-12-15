<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TutorResource\Pages;
use App\Models\Tutor;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Resources\Form;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Actions\Action;

class TutorResource extends Resource
{
    protected static ?string $model = Tutor::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Tutors';
    protected static ?string $pluralLabel = 'Tutors';
    protected static ?string $modelLabel = 'Tutor';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->required(),
            Forms\Components\TextInput::make('headline')->required(),
            Forms\Components\Textarea::make('about'),
            Forms\Components\TextInput::make('price_per_hour')->numeric(),
            Forms\Components\Toggle::make('verified'),
            Forms\Components\Select::make('moderation_status')
                ->options([
                    'pending' => 'pending',
                    'approved' => 'approved',
                    'rejected' => 'rejected',
                ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('headline')->limit(50)->wrap(),
                Tables\Columns\TextColumn::make('price_per_hour')->label('Price/hr')->money('INR', true),
                Tables\Columns\BadgeColumn::make('moderation_status')->colors([
                    'secondary' => 'pending',
                    'success' => 'approved',
                    'danger'  => 'rejected',
                ]),
                Tables\Columns\IconColumn::make('verified')->boolean()->label('Verified'),
                Tables\Columns\TextColumn::make('rating_avg')->label('Rating')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->moderation_status !== 'approved')
                    ->action(function (Tutor $record, array $data = []) {
                        $record->update(['moderation_status' => 'approved', 'verified' => true]);
                        // TODO: Notify tutor of approval (Notification)
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->moderation_status !== 'rejected')
                    ->action(function (Tutor $record, array $data = []) {
                        $record->update(['moderation_status' => 'rejected', 'verified' => false]);
                        // TODO: capture rejection reason & notify tutor
                    }),

                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->openUrl(fn (Tutor $record) => route('admin.tutors.pdf', ['tutor' => $record->id]), shouldOpenInNewTab: true),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approveSelected')
                    ->label('Approve selected')
                    ->action(function (array $records) {
                        foreach ($records as $r) {
                            $r->update(['moderation_status' => 'approved', 'verified' => true]);
                        }
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTutors::route('/'),
            'create' => Pages\CreateTutor::route('/create'),
            'edit' => Pages\EditTutor::route('/{record}/edit'),
        ];
    }
}