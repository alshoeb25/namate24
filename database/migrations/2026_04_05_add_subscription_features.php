<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Add coins included with this subscription
            $table->integer('coins_included')->default(0)->after('views_allowed');
            
            // Priority support flag
            $table->boolean('has_priority_support')->default(false)->after('coins_included');
            
            // eBooks & Content flag
            $table->boolean('has_ebook_content')->default(false)->after('has_priority_support');
            
            // Access delay in hours (for BASIC: 1-2 hours, PRO: 0 hours)
            $table->integer('access_delay_hours')->default(0)->after('has_ebook_content');
            
            // Cost per view (in coins or rupees)
            $table->integer('cost_per_view')->nullable()->after('access_delay_hours');
            
            // Whether coins carry forward when subscription lapses
            $table->boolean('coins_carry_forward')->default(false)->after('cost_per_view');
            
            // Grace period in hours after expiry for viewing requirements (for PRO subscriptions)
            $table->integer('lapse_grace_period_hours')->default(2)->after('coins_carry_forward');
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Track when grace period expires (after subscription lapses)
            $table->timestamp('grace_period_expires_at')->nullable()->after('expires_at');
            
            // Track if coins were carried forward
            $table->integer('coins_carried_forward')->default(0)->after('grace_period_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'coins_included',
                'has_priority_support',
                'has_ebook_content',
                'access_delay_hours',
                'cost_per_view',
                'coins_carry_forward',
                'lapse_grace_period_hours',
            ]);
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'grace_period_expires_at',
                'coins_carried_forward',
            ]);
        });
    }
};
