<?php

namespace App\Services;

use App\Models\StudentRequirement;
use App\Services\ElasticService;
use Illuminate\Pagination\LengthAwarePaginator;

class RequirementSearchService
{
    protected $elastic;

    public function __construct(ElasticService $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * Search Student Requirements via Elasticsearch
     * Used by tutors to find job opportunities
     */
    public function search(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = $filters['q'] ?? '';

        $must = [];
        $should = [];

        // Visible requirements only
        $must[] = ['term' => ['visible' => true]];
        
        // Active/posted requirements only
        $must[] = ['terms' => ['status' => ['active', 'posted', 'open']]];

        // Exclude disabled students/users
        $must[] = ['term' => ['student_is_disabled' => false]];
        $must[] = ['term' => ['student_user_is_disabled' => false]];

        // 🔍 Main full-text search
        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['subject_name', 'subjects', 'details', 'city', 'area', 'location'],
                    'type' => 'best_fields'
                ]
            ];
        }

        // Subject-based filtering (supports multiple subjects)
        $subjectIds = [];
        if (!empty($filters['subject_ids'])) {
            $subjectIds = is_array($filters['subject_ids'])
                ? $filters['subject_ids']
                : array_filter(explode(',', $filters['subject_ids']));
        }

        if (!empty($filters['subject_id'])) {
            $subjectIds[] = $filters['subject_id'];
        }

        $subjectIds = collect($subjectIds)
            ->map(fn($id) => intval($id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($subjectIds)) {
            $must[] = [
                'bool' => [
                    'should' => [
                        ['terms' => ['subject_ids' => $subjectIds]],
                        ['terms' => ['subject_id' => $subjectIds]],
                    ],
                    'minimum_should_match' => 1,
                ]
            ];
        }

        if (!empty($filters['subject'])) {
            $should[] = ['match' => ['subjects' => $filters['subject']]];
        }

        // Mode filtering (online/offline/both)
        if (!empty($filters['mode'])) {
            $should[] = ['match' => ['mode.keyword' => $filters['mode']]];
        }

        // Budget filtering
        if (!empty($filters['budget_min']) || !empty($filters['budget_max'])) {
            $min = $filters['budget_min'] ?? 0;
            $max = $filters['budget_max'] ?? 999999;

            $must[] = [
                'bool' => [
                    'should' => [
                        [
                            'range' => [
                                'budget_max' => ['gte' => floatval($min)]
                            ]
                        ],
                        [
                            'range' => [
                                'budget_min' => ['lte' => floatval($max)]
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Gender preference
        if (!empty($filters['gender_preference'])) {
            $should[] = ['term' => ['gender_preference.keyword' => $filters['gender_preference']]];
        }

        // Level
        if (!empty($filters['level'])) {
            $should[] = ['match' => ['level' => $filters['level']]];
        }

        // Language requirements
        if (!empty($filters['languages'])) {
            $languages = is_array($filters['languages']) ? $filters['languages'] : [$filters['languages']];
            $must[] = [
                'terms' => [
                    'languages' => $languages
                ]
            ];
        }

        // 📍 Location-based search with distance
        if (!empty($filters['location']) || (!empty($filters['lat']) && !empty($filters['lng']))) {
            $this->addLocationFilter($must, $filters);
        }

        // 📍 Nearby requirements search (within radius)
        if (!empty($filters['nearby']) && !empty($filters['lat']) && !empty($filters['lng'])) {
            $radius = $filters['radius'] ?? '10km'; // Default 10km for requirements
            $must[] = [
                'geo_distance' => [
                    'distance' => $radius,
                    'location_geo' => [
                        'lat' => floatval($filters['lat']),
                        'lon' => floatval($filters['lng']),
                    ]
                ]
            ];
        }

        // Pagination parameters for Elastic
        $from = ($page - 1) * $perPage;
        $size = $perPage;

        // Build query body
        $queryBody = [
            'query' => [
                'bool' => [
                    'must' => !empty($must) ? $must : [['match_all' => new \stdClass()]],
                ]
            ],
            'from' => $from,
            'size' => $size,
        ];

        // Add should conditions if any
        if (!empty($should)) {
            $queryBody['query']['bool']['should'] = $should;
            $queryBody['query']['bool']['minimum_should_match'] = 1;
        }

        // Add sorting
        if (!empty($filters['sort_by'])) {
            $queryBody['sort'] = $this->getSortOptions($filters['sort_by'], $filters);
        } else {
            // Default: Recent first, but prioritize distance if location search
            if (!empty($filters['nearby']) && !empty($filters['lat']) && !empty($filters['lng'])) {
                $queryBody['sort'] = [
                    [
                        '_geo_distance' => [
                            'location_geo' => [
                                'lat' => floatval($filters['lat']),
                                'lon' => floatval($filters['lng']),
                            ],
                            'order' => 'asc',
                            'unit' => 'km'
                        ]
                    ],
                    ['posted_at' => ['order' => 'desc']],
                ];
            } else {
                $queryBody['sort'] = [
                    ['posted_at' => ['order' => 'desc']],
                    ['created_at' => ['order' => 'desc']],
                ];
            }
        }

        // Add distance calculation for results if location search is active
        if (!empty($filters['lat']) && !empty($filters['lng'])) {
            $queryBody['script_fields'] = [
                'distance' => [
                    'script' => [
                        'lang' => 'painless',
                        'source' => "if (doc['location_geo'].size() == 0) { return null; } else { return doc['location_geo'].arcDistance(params.lat, params.lon) / 1000; }",
                        'params' => [
                            'lat' => floatval($filters['lat']),
                            'lon' => floatval($filters['lng'])
                        ]
                    ]
                ]
            ];
        }

        // 🔥 Elasticsearch Query
        try {
            $results = $this->elastic->client()->search([
                'index' => 'requirements',
                'body' => $queryBody
            ]);
        } catch (\Exception $e) {
            \Log::error('Elasticsearch search error', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'query' => json_encode($queryBody)
            ]);
            
            // Return empty results on error
            return new LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // Extract hits and merge distance if calculated
        $hits = collect($results['hits']['hits'])->map(function($hit) {
            $source = $hit['_source'];
            
            // Add calculated distance if available and valid
            if (isset($hit['fields']['distance'][0]) && $hit['fields']['distance'][0] !== null) {
                $distance = floatval($hit['fields']['distance'][0]);
                if ($distance >= 0) {
                    $source['distance'] = round($distance, 2);
                }
            }
            
            return $source;
        });

        // Total hits
        $total = $results['hits']['total']['value'] ?? 0;

        return new LengthAwarePaginator(
            $hits,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Search for nearby job requirements within a radius
     */
    public function searchNearby(float $latitude, float $longitude, array $filters = [], int $radius = 10, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $filters['lat'] = $latitude;
        $filters['lng'] = $longitude;
        $filters['nearby'] = true;
        $filters['radius'] = $radius . 'km';

        return $this->search($filters, $perPage, $page);
    }

    /**
     * Search for requirements by location name
     */
    public function searchByLocation(string $location, array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $filters['location'] = $location;
        return $this->search($filters, $perPage, $page);
    }

    /**
     * Get requirements matching a tutor's profile
     */
    public function getMatchingForTutor($tutor, array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        // Build filters based on tutor profile
        if ($tutor->subjects && $tutor->subjects->count() > 0) {
            $filters['subject_ids'] = $tutor->subjects->pluck('id')->toArray();
        }

        if ($tutor->lat && $tutor->lng) {
            $filters['lat'] = $tutor->lat;
            $filters['lng'] = $tutor->lng;
            $filters['radius'] = $tutor->travel_distance_km ?? 10;
            $filters['nearby'] = true;
        } elseif ($tutor->city) {
            $filters['location'] = $tutor->city;
        }

        if ($tutor->price_per_hour) {
            $filters['budget_max'] = $tutor->price_per_hour * 2; // Allow up to 2x hourly rate
        }

        return $this->search($filters, $perPage, $page);
    }

    /**
     * Advanced location filter supporting city, area, and nearby searches
     */
    protected function addLocationFilter(&$must, array $filters): void
    {
        if (!empty($filters['city'])) {
            $must[] = ['match' => ['city.keyword' => $filters['city']]];
        } elseif (!empty($filters['location'])) {
            // Match either city, area, or location
            $must[] = [
                'bool' => [
                    'should' => [
                        ['match' => ['city' => $filters['location']]],
                        ['match' => ['area' => $filters['location']]],
                        ['match' => ['location' => $filters['location']]],
                    ]
                ]
            ];
        }

        // Filter by state if provided
        if (!empty($filters['state'])) {
            $must[] = ['match' => ['state.keyword' => $filters['state']]];
        }
    }

    /**
     * Get sort options based on sort_by parameter
     */
    protected function getSortOptions(string $sortBy, array $filters): array
    {
        $sort = [];

        // If nearby search, add distance sorting
        if (!empty($filters['nearby']) && !empty($filters['lat']) && !empty($filters['lng'])) {
            $sort[] = [
                '_geo_distance' => [
                    'location_geo' => [
                        'lat' => floatval($filters['lat']),
                        'lon' => floatval($filters['lng']),
                    ],
                    'order' => 'asc',
                    'unit' => 'km'
                ]
            ];
        }

        // Add requested sort
        switch ($sortBy) {
            case 'budget_high':
                $sort[] = ['budget_max' => ['order' => 'desc']];
                break;
            case 'budget_low':
                $sort[] = ['budget_min' => ['order' => 'asc']];
                break;
            case 'recent':
                $sort[] = ['posted_at' => ['order' => 'desc']];
                break;
        }

        return !empty($sort) ? $sort : [['posted_at' => ['order' => 'desc']], ['created_at' => ['order' => 'desc']]];
    }
}
