<?php
namespace LARAVEL\Core\Support\Facades;

class Translator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'translator';
    }
}