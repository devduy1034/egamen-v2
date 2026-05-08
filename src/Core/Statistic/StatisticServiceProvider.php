<?php
namespace LARAVEL\Core\Statistic;

use Illuminate\Contracts\Container\BindingResolutionException;
use LARAVEL\Core\ServiceProvider;

class StatisticServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
       // $this->app['statistic']->getCounter();
    }
    public function register(): void
    {
        $this->app->singleton('statistic', function () {
            return new \LARAVEL\Core\Statistic\Statistic();
        });
    }
}