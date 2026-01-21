<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $tutor->user->name ?? 'Tutor' }} — Profile</title>
    <style>
        @page { margin: 20mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #222; 
            line-height: 1.5;
            padding: 0;
            margin: 0;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #007bff; 
            padding-bottom: 10px; 
        }
        .header h1 { font-size: 22px; margin-bottom: 5px; color: #007bff; }
        .meta { font-size: 9px; color: #666; }
        .section { 
            margin-bottom: 12px; 
            page-break-inside: avoid; 
        }
        .section-title { 
            font-weight: bold; 
            font-size: 12px; 
            color: #007bff; 
            margin-bottom: 6px; 
            border-bottom: 1px solid #ddd; 
            padding-bottom: 2px; 
        }
        .section-content { 
            margin-left: 8px; 
            margin-right: 8px;
        }
        .row { 
            margin-bottom: 5px; 
            clear: both;
        }
        .label { 
            font-weight: bold; 
            display: inline-block; 
            width: 140px; 
            color: #333; 
            vertical-align: top;
        }
        .value { 
            display: inline-block; 
            color: #555; 
            max-width: calc(100% - 150px);
            word-wrap: break-word;
        }
        ul, ol { 
            margin-left: 18px; 
            margin-bottom: 5px; 
            padding-left: 0;
        }
        li { 
            margin-bottom: 3px; 
            line-height: 1.4;
        }
        .box { 
            padding: 8px; 
            background: #f8f9fa; 
            border-left: 3px solid #007bff; 
            margin-bottom: 8px; 
            page-break-inside: avoid;
        }
        .box-title { 
            font-weight: bold; 
            color: #333; 
            margin-bottom: 4px; 
            font-size: 10px;
        }
        .box-item { 
            font-size: 9px; 
            margin-bottom: 3px; 
            line-height: 1.3;
        }
        footer { 
            position: fixed; 
            bottom: 10mm; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 8px; 
            color: #999; 
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $tutor->user->name ?? 'Tutor' }}</h1>
        <div class="meta">Profile Generated: {{ $generated_at }}</div>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">Personal Information</div>
        <div class="section-content">
            <div class="row">
                <span class="label">Name:</span>
                <span class="value">{{ $tutor->user->name ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Email:</span>
                <span class="value">{{ $tutor->user->email ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Phone:</span>
                <span class="value">{{ $tutor->user->phone ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Gender:</span>
                <span class="value">{{ $tutor->gender ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="section">
        <div class="section-title">Profile Information</div>
        <div class="section-content">
            @if($tutor->headline)
                <div class="row">
                    <span class="label">Headline:</span>
                    <span class="value">{{ $tutor->headline }}</span>
                </div>
            @endif
            @if($tutor->description)
                <div class="row">
                    <span class="label">Description:</span>
                    <span class="value">{{ $tutor->description }}</span>
                </div>
            @endif
            @if($tutor->speciality)
                <div class="row">
                    <span class="label">Speciality:</span>
                    <span class="value">{{ $tutor->speciality }}</span>
                </div>
            @endif
            @if($tutor->strength)
                <div class="row">
                    <span class="label">Strengths:</span>
                    <span class="value">{{ $tutor->strength }}</span>
                </div>
            @endif
            @if($tutor->current_role)
                <div class="row">
                    <span class="label">Current Role:</span>
                    <span class="value">{{ $tutor->current_role }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Languages & Opportunities -->
    <div class="section">
        <div class="section-title">Languages & Opportunities</div>
        <div class="section-content">
            @if($tutor->languages && count($tutor->languages) > 0)
                <div class="row">
                    <span class="label">Languages:</span>
                    <span class="value">{{ implode(', ', $tutor->languages) }}</span>
                </div>
            @endif
            @if($tutor->opportunities && count($tutor->opportunities) > 0)
                <div class="row">
                    <span class="label">Opportunities:</span>
                    <span class="value">
                        @if(count($tutor->opportunities) === 1)
                            {{ $tutor->opportunities[0] }}
                        @else
                            @php
                                $opps = $tutor->opportunities;
                                $last = array_pop($opps);
                                echo implode(', ', $opps) . ' and ' . $last;
                            @endphp
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Teaching Subjects -->
    @if($tutor->subjects && $tutor->subjects->count() > 0)
        <div class="section">
            <div class="section-title">Teaching Subjects</div>
            <div class="section-content">
                <ul>
                    @foreach($tutor->subjects as $subject)
                        <li>
                            <strong>{{ $subject->name ?? 'N/A' }}</strong>
                            @if($subject->pivot->from_level || $subject->pivot->to_level)
                                (Level: {{ $subject->pivot->from_level ?? '-' }} to {{ $subject->pivot->to_level ?? '-' }})
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Uploaded Documents -->
    @if($tutor->documents && $tutor->documents->count() > 0)
        <div class="section">
            <div class="section-title">Uploaded Documents</div>
            <div class="section-content">
                @foreach($tutor->documents as $doc)
                    <div class="box">
                        <div class="box-title">{{ $doc->document_type ?? 'Document' }} - {{ ucfirst($doc->verification_status) }}</div>
                        <div class="box-item"><strong>File:</strong> {{ $doc->file_name ?? 'N/A' }}</div>
                        @if($doc->verifiedBy?->name)
                            <div class="box-item"><strong>Verified By:</strong> {{ $doc->verifiedBy->name }}</div>
                        @endif
                        @if($doc->verified_at)
                            <div class="box-item"><strong>Verified At:</strong> {{ $doc->verified_at->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Address Information -->
    <div class="section">
        <div class="section-title">Address Information</div>
        <div class="section-content">
            @php
                $addressParts = [];
                if ($tutor->address) $addressParts[] = $tutor->address;
                if ($tutor->area) $addressParts[] = $tutor->area;
                if ($tutor->city) $addressParts[] = $tutor->city;
                if ($tutor->state) $addressParts[] = $tutor->state;
                if ($tutor->postal_code) $addressParts[] = $tutor->postal_code;
                if ($tutor->country) $addressParts[] = $tutor->country;
            @endphp
            @if(count($addressParts) > 0)
                @foreach($addressParts as $part)
                    <div style="margin-bottom: 3px;">{{ $part }}</div>
                @endforeach
            @else
                <div class="value">-</div>
            @endif
        </div>
    </div>

    <!-- Education -->
    @if($tutor->educations && count($tutor->educations) > 0)
        <div class="section">
            <div class="section-title">Education</div>
            <div class="section-content">
                @foreach($tutor->educations as $index => $edu)
                    <div class="box">
                        @php
                            $degree = $edu['degree'] ?? '';
                            $degreeType = $edu['degree_type'] ?? '';
                            $institution = $edu['institution'] ?? '';
                            $city = $edu['city'] ?? '';
                            $field = $edu['field_of_study'] ?? '';
                            $mode = $edu['study_mode'] ?? '';
                            $startMonth = $edu['start_month'] ?? '';
                            $startYear = $edu['start_year'] ?? '';
                            $endMonth = $edu['end_month'] ?? '';
                            $endYear = $edu['end_year'] ?? '';
                            $score = $edu['score'] ?? '';
                            
                            $title = '';
                            if ($degree && $degreeType) {
                                $title = "$degree ($degreeType)";
                            } elseif ($degree) {
                                $title = $degree;
                            }
                        @endphp
                        <div class="box-title">#{{ $index + 1 }}{{ $title ? " - $title" : '' }}</div>
                        @if($institution || $city)
                            <div class="box-item"><strong>Institution:</strong> {{ $institution }}{{ ($institution && $city) ? ', ' . $city : $city }}</div>
                        @endif
                        @if($field || $mode)
                            <div class="box-item"><strong>Field:</strong> {{ $field ?: '-' }} | <strong>Mode:</strong> {{ $mode ?: '-' }}</div>
                        @endif
                        @if($startMonth || $startYear || $endMonth || $endYear)
                            @php
                                $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
                                $start = '';
                                if ($startMonth) $start = $months[(int)$startMonth] ?? '';
                                if ($startYear) $start = trim($start . ' ' . $startYear);
                                $end = '';
                                if ($endMonth) $end = $months[(int)$endMonth] ?? '';
                                if ($endYear) $end = trim($end . ' ' . $endYear);
                            @endphp
                            <div class="box-item"><strong>Duration:</strong> {{ $start ?: 'Start pending' }} - {{ $end ?: 'Current' }}</div>
                        @endif
                        @if($score)
                            <div class="box-item"><strong>Score:</strong> {{ $score }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Experience -->
    @if($tutor->experiences && count($tutor->experiences) > 0)
        <div class="section">
            <div class="section-title">Experience</div>
            <div class="section-content">
                @foreach($tutor->experiences as $index => $exp)
                    <div class="box">
                        @php
                            $title = $exp['title'] ?? '';
                            $company = $exp['company'] ?? '';
                            $designation = $exp['designation'] ?? '';
                            $association = $exp['association'] ?? '';
                            $startMonth = $exp['start_month'] ?? '';
                            $startYear = $exp['start_year'] ?? '';
                            $endMonth = $exp['end_month'] ?? '';
                            $endYear = $exp['end_year'] ?? '';
                            $roles = $exp['roles'] ?? '';
                            
                            $titleInfo = '';
                            if ($title && $company) {
                                $titleInfo = "$title at $company";
                            } elseif ($title) {
                                $titleInfo = $title;
                            }
                        @endphp
                        <div class="box-title">#{{ $index + 1 }}{{ $titleInfo ? " - $titleInfo" : '' }}</div>
                        @if($designation || $association)
                            <div class="box-item"><strong>Designation:</strong> {{ $designation ?: '-' }} | <strong>Type:</strong> {{ $association ?: '-' }}</div>
                        @endif
                        @if($startMonth || $startYear || $endMonth || $endYear)
                            @php
                                $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
                                $start = '';
                                if ($startMonth) $start = $months[(int)$startMonth] ?? '';
                                if ($startYear) $start = trim($start . ' ' . $startYear);
                                $end = '';
                                if ($endMonth) $end = $months[(int)$endMonth] ?? '';
                                if ($endYear) $end = trim($end . ' ' . $endYear);
                            @endphp
                            <div class="box-item"><strong>Duration:</strong> {{ $start ?: 'Start pending' }} - {{ $end ?: 'Current' }}</div>
                        @endif
                        @if($roles)
                            <div class="box-item"><strong>Roles:</strong> {{ $roles }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Teaching Details -->
    <div class="section">
        <div class="section-title">Teaching Details</div>
        <div class="section-content">
            @php
                $minFee = $tutor->min_fee;
                $maxFee = $tutor->max_fee;
                $chargeType = $tutor->charge_type ?? 'Per Hour';
                $charges = '-';
                if ($minFee && $maxFee) {
                    $charges = '₹' . number_format($minFee, 2) . ' - ₹' . number_format($maxFee, 2) . ' (' . $chargeType . ')';
                } elseif ($minFee) {
                    $charges = '₹' . number_format($minFee, 2) . ' (' . $chargeType . ')';
                } elseif ($maxFee) {
                    $charges = '₹' . number_format($maxFee, 2) . ' (' . $chargeType . ')';
                }
            @endphp
            <div class="row">
                <span class="label">Charges:</span>
                <span class="value">{{ $charges }}</span>
            </div>
            <div class="row">
                <span class="label">Fee Notes:</span>
                <span class="value">{{ $tutor->fee_notes ?? '-' }}</span>
            </div>
            <div class="row">
                <span class="label">Session Duration:</span>
                <span class="value">{{ $tutor->session_duration ?? '-' }} minutes</span>
            </div>
        </div>
    </div>

    <!-- Professional Details -->
    <div class="section">
        <div class="section-title">Professional Details</div>
        <div class="section-content">
            <ul>
                @if($tutor->experience_years)
                    <li><strong>Years of Experience:</strong> {{ $tutor->experience_years }}</li>
                @endif
                @if($tutor->teaching_mode)
                    <li><strong>Teaching Mode:</strong> {{ is_array($tutor->teaching_mode) ? implode(', ', array_map('ucfirst', $tutor->teaching_mode)) : ucfirst($tutor->teaching_mode) }}</li>
                @endif
                @if($tutor->rating_avg)
                    <li><strong>Average Rating:</strong> {{ number_format($tutor->rating_avg, 2) }}</li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Teaching Preferences -->
    <div class="section">
        <div class="section-title">Teaching Preferences</div>
        <div class="section-content">
            <ul>
                @if($tutor->teaching_style)
                    <li><strong>Teaching Style:</strong> {{ $tutor->teaching_style }}</li>
                @endif
                <li><strong>Willing to Travel:</strong> {{ $tutor->travel_willing ? '✓ Yes' : '✗ No' }}</li>
                @if($tutor->travel_distance_km)
                    <li><strong>Travel Distance:</strong> {{ $tutor->travel_distance_km }} km</li>
                @endif
                <li><strong>Online Available:</strong> {{ $tutor->online_available ? '✓ Yes' : '✗ No' }}</li>
                <li><strong>Has Digital Pen:</strong> {{ $tutor->has_digital_pen ? '✓ Yes' : '✗ No' }}</li>
            </ul>
        </div>
    </div>

    <!-- Moderation Status -->
    <div class="section">
        <div class="section-title">Moderation Status</div>
        <div class="section-content">
            <div class="row">
                <span class="label">Status:</span>
                <span class="value">{{ ucfirst($tutor->moderation_status) }}</span>
            </div>
            @if($tutor->reviewedBy?->name)
                <div class="row">
                    <span class="label">Reviewed By:</span>
                    <span class="value">{{ $tutor->reviewedBy->name }}</span>
                </div>
            @endif
            @if($tutor->reviewed_at)
                <div class="row">
                    <span class="label">Reviewed At:</span>
                    <span class="value">{{ $tutor->reviewed_at->format('d M Y H:i') }}</span>
                </div>
            @endif
            <div class="row">
                <span class="label">Account Status:</span>
                <span class="value">{{ $tutor->is_disabled ? 'Disabled' : 'Active' }}</span>
            </div>
        </div>
    </div>

    <!-- Rejection Details (if applicable) -->
    @if($tutor->moderation_status === 'rejected')
        <div class="section">
            <div class="section-title">Rejection Details</div>
            <div class="section-content">
                @if($tutor->rejection_reason)
                    <div class="row">
                        <span class="label">Reason:</span>
                        <span class="value">{{ $tutor->rejection_reason }}</span>
                    </div>
                @endif
                @if($tutor->rejection_notes)
                    <div class="row">
                        <span class="label">Notes:</span>
                        <span class="value">{{ $tutor->rejection_notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <footer>
        Namate24 — Tutor Profile Export
    </footer>
</body>
</html>
