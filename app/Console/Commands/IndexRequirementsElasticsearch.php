<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentRequirement;
use App\Services\ElasticService;

class IndexRequirementsElasticsearch extends Command
{
    protected $signature = 'elasticsearch:index-requirements {--id= : Index specific requirement by ID}';
    protected $description = 'Index student requirements/jobs in Elasticsearch';

    protected $elasticService;

    public function __construct(ElasticService $elasticService)
    {
        parent::__construct();
        $this->elasticService = $elasticService;
    }

    public function handle()
    {
        $this->info('Indexing student requirements in Elasticsearch...');
        
        $client = $this->elasticService->client();
        $indexName = 'requirements';

        if ($id = $this->option('id')) {
            // Index specific requirement
            $requirement = StudentRequirement::find($id);
            if (!$requirement) {
                $this->error("Requirement with ID {$id} not found");
                return 1;
            }
            
            $this->indexRequirement($client, $indexName, $requirement);
            $this->info("✅ Indexed requirement {$id}");
        } else {
            // Index all visible requirements
            $count = 0;
            $page = 1;
            $perPage = 100;

            while (true) {
                $requirements = StudentRequirement::query()
                    ->with('student', 'subject', 'subjects')
                    ->where('visible', true)
                    ->whereIn('status', ['active', 'posted', 'open'])
                    ->forPage($page, $perPage)
                    ->get();

                if ($requirements->isEmpty()) {
                    break;
                }

                foreach ($requirements as $requirement) {
                    try {
                        $this->indexRequirement($client, $indexName, $requirement);
                        $count++;
                        $this->line("Indexed: {$count}");
                    } catch (\Exception $e) {
                        $this->error("Error indexing requirement {$requirement->id}: " . $e->getMessage());
                    }
                }

                $page++;
            }

            $this->info("✅ Successfully indexed {$count} student requirements");
        }

        return 0;
    }

    protected function indexRequirement($client, $indexName, StudentRequirement $requirement): void
    {
        $client->index([
            'index' => $indexName,
            'id' => $requirement->id,
            'body' => $requirement->toElasticArray(),
            'refresh' => true
        ]);
    }
}
