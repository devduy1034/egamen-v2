<?php
namespace LARAVEL\Core\Support\Facades;
/**
 * @method static \Illuminate\Contracts\Auth\Guard guard(string $string)
 */
class Auth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'auth';
    }
}