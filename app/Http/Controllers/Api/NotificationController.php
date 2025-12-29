<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List current user's notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = $user->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => class_basename($n->type),
                    'data' => $n->data,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Mark all unread notifications as read
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Optionally mark specific IDs if provided
        $ids = (array) ($request->input('ids') ?? []);
        if (!empty($ids)) {
            $user->notifications()
                ->whereIn('id', $ids)
                ->get()
                ->each(function ($n) {
                    if (!$n->read_at) {
                        $n->markAsRead();
                    }
                });
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a single notification as read
     */
    public function markRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
