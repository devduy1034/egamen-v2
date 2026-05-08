<?php
namespace LARAVEL\Core\Support\Facades;

class Flash extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flash';
    }
}