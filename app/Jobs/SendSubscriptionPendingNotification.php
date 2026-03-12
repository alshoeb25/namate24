<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Notifications\SubscriptionPendingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionPendingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $order;
    protected $transaction;

    public function __construct(User $user, Order $order, PaymentTransaction $transaction = null)
    {
        $this->user = $user;
        $this->order = $order;
        $this->transaction = $transaction;
    }

    public function handle()
    {
        try {
            $this->user->notify(new SubscriptionPendingNotification($this->user, $this->order));

            Log::info('Subscription pending notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription pending notification', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
