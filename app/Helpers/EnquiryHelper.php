<?php

namespace App\Helpers;

use App\Models\StudentRequirement;

/**
 * EnquiryHelper - Fee and status management utilities
 * 
 * HYBRID ARCHITECTURE PATTERN:
 * - Config stores current default fees (for new enquiries)
 * - Database stores historical snapshots (what was actually charged)
 * - This preserves historical accuracy even when fees change
 */
class EnquiryHelper
{
    /**
     * Get the effective post fee for a new enquiry
     * Uses current config value
     * 
     * @return int
     */
    public static function getPostFee(): int
    {
        return (int) config('enquiry.post_fee', 10);
    }

    /**
     * Get the effective unlock fee for a new enquiry unlock
     * Uses current config value
     * 
     * @return int
     */
    public static function getUnlockFee(): int
    {
        return (int) config('enquiry.unlock_fee', 10);
    }

    /**
     * Get historical fee (what was actually charged for this enquiry)
     * 
     * @param StudentRequirement $enquiry
     * @return int
     */
    public static function getHistoricalPostFee(StudentRequirement $enquiry): int
    {
        return (int) ($enquiry->post_fee ?? self::getPostFee());
    }

    /**
     * Get historical unlock fee (what was charged when tutor unlocked)
     * 
     * @param StudentRequirement $enquiry
     * @return int
     */
    public static function getHistoricalUnlockFee(StudentRequirement $enquiry): int
    {
        return (int) ($enquiry->unlock_price ?? self::getUnlockFee());
    }

    /**
     * Get all valid enquiry statuses
     * 
     * @return array [status_key => description]
     */
    public static function getStatuses(): array
    {
        return config('enquiry.statuses', [
            'active'    => 'Active',
            'paused'    => 'Paused',
            'closed'    => 'Closed',
            'cancelled' => 'Cancelled',
            'expired'   => 'Expired',
        ]);
    }

    /**
     * Check if status is valid
     * 
     * @param string $status
     * @return bool
     */
    public static function isValidStatus(string $status): bool
    {
        return isset(self::getStatuses()[$status]);
    }

    /**
     * Get all valid lead statuses
     * 
     * @return array [status_key => description]
     */
    public static function getLeadStatuses(): array
    {
        return config('enquiry.lead_statuses', [
            'open'       => 'Open',
            'unlocked'   => 'Unlocked',
            'responded'  => 'Responded',
            'hired'      => 'Hired',
            'rejected'   => 'Rejected',
        ]);
    }

    /**
     * Check if lead status is valid
     * 
     * @param string $status
     * @return bool
     */
    public static function isValidLeadStatus(string $status): bool
    {
        return isset(self::getLeadStatuses()[$status]);
    }

    /**
     * Get max leads allowed per enquiry
     * 
     * @return int
     */
    public static function getMaxLeads(): int
    {
        return (int) config('enquiry.max_leads', 5);
    }

    /**
     * Calculate refund if enquiry auto-closes with no leads
     * 
     * @param StudentRequirement $enquiry
     * @return int Refund amount
     */
    public static function calculateAutoCloseRefund(StudentRequirement $enquiry): int
    {
        if (!$enquiry->post_fee) {
            return 0;
        }

        $refundPercentage = (int) config('enquiry.fees.refund_percentage', 50);
        return (int) ($enquiry->post_fee * $refundPercentage / 100);
    }

    /**
     * Check if enquiry should auto-close (no unlocks in X days)
     * 
     * @param StudentRequirement $enquiry
     * @return bool
     */
    public static function shouldAutoClose(StudentRequirement $enquiry): bool
    {
        if ($enquiry->status === 'closed' || $enquiry->status === 'cancelled') {
            return false; // Already closed
        }

        $autoCloseDays = (int) config('enquiry.lifecycle.auto_close_days', 30);
        
        if ($autoCloseDays === 0) {
            return false; // Feature disabled
        }

        if (!$enquiry->posted_at) {
            return false;
        }

        $daysOld = now()->diffInDays($enquiry->posted_at);
        
        // Auto-close only if no unlocks in the period
        return $daysOld >= $autoCloseDays && $enquiry->current_leads === 0;
    }

    /**
     * Format fee information for display
     * Shows both historical and current defaults for comparison
     * 
     * @param StudentRequirement $enquiry
     * @return array
     */
    public static function formatFeeInfo(StudentRequirement $enquiry): array
    {
        return [
            'post_fee_charged' => self::getHistoricalPostFee($enquiry),
            'post_fee_current' => self::getPostFee(),
            'unlock_fee_charged' => self::getHistoricalUnlockFee($enquiry),
            'unlock_fee_current' => self::getUnlockFee(),
            'max_leads' => self::getMaxLeads(),
            'leads_remaining' => max(0, self::getMaxLeads() - ($enquiry->current_leads ?? 0)),
        ];
    }
}
