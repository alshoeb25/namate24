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
        Schema::table('referral_invites', function (Blueprint $table) {
            $table->foreignId('referral_code_id')
                ->nullable()
                ->constrained('referral_codes')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_invites', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ReferralCode::class);
        });
    }
};
