<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ElasticService;

class SetupElasticsearchIndices extends Command
{
    protected $signature = 'elasticsearch:setup';
    protected $description = 'Setup Elasticsearch indices with proper mappings for tutors and requirements';

    protected $elasticService;

    public function __construct(ElasticService $elasticService)
    {
        parent::__construct();
        $this->elasticService = $elasticService;
    }

    public function handle()
    {
        $this->info('Setting up Elasticsearch indices...');
        
        $this->setupTutorsIndex();
        $this->setupRequirementsIndex();
        
        $this->info('✅ Elasticsearch indices setup complete!');
    }

    protected function setupTutorsIndex()
    {
        $this->info('📚 Setting up Tutors index...');
        
        $client = $this->elasticService->client();
        $indexName = 'tutors';

        try {
            // Check if index exists
            if ($client->indices()->exists(['index' => $indexName])) {
                $this->warn("Index '$indexName' already exists. Deleting...");
                $client->indices()->delete(['index' => $indexName]);
            }

            // Create index with mappings
            $client->indices()->create([
                'index' => $indexName,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        'analysis' => [
                            'analyzer' => [
                                'standard_analyzer' => [
                                    'type' => 'standard',
                                    'stopwords' => '_english_'
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'name' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'headline' => ['type' => 'text'],
                            'subject_ids' => ['type' => 'keyword'],
                            'subjects' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'price_per_hour' => ['type' => 'float'],
                            'teaching_mode' => [
                                'type' => 'text',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            // Location fields
                            'city' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'state' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'area' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'address' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'country' => [
                                'type' => 'text',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'postal_code' => ['type' => 'keyword'],
                            // Geo-location for distance-based queries
                            'location' => ['type' => 'geo_point'],
                            'lat' => ['type' => 'float'],
                            'lng' => ['type' => 'float'],
                            // Other fields
                            'experience_years' => ['type' => 'integer'],
                            'experience_total_years' => ['type' => 'integer'],
                            'rating_avg' => ['type' => 'float'],
                            'verified' => ['type' => 'boolean'],
                            'gender' => [
                                'type' => 'text',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'badges' => ['type' => 'keyword'],
                            'online_available' => ['type' => 'boolean'],
                            'travel_willing' => ['type' => 'boolean'],
                            'travel_distance_km' => ['type' => 'float'],
                        ]
                    ]
                ]
            ]);

            $this->info("✅ Tutors index created successfully");
        } catch (\Exception $e) {
            $this->error("❌ Error creating Tutors index: " . $e->getMessage());
        }
    }

    protected function setupRequirementsIndex()
    {
        $this->info('🎓 Setting up Requirements index...');
        
        $client = $this->elasticService->client();
        $indexName = 'requirements';

        try {
            // Check if index exists
            if ($client->indices()->exists(['index' => $indexName])) {
                $this->warn("Index '$indexName' already exists. Deleting...");
                $client->indices()->delete(['index' => $indexName]);
            }

            // Create index with mappings
            $client->indices()->create([
                'index' => $indexName,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        'analysis' => [
                            'analyzer' => [
                                'standard_analyzer' => [
                                    'type' => 'standard',
                                    'stopwords' => '_english_'
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'student_id' => ['type' => 'keyword'],
                            'student_name' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'subject_id' => ['type' => 'keyword'],
                            'subjects' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'subject_name' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'budget_min' => ['type' => 'float'],
                            'budget_max' => ['type' => 'float'],
                            'budget' => ['type' => 'float'],
                            'budget_type' => ['type' => 'keyword'],
                            'mode' => [
                                'type' => 'text',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'service_type' => ['type' => 'keyword'],
                            // Location fields
                            'city' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'state' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer',
                                'fields' => ['keyword' => ['type' => 'keyword']]
                            ],
                            'area' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'location' => [
                                'type' => 'text',
                                'analyzer' => 'standard_analyzer'
                            ],
                            'pincode' => ['type' => 'keyword'],
                            // Geo-location for distance-based queries
                            'location_geo' => ['type' => 'geo_point'],
                            'lat' => ['type' => 'float'],
                            'lng' => ['type' => 'float'],
                            // Other fields
                            'details' => ['type' => 'text'],
                            'gender_preference' => ['type' => 'keyword'],
                            'level' => ['type' => 'keyword'],
                            'availability' => ['type' => 'text'],
                            'languages' => ['type' => 'keyword'],
                            'meeting_options' => ['type' => 'keyword'],
                            'tutor_location_preference' => ['type' => 'keyword'],
                            'travel_distance' => ['type' => 'float'],
                            'visible' => ['type' => 'boolean'],
                            'status' => ['type' => 'keyword'],
                            'lead_status' => ['type' => 'keyword'],
                            'posted_at' => ['type' => 'date'],
                            'created_at' => ['type' => 'date'],
                        ]
                    ]
                ]
            ]);

            $this->info("✅ Requirements index created successfully");
        } catch (\Exception $e) {
            $this->error("❌ Error creating Requirements index: " . $e->getMessage());
        }
    }
}
