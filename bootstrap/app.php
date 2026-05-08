<?php


$app = new \LARAVEL\Core\Container(realpath(__DIR__ . '/../'));
$app->singleton(\LARAVEL\Core\App::class, function ($app) {
    return new \LARAVEL\Core\App($app);
});
return $app;