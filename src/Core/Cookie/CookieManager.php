<?php
namespace LARAVEL\Core\Cookie;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Support\Arr;
use Illuminate\Support\InteractsWithTime;
class CookieManager
{
    use InteractsWithTime;
    protected $path = '/';
    protected $domain;
    protected $secure;
    protected $sameSite = 'lax';
    protected $queued = [];

    public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null){
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);
        $time = ($minutes == 0) ? 0 : $this->availableAt($minutes * 60);
        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forever($name, $value, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null){
        return $this->make($name, $value, 525600, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forget($name, $path = null, $domain = null){
        return $this->make($name, null, -2628000, $path, $domain);
    }
    public function hasQueued($key, $path = null){
        return !is_null($this->queued($key, null, $path));
    }
    public function queued($key, $default = null, $path = null){
        $queued = Arr::get($this->queued, $key, $default);
        if ($path === null) {
            return Arr::last($queued, null, $default);
        }
        return Arr::get($queued, $path, $default);
    }
    public function queue(...$parameters){
        if (isset($parameters[0]) && $parameters[0] instanceof Cookie) {
            $cookie = $parameters[0];
        } else {
            $cookie = $this->make(...array_values($parameters));
        }
        if (!isset($this->queued[$cookie->getName()])) {
            $this->queued[$cookie->getName()] = [];
        }
        $this->queued[$cookie->getName()][$cookie->getPath()] = $cookie;
    }
    public function expire($name, $path = null, $domain = null): void {
        $this->queue($this->forget($name, $path, $domain));
    }
    public function unqueue($name, $path = null): void {
        if ($path === null) {
            unset($this->queued[$name]);
            return;
        }
        unset($this->queued[$name][$path]);
        if (empty($this->queued[$name])) {
            unset($this->queued[$name]);
        }
    }
    public function getQueuedCookies(): array {
        return Arr::flatten($this->queued);
    }
    protected function getPathAndDomain($path, $domain, $secure = null, $sameSite = null): array {
        return [$path ?: $this->path, $domain ?: $this->domain, is_bool($secure) ? $secure : $this->secure, $sameSite ?: $this->sameSite];
    }
    public function setDefaultPathAndDomain($path, $domain, $secure = false, $sameSite = null): static {
        [$this->path, $this->domain, $this->secure, $this->sameSite] = [$path, $domain, $secure, $sameSite];
        return $this;
    }
    public function flushQueuedCookies(): static {
        $this->queued = [];
        return $this;
    }
    public function setCookies(): void
    {
        foreach ($this->getQueuedCookies() as $cookie) {
            
            setcookie($cookie->getName(), $cookie->getValue()??'', $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }
    }

}