<?php



namespace LARAVEL\Middlewares;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class LangRequest implements IMiddleware
{
    public function handle(Request $request): void
    {
        if (session()->get('locale') == null) {
            session()->set('locale', config('app.lang_default'));
        }
    }
}
