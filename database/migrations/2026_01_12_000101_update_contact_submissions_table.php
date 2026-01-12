<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            // Rename name column to first_name if it exists
            if (Schema::hasColumn('contact_submissions', 'name')) {
                $table->renameColumn('name', 'first_name');
            }
            
            // Add new columns
            if (!Schema::hasColumn('contact_submissions', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('contact_submissions', 'organization_name')) {
                $table->string('organization_name')->nullable();
            }
            if (!Schema::hasColumn('contact_submissions', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'organization_name', 'contact_person']);
        });
    }
};
