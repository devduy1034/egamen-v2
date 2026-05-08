<?php
namespace Pecee\Http;
use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;
use Pecee\Exceptions\InvalidArgumentException;

class Response
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function httpCode(int $code): self
    {
        http_response_code($code);

        return $this;
    }
    #[NoReturn] public function redirect(string $url, ?int $httpCode = null): void
    {
        if ($httpCode !== null) {
            $this->httpCode($httpCode);
        }

        \LARAVEL\Facade\EventHandler::dispatch(\LARAVEL\Core\Routing\EventHandler::EVENT_FINISH, ['url' => $url, 'code' => $httpCode]);
        $this->header('location: ' . $url);
        exit(0);
    }

    #[NoReturn] public function refresh(): void
    {
        $this->redirect($this->request->getUrl()->getOriginalUrl());
    }
    public function auth(string $name = ''): self
    {
        $this->headers([
            'WWW-Authenticate: Basic realm="' . $name . '"',
            'HTTP/1.0 401 Unauthorized',
        ]);

        return $this;
    }

    public function cache(string $eTag, int $lastModifiedTime = 2592000): self
    {
        $this->headers([
            'Cache-Control: public',
            sprintf('Last-Modified: %s GMT', gmdate('D, d M Y H:i:s', $lastModifiedTime)),
            sprintf('Etag: %s', $eTag),
        ]);
        $httpModified = $this->request->getHeader('http-if-modified-since');
        $httpIfNoneMatch = $this->request->getHeader('http-if-none-match');
        if (($httpIfNoneMatch !== null && $httpIfNoneMatch === $eTag) || ($httpModified !== null && strtotime($httpModified) === $lastModifiedTime)) {

            $this->header('HTTP/1.1 304 Not Modified');
            exit(0);
        }
        return $this;
    }
    public function json($value, int $options = 0, int $dept = 512): void
    {
        if (($value instanceof JsonSerializable) === false && is_array($value) === false) {
            throw new InvalidArgumentException('Invalid type for parameter "value". Must be of type array or object implementing the \JsonSerializable interface.');
        }
        $this->header('Content-Type: application/json; charset=utf-8');
        echo json_encode($value, $options, $dept);
        exit(0);
    }
    public function header(string $value): self
    {
        header($value);
        return $this;
    }
    public function headers(array $headers): self
    {
        foreach ($headers as $header) {
            $this->header($header);
        }
        return $this;
    }
}