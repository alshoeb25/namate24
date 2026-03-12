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
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_order_id')->nullable()->constrained('subscription_orders')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            
            // Link to regular order if applicable
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            
            // Payment details
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->enum('status', ['pending', 'initiated', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('type', ['subscription_purchase', 'subscription_renewal', 'subscription_upgrade', 'subscription_downgrade', 'subscription_refund'])->default('subscription_purchase');
            
            // Amount
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            
            // Description and metadata
            $table->text('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('meta')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('subscription_order_id');
            $table->index('subscription_plan_id');
            $table->index('status');
            $table->index('type');
            $table->index('razorpay_payment_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};
