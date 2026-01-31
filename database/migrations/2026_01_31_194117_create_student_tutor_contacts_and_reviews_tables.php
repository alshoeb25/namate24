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
        // Table to track which students have unlocked which tutors' contact details
        Schema::create('student_tutor_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('tutor_id');
            $table->integer('coins_spent')->default(50);
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id', 'stc_student_fk')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');
            
            $table->foreign('tutor_id', 'stc_tutor_fk')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Unique constraint to prevent duplicate unlocks
            $table->unique(['student_id', 'tutor_id'], 'stc_student_tutor_unique');

            // Indexes for faster queries
            $table->index('student_id', 'stc_student_idx');
            $table->index('tutor_id', 'stc_tutor_idx');
        });

        // Table to store tutor reviews from students
        Schema::create('tutor_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tutor_id');
            $table->unsignedBigInteger('student_id');
            $table->tinyInteger('rating')->comment('1-5 star rating');
            $table->text('comment');
            $table->timestamps();

            // Foreign keys
            $table->foreign('tutor_id', 'tr_tutor_fk')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->foreign('student_id', 'tr_student_fk')
                  ->references('id')
                  ->on('students')
                  ->onDelete('cascade');

            // One review per student per tutor
            $table->unique(['tutor_id', 'student_id'], 'tr_tutor_student_unique');

            // Indexes
            $table->index('tutor_id', 'tr_tutor_idx');
            $table->index('student_id', 'tr_student_idx');
            $table->index('rating', 'tr_rating_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_reviews');
        Schema::dropIfExists('student_tutor_contacts');
    }
};
