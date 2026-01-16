<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->comment('Reason for rejection');
            $table->text('rejection_notes')->nullable()->comment('Admin notes on rejection');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin who reviewed/moderated');
            $table->timestamp('reviewed_at')->nullable()->comment('When the moderation action was taken');
        });
    }

    public function down()
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropForeignIdFor('users', 'reviewed_by');
            $table->dropColumn('rejection_reason', 'rejection_notes', 'reviewed_by', 'reviewed_at');
        });
    }
};
