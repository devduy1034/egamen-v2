<?php
namespace LARAVEL\Core\Session;
use Illuminate\Support\Arr;
class Session
{
    protected static bool $sessionStarted = false;
    public function __construct()
    {
        if (!self::$sessionStarted) {
            session_start();
            self::$sessionStarted = true;
        }
    }
    public function getID(): bool|string{
        return session_id();
    }
    public function isset(string $key): bool {
        $keys = explode('.', $key);
        $sessionData = $_SESSION;
        foreach ($keys as $keyPart) {
            if (isset($sessionData[$keyPart])) {
                $sessionData = $sessionData[$keyPart];
            } else {
                return false;
            }
        }
        return true;
    }
//    public function set(string $key, $value): void
//    {
//        $_SESSION[$key] = $value;
//    }
    public function set(string $key, $value): void {
        $keys = explode('.', $key);
        $sessionData = &$_SESSION;
        foreach ($keys as $keyPart) {
            if (!isset($sessionData[$keyPart]) || !is_array($sessionData[$keyPart])) {
                $sessionData[$keyPart] = [];
            }
            $sessionData = &$sessionData[$keyPart];
        }
        $sessionData = $value;
    }
    public function put($key, $value = null): void
    {
        $_SESSION[$key] = $this->has($key)?$_SESSION[$key]:[];
        if (!is_array($key)) {
            $keyPut = [$key => $value];
        }
        foreach ($keyPut as $arrayKey => $arrayValue) {
            Arr::set($_SESSION[$key], $arrayKey, $arrayValue);
        }

    }
    public function has($key): bool {
        $trimKey = explode('.', $key);
        if (count($trimKey) == 1) {
            return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
        }
        $sessionData = $_SESSION;
        foreach ($trimKey as $part) {
            if (isset($sessionData[$part])) {
                $sessionData = $sessionData[$part];
            } else {
                return false;
            }
        }
        return !empty($sessionData);
    }
    public function hasError($key): bool {
        $trimKey = explode('.', $key);
        if (count($trimKey) == 1) {
            return isset($_SESSION['flashError'][$key]) && !empty($_SESSION['flashError'][$key]);
        }
        $sessionData = $_SESSION['flashError'] ?? [];
        foreach ($trimKey as $part) {
            if (isset($sessionData[$part])) {
                $sessionData = $sessionData[$part];
            } else {
                return false;
            }
        }
        return !empty($sessionData);
    }
    public function unset(string $key): void {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $sessionData = &$_SESSION;
        foreach ($keys as $keyPart) {
            if (isset($sessionData[$keyPart]) && is_array($sessionData[$keyPart])) {
                $sessionData = &$sessionData[$keyPart];
            } else {
                return;
            }
        }
        unset($sessionData[$lastKey]);
    }
   public function getError($key = 'flashError'){
        return $this->get($key)??[];
    }
    public function get(string $key) {
        $trimKey = explode('.', $key);
        $sessionData = $_SESSION;
        foreach ($trimKey as $part) {
            if (isset($sessionData[$part])) {
                $sessionData = $sessionData[$part];
            } else {
                return null;
            }
        }
        return $sessionData;
    }
    public function storage(): array
    {
        return $_SESSION;
    }
}