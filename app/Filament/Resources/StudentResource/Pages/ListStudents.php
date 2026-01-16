<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Student;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download Students')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $students = Student::with('user')->orderByDesc('created_at')->get();

        $fileName = 'students_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($students) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, [
                'ID', 'Name', 'Email', 'Phone', 'Grade Level', 'Coins', 'Status', 'Disabled Reason', 'Disabled At', 'Joined',
            ]);

            foreach ($students as $student) {
                fputcsv($out, [
                    $student->id,
                    $student->user->name ?? '-',
                    $student->user->email ?? '-',
                    $student->user->phone ?? '-',
                    $student->grade_level ?? '-',
                    $student->user->coins ?? 0,
                    ($student->is_disabled ?? false) ? 'Disabled' : 'Active',
                    $student->disabled_reason ?? '-',
                    optional($student->disabled_at)?->format('Y-m-d H:i') ?? '-',
                    optional($student->created_at)?->format('Y-m-d H:i') ?? '-',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
