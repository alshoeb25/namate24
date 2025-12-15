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
        // Create subject_modules table
        Schema::create('subject_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->integer('estimated_hours')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->index(['subject_id', 'is_active']);
        });

        // Create module_topics table (sub-topics within modules)
        Schema::create('module_topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('code')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('subject_modules')->onDelete('cascade');
            $table->index(['module_id', 'is_active']);
        });

        // Create module_competencies table
        Schema::create('module_competencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('competency_type', ['knowledge', 'skill', 'attitude'])->default('skill');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->foreign('module_id')->references('id')->on('subject_modules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_competencies');
        Schema::dropIfExists('module_topics');
        Schema::dropIfExists('subject_modules');
    }
};
