<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(0); // integer credits
            $table->timestamps();
        });

        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('credits');
            $table->decimal('price', 10, 2);
            $table->integer('validity_days')->nullable(); // null = no expiry
            $table->timestamps();
        });

        Schema::create('credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->integer('credits_total');
            $table->integer('credits_consumed')->default(0);
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_id')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('pending'); // pending, paid, cancelled
            $table->timestamps();
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('credit_purchase_id')->nullable()->constrained('credit_purchases')->nullOnDelete();
            $table->integer('amount'); // positive/negative
            $table->string('type'); // purchase, spend, expire, refund
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credit_purchases');
        Schema::dropIfExists('credit_packages');
        Schema::dropIfExists('wallets');
    }
};