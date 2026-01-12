<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirement Posted Successfully</title>
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

        /* Requirement card */
        .requirement-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid #ff69b4;
        }

        .requirement-header {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .requirement-header h3 {
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

        /* Stats box */
        .stats-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 25px 0;
        }

        .stat-item {
            background-color: #fff0f6;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #ffd9e8;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #ff4081;
            margin: 5px 0;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
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

            .stats-box {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with pink logo -->
        <div class="header">
            <img src="{{ asset('storage/logo.png') }}"
                alt="Namate24 Logo" class="logo">
        </div>

        <!-- Main content -->
        <div class="content">
            <h1>✅ Requirement Posted Successfully!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <h2>Your Requirement is Now Live</h2>
                <p style="margin: 0; color: white;">Tutors can now view and respond to your request</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>Great news! Your tutoring requirement has been successfully posted on our platform. Qualified tutors in your area will now be able to see your request and reach out to you.</p>

            <div class="requirement-card">
                <div class="requirement-header">
                    <h3>📋 Your Requirement Details</h3>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Requirement ID:</span>
                    <span class="detail-value">{{ $requirementId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Subject:</span>
                    <span class="detail-value">{{ $subject }}</span>
                </div>
                @if(isset($level))
                <div class="detail-row">
                    <span class="detail-label">Level:</span>
                    <span class="detail-value">{{ $level }}</span>
                </div>
                @endif
                @if(isset($location))
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value">{{ $location }}</span>
                </div>
                @endif
                @if(isset($budget))
                <div class="detail-row">
                    <span class="detail-label">Budget:</span>
                    <span class="detail-value">{{ $budget }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Posted On:</span>
                    <span class="detail-value">{{ $postedDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #4caf50;">Active & Visible</span>
                </div>
            </div>

            @if(isset($coinsSpent))
            <div class="info-box">
                <p><strong>💰 Coins Deducted:</strong></p>
                <p style="font-size: 24px; color: #ff4081; font-weight: bold; margin: 10px 0;">{{ $coinsSpent }} Coins</p>
                <p>Your current balance: <span class="highlight">{{ $currentBalance }} Coins</span></p>
            </div>
            @endif

            <div class="stats-box">
                <div class="stat-item">
                    <div class="stat-number">{{ $matchingTutors ?? '50+' }}</div>
                    <div class="stat-label">Matching Tutors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24-48h</div>
                    <div class="stat-label">Response Time</div>
                </div>
            </div>

            <a href="{{ $requirementUrl }}" class="action-button">View My Requirement</a>

            <div class="steps-box">
                <p><strong>🎯 What Happens Next?</strong></p>
                <ol>
                    <li><strong>Tutors Review Your Requirement</strong> - Qualified tutors in your area will see your post</li>
                    <li><strong>You Receive Applications</strong> - Interested tutors will contact you directly</li>
                    <li><strong>Review & Compare</strong> - Check tutor profiles, ratings, and experience</li>
                    <li><strong>Choose Your Tutor</strong> - Select the best match and schedule your first lesson</li>
                </ol>
            </div>

            <div class="info-box">
                <p><strong>📧 Stay Updated</strong></p>
                <p>You'll receive email notifications when:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>A tutor expresses interest in your requirement</li>
                    <li>You receive new messages from tutors</li>
                    <li>Your requirement is about to expire (if applicable)</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>💡 Pro Tips for Getting Better Responses:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Keep your requirement details clear and specific</li>
                    <li>Respond promptly to tutor inquiries</li>
                    <li>Check tutor profiles and reviews before making a decision</li>
                    <li>Ask questions to ensure they're the right fit</li>
                </ul>
            </div>

            <p>We're excited to help you find the perfect tutor. If you have any questions or need assistance, our support team is always here to help!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Need help? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Connecting students with great tutors • Your learning journey starts here</p>
            </div>
        </div>
    </div>
</body>

</html>
