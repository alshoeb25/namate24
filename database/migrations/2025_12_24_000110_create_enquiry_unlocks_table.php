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
            $table->unsignedBigInteger('teacher_id')->nullable(); // legacy; replaced by tutor_id
            $table->integer('unlock_price')->default(0);
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('enquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_unlocks');
    }
};
