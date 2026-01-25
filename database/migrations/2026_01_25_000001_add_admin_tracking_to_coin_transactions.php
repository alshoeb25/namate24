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
        Schema::table('coin_transactions', function (Blueprint $table) {
            $table->foreignId('added_by_admin_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->index('added_by_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coin_transactions', function (Blueprint $table) {
            $table->dropForeign(['added_by_admin_id']);
            $table->dropIndex(['added_by_admin_id']);
            $table->dropColumn('added_by_admin_id');
        });
    }
};
