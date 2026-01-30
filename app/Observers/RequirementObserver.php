<?php

namespace App\Observers;

use App\Models\StudentRequirement;
use App\Jobs\IndexRequirementJob;
use App\Jobs\RemoveRequirementFromIndexJob;
use Illuminate\Support\Facades\Log;

class RequirementObserver
{
    /**
     * Handle the StudentRequirement "created" event.
     */
    public function created(StudentRequirement $requirement): void
    {
        Log::info("RequirementObserver: created event for requirement ID {$requirement->id}, status: {$requirement->status}");
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
        Log::info("RequirementObserver: updated event for requirement ID {$requirement->id}, status: {$requirement->status}");
        // Only react to status changes
        if ($requirement->isDirty('status')) {
            // If open/active, add/update in Elasticsearch
            if (in_array($requirement->status, ['open', 'active'])) {
                dispatch(new IndexRequirementJob($requirement->id));
            }

            // If closed/approached/cancelled, remove from Elasticsearch
            if (in_array($requirement->status, ['closed', 'approached', 'cancelled', 'expired'])) {
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
        Log::info("RequirementObserver: deleted event for requirement ID {$requirement->id}");
        // Always remove from Elasticsearch when deleted
        dispatch(new RemoveRequirementFromIndexJob($requirement->id));
    }

    /**
     * Handle the StudentRequirement "restored" event.
     */
    public function restored(StudentRequirement $requirement): void
    {
        Log::info("RequirementObserver: restored event for requirement ID {$requirement->id}, status: {$requirement->status}");
        // If restored and status is open/active, re-index
        if (in_array($requirement->status, ['open', 'active'])) {
            dispatch(new IndexRequirementJob($requirement->id));
        }
    }
}
