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
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->renameColumn('hired_teacher_id', 'approached_teacher_id');
            $table->renameColumn('hired_at', 'approached_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->renameColumn('approached_teacher_id', 'hired_teacher_id');
            $table->renameColumn('approached_at', 'hired_at');
        });
    }
};
