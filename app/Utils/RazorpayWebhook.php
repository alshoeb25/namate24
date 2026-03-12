<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class RazorpayWebhook
{
    /**
     * Verify Razorpay webhook signature
     */
    public static function verifySignature(array $data, string $signature): bool
    {
        try {
            $orderId = $data['razorpay_order_id'] ?? null;
            $paymentId = $data['razorpay_payment_id'] ?? null;

            if (!$orderId || !$paymentId) {
                return false;
            }

            $message = "{$orderId}|{$paymentId}";
            $secret = config('services.razorpay.secret');

            $expectedSignature = hash_hmac('sha256', $message, $secret);

            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Razorpay signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify webhook request body (for webhooks from Razorpay)
     */
    public static function verifyWebhookBody(string $body, string $signature): bool
    {
        try {
            $secret = config('services.razorpay.secret');
            $expectedSignature = hash_hmac('sha256', $body, $secret);

            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Razorpay webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment details from Razorpay API
     */
    public static function getPaymentDetails(string $paymentId): ?array
    {
        try {
            $client = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $payment = $client->payment->fetch($paymentId);

            return [
                'id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'method' => $payment->method,
                'contact' => $payment->contact,
                'email' => $payment->email,
                'description' => $payment->description,
                'error_code' => $payment->error_code ?? null,
                'error_description' => $payment->error_description ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch payment details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get order details from Razorpay API
     */
    public static function getOrderDetails(string $orderId): ?array
    {
        try {
            $client = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $client->order->fetch($orderId);

            return [
                'id' => $order->id,
                'amount' => $order->amount,
                'amount_paid' => $order->amount_paid,
                'amount_due' => $order->amount_due,
                'currency' => $order->currency,
                'receipt' => $order->receipt,
                'status' => $order->status,
                'attempts' => $order->attempts,
                'notes' => $order->notes,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch order details: ' . $e->getMessage());
            return null;
        }
    }
}
