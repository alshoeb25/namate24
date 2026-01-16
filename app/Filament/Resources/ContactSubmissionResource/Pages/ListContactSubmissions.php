<?php

namespace App\Filament\Resources\ContactSubmissionResource\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListContactSubmissions extends ListRecords
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download Submissions')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $submissions = ContactSubmission::orderByDesc('created_at')->get();
        $fileName = 'contact_submissions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($submissions) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, [
                'ID', 'User Type', 'First Name', 'Last Name', 'Organization', 'Contact Person', 'Email', 'Phone', 'Message', 'Created At',
            ]);

            foreach ($submissions as $submission) {
                fputcsv($out, [
                    $submission->id,
                    $submission->user_type,
                    $submission->first_name,
                    $submission->last_name,
                    $submission->organization_name,
                    $submission->contact_person,
                    $submission->email,
                    $submission->mobile,
                    str_replace(["\r", "\n"], ' ', $submission->message),
                    optional($submission->created_at)?->format('Y-m-d H:i') ?? '-',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
