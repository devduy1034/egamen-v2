<?php
namespace LARAVEL\Core\Hashing;
use LARAVEL\Core\ServiceProvider;

class HashServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('hash', function () {
            return new BcryptHasher();
        });
    }
    public function provides()
    {
        return ['hash'];
    }
}