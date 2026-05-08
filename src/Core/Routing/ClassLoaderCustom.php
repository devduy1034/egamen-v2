<?php
namespace LARAVEL\Core\Routing;
use Pecee\SimpleRouter\ClassLoader\ClassLoader;
use ReflectionMethod;
class ClassLoaderCustom extends ClassLoader
{
    /**
     * @throws \ReflectionException
     */
    public function loadClassMethod($class, string $method, array $parameters): string
    {
        if (isset($parameters['language'])) unset($parameters['language']);
        $reflection = new ReflectionMethod($class, $method);
        $controller_parameter = [];
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin() && $type->getName() === \Illuminate\Http\Request::class) {
                $parameters[$param->name] = \Illuminate\Http\Request::capture();
            } 
            $controller_parameter[$param->name] = $parameters[$param->name] ?? null;
        }
        $value = (string)call_user_func_array([$class, $method], array_values($controller_parameter));
        \LARAVEL\Facade\EventHandler::dispatch(EventHandler::EVENT_FINISH, [$class, $method, $controller_parameter]);
        return $value;
    }
}
