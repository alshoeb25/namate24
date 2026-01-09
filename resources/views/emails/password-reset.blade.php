<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
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

        /* Reset button */
        .reset-button {
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

        .reset-button:hover {
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

        /* Security notice */
        .security-notice {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
            font-size: 14px;
            border-left: 3px solid #ff69b4;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .reset-button {
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
            <h1>Reset Your Password</h1>

            <p>Hello <span class="highlight">{{ $user->name ?? 'there' }}</span>,</p>

            <p>We received a request to reset the password for your account associated with <span
                    class="highlight">{{ $user->email }}</span>. If you made this request, please click the button below to
                create a new password:</p>

            <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>

            <div class="warning">
                <span class="warning-icon">⚠</span> This password reset link will expire in <span class="highlight">1
                    hour</span> for security reasons.
            </div>

            <div class="alternative-link">
                <p>If the button above doesn't work, copy and paste this link into your browser:</p>
                <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
            </div>

            <div class="info-box">
                <p><strong>Why did I receive this email?</strong></p>
                <p>This email was sent because someone requested a password reset for your account. If you didn't make
                    this request, you can safely ignore this email. Your password will remain unchanged.</p>
            </div>

            <div class="security-notice">
                <p><strong>Security Tip:</strong> For your security, never share your password or this reset link with
                    anyone. Our support team will never ask for your password.</p>
            </div>

            <p>Need help or have questions? Contact our support team for assistance.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <p>If you're having trouble with the button above, copy and paste the URL below into your web browser:</p>
            <p><small>{{ $resetUrl }}</small></p>

            <div class="support">
                <p>Need additional help? Contact our support team</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>
