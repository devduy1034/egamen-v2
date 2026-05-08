<?php


return [
    'compiled' => base_path('compiled'),

    'view_mobile' => base_path('src/Views/templates'),

    'view_templates' => base_path('src/Views/templates'),

    'view_amp' => base_path('src/Views/amp'),

    'mode' => [
        'web' =>LARAVEL\Core\View\BladeOne::MODE_AUTO,
        'admin' =>LARAVEL\Core\View\BladeOne::MODE_AUTO
    ], //BladeOne::MODE_AUTO,BladeOne::MODE_DEBUG,BladeOne::MODE_FAST,BladeOne::MODE_SLOW

    'asset' => '/',

    'composer' => \LARAVEL\Controllers\Web\AllController::class,

    'composer_admin' => \LARAVEL\Controllers\Admin\AllController::class,
];