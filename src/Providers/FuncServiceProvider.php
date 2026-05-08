<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Func;

class FuncServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('func', function () {
            return new Func();
        });
    }
    public function provides(){
        return ['func'];
    }
}