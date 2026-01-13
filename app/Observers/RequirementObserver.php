<?php

namespace App\Observers;

use App\Models\StudentRequirement;
use App\Jobs\IndexRequirementJob;
use App\Jobs\RemoveRequirementFromIndexJob;

class RequirementObserver
{
    /**
     * Handle the StudentRequirement "created" event.
     */
    public function created(StudentRequirement $requirement): void
    {
        // When a new requirement is created and status is open, index immediately
        if (in_array($requirement->status, ['open', 'active'])) {
            dispatch(new IndexRequirementJob($requirement->id));
        }
    }

    /**
     * Handle the StudentRequirement "updated" event.
     */
    public function updated(StudentRequirement $requirement): void
    {
        // Only react to status changes
        if ($requirement->isDirty('status')) {
            // If open/active, add/update in Elasticsearch
            if (in_array($requirement->status, ['open', 'active'])) {
                dispatch(new IndexRequirementJob($requirement->id));
            }

            // If closed/hired/cancelled, remove from Elasticsearch
            if (in_array($requirement->status, ['closed', 'hired', 'cancelled', 'expired'])) {
                dispatch(new RemoveRequirementFromIndexJob($requirement->id));
            }
        } elseif (in_array($requirement->status, ['open', 'active'])) {
            // If status is still open/active but other fields changed, re-index
            dispatch(new IndexRequirementJob($requirement->id));
        }
    }

    /**
     * Handle the StudentRequirement "deleted" event.
     */
    public function deleted(StudentRequirement $requirement): void
    {
        // Always remove from Elasticsearch when deleted
        dispatch(new RemoveRequirementFromIndexJob($requirement->id));
    }

    /**
     * Handle the StudentRequirement "restored" event.
     */
    public function restored(StudentRequirement $requirement): void
    {
        // If restored and status is open/active, re-index
        if (in_array($requirement->status, ['open', 'active'])) {
            dispatch(new IndexRequirementJob($requirement->id));
        }
    }
}
