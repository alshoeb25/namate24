<?php

namespace App\Helpers;

use App\Models\AdminSetting;

class CommonHelper
{
    /**
     * Get coins required to unlock tutor contact details.
     * Falls back to config if admin setting not found.
     */
    public static function getContactUnlockCoins(): int
    {
        return (int) AdminSetting::get('contact_unlock_coins', config('coins.contact_unlock', 50));
    }
}
