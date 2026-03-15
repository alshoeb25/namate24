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
        Schema::table('subscription_view_logs', function (Blueprint $table) {
            // Ensure user_id column exists as unsigned big integer
            if (!Schema::hasColumn('subscription_view_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id')->comment('Reference to user who performed the action');
                // Add FK only when we add the column (fresh installs already have it)
                $table->foreign('user_id', 'svl_user_fk')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_view_logs', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign('svl_user_fk');
        });
    }
};
