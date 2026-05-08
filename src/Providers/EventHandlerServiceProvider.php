<?php



namespace LARAVEL\Providers;

use LARAVEL\Core\ServiceProvider;

final class EventHandlerServiceProvider extends ServiceProvider
{
    public function register(): void {
        $this->app->singleton('event_handler', function () {
            return new \LARAVEL\Core\Routing\EventHandler();
        });
    }

    public function provides(){
        return ['event_handler'];
    }
}
