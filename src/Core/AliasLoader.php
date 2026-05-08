<?php
namespace LARAVEL\Core;
class AliasLoader
{
    public function __construct()
    {
        spl_autoload_register([$this, 'aliasLoader']);
    }
    public function aliasLoader(string $class): bool
    {
        $alias = \LARAVEL\Core\Support\Facades\Facade::defaultAliases()->merge(config('app.aliases'))->toArray();
        if (isset($alias[$class])) {
            return class_alias($alias[$class], $class);
        }
        return true;
    }
}