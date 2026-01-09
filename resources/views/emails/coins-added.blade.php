<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coins Added to Your Account</title>
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

        /* Coins card */
        .coins-card {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            padding: 30px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        .coin-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
        }

        .coins-added {
            font-size: 48px;
            font-weight: bold;
            color: #d81b60;
            margin: 10px 0;
        }

        .coins-label {
            font-size: 18px;
            color: #666;
            font-weight: 600;
        }

        /* Balance display */
        .balance-box {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 25px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
            text-align: center;
        }

        .balance-label {
            color: #2e7d32;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .balance-amount {
            font-size: 42px;
            font-weight: bold;
            color: #2e7d32;
            margin: 10px 0;
        }

        /* Transaction details */
        .transaction-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
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
        }

        .detail-value {
            font-weight: bold;
            color: #333;
        }

        /* Usage suggestions */
        .suggestions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 25px 0;
        }

        .suggestion-item {
            background-color: #fff0f6;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #ffd9e8;
        }

        .suggestion-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .suggestion-title {
            font-weight: bold;
            color: #d81b60;
            margin-bottom: 5px;
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

            .suggestions-grid {
                grid-template-columns: 1fr;
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
            <h1>🎊 Coins Added Successfully!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <h2>Your Coins Have Been Credited</h2>
                <p style="margin: 0; color: white;">Start using them right away!</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>Great news! Coins have been successfully added to your account. You can now use them to unlock premium features and connect with tutors.</p>

            <div class="coins-card">
                <span class="coin-icon">🪙</span>
                <p class="coins-label">Coins Added</p>
                <p class="coins-added">+{{ $coinsAdded }}</p>
                <p style="margin: 0; font-size: 14px; color: #666;">Added to your account</p>
            </div>

            <div class="balance-box">
                <p class="balance-label">💰 Your Total Balance</p>
                <p class="balance-amount">{{ $currentBalance }} Coins</p>
            </div>

            @if(isset($source) && $source === 'purchase')
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">{{ $transactionDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Source:</span>
                    <span class="detail-value">{{ $sourceDisplay ?? 'Purchase' }}</span>
                </div>
                @if(isset($amount))
                <div class="detail-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value">{{ $currency }} {{ $amount }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Coins Credited:</span>
                    <span class="detail-value" style="color: #4caf50;">+{{ $coinsAdded }} Coins</span>
                </div>
            </div>
            @elseif(isset($source))
            <div class="info-box">
                <p><strong>📋 Transaction Details:</strong></p>
                <p><strong>Source:</strong> {{ $sourceDisplay ?? ucfirst($source) }}</p>
                <p><strong>Date:</strong> {{ $transactionDate }}</p>
                @if(isset($reason))
                <p><strong>Reason:</strong> {{ $reason }}</p>
                @endif
            </div>
            @endif

            <a href="{{ $walletUrl }}" class="action-button">View My Wallet</a>

            <div class="info-box">
                <p><strong>✨ What Can You Do With Your Coins?</strong></p>
            </div>

            <div class="suggestions-grid">
                <div class="suggestion-item">
                    <div class="suggestion-icon">📝</div>
                    <div class="suggestion-title">Post Requirements</div>
                    <p style="font-size: 13px; color: #666; margin: 0;">Find the perfect tutor</p>
                </div>
                <div class="suggestion-item">
                    <div class="suggestion-icon">🔓</div>
                    <div class="suggestion-title">Unlock Enquiries</div>
                    <p style="font-size: 13px; color: #666; margin: 0;">Access tutor details</p>
                </div>
                <div class="suggestion-item">
                    <div class="suggestion-icon">💬</div>
                    <div class="suggestion-title">Direct Messaging</div>
                    <p style="font-size: 13px; color: #666; margin: 0;">Chat with tutors</p>
                </div>
                <div class="suggestion-item">
                    <div class="suggestion-icon">⭐</div>
                    <div class="suggestion-title">Premium Features</div>
                    <p style="font-size: 13px; color: #666; margin: 0;">Exclusive benefits</p>
                </div>
            </div>

            <div class="info-box">
                <p><strong>💡 Pro Tips:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Coins never expire - use them at your own pace</li>
                    <li>Track all transactions in your wallet history</li>
                    <li>Get refunds for unused enquiries</li>
                    <li>Purchase more coins anytime for better value packages</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>🎯 Ready to Start?</strong></p>
                <p>Your coins are ready to use! Browse our platform to find experienced tutors, post your learning requirements, or unlock tutor profiles to start your learning journey.</p>
                <p style="margin-top: 15px;"><a href="{{ config('app.url') }}/tutors">Browse Tutors</a> • <a href="{{ config('app.url') }}/post-requirement">Post Requirement</a></p>
            </div>

            <p>Thank you for choosing {{ config('app.name') }}. We're here to support your learning journey every step of the way!</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Questions about your coins? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Empowering learning • Building futures</p>
            </div>
        </div>
    </div>
</body>

</html>
