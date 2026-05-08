<?php


return [
    'loginpage' => 'basic', //cover or basic
    'defaults' => [
        'guard' => 'admin'
    ],
    'guards' => [
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ]
    ],
    'providers' => [
        'admin' => [
            'driver' => 'eloquent',
            'model' => \LARAVEL\Models\UserModel::class,
            'table' => 'user'
        ]
    ]
];