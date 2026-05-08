<?php
namespace LARAVEL\Core\Validator;
use LARAVEL\Core\ServiceProvider;
class ValidationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $validator = $this->app->make('validator');
        $validator->setRules([
            'required',
            'min',
            'max',
            'number',
            'string',
            'file',
            'image',
            'mimes',
            'video',
            'audio',
            'email',
            'unique',
            'same'
        ]);
    }
    public function register(): void
    {
        $this->app->singleton('validator', function () {
            return new Validator();
        });
    }
}