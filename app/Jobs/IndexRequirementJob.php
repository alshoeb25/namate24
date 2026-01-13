<?php

namespace App\Jobs;

use App\Models\StudentRequirement;
use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexRequirementJob implements ShouldQueue
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
            $requirement = StudentRequirement::with(['student', 'subject'])->find($this->requirementId);

            // If requirement not found or not open/active, skip indexing
            if (!$requirement || !in_array($requirement->status, ['open', 'active'])) {
                Log::info("Requirement {$this->requirementId} not found or not open/active. Skipping index.");
                return;
            }

            // Build Elasticsearch client
            $client = ClientBuilder::create()
                ->setHosts([config('elasticsearch.host', 'localhost:9200')])
                ->build();

            // Prepare document for indexing
            $params = [
                'index' => 'requirements',
                'id' => $requirement->id,
                'body' => $requirement->toElasticArray(),
            ];

            // Index the document
            $client->index($params);

            Log::info("Successfully indexed requirement {$this->requirementId} in Elasticsearch");

        } catch (\Exception $e) {
            Log::error("Failed to index requirement {$this->requirementId}: {$e->getMessage()}");
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("IndexRequirementJob failed permanently for requirement {$this->requirementId}: {$exception->getMessage()}");
    }
}
