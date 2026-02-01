<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tutor_reviews', 'status')) {
            Schema::table('tutor_reviews', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('comment');
            });
            return;
        }

        DB::statement("ALTER TABLE tutor_reviews MODIFY COLUMN status ENUM('pending','approved','rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('tutor_reviews', 'status')) {
            return;
        }

        DB::statement("ALTER TABLE tutor_reviews MODIFY COLUMN status ENUM('pending','approved') DEFAULT 'pending'");
    }
};
