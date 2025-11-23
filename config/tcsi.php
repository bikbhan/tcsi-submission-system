<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TCSI Provider Configuration
    |--------------------------------------------------------------------------
    */
    'provider_code' => env('TCSI_PROVIDER_CODE', 'PRV12345'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Submission Method
    |--------------------------------------------------------------------------
    | Options: 'proda', 'api'
    */
    'default_method' => env('TCSI_DEFAULT_METHOD', 'proda'),
    
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('TCSI_API_ENABLED', false),
        'endpoint' => env('TCSI_API_ENDPOINT', 'https://tcsi.edu.au/api/v2/'),
        'key' => env('TCSI_API_KEY', ''),
        'timeout' => env('TCSI_API_TIMEOUT', 300),
        'max_retries' => env('TCSI_API_MAX_RETRIES', 3),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'enabled' => env('TCSI_VALIDATION_ENABLED', true),
        'block_on_errors' => env('TCSI_BLOCK_ON_ERRORS', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'exports_path' => 'tcsi/exports',
        'error_reports_path' => 'tcsi/error-reports',
        'templates_path' => 'tcsi/templates',
    ],
];
