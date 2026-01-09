<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Order;
use App\Models\CoinTransaction;
use App\Models\Invoice;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentSuccessNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $order;
    public $transaction;
    public $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, Order $order, CoinTransaction $transaction, ?Invoice $invoice = null)
    {
        $this->user = $user;
        $this->order = $order;
        $this->transaction = $transaction;
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->user->notify(new PaymentSuccessNotification(
                $this->order,
                $this->transaction,
                $this->invoice
            ));

            \Log::info('Payment success notification sent', [
                'user_id' => $this->user->id,
                'order_id' => $this->order->id,
                'transaction_id' => $this->transaction->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send payment success notification', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
