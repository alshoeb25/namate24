<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use InvalidArgumentException;

class ElasticService
{
    protected $client;

    public function __construct()
    {
        // Prefer configured hosts, fall back to env URL, then default localhost:9200
        $hostsConfig = config('elasticsearch.hosts');

        if (is_string($hostsConfig) && $hostsConfig !== '') {
            $hosts = [$hostsConfig];
        } elseif (is_array($hostsConfig) && !empty($hostsConfig)) {
            $hosts = $hostsConfig;
        } else {
            $hosts = [env('ELASTICSEARCH_URL', 'http://127.0.0.1:9200')];
        }

        // Validate hosts to avoid silent fallbacks (e.g., hitting Apache on port 80)
        $validatedHosts = [];
        foreach ($hosts as $host) {
            if (!is_string($host) || trim($host) === '') {
                continue;
            }

            $parts = parse_url($host);
            if (empty($parts['scheme']) || empty($parts['host'])) {
                throw new InvalidArgumentException('Elasticsearch host must include scheme and host, e.g. http://127.0.0.1:9200');
            }

            if (!isset($parts['port'])) {
                throw new InvalidArgumentException('Elasticsearch host must include port (usually 9200), e.g. http://127.0.0.1:9200');
            }

            $validatedHosts[] = $host;
        }

        if (empty($validatedHosts)) {
            throw new InvalidArgumentException('No valid Elasticsearch hosts configured. Set ELASTICSEARCH_URL or config/elasticsearch.php hosts.');
        }

        $this->client = ClientBuilder::create()
            ->setHosts($validatedHosts)
            ->build();
    }

    /**
     * Get Elasticsearch client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Index a document
     */
    public function index(string $index, array $body)
    {
        return $this->client->index([
            'index' => $index,
            'id'    => $body['id'],
            'body'  => $body,
        ]);
    }

    /**
     * Delete a document
     */
    public function delete(string $index, string $id)
    {
        return $this->client->delete([
            'index' => $index,
            'id'    => $id,
        ]);
    }

    /**
     * Search documents
     */
    public function search(string $index, string $query, array $filters = [])
    {
        return $this->client->search([
            'index' => $index,
            'body'  => [
                'query' => [
                    'multi_match' => [
                        'query'  => $query,
                        'fields'=> ['name', 'subject', 'skills', 'city'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
            ],
        ]);
    }
}
