<?php

namespace App\Filament\Resources\StudentRequirementResource\Pages;

use App\Filament\Resources\StudentRequirementResource;
use App\Models\StudentRequirement;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStudentRequirements extends ListRecords
{
    protected static string $resource = StudentRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download Enquiries')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $enquiries = StudentRequirement::with(['student.user'])
            ->orderByDesc('created_at')
            ->get();

        $fileName = 'enquiries_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($enquiries) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, [
                'ID', 'Student', 'Email', 'Phone', 'Lead Status', 'Current Leads', 'Max Leads', 'Post Fee', 'Unlock Price', 'Created At',
            ]);

            foreach ($enquiries as $enquiry) {
                fputcsv($out, [
                    $enquiry->id,
                    $enquiry->student->user->name ?? '-',
                    $enquiry->student->user->email ?? '-',
                    $enquiry->student->user->phone ?? '-',
                    $enquiry->lead_status,
                    $enquiry->current_leads,
                    $enquiry->max_leads,
                    $enquiry->post_fee,
                    $enquiry->unlock_price,
                    optional($enquiry->created_at)?->format('Y-m-d H:i') ?? '-',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
