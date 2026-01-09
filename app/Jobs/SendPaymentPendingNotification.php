<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Notifications\PaymentPendingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentPendingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $order;
    public $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Order $order, PaymentTransaction $transaction)
    {
        $this->user = $user;
        $this->order = $order;
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->user->notify(new PaymentPendingNotification(
                $this->order,
                $this->transaction
            ));

            \Log::info('Payment pending notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'transaction_id' => $this->transaction->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment pending notification', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
