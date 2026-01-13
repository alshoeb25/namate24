<?php

namespace App\Jobs;

use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveTutorFromIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tutorId;
    public $tries = 3;
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $tutorId)
    {
        $this->tutorId = $tutorId;
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
                'index' => 'tutors',
                'id' => $this->tutorId,
            ]);

            if (!$exists) {
                Log::info("Tutor {$this->tutorId} not found in Elasticsearch index. Skipping removal.");
                return;
            }

            // Delete the document
            $client->delete([
                'index' => 'tutors',
                'id' => $this->tutorId,
            ]);

            Log::info("Successfully removed tutor {$this->tutorId} from Elasticsearch");

        } catch (\Exception $e) {
            // If document doesn't exist, it's not an error
            if (strpos($e->getMessage(), 'not_found') !== false) {
                Log::info("Tutor {$this->tutorId} already removed from Elasticsearch");
                return;
            }

            Log::error("Failed to remove tutor {$this->tutorId}: {$e->getMessage()}");
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("RemoveTutorFromIndexJob failed permanently for tutor {$this->tutorId}: {$exception->getMessage()}");
    }
}
