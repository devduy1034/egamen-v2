<?php



namespace LARAVEL\Cart;

use LARAVEL\Core\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton('cart', function () {
            return $this->app->make(Cart::class);
        });
    }
}