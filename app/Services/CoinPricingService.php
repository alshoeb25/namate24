<?php

namespace App\Services;

use App\Models\User;
use App\Models\CoinPackage;

class CoinPricingService
{
    // Base price: 1.25 USD for 100 coins
    const BASE_USD_PRICE_PER_100_COINS = 1.25;
    
    // GST rate for India
    const GST_RATE_INDIA = 0.18; // 18%
    
    // Countries that use Indian pricing (add more as needed)
    const INDIA_COUNTRIES = ['IN', 'India'];
    
    // USD to INR conversion rate (can be updated via API or config)
    const USD_TO_INR_RATE = 83.5;

    /**
     * Determine if user is from India
     */
    public static function isIndiaUser(User $user): bool
    {
        if (!$user->country_iso) {
            return false;
        }
        
        return strtoupper($user->country_iso) === 'IN';
    }

    /**
     * Get pricing for a coin package based on user location
     * 
     * Returns array with pricing details
     */
    public static function calculatePackagePrice(CoinPackage $package, User $user): array
    {
        $isIndia = self::isIndiaUser($user);
        
        if ($isIndia) {
            return self::calculateIndianPrice($package);
        } else {
            return self::calculateInternationalPrice($package);
        }
    }

    /**
     * Calculate price for India (with GST, in INR)
     */
    public static function calculateIndianPrice(CoinPackage $package): array
    {
        $basePrice = (float) $package->price;
        $gstRate = (float) config('coins.gst_rate', self::GST_RATE_INDIA);
        
        $subtotal = $basePrice;
        $taxAmount = round($subtotal * $gstRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);
        
        return [
            'is_india' => true,
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'subtotal' => $subtotal,
            'gst_rate' => $gstRate,
            'gst_percentage' => ($gstRate * 100) . '%',
            'tax_amount' => $taxAmount,
            'total' => $totalAmount,
            'display_price' => '₹' . number_format($totalAmount, 2),
            'description' => "₹{$totalAmount} (incl. {($gstRate * 100)}% GST)",
        ];
    }

    /**
     * Calculate price for international users (USD, no GST)
     */
    public static function calculateInternationalPrice(CoinPackage $package): array
    {
        // Calculate USD price based on coins
        $coinsAmount = $package->coins;
        $usdPrice = ($coinsAmount / 100) * self::BASE_USD_PRICE_PER_100_COINS;
        
        // Round to nearest sensible value
        $usdPrice = round($usdPrice, 2);
        
        return [
            'is_india' => false,
            'currency' => 'USD',
            'currency_symbol' => '$',
            'subtotal' => $usdPrice,
            'gst_rate' => 0,
            'gst_percentage' => '0%',
            'tax_amount' => 0,
            'total' => $usdPrice,
            'display_price' => '$' . number_format($usdPrice, 2),
            'description' => '$' . number_format($usdPrice, 2) . ' (no GST)',
        ];
    }

    /**
     * Get all packages with pricing for a specific user
     */
    public static function getPackagesWithPricing(User $user)
    {
        $packages = CoinPackage::active()->orderBy('sort_order')->get();
        
        return $packages->map(function (CoinPackage $package) use ($user) {
            $pricing = self::calculatePackagePrice($package, $user);
            
            return [
                'id' => $package->id,
                'name' => $package->name,
                'coins' => $package->coins,
                'bonus_coins' => $package->bonus_coins,
                'total_coins' => $package->total_coins,
                'description' => $package->description,
                'is_popular' => $package->is_popular,
                'original_price' => $package->price,
                'pricing' => $pricing,
                'price' => $pricing['total'],
                'currency' => $pricing['currency'],
                'display_price' => $pricing['display_price'],
            ];
        })->all();
    }

    /**
     * Convert INR to USD
     */
    public static function inrToUsd($inrAmount): float
    {
        return round($inrAmount / self::USD_TO_INR_RATE, 2);
    }

    /**
     * Convert USD to INR
     */
    public static function usdToInr($usdAmount): float
    {
        return round($usdAmount * self::USD_TO_INR_RATE, 2);
    }

    /**
     * Get Razorpay amount (in paisa/cents)
     */
    public static function getRazorpayAmount(float $amount, string $currency = 'INR'): int
    {
        if ($currency === 'USD') {
            // Convert USD to INR for Razorpay (uses INR)
            $amount = self::usdToInr($amount);
        }
        
        // Razorpay uses smallest currency unit (paisa for INR, cent for USD)
        return (int) ($amount * 100);
    }

    /**
     * Get coin cost based on user nationality and operation
     * 
     * @param User $user
     * @param string $operation 'post_requirement', 'unlock_tutor', 'contact_unlock'
     * @return int
     */
    public static function getCoinCost(User $user, string $operation): int
    {
        $isIndia = self::isIndiaUser($user);
        
        return match($operation) {
            'post_requirement' => $isIndia 
                ? config('enquiry.pricing_by_nationality.post.indian', 0)
                : config('enquiry.pricing_by_nationality.post.non_indian', 0),
            
            'unlock_tutor' => $isIndia
                ? config('enquiry.pricing_by_nationality.unlock.indian', 49)
                : config('enquiry.pricing_by_nationality.unlock.non_indian', 99),
            
            'contact_unlock' => $isIndia
                ? config('coins.pricing_by_nationality.contact_unlock.indian', 49)
                : config('coins.pricing_by_nationality.contact_unlock.non_indian', 99),
            
            'approach_tutor' => $isIndia
                ? config('coins.pricing_by_nationality.approach_tutor.indian', 49)
                : config('coins.pricing_by_nationality.approach_tutor.non_indian', 99),
            
            default => 0,
        };
    }

    /**
     * Get nationality information for user
     */
    public static function getNationalityInfo(User $user): array
    {
        $isIndia = self::isIndiaUser($user);
        
        return [
            'is_indian' => $isIndia,
            'nationality' => $isIndia ? 'Indian' : 'Non-Indian',
            'country_code' => $user->country_iso ?? 'Unknown',
        ];
    }
}
