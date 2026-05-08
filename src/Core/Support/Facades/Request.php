<?php
namespace LARAVEL\Core\Support\Facades;
class Request extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'request';
    }
}