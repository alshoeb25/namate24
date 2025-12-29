<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TutorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TutorDocumentController extends Controller
{
    /**
     * List current tutor's documents
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->tutor) {
            return response()->json(['message' => 'Tutor profile not found'], 404);
        }

        $docs = TutorDocument::where('tutor_id', $user->tutor->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'document_type' => $d->document_type,
                    'file_name' => $d->file_name,
                    'file_path' => $d->file_path,
                    'url' => $d->file_path ? asset('storage/' . $d->file_path) : null,
                    'verification_status' => $d->verification_status,
                    'rejection_reason' => $d->rejection_reason,
                    'verified_by' => $d->verified_by,
                    'verified_at' => $d->verified_at,
                    'created_at' => $d->created_at,
                ];
            });

        return response()->json($docs);
    }

    /**
     * Upload one or more documents
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->tutor) {
            return response()->json(['message' => 'Tutor profile not found'], 404);
        }

        $request->validate([
            'document_type' => 'required|string|in:educational,identification,experience,certification,other',
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $uploaded = [];
        foreach ($request->file('documents') as $file) {
            $path = $file->store('tutor-documents/' . $user->tutor->id, 'public');

            $doc = TutorDocument::create([
                'tutor_id' => $user->tutor->id,
                'document_type' => $request->input('document_type'),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'verification_status' => 'pending',
            ]);

            $uploaded[] = [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'file_name' => $doc->file_name,
                'url' => asset('storage/' . $doc->file_path),
                'verification_status' => $doc->verification_status,
                'created_at' => $doc->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'documents' => $uploaded,
        ]);
    }

    /**
     * Delete a document (allowed for own documents; recommended only for non-approved)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->tutor) {
            return response()->json(['message' => 'Tutor profile not found'], 404);
        }

        $doc = TutorDocument::where('id', $id)
            ->where('tutor_id', $user->tutor->id)
            ->first();

        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        if ($doc->verification_status === 'approved') {
            return response()->json(['message' => 'Approved documents cannot be deleted'], 400);
        }

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return response()->json(['success' => true]);
    }
}
