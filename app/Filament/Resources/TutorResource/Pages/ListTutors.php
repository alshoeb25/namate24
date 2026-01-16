<?php

namespace App\Filament\Resources\TutorResource\Pages;

use App\Filament\Resources\TutorResource;
use App\Models\Tutor;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListTutors extends ListRecords
{
    protected static string $resource = TutorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportExcel')
                ->label('Download Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    return $this->exportToExcel();
                }),
        ];
    }

    private function exportToExcel(): StreamedResponse
    {
        $tutors = Tutor::with('user', 'reviewedBy')
            ->orderByDesc('created_at')
            ->get();

        $fileName = 'tutors_report_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($tutors) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Headline',
                'Price/Hour (₹)',
                'Teaching Mode',
                'City',
                'Status',
                'Rating',
                'Rating Count',
                'Experience (Years)',
                'Reviewed By',
                'Reviewed At',
                'Created At',
            ]);

            // Data rows
            foreach ($tutors as $tutor) {
                fputcsv($file, [
                    $tutor->id,
                    $tutor->user->name ?? '-',
                    $tutor->user->email ?? '-',
                    $tutor->user->phone ?? '-',
                    $tutor->headline ?? '-',
                    $tutor->price_per_hour ?? '-',
                    ucfirst($tutor->teaching_mode[0] ?? '-'),
                    $tutor->city ?? '-',
                    ucfirst($tutor->moderation_status),
                    $tutor->rating_avg ?? '-',
                    $tutor->rating_count ?? '-',
                    $tutor->experience_years ?? '-',
                    $tutor->reviewedBy->name ?? '-',
                    $tutor->reviewed_at?->format('M d, Y H:i') ?? '-',
                    $tutor->created_at->format('M d, Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
