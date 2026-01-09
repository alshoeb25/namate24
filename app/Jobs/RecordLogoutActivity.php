<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\UserActivity;

class RecordLogoutActivity implements ShouldQueue
{
    use Queueable;

    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);
            if (!$user) {
                \Log::warning('User not found for logout activity recording', ['user_id' => $this->userId]);
                return;
            }

            // Find the most recent active session and mark as logged out
            $activity = UserActivity::where('user_id', $this->userId)
                ->whereNull('logout_time')
                ->latest('login_time')
                ->first();
            
            if ($activity) {
                $activity->update([
                    'logout_time' => now(),
                ]);

                // Calculate session duration
                $duration = $activity->logout_time->diffInMinutes($activity->login_time);

                \Log::info('User logout activity recorded via job', [
                    'user_id' => $this->userId,
                    'session_id' => $activity->id,
                    'duration_minutes' => $duration,
                    'ip' => $activity->ip_address,
                    'country' => $activity->country,
                ]);
            } else {
                \Log::warning('No active session found for logout', ['user_id' => $this->userId]);
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to record logout activity via job: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'exception' => $e,
            ]);
        }    }
}