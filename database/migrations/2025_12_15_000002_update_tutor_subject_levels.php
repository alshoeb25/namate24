<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_subject', function (Blueprint $table) {
            
            $table->foreignId('from_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('to_level_id')->nullable()->constrained('levels')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('tutor_subject', function (Blueprint $table) {
            $table->dropForeign(['from_level_id']);
            $table->dropForeign(['to_level_id']);
            $table->dropColumn(['from_level_id', 'to_level_id']);
            $table->string('level')->nullable();
        });
    }
};
