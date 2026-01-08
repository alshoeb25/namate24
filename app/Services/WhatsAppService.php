<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service for OTP and Messaging
 * 
 * Supports multiple providers:
 * - Twilio WhatsApp API
 * - MSG91 WhatsApp
 * - WhatsApp Business API
 */
class WhatsAppService
{
    private string $provider;
    private string $apiKey;
    private string $senderId;
    private string $apiUrl;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'twilio');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->senderId = config('services.whatsapp.sender_id');
        
        $this->apiUrl = match($this->provider) {
            'twilio' => 'https://api.twilio.com/2010-04-01',
            'msg91' => 'https://api.msg91.com/api/v5/whatsapp',
            'gupshup' => 'https://api.gupshup.io/sm/api/v1',
            default => config('services.whatsapp.api_url'),
        };
    }

    /**
     * Send WhatsApp OTP
     * 
     * @param string $phone Phone number with country code (e.g., +919876543210)
     * @param string $otp The OTP code
     * @return array Response with success status
     */
    public function sendOTP(string $phone, string $otp): array
    {
        $message = "Your Namate24 verification code is: *{$otp}*\n\nThis code expires in 10 minutes.\n\nDo not share this code with anyone.";
        
        return $this->sendMessage($phone, $message);
    }

    /**
     * Send WhatsApp message
     * 
     * @param string $phone Phone number with country code
     * @param string $message Message text
     * @param array $options Additional options (buttons, media, etc.)
     * @return array Response
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        try {
            return match($this->provider) {
                'twilio' => $this->sendViaTwilio($phone, $message, $options),
                'msg91' => $this->sendViaMSG91($phone, $message, $options),
                'gupshup' => $this->sendViaGupshup($phone, $message, $options),
                default => $this->sendViaCustom($phone, $message, $options),
            };
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'provider' => $this->provider,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Twilio WhatsApp API
     */
    private function sendViaTwilio(string $phone, string $message, array $options): array
    {
        $accountSid = config('services.whatsapp.twilio.account_sid');
        $authToken = config('services.whatsapp.twilio.auth_token');
        $from = config('services.whatsapp.twilio.from'); // whatsapp:+14155238886
        
        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("{$this->apiUrl}/Accounts/{$accountSid}/Messages.json", [
                'From' => $from,
                'To' => 'whatsapp:' . $phone,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('sid'),
                'provider' => 'twilio',
            ];
        }

        throw new \Exception($response->json('message', 'Failed to send WhatsApp message'));
    }

    /**
     * Send via MSG91 WhatsApp API
     */
    private function sendViaMSG91(string $phone, string $message, array $options): array
    {
        $response = Http::withHeaders([
            'authkey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/sendText", [
            'phone' => ltrim($phone, '+'),
            'message' => $message,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('request_id'),
                'provider' => 'msg91',
            ];
        }

        throw new \Exception($response->json('message', 'Failed to send WhatsApp message'));
    }

    /**
     * Send via Gupshup WhatsApp API
     */
    private function sendViaGupshup(string $phone, string $message, array $options): array
    {
        $response = Http::asForm()->post("{$this->apiUrl}/msg", [
            'userid' => config('services.whatsapp.gupshup.user_id'),
            'password' => config('services.whatsapp.gupshup.password'),
            'send_to' => $phone,
            'msg' => $message,
            'msg_type' => 'TEXT',
            'method' => 'sendMessage',
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->body(),
                'provider' => 'gupshup',
            ];
        }

        throw new \Exception('Failed to send WhatsApp message via Gupshup');
    }

    /**
     * Send via custom WhatsApp provider
     */
    private function sendViaCustom(string $phone, string $message, array $options): array
    {
        // Implement your custom WhatsApp provider here
        throw new \Exception('Custom WhatsApp provider not configured');
    }

    /**
     * Get WhatsApp chat link for a phone number
     * 
     * @param string $phone Phone with country code
     * @param string|null $prefilledMessage Optional prefilled message
     * @return string WhatsApp chat URL
     */
    public static function getChatLink(string $phone, ?string $prefilledMessage = null): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        $url = "https://wa.me/{$cleanPhone}";
        
        if ($prefilledMessage) {
            $url .= '?text=' . urlencode($prefilledMessage);
        }
        
        return $url;
    }

    /**
     * Get WhatsApp Business API link
     * 
     * @param string $phone Phone with country code
     * @param string|null $message Optional message
     * @return string WhatsApp API link
     */
    public static function getApiLink(string $phone, ?string $message = null): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        $url = "https://api.whatsapp.com/send?phone={$cleanPhone}";
        
        if ($message) {
            $url .= '&text=' . urlencode($message);
        }
        
        return $url;
    }

    /**
     * Format phone number for WhatsApp (remove +, spaces, etc.)
     */
    public static function formatPhone(string $phone): string
    {
        // Keep only numbers
        $clean = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure it starts with country code
        if (!str_starts_with($clean, '91') && strlen($clean) === 10) {
            $clean = '91' . $clean; // Add India country code
        }
        
        return '+' . $clean;
    }
}
