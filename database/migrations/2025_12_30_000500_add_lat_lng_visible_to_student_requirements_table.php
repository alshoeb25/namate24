<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirements', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('city');
            }
            if (!Schema::hasColumn('student_requirements', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
            if (!Schema::hasColumn('student_requirements', 'visible')) {
                $table->boolean('visible')->default(true)->after('desired_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('student_requirements', 'visible')) {
                $table->dropColumn('visible');
            }
        });
    }
};
