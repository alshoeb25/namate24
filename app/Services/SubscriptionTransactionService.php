<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionTransaction;
use App\Models\SubscriptionPlan;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SubscriptionTransactionService
{
    /**
     * Create a new subscription transaction
     */
    public function createTransaction(
        User $user,
        SubscriptionPlan $plan,
        string $type = 'subscription_purchase',
        string $status = 'INITIATED',
        array $data = []
    ): SubscriptionTransaction {
        try {
            return DB::transaction(function () use ($user, $plan, $type, $status, $data) {
                // Use provided amount (which may include GST) or calculate it
                $amount = $data['amount'] ?? (float)$plan->price;
                
                $transaction = SubscriptionTransaction::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'subscription_order_id' => $data['subscription_order_id'] ?? null,
                    'order_id' => $data['order_id'] ?? null,
                    'type' => $type,
                    'status' => $status,
                    'amount' => $amount,
                    'currency' => $data['currency'] ?? $plan->currency ?? 'INR',
                    'payment_method' => $data['payment_method'] ?? null,
                    'razorpay_order_id' => $data['razorpay_order_id'] ?? null,
                    'razorpay_payment_id' => $data['razorpay_payment_id'] ?? null,
                    'description' => $data['description'] ?? "Purchase of {$plan->name} subscription",
                    'meta' => array_merge(
                        $data['meta'] ?? [],
                        [
                            'base_amount' => $data['base_amount'] ?? (float)$plan->price,
                            'gst_amount' => $data['gst_amount'] ?? 0,
                            'gst_rate' => $data['gst_rate'] ?? 0,
                            'total_amount' => $amount,
                        ]
                    ),
                ]);

                Log::info("Subscription transaction created", [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'type' => $type,
                    'status' => $status,
                ]);

                return $transaction;
            });
        } catch (Exception $e) {
            Log::error("Failed to create subscription transaction: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update transaction status
     */
    public function updateTransactionStatus(
        SubscriptionTransaction $transaction,
        string $newStatus,
        array $additionalData = []
    ): SubscriptionTransaction {
        try {
            return DB::transaction(function () use ($transaction, $newStatus, $additionalData) {
                $logs = $transaction->meta ?? [];
                $logs['status_history'] = $logs['status_history'] ?? [];
                $logs['status_history'][] = [
                    'from' => $transaction->status,
                    'to' => $newStatus,
                    'timestamp' => now(),
                    'reason' => $additionalData['reason'] ?? null,
                ];

                $transaction->update([
                    'status' => $newStatus,
                    'meta' => array_merge($logs, $additionalData['meta'] ?? []),
                    'razorpay_payment_id' => $additionalData['razorpay_payment_id'] ?? $transaction->razorpay_payment_id,
                ]);

                Log::info("Subscription transaction status updated", [
                    'transaction_id' => $transaction->id,
                    'old_status' => $transaction->getOriginal('status'),
                    'new_status' => $newStatus,
                ]);

                return $transaction;
            });
        } catch (Exception $e) {
            Log::error("Failed to update subscription transaction status: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark transaction as successful
     */
    public function markAsSuccess(
        SubscriptionTransaction $transaction,
        string $razorpayPaymentId,
        array $additionalData = []
    ): SubscriptionTransaction {
        return $this->updateTransactionStatus($transaction, 'SUCCESS', array_merge([
            'razorpay_payment_id' => $razorpayPaymentId,
            'reason' => 'Payment successful',
        ], $additionalData));
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(
        SubscriptionTransaction $transaction,
        string $reason = 'Payment failed',
        array $additionalData = []
    ): SubscriptionTransaction {
        return $this->updateTransactionStatus($transaction, 'FAILED', array_merge([
            'reason' => $reason,
        ], $additionalData));
    }

    /**
     * Mark transaction as pending
     */
    public function markAsPending(
        SubscriptionTransaction $transaction,
        string $reason = 'Payment pending',
        array $additionalData = []
    ): SubscriptionTransaction {
        return $this->updateTransactionStatus($transaction, 'PENDING', array_merge([
            'reason' => $reason,
        ], $additionalData));
    }

    /**
     * Get transaction by Razorpay payment ID
     */
    public function getTransactionByRazorpayPaymentId(string $razorpayPaymentId): ?SubscriptionTransaction
    {
        return SubscriptionTransaction::where('razorpay_payment_id', $razorpayPaymentId)->first();
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactionHistory(
        User $user,
        string $type = 'all',
        string $status = 'all',
        int $limit = 50
    ) {
        $query = SubscriptionTransaction::where('user_id', $user->id);

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($status !== 'all') {
            if (in_array($status, ['success', 'completed'])) {
                $query->whereIn('status', ['SUCCESS', 'completed', 'paid']);
            } elseif ($status === 'failed') {
                $query->where('status', 'FAILED');
            } elseif ($status === 'pending') {
                $query->whereIn('status', ['INITIATED', 'PENDING', 'processing']);
            } else {
                $query->where('status', $status);
            }
        }

        return $query->with(['subscriptionPlan', 'subscriptionOrder'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transaction statistics for user
     */
    public function getTransactionStats(User $user, int $days = 30)
    {
        $fromDate = now()->subDays($days);

        $stats = DB::table('subscription_transactions')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $fromDate)
            ->selectRaw('
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN 1 END) as successful_transactions,
                COUNT(CASE WHEN status = "FAILED" THEN 1 END) as failed_transactions,
                COUNT(CASE WHEN status IN ("INITIATED", "PENDING", "processing") THEN 1 END) as pending_transactions,
                SUM(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE 0 END) as total_amount_spent,
                AVG(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as avg_transaction_amount,
                MAX(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as max_transaction_amount,
                MIN(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as min_transaction_amount
            ')
            ->first();

        return [
            'total_transactions' => $stats->total_transactions ?? 0,
            'successful_transactions' => $stats->successful_transactions ?? 0,
            'failed_transactions' => $stats->failed_transactions ?? 0,
            'pending_transactions' => $stats->pending_transactions ?? 0,
            'total_amount_spent' => (float)($stats->total_amount_spent ?? 0),
            'avg_transaction_amount' => (float)($stats->avg_transaction_amount ?? 0),
            'max_transaction_amount' => (float)($stats->max_transaction_amount ?? 0),
            'min_transaction_amount' => (float)($stats->min_transaction_amount ?? 0),
            'period_days' => $days,
        ];
    }

    /**
     * Get transaction analytics grouped by time period
     */
    public function getTransactionAnalytics(User $user, int $months = 6)
    {
        $startDate = now()->subMonths($months)->startOfMonth();

        // By type
        $byType = DB::table('subscription_transactions')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->groupBy('type')
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->get();

        // By month
        $byMonth = DB::table('subscription_transactions')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as total')
            ->orderBy('month', 'desc')
            ->get();

        // By plan
        $byPlan = DB::table('subscription_transactions')
            ->join('subscription_plans', 'subscription_transactions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscription_transactions.user_id', $user->id)
            ->where('subscription_transactions.created_at', '>=', $startDate)
            ->whereIn('subscription_transactions.status', ['SUCCESS', 'completed', 'paid'])
            ->groupBy('subscription_plans.name')
            ->selectRaw('subscription_plans.name, COUNT(*) as count, SUM(subscription_transactions.amount) as total')
            ->get();

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => now()->format('Y-m-d'),
                'months' => $months,
            ],
            'by_type' => $byType->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'total' => (float)$item->total,
                    'average' => (float)($item->total / $item->count),
                ];
            })->toArray(),
            'by_month' => $byMonth->map(function ($item) {
                return [
                    'month' => $item->month,
                    'count' => $item->count,
                    'total' => (float)$item->total,
                    'average' => (float)($item->total / $item->count),
                ];
            })->toArray(),
            'by_plan' => $byPlan->map(function ($item) {
                return [
                    'plan' => $item->name,
                    'count' => $item->count,
                    'total' => (float)$item->total,
                    'average' => (float)($item->total / $item->count),
                ];
            })->toArray(),
        ];
    }

    /**
     * Get recent successful transactions
     */
    public function getRecentSuccessfulTransactions(User $user, int $limit = 5)
    {
        return SubscriptionTransaction::where('user_id', $user->id)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->with(['subscriptionPlan', 'subscriptionOrder'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending transactions
     */
    public function getPendingTransactions(User $user)
    {
        return SubscriptionTransaction::where('user_id', $user->id)
            ->whereIn('status', ['INITIATED', 'PENDING', 'processing'])
            ->with(['subscriptionPlan', 'subscriptionOrder'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calculate refund for transaction
     */
    public function createRefundTransaction(
        User $user,
        SubscriptionTransaction $originalTransaction,
        string $reason = 'Refund'
    ): SubscriptionTransaction {
        try {
            return $this->createTransaction(
                $user,
                $originalTransaction->subscriptionPlan,
                'subscription_refund',
                'SUCCESS',
                [
                    'subscription_order_id' => $originalTransaction->subscription_order_id,
                    'order_id' => $originalTransaction->order_id,
                    'description' => $reason,
                    'meta' => [
                        'refund_for_transaction_id' => $originalTransaction->id,
                        'refund_of_amount' => $originalTransaction->amount,
                    ],
                ]
            );
        } catch (Exception $e) {
            Log::error("Failed to create refund transaction: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retry a failed transaction
     */
    public function retryFailedTransaction(SubscriptionTransaction $transaction): SubscriptionTransaction
    {
        if (!$transaction->isFailed()) {
            throw new Exception("Only failed transactions can be retried");
        }

        return $this->createTransaction(
            $transaction->user,
            $transaction->subscriptionPlan,
            $transaction->type,
            'INITIATED',
            [
                'subscription_order_id' => $transaction->subscription_order_id,
                'order_id' => $transaction->order_id,
                'description' => "Retry: {$transaction->description}",
                'meta' => array_merge($transaction->meta ?? [], [
                    'retry_of_transaction_id' => $transaction->id,
                ]),
            ]
        );
    }
}
