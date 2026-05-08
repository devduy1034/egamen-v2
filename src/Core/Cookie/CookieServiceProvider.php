<?php
namespace LARAVEL\Core\Cookie;

use LARAVEL\Core\ServiceProvider;

class CookieServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('cookie', function () {
            $config = [
                'path' => '/',
                'domain' => request()->getHost(),
                'secure' => request()->secure()
            ];
            return (new CookieManager)->setDefaultPathAndDomain(
                $config['path'], $config['domain'], $config['secure']
            );
        });
    }
    public function provides()
    {
        return ['cookie'];
    }
}