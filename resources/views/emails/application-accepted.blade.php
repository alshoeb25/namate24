<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Accepted</title>
    <style>
        /* Reset styles for email clients */
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

        /* Email container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .header {
            padding: 30px 20px;
            text-align: center;
        }

        .logo {
            max-width: 200px;
            height: auto;
            display: inline-block;
        }

        /* Content */
        .content {
            padding: 40px 30px;
        }

        h1 {
            color: #d81b60;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }

        .highlight {
            font-weight: bold;
            color: #ff4081;
        }

        .info-box {
            background-color: #fff0f6;
            border-left: 4px solid #ff69b4;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }

        /* Action button */
        .action-button {
            display: block;
            width: 280px;
            margin: 30px auto;
            padding: 16px 24px;
            background-color: #ff69b4;
            color: white;
            text-decoration: none;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #e0559c;
        }

        /* Success badge */
        .success-badge {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .success-badge h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .checkmark {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        /* Application card */
        .application-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid #4caf50;
        }

        .application-header {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .application-header h3 {
            color: white;
            margin: 0;
            font-size: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 600;
        }

        .detail-value {
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        /* Next steps */
        .steps-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }

        .steps-box ol {
            margin: 10px 0;
            padding-left: 20px;
        }

        .steps-box li {
            margin-bottom: 10px;
            color: #1565c0;
        }

        /* Waiting message */
        .waiting-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffa726;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
            text-align: center;
        }

        .waiting-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        /* Link styling */
        a {
            color: #ff4081;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Footer */
        .footer {
            background-color: #fff5f9;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #ffd9e8;
            font-size: 14px;
            color: #777;
        }

        .footer a {
            color: #ff4081;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Support info */
        .support {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ffd9e8;
            font-size: 13px;
            color: #999;
        }

        /* Responsive adjustments */
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

            .info-box {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with pink logo -->
        <div class="header">
            <img src="{{ url('storage/logo.png') }}"
                alt="Namate24 Logo" class="logo">
        </div>

        <!-- Main content -->
        <div class="content">
            <h1>✅ Application Accepted!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <h2>Your Application Was Accepted</h2>
                <p style="margin: 0; color: white;">The student is interested in working with you!</p>
            </div>

            <p>Hello <span class="highlight">{{ $tutor->name }}</span>,</p>

            <p>Excellent news! A student has reviewed your application and accepted it. They're interested in hiring you as their tutor. You're one step closer to starting a new teaching opportunity!</p>

            <div class="application-card">
                <div class="application-header">
                    <h3>📚 Application Details</h3>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student Name:</span>
                    <span class="detail-value">{{ $student->name }}</span>
                </div>
                @if(isset($subject))
                <div class="detail-row">
                    <span class="detail-label">Subject:</span>
                    <span class="detail-value">{{ $subject }}</span>
                </div>
                @endif
                @if(isset($level))
                <div class="detail-row">
                    <span class="detail-label">Level:</span>
                    <span class="detail-value">{{ $level }}</span>
                </div>
                @endif
                @if(isset($sessionType))
                <div class="detail-row">
                    <span class="detail-label">Session Type:</span>
                    <span class="detail-value">{{ $sessionType }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Accepted On:</span>
                    <span class="detail-value">{{ $acceptedDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #4caf50;">✓ Accepted</span>
                </div>
            </div>

            @if(isset($studentMessage))
            <div class="info-box">
                <p><strong>💬 Message from Student:</strong></p>
                <p style="font-style: italic; margin-top: 10px;">"{{ $studentMessage }}"</p>
            </div>
            @endif

            <div class="waiting-box">
                <span class="waiting-icon">⏳</span>
                <h3 style="color: #f57c00; margin-bottom: 10px;">Waiting for Student to Complete Approach</h3>
                <p style="margin: 0; color: #666;">The student needs to finalize the approach process. You'll receive another notification once they complete this step.</p>
            </div>

            <div class="steps-box">
                <p><strong>📋 What Happens Next:</strong></p>
                <ol>
                    <li><strong>Student Finalizes Approach</strong> - They'll complete the approach process</li>
                    <li><strong>You Get Notified</strong> - We'll email you when you're officially approached</li>
                    <li><strong>Start Communication</strong> - You can then message the student directly</li>
                    <li><strong>Schedule Sessions</strong> - Agree on timing and start teaching</li>
                </ol>
            </div>

            <a href="{{ $dashboardUrl }}" class="action-button">View in Dashboard</a>

            <div class="info-box">
                <p><strong>💡 Prepare for Success:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Review the student's requirements and learning goals</li>
                    <li>Prepare relevant teaching materials and resources</li>
                    <li>Think about your teaching approach for this student</li>
                    <li>Be ready to respond quickly once approached</li>
                    <li>Plan your availability for upcoming sessions</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>⚡ Quick Tips:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Students typically finalize hire within 24-48 hours of accepting</li>
                    <li>Keep checking your dashboard for updates</li>
                    <li>Have your introduction message ready</li>
                    <li>Be prepared to discuss schedule and expectations</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>❓ What If Student Doesn't Hire?</strong></p>
                <p>If the student doesn't complete the hire within a reasonable time, the application may remain open for other opportunities. You can follow up through the platform or apply to other postings.</p>
            </div>

            <p>Congratulations on getting your application accepted! We're confident this will lead to a great tutoring opportunity.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $tutor->email }}</span>.</p>

            <div class="support">
                <p>Questions? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Supporting tutors • Empowering education</p>
            </div>
        </div>
    </div>
</body>

</html>
