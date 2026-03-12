<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Add indexes for better performance
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index('razorpay_payment_id', 'idx_razorpay_payment');
            $table->index('type', 'idx_type');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_razorpay_payment');
            $table->dropIndex('idx_type');
            $table->dropIndex('idx_created_at');
        });
    }
};
