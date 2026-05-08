<?php


ini_set('memory_limit', -1);
const ROOT_PATH = __DIR__;
include_once( __DIR__ . '/vendor/autoload.php' );
$app = require __DIR__ . '/bootstrap/app.php';
$response = $app->make(LARAVEL\Core\App::class);
$response->run();