<?php


return [
    'default' => env('DB_CONNECTION','mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST','localhost'),
            'port' => env('DB_PORT','3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'prefix' => env('DB_PREFIX','table_')
        ]
    ],
];