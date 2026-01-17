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
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->enum('referral_type', ['welcome', 'promotion', 'fest'])->default('promotion')->after('type');
            $table->timestamp('expiry')->nullable()->after('referral_type');
            $table->integer('max_count')->nullable()->after('expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->dropColumn(['referral_type', 'expiry', 'max_count']);
        });
    }
};
