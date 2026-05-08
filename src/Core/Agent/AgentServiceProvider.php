<?php
namespace LARAVEL\Core\Agent;
use LARAVEL\Core\ServiceProvider;
class AgentServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('agent', function () {
            return new \Jenssegers\Agent\Agent();
        });
    }
    public function provides()
    {
        return ['agent'];
    }
}