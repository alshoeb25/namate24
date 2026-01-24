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
        if (Schema::hasTable('tutors')) {
            Schema::table('tutors', function (Blueprint $table) {
                if (!Schema::hasColumn('tutors', 'video_approval_status')) {
                    $table->enum('video_approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('youtube_intro_url')->comment('Video approval status: pending, approved, rejected');
                }
                if (!Schema::hasColumn('tutors', 'video_rejection_reason')) {
                    $table->text('video_rejection_reason')->nullable()->after('video_approval_status')->comment('Reason for rejecting the video');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tutors')) {
            Schema::table('tutors', function (Blueprint $table) {
                if (Schema::hasColumn('tutors', 'video_approval_status')) {
                    $table->dropColumn('video_approval_status');
                }
                if (Schema::hasColumn('tutors', 'video_rejection_reason')) {
                    $table->dropColumn('video_rejection_reason');
                }
            });
        }
    }
};
