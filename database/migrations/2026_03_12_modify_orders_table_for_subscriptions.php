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
        Schema::table('orders', function (Blueprint $table) {
            // Make coin-specific columns nullable for subscription orders
            $table->foreignId('package_id')->nullable()->change();
            $table->integer('coins')->nullable()->change();
            $table->string('receipt')->nullable()->change();
            
            // Add new columns for subscription support
            $table->string('order_id')->nullable()->after('id');
            $table->string('type')->default('coin')->after('currency'); // 'coin' or 'subscription'
            $table->string('payment_method')->default('razorpay')->after('type');
            $table->json('metadata')->nullable()->after('meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'type', 'payment_method', 'metadata']);
            
            // Revert nullable columns back
            $table->foreignId('package_id')->constrained('coin_packages')->cascadeOnDelete()->change();
            $table->integer('coins')->change();
            $table->string('receipt')->unique()->change();
        });
    }
};
