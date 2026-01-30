<?php

namespace App\Jobs;

use App\Models\AdminActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdminLogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $adminId,
        public string $action,
        public string $targetType,
        public int $targetId,
        public ?string $notes = null,
        public ?array $metadata = null,
    ) {
    }

    public function handle(): void
    {
        AdminActivityLog::create([
            'admin_id' => $this->adminId,
            'action' => $this->action,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
        ]);
    }
}
