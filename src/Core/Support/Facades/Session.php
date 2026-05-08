<?php
namespace LARAVEL\Core\Support\Facades;
class Session extends Facade{
    protected static function getFacadeAccessor(): string
    {
        return 'session';
    }
}