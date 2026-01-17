<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralInvite;
use App\Mail\ReferralInvitationMail;
use App\Jobs\SendReferralInviteEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReferralInviteController extends Controller
{
    /**
     * List all referral invites with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');
        $status = $request->query('status'); // 'used', 'unused', 'all'

        $query = ReferralInvite::query();

        // Filter by email search
        if ($search) {
            $query->where('email', 'like', "%{$search}%");
        }

        // Filter by status
        if ($status === 'used') {
            $query->where('is_used', true);
        } elseif ($status === 'unused') {
            $query->where('is_used', false);
        }

        $invites = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'message' => 'Referral invites retrieved successfully',
            'data' => $invites,
        ]);
    }

    /**
     * Create a single referral invite
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:referral_invites,email',
            'referred_coins' => 'required|integer|min:1|max:1000',
            'send_email' => 'nullable|boolean',
        ]);

        try {
            $invite = ReferralInvite::create([
                'email' => $validated['email'],
                'referred_coins' => $validated['referred_coins'],
                'is_used' => false,
            ]);

            // Send email if requested
            if ($validated['send_email'] ?? false) {
                $this->sendInviteEmail($invite);
            }

            return response()->json([
                'message' => 'Referral invite created successfully',
                'data' => $invite,
                'email_sent' => $validated['send_email'] ?? false,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create referral invite: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create referral invite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk upload referral invites via CSV
     * CSV format: email,referred_coins
     * Example: user@example.com,50
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'send_emails' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        $sendEmails = $request->boolean('send_emails', false);

        try {
            $results = [
                'success' => 0,
                'failed' => 0,
                'duplicates' => 0,
                'errors' => [],
                'invites' => [],
            ];

            $handle = fopen($file->getPathname(), 'r');
            $lineNumber = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                // Skip header row
                if ($lineNumber === 1 && ($row[0] === 'email' || $row[0] === 'Email')) {
                    continue;
                }

                $email = trim($row[0] ?? '');
                $coins = intval($row[1] ?? 0);

                // Validate email and coins
                $validator = Validator::make(
                    ['email' => $email, 'coins' => $coins],
                    [
                        'email' => 'required|email',
                        'coins' => 'required|integer|min:1|max:1000',
                    ]
                );

                if ($validator->fails()) {
                    $results['errors'][] = "Line {$lineNumber}: " . implode(', ', $validator->errors()->flatten()->toArray());
                    $results['failed']++;
                    continue;
                }

                // Check if email already exists
                if (ReferralInvite::where('email', $email)->exists()) {
                    $results['errors'][] = "Line {$lineNumber}: Email {$email} already exists in system";
                    $results['duplicates']++;
                    continue;
                }

                try {
                    $invite = ReferralInvite::create([
                        'email' => $email,
                        'referred_coins' => $coins,
                        'is_used' => false,
                    ]);

                    if ($sendEmails) {
                        $this->sendInviteEmail($invite);
                    }

                    $results['invites'][] = [
                        'id' => $invite->id,
                        'email' => $email,
                        'coins' => $coins,
                    ];
                    $results['success']++;
                } catch (\Throwable $e) {
                    $results['errors'][] = "Line {$lineNumber}: Failed to create invite - " . $e->getMessage();
                    $results['failed']++;
                }
            }

            fclose($handle);

            return response()->json([
                'message' => 'Bulk upload completed',
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            Log::error('CSV upload failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'CSV upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk create from comma-separated values
     * Format: email1,coins1;email2,coins2;email3,coins3
     * Or: email1|coins1,email2|coins2
     */
    public function bulkCreateFromText(Request $request)
    {
        $request->validate([
            'entries' => 'required|string',
            'send_emails' => 'nullable|boolean',
        ]);

        $sendEmails = $request->boolean('send_emails', false);
        $entriesText = $request->input('entries');

        try {
            $results = [
                'success' => 0,
                'failed' => 0,
                'duplicates' => 0,
                'errors' => [],
                'invites' => [],
            ];

            // Split by newlines or semicolons
            $lines = preg_split('/[\n;]/', $entriesText);

            foreach ($lines as $lineIndex => $line) {
                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                // Support both comma and pipe separators
                $parts = str_contains($line, '|')
                    ? explode('|', $line)
                    : explode(',', $line);

                if (count($parts) < 2) {
                    $results['errors'][] = "Entry " . ($lineIndex + 1) . ": Invalid format (expected email,coins or email|coins)";
                    $results['failed']++;
                    continue;
                }

                $email = trim($parts[0]);
                $coins = intval(trim($parts[1]));

                // Validate
                $validator = Validator::make(
                    ['email' => $email, 'coins' => $coins],
                    [
                        'email' => 'required|email',
                        'coins' => 'required|integer|min:1|max:1000',
                    ]
                );

                if ($validator->fails()) {
                    $results['errors'][] = "Entry " . ($lineIndex + 1) . ": " . implode(', ', $validator->errors()->flatten()->toArray());
                    $results['failed']++;
                    continue;
                }

                // Check if exists
                if (ReferralInvite::where('email', $email)->exists()) {
                    $results['errors'][] = "Entry " . ($lineIndex + 1) . ": Email {$email} already exists";
                    $results['duplicates']++;
                    continue;
                }

                try {
                    $invite = ReferralInvite::create([
                        'email' => $email,
                        'referred_coins' => $coins,
                        'is_used' => false,
                    ]);

                    if ($sendEmails) {
                        $this->sendInviteEmail($invite);
                    }

                    $results['invites'][] = [
                        'id' => $invite->id,
                        'email' => $email,
                        'coins' => $coins,
                    ];
                    $results['success']++;
                } catch (\Throwable $e) {
                    $results['errors'][] = "Entry " . ($lineIndex + 1) . ": " . $e->getMessage();
                    $results['failed']++;
                }
            }

            return response()->json([
                'message' => 'Bulk create from text completed',
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            Log::error('Bulk create from text failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Bulk create failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send invitation email to referral invites
     */
    public function sendEmails(Request $request)
    {
        $validated = $request->validate([
            'invite_ids' => 'required|array|min:1',
            'invite_ids.*' => 'integer|exists:referral_invites,id',
        ]);

        try {
            $results = [
                'success' => 0,
                'failed' => 0,
                'errors' => [],
            ];

            foreach ($validated['invite_ids'] as $inviteId) {
                try {
                    $invite = ReferralInvite::findOrFail($inviteId);
                    $this->sendInviteEmail($invite);
                    $results['success']++;
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = "Invite {$inviteId}: " . $e->getMessage();
                    Log::error("Failed to send referral invite email for ID {$inviteId}: " . $e->getMessage());
                }
            }

            return response()->json([
                'message' => 'Email sending completed',
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            Log::error('Batch email sending failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Batch email sending failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics
     */
    public function stats()
    {
        $stats = [
            'total' => ReferralInvite::count(),
            'used' => ReferralInvite::where('is_used', true)->count(),
            'unused' => ReferralInvite::where('is_used', false)->count(),
            'total_coins_offered' => ReferralInvite::sum('referred_coins'),
            'total_coins_redeemed' => ReferralInvite::where('is_used', true)->sum('referred_coins'),
        ];

        return response()->json([
            'message' => 'Statistics retrieved',
            'data' => $stats,
        ]);
    }

    /**
     * Show a single invite
     */
    public function show($id)
    {
        $invite = ReferralInvite::findOrFail($id);

        return response()->json([
            'message' => 'Referral invite retrieved',
            'data' => $invite,
        ]);
    }

    /**
     * Update invite coins (admin only)
     */
    public function update(Request $request, $id)
    {
        $invite = ReferralInvite::findOrFail($id);

        $validated = $request->validate([
            'referred_coins' => 'nullable|integer|min:1|max:1000',
        ]);

        if (isset($validated['referred_coins'])) {
            $invite->update(['referred_coins' => $validated['referred_coins']]);
        }

        return response()->json([
            'message' => 'Referral invite updated',
            'data' => $invite,
        ]);
    }

    /**
     * Delete a referral invite (only if unused)
     */
    public function destroy($id)
    {
        $invite = ReferralInvite::findOrFail($id);

        if ($invite->is_used) {
            return response()->json([
                'message' => 'Cannot delete used referral invite',
            ], 422);
        }

        $invite->delete();

        return response()->json([
            'message' => 'Referral invite deleted successfully',
        ]);
    }

    /**
     * Helper: Send invitation email via queue
     */
    private function sendInviteEmail(ReferralInvite $invite): void
    {
        try {
            SendReferralInviteEmail::dispatch($invite);
            Log::info("Referral invite email job dispatched for {$invite->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch email job for {$invite->email}: " . $e->getMessage());
            throw $e;
        }
    }
}
