<?php
namespace LARAVEL\Core\Translator;
use LARAVEL\Core\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('translator', function () {
            return $this->app->make(\LARAVEL\Core\Translator\Translator::class);
        });
    }
}