@component('mail::message')
# Reset your password

Hi {{ $user->name ?? 'there' }},

We received a request to reset your password. Click the button below to set a new one. This link will expire soon for security.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, you can safely ignore this email.

Thanks,
{{ config('app.name') }}
@endcomponent
