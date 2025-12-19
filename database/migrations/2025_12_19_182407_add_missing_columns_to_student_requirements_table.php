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
            if (!Schema::hasColumn('student_requirements', 'area')) {
                $table->string('area')->nullable()->after('city');
            }
            if (!Schema::hasColumn('student_requirements', 'pincode')) {
                $table->string('pincode', 10)->nullable()->after('area');
            }
            if (!Schema::hasColumn('student_requirements', 'alternate_phone')) {
                $table->string('alternate_phone', 20)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('student_requirements', 'student_name')) {
                $table->string('student_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('student_requirements', 'other_subject')) {
                $table->string('other_subject')->nullable()->after('subject_id');
            }
            if (!Schema::hasColumn('student_requirements', 'travel_distance')) {
                $table->integer('travel_distance')->nullable()->after('meeting_options');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropColumn([
                'area', 'pincode', 'alternate_phone', 'student_name', 
                'other_subject', 'travel_distance'
            ]);
        });
    }
};
