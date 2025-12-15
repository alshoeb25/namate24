<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([env('ELASTIC_HOST')])
            ->build();
    }

    /**
     * Return Elasticsearch client instance
     */
    public function client()
    {
        return $this->client;
    }

    public function index(string $index, array $body)
    {
        return $this->client->index([
            'index' => $index,
            'id'    => $body['id'],
            'body'  => $body
        ]);
    }

    public function delete(string $index, string $id)
    {
        return $this->client->delete([
            'index' => $index,
            'id'    => $id
        ]);
    }

    public function search(string $index, string $query, array $filters = [])
    {
        return $this->client->search([
            'index' => $index,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['name', 'subject', 'skills', 'city']
                    ]
                ]
            ]
        ]);
    }
}
