<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action_type', 64);
            $table->string('subject_type', 128);
            $table->unsignedBigInteger('subject_id');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['admin_id']);
            $table->index(['action_type']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_action_logs');
    }
};
