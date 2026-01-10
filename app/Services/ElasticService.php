<?php

namespace App\Services;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use InvalidArgumentException;

class ElasticService
{
    protected Client $client;

    public function __construct()
    {
        // -----------------------------
        // Resolve hosts
        // -----------------------------
        $hostsConfig = config('elasticsearch.hosts');

        if (is_string($hostsConfig) && $hostsConfig !== '') {
            $hosts = [$hostsConfig];
        } elseif (is_array($hostsConfig) && !empty($hostsConfig)) {
            $hosts = $hostsConfig;
        } else {
            $hosts = [env('ELASTICSEARCH_URL', 'https://127.0.0.1:9200')];
        }

        $validatedHosts = [];

        foreach ($hosts as $host) {
            if (!is_string($host) || trim($host) === '') {
                continue;
            }

            $parts = parse_url($host);

            if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['port'])) {
                throw new InvalidArgumentException(
                    'Elasticsearch host must include scheme, host and port. Example: https://127.0.0.1:9200'
                );
            }

            $validatedHosts[] = $host;
        }

        if (empty($validatedHosts)) {
            throw new InvalidArgumentException(
                'No valid Elasticsearch hosts configured. Check config/elasticsearch.php or .env'
            );
        }

        // -----------------------------
        // Build client
        // -----------------------------
        $builder = ClientBuilder::create()
            ->setHosts($validatedHosts)
            ->setRetries(config('elasticsearch.retries', 2));

        // -----------------------------
        // Authentication (REQUIRED)
        // -----------------------------
        $username = config('elasticsearch.username');
        $password = config('elasticsearch.password');

        if ($username && $password) {
            $builder->setBasicAuthentication($username, $password);
        }

        // -----------------------------
        // SSL handling (FIXES cURL 60)
        // -----------------------------
        if (config('elasticsearch.ssl_verification') === false) {
            $builder->setSSLVerification(false);
        }

        // Optional: trusted CA bundle (production)
        if ($ca = config('elasticsearch.ca_bundle')) {
            $builder->setCABundle($ca);
        }

        $this->client = $builder->build();
    }

    /**
     * Get Elasticsearch client
     */
    public function client(): Client
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
            'id'    => $body['id'] ?? null,
            'body'  => $body,
            'refresh' => true,
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
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query'     => $query,
                                    'fields'    => ['name^2', 'subject', 'skills', 'city'],
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                        ],
                        'filter' => $filters,
                    ],
                ],
            ],
        ]);
    }
}
