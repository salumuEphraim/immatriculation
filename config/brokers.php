<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Broker Services Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les services externes de vérification documents véhicule
    | Lubumbashi (DGI, SONAS, CTR, etc.).
    |
    */

    'timeout_default' => 5,

    'cache_ttl' => 3600, // 1h cache par plaque/doc

    'mock_mode' => env('BROKER_MOCK_MODE', true), // true pour dev (random statuses)

    'http_options' => [
        'verify' => false, // Dev only
        'headers' => [
            'User-Agent' => 'RoadShieldCD-Agent/1.0',
            'Accept' => 'application/json',
        ],
    ],

    /*
     * Exemples brokers (surchargés par DB)
     */
    'brokers' => [
        // 'dgi' => [
        //     'endpoint' => env('BROKER_DGI_ENDPOINT'),
        //     'api_key' => env('BROKER_DGI_API_KEY'),
        // ],
    ],
];

