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
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['purchase', 'referral_bonus', 'referral_reward', 'booking', 'refund', 'admin_credit', 'admin_debit']);
            $table->integer('amount'); // positive for credit, negative for debit
            $table->integer('balance_after');
            $table->text('description')->nullable();
            $table->string('payment_id')->nullable(); // Razorpay payment ID
            $table->string('order_id')->nullable(); // Razorpay order ID
            $table->json('meta')->nullable(); // Additional metadata
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
