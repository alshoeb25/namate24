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
        Schema::table('student_tutor_contacts', function (Blueprint $table) {
            // Drop the incorrect foreign key that references users
            $table->dropForeign('stc_tutor_fk');
            
            // Add correct foreign key referencing tutors table
            $table->foreign('tutor_id', 'stc_tutor_fk')
                  ->references('id')
                  ->on('tutors')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_tutor_contacts', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign('stc_tutor_fk');
            
            // Restore the old foreign key (for rollback)
            $table->foreign('tutor_id', 'stc_tutor_fk')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
