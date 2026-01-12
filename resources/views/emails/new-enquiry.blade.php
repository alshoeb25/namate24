<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Enquiry Notification</title>
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

        /* Details table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }

        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .details-table td:first-child {
            font-weight: bold;
            color: #666;
            width: 35%;
        }

        .details-table td:last-child {
            color: #333;
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

        /* Notification badge */
        .notification-badge {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .notification-badge h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 22px;
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
            <img src="{{ asset('storage/logo.png') }}"
                alt="Namate24 Logo" class="logo">
        </div>

        <!-- Main content -->
        <div class="content">
            <h1>📩 New Enquiry Received!</h1>

            <div class="notification-badge">
                <h2>You Have a New Student Enquiry</h2>
                <p style="margin: 0; color: white;">Someone is interested in your tutoring services!</p>
            </div>

            <p>Hello <span class="highlight">{{ $tutor->name }}</span>,</p>

            <p>Great news! A student has sent you an enquiry about your tutoring services. Here are the details:</p>

            <table class="details-table">
                <tr>
                    <td>Student Name:</td>
                    <td><strong>{{ $student->name }}</strong></td>
                </tr>
                <tr>
                    <td>Subject:</td>
                    <td><strong>{{ $subject ?? 'Not specified' }}</strong></td>
                </tr>
                <tr>
                    <td>Level:</td>
                    <td><strong>{{ $level ?? 'Not specified' }}</strong></td>
                </tr>
                <tr>
                    <td>Enquiry Date:</td>
                    <td><strong>{{ $enquiryDate }}</strong></td>
                </tr>
            </table>

            @if(isset($message))
            <div class="info-box">
                <p><strong>Student's Message:</strong></p>
                <p>{{ $message }}</p>
            </div>
            @endif

            <a href="{{ $enquiryUrl }}" class="action-button">View Enquiry Details</a>

            <div class="info-box">
                <p><strong>💡 Quick Tip:</strong></p>
                <p>Respond quickly to increase your chances of converting this enquiry into a booking! Students appreciate prompt responses.</p>
            </div>

            <p>Log in to your dashboard to view full details and respond to this enquiry.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $tutor->email }}</span>.</p>

            <div class="support">
                <p>Need help managing enquiries? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Connecting students with great tutors</p>
            </div>
        </div>
    </div>
</body>

</html>
