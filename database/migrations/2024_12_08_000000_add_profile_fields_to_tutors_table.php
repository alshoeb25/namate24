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
        Schema::table('tutors', function (Blueprint $table) {
            // Address fields
            if (!Schema::hasColumn('tutors', 'address')) {
                $table->string('address')->nullable()->after('city');
            }
            
            if (!Schema::hasColumn('tutors', 'state')) {
                $table->string('state')->nullable()->after('address');
            }
            
            if (!Schema::hasColumn('tutors', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
            
            if (!Schema::hasColumn('tutors', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }

            // Video fields
            if (!Schema::hasColumn('tutors', 'introductory_video')) {
                $table->string('introductory_video')->nullable()->after('about');
            }
            
            if (!Schema::hasColumn('tutors', 'video_title')) {
                $table->string('video_title')->nullable()->after('introductory_video');
            }

            // Teaching & Education fields
            if (!Schema::hasColumn('tutors', 'teaching_methodology')) {
                $table->text('teaching_methodology')->nullable()->after('about');
            }
            
            if (!Schema::hasColumn('tutors', 'educations')) {
                $table->json('educations')->nullable()->after('experience_years');
            }
            
            if (!Schema::hasColumn('tutors', 'experiences')) {
                $table->json('experiences')->nullable()->after('educations');
            }
            
            if (!Schema::hasColumn('tutors', 'courses')) {
                $table->json('courses')->nullable()->after('experiences');
            }
            
            if (!Schema::hasColumn('tutors', 'availability')) {
                $table->text('availability')->nullable()->after('teaching_mode');
            }

            // Settings field
            if (!Schema::hasColumn('tutors', 'settings')) {
                $table->json('settings')->nullable()->after('moderation_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $columns = ['address', 'state', 'country', 'postal_code', 'introductory_video', 
                       'video_title', 'teaching_methodology', 'educations', 'experiences', 
                       'courses', 'availability', 'settings'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('tutors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
