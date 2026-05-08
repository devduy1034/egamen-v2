<?php


namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\Email;

class EmailServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('email', function () {
            return new Email();
        });
    }
    public function provides(){
        return ['email'];
    }
}