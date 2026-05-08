<?php


return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => upload_path(''),
            'throw' => false,
        ]
    ]
];
