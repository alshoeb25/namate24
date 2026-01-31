<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Get the table name
        $table = 'enquiry_unlocks';

        // // Check if table exists
        // if (!Schema::hasTable($table)) {
        //     return;
        // }

        // // If teacher_id column still exists, remove it
        // if (Schema::hasColumn($table, 'teacher_id')) {
        //     // Get the foreign keys on this table
        //     $foreignKeys = DB::select("SELECT CONSTRAINT_NAME 
        //         FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        //         WHERE TABLE_NAME = ? AND COLUMN_NAME = 'teacher_id' AND TABLE_SCHEMA = ?", 
        //         [$table, env('DB_DATABASE')]
        //     );

        //     // Drop each foreign key
        //     foreach ($foreignKeys as $fk) {
        //         if ($fk->CONSTRAINT_NAME !== 'PRIMARY') {
        //             DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        //         }
        //     }

        //     // Now drop the column
        //     DB::statement("ALTER TABLE {$table} DROP COLUMN teacher_id");
        // }
    }

    public function down(): void
    {
        // Rollback would recreate the column but we don't need to support this
    }
};
