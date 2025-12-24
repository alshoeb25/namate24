<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('student_requirements')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->integer('unlock_price')->default(0);
            $table->timestamps();

            $table->unique(['enquiry_id', 'teacher_id']);
            $table->index('teacher_id');
            $table->index('enquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_unlocks');
    }
};
