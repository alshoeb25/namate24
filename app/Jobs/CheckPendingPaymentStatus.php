<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\CoinTransaction;
use App\Models\PaymentTransaction;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\PaymentSuccessNotification;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api as RazorpayApi;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\FcmService;

class CheckPendingPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $transactionId;
    public $force;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $transactionId, $force = false)
    {
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Checking pending payment status', [
            'order_id' => $this->orderId,
            'transaction_id' => $this->transactionId,
        ]);

        try {
            // Find order and payment transaction
            $order = Order::find($this->orderId);
            $paymentTx = PaymentTransaction::find($this->transactionId);

            if (!$order || !$paymentTx) {
                Log::warning('Order or transaction not found', [
                    'order_id' => $this->orderId,
                    'transaction_id' => $this->transactionId,
                ]);
                return;
            }

            // Check if already processed
            if (!$this->force && !in_array($order->status, ['pending', 'initiated', 'PENDING', 'INITIATED'])) {
                Log::info('Order already processed', [
                    'order_id' => $this->orderId,
                    'status' => $order->status,
                ]);
                return;
            }

            // Call Razorpay API to check order status
            $rzp = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $rzp->order->fetch($order->razorpay_order_id);
            $orderStatus = $razorpayOrder->status;
            
            Log::info('Razorpay order status fetched', [
                'order_id' => $this->orderId,
                'razorpay_order_id' => $order->razorpay_order_id,
                'status' => $orderStatus,
            ]);

            DB::beginTransaction();

            if ($orderStatus === 'paid') {
                // Payment succeeded - process success flow
                $this->processSuccessfulPayment($order, $paymentTx, $razorpayOrder);
            } elseif (in_array($orderStatus, ['attempted', 'created'])) {
                // Still pending (user may have cancelled/exited) - log and do nothing
                // Don't mark as failed unless there's evidence of actual payment failure
                Log::info('Payment still pending - user may have cancelled or exited', [
                    'order_id' => $this->orderId,
                    'razorpay_status' => $orderStatus,
                ]);
                
                // Update meta to indicate we checked
                $meta = $paymentTx->meta ?? [];
                $paymentTx->update([
                    'meta' => array_merge($meta, [
                        'status_checked_at' => now()->toDateTimeString(),
                        'razorpay_status' => $orderStatus,
                    ]),
                ]);
            } else {
                // Only mark as failed if status indicates actual failure (not cancellation/exit)
                // Statuses like 'closed', 'expired' indicate actual failure, not just cancellation
                if (in_array($orderStatus, ['expired', 'closed'])) {
                    $this->processFailedPayment($order, $paymentTx, $orderStatus, 'Payment window expired or closed');
                } else {
                    // Unknown status - log but don't mark as failed to avoid penalizing users
                    Log::info('Unknown Razorpay order status - treating as pending', [
                        'order_id' => $this->orderId,
                        'razorpay_status' => $orderStatus,
                    ]);
                    
                    $meta = $paymentTx->meta ?? [];
                    $paymentTx->update([
                        'meta' => array_merge($meta, [
                            'status_checked_at' => now()->toDateTimeString(),
                            'razorpay_status' => $orderStatus,
                        ]),
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking pending payment status', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment($order, $paymentTx, $razorpayOrder)
    {
        $user = User::find($order->user_id);
        
        if (!$user) {
            Log::error('User not found for successful payment', ['user_id' => $order->user_id]);
            return;
        }

        // Get payment details
        $payments = $razorpayOrder->payments();
        $payment = $payments->items[0] ?? null;

        $paymentId = $payment->id ?? null;

        // Update order
        $order->update([
            'status' => 'completed',
            'razorpay_payment_id' => $paymentId,
            'paid_at' => now(),
        ]);

        // Calculate coins
        $meta = $paymentTx->meta ?? [];
        $coins = $paymentTx->coins ?? ($meta['coins'] ?? ($order->coins ?? 0));
        $bonusCoins = $paymentTx->bonus_coins ?? ($meta['bonus_coins'] ?? ($order->bonus_coins ?? 0));
        $totalCoins = $coins + $bonusCoins;

        // Mark payment transaction SUCCESS (do not store coin balance here)
        $paymentTx->update([
            'razorpay_payment_id' => $paymentId,
            'status' => 'SUCCESS',
            'meta' => array_merge($meta, [
                'status' => 'completed',
                'completed_at' => now()->toDateTimeString(),
                'razorpay_payment_id' => $paymentId,
                'completed_via_job' => true,
            ]),
        ]);

        // Idempotent coin credit + transaction create under lock
        $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
        $coinTx = CoinTransaction::firstOrCreate(
            [
                'user_id' => $lockedUser->id,
                'order_id' => (string) $order->id,
                'type' => 'purchase',
            ],
            [
                'amount' => $totalCoins,
                'balance_after' => 0,
                'payment_id' => $paymentId,
                'description' => ($meta['package_name'] ?? 'Coin purchase') . ": {$coins} coins" . ($bonusCoins > 0 ? " + {$bonusCoins} bonus" : ''),
                'meta' => array_merge($meta, ['status' => 'completed']),
            ]
        );

        if ($coinTx->wasRecentlyCreated) {
            $lockedUser->increment('coins', $totalCoins);
            $coinTx->balance_after = $lockedUser->coins;
            $coinTx->save();
        }

        $invoice = $this->createInvoice($order, $coinTx, $user);

        // Send notification
        $user->notify(new PaymentSuccessNotification($order, $coinTx, $invoice));

        Log::info('Pending payment processed successfully via job', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'coins_added' => $totalCoins,
            'invoice_id' => $invoice->id,
        ]);
    }

    /**
     * Process failed payment
     */
    private function processFailedPayment($order, $paymentTx, $razorpayStatus, $reason)
    {
        $user = User::find($order->user_id);
        
        if (!$user) {
            Log::error('User not found for failed payment', ['user_id' => $order->user_id]);
            return;
        }

        // Update order
        $order->update([
            'status' => 'failed',
            'meta' => array_merge($order->meta ?? [], [
                'failure_reason' => $reason,
                'razorpay_status' => $razorpayStatus,
                'failed_at' => now()->toDateTimeString(),
            ]),
        ]);

        // Update payment transaction (store status within meta)
        $meta = $paymentTx->meta ?? [];
        $paymentTx->update([
            'meta' => array_merge($meta, [
                'status' => 'failed',
                'failure_reason' => $reason,
                'razorpay_status' => $razorpayStatus,
                'failed_at' => now()->toDateTimeString(),
                'failed_via_job' => true,
            ]),
            'status' => 'FAILED',
        ]);

        // Send notification
        $user->notify(new PaymentFailedNotification($order, $paymentTx, $reason));

        Log::info('Pending payment marked as failed via job', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Create invoice and generate PDF
     */
    private function createInvoice($order, $transaction, $user)
    {
        // Create invoice record
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $order->amount,
            'currency' => $order->currency ?? 'INR',
            'coins' => $order->coins ?? 0,
            'bonus_coins' => $order->bonus_coins ?? 0,
            'razorpay_order_id' => $order->razorpay_order_id,
            'razorpay_payment_id' => $order->razorpay_payment_id,
            'status' => 'paid',
            'issued_at' => now(),
            'meta' => [
                'package_name' => $transaction->meta['package_name'] ?? 'Coin Purchase',
                'transaction_id' => $transaction->id,
            ],
        ]);

        // Generate PDF
        try {
            $pdf = Pdf::loadView('invoices.coin-purchase', [
                'invoice' => $invoice,
                'order' => $order,
                'user' => $user,
                'transaction' => $transaction,
            ]);

            $filename = 'invoices/' . $invoice->invoice_number . '.pdf';
            $path = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $pdf->save($path);

            $invoice->update(['pdf_path' => $filename]);

            Log::info('Invoice PDF generated', [
                'invoice_id' => $invoice->id,
                'pdf_path' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $invoice;
    }
}
