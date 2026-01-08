<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('enquiry_unlocks')) {
            return;
        }

        // Drop FK constraints referencing teacher_id (if any)
        if (Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'enquiry_unlocks'
                  AND COLUMN_NAME = 'teacher_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            foreach ($constraints as $c) {
                $name = $c->CONSTRAINT_NAME ?? $c->constraint_name ?? null;
                if ($name) {
                    DB::statement("ALTER TABLE enquiry_unlocks DROP FOREIGN KEY `{$name}`");
                }
            }

            // Drop unique index if exists
            $uniqueExists = collect(DB::select("SHOW INDEX FROM enquiry_unlocks WHERE Key_name = 'enquiry_unlocks_enquiry_id_teacher_id_unique'"))->isNotEmpty();
            if ($uniqueExists) {
                DB::statement("ALTER TABLE enquiry_unlocks DROP INDEX `enquiry_unlocks_enquiry_id_teacher_id_unique`");
            }

            // Drop simple index on teacher_id if exists
            $idxExists = collect(DB::select("SHOW INDEX FROM enquiry_unlocks WHERE Key_name = 'enquiry_unlocks_teacher_id_index'"))->isNotEmpty();
            if ($idxExists) {
                DB::statement("ALTER TABLE enquiry_unlocks DROP INDEX `enquiry_unlocks_teacher_id_index`");
            }

            // Finally drop the column safely
            Schema::table('enquiry_unlocks', function (Blueprint $table) {
                if (Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
                    $table->dropColumn('teacher_id');
                }
            });
        }

        // Ensure indexes on tutor_id (if the column exists)
        if (Schema::hasColumn('enquiry_unlocks', 'tutor_id')) {
            // Add unique composite if not present
            $uniqueTutor = collect(DB::select("SHOW INDEX FROM enquiry_unlocks WHERE Key_name = 'enquiry_unlocks_enquiry_id_tutor_id_unique'"))->isNotEmpty();
            if (!$uniqueTutor) {
                Schema::table('enquiry_unlocks', function (Blueprint $table) {
                    $table->unique(['enquiry_id', 'tutor_id'], 'enquiry_unlocks_enquiry_id_tutor_id_unique');
                });
            }

            // Add index on tutor_id if not present
            $idxTutor = collect(DB::select("SHOW INDEX FROM enquiry_unlocks WHERE Key_name = 'enquiry_unlocks_tutor_id_index'"))->isNotEmpty();
            if (!$idxTutor) {
                Schema::table('enquiry_unlocks', function (Blueprint $table) {
                    $table->index('tutor_id', 'enquiry_unlocks_tutor_id_index');
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('enquiry_unlocks')) {
            return;
        }
        if (!Schema::hasColumn('enquiry_unlocks', 'teacher_id')) {
            Schema::table('enquiry_unlocks', function (Blueprint $table) {
                $table->unsignedBigInteger('teacher_id')->nullable()->after('enquiry_id');
            });
        }
    }
};
