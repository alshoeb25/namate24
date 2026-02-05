<?php

namespace App\Observers;

use App\Models\Tutor;
use App\Jobs\IndexTutorJob;
use App\Jobs\RemoveTutorFromIndexJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TutorObserver
{
    /**
     * Handle the Tutor "created" event.
     */
    public function created(Tutor $tutor): void
    {
        Log::info("TutorObserver: created event for tutor ID {$tutor->id}, status: {$tutor->moderation_status}");
        // When a new tutor is created and already approved, index immediately
        if ($tutor->moderation_status === 'approved' && !$tutor->is_disabled) {
            dispatch(new IndexTutorJob($tutor->id));
            $this->forgetLatestTutorsCache();
        }
    }

    /**
     * Handle the Tutor "updated" event.
     */
    public function updated(Tutor $tutor): void
    {
        Log::info("TutorObserver: updated event for tutor ID {$tutor->id}, status: {$tutor->moderation_status}");

        // Remove from index when disabled
        if ($tutor->isDirty('is_disabled') && $tutor->is_disabled) {
            dispatch(new RemoveTutorFromIndexJob($tutor->id));
            return;
        }

        // Re-index when enabled
        if ($tutor->isDirty('is_disabled') && !$tutor->is_disabled) {
            if ($tutor->moderation_status === 'approved') {
                dispatch(new IndexTutorJob($tutor->id));
            }
        }

        // Only react to moderation status changes
        if ($tutor->isDirty('moderation_status')) {
            // If approved, add/update in Elasticsearch
            if ($tutor->moderation_status === 'approved' && !$tutor->is_disabled) {
                dispatch(new IndexTutorJob($tutor->id));
                $this->forgetLatestTutorsCache();
            }

            // If rejected, remove from Elasticsearch
            if (in_array($tutor->moderation_status, ['rejected'])) {
                dispatch(new RemoveTutorFromIndexJob($tutor->id));
                $this->forgetLatestTutorsCache();
            }
        } elseif ($tutor->moderation_status === 'approved' && !$tutor->is_disabled) {
            // If status is still approved but other fields changed, re-index
            dispatch(new IndexTutorJob($tutor->id));
        }
        
        if ($tutor->isDirty('is_disabled')) {
            $this->forgetLatestTutorsCache();
        }
    }

    protected function forgetLatestTutorsCache(): void
    {
        Cache::forget('home.latest_tutors.6');
        Cache::forget('home.latest_tutors.9');
        Cache::forget('home.latest_tutors.12');
    }

    /**
     * Handle the Tutor "deleted" event.
     */
    public function deleted(Tutor $tutor): void
    {
        Log::info("TutorObserver: deleted event for tutor ID {$tutor->id}");
        // Always remove from Elasticsearch when deleted
        dispatch(new RemoveTutorFromIndexJob($tutor->id));
    }

    /**
     * Handle the Tutor "restored" event.
     */
    public function restored(Tutor $tutor): void
    {
        Log::info("TutorObserver: restored event for tutor ID {$tutor->id}, status: {$tutor->moderation_status}");
        // If restored and approved, re-index
        if ($tutor->moderation_status === 'approved' && !$tutor->is_disabled) {
            dispatch(new IndexTutorJob($tutor->id));
        }
    }
}
