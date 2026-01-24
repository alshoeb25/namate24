<?php

namespace App\Filament\Resources\StudentRequirementResource\Pages;

use App\Filament\Resources\StudentRequirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewStudentRequirement extends ViewRecord
{
    protected static string $resource = StudentRequirementResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $requirement = $this->record;
                    $requirement->load(['student.user', 'subjects', 'subject']);
                    
                    $pdf = Pdf::loadView('pdf.student-requirement', [
                        'requirement' => $requirement
                    ]);
                    
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'requirement-' . $requirement->id . '.pdf');
                }),
        ];
    }
}
