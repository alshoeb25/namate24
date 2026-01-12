<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactSubmissionNotification;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $userType = $request->input('user_type');

        $rules = [
            'user_type' => ['required', 'string', Rule::in(['tutor', 'student', 'organisation'])],
        ];

        // Tutor/Student fields
        if (in_array($userType, ['tutor', 'student'])) {
            $rules['first_name'] = ['required', 'string', 'max:255'];
            $rules['last_name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
            $rules['mobile'] = ['required', 'string', 'max:20', 'regex:/^[0-9\-\+\s\(\)]{7,20}$/'];
        } 
        // Organisation fields
        elseif ($userType === 'organisation') {
            $rules['organization_name'] = ['required', 'string', 'max:255'];
            $rules['contact_person'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        $rules['message'] = ['nullable', 'string', 'max:2000'];

        $data = $request->validate($rules);

        $submission = ContactSubmission::create([
            ...$data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', config('mail.from.address')));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new ContactSubmissionNotification($submission));
        }

        return response()->json([
            'message' => 'Thanks! Your message has been received.',
            'data' => $submission,
        ], 201);
    }
}
