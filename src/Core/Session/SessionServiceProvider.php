<?php
namespace LARAVEL\Core\Session;

use LARAVEL\Core\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('session', function () {
            return $this->app->make(\LARAVEL\Core\Session\Session::class);
        });
    }
    public function provides()
    {
        return ['session'];
    }
}