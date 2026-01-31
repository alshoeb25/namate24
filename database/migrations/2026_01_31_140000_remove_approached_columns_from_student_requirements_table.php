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
        // First, migrate existing data from student_requirements to student_requirement_approached_tutors
        $requirements = \DB::table('student_requirements')
            ->whereNotNull('approached_teacher_id')
            ->whereNotNull('approached_at')
            ->get(['id', 'student_id', 'approached_teacher_id', 'approached_at']);

        foreach ($requirements as $requirement) {
            // Check if this record doesn't already exist
            $exists = \DB::table('student_requirement_approached_tutors')
                ->where('student_requirement_id', $requirement->id)
                ->where('tutor_id', $requirement->approached_teacher_id)
                ->where('student_id', $requirement->student_id)
                ->exists();

            if (!$exists) {
                \DB::table('student_requirement_approached_tutors')->insert([
                    'student_requirement_id' => $requirement->id,
                    'tutor_id' => $requirement->approached_teacher_id,
                    'student_id' => $requirement->student_id,
                    'coins_spent' => 10, // Default approach cost
                    'created_at' => $requirement->approached_at,
                    'updated_at' => $requirement->approached_at,
                ]);
            }
        }

        // Now drop the columns from student_requirements
        // Check if foreign key exists before dropping
        $foreignKeyExists = \DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'student_requirements' 
             AND COLUMN_NAME = 'approached_teacher_id' 
             AND CONSTRAINT_NAME LIKE '%foreign%'"
        );

        if (!empty($foreignKeyExists)) {
            $constraintName = $foreignKeyExists[0]->CONSTRAINT_NAME;
            \DB::statement("ALTER TABLE student_requirements DROP FOREIGN KEY `{$constraintName}`");
        }

        Schema::table('student_requirements', function (Blueprint $table) {
            // Drop columns
            if (Schema::hasColumn('student_requirements', 'approached_teacher_id')) {
                $table->dropColumn('approached_teacher_id');
            }
            
            if (Schema::hasColumn('student_requirements', 'approached_at')) {
                $table->dropColumn('approached_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            // Recreate the columns
            $table->foreignId('approached_teacher_id')->nullable()->after('status');
            $table->timestamp('approached_at')->nullable()->after('approached_teacher_id');
            
            // Note: Foreign key constraint will not be recreated in down() 
            // as the original constraint reference may no longer be valid
        });
    }
};
