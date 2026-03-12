<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends Controller
{
    /**
     * List all subscription plans
     * GET /api/admin/subscription-plans
     */
    public function index()
    {
        try {
            $plans = SubscriptionPlan::orderBy('display_order', 'asc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $plans->items(),
                'pagination' => [
                    'current_page' => $plans->currentPage(),
                    'per_page' => $plans->perPage(),
                    'total' => $plans->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription plans: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plans',
            ], 500);
        }
    }

    /**
     * Create a new subscription plan
     * POST /api/admin/subscription-plans
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'validity_days' => 'required|integer|min:1',
                'views_allowed' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'display_order' => 'integer|min:0',
            ]);

            $plan = SubscriptionPlan::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan created successfully',
                'data' => $plan,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating subscription plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific subscription plan
     * GET /api/admin/subscription-plans/{id}
     */
    public function show(SubscriptionPlan $subscriptionPlan)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $subscriptionPlan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plan',
            ], 500);
        }
    }

    /**
     * Update a subscription plan
     * PUT /api/admin/subscription-plans/{id}
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        try {
            $request->validate([
                'name' => 'string|max:255',
                'price' => 'numeric|min:0',
                'currency' => 'string|size:3',
                'validity_days' => 'integer|min:1',
                'views_allowed' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'display_order' => 'integer|min:0',
            ]);

            $subscriptionPlan->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan updated successfully',
                'data' => $subscriptionPlan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating subscription plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a subscription plan
     * DELETE /api/admin/subscription-plans/{id}
     */
    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        try {
            // Check if plan has active subscriptions
            $activeCount = $subscriptionPlan->userSubscriptions()
                ->where('status', 'active')
                ->count();

            if ($activeCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete plan with {$activeCount} active subscriptions. Please mark as inactive instead.",
                ], 400);
            }

            $subscriptionPlan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting subscription plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscription plan',
            ], 500);
        }
    }

    /**
     * Toggle subscription plan active status
     * POST /api/admin/subscription-plans/{id}/toggle-active
     */
    public function toggleActive(SubscriptionPlan $subscriptionPlan)
    {
        try {
            $subscriptionPlan->update([
                'is_active' => !$subscriptionPlan->is_active,
            ]);

            $status = $subscriptionPlan->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "Subscription plan {$status} successfully",
                'data' => $subscriptionPlan,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling subscription plan status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle subscription plan status',
            ], 500);
        }
    }

    /**
     * Get subscription statistics
     * GET /api/admin/subscription-plans/stats
     */
    public function stats()
    {
        try {
            $stats = SubscriptionPlan::with([
                'userSubscriptions' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->get()
            ->map(function ($plan) {
                return [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'price' => $plan->price,
                    'active_subscriptions' => $plan->userSubscriptions->count(),
                    'total_revenue' => $plan->userSubscriptions->sum(function () {
                        return $this->price; // Simplified - would need Order relationship
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching subscription stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription statistics',
            ], 500);
        }
    }
}
