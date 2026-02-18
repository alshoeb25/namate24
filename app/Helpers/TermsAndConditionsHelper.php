<?php

namespace App\Helpers;

class TermsAndConditionsHelper
{
    /**
     * Get Terms & Conditions for coin operations
     * Including nationality-based pricing terms
     */
    public static function getCoinsTermsAndConditions(string $operation, ?string $nationality = null): array
    {
        $baseTerms = [
            'version' => '1.0',
            'effective_date' => '2026-02-18',
            'last_updated' => now()->toDateTimeString(),
        ];

        $operationTerms = match($operation) {
            'post_requirement' => self::getPostRequirementTerms($nationality),
            'unlock_tutor' => self::getUnlockTutorTerms($nationality),
            'contact_unlock' => self::getContactUnlockTerms($nationality),
            default => [],
        };

        return array_merge($baseTerms, $operationTerms);
    }

    /**
     * Terms for posting requirements with nationality-based pricing
     */
    private static function getPostRequirementTerms(?string $nationality = null): array
    {
        $indianCost = config('enquiry.pricing_by_nationality.post.indian', 49);
        $nonIndianCost = config('enquiry.pricing_by_nationality.post.non_indian', 99);
        
        return [
            'operation' => 'post_requirement',
            'title' => 'Terms for Posting Requirements',
            'terms' => [
                'pricing' => [
                    'description' => 'Nationality-based coin pricing applies to all requirement postings.',
                    'indian_cost' => $indianCost . ' coins',
                    'non_indian_cost' => $nonIndianCost . ' coins',
                    'determination' => 'Your nationality is determined by your registered country code.',
                ],
                'free_posts' => [
                    'description' => 'The first 3 requirements posted by a student are free.',
                    'subsequent_posts' => 'All subsequent postings require coin payment based on your nationality.',
                ],
                'refund_policy' => [
                    'description' => 'Coins are non-refundable after posting, unless the requirement receives no tutor interest within 30 days.',
                    'auto_refund' => 'If no tutors unlock your requirement within 30 days, coins will be automatically refunded.',
                ],
                'restrictions' => [
                    'must_be_honest' => 'All requirement information must be accurate and honest.',
                    'no_spam' => 'Spam or fraudulent requirements will result in account suspension and coin forfeiture.',
                    'appropriate_content' => 'Requirements must comply with platform community guidelines.',
                ],
            ],
            'acknowledgment' => 'By posting a requirement, you acknowledge and accept all terms and conditions including the nationality-based pricing structure.'
        ];
    }

    /**
     * Terms for unlocking requirements (tutors) with nationality-based pricing
     */
    private static function getUnlockTutorTerms(?string $nationality = null): array
    {
        $indianCost = config('enquiry.pricing_by_nationality.unlock.indian', 199);
        $nonIndianCost = config('enquiry.pricing_by_nationality.unlock.non_indian', 399);
        
        return [
            'operation' => 'unlock_tutor',
            'title' => 'Terms for Unlocking Requirements',
            'terms' => [
                'pricing' => [
                    'description' => 'Nationality-based coin pricing applies to all requirement unlocks.',
                    'indian_cost' => $indianCost . ' coins',
                    'non_indian_cost' => $nonIndianCost . ' coins',
                    'determination' => 'Your nationality is determined by your registered country code.',
                ],
                'payment_timing' => [
                    'immediate' => 'Coins are deducted immediately upon unlocking.',
                    'non_refundable' => 'Coins cannot be refunded after unlock is completed.',
                ],
                'access_guarantee' => [
                    'description' => 'Unlocking provides access to student contact details only.',
                    'no_contact_guarantee' => 'The platform does not guarantee that the student will respond to your inquiries.',
                ],
                'responsible_use' => [
                    'contact_only' => 'Use contact details only for educational inquiries related to the requirement.',
                    'no_misuse' => 'Misuse of contact information may result in account suspension.',
                ],
            ],
            'acknowledgment' => 'By unlocking a requirement, you acknowledge and accept all terms including the nationality-based pricing structure.'
        ];
    }

    /**
     * Terms for unlocking tutor contact details with nationality-based pricing
     */
    private static function getContactUnlockTerms(?string $nationality = null): array
    {
        $indianCost = config('coins.pricing_by_nationality.contact_unlock.indian', 49);
        $nonIndianCost = config('coins.pricing_by_nationality.contact_unlock.non_indian', 99);
        
        return [
            'operation' => 'contact_unlock',
            'title' => 'Terms for Unlocking Tutor Contact Details',
            'terms' => [
                'pricing' => [
                    'description' => 'Nationality-based coin pricing applies to all contact unlocks.',
                    'indian_cost' => $indianCost . ' coins',
                    'non_indian_cost' => $nonIndianCost . ' coins',
                    'determination' => 'Your nationality is determined by your registered country code.',
                ],
                'one_time_charge' => [
                    'description' => 'Once contact is unlocked, coins are deducted and cannot be refunded.',
                    'multiple_unlocks' => 'You can unlock contact details for multiple tutors, each requiring the specified coin cost.',
                ],
                'contact_accuracy' => [
                    'provided_as_is' => 'Contact details are provided as-is and the platform is not responsible for accuracy.',
                    'verification' => 'Verify tutor credentials before making payment or arranging sessions.',
                ],
                'responsible_use' => [
                    'educational_only' => 'Contact information must be used only for educational purposes.',
                    'respect_privacy' => 'Respect the tutor\'s privacy and do not share their contact with others without permission.',
                    'no_spam' => 'Do not send spam, phishing, or malicious content.',
                ],
                'meeting_safety' => [
                    'public_places' => 'For first meetings, always meet in public places.',
                    'inform_others' => 'Share your meeting plans with family or friends for safety.',
                    'verify_identity' => 'Verify the tutor\'s identity before sharing personal information.',
                ],
            ],
            'acknowledgment' => 'By unlocking tutor contact details, you acknowledge and accept all terms including the nationality-based pricing structure and safety guidelines.'
        ];
    }

    /**
     * Validate that user has accepted T&C for the operation
     */
    public static function validateAcceptance(string $operation, bool $accepted): bool
    {
        if (!$accepted) {
            throw new \Exception(
                'You must accept the Terms & Conditions to proceed with this ' . 
                str_replace('_', ' ', $operation) . ' operation. The coin cost varies based on your nationality (Indian: lower rate, Non-Indian: higher rate).'
            );
        }

        return true;
    }

    /**
     * Get T&C display content for a specific operation
     */
    public static function getDisplayContent(string $operation, string $nationality = 'non_indian'): string
    {
        $terms = self::getCoinsTermsAndConditions($operation, $nationality);
        $content = "<strong>{$terms['title']}</strong>\n\n";
        
        if (isset($terms['terms'])) {
            foreach ($terms['terms'] as $section => $details) {
                $content .= "• " . ucfirst(str_replace('_', ' ', $section)) . "\n";
                
                if (is_array($details)) {
                    foreach ($details as $key => $value) {
                        if (is_array($value)) {
                            $content .= "  - " . implode(": ", $value) . "\n";
                        } else {
                            $content .= "  - " . ($value ?: $key) . "\n";
                        }
                    }
                }
                $content .= "\n";
            }
        }

        $content .= "\n" . ($terms['acknowledgment'] ?? '');
        
        return $content;
    }
}
