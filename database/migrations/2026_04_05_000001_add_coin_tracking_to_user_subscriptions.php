<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Track coins spent from subscription (especially for BASIC plan limit of 49)
            $table->integer('coins_spent')->default(0)->after('views_used');
            
            // Track number of views that used coins (for BASIC plan limit of 2)
            $table->integer('views_with_coins')->default(0)->after('coins_spent');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['coins_spent', 'views_with_coins']);
        });
    }
};
