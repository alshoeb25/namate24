<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Interested in Your Requirement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header {
            padding: 30px 20px;
            text-align: center;
        }

        .logo {
            max-width: 200px;
            height: auto;
            display: inline-block;
        }

        .content {
            padding: 40px 30px;
        }

        h1 {
            color: #1e88e5;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            margin-bottom: 16px;
            font-size: 16px;
            color: #555;
        }

        .highlight {
            font-weight: bold;
            color: #1e88e5;
        }

        .info-box {
            background-color: #e8f2ff;
            border-left: 4px solid #1e88e5;
            padding: 18px;
            margin: 22px 0;
            border-radius: 0 8px 8px 0;
        }

        .action-button {
            display: block;
            width: 260px;
            margin: 28px auto;
            padding: 14px 22px;
            text-align: center;
            background: linear-gradient(135deg, #1e88e5, #1565c0);
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 30px;
        }

        .footer {
            padding: 20px 30px 30px;
            border-top: 1px solid #d9e9ff;
            font-size: 13px;
            color: #999;
            text-align: center;
        }

        @media (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .action-button {
                width: 90%;
                padding: 14px 20px;
                font-size: 16px;
            }

            .logo {
                max-width: 160px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ url('storage/logo.png') }}" alt="Namate24 Logo" class="logo">
        </div>

        <div class="content">
            <h1>Tutor Interested in Your Requirement</h1>

            <p>Hello <span class="highlight">{{ $studentName }}</span>,</p>
            <p>A tutor has unlocked your requirement and is interested in teaching you.</p>

            <div class="info-box">
                <p><strong>Tutor:</strong> {{ $teacherName }}</p>
                
                <p><strong>Subjects:</strong> {{ $subjectLabel }}</p>
                <p><strong>Location:</strong> {{ $requirement->area }}, {{ $requirement->city }}</p>
                <p><strong>Meeting Options:</strong> {{ $meetingOptionsLabel ?? $meetingOptions }}</p>
                <p><strong>Budget:</strong> {{ $budgetDisplay ?? ($requirement->budget_amount ?? $requirement->budget ?? 'N/A') }}</p>
                @if(!empty($serviceTypeLabel))
                    <p><strong>Service Type:</strong> {{ $serviceTypeLabel }}</p>
                @endif
                @if(!empty($availabilityLabel))
                    <p><strong>Availability:</strong> {{ $availabilityLabel }}</p>
                @endif
                @if(!empty($genderPreferenceLabel))
                    <p><strong>Gender Preference:</strong> {{ $genderPreferenceLabel }}</p>
                @endif
                @if(!empty($leadStatus))
                    <p><strong>Lead Status:</strong> {{ $leadStatus }}</p>
                @endif
                
            </div>

            <a href="{{ $viewUrl }}" class="action-button">View Requirement</a>

            <p>Please review the tutor profile and respond if interested.</p>
        </div>

        <div class="footer">
            @php
                $supportEmail = config('mail.from.address') ?: config('app.support_email');
            @endphp
            @if(!empty($supportEmail))
                <p>Need help? <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
            @endif
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
