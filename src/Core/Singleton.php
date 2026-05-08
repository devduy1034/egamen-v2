<?php
namespace LARAVEL\Core;
trait Singleton
{
    private static $instance;
    public static function getInstance(): static
    {
        if (!self::$instance) {
            return self::$instance = new self(...func_get_args());
        }
        return self::$instance;
    }
    public static function setInstance($instance = null): static
    {
        return static::$instance = $instance;
    }
}
