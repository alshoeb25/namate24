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
        Schema::create('subscription_view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_subscription_id')->constrained('user_subscriptions')->onDelete('cascade');
            $table->foreignId('viewable_id'); // ID of the profile/requirement viewed
            $table->string('viewable_type'); // Polymorphic type (e.g., 'App\Models\Tutor', 'App\Models\StudentRequirement')
            $table->ipAddress()->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('user_subscription_id');
            $table->index(['viewable_id', 'viewable_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_view_logs');
    }
};
