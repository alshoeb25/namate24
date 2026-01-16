<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('is_disabled')->default(false)->after('lng');
            $table->text('disabled_reason')->nullable()->after('is_disabled');
            $table->foreignId('disabled_by')->nullable()->constrained('users')->nullOnDelete()->after('disabled_reason');
            $table->timestamp('disabled_at')->nullable()->after('disabled_by');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['disabled_by']);
            $table->dropColumn(['is_disabled', 'disabled_reason', 'disabled_by', 'disabled_at']);
        });
    }
};
