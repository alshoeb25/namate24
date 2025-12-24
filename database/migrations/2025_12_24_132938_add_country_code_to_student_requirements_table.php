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
            if (!Schema::hasColumn('student_requirements', 'country_code')) {
                $table->string('country_code', 10)->default('+91')->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
};
