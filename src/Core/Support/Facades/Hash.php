<?php
namespace LARAVEL\Core\Support\Facades;
/**
 * @method static make(string $string)
 */
class Hash extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'hash';
    }
}