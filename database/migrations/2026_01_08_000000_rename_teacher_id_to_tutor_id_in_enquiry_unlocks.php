<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiry_unlocks', 'tutor_id')) {
                // Add as nullable to avoid issues on existing rows; backfill right after
                $table->unsignedBigInteger('tutor_id')->nullable()->after('enquiry_id');
            }
        });

        // Copy values from teacher_id to tutor_id if both exist
        if (Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
            DB::table('enquiry_unlocks')
                ->whereNull('tutor_id')
                ->update(['tutor_id' => DB::raw('teacher_id')]);
        }

        // Do NOT drop old column here to avoid migration failures in environments
        // where the column has already been removed or differs; safe to keep legacy column.
    }

    public function down(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('enquiry_id');
        });
        // Roll back values
        DB::table('enquiry_unlocks')
            ->whereNull('teacher_id')
            ->update(['teacher_id' => DB::raw('tutor_id')]);
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->dropColumn('tutor_id');
        });
    }
};