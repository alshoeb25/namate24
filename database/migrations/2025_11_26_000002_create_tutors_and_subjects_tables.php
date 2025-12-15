<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('about')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->decimal('price_per_hour', 8, 2)->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('teaching_mode', ['online','offline','both'])->default('online');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('verified')->default(false);
            $table->json('verification_documents')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('gender')->nullable();
            $table->json('badges')->nullable(); // e.g. ["top_rated"]
            $table->timestamps();
        });

        Schema::create('tutor_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('level')->nullable(); // e.g. "school", "college", "competitive"
            $table->timestamps();
            $table->unique(['tutor_id','subject_id','level']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutor_subject');
        Schema::dropIfExists('tutors');
        Schema::dropIfExists('subjects');
    }
};