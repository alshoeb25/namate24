<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add default contact unlock coins setting if not exists
        $exists = DB::table('admin_settings')->where('key', 'contact_unlock_coins')->exists();

        if (!$exists) {
            DB::table('admin_settings')->insert([
                'key' => 'contact_unlock_coins',
                'value' => '40',
                'type' => 'integer',
                'description' => 'Coins required to unlock tutor contact details',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('admin_settings')->where('key', 'contact_unlock_coins')->delete();
    }
};
