<?php

namespace App\Observers;

use App\Models\Tutor;
use App\Jobs\IndexTutorJob;
use App\Jobs\RemoveTutorFromIndexJob;
use Illuminate\Support\Facades\Log;

class TutorObserver
{
    /**
     * Handle the Tutor "created" event.
     */
    public function created(Tutor $tutor): void
    {
        Log::info("TutorObserver: created event for tutor ID {$tutor->id}, status: {$tutor->status}");
        // When a new tutor is created and already approved, index immediately
        if ($tutor->status === 'approved') {
            dispatch(new IndexTutorJob($tutor->id));
        }
    }

    /**
     * Handle the Tutor "updated" event.
     */
    public function updated(Tutor $tutor): void
    {
        Log::info("TutorObserver: updated event for tutor ID {$tutor->id}, status: {$tutor->status}");
        // Only react to status changes
        if ($tutor->isDirty('status')) {
            // If approved, add/update in Elasticsearch
            if ($tutor->status === 'approved') {
                dispatch(new IndexTutorJob($tutor->id));
            }

            // If blocked or rejected, remove from Elasticsearch
            if (in_array($tutor->status, ['blocked', 'rejected'])) {
                dispatch(new RemoveTutorFromIndexJob($tutor->id));
            }
        } elseif ($tutor->status === 'approved') {
            // If status is still approved but other fields changed, re-index
            dispatch(new IndexTutorJob($tutor->id));
        }
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
        Log::info("TutorObserver: restored event for tutor ID {$tutor->id}, status: {$tutor->status}");
        // If restored and approved, re-index
        if ($tutor->status === 'approved') {
            dispatch(new IndexTutorJob($tutor->id));
        }
    }
}
