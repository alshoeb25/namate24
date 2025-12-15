<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tutors table additions
        if (Schema::hasTable('tutors')) {
            Schema::table('tutors', function (Blueprint $table) {
                if (!Schema::hasColumn('tutors', 'youtube_intro_url')) {
                    $table->string('youtube_intro_url')->nullable()->after('introductory_video');
                }
                if (!Schema::hasColumn('tutors', 'speciality')) {
                    $table->string('speciality')->nullable()->after('youtube_intro_url');
                }
                if (!Schema::hasColumn('tutors', 'strength')) {
                    $table->text('strength')->nullable()->after('speciality');
                }
                if (!Schema::hasColumn('tutors', 'current_role')) {
                    $table->string('current_role')->nullable()->after('strength');
                }
            });
        }

        // Users table additions for phone OTP
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone_otp')) {
                    $table->string('phone_otp')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'phone_otp_expires_at')) {
                    $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp');
                }
                if (!Schema::hasColumn('users', 'phone_verified_at')) {
                    $table->timestamp('phone_verified_at')->nullable()->after('phone_otp_expires_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tutors')) {
            Schema::table('tutors', function (Blueprint $table) {
                if (Schema::hasColumn('tutors', 'youtube_intro_url')) {
                    $table->dropColumn('youtube_intro_url');
                }
                if (Schema::hasColumn('tutors', 'speciality')) {
                    $table->dropColumn('speciality');
                }
                if (Schema::hasColumn('tutors', 'strength')) {
                    $table->dropColumn('strength');
                }
                if (Schema::hasColumn('tutors', 'current_role')) {
                    $table->dropColumn('current_role');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'phone_otp')) {
                    $table->dropColumn('phone_otp');
                }
                if (Schema::hasColumn('users', 'phone_otp_expires_at')) {
                    $table->dropColumn('phone_otp_expires_at');
                }
                if (Schema::hasColumn('users', 'phone_verified_at')) {
                    $table->dropColumn('phone_verified_at');
                }
            });
        }
    }
};
