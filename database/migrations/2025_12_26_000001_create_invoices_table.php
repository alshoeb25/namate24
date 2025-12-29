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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->integer('coins')->default(0);
            $table->integer('bonus_coins')->default(0);
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
            $table->json('meta')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('order_id');
            $table->index('invoice_number');
            $table->index('razorpay_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
