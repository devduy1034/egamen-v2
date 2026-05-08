<?php
namespace LARAVEL\Core\Auth;

use LARAVEL\Core\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('auth', function () {
            return $this->app->make(\LARAVEL\Core\Auth\Authenticatable::class);
        });
    }
}