<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors', 'description')) {
                $table->longText('description')->nullable()->after('about');
            }
            
            if (!Schema::hasColumn('tutors', 'do_not_share_contact')) {
                $table->boolean('do_not_share_contact')->default(false)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            if (Schema::hasColumn('tutors', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('tutors', 'do_not_share_contact')) {
                $table->dropColumn('do_not_share_contact');
            }
        });
    }
};
