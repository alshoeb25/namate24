<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentFailedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $order;
    public $transaction;
    public $reason;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Order $order, PaymentTransaction $transaction, string $reason = null)
    {
        $this->user = $user;
        $this->order = $order;
        $this->transaction = $transaction;
        $this->reason = $reason ?? 'Payment could not be processed';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->user->notify(new PaymentFailedNotification(
                $this->order,
                $this->transaction,
                $this->reason
            ));

            \Log::info('Payment failed notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'transaction_id' => $this->transaction->id,
                'reason' => $this->reason,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment failed notification', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
