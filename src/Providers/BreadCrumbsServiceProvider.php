<?php



namespace LARAVEL\Providers;
use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\BreadCrumbs;
class BreadCrumbsServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('breadcrumbs', function () {
            return new BreadCrumbs();
        });
    }
    public function provides(){
        return ['breadcrumbs'];
    }
}