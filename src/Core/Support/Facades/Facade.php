<?php
namespace LARAVEL\Core\Support\Facades;
use LARAVEL\Core\Exceptions\LARAVELExceptions;
abstract class Facade
{
    protected static $app;
    protected static function getFacadeAccessor()
    {
        throw new LARAVELExceptions("Method " . __METHOD__ . " is not override.");
    }
    public static function __callStatic(string $method, array $arguments)
    {
        return app()->make(static::getFacadeAccessor())->$method(...$arguments);
    }
    public function __call(string $method, array $arguments)
    {
        return app()->make(static::getFacadeAccessor())->$method(...$arguments);
    }
    public static function defaultAliases()
    {
        return collect([
            "View" => \LARAVEL\Core\Support\Facades\View::class,
            "Session" => \LARAVEL\Core\Support\Facades\Session::class,
            "Translator" => \LARAVEL\Core\Support\Facades\Translator::class,
            "Hash" => \LARAVEL\Core\Support\Facades\Hash::class,
            "Auth" => \LARAVEL\Core\Support\Facades\Auth::class,
            "Func" => \LARAVEL\Core\Support\Facades\Func::class,
            "Statistic" => \LARAVEL\Core\Support\Facades\Statistic::class,
            "Flash" => \LARAVEL\Core\Support\Facades\Flash::class,
            "Seo" => \LARAVEL\Core\Support\Facades\Seo::class,
            "File" => \LARAVEL\Core\Support\Facades\File::class,
            "BreadCrumbs" => \LARAVEL\Core\Support\Facades\BreadCrumbs::class,
            "CacheHtml" => \LARAVEL\Core\Support\Facades\CacheHtml::class,
            "DB" => \LARAVEL\Core\Support\Facades\DB::class,
            'Config' => \LARAVEL\Core\Support\Facades\Config::class,
            'Validator' => \LARAVEL\Core\Support\Facades\Validator::class,
            'Image' => \LARAVEL\Core\Support\Facades\Image::class,
            'Cookie' => \LARAVEL\Core\Support\Facades\Cookie::class
        ]);
    }
}
