<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static setCookies()
 */
class Cookie extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cookie';
    }
}