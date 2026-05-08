<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Flash;

class FlashServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('flash', function () {
            return new Flash();
        });
    }
    public function provides(){
        return ['flash'];
    }
}