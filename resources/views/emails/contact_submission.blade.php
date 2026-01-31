<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Submission - Namate24</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f7;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: 1px;
        }
        .logo-subtitle {
            color: #e0e7ff;
            font-size: 14px;
            margin-top: 5px;
        }
        .email-content {
            padding: 40px 30px;
        }
        .email-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .email-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 30px 0;
        }
        .info-card {
            background: #f9fafb;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: baseline;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #4b5563;
            min-width: 140px;
            font-size: 14px;
        }
        .info-value {
            color: #1f2937;
            font-size: 14px;
            flex: 1;
        }
        .message-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-label {
            font-weight: 600;
            color: #1e40af;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .message-content {
            color: #1f2937;
            font-size: 14px;
            white-space: pre-wrap;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-tutor {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-student {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-organisation {
            background-color: #fef3c7;
            color: #92400e;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 30px 0;
        }
        .footer-info {
            background: #f9fafb;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-row {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .footer-row strong {
            color: #4b5563;
        }
        .email-footer {
            background-color: #1f2937;
            color: #9ca3af;
            padding: 25px 30px;
            text-align: center;
            font-size: 12px;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 30px 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header with Logo -->
        <div class="email-header">
            <div class="logo">NAMATE24</div>
            <div class="logo-subtitle">Connect. Learn. Grow.</div>
        </div>

        <!-- Email Content -->
        <div class="email-content">
            <h1 class="email-title"> New Contact Submission</h1>
            <p class="email-subtitle">You have received a new contact form submission from your website.</p>

            <!-- User Type Badge -->
            <div style="margin-bottom: 20px;">
                <span class="badge badge-{{ $submission->user_type }}">
                    {{ ucfirst($submission->user_type) }}
                </span>
            </div>

            <!-- Contact Information -->
            <div class="info-card">
                @if(in_array($submission->user_type, ['tutor', 'student']))
                    <div class="info-row">
                        <span class="info-label">First Name:</span>
                        <span class="info-value">{{ $submission->first_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Name:</span>
                        <span class="info-value">{{ $submission->last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><a href="mailto:{{ $submission->email }}" style="color: #667eea; text-decoration: none;">{{ $submission->email }}</a></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mobile:</span>
                        <span class="info-value"><a href="tel:{{ $submission->mobile }}" style="color: #667eea; text-decoration: none;">{{ $submission->mobile }}</a></span>
                    </div>
                @elseif($submission->user_type === 'organisation')
                    <div class="info-row">
                        <span class="info-label">Organization:</span>
                        <span class="info-value">{{ $submission->organization_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Person:</span>
                        <span class="info-value">{{ $submission->contact_person }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><a href="mailto:{{ $submission->email }}" style="color: #667eea; text-decoration: none;">{{ $submission->email }}</a></span>
                    </div>
                @endif
            </div>

            <!-- Message -->
            @if($submission->message)
                <div class="message-box">
                    <div class="message-label">💬 Message:</div>
                    <div class="message-content">{{ $submission->message }}</div>
                </div>
            @endif

            <div class="divider"></div>

            <!-- Submission Details -->
            <div class="footer-info">
                <div class="footer-row">
                    <strong>Submitted:</strong> {{ $submission->created_at->format('l, F j, Y \a\t g:i A') }}
                </div>
                @if($submission->ip_address)
                    <div class="footer-row">
                        <strong>IP Address:</strong> {{ $submission->ip_address }}
                    </div>
                @endif
                @if($submission->user_agent)
                    <div class="footer-row">
                        <strong>Browser:</strong> {{ $submission->user_agent }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong style="color: #ffffff;">NAMATE24</strong>
            </p>
            <p style="margin: 0 0 10px 0;">
                Your trusted platform for connecting students with expert tutors
            </p>
            <p style="margin: 0;">
                <a href="{{ config('app.url') }}">Visit Website</a> | 
                <a href="{{ config('app.url') }}/admin">Admin Panel</a>
            </p>
        </div>
    </div>
</body>
</html>
