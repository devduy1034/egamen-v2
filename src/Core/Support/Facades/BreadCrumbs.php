<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static set(string|null $key, mixed $value)
 * @method static get()
 * @method static setBreadcrumb(string|null ...$param)
 */
class BreadCrumbs extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'breadcrumbs';
    }
}