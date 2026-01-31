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
            $table->timestamp('teachers_viewed_at')->nullable()->after('approached_at');
            $table->integer('teachers_view_coins')->nullable()->after('teachers_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropColumn(['teachers_viewed_at', 'teachers_view_coins']);
        });
    }
};
