<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionTransaction;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\Invoice;
use App\Jobs\CheckPendingPaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Razorpay\Api\Api as RazorpayApi;

class SubscriptionOrderController extends Controller
{
    /**
     * Get all subscription orders for the user with filters and pagination
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $statusFilter = strtolower($request->get('status', 'all'));
        $search = $request->get('search');
        $perPage = (int)($request->per_page ?? 20);
        $page = (int)($request->page ?? 1);

        $query = SubscriptionOrder::where('user_id', $user->id);

        // Filter by status
        if ($statusFilter !== 'all') {
            if (in_array($statusFilter, ['success', 'completed', 'paid'])) {
                $query->whereIn('status', ['paid', 'completed', 'SUCCESS']);
            } elseif (in_array($statusFilter, ['failed', 'failure'])) {
                $query->where('status', 'FAILED');
            } elseif ($statusFilter === 'pending') {
                $query->whereIn('status', ['pending', 'PENDING', 'INITIATED']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('razorpay_order_id', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%")
                    ->orWhere('receipt', 'like', "%{$search}%");
            });
        }

        $paginator = $query->with(['subscriptionPlan', 'transactions'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Map response
        $data = $paginator->getCollection()->map(function ($order) {
            $meta = $order->meta ?? [];
            return [
                'id' => $order->id,
                'subscription_plan_id' => $order->subscription_plan_id,
                'plan_name' => $order->subscriptionPlan->name ?? 'N/A',
                'status' => $order->status,
                'amount' => (float)$order->amount,
                'currency' => $order->currency,
                'razorpay_order_id' => $order->razorpay_order_id,
                'razorpay_payment_id' => $order->razorpay_payment_id,
                'receipt' => $order->receipt,
                'paid_at' => $order->paid_at,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'is_paid' => $order->isPaid(),
                'is_pending' => $order->isPending(),
                'is_failed' => $order->isFailed(),
                'can_retry' => $order->isFailed(),
                'retry_count' => $meta['retry_count'] ?? 0,
                'failure_reason' => $meta['failure_reason'] ?? null,
            ];
        })->values();

        // Calculate stats
        $paidCount = SubscriptionOrder::where('user_id', $user->id)
            ->whereIn('status', ['paid', 'completed', 'SUCCESS'])
            ->count();
        $pendingCount = SubscriptionOrder::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'PENDING', 'INITIATED'])
            ->count();
        $failedCount = SubscriptionOrder::where('user_id', $user->id)
            ->where('status', 'FAILED')
            ->count();

        return response()->json([
            'success' => true,
            'orders' => [
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ],
            'stats' => [
                'total_orders' => $paginator->total(),
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'failed_count' => $failedCount,
                'success_rate' => $paginator->total() > 0 ? round(($paidCount / $paginator->total()) * 100, 2) . '%' : '0%',
                'failure_rate' => $paginator->total() > 0 ? round(($failedCount / $paginator->total()) * 100, 2) . '%' : '0%',
            ],
        ]);
    }

    /**
     * Get a specific subscription order with all transactions
     */
    public function show(Request $request, $orderId)
    {
        $user = $request->user();
        $order = SubscriptionOrder::where('user_id', $user->id)
            ->where('id', $orderId)
            ->with(['subscriptionPlan', 'transactions'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'subscription_plan_id' => $order->subscription_plan_id,
                'plan_name' => $order->subscriptionPlan->name,
                'status' => $order->status,
                'amount' => (float)$order->amount,
                'currency' => $order->currency,
                'razorpay_order_id' => $order->razorpay_order_id,
                'razorpay_payment_id' => $order->razorpay_payment_id,
                'razorpay_signature' => $order->razorpay_signature,
                'receipt' => $order->receipt,
                'paid_at' => $order->paid_at,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'is_paid' => $order->isPaid(),
                'is_pending' => $order->isPending(),
                'is_failed' => $order->isFailed(),
                'plan_details' => [
                    'name' => $order->subscriptionPlan->name,
                    'price' => (float)$order->subscriptionPlan->price,
                    'validity_days' => $order->subscriptionPlan->validity_days,
                    'views_allowed' => $order->subscriptionPlan->views_allowed,
                ],
                'transactions' => $order->transactions->map(function ($tx) {
                    return [
                        'id' => $tx->id,
                        'type' => $tx->type,
                        'status' => $tx->status,
                        'amount' => (float)$tx->amount,
                        'currency' => $tx->currency,
                        'payment_method' => $tx->payment_method,
                        'razorpay_payment_id' => $tx->razorpay_payment_id,
                        'description' => $tx->description,
                        'created_at' => $tx->created_at,
                    ];
                })->toArray(),
            ],
        ]);
    }

    /**
     * Get subscription transaction history with filters
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        $typeFilter = $request->get('type', 'all');
        $statusFilter = strtolower($request->get('status', 'all'));
        $search = $request->get('search');
        $perPage = (int)($request->per_page ?? 20);
        $page = (int)($request->page ?? 1);

        $query = SubscriptionTransaction::where('user_id', $user->id);

        // Type filter
        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        // Status filter
        if ($statusFilter !== 'all') {
            if (in_array($statusFilter, ['success', 'completed'])) {
                $query->whereIn('status', ['SUCCESS', 'completed', 'paid']);
            } elseif (in_array($statusFilter, ['failed', 'failure'])) {
                $query->where('status', 'FAILED');
            } elseif ($statusFilter === 'pending') {
                $query->whereIn('status', ['INITIATED', 'PENDING', 'processing']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$search}%");
            });
        }

        $paginator = $query->with(['subscriptionPlan', 'subscriptionOrder'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Map response
        $data = $paginator->getCollection()->map(function ($tx) {
            return [
                'id' => $tx->id,
                'subscription_order_id' => $tx->subscription_order_id,
                'subscription_plan_id' => $tx->subscription_plan_id,
                'plan_name' => $tx->subscriptionPlan->name ?? 'N/A',
                'type' => $tx->type,
                'status' => $tx->status,
                'amount' => (float)$tx->amount,
                'currency' => $tx->currency,
                'payment_method' => $tx->payment_method,
                'razorpay_payment_id' => $tx->razorpay_payment_id,
                'description' => $tx->description,
                'is_success' => $tx->isSuccess(),
                'is_pending' => $tx->isPending(),
                'is_failed' => $tx->isFailed(),
                'created_at' => $tx->created_at,
                'updated_at' => $tx->updated_at,
            ];
        })->values();

        // Calculate stats
        $successCount = SubscriptionTransaction::where('user_id', $user->id)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->count();
        $pendingCount = SubscriptionTransaction::where('user_id', $user->id)
            ->whereIn('status', ['INITIATED', 'PENDING', 'processing'])
            ->count();
        $failedCount = SubscriptionTransaction::where('user_id', $user->id)
            ->where('status', 'FAILED')
            ->count();
        $totalSpent = SubscriptionTransaction::where('user_id', $user->id)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->sum('amount');

        return response()->json([
            'success' => true,
            'transactions' => [
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ],
            'stats' => [
                'total_transactions' => $paginator->total(),
                'successful_transactions' => $successCount,
                'pending_transactions' => $pendingCount,
                'failed_transactions' => $failedCount,
                'total_amount_spent' => (float)$totalSpent,
                'success_rate' => $paginator->total() > 0 ? round(($successCount / $paginator->total()) * 100, 2) . '%' : '0%',
                'failure_rate' => $paginator->total() > 0 ? round(($failedCount / $paginator->total()) * 100, 2) . '%' : '0%',
            ],
        ]);
    }

    /**
     * Get subscription transaction breakdown by type and month
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        $months = (int)($request->get('months', 6));

        $startDate = now()->subMonths($months)->startOfMonth();

        // Transactions by type
        $byType = SubscriptionTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->groupBy('type')
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total_amount')
            ->get();

        // Transactions by month
        $byMonth = SubscriptionTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', ['SUCCESS', 'completed', 'paid'])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(amount) as total_amount')
            ->orderBy('month', 'desc')
            ->get();

        // Total stats
        $totalStats = SubscriptionTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN 1 ELSE 0 END) as successful_transactions,
                SUM(CASE WHEN status IN ("FAILED") THEN 1 ELSE 0 END) as failed_transactions,
                SUM(CASE WHEN status IN ("INITIATED", "PENDING", "processing") THEN 1 ELSE 0 END) as pending_transactions,
                AVG(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as avg_transaction_amount,
                MAX(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as max_transaction_amount,
                MIN(CASE WHEN status IN ("SUCCESS", "completed", "paid") THEN amount ELSE NULL END) as min_transaction_amount
            ')
            ->first();

        return response()->json([
            'success' => true,
            'analytics' => [
                'period_months' => $months,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'by_type' => $byType->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'count' => (int)$item->count,
                        'total_amount' => (float)$item->total_amount,
                        'average_amount' => (float)($item->total_amount / $item->count),
                    ];
                })->toArray(),
                'by_month' => $byMonth->map(function ($item) {
                    return [
                        'month' => $item->month,
                        'count' => (int)$item->count,
                        'total_amount' => (float)$item->total_amount,
                        'average_amount' => (float)($item->total_amount / $item->count),
                    ];
                })->toArray(),
                'total_stats' => [
                    'total_transactions' => (int)($totalStats->total_transactions ?? 0),
                    'successful_transactions' => (int)($totalStats->successful_transactions ?? 0),
                    'failed_transactions' => (int)($totalStats->failed_transactions ?? 0),
                    'pending_transactions' => (int)($totalStats->pending_transactions ?? 0),
                    'avg_transaction_amount' => (float)($totalStats->avg_transaction_amount ?? 0),
                    'max_transaction_amount' => (float)($totalStats->max_transaction_amount ?? 0),
                    'min_transaction_amount' => (float)($totalStats->min_transaction_amount ?? 0),
                ],
            ],
        ]);
    }

    /**
     * Retry a failed subscription order
     */
    public function retryOrder(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            $order = SubscriptionOrder::where('user_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            if (!$order->isFailed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only failed orders can be retried',
                    'current_status' => $order->status,
                ], 400);
            }

            // Create a new Razorpay order for retry
            $razorpayClient = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $razorpayClient->order->create([
                'amount' => (int)($order->amount * 100), // Convert to paise
                'currency' => $order->currency,
                'receipt' => 'retry_' . $order->id . '_' . Str::random(16),
                'notes' => [
                    'subscription_order_id' => $orderId,
                    'subscription_plan_id' => $order->subscription_plan_id,
                    'user_id' => $user->id,
                    'retry_attempt' => ($order->meta['retry_count'] ?? 0) + 1,
                ],
            ]);

            // Track retry attempts
            $retryCount = ($order->meta['retry_count'] ?? 0) + 1;
            $retryHistory = $order->meta['retry_history'] ?? [];
            $retryHistory[] = [
                'attempt' => $retryCount,
                'retried_at' => now(),
                'razorpay_order_id' => $razorpayOrder['id'],
            ];

            // Update order with new Razorpay details
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id'],
                'status' => 'INITIATED',
                'razorpay_payment_id' => null,
                'razorpay_signature' => null,
                'meta' => array_merge($order->meta ?? [], [
                    'retry_count' => $retryCount,
                    'retry_history' => $retryHistory,
                ]),
            ]);

            // Reset related transactions to INITIATED
            $order->transactions()
                ->where('status', 'FAILED')
                ->update([
                    'status' => 'INITIATED',
                    'razorpay_order_id' => $razorpayOrder['id'],
                ]);

            \Log::info('Subscription order retry initiated', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'retry_attempt' => $retryCount,
                'new_razorpay_order_id' => $razorpayOrder['id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order retry initiated (Attempt #' . $retryCount . ')',
                'data' => [
                    'order_id' => $order->id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'amount' => (int)($order->amount * 100),
                    'currency' => $order->currency,
                    'razorpay_key' => config('services.razorpay.key'),
                    'retry_attempt' => $retryCount,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error retrying subscription order: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'user_id' => $request->user()->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent subscription orders for dashboard
     */
    public function recentOrders(Request $request)
    {
        $user = $request->user();
        $limit = (int)($request->get('limit', 5));

        $orders = SubscriptionOrder::where('user_id', $user->id)
            ->with('subscriptionPlan')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'plan_name' => $order->subscriptionPlan->name,
                    'amount' => (float)$order->amount,
                    'currency' => $order->currency,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'is_paid' => $order->isPaid(),
                ];
            })->toArray(),
        ]);
    }
}
