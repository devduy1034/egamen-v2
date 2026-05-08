<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static read(string $string)
 */
class Image extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'image';
    }
}