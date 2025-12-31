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
        Schema::create('field_labels', function (Blueprint $table) {
            $table->id();
            $table->string('field_name')->index(); // e.g., 'service_type', 'budget_type', etc.
            $table->string('field_value')->index(); // e.g., 'tutoring', 'per_hour', etc.
            $table->string('label'); // e.g., 'Tutoring', 'Per Hour', etc.
            $table->string('category')->nullable()->index(); // e.g., 'requirement', 'general'
            $table->integer('order')->default(0); // For sorting in UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['field_name', 'field_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_labels');
    }
};
