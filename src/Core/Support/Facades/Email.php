<?php
namespace LARAVEL\Core\Support\Facades;

class Email extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'email';
    }
}