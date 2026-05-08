<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Laravel;

class LaravelServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('laravel', function () {
            return new Laravel();
        });
    }
    public function provides(){
        return ['laravel'];
    }
}