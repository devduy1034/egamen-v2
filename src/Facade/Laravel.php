<?php


namespace LARAVEL\Facade;
use LARAVEL\Core\Support\Facades\Facade;
class LARAVEL extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'LARAVEL';
    }
}