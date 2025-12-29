<?php

namespace App\Filament\Resources\StudentRequirementResource\Pages;

use App\Filament\Resources\StudentRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentRequirement extends EditRecord
{
    protected static string $resource = StudentRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
