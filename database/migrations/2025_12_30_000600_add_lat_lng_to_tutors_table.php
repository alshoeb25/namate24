<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutors')) {
            return;
        }

        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('city');
            }
            if (!Schema::hasColumn('tutors', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tutors')) {
            return;
        }

        Schema::table('tutors', function (Blueprint $table) {
            if (Schema::hasColumn('tutors', 'lng')) {
                $table->dropColumn('lng');
            }
            if (Schema::hasColumn('tutors', 'lat')) {
                $table->dropColumn('lat');
            }
        });
    }
};
