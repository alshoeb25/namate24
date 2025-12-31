<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tutor;
use App\Services\ElasticService;

class IndexTutorsElasticsearch extends Command
{
    protected $signature = 'elasticsearch:index-tutors {--id= : Index specific tutor by ID}';
    protected $description = 'Index tutors in Elasticsearch';

    protected $elasticService;

    public function __construct(ElasticService $elasticService)
    {
        parent::__construct();
        $this->elasticService = $elasticService;
    }

    public function handle()
    {
        $this->info('Indexing tutors in Elasticsearch...');
        
        $client = $this->elasticService->client();
        $indexName = 'tutors';

        if ($id = $this->option('id')) {
            // Index specific tutor
            $tutor = Tutor::find($id);
            if (!$tutor) {
                $this->error("Tutor with ID {$id} not found");
                return 1;
            }
            
            $this->indexTutor($client, $indexName, $tutor);
            $this->info("✅ Indexed tutor {$id}");
        } else {
            // Index all tutors
            $count = 0;
            $page = 1;
            $perPage = 100;

            while (true) {
                $tutors = Tutor::query()
                    ->with('user', 'subjects')
                    ->forPage($page, $perPage)
                    ->get();

                if ($tutors->isEmpty()) {
                    break;
                }

                foreach ($tutors as $tutor) {
                    try {
                        $this->indexTutor($client, $indexName, $tutor);
                        $count++;
                        $this->line("Indexed: {$count}");
                    } catch (\Exception $e) {
                        $this->error("Error indexing tutor {$tutor->id}: " . $e->getMessage());
                    }
                }

                $page++;
            }

            $this->info("✅ Successfully indexed {$count} tutors");
        }

        return 0;
    }

    protected function indexTutor($client, $indexName, Tutor $tutor): void
    {
        $client->index([
            'index' => $indexName,
            'id' => $tutor->id,
            'body' => $tutor->toElasticArray(),
            'refresh' => true
        ]);
    }
}
