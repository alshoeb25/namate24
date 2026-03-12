<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Notifications\SubscriptionFailureNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionFailureNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $order;
    protected $errorMessage;
    protected $errorReason;

    public function __construct(User $user, Order $order, string $errorMessage = 'Payment processing failed', string $errorReason = 'unknown')
    {
        $this->user = $user;
        $this->order = $order;
        $this->errorMessage = $errorMessage;
        $this->errorReason = $errorReason;
    }

    public function handle()
    {
        try {
            $this->user->notify(new SubscriptionFailureNotification(
                $this->user,
                $this->order,
                $this->errorMessage,
                $this->errorReason
            ));

            Log::info('Subscription failure notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'error_message' => $this->errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription failure notification', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
