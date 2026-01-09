<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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

        /* Booking details */
        .booking-card {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .booking-card h2 {
            color: white;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .booking-details {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

        .booking-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .booking-row:last-child {
            border-bottom: none;
        }

        /* Calendar icon */
        .calendar-box {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-month {
            color: #ff4081;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }

        .calendar-day {
            font-size: 48px;
            font-weight: bold;
            color: #d81b60;
            line-height: 1;
            margin: 10px 0;
        }

        .calendar-time {
            color: #666;
            font-size: 18px;
            font-weight: bold;
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

        /* Success badge */
        .success-badge {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .checkmark {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
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
            <img src="https://image2url.com/r2/bucket1/images/1767939315186-298731db-06a8-40da-b138-85c649ceec9c.png"
                alt="Namate24 Logo" class="logo">
        </div>

        <!-- Main content -->
        <div class="content">
            <h1>📅 Booking Confirmed!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <p style="margin: 0; color: white; font-size: 18px; font-weight: bold;">Your lesson is confirmed</p>
            </div>

            <p>Hello <span class="highlight">{{ $student->name }}</span>,</p>

            <p>Great news! Your tutoring session has been confirmed. Get ready for an amazing learning experience!</p>

            <div class="calendar-box">
                <div class="calendar-month">{{ $bookingMonth }}</div>
                <div class="calendar-day">{{ $bookingDay }}</div>
                <div class="calendar-time">{{ $bookingTime }}</div>
            </div>

            <div class="booking-card">
                <h2>📚 Session Details</h2>
                <div class="booking-details">
                    <div class="booking-row">
                        <span>Tutor:</span>
                        <span><strong>{{ $tutor->name }}</strong></span>
                    </div>
                    <div class="booking-row">
                        <span>Subject:</span>
                        <span><strong>{{ $subject }}</strong></span>
                    </div>
                    <div class="booking-row">
                        <span>Duration:</span>
                        <span><strong>{{ $duration }} minutes</strong></span>
                    </div>
                    <div class="booking-row">
                        <span>Type:</span>
                        <span><strong>{{ $sessionType }}</strong></span>
                    </div>
                    @if(isset($location))
                    <div class="booking-row">
                        <span>Location:</span>
                        <span><strong>{{ $location }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            <a href="{{ $bookingUrl }}" class="action-button">View Booking Details</a>

            @if(isset($meetingLink))
            <div class="info-box">
                <p><strong>🎥 Online Meeting Link:</strong></p>
                <p><a href="{{ $meetingLink }}" style="word-break: break-all;">{{ $meetingLink }}</a></p>
                <p style="margin-top: 10px; font-size: 14px;">This link will be active 15 minutes before your session starts.</p>
            </div>
            @endif

            <div class="info-box">
                <p><strong>📝 Before Your Session:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Prepare any questions or materials you want to cover</li>
                    <li>Test your internet connection and camera (for online sessions)</li>
                    <li>Be ready 5 minutes before the start time</li>
                    <li>Have a notebook and pen ready</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>Need to Reschedule?</strong></p>
                <p>If you need to change or cancel this booking, please do so at least 24 hours in advance through your dashboard or by contacting the tutor directly.</p>
            </div>

            <p><strong>Tutor Contact Information:</strong></p>
            <p>Email: <a href="mailto:{{ $tutor->email }}">{{ $tutor->email }}</a></p>
            @if(isset($tutor->phone))
            <p>Phone: {{ $tutor->phone }}</p>
            @endif

            <p>We hope you have a productive and enjoyable learning session!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $student->email }}</span>.</p>

            <div class="support">
                <p>Questions about your booking? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Happy learning! 📚</p>
            </div>
        </div>
    </div>
</body>

</html>
