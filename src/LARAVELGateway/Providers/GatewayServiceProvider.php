<?php



namespace LARAVEL\LARAVELGateway\Providers;

use LARAVEL\Core\ServiceProvider;
use LARAVEL\LARAVELGateway\GatewayManager;
use LARAVEL\LARAVELGateway\Omnipay\GatewayFactory;

class GatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('gateway', function ($app) {
            $defaults = $app['config']->get('gateways.defaults', array());
            return new GatewayManager($app, new GatewayFactory, $defaults);
        });
    }
}