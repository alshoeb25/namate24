<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fix all student_id and tutor_id foreign keys to reference the correct tables.
 *
 * Before: student_id → users.id  (wrong — user_id was stored, not students.id)
 * After:  student_id → students.id (correct)
 *
 * Before: tutor_refund_requests.tutor_id → users.id (wrong)
 * After:  tutor_refund_requests.tutor_id → tutors.id (correct)
 *
 * Also: enquiry_unlocks.tutor_id had no FK — add one → tutors.id
 */
return new class extends Migration
{
    public function up(): void
    {
        if (\DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support named FK drops; fresh test DBs start with correct schema.
            return;
        }

        // ── Step 1: Drop all incorrect FKs first (so data can be updated freely) ──
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropForeign('student_requirements_student_id_foreign');
        });
        Schema::table('student_requirement_approached_tutors', function (Blueprint $table) {
            $table->dropForeign('sr_approached_student_fk');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('bookings_student_id_foreign');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_student_id_foreign');
        });
        Schema::table('tutor_refund_requests', function (Blueprint $table) {
            $table->dropForeign('tutor_refund_requests_tutor_id_foreign');
        });

        // ── Step 2: Ensure every user who has student records has a student profile ──
        $this->ensureStudentProfilesExist();

        // ── Step 3: Migrate data — convert user_id values to students.id ──
        $this->migrateStudentIds('student_requirements', 'student_id');
        $this->migrateStudentIds('student_requirement_approached_tutors', 'student_id');
        $this->migrateStudentIds('bookings', 'student_id');
        $this->migrateStudentIds('reviews', 'student_id');

        // ── Step 4: Add correct FKs → students.id ──
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->foreign('student_id', 'student_requirements_student_id_foreign')
                  ->references('id')->on('students')->onDelete('cascade');
        });
        Schema::table('student_requirement_approached_tutors', function (Blueprint $table) {
            $table->foreign('student_id', 'sr_approached_student_fk')
                  ->references('id')->on('students')->onDelete('cascade');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('student_id', 'bookings_student_id_foreign')
                  ->references('id')->on('students')->onDelete('cascade');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('student_id', 'reviews_student_id_foreign')
                  ->references('id')->on('students')->onDelete('cascade');
        });

        // ── Step 5: Fix tutor_refund_requests.tutor_id → tutors.id ──
        Schema::table('tutor_refund_requests', function (Blueprint $table) {
            $table->foreign('tutor_id', 'tutor_refund_requests_tutor_id_foreign')
                  ->references('id')->on('tutors')->onDelete('cascade');
        });

        // ── Step 6: Add missing FK for enquiry_unlocks.tutor_id → tutors.id ──
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->foreign('tutor_id', 'enquiry_unlocks_tutor_id_foreign')
                  ->references('id')->on('tutors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Remove new FKs
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->dropForeign('enquiry_unlocks_tutor_id_foreign');
        });
        Schema::table('tutor_refund_requests', function (Blueprint $table) {
            $table->dropForeign('tutor_refund_requests_tutor_id_foreign');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign('reviews_student_id_foreign');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('bookings_student_id_foreign');
        });
        Schema::table('student_requirement_approached_tutors', function (Blueprint $table) {
            $table->dropForeign('sr_approached_student_fk');
        });
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropForeign('student_requirements_student_id_foreign');
        });

        // Restore original FKs → users.id
        // Note: data is NOT reversed (students.id values remain) — a fresh migrate:fresh is needed for full rollback
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->foreign('student_id', 'student_requirements_student_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('student_requirement_approached_tutors', function (Blueprint $table) {
            $table->foreign('student_id', 'sr_approached_student_fk')
                  ->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('student_id', 'bookings_student_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('student_id', 'reviews_student_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('tutor_refund_requests', function (Blueprint $table) {
            $table->foreign('tutor_id', 'tutor_refund_requests_tutor_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Create student profiles for any users who have records in student-related
     * tables but do not yet have a row in the students table.
     */
    private function ensureStudentProfilesExist(): void
    {
        $tables = [
            'student_requirements',
            'student_requirement_approached_tutors',
            'bookings',
            'reviews',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Get distinct user-ids that have no matching student profile
            $userIds = DB::table($table)
                ->leftJoin('students', 'students.user_id', '=', "{$table}.student_id")
                ->whereNull('students.id')
                ->distinct()
                ->pluck("{$table}.student_id");

            foreach ($userIds as $userId) {
                $userExists = DB::table('users')->where('id', $userId)->exists();
                $studentExists = DB::table('students')->where('user_id', $userId)->exists();

                if ($userExists && !$studentExists) {
                    DB::table('students')->insert([
                        'user_id'    => $userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Convert a student_id column's values from users.id → students.id.
     */
    private function migrateStudentIds(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $userIds = DB::table($table)->distinct()->pluck($column);

        foreach ($userIds as $userId) {
            $student = DB::table('students')->where('user_id', $userId)->first(['id']);

            if ($student) {
                DB::table($table)
                    ->where($column, $userId)
                    ->update([$column => $student->id]);
            }
        }
    }
};
