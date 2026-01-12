<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
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

        /* Error badge */
        .error-badge {
            background: linear-gradient(135deg, #f44336 0%, #e53935 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .error-badge h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 22px;
        }

        .error-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        /* Payment details */
        .payment-details {
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

        /* Common reasons */
        .reasons-list {
            background-color: #fff8e1;
            border-left: 4px solid #ffa726;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }

        .reasons-list ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .reasons-list li {
            margin-bottom: 8px;
            color: #5d4037;
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
            <h1>⚠️ Payment Failed</h1>

            <div class="error-badge">
                <span class="error-icon">✖</span>
                <h2>Payment Could Not Be Processed</h2>
                <p style="margin: 0; color: white;">We were unable to complete your transaction</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>We attempted to process your payment, but unfortunately it was declined. Don't worry - no charges have been made to your account.</p>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Attempted Date:</span>
                    <span class="detail-value">{{ $attemptDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Coin Package:</span>
                    <span class="detail-value">{{ $coins }} Coins</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
                </div>
                @if(isset($errorMessage))
                <div class="detail-row">
                    <span class="detail-label">Error:</span>
                    <span class="detail-value" style="color: #f44336;">{{ $errorMessage }}</span>
                </div>
                @endif
            </div>

            <div class="reasons-list">
                <p><strong>⚡ Common Reasons for Payment Failure:</strong></p>
                <ul>
                    <li>Insufficient funds in your account</li>
                    <li>Incorrect card details or expired card</li>
                    <li>Payment limit exceeded</li>
                    <li>Card blocked by your bank for online transactions</li>
                    <li>Network or connectivity issues</li>
                </ul>
            </div>

            <a href="{{ $retryUrl }}" class="action-button">Try Again</a>

            <div class="info-box">
                <p><strong>💡 What You Can Do:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Check your card details</strong> - Verify card number, CVV, and expiry date</li>
                    <li><strong>Contact your bank</strong> - Ensure online payments are enabled</li>
                    <li><strong>Try a different payment method</strong> - Use another card or payment option</li>
                    <li><strong>Check your balance</strong> - Make sure you have sufficient funds</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>Need Help?</strong></p>
                <p>If you continue to experience issues, our support team is here to help. We can assist you with:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Alternative payment methods</li>
                    <li>Troubleshooting payment issues</li>
                    <li>Account verification</li>
                </ul>
                <p style="margin-top: 15px;"><a href="{{ config('app.url') }}/support">Contact Support Team</a></p>
            </div>

            <p>We understand this can be frustrating, and we're here to help you complete your purchase as quickly as possible.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Having payment issues? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Secure payments • Trusted platform</p>
            </div>
        </div>
    </div>
</body>

</html>
