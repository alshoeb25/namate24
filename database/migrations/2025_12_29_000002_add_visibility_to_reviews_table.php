<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false);
            }
            if (!Schema::hasColumn('reviews', 'hidden_by')) {
                $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('reviews', 'hidden_at')) {
                $table->timestamp('hidden_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'hidden_by');
            $table->dropColumn(['is_hidden', 'hidden_by', 'hidden_at']);
        });
    }
};
