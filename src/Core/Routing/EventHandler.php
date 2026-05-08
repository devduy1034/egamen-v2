<?php
namespace LARAVEL\Core\Routing;

use LARAVEL\Core\Singleton;
use Pecee\SimpleRouter\Handlers\EventHandler as HandlersEventHandler;
use Pecee\SimpleRouter\Router;

final class EventHandler extends HandlersEventHandler
{
    use Singleton;
    
    /**
     * Fires before a route is rendered.
     */
    public const EVENT_FINISH = 'onFinish';

    public function __construct()
    {
        array_push(self::$events, self::EVENT_FINISH);
    }

    public function dispatch(string $name, array $eventArgs = []): void
    {
        $this->fireEvents(LARAVELRouter::router(), $name, $eventArgs);
    }

    public function addEventListener(string $name, \Closure $callback): void
    {
        $this->register($name, $callback);
    }
}
