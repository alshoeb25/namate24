<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            // Drop unique constraint first
            $table->dropUnique('enquiry_unlocks_enquiry_id_teacher_id_unique');
            // Drop index
            $table->dropIndex('enquiry_unlocks_teacher_id_index');
        });

        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            // Now drop the column
            $table->dropColumn('teacher_id');
        });

        // Add unique constraint for the new tutor_id
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->unique(['enquiry_id', 'tutor_id']);
            $table->index('tutor_id');
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('enquiry_id');
        });
    }
};
