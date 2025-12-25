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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Razorpay order details
            $table->string('razorpay_order_id')->unique()->nullable();
            $table->decimal('amount', 10, 2); // Amount in INR
            $table->string('currency')->default('INR');
            
            // Package details
            $table->foreignId('package_id')->constrained('coin_packages')->cascadeOnDelete();
            $table->integer('coins');
            $table->integer('bonus_coins')->default(0);
            
            // Order status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            
            // Payment details (after payment)
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('razorpay_signature')->nullable();
            
            // Receipt and tracking
            $table->string('receipt')->unique();
            $table->json('razorpay_response')->nullable(); // Complete API response
            $table->json('meta')->nullable(); // Additional metadata
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
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
        Schema::dropIfExists('orders');
    }
};
