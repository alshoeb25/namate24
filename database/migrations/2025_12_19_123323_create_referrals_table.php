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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete(); // Who referred
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete(); // Who got referred
            $table->integer('referrer_coins')->default(0); // Coins given to referrer
            $table->integer('referred_coins')->default(0); // Coins given to referred user
            $table->boolean('reward_given')->default(false);
            $table->timestamp('reward_given_at')->nullable();
            $table->timestamps();
            
            $table->index('referrer_id');
            $table->index('referred_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
