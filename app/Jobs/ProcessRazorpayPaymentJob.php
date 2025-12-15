<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Models\CreditPurchase;
use App\Services\CreditService;
use Carbon\Carbon;

class ProcessRazorpayPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(CreditService $creditService)
    {
        $event = $this->payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $payment = $this->payload['payload']['payment']['entity'] ?? null;
            if (!$payment) {
                Log::error('Razorpay: missing payment entity');
                return;
            }

            $orderId = $payment['order_id'] ?? null;
            $amount = ($payment['amount'] ?? 0) / 100.0;

            $purchase = CreditPurchase::where('payment_gateway_order_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if (! $purchase) {
                Log::warning('Razorpay: purchase not found for order', ['order_id' => $orderId]);
                return;
            }

            $purchase->update([
                'status' => 'paid',
                'purchased_at' => Carbon::now(),
            ]);

            // credit wallet
            $creditService->creditWallet($purchase);

            Log::info('Razorpay: credited wallet', ['purchase_id' => $purchase->id]);
        }

        // TODO: handle refunds and other events
    }
}