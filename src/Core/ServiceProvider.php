<?php
namespace LARAVEL\Core;
use LARAVEL\Core\Support\DefaultProviders;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

abstract class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    public function __construct($app){
        parent::__construct($app);
    }
    public function boot(): void
    {}
    public static function defaultProviders(): DefaultProviders
    {
        return new DefaultProviders;
    }

}