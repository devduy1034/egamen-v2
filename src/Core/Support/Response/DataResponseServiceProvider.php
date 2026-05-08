<?php
namespace LARAVEL\Core\Support\Response;

use LARAVEL\Core\ServiceProvider;

class DataResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('baseResponse', function () {
            return new \LARAVEL\Core\Support\Response\Response;
        });
    }
}