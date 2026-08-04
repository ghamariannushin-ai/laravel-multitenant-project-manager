<?php

return [

    'default' => env('DB_CONNECTION', 'central'),

    'connections' => [

        'central' => [
            'driver' => env('CENTRAL_DB_CONNECTION', 'mysql'),
            'host' => env('CENTRAL_DB_HOST', '127.0.0.1'),
            'port' => env('CENTRAL_DB_PORT', '3306'),
            'database' => env('CENTRAL_DB_DATABASE', 'forge'),
            'username' => env('CENTRAL_DB_USERNAME', 'forge'),
            'password' => env('CENTRAL_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'tenant' => [
            'driver' => env('TENANT_DB_CONNECTION', 'mysql'),
            'host' => env('TENANT_DB_HOST', '127.0.0.1'),
            'port' => env('TENANT_DB_PORT', '3306'),
            'database' => env('TENANT_DB_DATABASE', 'forge'),
            'username' => env('TENANT_DB_USERNAME', 'forge'),
            'password' => env('TENANT_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

    ],

];
