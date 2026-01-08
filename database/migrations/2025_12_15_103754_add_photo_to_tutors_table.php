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
            if (!Schema::hasColumn('tutors', 'photo')) {
                $table->string('photo')->nullable();
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
            if (Schema::hasColumn('tutors', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }
};
