<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutors')) {
            return;
        }

        Schema::table('tutors', function (Blueprint $table) {
            if (!Schema::hasColumn('tutors', 'charge_type')) {
                $table->string('charge_type', 20)->nullable()->after('price_per_hour');
            }
            if (!Schema::hasColumn('tutors', 'min_fee')) {
                $table->decimal('min_fee', 10, 2)->nullable()->after('charge_type');
            }
            if (!Schema::hasColumn('tutors', 'max_fee')) {
                $table->decimal('max_fee', 10, 2)->nullable()->after('min_fee');
            }
            if (!Schema::hasColumn('tutors', 'fee_notes')) {
                $table->text('fee_notes')->nullable()->after('max_fee');
            }

            if (!Schema::hasColumn('tutors', 'experience_total_years')) {
                $table->unsignedSmallInteger('experience_total_years')->nullable()->after('experience_years');
            }
            if (!Schema::hasColumn('tutors', 'experience_teaching_years')) {
                $table->unsignedSmallInteger('experience_teaching_years')->nullable()->after('experience_total_years');
            }
            if (!Schema::hasColumn('tutors', 'experience_online_years')) {
                $table->unsignedSmallInteger('experience_online_years')->nullable()->after('experience_teaching_years');
            }

            if (!Schema::hasColumn('tutors', 'travel_willing')) {
                $table->boolean('travel_willing')->default(false)->after('teaching_mode');
            }
            if (!Schema::hasColumn('tutors', 'travel_distance_km')) {
                $table->unsignedSmallInteger('travel_distance_km')->nullable()->after('travel_willing');
            }
            if (!Schema::hasColumn('tutors', 'online_available')) {
                $table->boolean('online_available')->default(true)->after('travel_distance_km');
            }
            if (!Schema::hasColumn('tutors', 'has_digital_pen')) {
                $table->boolean('has_digital_pen')->default(false)->after('online_available');
            }
            if (!Schema::hasColumn('tutors', 'helps_homework')) {
                $table->boolean('helps_homework')->default(false)->after('has_digital_pen');
            }
            if (!Schema::hasColumn('tutors', 'employed_full_time')) {
                $table->boolean('employed_full_time')->default(false)->after('helps_homework');
            }

            if (!Schema::hasColumn('tutors', 'opportunities')) {
                $table->json('opportunities')->nullable()->after('employed_full_time');
            }
            if (!Schema::hasColumn('tutors', 'languages')) {
                $table->json('languages')->nullable()->after('opportunities');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tutors')) {
            return;
        }

        Schema::table('tutors', function (Blueprint $table) {
            $columns = [
                'charge_type','min_fee','max_fee','fee_notes',
                'experience_total_years','experience_teaching_years','experience_online_years',
                'travel_willing','travel_distance_km','online_available','has_digital_pen','helps_homework','employed_full_time',
                'opportunities','languages'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tutors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
