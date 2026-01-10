<?php

return [
     'hosts' => [
        env('ELASTICSEARCH_SCHEME', 'http') . '://' .
        env('ELASTICSEARCH_HOST', '127.0.0.1') . ':' .
        env('ELASTICSEARCH_PORT', 9200),
    ],
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    'username' => env('ELASTICSEARCH_USERNAME',''),
    'password' => env('ELASTICSEARCH_PASSWORD',''),

    // Default index names (override via env if needed)
    'indices' => [
        'tutors' => env('ELASTICSEARCH_INDEX_TUTORS', 'tutors'),
        'requirements' => env('ELASTICSEARCH_INDEX_REQUIREMENTS', 'requirements'),
    ],

    // Connection options
    'retries' => (int) env('ELASTICSEARCH_RETRIES', 2),
    'ssl_verification' => env('ELASTICSEARCH_SSL_VERIFICATION', false),

    // Optional (production only)
    'ca_bundle' => env('ELASTICSEARCH_CA_BUNDLE'),
];
