<?php
namespace Pecee\Http;

use JsonSerializable;
use Pecee\Http\Exceptions\MalformedUrlException;

class Url implements JsonSerializable
{
    private ?string $originalUrl = null;
    private ?string $scheme = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?string $path = null;
    private ?string $originalPath = null;
    private array $params = [];
    private ?string $fragment = null;
    public function __construct(?string $url)
    {
        $this->originalUrl = $url;
        $this->parse($url, true);
    }
    public function parse(?string $url, bool $setOriginalPath = false): self
    {
        if ($url !== null) {
            $data = $this->parseUrl($url);

            $this->scheme = $data['scheme'] ?? null;
            $this->host = $data['host'] ?? null;
            $this->port = $data['port'] ?? null;
            $this->username = $data['user'] ?? null;
            $this->password = $data['pass'] ?? null;
            if (isset($data['path']) === true) {
                $this->setPath($data['path']);

                if ($setOriginalPath === true) {
                    $this->originalPath = $data['path'];
                }
            }
            $this->fragment = $data['fragment'] ?? null;
            if (isset($data['query']) === true) {
                $this->setQueryString($data['query']);
            }
        }

        return $this;
    }
    public function isSecure(): bool
    {
        return (strtolower($this->getScheme()) === 'https');
    }
    public function isRelative(): bool
    {
        return ($this->getHost() === null);
    }
    public function getScheme(): ?string
    {
        return $this->scheme;
    }
    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }
    public function getHost(bool $includeTrails = false): ?string
    {
        if ((string)$this->host !== '' && $includeTrails === true) {
            return '//' . $this->host;
        }

        return $this->host;
    }
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }
    public function getPort(): ?int
    {
        return ($this->port !== null) ? (int)$this->port : null;
    }
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }
    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    public function getPath(): ?string
    {
        return $this->path ?? '/';
    }
    public function getOriginalPath(): ?string
    {
        return $this->originalPath;
    }
    public function setPath(string $path): self
    {
        $this->path = rtrim($path, '/') . '/';

        return $this;
    }
    public function getParams(): array
    {
        return $this->params;
    }
    public function mergeParams(array $params): self
    {
        return $this->setParams(array_merge($this->getParams(), $params));
    }
    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }
    public function setQueryString(string $queryString): self
    {
        $params = [];
        parse_str($queryString, $params);

        if (count($params) > 0) {
            return $this->setParams($params);
        }

        return $this;
    }
    public function getQueryString(): string
    {
        return static::arrayToParams($this->getParams());
    }
    public function getFragment(): ?string
    {
        return $this->fragment;
    }
    public function setFragment(string $fragment): self
    {
        $this->fragment = $fragment;

        return $this;
    }
    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }
    public function indexOf(string $value): int
    {
        $index = stripos($this->getOriginalUrl(), $value);

        return ($index === false) ? -1 : $index;
    }
    public function contains(string $value): bool
    {
        return (stripos($this->getOriginalUrl(), $value) !== false);
    }
    public function hasParam(string $name): bool
    {
        return array_key_exists($name, $this->getParams());
    }
    public function removeParams(...$names): self
    {
        $params = array_diff_key($this->getParams(), array_flip(...$names));
        $this->setParams($params);

        return $this;
    }
    public function removeParam(string $name): self
    {
        $params = $this->getParams();
        unset($params[$name]);
        $this->setParams($params);

        return $this;
    }
    public function getParam(string $name, ?string $defaultValue = null): ?string
    {
        return (isset($this->getParams()[$name]) === true) ? $this->getParams()[$name] : $defaultValue;
    }
    public function parseUrl(string $url, int $component = -1): array
    {
        $encodedUrl = preg_replace_callback(
            '/[^:\/@?&=#]+/u',
            static function ($matches): string {
                return urlencode($matches[0]);
            },
            $url
        );

        $parts = parse_url($encodedUrl, $component);

        if ($parts === false) {
            throw new MalformedUrlException(sprintf('Failed to parse url: "%s"', $url));
        }

        return array_map('urldecode', $parts);
    }
    public static function arrayToParams(array $getParams = [], bool $includeEmpty = true): string
    {
        if (count($getParams) !== 0) {

            if ($includeEmpty === false) {
                $getParams = array_filter($getParams, static function ($item): bool {
                    return (trim($item) !== '');
                });
            }

            return http_build_query($getParams);
        }

        return '';
    }
    public function getRelativeUrl(bool $includeParams = true): string
    {
        $path = $this->path ?? '/';

        if ($includeParams === false) {
            return $path;
        }

        $query = $this->getQueryString() !== '' ? '?' . $this->getQueryString() : '';
        $fragment = $this->fragment !== null ? '#' . $this->fragment : '';

        return $path . $query . $fragment;
    }
    public function getAbsoluteUrl(bool $includeParams = true): string
    {
        $scheme = $this->scheme !== null ? $this->scheme . '://' : '';
        $host = $this->host ?? '';
        $port = $this->port !== null ? ':' . $this->port : '';
        $user = $this->username ?? '';
        $pass = $this->password !== null ? ':' . $this->password : '';
        $pass = ($user !== '' || $pass !== '') ? $pass . '@' : '';

        return $scheme . $user . $pass . $host . $port . $this->getRelativeUrl($includeParams);
    }
    public function jsonSerialize(): string
    {
        return $this->getHost(true) . $this->getRelativeUrl();
    }
    public function __toString(): string
    {
        return $this->getHost(true) . $this->getRelativeUrl();
    }

}