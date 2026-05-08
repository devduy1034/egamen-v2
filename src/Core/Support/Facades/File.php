<?php
namespace LARAVEL\Core\Support\Facades;
/**
 * @method static exists(string $upload)
 */
class File extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'files';
    }
}