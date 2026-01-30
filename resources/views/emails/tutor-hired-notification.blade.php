<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You've Been Approached!</title>
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

        /* Celebration banner */
        .celebration-banner {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            padding: 30px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .celebration-banner h2 {
            color: #d81b60;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .celebration-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
        }

        /* Student card */
        .student-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            border: 2px solid #ff69b4;
        }

        .student-header {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .student-header h3 {
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

        /* Earnings box */
        .earnings-box {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .earnings-amount {
            font-size: 36px;
            font-weight: bold;
            color: white;
            margin: 10px 0;
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
            background-color: #fff8e1;
            border-left: 4px solid #ffa726;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
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
            <h1>🎊 Congratulations! You've Been Approached!</h1>

            <div class="celebration-banner">
                <span class="celebration-icon">🎉</span>
                <h2>You Got the Job!</h2>
                <p style="margin: 0; font-size: 18px; color: #555;">A student has chosen you as their tutor</p>
            </div>

            <p>Hello <span class="highlight">{{ $tutor->name }}</span>,</p>

            <p>Fantastic news! A student has accepted your application and approached you as their tutor. This is a great opportunity to make a positive impact on their learning journey!</p>

            <div class="student-card">
                <div class="student-header">
                    <h3>👨‍🎓 Student Information</h3>
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
                @if(isset($learningGoals))
                <div class="detail-row">
                    <span class="detail-label">Learning Goals:</span>
                    <span class="detail-value">{{ $learningGoals }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Approached On:</span>
                    <span class="detail-value">{{ $approachedDate }}</span>
                </div>
            </div>

            @if(isset($studentMessage))
            <div class="info-box">
                <p><strong>📝 Message from Student:</strong></p>
                <p style="font-style: italic;">"{{ $studentMessage }}"</p>
            </div>
            @endif

            @if(isset($earnings))
            <div class="earnings-box">
                <p style="font-size: 16px; color: white; margin-bottom: 5px;">Potential Earnings</p>
                <p class="earnings-amount">{{ $earnings }}</p>
                <p style="font-size: 14px; color: rgba(255,255,255,0.9); margin: 0;">Per session/hour</p>
            </div>
            @endif

            <a href="{{ $chatUrl ?? $dashboardUrl }}" class="action-button">Contact Student Now</a>

            @if(isset($myLearnersUrl))
            <p style="text-align: center; margin-top: -10px;">
                <a href="{{ $myLearnersUrl }}">View My Learners</a>
            </p>
            @endif

            <div class="contact-box">
                <p><strong>📞 Requirement Contact Details:</strong></p>
                @if(isset($requirementPhone))
                <p style="margin-top: 10px;"><strong>Phone:</strong> {{ $requirementPhone }}</p>
                @endif
                @if(isset($requirementAlternatePhone))
                <p><strong>Alternate Phone:</strong> {{ $requirementAlternatePhone }}</p>
                @endif
                <p style="margin-top: 10px;"><strong>Email:</strong> <a href="mailto:{{ $student->email }}">{{ $student->email }}</a></p>
            </div>

            <div class="steps-box">
                <p><strong>🚀 What To Do Next:</strong></p>
                <ol>
                    <li><strong>Reach Out Immediately</strong> - Contact the student to introduce yourself</li>
                    <li><strong>Discuss Expectations</strong> - Clarify learning goals, schedule, and teaching approach</li>
                    <li><strong>Schedule First Session</strong> - Agree on date, time, and location/platform</li>
                    <li><strong>Prepare Materials</strong> - Get ready with lesson plans and resources</li>
                    <li><strong>Deliver Excellence</strong> - Provide high-quality tutoring to build long-term relationship</li>
                </ol>
            </div>

            <div class="info-box">
                <p><strong>💡 Tips for Success:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Respond promptly to student messages</li>
                    <li>Be professional and punctual</li>
                    <li>Customize your teaching to student's learning style</li>
                    <li>Provide regular feedback on progress</li>
                    <li>Be patient and encouraging</li>
                    <li>Maintain clear communication</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>📋 Important Reminders:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Confirm session details at least 24 hours in advance</li>
                    <li>Prepare materials and lesson plans beforehand</li>
                    <li>Keep track of sessions for accurate payment</li>
                    <li>Encourage students to leave reviews after sessions</li>
                    <li>Report any issues to our support team</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>🆘 Need Help?</strong></p>
                <p>If you have any questions about this approach or need assistance with anything, our support team is here to help you succeed.</p>
                <p style="margin-top: 15px;"><a href="{{ config('app.url') }}/support">Contact Support Team</a></p>
            </div>

            <p>Congratulations again on being approached! We're confident you'll provide an excellent learning experience. Good luck with your new student!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $tutor->email }}</span>.</p>

            <div class="support">
                <p>Questions? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Empowering tutors • Transforming education</p>
            </div>
        </div>
    </div>
</body>

</html>
