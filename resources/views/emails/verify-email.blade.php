<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
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

        /* Verify button */
        .verify-button {
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

        .verify-button:hover {
            background-color: #e0559c;
        }

        /* Expiry warning */
        .warning {
            text-align: center;
            padding: 15px;
            background-color: #fff8e1;
            border: 1px solid #ffecb3;
            border-radius: 6px;
            margin: 25px 0;
            color: #5d4037;
        }

        .warning-icon {
            color: #ff9800;
            font-weight: bold;
        }

        /* Alternative link */
        .alternative-link {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            color: #777;
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

        /* Welcome box */
        .welcome-box {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .welcome-box h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 24px;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .verify-button {
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
            <h1>Verify Your Email Address</h1>

            <div class="welcome-box">
                <h2>Welcome to Namate24! 🎉</h2>
                <p style="margin: 0; color: white;">We're excited to have you join our learning community!</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>Thank you for creating an account with us! To get started and access all features, please verify your
                email address by clicking the button below:</p>

            <a href="{{ $verificationUrl }}" class="verify-button">Verify Email Address</a>

            <div class="warning">
                <span class="warning-icon">⚠</span> This verification link will expire in <span class="highlight">24
                    hours</span> for security reasons.
            </div>

            <div class="alternative-link">
                <p>If the button above doesn't work, copy and paste this link into your browser:</p>
                <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
            </div>

            <div class="info-box">
                <p><strong>Why verify your email?</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Access all platform features</li>
                    <li>Secure your account</li>
                    <li>Receive important notifications</li>
                    <li>Connect with tutors and students</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>Didn't create an account?</strong></p>
                <p>If you didn't sign up for {{ config('app.name') }}, you can safely ignore this email. No account has
                    been created yet.</p>
            </div>

            <p>Once verified, you'll be ready to explore everything our platform has to offer!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <p>If you're having trouble with the button above, copy and paste the URL below into your web browser:</p>
            <p><small>{{ $verificationUrl }}</small></p>

            <div class="support">
                <p>Need help getting started? Contact our support team</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Secure learning connections • Building better futures</p>
            </div>
        </div>
    </div>
</body>

</html>
