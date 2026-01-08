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
        if (!Schema::hasTable('tutors')) {
            return;
        }
        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors', 'phone')) {
                $table->string('phone', 20)->nullable()->after('city');
            }
            if (!Schema::hasColumn('tutors', 'country_code')) {
                $table->string('country_code', 10)->default('+91')->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tutors')) {
            return;
        }
        Schema::table('tutors', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('tutors', 'country_code')) $drops[] = 'country_code';
            if (Schema::hasColumn('tutors', 'phone')) $drops[] = 'phone';
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
