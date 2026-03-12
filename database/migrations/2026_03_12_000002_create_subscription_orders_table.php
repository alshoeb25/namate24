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
        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            
            // Order identification
            $table->string('order_id')->nullable()->unique(); // Unique order ID
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->enum('status', ['pending', 'initiated', 'paid', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            
            // Receipt and tracking
            $table->string('receipt')->unique();
            $table->json('razorpay_response')->nullable(); // Complete API response
            $table->json('meta')->nullable(); // Additional metadata
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('subscription_plan_id');
            $table->index('status');
            $table->index('razorpay_order_id');
            $table->index('razorpay_payment_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_orders');
    }
};
