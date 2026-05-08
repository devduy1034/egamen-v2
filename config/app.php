<?php

return [
    'timezone' => env('APP_TIMEZONE'),
    'site_path' => env('SITE_PATH'),
    'asset' => env('APP_URL'),
    'admin' => env('APP_URL') . "admin/",
    "token" => md5(env('MSHD','')),
    "author" => env('AUTHOR'),
    "environment" => env('ENVIRONMENT', 'dev'),
    "mobile" => env('MOBILE', 'false'),
    'random_key' => env('RANDOM_KEY', 'f0b952448bb44939db36aa9859f77030'),
    'secretkey' => '!@*S3cr3tP3pp3r',
    'recaptcha' => [
        'active' => env('GG_RECAPTCHA', false),
        'urlapi' => env('GG_URLAPI'),
        'sitekey' => env('GG_SITEKEY'),
        'secretkey' => env('GG_SECRETKEY')
    ],
    'oneSignal' => array(
        'active' => env('ONE_ACTIVE', true),
        'id' => env('ONE_ID'),
        'restId' => env('ONE_RESTID')
    ),
    'oauth' => [
        'google' => [
            'active' => env('GOOGLE_LOGIN_ACTIVE', false),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
        ],
    ],
    'langs' => [
        "vi" => 'Tiếng Việt',
        // "en" => 'English'
    ],
    'slugs' => [
        "vi" => 'Tiếng Việt'
    ],
    // 'lang_default' => env('LANG_DEFAULT', 'vi'),
    'seo_default' => env('LANG_SEO_DEFAULT', 'vi'),
    'langconfig' => env('LANG_CONFIG', 'session'),
    'cache_file' => env('CACHE_HTML', false),
    'cache_pages_time' => env('CACHE_HTML_TIME', 10),
    'cache_css' => env('CACHE_CSS', false),
    'cache_js' => env('CACHE_JS', false),
    'image_webp_quality' => (int) env('IMAGE_WEBP_QUALITY', 82),
    'nocache' => [],
    'web_prefix' => substr(env('SITE_PATH'), 0, -1) . ((env('LANG_CONFIG') == 'link') ? '/{language}' : ''),
    'admin_prefix' => (env('SITE_PATH') . 'admin'),
    'aliases' => [
        "Email" => \LARAVEL\Core\Support\Facades\Email::class,
        "Comment" => \LARAVEL\Core\Support\Facades\Comment::class,
        "Cart" => \LARAVEL\Facade\Cart::class,
        "LARAVEL" => \LARAVEL\Facade\LARAVEL::class,
        "Event" => \LARAVEL\Facade\Event::class,
        "Clockwork" => \LARAVEL\Helpers\Clockwork\Facade::class,
        "EventHandler" => \LARAVEL\Facade\EventHandler::class
    ],
    'providers' => [
        \LARAVEL\Providers\EmailServiceProvider::class,
        \LARAVEL\Providers\CommentServiceProvider::class,
        \LARAVEL\Providers\LARAVELServiceProvider::class,
        \LARAVEL\Cart\CartServiceProvider::class,
        \LARAVEL\LARAVELGateway\Providers\GatewayServiceProvider::class
    ]
];
