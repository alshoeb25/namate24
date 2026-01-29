<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Pending</title>
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

        /* Pending badge */
        .pending-badge {
            background: linear-gradient(135deg, #ff9800 0%, #ffa726 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .pending-badge h2 {
            color: white;
            margin-bottom: 10px;
            font-size: 22px;
        }

        .pending-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
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

        /* Status timeline */
        .timeline {
            background-color: #fff8e1;
            border-left: 4px solid #ffa726;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }

        .timeline-step {
            padding: 10px 0;
            display: flex;
            align-items: center;
        }

        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ffa726;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-weight: bold;
        }

        .step-icon.active {
            background: linear-gradient(135deg, #ff9800 0%, #ffa726 100%);
            animation: pulse 2s ease-in-out infinite;
        }

        .step-icon.completed {
            background-color: #4caf50;
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
            <h1>⏳ Payment Processing</h1>

            <div class="pending-badge">
                <span class="pending-icon">⏰</span>
                <h2>Your Payment is Being Processed</h2>
                <p style="margin: 0; color: white;">Please wait while we confirm your transaction</p>
            </div>

            <p>Hello <span class="highlight">{{ $user->name }}</span>,</p>

            <p>We've received your payment request and it's currently being processed. This usually takes a few minutes, but can sometimes take up to 24 hours depending on your payment method.</p>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Transaction ID:</span>
                    <span class="detail-value">{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Initiated On:</span>
                    <span class="detail-value">{{ $initiatedDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Coin Package:</span>
                    <span class="detail-value">{{ $coins }} Coins</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $paymentMethod }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #ff9800;">⏳ Processing</span>
                </div>
            </div>

            <div class="timeline">
                <p><strong>📊 Transaction Progress:</strong></p>
                <div style="margin-top: 15px;">
                    <div class="timeline-step">
                        <div class="step-icon completed">✓</div>
                        <div>
                            <strong>Payment Initiated</strong><br>
                            <small style="color: #666;">Your payment request was received</small>
                        </div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-icon active">2</div>
                        <div>
                            <strong>Processing Payment</strong><br>
                            <small style="color: #666;">Verifying with payment gateway...</small>
                        </div>
                    </div>
                    <div class="timeline-step">
                        <div class="step-icon">3</div>
                        <div>
                            <strong>Coins Added</strong><br>
                            <small style="color: #666;">Coins will be credited to your account</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <p><strong>⏱️ Expected Processing Time:</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Credit/Debit Card:</strong> 2-5 minutes</li>
                    <li><strong>Bank Transfer:</strong> 1-24 hours</li>
                    <li><strong>Digital Wallet:</strong> Instant to 10 minutes</li>
                    <li><strong>Other Methods:</strong> Up to 24 hours</li>
                </ul>
            </div>

            <a href="{{ $statusUrl }}" class="action-button">Check Payment Status</a>

            <div class="info-box">
                <p><strong>💡 What Happens Next?</strong></p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>We'll send you an email once payment is confirmed</li>
                    <li>Coins will be automatically added to your account</li>
                    <li>You'll receive a receipt for your records</li>
                    <li>You can immediately start using your coins</li>
                </ul>
            </div>

            <div class="info-box">
                <p><strong>⚠️ Payment Taking Longer Than Expected?</strong></p>
                <p>If your payment is still pending after the expected time:</p>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Check your email for confirmation from your bank/payment provider</li>
                    <li>Verify your account has sufficient funds</li>
                    <li>Contact your bank if the amount was deducted but coins not credited</li>
                    <li>Reach out to our support team with your transaction ID</li>
                </ul>
            </div>

            <p><strong>Important:</strong> Do not attempt another payment until this transaction is complete. If you have concerns, please contact our support team with your transaction ID.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This email was sent to <span class="highlight">{{ $user->email }}</span>.</p>

            <div class="support">
                <p>Payment issues? <a href="{{ config('app.url') }}/support">Contact our support team</a></p>
                <p>Transaction ID: <strong>{{ $transactionId }}</strong></p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="margin-top: 10px;">Secure payments • Trusted platform</p>
            </div>
        </div>
    </div>
</body>

</html>
