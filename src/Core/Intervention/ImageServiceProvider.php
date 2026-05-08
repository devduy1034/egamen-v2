<?php
namespace LARAVEL\Core\Intervention;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use LARAVEL\Core\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register(): void
    {
        $this->app->singleton('image', function () {
            return new ImageManager(new Driver());
        });
    }
    public function provides(): array
    {
        return ['image'];
    }
}