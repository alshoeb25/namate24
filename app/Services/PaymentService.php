<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Create a payment order
     */
    public function createPaymentOrder(User $user, array $data): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_id' => $data['order_id'] ?? 'ORDER_' . time(),
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'INR',
            'type' => $data['type'] ?? 'coin', // 'coin' or 'subscription'
            'payment_method' => $data['payment_method'] ?? 'razorpay',
            'status' => PaymentStatus::INITIATED->value,
            'package_id' => $data['package_id'] ?? null,
            'coins' => $data['coins'] ?? null,
            'bonus_coins' => $data['bonus_coins'] ?? 0,
            'receipt' => $data['receipt'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Create a payment transaction record
     */
    public function createPaymentTransaction(
        User $user,
        Order $order,
        string $type,
        array $data = []
    ): PaymentTransaction {
        return PaymentTransaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'razorpay_order_id' => $data['razorpay_order_id'] ?? null,
            'razorpay_payment_id' => $data['razorpay_payment_id'] ?? null,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'type' => $type, // 'coin_purchase', 'subscription_purchase', etc.
            'status' => PaymentStatus::INITIATED->value,
            'coins' => $order->coins,
            'bonus_coins' => $order->bonus_coins,
            'description' => $data['description'] ?? null,
            'meta' => $data['metadata'] ?? $order->metadata,
        ]);
    }

    /**
     * Update order payment status
     */
    public function updateOrderPaymentStatus(
        Order $order,
        PaymentStatus $status,
        array $data = []
    ): Order {
        return DB::transaction(function () use ($order, $status, $data) {
            // Map PaymentStatus to Order status enum values
            // Orders table only accepts: 'pending', 'completed', 'failed', 'cancelled'
            $orderStatus = match ($status) {
                PaymentStatus::SUCCESS => 'completed',
                PaymentStatus::FAILED => 'failed',
                PaymentStatus::CANCELLED => 'cancelled',
                default => 'pending', // INITIATED, PENDING, PROCESSING
            };

            $order->update([
                'status' => $orderStatus,
                'razorpay_order_id' => $data['razorpay_order_id'] ?? $order->razorpay_order_id,
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? $order->razorpay_payment_id,
                'razorpay_signature' => $data['razorpay_signature'] ?? $order->razorpay_signature,
                'razorpay_response' => $data['razorpay_response'] ?? $order->razorpay_response,
                'paid_at' => $status->isSuccess() ? now() : $order->paid_at,
            ]);

            Log::info("Order #{$order->id} payment status updated to {$orderStatus}");

            return $order;
        });
    }

    /**
     * Update transaction payment status
     */
    public function updateTransactionPaymentStatus(
        PaymentTransaction $transaction,
        PaymentStatus $status,
        array $data = []
    ): PaymentTransaction {
        return DB::transaction(function () use ($transaction, $status, $data) {
            $transaction->update([
                'status' => $status->value,
                'razorpay_order_id' => $data['razorpay_order_id'] ?? $transaction->razorpay_order_id,
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? $transaction->razorpay_payment_id,
                'meta' => array_merge($transaction->meta ?? [], $data['metadata'] ?? []),
            ]);

            Log::info("Transaction #{$transaction->id} payment status updated to {$status->value}");

            return $transaction;
        });
    }

    /**
     * Verify payment with Razorpay
     */
    public function verifyRazorpayPayment(
        string $orderId,
        string $paymentId,
        string $signature
    ): bool {
        $expectedSignature = hash_hmac(
            'sha256',
            "{$orderId}|{$paymentId}",
            config('services.razorpay.secret')
        );

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Log payment event
     */
    public function logPaymentEvent(
        Order $order,
        string $event,
        array $details = []
    ): void {
        Log::info("Payment Event: {$event}", [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->amount,
            'status' => $order->status,
            'details' => $details,
        ]);
    }

    /**
     * Handle payment success
     */
    public function handlePaymentSuccess(
        Order $order,
        string $paymentId,
        string $signature,
        array $razorpayResponse = []
    ): void {
        DB::transaction(function () use ($order, $paymentId, $signature, $razorpayResponse) {
            // Update order
            $this->updateOrderPaymentStatus($order, PaymentStatus::SUCCESS, [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
                'razorpay_response' => $razorpayResponse,
            ]);

            // Update related transaction
            if ($transaction = $order->paymentTransactions()->first()) {
                $this->updateTransactionPaymentStatus($transaction, PaymentStatus::SUCCESS, [
                    'razorpay_payment_id' => $paymentId,
                    'metadata' => $razorpayResponse,
                ]);
            }

            $this->logPaymentEvent($order, 'PAYMENT_SUCCESS', [
                'payment_id' => $paymentId,
            ]);
        });
    }

    /**
     * Handle payment failure
     */
    public function handlePaymentFailure(
        Order $order,
        string $reason = 'Unknown error',
        array $details = []
    ): void {
        DB::transaction(function () use ($order, $reason, $details) {
            // Update order
            $this->updateOrderPaymentStatus($order, PaymentStatus::FAILED, [
                'razorpay_response' => array_merge(['error' => $reason], $details),
            ]);

            // Update related transaction
            if ($transaction = $order->paymentTransactions()->first()) {
                $this->updateTransactionPaymentStatus($transaction, PaymentStatus::FAILED, [
                    'metadata' => ['error' => $reason, ...$details],
                ]);
            }

            $this->logPaymentEvent($order, 'PAYMENT_FAILED', [
                'reason' => $reason,
                'details' => $details,
            ]);
        });
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Order $order): PaymentStatus
    {
        return PaymentStatus::from($order->status);
    }

    /**
     * Check if payment is completed
     */
    public function isPaymentCompleted(Order $order): bool
    {
        return $this->getPaymentStatus($order)->isSuccess();
    }
}
