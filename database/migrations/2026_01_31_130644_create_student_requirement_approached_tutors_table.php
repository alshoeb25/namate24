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
        // Drop table if it exists from previous failed migration
        Schema::dropIfExists('student_requirement_approached_tutors');
        
        Schema::create('student_requirement_approached_tutors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_requirement_id');
            $table->unsignedBigInteger('tutor_id');
            $table->unsignedBigInteger('student_id');
            $table->integer('coins_spent')->default(10);
            $table->timestamps();
            
            // Foreign keys with shorter names
            $table->foreign('student_requirement_id', 'sr_approached_requirement_fk')
                  ->references('id')->on('student_requirements')->onDelete('cascade');
            $table->foreign('tutor_id', 'sr_approached_tutor_fk')
                  ->references('id')->on('tutors')->onDelete('cascade');
            $table->foreign('student_id', 'sr_approached_student_fk')
                  ->references('id')->on('users')->onDelete('cascade');
            
            // Prevent duplicate approaches
            $table->unique(['student_requirement_id', 'tutor_id'], 'requirement_tutor_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_requirement_approached_tutors');
    }
};
