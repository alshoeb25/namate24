<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_requirements', 'post_fee')) {
                $table->integer('post_fee')->default(0)->after('status');
            }
            if (!Schema::hasColumn('student_requirements', 'unlock_price')) {
                $table->integer('unlock_price')->default(0)->after('post_fee');
            }
            if (!Schema::hasColumn('student_requirements', 'max_leads')) {
                $table->unsignedTinyInteger('max_leads')->default(5)->after('unlock_price');
            }
            if (!Schema::hasColumn('student_requirements', 'current_leads')) {
                $table->unsignedTinyInteger('current_leads')->default(0)->after('max_leads');
            }
            if (!Schema::hasColumn('student_requirements', 'lead_status')) {
                $table->string('lead_status', 20)->default('open')->after('current_leads');
            }
            if (!Schema::hasColumn('student_requirements', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('lead_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropColumn([
                'post_fee',
                'unlock_price',
                'max_leads',
                'current_leads',
                'lead_status',
                'posted_at',
            ]);
        });
    }
};
