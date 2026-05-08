<?php



namespace LARAVEL\Providers;

use LARAVEL\Core\Routing\EventHandler;
use LARAVEL\Core\Routing\LARAVELRouter;

final class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void {}

    public function provides()
    {
        return ['app_service'];
    }

    public function boot(): void
    {
        
    }
}