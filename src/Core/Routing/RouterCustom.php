<?php
namespace LARAVEL\Core\Routing;
use LARAVEL\Core\Routing\ClassLoaderCustom;
use Pecee\SimpleRouter\Router;
class RouterCustom extends Router
{
    public function reset(): void {
        parent::reset();
        $this->classLoader = new ClassLoaderCustom();
    }
}