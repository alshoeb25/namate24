<?php

namespace App\Filament\Resources;

use App\Models\Review;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\ReviewResource\Pages;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('reviewable.user.name')
                    ->label('Tutor')
                    ->disabled(),
                TextInput::make('user.name')
                    ->label('Reviewer')
                    ->disabled(),
                TextInput::make('rating')
                    ->numeric()
                    ->min(1)
                    ->max(5)
                    ->disabled(),
                RichEditor::make('comment')
                    ->columnSpanFull(),
                Toggle::make('is_hidden')
                    ->label('Hide Review'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewable.user.name')
                    ->label('Tutor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Reviewer')
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn(int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'info',
                        default => 'warning',
                    }),
                TextColumn::make('comment')
                    ->limit(50)
                    ->wrap(),
                ToggleColumn::make('is_hidden')
                    ->label('Hidden'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
                SelectFilter::make('is_hidden')
                    ->options([
                        true => 'Hidden',
                        false => 'Visible',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
