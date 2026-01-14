<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            // Drop the teacher_id foreign key constraint first
            if (Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
                // Drop foreign key constraint - try multiple possible names
                try {
                    $table->dropForeign('enquiry_unlocks_teacher_id_foreign');
                } catch (\Exception $e) {
                    // Ignore if doesn't exist
                }
                // Then drop the column
                $table->dropColumn('teacher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            // Recreate teacher_id column on rollback
            if (!Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('enquiry_id')->constrained('users')->cascadeOnDelete();
            }
        });
    }
};
