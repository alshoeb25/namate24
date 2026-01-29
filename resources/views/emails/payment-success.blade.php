<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
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

        /* Payment summary */
        .payment-summary {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .payment-row:last-child {
            border-bottom: none;
            padding-top: 20px;
            margin-top: 10px;
            border-top: 2px solid #ff69b4;
            font-size: 20px;
            font-weight: bold;
            color: #d81b60;
        }

        .payment-label {
            color: #666;
        }

        .payment-value {
            font-weight: bold;
            color: #333;
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

        .success-badge h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 22px;
        }

        .checkmark {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
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
            <h1>💳 Payment Successful!</h1>

            <div class="success-badge">
                <span class="checkmark">✓</span>
                <h2>Thank You for Your Payment</h2>
                <p style="margin: 0; color: white;">Your transaction has been completed successfully</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>We've received your payment and your coins have been added to your account. Here's a summary of your transaction:</p>

            <div class="payment-summary">
                <div class="payment-row">
                    <span class="payment-label">Transaction ID:</span>
                    <span class="payment-value">{{ $transactionId }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Payment Date:</span>
                    <span class="payment-value">{{ $paymentDate }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Coins Purchased:</span>
                    <span class="payment-value">{{ $coins }} Coins</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Payment Method:</span>
                    <span class="payment-value">{{ $paymentMethod }}</span>
                </div>
                <div class="payment-row">
                    <span class="payment-label">Amount Paid:</span>
                    <span class="payment-value">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
                </div>
            </div>

            <div class="info-box">
                <p><strong>Your Current Balance:</strong></p>
                <p style="font-size: 24px; color: #ff4081; font-weight: bold; margin: 10px 0;">{{ $currentBalance }} Coins</p>
                <p>Your coins are now available for use. Start connecting with tutors and unlocking opportunities!</p>
            </div>

            <a href="{{ $dashboardUrl }}" class="action-button">View My Dashboard</a>

            <div class="info-box">
                <p><strong>Need a receipt?</strong></p>
                <p>A detailed receipt has been attached to this email. You can also download it anytime from your transaction history.</p>
            </div>

            <p>Thank you for choosing {{ config('app.name') }}. If you have any questions about this transaction, please don't hesitate to contact our support team.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Questions about your payment? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Secure payments • Trusted platform</p>
            </div>
        </div>
    </div>
</body>

</html>
