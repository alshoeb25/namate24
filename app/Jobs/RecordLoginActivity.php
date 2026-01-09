<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class RecordLoginActivity implements ShouldQueue
{
    use Queueable;

    protected $userId;
    protected $ipAddress;
    protected $userAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $ipAddress, string $userAgent = null)
    {
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                \Log::warning('User not found for login activity recording', ['user_id' => $this->userId]);
                return;
            }

            // Detect country from IP
            $countryData = $this->detectCountry($this->ipAddress);

            // Create login activity record
            UserActivity::create([
                'user_id' => $this->userId,
                'ip_address' => $this->ipAddress,
                'country' => $countryData['country'],
                'country_iso' => $countryData['country_iso'],
                'login_time' => now(),
                'user_agent' => $this->userAgent,
            ]);

            // Update user's country if not already set
            if (empty($user->country_code) || empty($user->country) || empty($user->country_iso)) {
                $user->update([
                    'country_code' => $countryData['country_code'],
                    'country' => $countryData['country'],
                    'country_iso' => $countryData['country_iso'],
                ]);
            }

            \Log::info('User login activity recorded via job', [
                'user_id' => $this->userId,
                'ip' => $this->ipAddress,
                'country' => $countryData['country'],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to record login activity via job: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Detect country from IP address with full details
     */
    private function detectCountry(string $ip): array
    {
        $default = [
            'country_code' => 'IN',
            'country' => 'India',
            'country_iso' => 'IN',
        ];

        // Skip local/private IPs
        if ($this->isLocalIp($ip)) {
            return $default;
        }

        try {
            // Use ip-api.com for geolocation (free, no API key needed)
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode");
            
            if ($response) {
                $data = json_decode($response, true);
                
                if (isset($data['status']) && $data['status'] === 'success') {
                    $countryCode = $data['countryCode'] ?? null;
                    $country = $data['country'] ?? null;
                    
                    if ($countryCode && $country) {
                        return [
                            'country_code' => strtoupper($countryCode),
                            'country' => $country,
                            'country_iso' => strtoupper($countryCode),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Country detection failed in login job: ' . $e->getMessage());
        }

        return $default;
    }

    /**
     * Check if IP is local/private
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) 
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;    }
}