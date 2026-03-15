<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tutors: ranking & bid columns ──
        Schema::table('tutors', function (Blueprint $table) {
            $table->unsignedInteger('monthly_bid')->default(0)->after('rating_count')
                  ->comment('Monthly coin commitment set by tutor for ranking');
            $table->unsignedInteger('rank')->nullable()->after('monthly_bid')
                  ->comment('Computed rank based on monthly_bid (1 = best)');
            $table->unsignedTinyInteger('early_access_minutes')->default(120)->after('rank')
                  ->comment('Minutes delay before tutor sees new requirements (0 = instant)');
            $table->unsignedSmallInteger('rotation_order')->default(0)->after('early_access_minutes')
                  ->comment('Tiebreaker for equal-bid tutors — rotated daily');
            $table->timestamp('rank_updated_at')->nullable()->after('rotation_order');

            $table->index('monthly_bid', 'tutors_monthly_bid_idx');
            $table->index('rank', 'tutors_rank_idx');
        });

        // ── Student Requirements: dynamic pricing columns ──
        Schema::table('student_requirements', function (Blueprint $table) {
            $table->unsignedSmallInteger('base_price')->default(0)->after('unlock_price')
                  ->comment('Base coin cost set at creation (demand + region)');
            $table->unsignedSmallInteger('dynamic_price')->default(0)->after('base_price')
                  ->comment('Current coin cost after competition multiplier');
            $table->string('demand_level', 20)->nullable()->after('dynamic_price')
                  ->comment('high | medium | low — from subject classification');
            $table->string('region_code', 10)->nullable()->after('demand_level')
                  ->comment('Country code from student location for region multiplier');
            $table->boolean('price_decayed')->default(false)->after('region_code')
                  ->comment('True when 36-hour decay rule made this free');
            $table->timestamp('decay_checked_at')->nullable()->after('price_decayed');
            $table->timestamp('last_price_update')->nullable()->after('decay_checked_at');

            $table->index('posted_at', 'sr_posted_at_idx');
            $table->index('dynamic_price', 'sr_dynamic_price_idx');
        });

        // ── Enquiry Unlocks: track if student viewed the contact ──
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->timestamp('student_viewed_at')->nullable()->after('unlock_price')
                  ->comment('When student first viewed this tutor after unlock');
            $table->boolean('auto_refunded')->default(false)->after('student_viewed_at')
                  ->comment('True if 15-day auto-refund was issued');
            $table->timestamp('auto_refunded_at')->nullable()->after('auto_refunded');
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_unlocks', function (Blueprint $table) {
            $table->dropColumn(['student_viewed_at', 'auto_refunded', 'auto_refunded_at']);
        });

        Schema::table('student_requirements', function (Blueprint $table) {
            $table->dropIndex('sr_dynamic_price_idx');
            $table->dropIndex('sr_posted_at_idx');
            $table->dropColumn(['base_price', 'dynamic_price', 'demand_level', 'region_code',
                                'price_decayed', 'decay_checked_at', 'last_price_update']);
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->dropIndex('tutors_rank_idx');
            $table->dropIndex('tutors_monthly_bid_idx');
            $table->dropColumn(['monthly_bid', 'rank', 'early_access_minutes', 'rotation_order', 'rank_updated_at']);
        });
    }
};
