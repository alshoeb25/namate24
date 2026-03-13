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
        Schema::table('subscription_view_logs', function (Blueprint $table) {
            // Add action_type column if it doesn't exist
            if (!Schema::hasColumn('subscription_view_logs', 'action_type')) {
                $table->string('action_type')->nullable()->after('viewable_type')->comment('Type of action: student_contact_unlock, student_approach_tutor, tutor_enquiry_unlock, etc');
            }
            
            // Add viewed_at column if it doesn't exist
            if (!Schema::hasColumn('subscription_view_logs', 'viewed_at')) {
                $table->timestamp('viewed_at')->nullable()->after('user_agent')->comment('Timestamp when the view occurred');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_view_logs', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_view_logs', 'action_type')) {
                $table->dropColumn('action_type');
            }
            if (Schema::hasColumn('subscription_view_logs', 'viewed_at')) {
                $table->dropColumn('viewed_at');
            }
        });
    }
};

