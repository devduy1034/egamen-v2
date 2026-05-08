<?php
namespace LARAVEL\Core\Support\Facades;
class DB extends Facade{
    protected static function getFacadeAccessor(): string
    {
        return 'db';
    }
}