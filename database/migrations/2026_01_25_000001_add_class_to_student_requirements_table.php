<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirements', 'class')) {
                $table->string('class')->nullable()->after('student_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('student_requirements', 'class')) {
                $table->dropColumn('class');
            }
        });
    }
};
