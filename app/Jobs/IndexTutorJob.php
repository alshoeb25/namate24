<?php

namespace App\Jobs;

use App\Models\Tutor;
use Elasticsearch\ClientBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexTutorJob implements ShouldQueue
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
            $tutor = Tutor::with(['subjects', 'user'])->find($this->tutorId);

            // If tutor not found, not approved, or disabled, skip indexing
            if (!$tutor || $tutor->moderation_status !== 'approved' || $tutor->is_disabled || $tutor->user?->is_disabled) {
                Log::info("Tutor {$this->tutorId} not found, not approved, or disabled. Skipping index.");
                return;
            }

            // Build Elasticsearch client
            $client = ClientBuilder::create()
                ->setHosts([config('elasticsearch.host', 'localhost:9200')])
                ->build();

            // Prepare document for indexing
            $params = [
                'index' => 'tutors',
                'id' => $tutor->id,
                'body' => $tutor->toElasticArray(),
            ];

            // Index the document
            $client->index($params);

            Log::info("Successfully indexed tutor {$this->tutorId} in Elasticsearch");

        } catch (\Exception $e) {
            Log::error("Failed to index tutor {$this->tutorId}: {$e->getMessage()}");
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("IndexTutorJob failed permanently for tutor {$this->tutorId}: {$exception->getMessage()}");
    }
}
