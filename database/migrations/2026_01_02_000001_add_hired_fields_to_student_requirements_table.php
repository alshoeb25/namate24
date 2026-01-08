<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirements', 'hired_teacher_id')) {
                $table->foreignId('hired_teacher_id')->nullable()->constrained('users')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('student_requirements', 'hired_at')) {
                $table->timestamp('hired_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('student_requirements', 'hired_teacher_id')) {
                $table->dropConstrainedForeignId('hired_teacher_id');
            }
            if (Schema::hasColumn('student_requirements', 'hired_at')) {
                $table->dropColumn('hired_at');
            }
        });
    }
};
