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
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Unauthorized. Please login first.');
        }

        // Allow any authenticated user who can access the admin panel
        // If they can access ViewTutor page, they can download the PDF
        $user = auth()->user();
        
        // Load all relationships like ViewTutor page
        $tutor->load([
            'user',
            'reviewedBy',
            'moderationActions.admin',
            'documents',
            'documents.verifiedBy',
            'disabledBy',
            'subjects'
        ]);

        // Prepare data for PDF
        $data = [
            'tutor' => $tutor,
            'generated_at' => now()->toDateTimeString(),
        ];

        // Render a blade view and pass to DomPDF
        $pdf = Pdf::loadView('admin.tutors.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = \Str::slug($tutor->user->name ?? 'tutor') . '-profile.pdf';

        return $pdf->download($filename);
    }
}