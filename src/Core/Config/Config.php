<?php
namespace LARAVEL\Core\Config;
use ArrayAccess;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use LARAVEL\Core\Singleton;

class Config implements ArrayAccess, ConfigContract
{
    use Singleton;
    use Macroable;
    protected $items = [];
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    public function has($key): bool
    {
        return Arr::has($this->items, $key);
    }
    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }
        return Arr::get($this->items, $key, $default);
    }
    public function getMany($keys): array
    {
        $config = [];

        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                [$key, $default] = [$default, null];
            }

            $config[$key] = Arr::get($this->items, $key, $default);
        }

        return $config;
    }
    public function set($key, $value = null): void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }
    public function setConfigCache($value)
    {
        $this->items = $value;
    }
    public function prepend($key, $value): void
    {
        $array = $this->get($key, []);

        array_unshift($array, $value);

        $this->set($key, $array);
    }
    public function push($key, $value): void
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->set($key, $array);
    }
    public function all()
    {
        return $this->items;
    }
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }
    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }

}