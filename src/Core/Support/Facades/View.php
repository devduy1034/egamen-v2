<?php
namespace LARAVEL\Core\Support\Facades;
class View extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'view';
    }
}