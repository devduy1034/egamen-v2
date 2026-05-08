<?php
namespace Pecee\Http;

use Pecee\Http\Exceptions\MalformedUrlException;
use Pecee\Http\Input\InputHandler;
use Pecee\Http\Middleware\BaseCsrfVerifier;
use Pecee\SimpleRouter\Route\ILoadableRoute;
use Pecee\SimpleRouter\Route\RouteUrl;
use Pecee\SimpleRouter\SimpleRouter;

class Request
{
    public const REQUEST_TYPE_GET = 'get';
    public const REQUEST_TYPE_POST = 'post';
    public const REQUEST_TYPE_PUT = 'put';
    public const REQUEST_TYPE_PATCH = 'patch';
    public const REQUEST_TYPE_OPTIONS = 'options';
    public const REQUEST_TYPE_DELETE = 'delete';
    public const REQUEST_TYPE_HEAD = 'head';

    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
    public const CONTENT_TYPE_X_FORM_ENCODED = 'application/x-www-form-urlencoded';

    public const FORCE_METHOD_KEY = '_method';

    public static array $requestTypes = [
        self::REQUEST_TYPE_GET,
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_OPTIONS,
        self::REQUEST_TYPE_DELETE,
        self::REQUEST_TYPE_HEAD,
    ];

    public static array $requestTypesPost = [
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_DELETE,
    ];
    private array $data = [];
    protected array $headers = [];
    protected string $contentType;
    protected ?string $host;
    protected Url $url;
    protected string $method;
    protected InputHandler $inputHandler;
    protected bool $hasPendingRewrite = false;
    protected ?ILoadableRoute $rewriteRoute = null;
    protected ?string $rewriteUrl = null;
    protected array $loadedRoutes = [];
    public function __construct()
    {
        foreach ($_SERVER as $key => $value) {
            $this->headers[strtolower($key)] = $value;
            $this->headers[str_replace('_', '-', strtolower($key))] = $value;
        }
        $this->setHost($this->getHeader('http-host'));
        $url = $this->getHeader('unencoded-url');
        if ($url !== null) {
            $this->setUrl(new Url($url));
        } else {
            $this->setUrl(new Url(urldecode((string)$this->getHeader('request-uri'))));
        }
        $this->setContentType((string)$this->getHeader('content-type'));
        $this->setMethod((string)($_POST[static::FORCE_METHOD_KEY] ?? $this->getHeader('request-method')));
        $this->inputHandler = new InputHandler($this);
    }

