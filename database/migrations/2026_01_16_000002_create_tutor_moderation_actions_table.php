<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutor_moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('tutors')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->enum('action', ['approve', 'reject', 'pending_review'])->default('pending_review');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->enum('old_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->enum('new_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutor_moderation_actions');
    }
};
