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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->dateTime('activated_at'); // When subscription becomes active
            $table->dateTime('expires_at'); // When subscription expires
            $table->integer('views_used')->default(0); // Tracks views used
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
            $table->unique(['user_id', 'id']); // User can have one active subscription
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
