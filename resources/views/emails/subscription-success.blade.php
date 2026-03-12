@component('mail::message')
# Subscription Activated Successfully! 🎉

Hi {{ $userName }},

Great news! Your subscription to **{{ $planName }}** is now active and ready to use.

## Subscription Details

| Detail | Value |
|--------|-------|
| **Plan** | {{ $planName }} |
| **Amount** | {{ $currencySymbol }}{{ $amount }} {{ $currency }} |
| **Validity** | {{ $validityDays }} days |
| **Views Allowed** | {{ $viewsAllowed }} |
| **Activated Date** | {{ $activatedAt }} |
| **Expiry Date** | {{ $expiresAt }} |

## Payment Information

| Detail | Value |
|--------|-------|
| **Payment ID** | {{ $paymentId }} |
| **Order ID** | {{ $orderId }} |
| **Payment Date** | {{ $paymentDate }} |
| **Payment Method** | {{ $paymentMethod }} |
@if($invoiceNumber)
| **Invoice Number** | {{ $invoiceNumber }} |
@endif

## What's Next?

You can now access premium content with your subscription. Visit your dashboard to start exploring!

@component('mail::button', ['url' => $subscriptionUrl])
View Subscription Details
@endcomponent

If you have any questions or need assistance, please don't hesitate to reach out to our support team.

Thank you for choosing Namate24!

**Regards,**
Namate24 Team

---

*This is an automated email. Please do not reply to this message.*
@endcomponent