    public function isSecure(): bool
    {
        return $this->getHeader('http-x-forwarded-proto') === 'https' || $this->getHeader('https') !== null || (int)$this->getHeader('server-port') === 443;
    }
    public function getUrl(): Url
    {
        return $this->url;
    }
    public function getUrlCopy(): Url
    {
        return clone $this->url;
    }
    public function getHost(): ?string
    {
        return $this->host;
    }
    public function getMethod(): ?string
    {
        return $this->method;
    }
    public function getUser(): ?string
    {
        return $this->getHeader('php-auth-user');
    }
    public function getPassword(): ?string
    {
        return $this->getHeader('php-auth-pw');
    }
    public function getCsrfToken(): ?string
    {
        return $this->getHeader(BaseCsrfVerifier::HEADER_KEY);
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function getIp(bool $safeMode = false): ?string
    {
        $headers = [];
        if ($safeMode === false) {
            $headers = [
                'http-cf-connecting-ip',
                'http-client-ip',
                'http-x-forwarded-for',
            ];
        }

        $headers[] = 'remote-addr';

        return $this->getFirstHeader($headers);
    }
    public function getRemoteAddr(): ?string
    {
        return $this->getIp();
    }
    public function getReferer(): ?string
    {
        return $this->getHeader('http-referer');
    }
    public function getUserAgent(): ?string
    {
        return $this->getHeader('http-user-agent');
    }
    public function getHeader(string $name, $defaultValue = null, bool $tryParse = true): ?string
    {
        $name = strtolower($name);
        $header = $this->headers[$name] ?? null;
        if ($tryParse === true && $header === null) {
            if (str_starts_with($name, 'http-')) {
                $header = $this->headers[str_replace('http-', '', $name)] ?? null;
            } else {
                $header = $this->headers['http-' . $name] ?? null;
            }
        }

        return $header ?? $defaultValue;
    }
    public function getFirstHeader(array $headers, $defaultValue = null)
    {
        foreach ($headers as $header) {
            $header = $this->getHeader($header);
            if ($header !== null) {
                return $header;
            }
        }

        return $defaultValue;
    }
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
    protected function setContentType(string $contentType): self
    {
        if (strpos($contentType, ';') > 0) {
            $this->contentType = strtolower(substr($contentType, 0, strpos($contentType, ';')));
        } else {
            $this->contentType = strtolower($contentType);
        }

        return $this;
    }
    public function getInputHandler(): InputHandler
    {
        return $this->inputHandler;
    }
    public function isFormatAccepted(string $format): bool
    {
        return ($this->getHeader('http-accept') !== null && stripos($this->getHeader('http-accept'), $format) !== false);
    }
    public function isAjax(): bool
    {
        return (strtolower((string)$this->getHeader('http-x-requested-with')) === 'xmlhttprequest');
    }
    public function isPostBack(): bool
    {
        return in_array($this->getMethod(), static::$requestTypesPost, true);
    }
    public function getAcceptFormats(): array
    {
        return explode(',', $this->getHeader('http-accept'));
    }
    public function setUrl(Url $url): void
    {
        $this->url = $url;

        if ($this->isSecure() === true) {
            $this->url->setScheme('https');
        }
    }
    public function setHost(?string $host): void
    {
        if (str_contains((string)$host, ':')) {
            $host = strstr($host, strrchr($host, ':'), true);
        }

        $this->host = $host;
    }
    public function setMethod(string $method): void
    {
        $this->method = strtolower($method);
    }
    public function setRewriteRoute(ILoadableRoute $route): self
    {
        $this->hasPendingRewrite = true;
        $this->rewriteRoute = SimpleRouter::addDefaultNamespace($route);

        return $this;
    }
    public function getRewriteRoute(): ?ILoadableRoute
    {
        return $this->rewriteRoute;
    }
    public function getRewriteUrl(): ?string
    {
        return $this->rewriteUrl;
    }
    public function setRewriteUrl(string $rewriteUrl): self
    {
        $this->hasPendingRewrite = true;
        $this->rewriteUrl = rtrim($rewriteUrl, '/') . '/';

        return $this;
    }
    public function setRewriteCallback($callback): self
    {
        $this->hasPendingRewrite = true;

        return $this->setRewriteRoute(new RouteUrl($this->getUrl()->getPath(), $callback));
    }
    public function getLoadedRoute(): ?ILoadableRoute
    {
        return (count($this->loadedRoutes) > 0) ? end($this->loadedRoutes) : null;
    }
    public function getLoadedRoutes(): array
    {
        return $this->loadedRoutes;
    }
    public function setLoadedRoutes(array $routes): self
    {
        $this->loadedRoutes = $routes;
        return $this;
    }
    public function addLoadedRoute(ILoadableRoute $route): self
    {
        $this->loadedRoutes[] = $route;
        return $this;
    }
    public function hasPendingRewrite(): bool
    {
        return $this->hasPendingRewrite;
    }
    public function setHasPendingRewrite(bool $boolean): self
    {
        $this->hasPendingRewrite = $boolean;
        return $this;
    }
    public function __isset($name): bool
    {
        return array_key_exists($name, $this->data) === true;
    }
    public function __set($name, $value = null)
    {
        $this->data[$name] = $value;
    }
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
}