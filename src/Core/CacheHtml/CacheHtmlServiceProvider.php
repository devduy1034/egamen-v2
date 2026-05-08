<?php
namespace LARAVEL\Core\CacheHtml;
use LARAVEL\Core\ServiceProvider;
class CacheHtmlServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('cachehtml', function () {
            return new CacheHtml();
        });
    }
    public function provides()
    {
        return ['cachehtml'];
    }
}