<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Seo;

class SeoServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('seo', function () {
            return new Seo();
        });
    }
    public function provides(){
        return ['seo'];
    }
}