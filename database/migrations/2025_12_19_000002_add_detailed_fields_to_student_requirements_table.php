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
            // Check and add only missing columns
            if (!Schema::hasColumn('student_requirements', 'phone')) {
                $table->string('phone')->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('student_requirements', 'location')) {
                $table->string('location')->nullable()->after('city');
            }
            if (!Schema::hasColumn('student_requirements', 'subjects')) {
                $table->json('subjects')->nullable()->after('subject_id');
            }
            if (!Schema::hasColumn('student_requirements', 'level')) {
                $table->string('level')->nullable()->after('subjects');
            }
            if (!Schema::hasColumn('student_requirements', 'service_type')) {
                $table->string('service_type')->nullable()->after('mode');
            }
            if (!Schema::hasColumn('student_requirements', 'meeting_options')) {
                $table->json('meeting_options')->nullable()->after('service_type');
            }
            if (!Schema::hasColumn('student_requirements', 'budget')) {
                $table->decimal('budget', 10, 2)->nullable()->after('budget_max');
            }
            if (!Schema::hasColumn('student_requirements', 'budget_type')) {
                $table->string('budget_type')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('student_requirements', 'gender_preference')) {
                $table->string('gender_preference')->nullable()->after('budget_type');
            }
            if (!Schema::hasColumn('student_requirements', 'availability')) {
                $table->json('availability')->nullable()->after('gender_preference');
            }
            if (!Schema::hasColumn('student_requirements', 'time_preference')) {
                $table->string('time_preference')->nullable()->after('availability');
            }
            if (!Schema::hasColumn('student_requirements', 'languages')) {
                $table->json('languages')->nullable()->after('time_preference');
            }
            if (!Schema::hasColumn('student_requirements', 'tutor_location_preference')) {
                $table->string('tutor_location_preference')->nullable()->after('languages');
            }
            if (!Schema::hasColumn('student_requirements', 'max_distance')) {
                $table->integer('max_distance')->nullable()->after('tutor_location_preference');
            }
            if (!Schema::hasColumn('student_requirements', 'status')) {
                $table->string('status')->default('active');
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
                'phone',
                'location',
                'subjects',
                'level',
                'service_type',
                'meeting_options',
                'budget',
                'budget_type',
                'gender_preference',
                'availability',
                'time_preference',
                'languages',
                'tutor_location_preference',
                'max_distance',
                'status',
            ]);
        });
    }
};
