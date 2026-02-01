<?php

namespace App\Filament\Resources\TutorReviewResource\Pages;

use App\Filament\Resources\TutorReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTutorReviews extends ListRecords
{
    protected static string $resource = TutorReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for tutor reviews
        ];
    }
}
