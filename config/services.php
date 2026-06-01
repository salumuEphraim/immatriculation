<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'ocr_python' => [
        'url' => env('OCR_PYTHON_URL', 'http://127.0.0.1:8010/scan-plaque'),
        'health_url' => env('OCR_PYTHON_HEALTH_URL', 'http://127.0.0.1:8010/health'),
        'timeout' => (int) env('OCR_PYTHON_TIMEOUT', 60),
        'connect_timeout' => (int) env('OCR_PYTHON_CONNECT_TIMEOUT', 10),
        'use_openai' => (bool) env('OCR_PYTHON_USE_OPENAI', true),
    ],

];
