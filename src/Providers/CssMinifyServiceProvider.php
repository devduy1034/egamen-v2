<?php



namespace LARAVEL\Providers;

use LARAVEL\Core\ServiceProvider;
use LARAVEL\Helpers\CssMinify;

class CssMinifyServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('cssminify', function () {
            return new CssMinify();
        });
    }
    public function provides(){
        return ['cssminify'];
    }
}