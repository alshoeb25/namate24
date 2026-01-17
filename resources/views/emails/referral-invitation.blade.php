<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited to NaMate24</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .logo-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 12px;
            background: white;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .logo-header p {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
            font-weight: 300;
        }

        .content {
            padding: 40px;
            color: #2d3748;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .message {
            font-size: 15px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        .welcome-box {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .coin-highlight {
            text-align: center;
            margin: 20px 0;
        }

        .coin-amount {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
            margin: 10px 0;
        }

        .coin-label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .referral-box {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }

        .referral-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .referral-code {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 3px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }

        .referral-subtitle {
            font-size: 13px;
            color: #718096;
            margin-top: 8px;
        }

        .button-container {
            text-align: center;
            margin: 35px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 16px 48px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            display: inline-block;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
        }

        .features {
            background: #f7fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .features-title {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            padding: 8px 0;
            color: #4a5568;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .checkmark {
            color: #48bb78;
            font-weight: 700;
            flex-shrink: 0;
        }

        .link-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .link-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
            display: block;
        }

        .referral-link {
            word-break: break-all;
            color: #667eea;
            text-decoration: none;
            font-size: 12px;
            display: block;
            padding: 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-weight: 500;
        }

        .info-section {
            background: #fffaf0;
            border-left: 4px solid #ed8936;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 13px;
            color: #7c2d12;
            line-height: 1.6;
        }

        .footer {
            background: #f7fafc;
            padding: 30px 40px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .footer-text {
            font-size: 12px;
            color: #718096;
            line-height: 1.8;
        }

        .footer-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }

            .logo-header {
                padding: 30px 20px;
            }

            .logo-header h1 {
                font-size: 24px;
            }

            .content {
                padding: 25px;
            }

            .coin-amount {
                font-size: 36px;
            }

            .cta-button {
                width: 100%;
                padding: 14px 30px;
            }

            .referral-code {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo Header -->
        <div class="logo-header">
            <div class="logo">
                <img src="https://image2url.com/images/1765179057005-967d0875-ac5d-4a43-b65f-a58abd9f651d.png" alt="NaMate24 Logo">
            </div>
            <h1>{{ $appName ?? 'NaMate24' }}</h1>
            <p>Connecting Students with Expert Tutors</p>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                Hello! 👋
            </div>

            <div class="message">
                You've been invited to join <strong>{{ $appName ?? 'NaMate24' }}</strong>, a platform connecting ambitious students with verified tutors worldwide.
            </div>

            <!-- Welcome Bonus -->
            <div class="welcome-box">
                <div class="coin-highlight">
                    <div class="coin-label">💰 Welcome Bonus</div>
                    <div class="coin-amount">{{ $coins ?? 0 }}</div>
                    <div class="coin-label">Free Coins</div>
                </div>
                <div style="text-align: center; font-size: 14px; color: #555; margin-top: 12px;">
                    Use these coins to unlock tutor contacts, post learning requirements, and start your journey!
                </div>
            </div>

            <!-- Referral Code Box -->
            <div class="referral-box">
                <div class="referral-label">✨ Your Referral Code</div>
                <div class="referral-code">{{ $referralCode ?? 'N/A' }}</div>
                <div class="referral-subtitle">
                    Share this code with friends or use it during registration
                </div>
            </div>

            <!-- CTA Button -->
            <div class="button-container">
                <a href="{{ $referralLink ?? $appUrl }}" class="cta-button">
                    Create Your Account Now
                </a>
            </div>

            <!-- Features -->
            <div class="features">
                <div class="features-title">
                    ✨ Why Join NaMate24?
                </div>
                <ul class="features-list">
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Expert Tutors</strong> - Access qualified tutors from top institutions</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Personalized Learning</strong> - Custom lessons tailored to your needs</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Flexible Scheduling</strong> - Learn at your own pace, anytime</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Affordable</strong> - High-quality education within budget</span>
                    </li>
                </ul>
            </div>

            <!-- Link Section -->
            <div class="link-section">
                <label class="link-label">Or copy this link to your browser:</label>
                <a href="{{ $referralLink ?? $appUrl }}" class="referral-link">
                    {{ $referralLink ?? $appUrl }}
                </a>
            </div>

            <!-- Info -->
            <div class="info-section">
                <strong>📌 Getting Started:</strong><br>
                1️⃣ Click the button above or copy the link<br>
                2️⃣ Sign up with your email/phone<br>
                3️⃣ Enter the referral code: <strong>{{ $referralCode ?? 'N/A' }}</strong><br>
                4️⃣ Get {{ $coins ?? 0 }} coins instantly and start exploring!
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <p style="margin-bottom: 12px;">
                    This invitation was sent to <strong>{{ $email }}</strong><br>
                    If you didn't expect this email, you can safely ignore it.
                </p>
                <p style="margin: 12px 0;">
                    <a href="{{ $appUrl }}">Visit NaMate24</a> • 
                    <a href="mailto:{{ config('mail.from.address') }}">Contact Support</a>
                </p>
                <p style="margin-top: 12px; color: #a0aec0;">
                    © {{ date('Y') }} {{ $appName ?? 'NaMate24' }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
            text-align: center;
            line-height: 36px;
            color: #667eea;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 30px 0;
        }

        .timer {
            background: #fff5f5;
            border-left: 4px solid #fc8181;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 13px;
            color: #742a2a;
        }

        @media (max-width: 600px) {
            .container {
                border-radius: 0;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 20px;
            }

            .coin-value {
                font-size: 32px;
            }

            .cta-button {
                padding: 12px 30px;
                font-size: 14px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎉 You're Invited to Namate24</h1>
            <p>Join thousands of students and tutors on the best learning platform</p>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="welcome">
                Hello there! 👋
            </div>

            <p style="color: #4a5568; font-size: 15px; line-height: 1.7; margin-bottom: 20px;">
                We're excited to welcome you to <strong>Namate24</strong>, the ultimate platform connecting dedicated tutors with ambitious students. Your learning journey is about to become extraordinary!
            </p>

            <!-- Coins Highlight -->
            <div class="highlight-box">
                <div style="text-align: center;">
                    <div class="coin-label">Welcome Bonus</div>
                    <div class="coin-amount">
                        <span>💰</span>
                        <div class="coin-value">{{ $referralInvite->referred_coins }}</div>
                    </div>
                    <div class="coin-label">Coins Added to Your Account</div>
                </div>
                <p style="text-align: center; color: #718096; font-size: 13px; margin-top: 15px;">
                    Use these coins to unlock tutoring sessions, get personalized help, and achieve your goals!
                </p>
            </div>

            <!-- Benefits Section -->
            <div class="benefits">
                <div class="benefits-title">
                    ✨ Why Join Namate24?
                </div>
                <ul class="benefits-list">
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Expert Tutors</strong> - Connect with verified tutors from top institutions</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Personalized Learning</strong> - Get custom learning plans tailored to your needs</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Flexible Scheduling</strong> - Learn at your own pace, whenever you want</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>Affordable Pricing</strong> - High-quality education without breaking the bank</span>
                    </li>
                    <li>
                        <span class="checkmark">✓</span>
                        <span><strong>24/7 Support</strong> - Our team is always here to help</span>
                    </li>
                </ul>
            </div>

            <!-- CTA Button -->
            <div class="cta-container">
                @php
                    $signupUrl = config('app.frontend_url') . '/register';
                    
                    // Add referral code if available
                    if ($referralInvite->referralCode) {
                        $signupUrl .= '?referral_code=' . urlencode($referralInvite->referralCode->referral_code);
                    } else {
                        // Otherwise use email
                        $signupUrl .= '?referral=' . base64_encode($referralInvite->email);
                    }
                @endphp
                <a href="{{ $signupUrl }}" class="cta-button">
                    Start Learning Now
                </a>
            </div>

            <p style="text-align: center; color: #718096; font-size: 13px; margin-top: 15px;">
                No credit card required • Free to start
            </p>

            <!-- Info Box -->
            <div class="info-section">
                <strong>📌 How It Works:</strong><br>
                1. Click the button above to sign up<br>
                2. Verify your email<br>
                3. Your {{ $referralInvite->referred_coins }} coins will be automatically added<br>
                4. Start browsing tutors and book your first session!
            </div>

            <!-- Timer Notice -->
            <div class="timer">
                <strong>⏰ Heads Up!</strong> This invite is valid for 90 days. Make sure to sign up before it expires!
            </div>

            <div class="divider"></div>

            <!-- Personal Touch -->
            <p style="color: #4a5568; font-size: 14px; line-height: 1.7; margin-bottom: 10px;">
                If you have any questions or need assistance getting started, our support team is just an email away. We're here to help you succeed!
            </p>

            <p style="color: #4a5568; font-size: 14px; line-height: 1.7; margin-bottom: 20px;">
                Happy learning! 🚀
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                <p style="margin-bottom: 15px;">
                    © {{ date('Y') }} Namate24. All rights reserved.
                </p>
                <p style="margin-bottom: 15px; color: #a0aec0;">
                    <a href="{{ config('app.frontend_url') }}/help" style="color: #667eea; text-decoration: none;">Help Center</a> • 
                    <a href="{{ config('app.frontend_url') }}/terms" style="color: #667eea; text-decoration: none;">Terms of Service</a> • 
                    <a href="{{ config('app.frontend_url') }}/privacy" style="color: #667eea; text-decoration: none;">Privacy Policy</a>
                </p>
                <p style="font-size: 11px; color: #cbd5e0;">
                    You received this email because you were invited to Namate24. If you didn't expect this email, you can safely ignore it.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
