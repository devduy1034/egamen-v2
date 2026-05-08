<?php
namespace LARAVEL\Core\Request;
use LARAVEL\Core\ServiceProvider;

class RequestServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('request', function () {
            return Request::capture();
        });
    }
    public function provides()
    {
        return ['request'];
    }
}