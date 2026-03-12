<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\UserSubscription;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SubscriptionSuccessNotification extends Notification implements ShouldBroadcastNow
{
    public $order;
    public $subscription;
    public $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, UserSubscription $subscription, ?Invoice $invoice = null)
    {
        $this->order = $order;
        $this->subscription = $subscription;
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $plan = $this->subscription->plan;
        $currency = $this->order->currency ?? 'INR';
        $currencySymbol = $currency === 'USD' ? '$' : '₹';

        return (new MailMessage)
            ->subject('Subscription Activated Successfully!')
            ->view('emails.subscription-success', [
                'user' => $notifiable,
                'userName' => $notifiable->name,
                'plan' => $plan,
                'planName' => $plan->name,
                'amount' => $this->order->amount,
                'currency' => $currency,
                'currencySymbol' => $currencySymbol,
                'validityDays' => $plan->validity_days,
                'viewsAllowed' => $plan->views_allowed === null ? 'Unlimited' : $plan->views_allowed,
                'activatedAt' => $this->subscription->activated_at->format('M d, Y'),
                'expiresAt' => $this->subscription->expires_at->format('M d, Y'),
                'invoiceNumber' => $this->invoice?->invoice_number,
                'orderId' => $this->order->razorpay_order_id,
                'paymentId' => $this->order->razorpay_payment_id,
                'paymentDate' => $this->order->created_at->format('M d, Y h:i A'),
                'paymentMethod' => 'Razorpay (Online)',
                'dashboardUrl' => url(method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
                    ? '/tutor/wallet'
                    : '/student/wallet'),
                'subscriptionUrl' => url(method_exists($notifiable, 'hasRole') && $notifiable->hasRole('tutor')
                    ? '/tutor/subscriptions'
                    : '/student/subscriptions'),
            ]);
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'subscription_success',
            'title' => 'Subscription Activated!',
            'message' => "Your subscription to {$this->subscription->plan->name} is now active!",
            'data' => [
                'subscription_id' => $this->subscription->id,
                'plan_name' => $this->subscription->plan->name,
                'expires_at' => $this->subscription->expires_at,
                'order_id' => $this->order->id,
            ],
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'subscription_success',
            'title' => 'Subscription Activated!',
            'message' => "Your subscription to {$this->subscription->plan->name} is now active and valid until {$this->subscription->expires_at->format('M d, Y')}.",
            'order_id' => $this->order->id,
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'amount' => $this->order->amount,
            'currency' => $this->order->currency ?? 'INR',
            'expires_at' => $this->subscription->expires_at,
            'invoice_id' => $this->invoice?->id,
            'invoice_number' => $this->invoice?->invoice_number,
        ];
    }
}
