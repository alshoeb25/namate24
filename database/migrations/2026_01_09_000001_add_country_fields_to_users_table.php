<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('country_code')->comment('Country name for location-based pricing');
            $table->string('country_iso', 2)->nullable()->after('country')->comment('ISO 3166-1 alpha-2 country code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'country_iso']);
        });
    }
};
