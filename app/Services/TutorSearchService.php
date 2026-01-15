<?php

namespace App\Services;

use App\Models\Tutor;
use App\Services\ElasticService;
use Illuminate\Pagination\LengthAwarePaginator;

class TutorSearchService
{
    protected ElasticService $elastic;

    public function __construct(ElasticService $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * Search Tutors via Elasticsearch (IDs only),
     * then hydrate from DB to keep LOCAL & PROD identical.
     */
    public function search(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = $filters['q'] ?? '';

        $lat = isset($filters['lat']) && is_numeric($filters['lat']) ? (float) $filters['lat'] : null;
        $lng = isset($filters['lng']) && is_numeric($filters['lng']) ? (float) $filters['lng'] : null;

        $must = [];
        $should = [];

        // ✅ Always approved tutors
        $must[] = ['term' => ['moderation_status.keyword' => 'approved']];

        // 🔍 Full-text search
        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query'  => $query,
                    'fields' => ['name', 'subjects', 'skills', 'city', 'area', 'headline'],
                    'type'   => 'best_fields',
                ]
            ];
        }

        // 🎯 Filters
        if (!empty($filters['subject_id'])) {
            $must[] = ['term' => ['subject_ids' => (int) $filters['subject_id']]];
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
            $must[] = [
                'range' => [
                    'price_per_hour' => [
                        'gte' => (float) ($filters['min_price'] ?? 0),
                        'lte' => (float) ($filters['max_price'] ?? 999999),
                    ]
                ]
            ];
        }

        if (!empty($filters['rating_min'])) {
            $must[] = [
                'range' => [
                    'rating_avg' => ['gte' => (float) $filters['rating_min']]
                ]
            ];
        }

        if (!empty($filters['verified']) && $filters['verified'] === 'true') {
            $must[] = ['term' => ['verified' => true]];
        }

        if (!empty($filters['online']) && $filters['online'] === 'true') {
            $must[] = ['term' => ['online_available' => true]];
        }

        // 📍 Location filters
        if (!empty($filters['location']) || ($lat !== null && $lng !== null)) {
            $this->addLocationFilter($must, $filters);
        }

        if (!empty($filters['nearby']) && $lat !== null && $lng !== null) {
            $must[] = [
                'geo_distance' => [
                    'distance' => ($filters['radius'] ?? '5km'),
                    'location' => ['lat' => $lat, 'lon' => $lng],
                ]
            ];
        }

        // Pagination
        $from = ($page - 1) * $perPage;

        // ES Query
        $queryBody = [
            'query' => [
                'bool' => [
                    'must' => $must ?: [['match_all' => new \stdClass()]],
                ],
            ],
            'from' => $from,
            'size' => $perPage,
            'sort' => $this->getSortOptions($filters['sort_by'] ?? null, $filters, $lat, $lng),
        ];

        if (!empty($should)) {
            $queryBody['query']['bool']['should'] = $should;
            $queryBody['query']['bool']['minimum_should_match'] = 1;
        }

        $results = $this->elastic->client()->search([
            'index' => 'tutors',
            'body'  => $queryBody,
        ]);

        // 🔑 Extract tutor IDs ONLY
        $ids = collect($results['hits']['hits'])
            ->pluck('_source.id')
            ->toArray();

        $total = $results['hits']['total']['value'] ?? 0;

        if (empty($ids)) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        // 🧠 Hydrate full Tutor models (same as LOCAL)
        $tutors = Tutor::with([
                'subjects',
                'educations',
                'experiences',
                'languages',
                'user',
            ])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($tutor) => array_search($tutor->id, $ids))
            ->values();

        return new LengthAwarePaginator(
            $tutors,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    protected function addLocationFilter(array &$must, array $filters): void
    {
        if (!empty($filters['city'])) {
            $must[] = ['term' => ['city.keyword' => $filters['city']]];
        } elseif (!empty($filters['location'])) {
            $must[] = [
                'bool' => [
                    'should' => [
                        ['match' => ['city' => $filters['location']]],
                        ['match' => ['area' => $filters['location']]],
                        ['match' => ['state' => $filters['location']]],
                    ],
                ]
            ];
        }

        if (!empty($filters['state'])) {
            $must[] = ['term' => ['state.keyword' => $filters['state']]];
        }
    }

    protected function getSortOptions(?string $sortBy, array $filters, ?float $lat, ?float $lng): array
    {
        $sort = [];

        if (!empty($filters['nearby']) && $lat !== null && $lng !== null) {
            $sort[] = [
                '_geo_distance' => [
                    'location' => ['lat' => $lat, 'lon' => $lng],
                    'order' => 'asc',
                    'unit'  => 'km',
                ]
            ];
        }

        match ($sortBy) {
            'price_low_to_high' => $sort[] = ['price_per_hour' => ['order' => 'asc']],
            'price_high_to_low' => $sort[] = ['price_per_hour' => ['order' => 'desc']],
            'rating'            => $sort[] = ['rating_avg' => ['order' => 'desc']],
            'experience'        => $sort[] = ['experience_total_years' => ['order' => 'desc']],
            default             => null,
        };

        return $sort ?: [
            ['verified' => ['order' => 'desc']],
            ['rating_avg' => ['order' => 'desc']],
        ];
    }
}
