<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Requirement #{{ $requirement->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2563eb;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f3f4f6;
            border-left: 4px solid #2563eb;
        }
        .field {
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .field-label {
            font-weight: bold;
            color: #4b5563;
            display: inline-block;
            width: 150px;
        }
        .field-value {
            color: #1f2937;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background-color: #dbeafe;
            color: #1e40af;
            font-size: 10px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Requirement Details</h1>
        <p>Requirement ID: #{{ $requirement->id }}</p>
    </div>

    <!-- Student Information -->
    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="field">
            <span class="field-label">Student Name:</span>
            <span class="field-value">{{ $requirement->student?->user?->name ?? $requirement->student_name ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Phone:</span>
            <span class="field-value">{{ $requirement->phone ?? 'N/A' }}</span>
        </div>
        @if($requirement->alternate_phone)
        <div class="field">
            <span class="field-label">Alternate Phone:</span>
            <span class="field-value">{{ $requirement->alternate_phone }}</span>
        </div>
        @endif
    </div>

    <!-- Subject & Details -->
    <div class="section">
        <div class="section-title">Subject & Details</div>
        <div class="field">
            <span class="field-label">Subjects:</span>
            <span class="field-value">
                @if($requirement->subjects && $requirement->subjects->isNotEmpty())
                    @foreach($requirement->subjects as $subject)
                        <span class="badge">{{ $subject->name }}</span>
                    @endforeach
                @elseif($requirement->subject)
                    <span class="badge">{{ $requirement->subject->name }}</span>
                @elseif($requirement->other_subject)
                    <span class="badge">{{ $requirement->other_subject }}</span>
                @else
                    N/A
                @endif
            </span>
        </div>
        @if($requirement->service_type)
        <div class="field">
            <span class="field-label">Service Type:</span>
            <span class="field-value">{{ ucfirst(str_replace('_', ' ', $requirement->service_type)) }}</span>
        </div>
        @endif
        @if($requirement->details)
        <div class="field">
            <span class="field-label">Details:</span>
            <span class="field-value">{{ $requirement->details }}</span>
        </div>
        @endif
    </div>

    <!-- Location -->
    <div class="section">
        <div class="section-title">Location</div>
        <div class="field">
            <span class="field-label">Location:</span>
            <span class="field-value">{{ $requirement->location ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="field-label">City:</span>
            <span class="field-value">{{ $requirement->city ?? 'N/A' }}</span>
        </div>
        @if($requirement->area)
        <div class="field">
            <span class="field-label">Area:</span>
            <span class="field-value">{{ $requirement->area }}</span>
        </div>
        @endif
        @if($requirement->pincode)
        <div class="field">
            <span class="field-label">Pincode:</span>
            <span class="field-value">{{ $requirement->pincode }}</span>
        </div>
        @endif
    </div>

    <!-- Academic Details -->
    <div class="section">
        <div class="section-title">Academic Details</div>
        @if($requirement->class)
        <div class="field">
            <span class="field-label">Class/Grade:</span>
            <span class="field-value">{{ $requirement->class }}</span>
        </div>
        @endif
        @if($requirement->level)
        <div class="field">
            <span class="field-label">Level:</span>
            <span class="field-value">{{ $requirement->level }}</span>
        </div>
        @endif
    </div>

    <!-- Budget & Preferences -->
    <div class="section">
        <div class="section-title">Budget & Preferences</div>
        @if($requirement->budget)
        <div class="field">
            <span class="field-label">Budget:</span>
            <span class="field-value">₹{{ number_format($requirement->budget, 2) }} {{ $requirement->budget_type ? '(' . ucfirst(str_replace('_', ' ', $requirement->budget_type)) . ')' : '' }}</span>
        </div>
        @endif
        @if($requirement->mode)
        <div class="field">
            <span class="field-label">Mode:</span>
            <span class="field-value">{{ ucfirst($requirement->mode) }}</span>
        </div>
        @endif
        @if($requirement->gender_preference)
        <div class="field">
            <span class="field-label">Gender Preference:</span>
            <span class="field-value">{{ ucfirst(str_replace('_', ' ', $requirement->gender_preference)) }}</span>
        </div>
        @endif
        @if($requirement->availability)
        <div class="field">
            <span class="field-label">Availability:</span>
            <span class="field-value">{{ ucfirst(str_replace('_', ' ', $requirement->availability)) }}</span>
        </div>
        @endif
    </div>

    <!-- Status & Lead Management -->
    <div class="section">
        <div class="section-title">Status & Lead Management</div>
        <div class="field">
            <span class="field-label">Status:</span>
            <span class="field-value">{{ ucfirst($requirement->status ?? 'N/A') }}</span>
        </div>
        <div class="field">
            <span class="field-label">Lead Status:</span>
            <span class="field-value">{{ ucfirst($requirement->lead_status ?? 'N/A') }}</span>
        </div>
        <div class="field">
            <span class="field-label">Current Leads:</span>
            <span class="field-value">{{ $requirement->current_leads ?? 0 }}</span>
        </div>
        <div class="field">
            <span class="field-label">Max Leads:</span>
            <span class="field-value">{{ $requirement->max_leads ?? 0 }}</span>
        </div>
        @if($requirement->post_fee)
        <div class="field">
            <span class="field-label">Post Fee:</span>
            <span class="field-value">₹{{ number_format($requirement->post_fee, 2) }}</span>
        </div>
        @endif
        @if($requirement->unlock_price)
        <div class="field">
            <span class="field-label">Unlock Price:</span>
            <span class="field-value">{{ $requirement->unlock_price }} coins</span>
        </div>
        @endif
        <div class="field">
            <span class="field-label">Posted At:</span>
            <span class="field-value">{{ $requirement->created_at->format('d M Y, h:i A') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
        <p>This is a system generated document.</p>
    </div>
</body>
</html>
