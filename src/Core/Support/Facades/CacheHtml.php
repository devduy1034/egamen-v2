<?php
namespace LARAVEL\Core\Support\Facades;
class CacheHtml extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cachehtml';
    }
}