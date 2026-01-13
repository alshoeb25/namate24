<?php

namespace App\Jobs;

use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveRequirementFromIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $requirementId;
    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $requirementId)
    {
        $this->requirementId = $requirementId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Build Elasticsearch client
            $client = ClientBuilder::create()
                ->setHosts([config('elasticsearch.host', 'localhost:9200')])
                ->build();

            // Check if document exists
            $exists = $client->exists([
                'index' => 'requirements',
                'id' => $this->requirementId,
            ]);

            if (!$exists) {
                Log::info("Requirement {$this->requirementId} not found in Elasticsearch index. Skipping removal.");
                return;
            }

            // Delete the document
            $client->delete([
                'index' => 'requirements',
                'id' => $this->requirementId,
            ]);

            Log::info("Successfully removed requirement {$this->requirementId} from Elasticsearch");

        } catch (\Exception $e) {
            // If document doesn't exist, it's not an error
            if (strpos($e->getMessage(), 'not_found') !== false) {
                Log::info("Requirement {$this->requirementId} already removed from Elasticsearch");
                return;
            }

            Log::error("Failed to remove requirement {$this->requirementId}: {$e->getMessage()}");
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RemoveRequirementFromIndexJob failed permanently for requirement {$this->requirementId}: {$exception->getMessage()}");
    }
}
