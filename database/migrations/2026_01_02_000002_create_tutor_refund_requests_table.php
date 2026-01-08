<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enquiry_id')->constrained('student_requirements')->cascadeOnDelete();
            $table->foreignId('unlock_id')->nullable()->constrained('enquiry_unlocks')->cascadeOnDelete();
            
            // Refund reason/status
            $table->enum('reason', ['spam', 'no_response', 'wrong_details', 'other'])->default('no_response');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->integer('refund_amount')->default(0); // coins to refund
            $table->text('notes')->nullable(); // teacher's explanation
            $table->text('admin_notes')->nullable(); // admin decision reason
            
            // Timestamps
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes for queries
            $table->index(['tutor_id', 'status']);
            $table->index(['enquiry_id', 'status']);
            $table->index('status');
            $table->unique(['tutor_id', 'enquiry_id']); // One refund request per tutor per enquiry
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_refund_requests');
    }
};
