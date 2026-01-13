<?php

namespace App\Observers;

use App\Models\Tutor;
use App\Jobs\IndexTutorJob;
use App\Jobs\RemoveTutorFromIndexJob;

class TutorObserver
{
    /**
     * Handle the Tutor "created" event.
     */
    public function created(Tutor $tutor): void
    {
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
        // Only react to status changes
        if ($tutor->isDirty('status')) {
            // If approved, add/update in Elasticsearch
            if ($tutor->status === 'approved') {
                dispatch(new IndexTutorJob($tutor->id));
            }

            // If blocked or rejected, remove from Elasticsearch
            if (in_array($tutor->status, ['blocked', 'rejected', 'pending'])) {
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
        // Always remove from Elasticsearch when deleted
        dispatch(new RemoveTutorFromIndexJob($tutor->id));
    }

    /**
     * Handle the Tutor "restored" event.
     */
    public function restored(Tutor $tutor): void
    {
        // If restored and approved, re-index
        if ($tutor->status === 'approved') {
            dispatch(new IndexTutorJob($tutor->id));
        }
    }
}
