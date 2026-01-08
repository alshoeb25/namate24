<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 0; }
        .container { max-width: 640px; margin: 0 auto; padding: 24px; }
        .card { background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        .logo { font-weight: 700; font-size: 20px; color: #ec4899; }
        .btn { display: inline-block; background: linear-gradient(120deg, #ec4899, #ef4444); color: #fff; padding: 12px 22px; border-radius: 12px; text-decoration: none; font-weight: 600; }
        .muted { color: #64748b; }
        .footer { margin-top: 24px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="logo">Namate24</div>
        <h2 style="margin-top: 16px; margin-bottom: 12px;">Verify your email</h2>
        <p class="muted" style="margin-top: 0;">Hi {{ $user->name }}, thanks for signing up! Please confirm your email to activate your account.</p>

        <p style="margin: 20px 0; text-align: center;">
            <a class="btn" href="{{ $verificationUrl }}" target="_blank" rel="noopener">Verify Email</a>
        </p>

        <p class="muted" style="margin-top: 0;">This link expires in 24 hours. If you didn’t create an account, you can safely ignore this email.</p>

        <div class="footer">Namate24 • Secure learning connections</div>
    </div>
</div>
</body>
</html>
