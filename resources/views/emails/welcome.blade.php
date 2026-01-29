<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Namate24</title>
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

        h2 {
            color: #d81b60;
            font-size: 22px;
            margin: 25px 0 15px 0;
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

        /* Welcome banner */
        .welcome-banner {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .welcome-banner h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 28px;
        }

        /* Features grid */
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 25px 0;
        }

        .feature-item {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #f0f0f0;
        }

        .feature-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .feature-title {
            font-weight: bold;
            color: #d81b60;
            margin-bottom: 8px;
        }

        .feature-desc {
            font-size: 14px;
            color: #666;
        }

        /* Steps */
        .steps {
            counter-reset: step-counter;
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }

        .step-item {
            counter-increment: step-counter;
            padding: 15px 15px 15px 60px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            position: relative;
        }

        .step-item::before {
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 15px;
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
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

            .features {
                grid-template-columns: 1fr;
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
            <div class="welcome-banner">
                <h2>🎉 Welcome to Namate24!</h2>
                <p style="margin: 0; color: white; font-size: 18px;">Your journey to better learning starts here</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>Welcome aboard! We're thrilled to have you join our community of passionate learners and expert tutors. You're now part of a platform that's transforming how people connect for learning.</p>

            <h2>🚀 Getting Started</h2>

            <ol class="steps">
                <li class="step-item">
                    <strong style="color: #d81b60;">Complete Your Profile</strong><br>
                    <span style="font-size: 14px; color: #666;">Add your details to help us personalize your experience</span>
                </li>
                <li class="step-item">
                    <strong style="color: #d81b60;">Browse Tutors</strong><br>
                    <span style="font-size: 14px; color: #666;">Find the perfect tutor for your learning needs</span>
                </li>
                <li class="step-item">
                    <strong style="color: #d81b60;">Send Your First Enquiry</strong><br>
                    <span style="font-size: 14px; color: #666;">Connect with tutors and start your learning journey</span>
                </li>
            </ol>

            <a href="{{ $dashboardUrl }}" class="action-button">Complete My Profile</a>

            <h2>✨ What You Can Do</h2>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">🔍</div>
                    <div class="feature-title">Find Expert Tutors</div>
                    <div class="feature-desc">Search and filter through qualified tutors</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💬</div>
                    <div class="feature-title">Direct Messaging</div>
                    <div class="feature-desc">Chat with tutors before committing</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🎯</div>
                    <div class="feature-title">Track Progress</div>
                    <div class="feature-desc">Monitor your learning journey</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">⭐</div>
                    <div class="feature-title">Reviews & Ratings</div>
                    <div class="feature-desc">Make informed decisions</div>
                </div>
            </div>

            <div class="info-box">
                <p><strong>💡 Pro Tip:</strong></p>
                <p>Complete your profile to get personalized tutor recommendations and increase your chances of finding the perfect match!</p>
            </div>

            <div class="info-box">
                <p><strong>Need Help Getting Started?</strong></p>
                <p>Check out our <a href="{{ config('app.url') }}/help">Help Center</a> or contact our support team. We're here to help you every step of the way!</p>
            </div>

            <p>We're excited to be part of your learning journey. Let's get started!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Questions? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>Follow us: <a href="#">Facebook</a> • <a href="#">Twitter</a> • <a href="#">Instagram</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Connecting students with great tutors • Building better futures</p>
            </div>
        </div>
    </div>
</body>

</html>
