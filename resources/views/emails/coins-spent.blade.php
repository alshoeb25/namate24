<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coins Spent</title>
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

        /* Transaction card */
        .transaction-card {
            background: linear-gradient(135deg, #ff69b4 0%, #ff4081 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .coin-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
        }

        .coins-spent {
            font-size: 36px;
            font-weight: bold;
            color: white;
            margin: 10px 0;
        }

        .transaction-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
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

        /* Balance box */
        .balance-box {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
            text-align: center;
        }

        .balance-amount {
            font-size: 32px;
            font-weight: bold;
            color: #2e7d32;
            margin: 10px 0;
        }

        /* Warning box */
        .warning-box {
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
            <img src="{{ asset('storage/logo.png') }}"
                alt="Namate24 Logo" class="logo">
        </div>

        <!-- Main content -->
        <div class="content">
            <h1>🪙 Coins Spent</h1>

            <div class="transaction-card">
                <span class="coin-icon">💰</span>
                <p class="transaction-label">Coins Deducted</p>
                <p class="coins-spent">{{ $coinsSpent }} Coins</p>
                <p class="transaction-label">{{ $transactionType }}</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>This email confirms that coins have been deducted from your account for the following transaction:</p>

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
                    <span class="detail-label">Transaction Type:</span>
                    <span class="detail-value">{{ $transactionType }}</span>
                </div>
                @if(isset($description))
                <div class="detail-row">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value">{{ $description }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Coins Spent:</span>
                    <span class="detail-value" style="color: #f44336;">-{{ $coinsSpent }} Coins</span>
                </div>
            </div>

            <div class="balance-box">
                <p style="color: #2e7d32; font-weight: bold; margin-bottom: 10px;">Your Current Balance</p>
                <p class="balance-amount">{{ $currentBalance }} Coins</p>
            </div>

            @if($currentBalance < 10)
            <div class="warning-box">
                <p><strong>⚠️ Low Balance Alert</strong></p>
                <p>Your coin balance is running low. Consider purchasing more coins to continue enjoying our services without interruption.</p>
            </div>
            @endif

            <a href="{{ $walletUrl }}" class="action-button">View Transaction History</a>

            <div class="info-box">
                <p><strong>💡 What Were These Coins Used For?</strong></p>
                @if(isset($usageDetails))
                <p>{{ $usageDetails }}</p>
                @else
                <p>These coins were used to {{ strtolower($transactionType) }}. You can view full details in your transaction history.</p>
                @endif
            </div>

            @if($currentBalance > 0)
            <div class="info-box">
                <p><strong>✨ Continue Exploring</strong></p>
                <p>You still have <span class="highlight">{{ $currentBalance }} coins</span> available. Keep connecting with tutors and making the most of your learning journey!</p>
            </div>
            @else
            <div class="info-box">
                <p><strong>📦 Need More Coins?</strong></p>
                <p>Your balance is now empty. Purchase more coins to continue accessing our premium features and connecting with tutors.</p>
                <p style="margin-top: 15px;"><a href="{{ config('app.url') }}/coins/purchase">Purchase Coins</a></p>
            </div>
            @endif

            <p>If you believe this transaction was made in error, please contact our support team immediately.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Questions about this transaction? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Secure transactions • Transparent pricing</p>
            </div>
        </div>
    </div>
</body>

</html>
