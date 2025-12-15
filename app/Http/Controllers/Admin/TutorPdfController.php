<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class TutorPdfController extends Controller
{
    /**
     * Show or download tutor PDF.
     *
     * Route protected for admin users only.
     */
    public function show(Tutor $tutor)
    {
        // Ensure only admins (or permitted users) can access
        $this->authorize('moderate', $tutor);

        // Prepare data for PDF
        $data = [
            'tutor' => $tutor->load('user', 'subjects'),
            'generated_at' => now()->toDateTimeString(),
        ];

        // Render a blade view and pass to DomPDF
        $pdf = Pdf::loadView('admin.tutors.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'tutor_' . $tutor->id . '_' . \Str::slug($tutor->user->name ?? 'tutor') . '.pdf';

        return $pdf->download($filename);
    }
}