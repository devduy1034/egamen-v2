<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static void set(string $key, string $value)
 * @method static mixin get(string $key)
 * @method static array setSeoData(array $data, string|null $path, string|null $table)
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'seo';
    }
}