<?php

namespace App\Services;

use App\Models\Tutor;
use App\Services\ElasticService;
use Illuminate\Pagination\LengthAwarePaginator;

class TutorSearchService
{
    protected $elastic;

    public function __construct(ElasticService $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * Search Tutors via Elasticsearch with filters.
     * Returns LengthAwarePaginator
     */
    public function search(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = $filters['q'] ?? '';

        $lat = isset($filters['lat']) && is_numeric($filters['lat']) ? (float) $filters['lat'] : null;
        $lng = isset($filters['lng']) && is_numeric($filters['lng']) ? (float) $filters['lng'] : null;

        $must = [];
        $should = [];

        // ✅ Filter by approved moderation status (ALWAYS)
        $must[] = ['term' => ['moderation_status.keyword' => 'approved']];

        // 🔍 Main full-text search (replaces Scout::search)
        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name', 'subjects', 'skills', 'city', 'area', 'headline'],
                    'type' => 'best_fields'
                ]
            ];
        }

        // 🎯 Exact filters (replaces Scout attribute filters)
        if (!empty($filters['subject_id'])) {
            $must[] = ['term' => ['subject_ids' => intval($filters['subject_id'])]];
        }

        if (!empty($filters['subject'])) {
            $should[] = ['match' => ['subjects' => $filters['subject']]];
        }

        if (!empty($filters['teaching_mode'])) {
            $must[] = ['term' => ['teaching_mode.keyword' => $filters['teaching_mode']]];
        }

        if (!empty($filters['gender'])) {
            $must[] = ['term' => ['gender.keyword' => $filters['gender']]];
        }

        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $min = $filters['min_price'] ?? 0;
            $max = $filters['max_price'] ?? 999999;

            $must[] = [
                'range' => [
                    'price_per_hour' => [
                        'gte' => floatval($min),
                        'lte' => floatval($max),
                    ]
                ]
            ];
        }

        if (!empty($filters['rating_min'])) {
            $must[] = [
                'range' => [
                    'rating_avg' => ['gte' => floatval($filters['rating_min'])]
                ]
            ];
        }

        if (!empty($filters['verified']) && $filters['verified'] === 'true') {
            $must[] = ['term' => ['verified' => true]];
        }

        if (!empty($filters['online']) && $filters['online'] === 'true') {
            $must[] = ['term' => ['online_available' => true]];
        }

        // 🌟 Featured filter (high-rated, verified tutors)
        if (!empty($filters['featured']) && $filters['featured'] === 'true') {
            $must[] = ['term' => ['verified' => true]];
            $must[] = [
                'range' => [
                    'rating_avg' => ['gte' => 4.5]
                ]
            ];
        }

        // 📍 Location-based search with distance
        if (!empty($filters['location']) || ($lat !== null && $lng !== null)) {
            $this->addLocationFilter($must, $filters);
        }

        // 📍 Nearby tutors search (within radius)
        if (!empty($filters['nearby']) && $lat !== null && $lng !== null) {
            $radius = $filters['radius'] ?? '5km'; // Default 5km
            $must[] = [
                'geo_distance' => [
                    'distance' => $radius,
                    'location' => [
                        'lat' => $lat,
                        'lon' => $lng,
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
            $queryBody['sort'] = $this->getSortOptions($filters['sort_by'], $filters, $lat, $lng);
        } elseif (!empty($filters['featured']) && $filters['featured'] === 'true') {
            // Featured sorting: by rating and rating count
            $queryBody['sort'] = [
                ['rating_avg' => ['order' => 'desc']],
                ['rating_count' => ['order' => 'desc']],
            ];
        } else {
            // Default: Sort by verified status and rating
            $queryBody['sort'] = [
                ['verified' => ['order' => 'desc']],
                ['rating_avg' => ['order' => 'desc']],
            ];
        }

        // 🔥 Elasticsearch Query
        $results = $this->elastic->client()->search([
            'index' => 'tutors',
            'body' => $queryBody
        ]);

        // Extract hits
        $hits = collect($results['hits']['hits'])->map(fn($hit) => $hit['_source']);

        // Total hits
        $total = $results['hits']['total']['value'] ?? 0;

        // Same format as Scout::paginate()
        return new LengthAwarePaginator(
            $hits,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Search for nearby tutors within a radius of given coordinates
     */
    public function searchNearby(float $latitude, float $longitude, array $filters = [], int $radius = 5, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $filters['lat'] = $latitude;
        $filters['lng'] = $longitude;
        $filters['nearby'] = true;
        $filters['radius'] = $radius . 'km';

        return $this->search($filters, $perPage, $page);
    }

    /**
     * Search for tutors by city name or area
     */
    public function searchByLocation(string $location, array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $filters['location'] = $location;
        return $this->search($filters, $perPage, $page);
    }

    /**
     * Advanced location filter supporting city, state, and nearby searches
     */
    protected function addLocationFilter(&$must, array $filters): void
    {
        if (!empty($filters['city'])) {
            $must[] = ['match' => ['city.keyword' => $filters['city']]];
        } elseif (!empty($filters['location'])) {
            // Match either city, area, or state
            $must[] = [
                'bool' => [
                    'should' => [
                        ['match' => ['city' => $filters['location']]],
                        ['match' => ['area' => $filters['location']]],
                        ['match' => ['state' => $filters['location']]],
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
    protected function getSortOptions(string $sortBy, array $filters, ?float $lat = null, ?float $lng = null): array
    {
        $sort = [];

        // If nearby search, add distance sorting
        if (!empty($filters['nearby']) && $lat !== null && $lng !== null) {
            $sort[] = [
                '_geo_distance' => [
                    'location' => [
                        'lat' => $lat,
                        'lon' => $lng,
                    ],
                    'order' => 'asc',
                    'unit' => 'km'
                ]
            ];
        }

        // Add requested sort
        switch ($sortBy) {
            case 'price_low_to_high':
                $sort[] = ['price_per_hour' => ['order' => 'asc']];
                break;
            case 'price_high_to_low':
                $sort[] = ['price_per_hour' => ['order' => 'desc']];
                break;
            case 'rating':
                $sort[] = ['rating_avg' => ['order' => 'desc']];
                break;
            case 'experience':
                $sort[] = ['experience_total_years' => ['order' => 'desc']];
                break;
        }

        return !empty($sort) ? $sort : [['verified' => ['order' => 'desc']], ['rating_avg' => ['order' => 'desc']]];
    }
}
