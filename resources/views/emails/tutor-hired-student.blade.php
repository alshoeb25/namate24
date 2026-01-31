<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Approached Successfully</title>
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

        /* Tutor card */
        .tutor-card {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .tutor-card h3 {
            color: white;
            margin-bottom: 15px;
            font-size: 22px;
        }

        .tutor-details {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .detail-row:last-child {
            border-bottom: none;
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

        /* Contact box */
        .contact-box {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            border: 2px solid #ff69b4;
        }

        .contact-item {
            padding: 10px 0;
            display: flex;
            align-items: center;
        }

        .contact-icon {
            font-size: 24px;
            margin-right: 15px;
            width: 30px;
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
            <h1> You Approached a Tutor!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <h2>You've Unlocked Tutor Contact Details</h2>
                <p style="margin: 0; color: white;">Connect with your tutor now!</p>
            </div>

            <p>Hello <span class="highlight">{{ $student->name }}</span>,</p>

            <p>Great news! You've successfully unlocked this tutor's contact details. You can now reach out to them directly to discuss your learning needs and schedule your first lesson.</p>

            <div class="tutor-card">
                <h3>Your Tutor Details</h3>
                <div class="tutor-details">
                    <div class="detail-row">
                        <span>Tutor Name:</span>
                        <span><strong>{{ $tutor->name }}</strong></span>
                    </div>
                    @if(isset($subject))
                    <div class="detail-row">
                        <span>Subject:</span>
                        <span><strong>{{ $subject }}</strong></span>
                    </div>
                    @endif
                    @if(isset($level))
                    <div class="detail-row">
                        <span>Level:</span>
                        <span><strong>{{ $level }}</strong></span>
                    </div>
                    @endif
                    @if(isset($hourlyRate))
                    <div class="detail-row">
                        <span>Rate:</span>
                        <span><strong>{{ $hourlyRate }}</strong></span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span>Contact Unlocked:</span>
                        <span><strong>{{ $approachedDate }}</strong></span>
                    </div>
                </div>
            </div>

            <a href="{{ $chatUrl ?? $dashboardUrl }}" class="action-button">Contact Tutor Now</a>

            <div class="contact-box">
                <h3 style="color: #d81b60; margin-bottom: 15px;"> Contact This Tutor</h3>
                <div class="contact-item">
                    <span class="contact-icon">📧</span>
                    <div>
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $tutor->email }}">{{ $tutor->email }}</a>
                    </div>
                </div>
                @if(isset($tutor->phone))
                <div class="contact-item">
                    <span class="contact-icon">📱</span>
                    <div>
                        <strong>Phone:</strong><br>
                        {{ $tutor->phone }}
                    </div>
                </div>
                @endif
                @if(isset($tutor->profile_url))
                <div class="contact-item">
                    <span class="contact-icon">👤</span>
                    <div>
                        <strong>Profile:</strong><br>
                        <a href="{{ $tutor->profile_url }}">View Full Profile</a>
                    </div>
                </div>
                @endif
            </div>

            <div class="steps-box">
                <p><strong>What to Do Next:</strong></p>
                <ol>
                    <li><strong>Reach Out to the Tutor</strong> - Use the contact details above to introduce yourself</li>
                    <li><strong>Discuss Your Requirements</strong> - Explain what you want to learn and your goals</li>
                    <li><strong>Schedule Your First Session</strong> - Agree on timing, location, and lesson format</li>
                    <li><strong>Start Learning</strong> - Begin your educational journey!</li>
                </ol>
            </div>

            <div class="info-box">
                <p><strong>Tips for a Great Learning Experience:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Be clear about your learning goals and expectations</li>
                    <li>Maintain regular communication with your tutor</li>
                    <li>Be punctual and prepared for each session</li>
                    <li>Ask questions and provide feedback</li>
                    <li>Practice between sessions to reinforce learning</li>
                </ul>
            </div>

            @if(isset($coinsSpent))
            <div class="info-box">
                <p><strong> Transaction Details:</strong></p>
                <p>Coins spent for this approach: <span class="highlight">{{ $coinsSpent }} Coins</span></p>
                <p>Your current balance: <span class="highlight">{{ $currentBalance }} Coins</span></p>
            </div>
            @endif

            <div class="info-box">
                <p><strong> Need to Make Changes?</strong></p>
                <p>If you need to reschedule or have any concerns, please communicate directly with your tutor. For any issues or disputes, our support team is here to help.</p>
            </div>

            <p>We're excited for you to start this learning journey! If you have any questions or need assistance, don't hesitate to reach out to our support team.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $student->email }}</span>.</p>

            <div class="support">
                <p>Need help? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Happy learning! 📚 • Your success is our mission</p>
            </div>
        </div>
    </div>
</body>

</html>
