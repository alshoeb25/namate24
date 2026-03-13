<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table already exists in database - this migration is skipped
        // The subscription_orders table was created manually
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - table creation is skipped
    }
};
