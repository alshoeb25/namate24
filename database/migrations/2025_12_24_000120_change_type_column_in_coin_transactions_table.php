<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coin_transactions')) {
            DB::statement("ALTER TABLE coin_transactions MODIFY type VARCHAR(50)");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('coin_transactions')) {
            DB::statement("ALTER TABLE coin_transactions MODIFY type ENUM('purchase','referral_bonus','referral_reward','booking','refund','admin_credit','admin_debit')");
        }
    }
};
