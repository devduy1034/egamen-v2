<?php
namespace LARAVEL\Core\Support\Facades;

class Comment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'comment';
    }
}