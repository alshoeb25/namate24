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
            // Extended profile fields (ensure availability when earlier alter migration is skipped)
            $table->text('teaching_methodology')->nullable();
            $table->json('educations')->nullable();
            $table->json('experiences')->nullable();
            $table->json('courses')->nullable();
            $table->text('availability')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->decimal('price_per_hour', 8, 2)->nullable();
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('teaching_mode', ['online','offline','both'])->default('online');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('introductory_video')->nullable();
            $table->string('video_title')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('verified')->default(false);
            $table->json('verification_documents')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('gender')->nullable();
            $table->json('badges')->nullable(); // e.g. ["top_rated"]
            $table->json('settings')->nullable();
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