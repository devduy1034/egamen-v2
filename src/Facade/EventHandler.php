<?php



namespace LARAVEL\Facade;

use LARAVEL\Core\Support\Facades\Facade;

/**
 * @method static void dispatch(string $event, array $payload = [])
 * @method static void addEventListener(string $event, \Closure $callback)
 * @static string EVENT_FINISH
 * @see \LARAVEL\Core\Routing\EventHandler
 */
final class EventHandler extends Facade
{
    
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'event_handler';
    }
}
