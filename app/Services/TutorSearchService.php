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

        $must = [];

        // 🔍 Main full-text search (replaces Scout::search)
        if (!empty($query)) {
            $must[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['name', 'subject', 'skills', 'city', 'description'],
                    'type' => 'best_fields'
                ]
            ];
        }

        // 🎯 Exact filters (replaces Scout attribute filters)
        if (!empty($filters['subject_id'])) {
            $must[] = ['term' => ['subject_id' => intval($filters['subject_id'])]];
        }

        if (!empty($filters['teaching_mode'])) {
            $must[] = ['term' => ['teaching_mode' => $filters['teaching_mode']]];
        }

        if (!empty($filters['city'])) {
            $must[] = ['match' => ['city' => $filters['city']]];
        }

        if (!empty($filters['gender'])) {
            $must[] = ['term' => ['gender' => $filters['gender']]];
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

        // Pagination parameters for Elastic
        $from = ($page - 1) * $perPage;
        $size = $perPage;

        // 🔥 Elasticsearch Query
        $results = $this->elastic->client()->search([
            'index' => 'tutors',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $must
                    ]
                ],
                'from' => $from,
                'size' => $size
            ]
        ]);

        // Extract hits
        $hits = collect($results['hits']['hits'])->pluck('_source');

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
}
