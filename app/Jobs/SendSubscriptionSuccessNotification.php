<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Models\UserSubscription;
use App\Models\Invoice;
use App\Notifications\SubscriptionSuccessNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSubscriptionSuccessNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $order;
    public $subscription;
    public $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Order $order, UserSubscription $subscription, ?Invoice $invoice = null)
    {
        $this->user = $user;
        $this->order = $order;
        $this->subscription = $subscription;
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->user->notify(new SubscriptionSuccessNotification(
                $this->order,
                $this->subscription,
                $this->invoice
            ));

            \Log::info('Subscription success notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'subscription_id' => $this->subscription->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send subscription success notification', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
