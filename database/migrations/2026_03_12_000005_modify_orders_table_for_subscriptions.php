<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table already modified in database - this migration is skipped
        // The orders table modifications were applied manually
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op - table modifications are skipped
    }
};

