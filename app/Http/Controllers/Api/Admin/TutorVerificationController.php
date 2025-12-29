<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\AdminActionLog;
use App\Models\User;

class TutorVerificationController extends Controller
{
    protected function key(int $userId): string
    {
        return "user:{$userId}:verification";
    }

    public function status(User $user)
    {
        $key = $this->key($user->id);
        $data = Redis::hgetall($key);

        $status = [
            'email' => $data['email'] ?? 'unknown',
            'photo' => $data['photo'] ?? 'unknown',
            'docs' => $data['docs'] ?? 'unknown',
            'updated_at' => $data['updated_at'] ?? null,
        ];

        return response()->json($status);
    }

    public function emailVerify(Request $request, User $user)
    {
        $admin = $request->user();
        $key = $this->key($user->id);
        Redis::hmset($key, 'email', 'verified', 'updated_at', now()->toISOString());

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'profile_email_verified',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata' => [],
        ]);
        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'profile_email_verified',
            'subject' => ['type' => 'User', 'id' => $user->id],
        ]));

        return response()->json(['success' => true]);
    }

    public function emailUnverify(Request $request, User $user)
    {
        $admin = $request->user();
        $key = $this->key($user->id);
        Redis::hmset($key, 'email', 'unverified', 'updated_at', now()->toISOString());

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'profile_email_unverified',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata' => [],
        ]);
        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'profile_email_unverified',
            'subject' => ['type' => 'User', 'id' => $user->id],
        ]));

        return response()->json(['success' => true]);
    }

    public function photoVerify(Request $request, User $user)
    {
        $admin = $request->user();
        $key = $this->key($user->id);
        Redis::hmset($key, 'photo', 'verified', 'updated_at', now()->toISOString());

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'profile_photo_verified',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata' => [],
        ]);
        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'profile_photo_verified',
            'subject' => ['type' => 'User', 'id' => $user->id],
        ]));

        return response()->json(['success' => true]);
    }

    public function photoUnverify(Request $request, User $user)
    {
        $admin = $request->user();
        $key = $this->key($user->id);
        Redis::hmset($key, 'photo', 'unverified', 'updated_at', now()->toISOString());

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'profile_photo_unverified',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata' => [],
        ]);
        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'profile_photo_unverified',
            'subject' => ['type' => 'User', 'id' => $user->id],
        ]));

        return response()->json(['success' => true]);
    }

    public function photoDelete(Request $request, User $user)
    {
        $admin = $request->user();
        $key = $this->key($user->id);
        Redis::hmset($key, 'photo', 'deleted', 'updated_at', now()->toISOString());

        AdminActionLog::create([
            'admin_id' => $admin->id,
            'action_type' => 'profile_photo_delete',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'metadata' => [],
        ]);
        Redis::rpush('admin:actions', json_encode([
            'ts' => now()->toISOString(),
            'admin_id' => $admin->id,
            'type' => 'profile_photo_delete',
            'subject' => ['type' => 'User', 'id' => $user->id],
        ]));

        return response()->json(['success' => true]);
    }
}
