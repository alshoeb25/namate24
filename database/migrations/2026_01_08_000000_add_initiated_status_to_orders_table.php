<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'initiated' to the orders status enum
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'initiated', 'completed', 'failed', 'cancelled') DEFAULT 'initiated'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'initiated' from the orders status enum
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending'");
    }
};
