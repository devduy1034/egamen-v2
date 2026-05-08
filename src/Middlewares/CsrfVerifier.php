<?php



namespace LARAVEL\Middlewares;
use Pecee\Http\Middleware\BaseCsrfVerifier;

class CsrfVerifier extends BaseCsrfVerifier
{
    protected array $except = ['/cart/*', '/wishlist/*'];
}
